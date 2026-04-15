<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\DailyActivity;
use App\Http\Requests\DailyActivityRequest;
use Illuminate\Http\Request;

class DailyActivityApiController extends Controller
{
    public function index(Request $request)
    {
        $query = DailyActivity::with('user')
                    ->orderBy('activity_date', 'desc');

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date')) {
            $query->whereDate('activity_date', $request->date);
        }

        $perPage = (int) $request->get('per_page', 20);

        $data = $query->paginate($perPage);

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function store(DailyActivityRequest $request)
    {
        $payload = $request->validated();
        $payload['user_id'] = auth()->id();

        $activity = DailyActivity::create($payload);

        return response()->json(['success' => true, 'data' => $activity], 201);
    }

    public function show(DailyActivity $dailyActivity)
    {
        $dailyActivity->load('user');
        return response()->json(['success' => true, 'data' => $dailyActivity]);
    }

    public function update(DailyActivityRequest $request, DailyActivity $dailyActivity)
    {
        $this->authorize('update', $dailyActivity);

        $dailyActivity->update($request->validated());

        return response()->json(['success' => true, 'data' => $dailyActivity]);
    }

    public function destroy(DailyActivity $dailyActivity)
    {
        $this->authorize('delete', $dailyActivity);
        $dailyActivity->delete();
        return response()->json(['success' => true]);
    }
}
