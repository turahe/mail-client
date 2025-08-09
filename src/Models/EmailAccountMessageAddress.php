<?php

namespace Turahe\MailClient\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailAccountMessageAddress extends Model
{
    use HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Turahe\MailClient\Tests\Factories\EmailAccountMessageAddressFactory::new();
    }

    /**
     * Indicates if the model has timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['address', 'name', 'address_type'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'message_id' => 'string',
    ];
}
