<?php

namespace App\Models;

use App\Models\partList;
use App\Models\prTicket;
use App\Models\PrLogHistory;
use App\Models\PrDocument;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class prRequest extends Model
{
    use HasFactory;
    use SoftDeletes;

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

    public function documents()
    {
        return $this->hasMany(PrDocument::class);
    }


}
