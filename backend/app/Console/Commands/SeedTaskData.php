<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Database\Seeders\TaskSeeder;
use App\Models\Task;
use Carbon\Carbon;

class SeedTaskData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:seed-data {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with sample task data distributed across different months of the current year';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will add sample task data to your database. Do you want to continue?')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $this->info('Seeding task data...');
        
        try {
            $seeder = new TaskSeeder();
            $seeder->run();
            
            $currentYear = now()->year;
            $this->info("Task data seeded successfully! Created tasks distributed across {$currentYear}.");
            
            // Mostrar estadísticas por mes
            $this->info("\nEstadísticas de tareas completadas por mes:");
            for ($month = 1; $month <= 12; $month++) {
                $completedCount = Task::whereYear('created_at', $currentYear)
                    ->whereMonth('created_at', $month)
                    ->where('status', 'completed')
                    ->count();
                
                $monthName = Carbon::create($currentYear, $month, 1)->locale('es')->monthName;
                $this->info("$monthName: $completedCount tareas completadas");
            }
            
            $this->info("\nYou can now view the admin dashboard to see the bar chart!");
        } catch (\Exception $e) {
            $this->error('Error seeding task data: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
