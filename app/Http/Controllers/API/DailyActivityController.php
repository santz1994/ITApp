<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\DailyActivity;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DailyActivityController extends Controller
{
    /**
     * Display a listing of daily activities
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = DailyActivity::with(['user', 'ticket']);

        // Apply filters
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        if ($request->has('date_from')) {
            $query->where('activity_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('activity_date', '<=', $request->date_to);
        }

        if ($request->has('is_completed')) {
            $query->where('is_completed', $request->boolean('is_completed'));
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Order
        $query->orderBy('activity_date', 'desc')
              ->orderBy('created_at', 'desc');

        // Pagination
        $perPage = $request->get('per_page', 15);
        $activities = $query->paginate($perPage);

        // Transform data
        $activities->through(function ($activity) {
            return $this->transformActivity($activity);
        });

        return response()->json([
            'success' => true,
            'data' => $activities,
            'message' => 'Daily activities retrieved successfully'
        ]);
    }

    /**
     * Store a newly created daily activity
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'activity_type' => 'required|in:ticket_work,asset_maintenance,general_task,meeting,training',
            'activity_date' => 'required|date',
            'estimated_duration' => 'nullable|integer|min:1',
            'ticket_id' => 'nullable|exists:tickets,id',
            'user_id' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $activityData = $request->all();
        
        // If no user_id provided, use authenticated user
        if (!isset($activityData['user_id'])) {
            $activityData['user_id'] = auth()->user()->id;
        }

        // Check if user can create activity for another user
        if ($activityData['user_id'] != auth()->user()->id && 
            !user_has_any_role(auth()->user(), ['admin', 'super-admin', 'management'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create activities for other users'
            ], 403);
        }

        $activity = DailyActivity::create($activityData);
        $activity->load(['user', 'ticket']);

        return response()->json([
            'success' => true,
            'data' => $this->transformActivity($activity, true),
            'message' => 'Daily activity created successfully'
        ], 201);
    }

    /**
     * Display the specified daily activity
     *
     * @param DailyActivity $dailyActivity
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(DailyActivity $dailyActivity)
    {
        // Check permissions
        if (!$this->canViewActivity($dailyActivity)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this activity'
            ], 403);
        }

        $dailyActivity->load(['user', 'ticket']);

        return response()->json([
            'success' => true,
            'data' => $this->transformActivity($dailyActivity, true),
            'message' => 'Daily activity retrieved successfully'
        ]);
    }

    /**
     * Update the specified daily activity
     *
     * @param Request $request
     * @param DailyActivity $dailyActivity
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, DailyActivity $dailyActivity)
    {
        // Check permissions
        if (!$this->canEditActivity($dailyActivity)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this activity'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'activity_type' => 'sometimes|in:ticket_work,asset_maintenance,general_task,meeting,training',
            'activity_date' => 'sometimes|date',
            'estimated_duration' => 'nullable|integer|min:1',
            'actual_duration' => 'nullable|integer|min:1',
            'is_completed' => 'sometimes|boolean',
            'completion_notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $dailyActivity->update($request->all());
        $dailyActivity->load(['user', 'ticket']);

        return response()->json([
            'success' => true,
            'data' => $this->transformActivity($dailyActivity, true),
            'message' => 'Daily activity updated successfully'
        ]);
    }

    /**
     * Remove the specified daily activity
     *
     * @param DailyActivity $dailyActivity
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(DailyActivity $dailyActivity)
    {
        // Check permissions
        if (!$this->canEditActivity($dailyActivity)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this activity'
            ], 403);
        }

        $dailyActivity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Daily activity deleted successfully'
        ]);
    }

    /**
     * Mark activity as completed
     *
     * @param Request $request
     * @param DailyActivity $dailyActivity
     * @return \Illuminate\Http\JsonResponse
     */
    public function markCompleted(Request $request, DailyActivity $dailyActivity)
    {
        // Check permissions
        if (!$this->canEditActivity($dailyActivity)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to update this activity'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'actual_duration' => 'nullable|integer|min:1',
            'completion_notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $dailyActivity->markCompleted(
            $request->actual_duration,
            $request->completion_notes
        );

        return response()->json([
            'success' => true,
            'data' => $this->transformActivity($dailyActivity->fresh()),
            'message' => 'Activity marked as completed successfully'
        ]);
    }

    /**
     * Get activities for specific user
     *
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserActivities(User $user, Request $request)
    {
        // Check permissions
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin', 'management']) && auth()->user()->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view activities for this user'
            ], 403);
        }

        $query = DailyActivity::where('user_id', $user->id)
                             ->with(['ticket']);

        // Apply filters
        if ($request->has('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }

        if ($request->has('date_from')) {
            $query->where('activity_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('activity_date', '<=', $request->date_to);
        }

        if ($request->has('is_completed')) {
            $query->where('is_completed', $request->boolean('is_completed'));
        }

        $query->orderBy('activity_date', 'desc');

        $perPage = $request->get('per_page', 15);
        $activities = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $activities,
            'message' => 'User activities retrieved successfully'
        ]);
    }

    /**
     * Get activity summary for specific user
     *
     * @param User $user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserSummary(User $user, Request $request)
    {
        // Check permissions
        if (!user_has_any_role(auth()->user(), ['admin', 'super-admin', 'management']) && auth()->user()->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view activity summary for this user'
            ], 403);
        }

        $days = $request->get('days', 30);
        $summary = $user->getUserActivitySummary($days);

        return response()->json([
            'success' => true,
            'data' => $summary,
            'message' => 'User activity summary retrieved successfully'
        ]);
    }

    /**
     * Transform activity data
     *
     * @param DailyActivity $activity
     * @param bool $detailed
     * @return array
     */
    private function transformActivity(DailyActivity $activity, $detailed = false)
    {
        $data = [
            'id' => $activity->id,
            'title' => $activity->title,
            'description' => $activity->description,
            'activity_type' => $activity->activity_type,
            'activity_date' => $activity->activity_date,
            'estimated_duration' => $activity->estimated_duration,
            'actual_duration' => $activity->actual_duration,
            'is_completed' => $activity->is_completed,
            'completion_notes' => $activity->completion_notes,
            'user' => [
                'id' => $activity->user->id,
                'name' => $activity->user->name,
                'email' => $activity->user->email
            ],
            'ticket' => $activity->ticket ? [
                'id' => $activity->ticket->id,
                'title' => $activity->ticket->title,
                'status' => $activity->ticket->status->name ?? null
            ] : null,
            'type_badge' => $activity->type_badge,
            'formatted_duration' => $activity->formatted_duration,
            'is_today' => $activity->is_today,
            'created_at' => $activity->created_at,
            'updated_at' => $activity->updated_at
        ];

        return $data;
    }

    /**
     * Check if user can view activity
     *
     * @param DailyActivity $activity
     * @return bool
     */
    private function canViewActivity(DailyActivity $activity)
    {
        $user = auth()->user();
        
        return user_has_any_role($user, ['admin', 'super-admin', 'management']) || 
               $activity->user_id == $user->id;
    }

    /**
     * Check if user can edit activity
     *
     * @param DailyActivity $activity
     * @return bool
     */
    private function canEditActivity(DailyActivity $activity)
    {
        $user = auth()->user();
        
        return user_has_any_role($user, ['admin', 'super-admin', 'management']) || 
               $activity->user_id == $user->id;
    }
}