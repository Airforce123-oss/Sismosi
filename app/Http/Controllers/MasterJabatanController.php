<?php

namespace App\Http\Controllers;

use App\Models\MasterJabatan;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Http\Resources\MasterJabatanResource;
use Illuminate\Support\Facades\Log; 
class MasterJabatanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function indexMasterJabatan(Request $request)
    {
        $user = auth()->user();

    if (!$user) {
        return redirect()->route('login');
    }

    $role = $user->roles->first()?->name ?? 'guest';

    $data = MasterJabatan::all();

    return Inertia::render('MasterJabatan/index', [
        'jabatan'   => MasterJabatanResource::collection($data),
        'role_type' => $role,
        'auth'      => ['user' => $user],
    ]);

    }

    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(MasterJabatan $masterJabatan)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MasterJabatan $masterJabatan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MasterJabatan $masterJabatan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MasterJabatan $masterJabatan)
    {
        //
    }
}
