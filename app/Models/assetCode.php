<?php

namespace App\Models;

use App\Models\deptList;
use App\Models\partList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class assetCode extends Model
{
    use HasFactory;

    protected $guarded =
    [
        'id'
    ];

    public function deptList()
    {
        return $this->belongsTo(deptList::class, 'dept_code', 'dept_code');
    }

    public function partList()
    {
        return $this->hasMany(partList::class);
    }
}
