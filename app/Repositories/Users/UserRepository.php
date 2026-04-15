<?php

namespace App\Repositories\Users;

use App\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    public function getAll()
    {
        return $this->model->with(['roles', 'division'])->get();
    }

    public function getAllPaginated($perPage = 20)
    {
        return $this->model->with(['roles', 'division'])
                          ->orderBy('name')
                          ->paginate($perPage);
    }

    public function getAllWithStats()
    {
        return $this->model->with(['roles', 'division'])
                          ->withCount(['assets', 'tickets'])
                          ->orderBy('name')
                          ->paginate(20);
    }

    public function find($id)
    {
        return $this->model->with(['roles', 'division', 'assets', 'tickets'])
                          ->findOrFail($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $user = $this->model->findOrFail($id);
        $user->update($data);
        return $user->fresh(['roles', 'division']);
    }

    public function delete($id)
    {
        $user = $this->model->findOrFail($id);
        return $user->delete();
    }

    public function findByEmail($email)
    {
        return $this->model->with(['roles', 'division'])
                          ->where('email', $email)
                          ->first();
    }

    public function findByEmployeeNum($employeeNum)
    {
        return $this->model->with(['roles', 'division'])
                          ->where('employee_num', $employeeNum)
                          ->first();
    }

    public function getByRole($roleName)
    {
        return $this->model->role($roleName)
                          ->with(['roles', 'division'])
                          ->orderBy('name')
                          ->get();
    }

    public function getByDivision($divisionId)
    {
        return $this->model->with(['roles', 'division'])
                          ->where('division_id', $divisionId)
                          ->orderBy('name')
                          ->get();
    }

    public function getActiveUsers()
    {
        return $this->model->with(['roles', 'division'])
                          ->where('activated', true)
                          ->orderBy('name')
                          ->get();
    }

    public function getInactiveUsers()
    {
        return $this->model->with(['roles', 'division'])
                          ->where('activated', false)
                          ->orderBy('name')
                          ->get();
    }

    public function search($searchTerm)
    {
        return $this->model->with(['roles', 'division'])
                          ->where(function($query) use ($searchTerm) {
                              $query->where('name', 'like', "%{$searchTerm}%")
                                    ->orWhere('email', 'like', "%{$searchTerm}%")
                                    ->orWhere('employee_num', 'like', "%{$searchTerm}%")
                                    ->orWhere('position', 'like', "%{$searchTerm}%");
                          })
                          ->orderBy('name')
                          ->paginate(20);
    }

    public function getUsersWithAssets()
    {
        return $this->model->with(['roles', 'division', 'assets'])
                          ->has('assets')
                          ->withCount('assets')
                          ->orderBy('name')
                          ->get();
    }

    public function getUsersWithTickets()
    {
        return $this->model->with(['roles', 'division', 'tickets'])
                          ->has('tickets')
                          ->withCount('tickets')
                          ->orderBy('name')
                          ->get();
    }

    public function assignRole($userId, $roleId)
    {
        $user = $this->model->findOrFail($userId);
        $role = Role::findOrFail($roleId);
        $user->assignRole($role);
        return $user->fresh('roles');
    }

    public function removeRole($userId, $roleId)
    {
        $user = $this->model->findOrFail($userId);
        $role = Role::findOrFail($roleId);
        $user->removeRole($role);
        return $user->fresh('roles');
    }

    public function toggleActivation($userId)
    {
        $user = $this->model->findOrFail($userId);
        $user->update(['activated' => !$user->activated]);
        return $user;
    }
}