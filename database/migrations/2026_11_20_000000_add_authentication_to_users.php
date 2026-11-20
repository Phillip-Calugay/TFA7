<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // These checks also allow retrying an interrupted SQLite migration.
        foreach (['email', 'email_verified_at', 'password', 'remember_token'] as $column) {
            if (! Schema::hasColumn('users', $column)) {
                Schema::table('users', function (Blueprint $table) use ($column) {
                    match ($column) {
                        'email_verified_at' => $table->timestamp($column)->nullable(),
                        'remember_token' => $table->rememberToken(),
                        default => $table->string($column)->nullable(),
                    };
                });
            }
        }
        if (! Schema::hasIndex('users', 'users_auth_email_unique')) {
            Schema::table('users', fn (Blueprint $table) => $table->unique('email', 'users_auth_email_unique'));
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_auth_email_unique');
            $table->dropColumn(['email', 'email_verified_at', 'password', 'remember_token']);
        });
    }
};
