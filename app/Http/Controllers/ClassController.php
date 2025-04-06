<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    public function index()
    {
        $classes = ClassModel::withcount('students')
            ->with(['waliKelas' => function ($query) {
                $query->where('role_id', '2');
            }])
            ->get();

        return view('classes.index',  compact('classes'));
    }

    public function create()
    {
        return view('classes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:classes,name',
        ]);
        ClassModel::create([
            'name' => $request->name,
        ]);
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit($id)
    {
        // Logic to show the form for editing an existing class
        // return view('classes.edit', compact('id'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:classes,name,' . $id,
        ]);
    
        $kelas = ClassModel::findOrFail($id);
        $kelas->update([
            'name' => $request->name,
        ]);
    
        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }
    

    public function destroy($id)
    {
        // Find the class by ID and delete it
        $class = ClassModel::findOrFail($id);
        $class->delete();

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }

    public function show($id)
    {
    $kelas = ClassModel::with(['students', 'waliKelas'])->findOrFail($id);
    return view('classes.show', compact('kelas'));
}
}
