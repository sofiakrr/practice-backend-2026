<?php
namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Booking;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReviewController extends Controller
{

    public function index(Request $request, Resource $resource)
    {
        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
            'rating' => 'sometimes|integer|min:1|max:5',
            'sort_by' => 'sometimes|in:created_at,rating',
            'sort_order' => 'sometimes|in:asc,desc',
        ]);

        $perPage = $request->get('per_page', 10);
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');

        $reviews = Review::where('resource_id', $resource->id)
            ->with('user:id,name');

        if ($request->filled('rating')) {
            $reviews->where('rating', $request->rating);
        }

        $reviews = $reviews
            ->orderBy($sortBy, $sortOrder)
            ->paginate($perPage);

        $avgRating = Review::where('resource_id', $resource->id)->avg('rating');

        return response()->json([
            'resource'       => $resource->name,
            'average_rating' => round($avgRating, 1),
            'reviews'        => $reviews,
        ]);
    }

    public function store(Request $request, Resource $resource)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:1000',
        ]);

        $user    = auth('api')->user();
        $booking = Booking::findOrFail($request->booking_id);

        if ($booking->user_id !== $user->id) {
            return response()->json([
                'message' => 'Вы можете оставить отзыв только по своему бронированию.'
            ], 403);
        }

        if ($booking->resource_id !== $resource->id) {
            return response()->json([
                'message' => 'Бронирование не соответствует данному ресурсу.'
            ], 422);
        }

        $bookingEnd = $booking->date . ' ' . $booking->end_time;
        if (now() < now()->parse($bookingEnd)) {
            return response()->json([
                'message' => 'Отзыв можно оставить только после завершения бронирования.'
            ], 422);
        }

        $exists = Review::where('booking_id', $booking->id)
            ->where('user_id', $user->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Вы уже оставили отзыв по этому бронированию.'
            ], 422);
        }

        $review = Review::create([
            'user_id'     => $user->id,
            'booking_id'  => $booking->id,
            'resource_id' => $resource->id,
            'rating'      => $request->rating,
            'comment'     => $request->comment,
        ]);

        Log::info('Отзыв добавлен', [
            'user_id'     => $user->id,
            'resource_id' => $resource->id,
            'booking_id'  => $booking->id,
            'rating'      => $request->rating,
        ]);

        return response()->json($review->load('user:id,name'), 201);
    }
}
