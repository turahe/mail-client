<?php

namespace Turahe\MailClient\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Turahe\Core\Concerns\HasConfigurablePrimaryKey;

class PredefinedMailTemplate extends Model
{
    use HasConfigurablePrimaryKey, HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Turahe\MailClient\Tests\Factories\PredefinedMailTemplateFactory::new();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['name', 'subject', 'body', 'is_shared'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_shared' => 'boolean',
    ];

    /**
     * Scope a query to only include templates visible for the user.
     */
    public function scopeVisibleToUser(Builder $query, ?int $userId = null): void
    {
        $query->where('is_shared', true);
    }

    /**
     * Scope a query to only include shared templates.
     */
    public function scopeShared(Builder $query): void
    {
        $query->where('is_shared', true);
    }
}
