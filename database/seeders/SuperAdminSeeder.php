<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\AdminPermission;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    /**
     * Email akun super admin yang dikelola seeder ini.
     */
    private const SUPER_ADMIN_EMAIL = 'admin@mersiflab.com';

    public function run(): void
    {
        // Pengecekan dilakukan berdasarkan email akun ini, bukan "apakah ada
        // user ber-role admin". Pengecekan lama membuat seeder berhenti begitu
        // ada admin lain di database (mis. admin@example.com dari
        // DatabaseSeeder), sehingga akun super admin tidak pernah terbuat.
        $superAdmin = User::where('email', self::SUPER_ADMIN_EMAIL)->first();

        if ($superAdmin) {
            $this->command->info('Super admin already exists: ' . $superAdmin->email);
        } else {
            // Create super admin
            $superAdmin = User::create([
                'name' => 'Super Admin',
                'email' => self::SUPER_ADMIN_EMAIL,
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_active' => true,
                'is_banned' => false,
                // Login admin tidak memakai gate verifikasi email, tapi kolom
                // ini diisi agar akun juga valid bila dipakai di /login.
                'email_verified_at' => now(),
            ]);

            $superAdmin->logActivity('admin_created', "Super admin account created: {$superAdmin->name} ({$superAdmin->email})");
        }

        // Grant all permissions to super admin.
        // firstOrCreate: aman dijalankan ulang, dan melengkapi permission baru
        // yang ditambahkan belakangan tanpa menduplikasi yang sudah ada.
        $permissions = AdminPermission::getAvailablePermissions();
        $grantedBy = $superAdmin->id; // Self-granted

        foreach ($permissions as $permission => $label) {
            AdminPermission::firstOrCreate(
                [
                    'user_id' => $superAdmin->id,
                    'permission' => $permission,
                ],
                [
                    'granted' => true,
                    'granted_by' => $grantedBy,
                ]
            );
        }

        $this->command->info('Super admin siap digunakan!');
        $this->command->info('Email: ' . self::SUPER_ADMIN_EMAIL);
        $this->command->info('Password: admin123');
        $this->command->warn('Please change the password after first login.');
    }
}
