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
        Schema::create('entry_picture', function (Blueprint $table) {
            $table->id();
            $table->char('entry_id', 8);
            $table->foreign('entry_id')->references('entry_id')->on('entry')->cascadeOnDelete();
            $table->string('file_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entry_picture');
    }
};
