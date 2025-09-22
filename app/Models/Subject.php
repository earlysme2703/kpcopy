<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
      public function users()
    {
        return $this->hasMany(User::class, 'subject_id');
    }
    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id');
    }

}

