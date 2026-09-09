<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ItemCatalogo extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

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
