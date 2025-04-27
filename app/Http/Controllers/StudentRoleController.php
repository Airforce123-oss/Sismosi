<?php  
  
namespace App\Http\Controllers;  
  
use Illuminate\Http\Request;  
use App\Models\Student;  
use App\Models\JadwalMataPelajaran;

use Inertia\Inertia;  

  
class StudentRoleController extends Controller  
{ 
    public function index()  
    {  
        $students = Student::all();  
        return Inertia::render('Students/Index', [  
            'students' => $students,  
        ]);  
    }  
  
    public function create()  
    {  
        return Inertia::render('Students/Create');  
    }  
  

    /*
        public function store(Request $request)  
    {  
        $validatedData = $request->validate([  
            'name' => 'required|string|max:255',  
            'description' => 'nullable|string',  
        ]);  
  
        Student::create($validatedData);  
  
        return redirect()->route('student_roles.index')->with('success', 'Student role created successfully.');  
    }  
  
    */

    public function show(Student $student)  
    {  
        return Inertia::render('Students/Show', [  
            'student' => $student,  
        ]);  
    }  

    public function edit(Student $student)  
    {  
        return Inertia::render('Students/Edit', [  
            'student' => $student,  
        ]);  
    }  


     /*
         public function update(Request $request, Student $student)  
    {  
        $validatedData = $request->validate([  
            'name' => 'required|string|max:255',  
            'description' => 'nullable|string',  
        ]);  
  
        $student->update($validatedData);  
  
        return redirect()->route('student_roles.index')->with('success', 'Student role updated successfully.');  
    }  
     */

    public function destroy(Student $student)  
    {  
        $student->delete();  
  
        return redirect()->route('student_roles.index')->with('success', 'Student role deleted successfully.');  
    }  
  
    public function melihatTugas()  
    {  
        return Inertia::render('Students/melihatTugasSiswa'); // Pastikan nama file sesuai  
    }  

    public function melihatDataAbsensiSiswa()    
    {    
        return Inertia::render('Students/melihatDataAbsensiSiswa'); // Menggunakan huruf kecil  
    }  
    public function melihatJadwalPelajaran()
    {
        // 1. Ambil semua jadwal, eager-load relasi mapel & kelas
        $schedule = JadwalMataPelajaran::with(['mapel', 'kelas'])
            ->get()
            ->groupBy('jam_ke')
            ->map(function ($group) {
                $jamKe = $group[0]->jam_ke;
                $jam   = $group[0]->jam; // kolom 'jam' di tabel jadwal_mata_pelajaran
    
                // Inisialisasi struktur per jam_ke
                $data = [
                    'jam_ke' => $jamKe,
                    'jam'    => $jam,
                    'jadwal' => [],        // akan diisi per hari
                ];
    
                // Isi jadwal per hari
                foreach ($group as $jadwal) {
                    $hari = strtolower($jadwal->hari);
                    $data['jadwal'][$hari] = [
                        'mapel'   => $jadwal->mapel->mapel ?? '-',   
                        'kelas'   => $jadwal->kelas->name  ?? '-',     
                        'kelas_id' => $jadwal->kelas_id, // tambahkan kelas_id
                    ];
                }
    
                return $data;
            })
            ->values(); // reset key numeric
    
        // 2. Ambil daftar kelas untuk dropdown: [id => name]
        $kelasList = \App\Models\Classes::pluck('name', 'id')->toArray();
    
        // 3. Kirim ke Inertia
        return Inertia::render('Students/melihatJadwalPelajaran', [
            'schedule'  => $schedule,
            'kelasList' => $kelasList,
        ]);
    }
    
    
    
}  
