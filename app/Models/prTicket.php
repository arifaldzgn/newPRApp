<?php

namespace App\Models;

use App\Models\PrLogHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class prTicket extends Model
{
    use HasFactory;

    protected $guarded =
    [
        'id'
    ];

    public function prRequest()
    {
        return $this->hasMany(prRequest::class, 'ticket_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function PrLogHistory()
    {
        return $this->hasMany(PrLogHistory::class, 'id', 'row_id');
    }

    protected static function boot()
    {
        parent::boot();

        self::created(function ($prTicket) {
            $prTicket->logHistory('created', null, $prTicket->toArray(), null);
        });

        self::updated(function ($prTicket) {
            $original = $prTicket->getOriginal();
            $changes = $prTicket->getChanges();
            $prTicket->logHistory('updated', $original, $changes, $prTicket->toArray());
        });

        self::deleted(function ($prTicket) {
            $prTicket->logHistory('deleted', $prTicket->toArray(), null, null);
        });
    }

    public function logHistory($action, $oldData, $changes, $newData)
    {
        PrLogHistory::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'table_name' => 'PR_TICKETS',
            'row_id' => $this->id,
            'new_data' => json_encode($newData),
        ]);
    }
}
