<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;
    protected $fillable=['user_id', 'course_id', 'issued_date', 'certificate_code'];

    public function user()
{
        return $this->belongsTo(User::class);
}

    public function course()
{
         return $this->belongsTo(Course::class);
}

}