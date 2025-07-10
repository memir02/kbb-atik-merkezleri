<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SearchService;

class OptimizeSearchCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:optimize';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize search database indexes for better performance';

    /**
     * Execute the console command.
     */
    public function handle(SearchService $searchService): int
    {
        $this->info('Optimizing search database indexes...');
        
        if ($searchService->optimizeDatabase()) {
            $this->info('✅ Search optimization completed successfully!');
            $this->line('Database indexes have been created for better search performance.');
            return Command::SUCCESS;
        } else {
            $this->error('❌ Search optimization failed!');
            $this->line('Check the logs for more details.');
            return Command::FAILURE;
        }
    }
}
