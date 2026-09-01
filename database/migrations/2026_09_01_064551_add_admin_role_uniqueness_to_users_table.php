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
        // MySQL has no native "unique only when role = admin" partial index,
        // so this generated column resolves to 1 for admins and NULL for
        // everyone else. A unique index on it then blocks a second admin
        // row at the database level, while unique NULLs stay unrestricted
        // for customer/technician rows — a backstop below the UniqueAdminRole
        // validation rule for race conditions.
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('admin_slot')
                ->nullable()
                ->storedAs("CASE WHEN role = 'admin' THEN 1 ELSE NULL END")
                ->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique('users_admin_slot_unique');
            $table->dropColumn('admin_slot');
        });
    }
};
