<?php

use App\Models\Barberia;
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
        Schema::create('item_catalogos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Barberia::class)->constrained();
            $table->enum('tipo', ['servicio', 'combo']);
            $table->string('nombre');
            $table->decimal('precio', 8, 2);
            $table->integer('duracion_minutos');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_catalogos');
    }
};
