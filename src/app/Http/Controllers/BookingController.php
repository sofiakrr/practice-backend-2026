<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    // Список бронирований
    public function index()
    {
        $user = auth('api')->user();

        if ($user->isAdmin()) {
            $bookings = Booking::with(['user', 'resource'])->get();
        } else {
            $bookings = Booking::with(['resource'])
                ->where('user_id', $user->id)
                ->get();
        }

        return response()->json($bookings);
    }

    // Создать бронирование
    public function store(Request $request)
    {
        $request->validate([
            'resource_id' => 'required|exists:resources,id',
            'date'        => 'required|date|after_or_equal:today',
            'start_time'  => 'required|date_format:H:i',
            'end_time'    => 'required|date_format:H:i|after:start_time',
        ]);

        $user = auth('api')->user();

        // Проверка: активен ли ресурс
        $resource = Resource::findOrFail($request->resource_id);
        if (!$resource->is_active) {
            return response()->json([
                'message' => 'Этот столик недоступен для бронирования.'
            ], 422);
        }

        // Проверка пересечений по времени (ключевая бизнес-логика)
        $conflict = Booking::where('resource_id', $request->resource_id)
            ->where('date', $request->date)
            ->where('status', 'active')
            ->where(function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    // Новое начало внутри существующего интервала
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

    // Отмена бронирования
    public function destroy($id)
    {
        $user    = auth('api')->user();
        $booking = Booking::findOrFail($id);

        // Уже отменено
        if ($booking->status === 'cancelled') {
            return response()->json([
                'message' => 'Бронирование уже отменено.'
            ], 422);
        }

        // Обычный user может отменить только своё
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
