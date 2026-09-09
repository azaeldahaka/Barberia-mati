<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Combo extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'item_catalogo_id',
    ];

    public function itemCatalogo(): BelongsTo
    {
        return $this->belongsTo(ItemCatalogo::class);
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'combo_servicio', 'combo_id', 'servicio_id')->withTimestamps();
    }
}
