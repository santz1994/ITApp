<?php

namespace App\Repositories\Tickets;

use Illuminate\Http\Request;

interface TicketRepositoryInterface
{
    public function getAll();
    public function getAllWithFilters(Request $request, $user = null);
    public function getAllPaginated($perPage = 20, $user = null);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getByUser($userId);
    public function getByStatus($statusId);
    public function getByPriority($priorityId);
    public function getAssignedTo($adminId);
    public function getUnassigned();
    public function getTicketStats($user = null);
    public function getRecentTickets($limit = 10, $user = null);
    public function assignToAdmin($ticketId, $adminId);
    public function updateStatus($ticketId, $statusId);
    public function addNote($ticketId, $note, $userId);
    public function search($searchTerm, $user = null);
}