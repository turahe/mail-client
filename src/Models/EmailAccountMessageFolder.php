<?php

namespace Turahe\MailClient\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class EmailAccountMessageFolder extends Pivot
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Turahe\MailClient\Tests\Factories\EmailAccountMessageFolderFactory::new();
    }

    /**
     * Indicates if the model has timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'message_id' => 'string',
        'folder_id' => 'string',
    ];
}
