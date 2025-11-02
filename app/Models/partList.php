<?php

namespace App\Models;

use App\Models\PartStock;
use App\Models\PartListLogHistories;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class partList extends Model
{
    use HasFactory;

    protected $guarded =
    [
        'id'
    ];

    public function assetCode()
    {
        return $this->belongsTo(assetCode::class);
    }

    public function PartStock()
    {
        return $this->hasMany(PartStock::class);
    }

    protected static function boot()
    {
        parent::boot();

        self::created(function($part) {

            $userId = auth()->user() ? auth()->user()->id : 1;

            PartListLogHistories::create([
                'part_list_id' => $part->id,
                'asset_code_id' => $part->asset_code_id,
                'part_name' => $part->part_name,
                'category' => $part->category,
                'UoM' => $part->UoM,
                'type' => $part->type,
                'action' => 'create',
                'user_id' => $userId,
            ]);
        });

        self::updated(function($part) {
            $changes = $part->getChanges();
            $oldData = array_intersect_key($part->getOriginal(), $changes);

            $userId = auth()->user() ? auth()->user()->id : 1;

            PartListLogHistories::create([
                'part_list_id' => $part->id,
                'asset_code_id' => $part->asset_code_id,
                'part_name' => $part->part_name,
                'category' => $part->category,
                'UoM' => $part->UoM,
                'type' => $part->type,
                'action' => 'update',
                'user_id' => $userId,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($changes),
            ]);
        });


    }
}
