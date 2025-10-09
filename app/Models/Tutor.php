<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tutor extends Model
{
    use HasFactory;
    protected $fillable= ['name', 'signature'];

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
