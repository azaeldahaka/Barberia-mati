<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barberia extends Model
{
    use HasFactory;

    protected $fillable = ['nombre'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
