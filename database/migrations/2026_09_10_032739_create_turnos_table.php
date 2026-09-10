<?php

use App\Models\Barberia;
use App\Models\Client;
use App\Models\ItemCatalogo;
use App\Models\User;
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
        Schema::create('turnos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(Barberia::class)->constrained();
            $table->foreignIdFor(Client::class, 'cliente_id')->constrained('clients');
            $table->foreignIdFor(User::class, 'usuario_id')->constrained('users');
            $table->foreignIdFor(ItemCatalogo::class, 'item_catalogo_id')->constrained('item_catalogos');
            $table->dateTime('fecha_hora_inicio');
            $table->dateTime('fecha_hora_fin');
            $table->string('estado')->default('reservado');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turnos');
    }
};
