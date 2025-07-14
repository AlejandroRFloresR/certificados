<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    public function users()
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
    
    protected $fillable = ['title', 'description', 'start_date', 'end_date'];
}