<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCatalogo extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'barberia_id',
        'tipo',
        'nombre',
        'precio',
        'duracion_minutos',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'duracion_minutos' => 'integer',
    ];

    public function barberia(): BelongsTo
    {
        return $this->belongsTo(Barberia::class);
    }
}
