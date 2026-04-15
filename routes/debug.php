<?php

/**
 * Debug and Test Routes
 * 
 * ⚠️ ONLY LOADED IN LOCAL ENVIRONMENT ⚠️
 * 
 * Contains all debug, test, and development helper routes
 * Automatically excluded in production
 */

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Current user debug
Route::get('/debug/user', function() {
    return view('debug.current-user');
})->middleware(['web', 'auth']);

// Debug route for assets issue
Route::get('/test-inventory-debug', function() {
    try {
        echo "<h2>Testing Inventory Controller Data</h2>";
        
        // Test each model one by one
        echo "<h3>Model Tests:</h3>";
        
        echo "Asset count: " . \App\Asset::count() . "<br>";
        echo "AssetType count: " . \App\AssetType::count() . "<br>";
        echo "Status count: " . \App\Status::count() . "<br>";
        echo "Location count: " . \App\Location::count() . "<br>";
        echo "Division count: " . \App\Division::count() . "<br>";
        
        // Test the problematic queries
        echo "<h3>Stats Queries:</h3>";
        
        $activeCount = \App\Asset::whereHas('status', function($q) {
            $q->where('name', 'Active');
        })->count();
        echo "Active assets: " . $activeCount . "<br>";
        
        $maintenanceCount = \App\Asset::whereHas('status', function($q) {
            $q->where('name', 'like', '%repair%');
        })->count();
        echo "Maintenance assets: " . $maintenanceCount . "<br>";
        
        // Check if AssetRequest model exists
        if (class_exists('\App\AssetRequest')) {
            $pendingCount = \App\AssetRequest::where('status', 'pending')->count();
            echo "Pending requests: " . $pendingCount . "<br>";
        } else {
            echo "AssetRequest model not found<br>";
        }
        
        echo "<h3>Controller Test:</h3>";
        
        // Test the actual controller
        $controller = new \App\Http\Controllers\InventoryController();
        $request = new \Illuminate\Http\Request();
        
        echo "Calling controller index method...<br>";
        $result = $controller->index($request);
        
        if ($result instanceof \Illuminate\View\View) {
            echo "Controller returned a view successfully!<br>";
            $viewData = $result->getData();
            echo "View data keys: " . implode(', ', array_keys($viewData)) . "<br>";
        } else {
            echo "Controller returned: " . gettype($result) . "<br>";
        }
        
    } catch (\Exception $e) {
        echo "<h3>Error:</h3>";
        echo "Message: " . $e->getMessage() . "<br>";
        echo "File: " . $e->getFile() . "<br>";
        echo "Line: " . $e->getLine() . "<br>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
});

// Debug authentication route (outside middleware)
Route::get('/debug-auth', function() {
    /** @var \App\User $user */
    $user = auth()->user();
    if (!$user) {
        return '<h1>NOT LOGGED IN</h1><p>You need to log in first</p><a href="/login">Go to Login</a>';
    }
    
    $roles = $user->roles->pluck('name')->toArray() ?? [];
    
    return '<h1>Authentication Debug</h1>' .
           '<p><strong>User:</strong> ' . $user->name . '</p>' .
           '<p><strong>Email:</strong> ' . $user->email . '</p>' .
           '<p><strong>Roles:</strong> ' . implode(', ', $roles) . '</p>' .
           '<p><strong>Has admin role:</strong> ' . (user_has_role($user, 'admin') ? 'YES' : 'NO') . '</p>' .
           '<p><strong>Has super-admin role:</strong> ' . (user_has_role($user, 'super-admin') ? 'YES' : 'NO') . '</p>';
});

// Simple assets test route
Route::get('/assets-simple', function() {
    try {
        $pageTitle = 'Simple Assets Test';
        
        // Simple stats without complex relationships
        $stats = [
            'total_assets' => \App\Asset::count(),
            'active_assets' => 0,
            'maintenance_assets' => 0,
            'pending_requests' => 0
        ];
        
        // Get basic data
        $assets = \App\Asset::with(['assetModel', 'status', 'division'])->paginate(25);
        $categories = \App\AssetType::all();
        $statuses = \App\Status::all();
        $locations = \App\Location::all();
        $divisions = \App\Division::all();
        $categoryStats = collect();
        
        return view('inventory.index', compact(
            'assets', 'categories', 'statuses', 'locations', 'divisions', 
            'stats', 'categoryStats', 'pageTitle'
        ));
        
    } catch (\Exception $e) {
        return response("Error: " . $e->getMessage() . " in " . $e->getFile() . " line " . $e->getLine(), 500);
    }
});

// Test Routes (untuk development)
Route::prefix('test')->group(function () {
    Route::get('/qr', [\App\Http\Controllers\TestController::class, 'testQrCode'])->name('test.qr');
    Route::get('/asset-qr', [\App\Http\Controllers\TestController::class, 'testAssetQrCode'])->name('test.asset-qr');
    Route::get('/status', [\App\Http\Controllers\TestController::class, 'systemStatus'])->name('test.status');
    
    // UAC Test Routes
    Route::get('/super-admin-test', function() {
        return 'Super Admin Access Working!';
    })->middleware(['auth', 'role:super-admin']);
    
    Route::get('/admin-test', function() {
        return 'Admin Access Working!';
    })->middleware(['auth', 'role:admin']);
    
    Route::get('/management-test', function() {
        return 'Management Access Working!';
    })->middleware(['auth', 'role:management']);
    
    // Debug current user roles
    Route::get('/debug-my-roles', function() {
    /** @var \App\User $user */
    $user = auth()->user();
        if (!$user) {
            return 'Not logged in';
        }
        
        $output = "<h2>Current User Debug Info</h2>";
        $output .= "<p><strong>Name:</strong> " . $user->name . "</p>";
        $output .= "<p><strong>Email:</strong> " . $user->email . "</p>";
        $output .= "<p><strong>ID:</strong> " . $user->id . "</p>";
        
        // Check roles using different methods
        $output .= "<h3>Role Check Methods:</h3>";
        $output .= "<p><strong>hasRole('super-admin'):</strong> " . (user_has_role($user, 'super-admin') ? 'YES' : 'NO') . "</p>";
        $output .= "<p><strong>hasRole('admin'):</strong> " . (user_has_role($user, 'admin') ? 'YES' : 'NO') . "</p>";
        $output .= "<p><strong>hasRole('user'):</strong> " . (user_has_role($user, 'user') ? 'YES' : 'NO') . "</p>";
        $output .= "<p><strong>hasRole('management'):</strong> " . (user_has_role($user, 'management') ? 'YES' : 'NO') . "</p>";
        
        // Get all roles from database
        $roles = DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->where('model_type', 'App\\User')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->pluck('roles.name')
            ->toArray();
        
        $output .= "<h3>Database Roles:</h3>";
        $output .= "<pre>" . print_r($roles, true) . "</pre>";
        
        return $output;
    })->middleware(['auth']);
});

// Test routes for role middleware verification
Route::group(['middleware' => ['web', 'auth']], function () {
    Route::get('/test-role', function() {
    /** @var \App\User $user */
    $user = Auth::user();
        echo "<h1>User Role Test</h1>";
        echo "Logged in as: " . $user->name . "<br>";
        echo "Email: " . $user->email . "<br>";
        echo "<hr>";
        
        echo "<h2>Role Check</h2>";
        echo "Has super-admin role: " . (user_has_role($user, 'super-admin') ? 'YES' : 'NO') . "<br>";
        echo "Has admin role: " . (user_has_role($user, 'admin') ? 'YES' : 'NO') . "<br>";
        echo "Has management role: " . (user_has_role($user, 'management') ? 'YES' : 'NO') . "<br>";
        echo "Has user role: " . (user_has_role($user, 'user') ? 'YES' : 'NO') . "<br>";
        
        echo "<h2>Database Role Assignments</h2>";
        $spatieRoles = DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->where('model_type', 'App\\User')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->select('roles.name')
            ->get();
            
        $legacyRoles = DB::table('role_user')
            ->where('user_id', $user->id)
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('roles.name')
            ->get();
            
        echo "Spatie Roles: ";
        if ($spatieRoles->isEmpty()) {
            echo "None";
        } else {
            echo implode(", ", $spatieRoles->pluck('name')->toArray());
        }
        echo "<br>";
        
        echo "Legacy Roles: ";
        if ($legacyRoles->isEmpty()) {
            echo "None";
        } else {
            echo implode(", ", $legacyRoles->pluck('name')->toArray());
        }
        echo "<br>";
        
        echo "<h2>Protected Route Tests</h2>";
        echo "The following links test if the role middleware is working correctly:<br>";
        echo "<ul>";
        echo "<li><a href='/test-super-admin'>Test Super Admin Route</a> (requires super-admin role)</li>";
        echo "<li><a href='/test-admin'>Test Admin Route</a> (requires admin role)</li>";
        echo "<li><a href='/test-management'>Test Management Route</a> (requires management role)</li>";
        echo "<li><a href='/test-user'>Test User Route</a> (requires user role)</li>";
        echo "</ul>";
        
        return "";
    });
    
    // Test routes with different role middleware requirements
    Route::get('/test-super-admin', function() {
        return "You have the super-admin role!";
    })->middleware('role:super-admin');
    
    Route::get('/test-admin', function() {
        return "You have the admin role!";
    })->middleware('role:admin');
    
    Route::get('/test-management', function() {
        return "You have the management role!";
    })->middleware('role:management');
    
    Route::get('/test-user', function() {
        return "You have the user role!";
    })->middleware('role:user');
});

// Debug routes for role middleware issues
Route::get('/debug-roles', function () {
    /** @var \App\User $user */
    $user = auth()->user();
    if (!$user) {
        return '<h1>❌ NOT LOGGED IN</h1><p>Please login first at: <a href="/login">/login</a></p>';
    }

    $html = '<h1>Role Debug Information</h1>';
    $html .= '<p><strong>User:</strong> ' . $user->name . ' (ID: ' . $user->id . ')</p>';
    $html .= '<p><strong>Email:</strong> ' . $user->email . '</p>';

    // Test if user has role directly from DB
    $directDbRoles = DB::table('model_has_roles')
        ->where('model_id', $user->id)
        ->where('model_type', 'App\\User')
        ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
        ->pluck('roles.name')
        ->toArray();

    $html .= '<h2>Roles from DB:</h2>';
    if (empty($directDbRoles)) {
        $html .= '<p style="color:red;"><strong>❌ NO ROLES ASSIGNED TO THIS USER!</strong></p>';
        $html .= '<p>This is why you\'re getting 403 errors!</p>';
    } else {
        $html .= '<ul>';
        foreach ($directDbRoles as $role) {
            $html .= '<li><strong>' . $role . '</strong></li>';
        }
        $html .= '</ul>';
    }

    // Test HasRole method
    $html .= '<h2>Role Checks:</h2>';
    $html .= '<ul>';
    $html .= '<li>Has role "super-admin": ' . (user_has_role($user, 'super-admin') ? '✅ YES' : '❌ NO') . '</li>';
    $html .= '<li>Has role "admin": ' . (user_has_role($user, 'admin') ? '✅ YES' : '❌ NO') . '</li>';
    $html .= '<li>Has any role ["super-admin", "admin"]: ' . (user_has_any_role($user, ['super-admin', 'admin']) ? '✅ YES' : '❌ NO') . '</li>';
    $html .= '</ul>';
    
    $html .= '<h2>Expected Result:</h2>';
    $html .= '<p>For routes to work, you need: <strong>admin</strong> OR <strong>super-admin</strong> role</p>';
    
    if (in_array('super-admin', $directDbRoles) || in_array('admin', $directDbRoles)) {
        $html .= '<p style="color:green;"><strong>✅ YOU HAVE CORRECT ROLES - Routes should work!</strong></p>';
        $html .= '<p>If still getting 403, clear browser cache and try again.</p>';
    } else {
        $html .= '<p style="color:red;"><strong>❌ YOU DON\'T HAVE REQUIRED ROLES - This is why you get 403!</strong></p>';
        $html .= '<p>Login as: superadmin@quty.co.id or admin@quty.co.id</p>';
    }

    return $html;
});

// Test route protected by role middleware
Route::get('/test-super-admin', function () {
    return '<h1>You have super-admin access!</h1>';
})->middleware('role:super-admin');

// Debug menu - shows all available debug routes
Route::get('/debug-menu', function() {
    $html = '<h2>🔧 Debug Menu</h2>';
    $html .= '<div style="margin: 20px 0;">';
    $html .= '<h3>Authentication Debug:</h3>';
    $html .= '<p><a href="/debug-current-user" style="background: #17a2b8; color: white; padding: 8px 15px; text-decoration: none; margin: 5px;">🔍 Check Current User</a></p>';
    $html .= '<p><a href="/check-users" style="background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; margin: 5px;">👥 List All Users</a></p>';
    $html .= '</div>';
    
    $html .= '<div style="margin: 20px 0;">';
    $html .= '<h3>Quick Login (for testing):</h3>';
    $html .= '<p><a href="/quick-login-superadmin" style="background: #dc3545; color: white; padding: 8px 15px; text-decoration: none; margin: 5px;">🔑 Login as Super Admin</a></p>';
    $html .= '<p><a href="/quick-login-admin" style="background: #ffc107; color: black; padding: 8px 15px; text-decoration: none; margin: 5px;">🔑 Login as Admin</a></p>';
    $html .= '<p><a href="/quick-login-user" style="background: #28a745; color: white; padding: 8px 15px; text-decoration: none; margin: 5px;">🔑 Login as User</a></p>';
    $html .= '</div>';
    
    $html .= '<div style="margin: 20px 0;">';
    $html .= '<h3>Page Access:</h3>';
    $html .= '<p><a href="/assets" style="background: #007bff; color: white; padding: 8px 15px; text-decoration: none; margin: 5px;">📦 Assets Page</a></p>';
    $html .= '<p><a href="/tickets" style="background: #6f42c1; color: white; padding: 8px 15px; text-decoration: none; margin: 5px;">🎫 Tickets Page</a></p>';
    $html .= '<p><a href="/daily-activities" style="background: #fd7e14; color: white; padding: 8px 15px; text-decoration: none; margin: 5px;">📅 Daily Activities</a></p>';
    $html .= '</div>';
    
    $html .= '<div style="margin: 20px 0;">';
    $html .= '<h3>Logout:</h3>';
    $html .= '<p><a href="/force-relogin" style="background: #6c757d; color: white; padding: 8px 15px; text-decoration: none; margin: 5px;">🚪 Logout & Clear Session</a></p>';
    $html .= '</div>';
    
    return $html;
});

// Check users in database
Route::get('/check-users', function() {
    $users = \App\User::take(5)->get(['id', 'name', 'email']);
    $html = '<h2>Users in Database (' . \App\User::count() . ' total)</h2>';
    foreach($users as $user) {
        $html .= '<p>ID: ' . $user->id . ', Name: ' . $user->name . ', Email: ' . $user->email . '</p>';
    }
    return $html;
});

// Test hasRole method
Route::get('/test-has-role', function() {
    $html = '<h2>Test hasRole Method</h2>';
    
    try {
        $user = \App\User::find(1);
        if ($user) {
            $html .= '<p>User found: ' . $user->name . '</p>';
            $html .= '<p>hasRole method exists: ' . (method_exists($user, 'hasRole') ? '✅ YES' : '❌ NO') . '</p>';
            
            if (method_exists($user, 'hasRole')) {
                $hasSuperAdmin = user_has_role($user, 'super-admin');
                $hasAdmin = user_has_role($user, 'admin');
                $hasUser = user_has_role($user, 'user');

                $html .= '<p>Has super-admin role: ' . ($hasSuperAdmin ? '✅ YES' : '❌ NO') . '</p>';
                $html .= '<p>Has admin role: ' . ($hasAdmin ? '✅ YES' : '❌ NO') . '</p>';
                $html .= '<p>Has user role: ' . ($hasUser ? '✅ YES' : '❌ NO') . '</p>';
            }
            
            // Test getRoleNames if available
            if (method_exists($user, 'getRoleNames')) {
                $roles = user_get_role_names($user);
                $html .= '<p>All roles: ' . $roles->implode(', ') . '</p>';
            }
        } else {
            $html .= '<p>❌ User not found</p>';
        }
    } catch (\Exception $e) {
        $html .= '<p>❌ Error: ' . $e->getMessage() . '</p>';
    }
    
    return $html;
});

// Debug current user info
Route::get('/debug-current-user', function() {
    \Illuminate\Support\Facades\Log::info('Debug route accessed', [
        'authenticated' => Auth::check(),
        'user_id' => Auth::id(),
        'session_id' => session()->getId()
    ]);
    
    $html = '<h2>Current User Debug Info</h2>';
    
    if (Auth::check()) {
        $user = Auth::user();
        
        \Illuminate\Support\Facades\Log::info('User authenticated', [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email
        ]);
        
        $html .= '<div style="background: #d4edda; padding: 10px; border: 1px solid #c3e6cb; margin: 10px 0;">';
        $html .= '<h3>✅ LOGGED IN</h3>';
        $html .= '<p><strong>User ID:</strong> ' . $user->id . '</p>';
        $html .= '<p><strong>Name:</strong> ' . $user->name . '</p>';
        $html .= '<p><strong>Email:</strong> ' . $user->email . '</p>';
        
        // Check roles if available
        if (method_exists($user, 'getRoleNames')) {
            $roles = user_get_role_names($user);
            $html .= '<p><strong>Roles:</strong> ' . ($roles->count() > 0 ? $roles->implode(', ') : 'None') . '</p>';
        }

        if (method_exists($user, 'hasRole')) {
            $hasSuperAdmin = user_has_role($user, 'super-admin');
            $hasAdmin = user_has_role($user, 'admin');
            $hasManagement = user_has_role($user, 'management');

            $html .= '<p><strong>Has super-admin role:</strong> ' . ($hasSuperAdmin ? '✅ YES' : '❌ NO') . '</p>';
            $html .= '<p><strong>Has admin role:</strong> ' . ($hasAdmin ? '✅ YES' : '❌ NO') . '</p>';
            $html .= '<p><strong>Has management role:</strong> ' . ($hasManagement ? '✅ YES' : '❌ NO') . '</p>';
        }
        
        $html .= '</div>';
        $html .= '<p><a href="/assets" style="background: #007bff; color: white; padding: 10px 20px; text-decoration: none;">Go to Assets Page</a></p>';
        $html .= '<p><a href="/tickets" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none;">Go to Tickets Page</a></p>';
    } else {
        $html .= '<div style="background: #f8d7da; padding: 10px; border: 1px solid #f5c6cb; margin: 10px 0;">';
        $html .= '<h3>❌ NOT LOGGED IN</h3>';
        $html .= '<p>You need to log in first</p>';
        $html .= '</div>';
        $html .= '<p><a href="/login" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none;">Go to Login</a></p>';
    }
    
    $html .= '<hr>';
    $html .= '<p><strong>Session ID:</strong> ' . session()->getId() . '</p>';
    $html .= '<p><strong>CSRF Token:</strong> ' . csrf_token() . '</p>';
    
    return $html;
});

// Temporary: Seed import_summary in session and redirect to download for testing
Route::get('/debug-seed-import-summary', function() {
    $summary = [
        'errors' => [
            ['row' => 2, 'errors' => ['Missing asset tag'], 'data' => ['name' => 'Sample Asset 1']],
            ['row' => 5, 'errors' => ['Invalid model'], 'data' => ['name' => 'Sample Asset 2']],
        ]
    ];

    session(['import_summary' => $summary]);

    return redirect()->route('assets.import-errors-download');
});

// Debug: seed a sample import_summary and stream the CSV directly (no auth)
Route::get('/debug-download-import-errors', function() {
    $summary = [
        'errors' => [
            ['row' => 2, 'errors' => ['Missing asset tag'], 'data' => ['name' => 'Sample Asset 1']],
            ['row' => 5, 'errors' => ['Invalid model'], 'data' => ['name' => 'Sample Asset 2']],
        ]
    ];

    $errors = $summary['errors'] ?? [];

    $callback = function() use ($errors) {
        $out = fopen('php://output', 'w');
        // Header
        fputcsv($out, ['row', 'messages', 'data']);

        foreach ($errors as $err) {
            $row = $err['row'] ?? '';
            $messages = '';
            if (!empty($err['errors'])) {
                $messages = implode('; ', $err['errors']);
            } else {
                $messages = $err['error'] ?? '';
            }
            $data = isset($err['data']) ? json_encode($err['data']) : '';

            fputcsv($out, [$row, $messages, $data]);
        }

        fclose($out);
    };

    return response()->stream($callback, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="import_errors_debug.csv"',
    ]);
});

