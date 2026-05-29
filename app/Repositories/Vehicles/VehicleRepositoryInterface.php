<?php

namespace App\Repositories\Vehicles;

interface VehicleRepositoryInterface
{
    public function getAll($filters = []);
    public function getById($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    public function getAvailableVehicles($startDatetime, $endDatetime);
    public function checkAvailability($vehicleId, $startDatetime, $endDatetime, $excludeBookingId = null);
}