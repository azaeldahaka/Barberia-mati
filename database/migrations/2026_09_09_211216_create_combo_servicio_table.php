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
        Schema::create('combo_servicio', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('combo_id')->constrained('combos')->onDelete('cascade');
            $table->foreignUuid('servicio_id')->constrained('services')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combo_servicio');
    }
};
