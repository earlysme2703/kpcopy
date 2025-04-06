<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassModel extends Model
{
    use HasFactory;

    protected $table = 'classes'; // Pastikan sesuai dengan nama tabel di database

    protected $fillable = ['name'];

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function waliKelas()
    {
        return $this->hasOne(User::class, 'class_id')->where('role_id', 2);
    }
}
