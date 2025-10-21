<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Los datos proporcionados no son correctos.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token,
        ]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Asignar rol de viewer por defecto a nuevos usuarios
        $user->assignRole('viewer');

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token,
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    public function user(Request $request)
    {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'roles' => $request->user()->getRoleNames(),
            ],
        ]);
    }

    public function index(Request $request)
    {
        // Solo admin puede ver la lista de usuarios
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $users = User::select('id', 'name', 'email', 'created_at')
            ->with('roles:id,name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                    'created_at' => $user->created_at,
                ];
            });

        return response()->json($users);
    }

    public function show(Request $request, $id)
    {
        // Solo admin puede ver detalles de usuarios
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = User::findOrFail($id);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    public function update(Request $request, $id)
    {
        // Solo admin puede actualizar usuarios
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $id,
            'role' => 'sometimes|string|in:admin,editor,viewer',
        ]);

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        $user->save();

        // Actualizar rol si se proporciona
        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'updated_at' => $user->updated_at,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        // Solo admin puede eliminar usuarios
        if (!$request->user()->hasRole('admin')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // No permitir que el admin se elimine a sí mismo
        if ($request->user()->id == $id) {
            return response()->json(['message' => 'No puedes eliminar tu propia cuenta'], 403);
        }

        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'Usuario eliminado correctamente']);
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Buscar usuario existente por google_id o email
            $user = User::where('google_id', $googleUser->id)
                ->orWhere('email', $googleUser->email)
                ->first();

            if ($user) {
                // Usuario existente - actualizar google_id si no lo tiene
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleUser->id,
                        'avatar' => $googleUser->avatar
                    ]);
                }
            } else {
                // Crear nuevo usuario
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => Hash::make(Str::random(24)), // Password aleatorio ya que usamos Google
                ]);

                // Asignar rol de viewer por defecto
                $user->assignRole('viewer');
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            // En lugar de redireccionar, devolver los datos para el frontend
            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'roles' => $user->getRoleNames(),
                ],
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al autenticar con Google',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Google OAuth URL for frontend
     */
    public function getGoogleAuthUrl()
    {
        $url = Socialite::driver('google')
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json(['url' => $url]);
    }

    /**
     * Handle Google OAuth authentication from frontend
     */
    public function handleGoogleAuth(Request $request)
    {
        try {
            $request->validate([
                'credential' => 'required|string',
            ]);

            // Decodificar el JWT de Google
            $credential = $request->credential;
            $tokenParts = explode('.', $credential);
            $payload = json_decode(base64_decode($tokenParts[1]), true);

            // Verificar que es un token válido de Google
            if (!$payload || $payload['iss'] !== 'https://accounts.google.com') {
                throw new \Exception('Invalid Google token');
            }

            // Extraer información del usuario
            $googleId = $payload['sub'];
            $email = $payload['email'];
            $name = $payload['name'];
            $picture = $payload['picture'] ?? null;
            
            // Buscar usuario existente por google_id o email
            $user = User::where('google_id', $googleId)
                ->orWhere('email', $email)
                ->first();

            if ($user) {
                // Usuario existente - actualizar google_id si no lo tiene
                if (!$user->google_id) {
                    $user->update([
                        'google_id' => $googleId,
                        'avatar' => $picture
                    ]);
                } else {
                    // Actualizar avatar siempre
                    $user->update(['avatar' => $picture]);
                }
            } else {
                // Crear nuevo usuario
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar' => $picture,
                    'password' => Hash::make(Str::random(24)), // Password aleatorio ya que usamos Google
                ]);

                // Asignar rol de viewer por defecto
                $user->assignRole('viewer');
            }

            $token = $user->createToken('auth-token')->plainTextToken;

            return response()->json([
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'roles' => $user->getRoleNames(),
                ],
                'token' => $token,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al autenticar con Google',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}