// Quick login routes for testing
Route::get('/quick-login-superadmin', function() {
    Auth::loginUsingId(1);
    \Illuminate\Support\Facades\Log::info('Quick login as Super Admin', ['user_id' => 1]);
    return redirect('/debug-current-user')->with('message', 'Logged in as Super Admin');
});

Route::get('/quick-login-admin', function() {
    Auth::loginUsingId(2);
    \Illuminate\Support\Facades\Log::info('Quick login as Admin', ['user_id' => 2]);
    return redirect('/debug-current-user')->with('message', 'Logged in as Admin');
});

Route::get('/quick-login-user', function() {
    Auth::loginUsingId(3);
    \Illuminate\Support\Facades\Log::info('Quick login as User', ['user_id' => 3]);
    return redirect('/debug-current-user')->with('message', 'Logged in as User');
});

// Browser-friendly login and redirect
Route::get('/browser-login-superadmin', function() {
    Auth::loginUsingId(1);
    \Illuminate\Support\Facades\Log::info('Browser login as Super Admin', ['user_id' => 1]);
    return redirect('/assets')->with('success', 'Logged in as Super Admin - you should now see the assets page!');
});

Route::get('/browser-login-admin', function() {
    Auth::loginUsingId(2);
    \Illuminate\Support\Facades\Log::info('Browser login as Admin', ['user_id' => 2]);
    return redirect('/assets')->with('success', 'Logged in as Admin - you should now see the assets page!');
});

