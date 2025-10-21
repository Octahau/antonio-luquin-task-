<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existen tareas para este año
        $currentYear = Carbon::now()->year;
        $existingTasksCount = Task::whereYear('created_at', $currentYear)->count();
        
        if ($existingTasksCount > 0) {
            // Si ya existen tareas para este año, no hacer nada
            return;
        }

        // Obtener usuarios para asignar tareas
        $users = User::all();
        
        if ($users->isEmpty()) {
            // Si no hay usuarios, crear algunos de prueba
            $admin = User::create([
                'name' => 'Admin Test',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
            ]);
            $admin->assignRole('admin');

            $editor = User::create([
                'name' => 'Editor Test',
                'email' => 'editor@test.com',
                'password' => bcrypt('password'),
            ]);
            $editor->assignRole('editor');

            $viewer = User::create([
                'name' => 'Viewer Test',
                'email' => 'viewer@test.com',
                'password' => bcrypt('password'),
            ]);
            $viewer->assignRole('viewer');

            $users = User::all();
        }

        $tasks = [];

        // Crear tareas para cada mes del año actual
        for ($month = 1; $month <= 12; $month++) {
            $daysInMonth = Carbon::create($currentYear, $month, 1)->daysInMonth;
            
            // Número de tareas por mes (variando para hacer el gráfico más interesante)
            $tasksPerMonth = match ($month) {
                1 => 8,   // Enero
                2 => 12,  // Febrero
                3 => 15,  // Marzo
                4 => 10,  // Abril
                5 => 18,  // Mayo
                6 => 22,  // Junio
                7 => 25,  // Julio
                8 => 20,  // Agosto
                9 => 16,  // Septiembre
                10 => 14, // Octubre
                11 => 11, // Noviembre
                12 => 9,  // Diciembre
            };

            for ($i = 0; $i < $tasksPerMonth; $i++) {
                $day = rand(1, $daysInMonth);
                $createdAt = Carbon::create($currentYear, $month, $day)
                    ->addHours(rand(8, 18))
                    ->addMinutes(rand(0, 59));

                // Distribuir estados: 30% pendientes, 20% en progreso, 50% completadas
                $statusRand = rand(1, 100);
                $status = match (true) {
                    $statusRand <= 30 => 'pending',
                    $statusRand <= 50 => 'in_progress',
                    default => 'completed'
                };

                // Si está completada, agregar fecha de actualización posterior
                $updatedAt = $createdAt;
                if ($status === 'completed') {
                    $updatedAt = $createdAt->copy()->addDays(rand(1, 15));
                }

                $taskTitles = [
                    'Revisar documentación del proyecto',
                    'Implementar nueva funcionalidad',
                    'Corregir bugs reportados',
                    'Optimizar rendimiento',
                    'Revisar código de compañero',
                    'Actualizar dependencias',
                    'Escribir tests unitarios',
                    'Documentar API endpoints',
                    'Configurar CI/CD',
                    'Revisar seguridad',
                    'Refactorizar código legacy',
                    'Integrar nuevos servicios',
                    'Probar en diferentes navegadores',
                    'Optimizar base de datos',
                    'Crear presentación para cliente',
                    'Reunión con stakeholders',
                    'Planificar sprint siguiente',
                    'Analizar métricas de uso',
                    'Investigar nuevas tecnologías',
                    'Capacitar a nuevos miembros'
                ];

                $taskDescriptions = [
                    'Tarea importante que requiere atención detallada',
                    'Implementación de nueva característica solicitada por el cliente',
                    'Corrección de errores encontrados durante las pruebas',
                    'Mejora del rendimiento general del sistema',
                    'Revisión de código según estándares de calidad',
                    'Actualización de librerías y dependencias del proyecto',
                    'Desarrollo de tests para garantizar la calidad',
                    'Documentación técnica para futuras referencias',
                    'Configuración de pipeline de integración continua',
                    'Análisis y mejora de la seguridad del sistema'
                ];

                $dueDate = null;
                if (rand(1, 3) === 1) { // 33% de probabilidad de tener fecha límite
                    $dueDate = $createdAt->copy()->addDays(rand(1, 30));
                }

                $tasks[] = [
                    'title' => $taskTitles[array_rand($taskTitles)],
                    'description' => $taskDescriptions[array_rand($taskDescriptions)],
                    'status' => $status,
                    'user_id' => $users->random()->id,
                    'due_date' => $dueDate,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                ];
            }
        }

        // Insertar todas las tareas de una vez para mejor rendimiento
        Task::insert($tasks);
    }
}
