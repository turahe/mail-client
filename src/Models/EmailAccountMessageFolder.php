<?php

namespace Turahe\MailClient\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Turahe\Core\Concerns\HasConfigurablePrimaryKey;

class EmailAccountMessageFolder extends Pivot
{
    use HasConfigurablePrimaryKey;
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'email_account_message_folders';

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
