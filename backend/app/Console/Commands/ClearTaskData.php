<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Carbon\Carbon;

class ClearTaskData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tasks:clear-data {--force : Force the operation to run without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all task data from the current year to prepare for re-seeding';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete all tasks from the current year. Do you want to continue?')) {
                $this->info('Operation cancelled.');
                return;
            }
        }

        $currentYear = now()->year;
        $deletedCount = Task::whereYear('created_at', $currentYear)->count();
        
        if ($deletedCount === 0) {
            $this->info('No tasks found for the current year.');
            return;
        }

        Task::whereYear('created_at', $currentYear)->delete();
        
        $this->info("Deleted {$deletedCount} tasks from {$currentYear}. You can now run 'php artisan tasks:seed-data' to create new sample data.");
        
        return 0;
    }
}
