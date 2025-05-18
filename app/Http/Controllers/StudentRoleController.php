<?php  
  
namespace App\Http\Controllers;  
  
use Illuminate\Http\Request;  
use App\Models\Student;  
use App\Models\JadwalMataPelajaran;
use App\Models\Teacher;
use App\Models\Attendance;
use Inertia\Inertia;  
use Carbon\Carbon;

  
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
    public function melihatDataAbsensiSiswa(Request $request)
    {
        $student = auth()->user()->student->load(['class', 'mapel']); // load relasi class & mapel
        
        // Ambil bulan & tahun dari request (atau default ke sekarang)
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));
    
        // Ambil data absensi untuk siswa ini
        $absensi = Attendance::where('student_id', $student->id)
            ->whereMonth('tanggal_kehadiran', $month)
            ->whereYear('tanggal_kehadiran', $year)
            ->orderBy('tanggal_kehadiran')
            ->pluck('status_kehadiran', 'tanggal_kehadiran');
        
        return Inertia::render('Students/melihatDataAbsensiSiswa', [
            'attendanceData' => $absensi,
            'month' => $month,
            'year' => $year,
            'student' => $student,
            'subject' => $student->mapel, // Ambil mapel dari relasi
        ]);
    }
    
    
    
    public function melihatJadwalPelajaran()
    {
        $schedule = JadwalMataPelajaran::with(['mapel', 'kelas'])
            ->get()
            ->groupBy('jam_ke')
            ->map(function ($group) {
                $jamKe = $group[0]->jam_ke;
                $jam   = $group[0]->jam;
    
                $data = [
                    'jam_ke' => $jamKe,
                    'jam'    => $jam,
                    'jadwal' => [],
                ];
    
                foreach ($group as $jadwal) {
                    $hari = strtolower($jadwal->hari);
                    $data['jadwal'][$hari] = [
                        'mapel'    => $jadwal->mapel->mapel ?? '-',
                        'kelas'    => $jadwal->kelas->name ?? '-',
                        'kelas_id' => $jadwal->kelas_id,
                        // wali_kelas akan ditambahkan nanti
                    ];
                }
    
                return $data;
            })
            ->values();
    
        $teachers = Teacher::all();
    
        $schedule = $schedule->transform(function ($slot) use ($teachers) {
            foreach ($slot['jadwal'] as $hari => &$jadwalPerHari) {
                $waliKelas = $teachers->firstWhere('class_id', $jadwalPerHari['kelas_id']);
                $jadwalPerHari['wali_kelas'] = $waliKelas ? $waliKelas->name : 'Tidak ada wali kelas';
            }
    
            // Kembalikan slot yang sudah dimodifikasi
            return $slot;
        });
    
        $kelasList = \App\Models\Classes::pluck('name', 'id')->toArray();
        //dd($schedule);x
        return Inertia::render('Students/melihatJadwalPelajaran', [
            'schedule'    => $schedule,
            'kelasList'   => $kelasList,
            'wali_kelas'  => $teachers,
        ]);
    }
    
}  
