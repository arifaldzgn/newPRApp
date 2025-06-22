<?php

namespace App\Models;

use App\Models\partList;
use App\Models\prTicket;
use App\Models\PrLogHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class prRequest extends Model
{
    use HasFactory;

    protected $guarded =
    [
        'id'
    ];

    public function partList()
    {
        return $this->belongsTo(partList::class, 'partlist_id');
    }

    public function prTicket()
    {
        return $this->belongsTo(prTicket::class ,'ticket_id', 'id');
    }


}
