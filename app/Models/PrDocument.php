<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrDocument extends Model
{
    protected $table = 'pr_documents';

    protected $fillable = [
        'pr_request_id',
        'document_type',
        'file_name',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
    ];

    public function prRequest()
    {
        return $this->belongsTo(PrRequest::class);
    }
}
