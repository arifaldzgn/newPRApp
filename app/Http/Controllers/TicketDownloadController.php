<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\prTicket;
use Illuminate\Support\Facades\Storage;

class TicketDownloadController extends Controller
{
    //
    public function listApprovedTickets()
    {
        try {
            $tickets = prTicket::where('status', 'Approved')
                ->orderBy('date_purchasing_approval', 'desc')
                ->get(['id', 'ticketCode', 'date_purchasing_approval']);

            // Build download URL for each ticket
            $tickets = $tickets->map(function($ticket) {
                return [
                    'id' => $ticket->id,
                    'ticketCode' => $ticket->ticketCode,
                    'approved_at' => $ticket->date_purchasing_approval,
                    'download_url' => url("/printTicket/".urlencode($ticket->ticketCode))
                ];
            });

            return response()->json([
                'message' => 'List of approved tickets',
                'tickets' => $tickets
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
