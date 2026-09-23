<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('company_id');
            $table->string('last_name')->nullable()->after('first_name');
        });

        // Migrate existing names to first/last name
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            if ($user->name) {
                $parts = explode(' ', $user->name, 2);
                DB::table('users')->where('id', $user->id)->update([
                    'first_name' => $parts[0] ?? 'User',
                    'last_name' => $parts[1] ?? '',
                ]);
            }
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->string('first_name')->nullable(false)->change();
            $table->string('last_name')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->after('company_id');
        });

        $users = DB::table('users')->get();
        foreach ($users as $user) {
            DB::table('users')->where('id', $user->id)->update([
                'name' => trim($user->first_name . ' ' . $user->last_name),
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
