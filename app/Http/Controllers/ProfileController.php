<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{

    public function index()
    {
        return inertia('Profile/index');

    }
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    public function dashboard()
    {
        $user = auth()->user();
        $roles = $user->roles->pluck('name');
    
        Log::info('User roles on dashboard access:', ['roles' => $roles]);
    
        // Cek berdasarkan peran dengan switch
        switch (true) {
            case $roles->contains('admin'):
                return Inertia::render('dashboard');
            case $roles->contains('teacher'):
                return Inertia::render('teachersDashboard');
            case $roles->contains('student'):
                return Inertia::render('studentsDashboard');
            default:
                return redirect()->route('home');
        }
    }
    
    
    
    

    /*
        if ($user->hasRole('admin')) {
            return Inertia::render('dashboard');
        } elseif ($user->hasRole('teacher')) {
            return Inertia::render('teachersDashboard');
        } elseif ($user->hasRole('student')) {
            return Inertia::render('studentsDashboard');
        } else {
            return redirect()->route('home'); 
        }
    */
    
    

      /*


            if(auth()->user()->hasRole('admin')){
            return Inertia::render('dashboard');
        }elseif(auth()->user()->hasRole('teacher')){
            return Inertia::render('teachersDashboard');
        }elseif(auth()->user()->hasRole('student')){
            return Inertia::render('studentsDashboard');
        }
      */


    

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
