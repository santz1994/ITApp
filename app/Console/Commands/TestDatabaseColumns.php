<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Location;
use App\AssetType;
use App\TicketsStatus;
use App\TicketsPriority;
use App\TicketsType;

class TestDatabaseColumns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:database-columns';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test database column fixes for SQL errors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Database Column Fix Validation Test ===');
        $this->newLine();

        try {
            // Test Location model
            $this->info('Testing Location model...');
            $locations = Location::select('id', 'location_name')->limit(3)->get();
            $this->info("✅ Location query successful - found {$locations->count()} records");
            
            if ($locations->count() > 0) {
                $firstLocation = $locations->first();
                $this->line("   - location_name: {$firstLocation->location_name}");
                $this->line("   - name accessor: {$firstLocation->name}");
            }
            $this->newLine();

            // Test AssetType model
            $this->info('Testing AssetType model...');
            $assetTypes = AssetType::select('id', 'type_name')->limit(3)->get();
            $this->info("✅ AssetType query successful - found {$assetTypes->count()} records");
            
            if ($assetTypes->count() > 0) {
                $firstType = $assetTypes->first();
                $this->line("   - type_name: {$firstType->type_name}");
                $this->line("   - name accessor: {$firstType->name}");
            }
            $this->newLine();

            // Test TicketsStatus model
            $this->info('Testing TicketsStatus model...');
            $ticketsStatuses = TicketsStatus::select('id', 'status')->limit(3)->get();
            $this->info("✅ TicketsStatus query successful - found {$ticketsStatuses->count()} records");
            
            if ($ticketsStatuses->count() > 0) {
                $firstStatus = $ticketsStatuses->first();
                $this->line("   - status: {$firstStatus->status}");
                $this->line("   - name accessor: {$firstStatus->name}");
            }
            $this->newLine();

            // Test TicketsPriority model
            $this->info('Testing TicketsPriority model...');
            $ticketsPriorities = TicketsPriority::select('id', 'priority')->limit(3)->get();
            $this->info("✅ TicketsPriority query successful - found {$ticketsPriorities->count()} records");
            
            if ($ticketsPriorities->count() > 0) {
                $firstPriority = $ticketsPriorities->first();
                $this->line("   - priority: {$firstPriority->priority}");
                $this->line("   - name accessor: {$firstPriority->name}");
            }
            $this->newLine();

            // Test TicketsType model
            $this->info('Testing TicketsType model...');
            $ticketsTypes = TicketsType::select('id', 'type')->limit(3)->get();
            $this->info("✅ TicketsType query successful - found {$ticketsTypes->count()} records");
            
            if ($ticketsTypes->count() > 0) {
                $firstTicketType = $ticketsTypes->first();
                $this->line("   - type: {$firstTicketType->type}");
                $this->line("   - name accessor: {$firstTicketType->name}");
            }
            $this->newLine();

            $this->info('=== All Database Column Tests Passed! ===');
            $this->info('✅ All SQL column errors should now be resolved.');

        } catch (\Exception $e) {
            $this->error('❌ Error during testing: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            return 1;
        }

        return 0;
    }
}
