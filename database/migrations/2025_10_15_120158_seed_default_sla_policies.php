<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get ticket priorities
        $priorities = DB::table('tickets_priorities')->get()->keyBy('name');
        
        $policies = [
            [
                'name' => 'Urgent Priority SLA',
                'description' => 'SLA policy for urgent priority tickets requiring immediate attention',
                'response_time' => 60,        // 1 hour first response
                'resolution_time' => 240,     // 4 hours resolution
                'priority_id' => $priorities['Urgent']->id ?? 1,
                'business_hours_only' => 0,   // 24/7
                'escalation_time' => 120,     // Escalate after 2 hours
                'escalate_to_user_id' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'High Priority SLA',
                'description' => 'SLA policy for high priority tickets',
                'response_time' => 240,       // 4 hours first response
                'resolution_time' => 1440,    // 24 hours (1 day) resolution
                'priority_id' => $priorities['High']->id ?? 2,
                'business_hours_only' => 1,   // Business hours only
                'escalation_time' => 720,     // Escalate after 12 hours
                'escalate_to_user_id' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Normal Priority SLA',
                'description' => 'SLA policy for normal/medium priority tickets',
                'response_time' => 1440,      // 24 hours (1 day) first response
                'resolution_time' => 4320,    // 72 hours (3 days) resolution
                'priority_id' => $priorities['Normal']->id ?? 3,
                'business_hours_only' => 1,   // Business hours only
                'escalation_time' => null,
                'escalate_to_user_id' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Low Priority SLA',
                'description' => 'SLA policy for low priority tickets',
                'response_time' => 2880,      // 48 hours (2 days) first response
                'resolution_time' => 10080,   // 168 hours (1 week) resolution
                'priority_id' => $priorities['Low']->id ?? 4,
                'business_hours_only' => 1,   // Business hours only
                'escalation_time' => null,
                'escalate_to_user_id' => null,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        foreach ($policies as $policy) {
            // Check if policy already exists
            $exists = DB::table('sla_policies')
                       ->where('priority_id', $policy['priority_id'])
                       ->exists();
            
            if (!$exists) {
                DB::table('sla_policies')->insert($policy);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete seeded SLA policies
        DB::table('sla_policies')
          ->whereIn('name', [
              'Urgent Priority SLA',
              'High Priority SLA',
              'Normal Priority SLA',
              'Low Priority SLA'
          ])
          ->delete();
    }
};
