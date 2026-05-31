<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class NewUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Add new user accounts with default password: 123456
     *
     * @return void
     */
    public function run()
    {
        $defaultPassword = Hash::make('123456');

        // ============================================
        // TEMPLATE - COPY AND MODIFY AS NEEDED
        // ============================================
        
        /*
        // Example: Developer
        $user = User::firstOrCreate(
            ['email' => 'newemail@example.com'],
            [
                'name' => 'User Full Name',
                'username' => 'username123',
                'password' => $defaultPassword,
                'api_token' => bin2hex(random_bytes(30)),
                'email_verified_at' => now(),
                'active' => true,
                'division_id' => 1, // Optional: Set division (1=IT, 2=Finance, etc)
            ]
        );
        $user->assignRole('developer');
        $this->command->info("✓ Created: {$user->name} ({$user->email}) - Role: developer");
        */

        // ============================================
        // ADD NEW USERS BELOW THIS LINE
        // ============================================

        // Receptionist
        $user = User::firstOrCreate(['email' => 'recepsionist@quty.co.id'], [
            'name' => 'Siska',
            'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 1,
        ]);
        $user->assignRole('receptionist');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // HRD-GA Department
        $user = User::firstOrCreate(['email' => 'warjo@quty.co.id'], [
            'name' => 'Warjo',
            'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 2,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'rheza@quty.co.id'], [
            'name' => 'Rheza',
            'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 2,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'recruitment@quty.co.id'], [
            'name' => 'Recruitment', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 2,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'yophie@quty.co.id'], [
            'name' => 'Yophie', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 2,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // HRD Department
        $user = User::firstOrCreate(['email' => 'tonny@quty.co.id'], [
            'name' => 'Tonny', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 2,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'jhony@quty.co.id'], [
            'name' => 'Jhony', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 2,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'yugi.vriyayu@quty.co.id'], [
            'name' => 'Yugi', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 2,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'jessica@quty.co.id'], [
            'name' => 'Jessica', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 2,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // IT Staff (already exists in system, skip if exists)
        // idol@quty.co.id, ridwan_it@quty.co.id, daniel@quty.co.id

        // Kasir (Cashier)
        $user = User::firstOrCreate(['email' => 'rita.stk@quty.co.id'], [
            'name' => 'Bu Rita', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 3,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'sri.acc@quty.co.id'], [
            'name' => 'Sri', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 3,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Accounting
        $user = User::firstOrCreate(['email' => 'lilik@quty.co.id'], [
            'name' => 'Lilik', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 3,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'eka_novera@quty.co.id'], [
            'name' => 'Eka', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 3,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // EXIM Department
        $user = User::firstOrCreate(['email' => 'dr.sulaeman@quty.co.id'], [
            'name' => 'Rahmat', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 4,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'yuliviana.exp@quty.co.id'], [
            'name' => 'Yuli', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 4,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'lina@quty.co.id'], [
            'name' => 'Lina', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 4,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'alik@quty.co.id'], [
            'name' => 'Aliq', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 4,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'dery@quty.co.id'], [
            'name' => 'Deri', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 4,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'rizky.exim@quty.co.id'], [
            'name' => 'Risky', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 4,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'ila@quty.co.id'], [
            'name' => 'Ila', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 4,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'titi@quty.co.id'], [
            'name' => 'Kartini', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 4,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'sri.utomo@quty.co.id'], [
            'name' => 'Sri Utomo', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 4,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Business Dev
        $user = User::firstOrCreate(['email' => 'adri@quty.co.id'], [
            'name' => 'Adri', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 5,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'Pikki.aziz@quty.co.id'], [
            'name' => 'Pikki', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 5,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'titin.rohaeti@quty.co.id'], [
            'name' => 'Titin', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 5,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Quality & Marketing
        $user = User::firstOrCreate(['email' => 'dika@quty.co.id'], [
            'name' => 'Dika', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 6,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'ilham@quty.co.id'], [
            'name' => 'Ilham', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 6,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'fitri.pratiwi@quty.co.id'], [
            'name' => 'Fitri', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 6,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'winanda@quty.co.id'], [
            'name' => 'Winanda', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 6,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'feti@quty.co.id'], [
            'name' => 'Feti', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 6,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'irma@quty.co.id'], [
            'name' => 'Irma', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 6,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'hapiz@quty.co.id'], [
            'name' => 'Hapiz', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 6,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'windiyan@quty.co.id'], [
            'name' => 'Windiyan', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 6,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Sustainability
        $user = User::firstOrCreate(['email' => 'budhi.irawan@quty.co.id'], [
            'name' => 'Budi', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 7,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'Meta@quty.co.id'], [
            'name' => 'Meta', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 7,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'muh.ihsan@quty.co.id'], [
            'name' => 'Maftuh', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 7,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'salman@quty.co.id'], [
            'name' => 'Salman', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 7,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'dini.cmpl@quty.co.id'], [
            'name' => 'Dini', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 7,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'hasan.cmpl@quty.co.id'], [
            'name' => 'Hasan', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 7,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'ines@quty.co.id'], [
            'name' => 'Ines', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 7,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Logistic
        $user = User::firstOrCreate(['email' => 'riana.logs@quty.co.id'], [
            'name' => 'Riana', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 8,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'metirosmiati@quty.co.id'], [
            'name' => 'Meti', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 8,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'nunung.farida@quty.co.id'], [
            'name' => 'Nunung', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 8,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // PPIC
        $user = User::firstOrCreate(['email' => 'iis.ppic@quty.co.id'], [
            'name' => 'Iis', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 9,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'endah@quty.co.id'], [
            'name' => 'Endah', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 9,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'wina@quty.co.id'], [
            'name' => 'Wina', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 9,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'ugi.wulandari@quty.co.id'], [
            'name' => 'Wulan', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 9,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'sentiani@quty.co.id'], [
            'name' => 'Siti Sentiani', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 9,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Purchasing
        $user = User::firstOrCreate(['email' => 'evie@quty.co.id'], [
            'name' => 'Evi', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 10,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'k.komalasari@quty.co.id'], [
            'name' => 'Kokom', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 10,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'Elmi@quty.co.id'], [
            'name' => 'Elmi', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 10,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'backup.purchase@quty.co.id'], [
            'name' => 'Ulfa', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 10,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'Nurmuniroh@quty.co.id'], [
            'name' => 'Nur', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 10,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Lean
        $user = User::firstOrCreate(['email' => 'yudi.suyudi@quty.co.id'], [
            'name' => 'Yudi', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 11,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'deni.nopianto@quty.co.id'], [
            'name' => 'Deni', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 11,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'hermawan@quty.co.id'], [
            'name' => 'Hermawan', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 11,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'admin-lean3@quty.co.id'], [
            'name' => 'Kanitha', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 11,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'allief@quty.co.id'], [
            'name' => 'Allief', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 11,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'admin.lean@quty.co.id'], [
            'name' => 'Yuli', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 11,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'ilham.eka@quty.co.id'], [
            'name' => 'Ilham', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 11,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // QA
        $user = User::firstOrCreate(['email' => 'bobby@quty.co.id'], [
            'name' => 'Bobby', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 12,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'meta.qa@quty.co.id'], [
            'name' => 'Meta', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 12,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'dwi@quty.co.id'], [
            'name' => 'Dwi', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 12,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // QC
        $user = User::firstOrCreate(['email' => 'siti.maryam@quty.co.id'], [
            'name' => 'Iyam', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 13,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'wiwin.winarti@quty.co.id'], [
            'name' => 'Wiwin', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 13,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Warehouse
        $user = User::firstOrCreate(['email' => 'kusnadi.eri@quty.co.id'], [
            'name' => 'Eri', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 14,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'ira_wh@quty.co.id'], [
            'name' => 'Ira (Label)', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 14,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'yeni_wh@quty.co.id'], [
            'name' => 'Yeni', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 14,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'admin.warehouse2@quty.co.id'], [
            'name' => 'Fitri', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 14,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'adm.warehouse5@quty.co.id'], [
            'name' => 'Nurul', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 14,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'ulfa@quty.co.id'], [
            'name' => 'Ulfa', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 14,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'admin.warehouse3@quty.co.id'], [
            'name' => 'Miftah', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 14,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Cutting Gerber
        $user = User::firstOrCreate(['email' => 'jamal@quty.co.id'], [
            'name' => 'Jamal', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 15,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'riki@quty.co.id'], [
            'name' => 'Riki', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 15,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'adm.cuting@quty.co.id'], [
            'name' => 'Novi', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 15,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'adm.cutting2@quty.co.id'], [
            'name' => 'Lim', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 15,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'admin.cuting1@quty.co.id'], [
            'name' => 'Suryati', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 15,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Cutting Laser
        $user = User::firstOrCreate(['email' => 'adm.embolaser2@quty.co.id'], [
            'name' => 'Ajeng', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 16,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'raihan@quty.co.id'], [
            'name' => 'Raihan', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 16,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Embo
        $user = User::firstOrCreate(['email' => 'm.johan@quty.co.id'], [
            'name' => 'Johanes', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 17,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'tia@quty.co.id'], [
            'name' => 'Tia', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 17,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'adm.embolaser@quty.co.id'], [
            'name' => 'Siti', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 17,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Sewing
        $user = User::firstOrCreate(['email' => 'adm.sewing3@quty.co.id'], [
            'name' => 'Indah', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 18,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'adm.sewing2@quty.co.id'], [
            'name' => 'Trisna SW2', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 18,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'anggih@quty.co.id'], [
            'name' => 'Anggih SW2', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 18,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'adm.sewing@quty.co.id'], [
            'name' => 'Marli SW1', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 18,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'adm.qcsewing@quty.co.id'], [
            'name' => 'Indri', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 18,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Finishing
        $user = User::firstOrCreate(['email' => 'adm.finishing@quty.co.id'], [
            'name' => 'Lilis', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 19,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'adm.finishing2@quty.co.id'], [
            'name' => 'Fani', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 19,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Packing
        $user = User::firstOrCreate(['email' => 'yoga@quty.co.id'], [
            'name' => 'Yoga', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 20,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'adm.packing@quty.co.id'], [
            'name' => 'Pipit', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 20,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Finishgood
        $user = User::firstOrCreate(['email' => 'finishgood@quty.co.id'], [
            'name' => 'Yeni', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 21,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'adm.finishgood@quty.co.id'], [
            'name' => 'Mia', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 21,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Mekanik
        $user = User::firstOrCreate(['email' => 'adm.mekanik@quty.co.id'], [
            'name' => 'Lia', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 22,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // Korea Team
        $user = User::firstOrCreate(['email' => 'sskwon@quty.co.id'], [
            'name' => 'Mr. Kwon Sam Soo', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 23,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'kimyc@quty.co.id'], [
            'name' => 'Mr. Kim', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 23,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'matthewkim@quty.co.id'], [
            'name' => 'Mr. Matthew', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 23,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'gracekim@quty.co.id'], [
            'name' => 'Mrs. Grace', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 23,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'yc.lee@quty.co.id'], [
            'name' => 'Mr. Lee', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 23,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'ci.hwan@quty.co.id'], [
            'name' => 'Mrs. Cho', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 23,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // R & D
        $user = User::firstOrCreate(['email' => 'achmad@quty.co.id'], [
            'name' => 'Achmad', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 24,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'ninik@quty.co.id'], [
            'name' => 'Ninik', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 24,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // KTM (Marketing)
        $user = User::firstOrCreate(['email' => 'rizki_mkt@quty.co.id'], [
            'name' => 'Rizki', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 25,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'qowi@quty.co.id'], [
            'name' => 'Qowi', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 25,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'tifani@quty.co.id'], [
            'name' => 'Tifani', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 25,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'tiara@quty.co.id'], [
            'name' => 'Tiara', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 25,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        $user = User::firstOrCreate(['email' => 'devi@quty.co.id'], [
            'name' => 'Devi', 'password' => $defaultPassword,
            'api_token' => bin2hex(random_bytes(30)),
            'division_id' => 25,
        ]);
        $user->assignRole('user');
        $this->command->info("✓ {$user->name} ({$user->email})");

        // ============================================
        // ADD MORE USERS BELOW (copy template above)
        // ============================================

        $this->command->info("\n✅ All new users created successfully!");
        $this->command->info("📝 Default password for all users: 123456");
        $this->command->warn("⚠️  Please ask users to change their password after first login!");
    }
}
