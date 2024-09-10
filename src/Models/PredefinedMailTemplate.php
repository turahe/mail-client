<?php

namespace Turahe\MailClient\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class PredefinedMailTemplate extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'subject', 'body', 'is_shared', 'user_id'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_shared' => 'boolean',
        'user_id' => 'int',
    ];

    /**
     * Get the template owner
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Scope a query to only include templates visible for the user.
     */
    public function scopeVisibleToUser(Builder $query, ?int $userId = null): void
    {
        $query->where(function ($query) use ($userId) {
            $query->where('user_id', $userId ?: Auth::id())->orWhere('is_shared', true);
        });
    }

    /**
     * Scope a query to only include shared templates.
     */
    public function scopeShared(Builder $query): void
    {
        $query->where('is_shared', true);
    }
}
