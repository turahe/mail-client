<?php

namespace Turahe\MailClient\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Turahe\Core\Concerns\HasConfigurablePrimaryKey;

class EmailAccountMessageHeader extends Model
{
    use HasConfigurablePrimaryKey, HasFactory;

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Turahe\MailClient\Tests\Factories\EmailAccountMessageHeaderFactory::new();
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
    protected $fillable = ['name', 'value', 'header_type', 'message_id'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'message_id' => 'string',
    ];

    /**
     * Get the mapped attribute
     *
     * We will map the header into a appropriate header class
     */
    protected function mapped(): Attribute
    {
        return Attribute::get(
            fn () => new $this->header_type($this->name, $this->value)
        );
    }
}
