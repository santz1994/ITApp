<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\ApprovalRule;
use App\ApprovalRuleStep;

class ApprovalRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // ========================================
        // MEETING ROOM BOOKING APPROVAL
        // Flow: Staff → Manager → HRD-GA (optional)
        // ========================================
        $meetingRoomRule = ApprovalRule::create([
            'module' => 'meeting_room',
            'name' => 'Meeting Room - Standard Approval',
            'description' => 'Alur persetujuan standar booking ruang rapat: Manager → HRD-GA',
            'priority' => 10,
            'is_active' => true,
        ]);

        ApprovalRuleStep::create([
            'rule_id' => $meetingRoomRule->id,
            'step_order' => 1,
            'approval_type' => 'role',
            'approver_reference' => 'manager_produksi',
            'is_mandatory' => true,
            'any_of_group' => true,
        ]);

        ApprovalRuleStep::create([
            'rule_id' => $meetingRoomRule->id,
            'step_order' => 2,
            'approval_type' => 'role',
            'approver_reference' => 'manager_hrd_ga',
            'is_mandatory' => false,
            'any_of_group' => true,
        ]);

        // ========================================
        // VEHICLE BOOKING APPROVAL
        // Flow: Manager → HRD-GA
        // ========================================
        $vehicleRule = ApprovalRule::create([
            'module' => 'vehicle',
            'name' => 'Vehicle - Standard Approval',
            'description' => 'Alur persetujuan standar booking kendaraan: Manager → HRD-GA',
            'priority' => 10,
            'is_active' => true,
        ]);

        ApprovalRuleStep::create([
            'rule_id' => $vehicleRule->id,
            'step_order' => 1,
            'approval_type' => 'role',
            'approver_reference' => 'manager_produksi',
            'is_mandatory' => true,
            'any_of_group' => true,
        ]);

        ApprovalRuleStep::create([
            'rule_id' => $vehicleRule->id,
            'step_order' => 2,
            'approval_type' => 'role',
            'approver_reference' => 'manager_hrd_ga',
            'is_mandatory' => true,
            'any_of_group' => true,
        ]);

        // ========================================
        // INVENTORY REQUEST APPROVAL
        // Flow: Manager → HRD-GA (final)
        // ========================================
        $inventoryRule = ApprovalRule::create([
            'module' => 'inventory',
            'name' => 'Inventory - Standard Approval',
            'description' => 'Alur persetujuan standar request ATK/Sparepart: Manager → HRD-GA',
            'priority' => 10,
            'is_active' => true,
        ]);

        ApprovalRuleStep::create([
            'rule_id' => $inventoryRule->id,
            'step_order' => 1,
            'approval_type' => 'role',
            'approver_reference' => 'manager_produksi',
            'is_mandatory' => true,
            'any_of_group' => true,
        ]);

        ApprovalRuleStep::create([
            'rule_id' => $inventoryRule->id,
            'step_order' => 2,
            'approval_type' => 'role',
            'approver_reference' => 'manager_hrd_ga',
            'is_mandatory' => true,
            'any_of_group' => true,
        ]);
    }
}