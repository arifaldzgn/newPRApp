<?php

namespace App\Models;

use App\Models\User;
use App\Models\prTicket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrLogHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'table_name',
        'row_id',
        'old_data',
        'new_data',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prTicket()
    {
        return $this->belongsTo(prTicket::class, 'row_id');
    }
}
