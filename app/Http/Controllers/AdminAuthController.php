<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super-admin');
    }

    /**
     * Show admin authentication form
     */
    public function authenticate(Request $request)
    {
        $intended = $request->get('intended', route('admin.dashboard'));
        $action = $request->get('action', 'access');
        $module = $request->get('module', 'Admin Panel');
        
        // Check if user is authorized for edit operations
        $canEdit = auth()->user()->email === 'daniel@quty.co.id';
        
        return view('admin.authenticate', compact('intended', 'action', 'module', 'canEdit'));
    }

    /**
     * Process admin authentication
     */
    public function processAuth(Request $request)
    {
        $request->validate([
            'password' => 'required',
            'intended' => 'required|url',
            'acknowledge' => 'accepted'
        ], [
            'acknowledge.accepted' => 'You must acknowledge the security notice to proceed.'
        ]);

        $user = auth()->user();
        
        // Verify password
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Invalid password.'])->withInput();
        }
        
        // Set password confirmation session
        session(['admin_password_confirmed' => now()]);
        
        // Log the authentication
        Log::info('Admin authentication successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'module' => $request->get('module', 'Unknown'),
            'action' => $request->get('action', 'unknown'),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        return redirect($request->intended);
    }

    /**
     * Clear admin authentication
     */
    public function clearAuth(Request $request)
    {
        session()->forget('admin_password_confirmed');
        
        Log::info('Admin authentication cleared', [
            'user_id' => auth()->id(),
            'email' => auth()->user()->email,
            'ip' => $request->ip()
        ]);
        
        return redirect()->route('admin.dashboard')
            ->with('success', 'Admin authentication cleared successfully.');
    }
}