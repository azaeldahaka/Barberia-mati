<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barberia extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['nombre', 'horario_apertura', 'horario_cierre'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
