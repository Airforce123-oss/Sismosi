<?php  
  
namespace App\Http\Controllers;  
  
use Illuminate\Http\Request;  
use App\Models\Student;  
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
        return Inertia::render('Students/melihatJadwalPelajaran'); // Menggunakan huruf kecil  
    }  
    
  
 
}  
