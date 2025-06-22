<?php

namespace App\Models;

use App\Models\User;
use App\Models\assetCode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;

class deptList extends Model
{
    use HasFactory;

    protected $guarded =
    [
        'id'
    ];

    public function user()
    {
        return $this->hasMany(User::class, 'dept_id');
    }

    public function assetCode()
    {
        return $this->hasMany(assetCode::class, 'dept_code', 'dept_code');
    }

    public function hod()
    {
        return $this->belongsTo(User::class, 'user_hod_id', 'id');
    }
}