// Test all major controllers
Route::get('/test-all-controllers', function() {
    Auth::loginUsingId(1);
    
    $html = '<h2>Controller Tests (as Super Admin)</h2>';
    
    $controllers = [
        'InventoryController@index' => '/assets',
        'TicketController@index' => '/tickets', 
        'DailyActivityController@index' => '/daily-activities',
        'HomeController@index' => '/home'
    ];
    
    foreach($controllers as $controller => $route) {
        try {
            $request = \Illuminate\Http\Request::create($route, 'GET');
            $request->setUserResolver(function() {
                return Auth::user();
            });
            
            list($controllerClass, $method) = explode('@', $controller);
            $fullControllerClass = "\\App\\Http\\Controllers\\{$controllerClass}";
            
            if (class_exists($fullControllerClass)) {
                $controllerInstance = app($fullControllerClass);
                
                if (method_exists($controllerInstance, $method)) {
                    $controllerInstance->$method($request);
                    $html .= "<p>✅ <strong>{$controller}</strong> ({$route}): SUCCESS</p>";
                } else {
                    $html .= "<p>❌ <strong>{$controller}</strong> ({$route}): Method not found</p>";
                }
            } else {
                $html .= "<p>❌ <strong>{$controller}</strong> ({$route}): Controller not found</p>";
            }
            
        } catch (\Exception $e) {
            $html .= "<p>❌ <strong>{$controller}</strong> ({$route}): ERROR - " . $e->getMessage() . "</p>";
        }
    }
    
    return $html;
});

