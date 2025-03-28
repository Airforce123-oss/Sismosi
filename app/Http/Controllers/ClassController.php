<?php

namespace App\Http\Controllers;

use App\Models\Classes;
use App\Http\Requests\StoreClassesRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        // Eager load the waliKelas relationship
        $classesQuery = Classes::with('waliKelas'); // Mengambil kelas beserta wali kelas
    
        // Pagination
        $classes_for_student = $classesQuery->paginate(5)->appends($request->only('search'));
    
        // Format data untuk respons Inertia
        return Inertia::render('Kelas/index', [
            'classes_for_student' => [
                'data' => $classes_for_student->items(), // Mengambil koleksi item dari paginator
                'meta' => [
                    'total' => $classes_for_student->total(),
                    'per_page' => $classes_for_student->perPage(),
                    'current_page' => $classes_for_student->currentPage(),
                    'last_page' => $classes_for_student->lastPage(),
                    'links' => array_merge(
                        [[
                            'url' => $classes_for_student->url(1),
                            'label' => 'First',
                            'active' => $classes_for_student->currentPage() == 1,
                        ]],
                        collect(range(1, $classes_for_student->lastPage()))->map(function ($page) use ($classes_for_student) {
                            return [
                                'url' => $classes_for_student->url($page),
                                'label' => $page,
                                'active' => $classes_for_student->currentPage() == $page,
                            ];
                        })->toArray(),
                        [[
                            'url' => $classes_for_student->previousPageUrl(),
                            'label' => 'Previous',
                            'active' => $classes_for_student->previousPageUrl() !== null,
                        ],
                        [
                            'url' => $classes_for_student->nextPageUrl(),
                            'label' => 'Next',
                            'active' => $classes_for_student->nextPageUrl() !== null,
                        ],
                        [
                            'url' => $classes_for_student->url($classes_for_student->lastPage()),
                            'label' => 'Last',
                            'active' => $classes_for_student->currentPage() == $classes_for_student->lastPage(),
                        ]]
                    ),
                ],
            ],
        ]);
        
        
    }
    
    
    public function indexApi(Request $request)
    {
        $classesQuery = Classes::query();
    
        // Terapkan filter pencarian jika ada
        $this->applySearch($classesQuery, $request->search);
    
        // Urutkan berdasarkan ID kelas
        $classesQuery->orderBy('id');
    
        // Pagination, dengan jumlah per halaman 20
        $classes = $classesQuery->paginate(10)->appends($request->only('search'));
    
        return response()->json($classes);
    }
    

    public function getClasses()
   {
       $classes = Classes::all();
       return response()->json($classes);
   }

    public function create()
    {
        return inertia('Kelas/create');
    }

    public function store(StoreClassesRequest $request)
    {
        // Log the incoming request data
        Log::info('Incoming request data:', $request->all());
    
        // Validate the request data
        $validatedData = $request->validated();
    
        // Log the validated data
        Log::info('Validated data:', $validatedData);
    
        try {
            // Create a new class entry
            Classes::create($validatedData);
    
            // Log success message
            Log::info('Class created successfully:', $validatedData);
    
            // Redirect to the index route
            return redirect()->route('kelas.index');
        } catch (\Exception $e) {
            // Log any exceptions that occur
            Log::error('Error creating class:', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    
            // Redirect back with error message
            return redirect()->back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan data kelas.'])->withInput();
        }
    }
    

    public function show($classId)
    {
        $classForStudent = Classes::findOrFail($classId);
        return inertia('Kelas/show', [
            'classForStudent' => $classForStudent
        ]);
    }

    public function edit($classId)
    {
        // Mengambil data kelas berdasarkan classId
        $classForStudent = Classes::findOrFail($classId);
    
        // Mengembalikan tampilan dengan data kelas
        return inertia('Kelas/edit', [
            'classForStudent' => $classForStudent
        ]);
    }
    
    public function update(StoreClassesRequest $request, $classId)
    {
        $classForStudent = Classes::findOrFail($classId);
        $classForStudent->update($request->validated());
        return redirect()->route('kelas.index');
    }

    public function destroy($classId)
    {
        $classForStudent = Classes::findOrFail($classId);
        $classForStudent->delete();
        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }
}
