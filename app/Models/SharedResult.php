<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SharedResult extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'session_id',
        'result_title',
        'result_text',
        'stress_score',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * `metadata` is cast to array so Laravel automatically
     * JSON-encodes on write and JSON-decodes on read.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'stress_score' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Bootstrap the model and its traits.
     *
     * Automatically generates a secure UUID (v4) via `Str::uuid()`
     * before the record is persisted for the first time, ensuring
     * the public share URL never leaks the internal auto-increment ID.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (SharedResult $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // -------------------------------------------------------------------------
    // Accessors / Helpers
    // -------------------------------------------------------------------------

    /**
     * Return the full public share URL for this result.
     */
    public function getShareUrlAttribute(): string
    {
        return route('share.show', ['uuid' => $this->uuid]);
    }
}
