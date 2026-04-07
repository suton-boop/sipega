<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\PerformanceService;

class CalculatePerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sipega:calculate-performance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate weighted performance score for all users';

    /**
     * Execute the console command.
     */
    public function handle(PerformanceService $service)
    {
        $this->info('Starting performance calculation...');
        $users = User::all();
        $bar = $this->output->createProgressBar(count($users));

        $bar->start();
        foreach ($users as $user) {
            $service->updateScore($user);
            $bar->advance();
        }
        $bar->finish();

        $this->newLine();
        $this->info('Performance calculation completed successfully!');
    }
}
