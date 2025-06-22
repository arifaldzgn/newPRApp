<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomEvent;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class BookingRoomController extends Controller
{
    //

    public function index()
    {
        $now = \Carbon\Carbon::now();

        $events = \App\Models\RoomEvent::all()->map(function ($event) use ($now) {
            $eventDateTime = \Carbon\Carbon::parse($event->date . ' ' . $event->time_to);
            $status = $event->status;

            if ($now > $eventDateTime) {
                $status = 'Closed';
            }

            return [
                'id' => $event->id,
                'room' => $event->room,
                'title' => $event->title,
                'category' => $event->category,
                'date' => $event->date,
                'time_from' => $event->time_from,
                'time_to' => $event->time_to,
                'requested_by' => $event->requested_by,
                'status' => $status,
            ];
        });

        $active = $events->filter(fn ($e) => $e['status'] !== 'Closed')->values();
        $past = $events->filter(fn ($e) => $e['status'] === 'Closed')->values();

        return view('bookingroom.create_br', [
            'dataT' => [
                'active' => $active,
                'past' => $past,
            ],
        ]);
    }


    public function getEvents()
    {
        $events = RoomEvent::all()->map(function ($event) {
            // Determine status based on date/time
            $now = Carbon::now();
            $eventDateTime = Carbon::parse($event->date . ' ' . $event->time_to);
            
            $status = $event->status;
            if ($now > $eventDateTime) {
                $status = 'Closed';
            }

            // Determine color based on status
            $className = '';
            if ($status === 'Approved') {
                $className = 'bg-success';
            } elseif ($status === 'Pending') {
                $className = 'bg-warning';
            } elseif ($status === 'Closed') {
                $className = 'bg-danger';
            } elseif ($status === 'Rejected') {
                $className = 'bg-secondary';
            }

            return [
                'id' => $event->id,
                'title' => $event->title,
                'start' => $event->date . 'T' . $event->time_from,
                'end' => $event->date . 'T' . $event->time_to,
                'allDay' => false,
                'className' => $className,
                'extendedProps' => [
                    'room' => $event->room,
                    'requestedBy' => $event->requested_by,
                    'timeFrom' => $event->time_from,
                    'timeTo' => $event->time_to,
                    'status' => $status,
                    'userId' => $event->user_id,
                ],
            ];
        });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'id' => 'nullable|exists:room_events,id',
            'title' => 'required|string|max:255',
            'category' => 'nullable|string|max:50',
            'room' => 'required|string|max:255',
            'date' => 'required|date',
            'time_from' => 'required',
            'time_to' => 'required',
            'requested_by' => 'string|max:255',
            'status' => 'nullable|string|max:50',
            'remark' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        if (isset($validated['id'])) {
            $event = RoomEvent::findOrFail($validated['id']);
            
            // Check if user can edit (owner or admin)
            if ($event->user_id !== $user->id && $user->role !== 'admin') {
                return response()->json(['success' => false, 'message' => 'Unauthorized to edit this event'], 403);
            }

            // If status is being changed to Approved, record who approved it
            if ($request->status === 'Approved' && $event->status !== 'Approved') {
                $validated['approved_user_id'] = $user->id;
            }

            $event->update($validated);
        } else {
            // New event - set user_id and default status
            $validated['user_id'] = $user->id;
            $validated['approved_user_id'] = 1; // No approver for new events
            $validated['requested_by'] = $user->name; // Set requested_by to current user's name
            $validated['status'] = 'Pending'; // Default status for new events
            
            $event = RoomEvent::create($validated);
        }

        return response()->json(['success' => true, 'event' => $event]);
    }

    public function destroy($id)
    {
        $event = RoomEvent::findOrFail($id);
        $user = Auth::user();

        // Check if user can delete (owner or admin)
        if ($event->user_id !== $user->id && $user->role !== 'admin') {
            return response()->json(['success' => false, 'message' => 'Unauthorized to delete this event'], 403);
        }

        $event->delete();

        return response()->json(['success' => true]);
    }
}