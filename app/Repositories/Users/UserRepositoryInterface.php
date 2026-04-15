<?php

namespace App\Repositories\Users;

use Illuminate\Http\Request;

interface UserRepositoryInterface
{
    public function getAll();
    public function getAllPaginated($perPage = 20);
    public function getAllWithStats();
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function findByEmail($email);
    public function findByEmployeeNum($employeeNum);
    public function getByRole($roleName);
    public function getByDivision($divisionId);
    public function getActiveUsers();
    public function getInactiveUsers();
    public function search($searchTerm);
    public function getUsersWithAssets();
    public function getUsersWithTickets();
    public function assignRole($userId, $roleId);
    public function removeRole($userId, $roleId);
    public function toggleActivation($userId);
}