<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use Illuminate\Support\Facades\File;

class BackupUsers extends Seeder
{
    public function run()
    {
        $users = User::with(['roles', 'division'])->get();
        $backup = $users->map(function ($u) {
            return [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'password' => $u->password,
                'phone' => $u->phone,
                'division_id' => $u->division_id,
                'is_active' => $u->is_active ? 1 : 0,
                'profile_picture' => $u->profile_picture,
                'created_at' => $u->created_at->toDateTimeString(),
                'updated_at' => $u->updated_at->toDateTimeString(),
                'roles' => $u->roles->pluck('name')->toArray(),
            ];
        });

        $path = storage_path('app/user_backup_' . date('Y-m-d_His') . '.json');
        File::put($path, json_encode($backup->toArray(), JSON_PRETTY_PRINT));
        $this->command->info("✓ Backed up {$backup->count()} users to {$path}");
    }
}
