<?php

namespace App\Http\Controllers;
use Inertia\Inertia;

use Illuminate\Http\Request;

class ParentController extends Controller
{
    public function parentDashboard()
    {
        return Inertia::render('Parents/parentsDashboard');
    }

    public function memeriksaTugasSubmit()
    {
        return Inertia::render('Parents/memeriksaTugasSubmit');

    }
    public function memberikanKomentarKepadaSiswa(){
        return Inertia::render(component: 'Parents/memberikanKomentarKepadaSiswa');
    }
    public function melihatPresensiSiswa(){
        return Inertia::render(component: 'Parents/melihatPresensiSiswa');
    }
    public function melihatNilaiSiswa()
    {
        return Inertia::render(component: 'Parents/melihatNilaiSiswa');
    }
}
