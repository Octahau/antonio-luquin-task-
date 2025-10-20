<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Task::with('user');
        
        // Admin puede ver todas las tareas, otros solo las suyas
        if (!$user->hasRole('admin')) {
            $query->where('user_id', $user->id);
        }
        
        // Filtro por status si se proporciona
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $tasks = $query->latest()->get();
        
        return response()->json($tasks);
    }

    public function store(Request $request): JsonResponse
    {
        // Solo admin y editor pueden crear tareas
        if (!$request->user()->hasAnyRole(['admin', 'editor'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'in:pending,in_progress,completed',
            'due_date' => 'nullable|date|after:today',
        ]);
        
        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status ?? 'pending',
            'due_date' => $request->due_date,
            'user_id' => $request->user()->id,
        ]);
        
        return response()->json($task->load('user'), 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $task = Task::with('user')->findOrFail($id);
        
        // Admin puede ver cualquier tarea, otros solo las suyas
        if (!$user->hasRole('admin') && $task->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        return response()->json($task);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        // Solo admin y editor pueden actualizar
        if (!$request->user()->hasAnyRole(['admin', 'editor'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        $user = $request->user();
        $task = Task::findOrFail($id);
        
        // Solo admin puede editar tareas de otros usuarios
        if (!$user->hasRole('admin') && $task->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);
        
        $task->update($request->only(['title', 'description', 'status', 'due_date']));
        
        return response()->json($task->load('user'));
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        // Solo admin y editor pueden eliminar
        if (!$request->user()->hasAnyRole(['admin', 'editor'])) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        $user = $request->user();
        $task = Task::findOrFail($id);
        
        // Solo admin puede eliminar tareas de otros usuarios
        if (!$user->hasRole('admin') && $task->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }
        
        $task->delete();
        
        return response()->json(['message' => 'Tarea eliminada correctamente']);
    }

    // Eliminamos los métodos create() y edit() que no son necesarios para API
}