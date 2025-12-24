<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $fillable = ['name', 'is_active'];

    public function studentClasses()
    {
        return $this->hasMany(StudentClass::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function classes()
    {
        return $this->belongsToMany(ClassModel::class, 'class_academic_year')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }
}
