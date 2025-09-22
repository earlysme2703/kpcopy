<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $table = 'teachers';

    protected $fillable = [
        'user_id',
        'nip',
        'nuptk',
        'nama_lengkap',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'contact_email',
        'status_kerja',
        'class_id',   // wali kelas (opsional)
        'subject_id', // guru mapel (opsional, khusus Agama/PJOK)
    ];

    /* RELATIONS */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function class()
    {
        // pakai ClassModel yang sudah kamu punya
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /* HELPERS */
    public function isHomeroom(): bool
    {
        return !is_null($this->class_id);
    }

    public function isSubjectTeacher(): bool
    {
        return !is_null($this->subject_id);
    }
}
