<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description'
    ];

    public function questions()
    {
        return $this->hasMany(\App\Models\Question::class);
    }
}