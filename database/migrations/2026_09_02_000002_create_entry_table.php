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
        Schema::create('entry', function (Blueprint $table) {
            $table->char('entry_id', 8)->primary();
            $table->foreignId('customer_id')->constrained('users');
            $table->string('name_unit', 40);
            $table->text('problem');
            $table->date('entry_date');
            $table->time('entry_time');
            $table->char('status', 1);
            $table->foreign('status')->references('status_id')->on('entry_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry');
    }
};
