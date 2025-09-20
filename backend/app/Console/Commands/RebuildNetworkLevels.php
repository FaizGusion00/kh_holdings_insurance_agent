<?php

namespace App\Console\Commands;

use App\Services\NetworkLevelService;
use Illuminate\Console\Command;

class RebuildNetworkLevels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'network:rebuild {--force : Force rebuild even if data exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild the network levels table with accurate MLM hierarchy';

    /**
     * Execute the console command.
     */
    public function handle(NetworkLevelService $networkLevelService)
    {
        $this->info('Starting network levels rebuild...');
        
        if (!$this->option('force') && \App\Models\NetworkLevel::count() > 0) {
            if (!$this->confirm('Network levels table already has data. Do you want to rebuild it?')) {
                $this->info('Rebuild cancelled.');
                return;
            }
        }
        
        $networkLevelService->rebuildNetworkLevels();
        
        $this->info('Network levels rebuild completed!');
        
        // Show summary
        $totalLevels = \App\Models\NetworkLevel::count();
        $this->info("Total network level records: {$totalLevels}");
        
        // Show level breakdown
        $breakdown = \App\Models\NetworkLevel::selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->orderBy('level')
            ->get();
            
        $this->table(['Level', 'Count'], $breakdown->map(function($item) {
            return [$item->level, $item->count];
        }));
    }
}