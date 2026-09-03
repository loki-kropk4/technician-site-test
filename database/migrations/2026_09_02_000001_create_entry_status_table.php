<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('entry_status', function (Blueprint $table) {
            $table->char('status_id', 1)->primary();
            $table->string('status', 10);
        });

        // Fixed reference data, seeded here (rather than via a separate
        // seeder) so it exists in every environment automatically —
        // including the in-memory SQLite test DB, which runs migrations
        // but not seeders.
        DB::table('entry_status')->insert([
            ['status_id' => '1', 'status' => 'Pending'],
            ['status_id' => '2', 'status' => 'Ongoing'],
            ['status_id' => '3', 'status' => 'Completed'],
            ['status_id' => '4', 'status' => 'Cancelled'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_status');
    }
};
