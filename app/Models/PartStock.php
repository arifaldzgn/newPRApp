<?php

namespace App\Models;

use App\Models\partList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PartStock extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function partList()
    {
        return $this->belongsTo(partList::class);
    }
}
