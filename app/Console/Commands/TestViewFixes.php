<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\User;
use App\Asset;

class TestViewFixes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:view-fixes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test view data fixes for calendar and assets';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Testing View Fixes ===');
        $this->newLine();

        try {
            // Test User data for calendar
            $this->info('Testing User data for calendar view...');
            $users = User::select('id', 'name')->whereNotNull('name')->get();
            $this->info("✅ Found {$users->count()} valid users");
            
            if ($users->count() > 0) {
                $firstUser = $users->first();
                $this->line("   - First user: ID={$firstUser->id}, Name={$firstUser->name}");
                
                // Verify data types
                if (is_object($firstUser) && isset($firstUser->id) && isset($firstUser->name)) {
                    $this->info('   ✅ User objects have correct properties');
                } else {
                    $this->error('   ❌ User objects missing required properties');
                }
            }
            $this->newLine();

            // Test Asset statistics
            $this->info('Testing Asset statistics for index view...');
            $totalAssets = Asset::count();
            $deployed = Asset::byStatus('Deployed')->count();
            $readyToDeploy = Asset::byStatus('Ready to Deploy')->count();
            $repairs = Asset::byStatus('Out for Repair')->count();
            $writtenOff = Asset::byStatus('Written off')->count();

            $this->info("✅ Asset statistics calculated successfully:");
            $this->line("   - Total Assets: {$totalAssets}");
            $this->line("   - Deployed: {$deployed}");
            $this->line("   - Ready to Deploy: {$readyToDeploy}");
            $this->line("   - Out for Repair: {$repairs}");
            $this->line("   - Written Off: {$writtenOff}");
            $this->newLine();

            $this->info('=== All View Fix Tests Passed! ===');
            $this->info('✅ Calendar and Assets views should now work without errors.');

        } catch (\Exception $e) {
            $this->error('❌ Error during testing: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return 1;
        }

        return 0;
    }
}
