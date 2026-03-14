<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'sometimes|in:active,cancelled',
            'resource_id' => 'sometimes|integer|exists:resources,id',
            'user_id' => 'sometimes|integer|exists:users,id',
            'date_from' => 'sometimes|date',
            'date_to' => 'sometimes|date|after_or_equal:date_from',
            'sort_by' => 'sometimes|in:date,start_time,created_at',
            'sort_order' => 'sometimes|in:asc,desc',
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $user = auth('api')->user();
        $query = Booking::query();

        if ($user->isAdmin()) {
            $query->with(['user', 'resource']);

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
        } else {
            $query->with(['resource'])
                ->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('resource_id')) {
            $query->where('resource_id', $request->resource_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $sortBy = $request->get('sort_by', 'date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder)
            ->orderBy('start_time', 'asc');

        $perPage = $request->get('per_page', 10);
        $bookings = $query->paginate($perPage);

        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'date'        => 'required|date|after_or_equal:today',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);

        $user = auth('api')->user();

        $resource = Resource::findOrFail($request->resource_id);
        if (!$resource->is_active) {
            return response()->json([
                'message' => 'Этот столик недоступен для бронирования.'
            ], 422);
        }

        $conflict = Booking::where('resource_id', $request->resource_id)
            ->where('date', $request->date)
            ->where('status', 'active')
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->end_time)
                      ->where('end_time', '>', $request->start_time);
                });
            })
            ->exists();

        if ($conflict) {
            Log::warning('Конфликт бронирования', [
                'user_id'     => $user->id,
                'resource_id' => $request->resource_id,
                'date'        => $request->date,
                'start_time'  => $request->start_time,
                'end_time'    => $request->end_time,
                'reason'      => 'Пересечение времени с существующим бронированием',
            ]);

            return response()->json([
                'message' => 'Этот столик уже забронирован на указанное время. Выберите другое время.'
            ], 422);
        }

        $booking = Booking::create([
            'user_id'     => $user->id,
            'resource_id' => $request->resource_id,
            'date'        => $request->date,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'status'      => 'active',
        ]);

        Log::info('Бронирование создано', [
            'user_id'    => $user->id,
            'booking_id' => $booking->id,
            'resource'   => $resource->name,
            'date'       => $request->date,
            'time'       => $request->start_time . ' - ' . $request->end_time,
        ]);

        return response()->json($booking->load('resource'), 201);
    }

    public function destroy($id)
    {
        $user    = auth('api')->user();
        $booking = Booking::findOrFail($id);

        if ($booking->status === 'cancelled') {
            return response()->json([
                'message' => 'Бронирование уже отменено.'
            ], 422);
        }

        if (!$user->isAdmin() && $booking->user_id !== $user->id) {
            Log::warning('Попытка отменить чужое бронирование', [
                'initiator_user_id' => $user->id,
                'booking_id'        => $booking->id,
                'owner_user_id'     => $booking->user_id,
                'reason'            => 'Нет прав на отмену чужого бронирования',
            ]);

            return response()->json([
                'message' => 'Вы можете отменить только своё бронирование.'
            ], 403);
        }

        $booking->update(['status' => 'cancelled']);

        Log::info('Бронирование отменено', [
            'initiator_user_id' => $user->id,
            'booking_id'        => $booking->id,
            'role'              => $user->role,
        ]);

        return response()->json(['message' => 'Бронирование отменено.']);
    }
}
