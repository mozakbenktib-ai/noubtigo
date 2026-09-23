<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class MakeSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rbac:make-super-admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Promote a user to System Admin (Global Super Admin)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        $user->update([
            'is_system_admin' => true,
            'company_id' => null, // Global user
        ]);

        $this->info("User {$user->full_name} ({$email}) is now a System Admin.");
        return 0;
    }
}
