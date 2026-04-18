<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterDataImportBatch extends Model
{
    protected $fillable = [
        'school_key',
        'mode',
        'scope',
        'status',
        'filename',
        'storage_path',
        'checksum',
        'actor_id',
        'summary_json',
        'errors_json',
        'warnings_json',
        'error_report_path',
    ];

    protected $casts = [
        'summary_json' => 'array',
        'errors_json' => 'array',
        'warnings_json' => 'array',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
