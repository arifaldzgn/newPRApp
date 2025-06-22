<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PartListLogHistories extends Model
{
    use HasFactory;

    protected $fillable = [
        'part_list_id',
        'asset_code_id',
        'part_name',
        'category',
        'UoM',
        'type',
        'action',
        'user_id',
        'old_data',
        'new_data'
    ];
}
