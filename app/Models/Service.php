<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'item_catalogo_id',
        'cuenta_para_fidelizacion',
    ];

    protected $casts = [
        'cuenta_para_fidelizacion' => 'boolean',
    ];

    public function itemCatalogo(): BelongsTo
    {
        return $this->belongsTo(ItemCatalogo::class);
    }
}
