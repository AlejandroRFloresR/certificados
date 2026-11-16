<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signer extends Model
{
    /** Máximo de firmas que entran en un certificado */
    public const MAX_PER_COURSE = 3;

    protected $fillable = ['name', 'position', 'signature'];

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    public function getSignatureUrlAttribute(): ?string
    {
        return $this->signature ? asset('storage/' . $this->signature) : null;
    }
}
