<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Asset;
use App\User;
use Illuminate\Http\JsonResponse;

class ValidationController extends Controller
{
    /**
     * Check if asset tag is unique
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateAssetTag(Request $request): JsonResponse
    {
        $assetTag = $request->input('asset_tag');
        $excludeId = $request->input('exclude_id');

        if (empty($assetTag)) {
            return response()->json(['available' => true]);
        }

        $query = Asset::where('asset_tag', $assetTag);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Asset tag already exists' : 'Asset tag is available'
        ]);
    }

    /**
     * Check if serial number is unique
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateSerialNumber(Request $request): JsonResponse
    {
        $serialNumber = $request->input('serial_number');
        $excludeId = $request->input('exclude_id');

        if (empty($serialNumber)) {
            return response()->json(['available' => true]);
        }

        $query = Asset::where('serial_number', $serialNumber);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Serial number already exists' : 'Serial number is available'
        ]);
    }

    /**
     * Check if email is unique
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateEmail(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $excludeId = $request->input('exclude_id');

        if (empty($email)) {
            return response()->json(['available' => true]);
        }

        $query = User::where('email', $email);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Email already registered' : 'Email is available'
        ]);
    }

    /**
     * Validate IP address format and availability
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateIpAddress(Request $request): JsonResponse
    {
        $ipAddress = $request->input('ip_address');
        $excludeId = $request->input('exclude_id');

        if (empty($ipAddress)) {
            return response()->json(['valid' => true, 'available' => true]);
        }

        // Validate format
        if (!filter_var($ipAddress, FILTER_VALIDATE_IP)) {
            return response()->json([
                'valid' => false,
                'available' => false,
                'message' => 'Invalid IP address format'
            ]);
        }

        // Check uniqueness
        $query = Asset::where('ip_address', $ipAddress);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json([
            'valid' => true,
            'available' => !$exists,
            'message' => $exists ? 'IP address already in use' : 'IP address is available'
        ]);
    }

    /**
     * Validate MAC address format and availability
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateMacAddress(Request $request): JsonResponse
    {
        $macAddress = $request->input('mac_address');
        $excludeId = $request->input('exclude_id');

        if (empty($macAddress)) {
            return response()->json(['valid' => true, 'available' => true]);
        }

        // Validate format (AA:BB:CC:DD:EE:FF or AA-BB-CC-DD-EE-FF)
        $pattern = '/^([0-9A-Fa-f]{2}[:-]){5}([0-9A-Fa-f]{2})$/';
        if (!preg_match($pattern, $macAddress)) {
            return response()->json([
                'valid' => false,
                'available' => false,
                'message' => 'Invalid MAC address format. Use AA:BB:CC:DD:EE:FF'
            ]);
        }

        // Check uniqueness
        $query = Asset::where('mac_address', $macAddress);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return response()->json([
            'valid' => true,
            'available' => !$exists,
            'message' => $exists ? 'MAC address already in use' : 'MAC address is available'
        ]);
    }

    /**
     * Batch validate multiple fields
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function validateBatch(Request $request): JsonResponse
    {
        $fields = $request->input('fields', []);
        $results = [];

        foreach ($fields as $field => $value) {
            switch ($field) {
                case 'asset_tag':
                    $results[$field] = $this->validateAssetTag($request)->getData();
                    break;
                case 'serial_number':
                    $results[$field] = $this->validateSerialNumber($request)->getData();
                    break;
                case 'email':
                    $results[$field] = $this->validateEmail($request)->getData();
                    break;
                case 'ip_address':
                    $results[$field] = $this->validateIpAddress($request)->getData();
                    break;
                case 'mac_address':
                    $results[$field] = $this->validateMacAddress($request)->getData();
                    break;
            }
        }

        return response()->json($results);
    }
}

