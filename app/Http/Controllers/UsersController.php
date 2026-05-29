<?php

namespace App\Http\Controllers;

use App\User;
use App\Role;
use App\Services\UserService;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UsersController extends Controller
{
  protected $userService;

  public function __construct(UserService $userService)
  {
    $this->middleware('auth');
    $this->userService = $userService;
  }

  public function sendEmailReminder(Request $request, User $user)
  {
      if ($this->userService->sendEmailReminder($user)) {
          Session::flash('status', 'success');
          Session::flash('message', 'Email reminder sent successfully');
      } else {
          Session::flash('status', 'error');
          Session::flash('message', 'Failed to send email reminder');
      }
      
      return back();
  }

  public function index()
  {
    $pageTitle = 'Users';
    $data = $this->userService->getUsersForIndex();
    $roles = \App\Services\CacheService::getRoles();
    return view('admin.users.index', compact('pageTitle', 'roles') + $data);
  }

  public function store(StoreUserRequest $request)
  {
    try {
      $data = $request->validated();
      $data['role_id'] = $data['role_id'] ?? (Role::where('name', 'user')->first()->id ?? null);
      
      $user = $this->userService->createUser($data);

      Session::flash('status', 'success');
      Session::flash('title', 'User: ' . $request->name);
      Session::flash('message', 'Successfully created');

      return redirect('/admin/users?legacy_msg=Successfully created&legacy_status=success&legacy_title=User: ' . urlencode($request->name));
      
    } catch (\Exception $e) {
      Session::flash('status', 'error');
      Session::flash('title', 'Error');
      Session::flash('message', 'Failed to create user: ' . $e->getMessage());
      return back()->withInput();
    }
  }

  public function create()
  {
    $pageTitle = 'Create New User';
    $formData = $this->userService->getFormData();
    return view('admin.users.create', compact('pageTitle') + $formData);
  }

  public function edit(User $user)
  {
    $pageTitle = 'Edit User - ' . ($user->name ?? '');
    $data = $this->userService->getUsersForIndex();
    $formData = $this->userService->getFormData();
    return view('admin.users.edit', compact('pageTitle', 'user') + $data + $formData);
  }

  public function update(UpdateUserRequest $request, User $user)
  {
    try {
      $data = $request->validated();
      $updatedUser = $this->userService->updateUserWithRoleValidation($user, $data);

      if ((int) Auth::id() === (int) $updatedUser->id) {
        Auth::setUser($updatedUser->fresh(['roles', 'division']));
      }

      Session::flash('status', 'success');
      Session::flash('title', 'User: ' . $updatedUser->name);
      Session::flash('message', 'Successfully updated');

      return redirect('/admin/users?legacy_msg=Successfully updated&legacy_status=success&legacy_title=User: ' . urlencode($updatedUser->name));
      
    } catch (\Exception $e) {
      Session::flash('status', 'error');
      Session::flash('title', 'Error');
      Session::flash('message', $e->getMessage());
      
      return redirect('/admin/users/' . $user->id . '/edit?legacy_msg=' . urlencode($e->getMessage()) . '&legacy_status=warning');
    }
  }

  /**
   * Delete a single user (per-row delete)
   */
  public function destroy(User $user)
  {
    $this->authorize('delete-users');

    try {
      $this->userService->deleteUser($user, auth()->id());
      Session::flash('status', 'success');
      Session::flash('message', 'Successfully deleted');
    } catch (\Exception $e) {
      Session::flash('status', Str::contains($e->getMessage(), 'cannot delete your own') ? 'error' : 'warning');
      Session::flash('message', $e->getMessage());
      return back();
    }

    return redirect('/admin/users');
  }

  public function roles()
  {
    $roles = Role::query()
      ->canonical()
      ->with(['users', 'permissions'])
      ->get();
    return view('users.roles', compact('roles'));
  }

  /**
   * Bulk delete users (AJAX)
   */
  public function bulkDelete(Request $request)
  {
    $this->authorize('delete-users');

    $ids = $request->input('ids', []);
    if (!is_array($ids) || empty($ids)) {
      return response()->json(['success' => false, 'message' => 'No user ids provided'], 400);
    }

    try {
      $deleted = $this->userService->bulkDeleteUsers($ids, auth()->id());
      return response()->json(['success' => true, 'deleted' => $deleted]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
  }
}
