<?php
namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    public function index(Request $request)
    {
        $query = Resource::where('is_active', true)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
        }

        if ($request->has('max_price')) {
            $query->where('price_per_hour', '<=', $request->max_price);
        }

        if ($request->has('location')) {
            $query->where('location', 'like', '%' . $request->location . '%');
        }

        $sortBy    = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'asc');
        $allowedSorts = ['id', 'price_per_hour', 'capacity', 'reviews_avg_rating'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = $request->get('per_page', 10);
        $resources = $query->paginate($perPage);

        return response()->json($resources);
    }

    public function show(Resource $resource)
    {
        $resource->loadCount('reviews');
        $resource->loadAvg('reviews', 'rating');
        return response()->json($resource);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'type'           => 'required|string',
            'capacity'       => 'required|integer|min:1',
            'location'       => 'required|string',
            'price_per_hour' => 'required|numeric|min:0',
            'description'    => 'nullable|string',
        ]);

        $resource = Resource::create($request->all());
        return response()->json($resource, 201);
    }

    public function update(Request $request, Resource $resource)
    {
        $request->validate([
            'name'           => 'sometimes|string|max:255',
            'type'           => 'sometimes|string',
            'capacity'       => 'sometimes|integer|min:1',
            'location'       => 'sometimes|string',
            'price_per_hour' => 'sometimes|numeric|min:0',
            'description'    => 'nullable|string',
            'is_active'      => 'sometimes|boolean',
        ]);

        $resource->update($request->all());
        return response()->json($resource);
    }

    public function destroy(Resource $resource)
    {
        $resource->delete();
        return response()->json(['message' => 'Столик удалён']);
    }

    public function schedule(Request $request, Resource $resource)
    {
        $request->validate([
            'date' => 'required|date',
            'mode' => 'sometimes|in:day,week',
        ]);

        $mode = $request->get('mode', 'day');
        $date = $request->date;

        if ($mode === 'week') {
            $startDate = now()->parse($date)->startOfWeek()->format('Y-m-d');
            $endDate   = now()->parse($date)->endOfWeek()->format('Y-m-d');

            $bookings = $resource->bookings()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('status', 'active')
                ->orderBy('date')
                ->orderBy('start_time')
                ->get(['id', 'date', 'start_time', 'end_time', 'status']);

            return response()->json([
                'resource' => $resource->name,
                'mode'     => 'week',
                'from'     => $startDate,
                'to'       => $endDate,
                'bookings' => $bookings,
            ]);
        }

        $bookings = $resource->bookings()
            ->where('date', $date)
            ->where('status', 'active')
            ->orderBy('start_time')
            ->get(['id', 'date', 'start_time', 'end_time', 'status']);

        return response()->json([
            'resource' => $resource->name,
            'mode'     => 'day',
            'date'     => $date,
            'bookings' => $bookings,
        ]);
    }

    public function available(Request $request)
    {
        $request->validate([
            'date'       => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'capacity'   => 'sometimes|integer|min:1',
            'type'       => 'sometimes|string',
            'sort_by'    => 'sometimes|in:id,price_per_hour,capacity,reviews_avg_rating',
            'sort_order' => 'sometimes|in:asc,desc',
            'per_page'   => 'sometimes|integer|min:1|max:100',
        ]);

        $busyIds = \App\Models\Booking::where('date', $request->date)
            ->where('status', 'active')
            ->where('start_time', '<', $request->end_time)
            ->where('end_time', '>', $request->start_time)
            ->pluck('resource_id');

        $query = Resource::where('is_active', true)
            ->whereNotIn('id', $busyIds)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        if ($request->has('capacity')) {
            $query->where('capacity', '>=', $request->capacity);
        }
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $sortBy = $request->get('sort_by', 'id');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage   = $request->get('per_page', 10);
        $resources = $query->paginate($perPage);

        return response()->json([
            'date'       => $request->date,
            'start_time' => $request->start_time,
            'end_time'   => $request->end_time,
            'available'  => $resources,
        ]);
    }
}
