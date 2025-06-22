<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\User;

class RoomEvent extends Model
{
    /** @use HasFactory<\Database\Factories\RoomEventFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'approved_user_id',
        'title',
        'category',
        'room',
        'date',
        'time_from',
        'time_to',
        'requested_by',
        'remark',
        'status',
    ];

    protected $appends = ['is_closed'];

    public function getIsClosedAttribute()
    {
        $now = Carbon::now();
        $endDateTime = Carbon::parse("{$this->date} {$this->time_to}");
        return $now->gt($endDateTime);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