// Test login and immediately access assets in same request
Route::get('/test-login-and-assets', function() {
    $html = '<h2>Login and Assets Test (Single Request)</h2>';
    
    Auth::loginUsingId(1);
    $html .= '<h3>Step 1: Login as Super Admin</h3>';
    $html .= '<p>Auth::check(): ' . (Auth::check() ? '✅ TRUE' : '❌ FALSE') . '</p>';
    $html .= '<p>Auth::id(): ' . (Auth::id() ?? 'NULL') . '</p>';
    
    if (Auth::check()) {
        $user = Auth::user();
        $html .= '<p>User: ' . $user->name . ' (' . $user->email . ')</p>';
    }
    
    $html .= '<h3>Step 2: Test Assets Controller</h3>';
    try {
        $controller = new \App\Http\Controllers\InventoryController();
        $request = \Illuminate\Http\Request::create('/assets', 'GET');
        
        $request->setUserResolver(function() {
            return Auth::user();
        });
        
        $response = $controller->index($request);
        $html .= '<p>✅ SUCCESS: Controller returned response</p>';
        $html .= '<p>Response type: ' . get_class($response) . '</p>';
        
        if (method_exists($response, 'render')) {
            $content = $response->render();
            $html .= '<p>Content length: ' . strlen($content) . ' characters</p>';
            $html .= '<p>Contains "Total Assets": ' . (strpos($content, 'Total Assets') !== false ? '✅ YES' : '❌ NO') . '</p>';
        }
        
    } catch (\Exception $e) {
        $html .= '<p>❌ ERROR: ' . $e->getMessage() . '</p>';
        $html .= '<p>File: ' . $e->getFile() . ':' . $e->getLine() . '</p>';
    }
    
    $html .= '<h3>Step 3: Test Links</h3>';
    $html .= '<p><a href="/assets" style="background: #007bff; color: white; padding: 10px; text-decoration: none;">Go to Assets Page</a></p>';
    $html .= '<p><strong>Note:</strong> If the link above shows login page, then the session is not persisting between requests.</p>';
    
    return $html;
});

// Temporary test route to see assets page without authentication
Route::get('/test-assets-no-auth', function() {
    Auth::loginUsingId(1);
    return redirect('/assets');
});

// Test multiple pages with auth
Route::get('/test-all-pages', function() {
    Auth::loginUsingId(1);
    
    $pages = [
        '/assets' => 'Assets Page',
        '/daily-activities' => 'Daily Activities', 
        '/tickets' => 'Tickets',
        '/home' => 'Home/Dashboard'
    ];
    
    $html = '<h2>Testing Pages (logged in as Super Admin)</h2>';
    foreach($pages as $url => $name) {
        try {
            $response = file_get_contents('http://192.168.1.122' . $url);
            $status = strpos($response, 'form-control name=email') === false ? '✅ WORKS' : '❌ LOGIN PAGE';
            $html .= '<p><strong>' . $name . '</strong> (' . $url . '): ' . $status . '</p>';
        } catch (Exception $e) {
            $html .= '<p><strong>' . $name . '</strong> (' . $url . '): ❌ ERROR</p>';
        }
    }
    return $html;
});
