<?php

namespace Turahe\MailClient\Models;

use Illuminate\Database\Eloquent\Model;

class MessageLinksClick extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['url'];
}
