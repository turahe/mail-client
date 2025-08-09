<?php

namespace Turahe\MailClient\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Turahe\MailClient\Support\EmailAccountMessageBody;
use Turahe\Media\HasMedia;

/**
 * @method static \Illuminate\Database\Eloquent\Builder|EmailAccountMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailAccountMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EmailAccountMessage query()
 *
 * @mixin \Eloquent
 */
class EmailAccountMessage extends Model
{
    use HasMedia;
    use HasUlids;

    const TAGS_TYPE = 'messages';

    const ATTACHMENTS_MEDIA_TAG = 'attachments';

    const EMBEDDED_ATTACHMENTS_MEDIA_TAG = 'embedded-attachments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email_account_id', 'remote_id', 'message_id',
        'subject', 'html_body', 'text_body', 'is_read',
        'is_draft', 'date', 'is_sent_via_app', 'hash',
    ];

    /**
     * The attributes that should be cast.
     *
     * Proper casts must be added to ensure the isDirty() method works fine
     * when checking whether the message is updated to broadcast to the front-end via sync
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
        'is_draft' => 'boolean',
        'is_read' => 'boolean',
        'is_sent_via_app' => 'boolean',
        'email_account_id' => 'string',
        'clicks' => 'int',
        'clicked_at' => 'datetime',
        'opens' => 'int',
        'opened_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    /**
     * Perform any actions required after the model boots.
     */
    protected static function booted(): void
    {
        static::deleting(function (EmailAccountMessage $model) {
            $model->purge();
        });
    }

    /**
     * A messages belongs to email account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(\Turahe\MailClient\Models\EmailAccount::class, 'email_account_id');
    }

    /**
     * A message belongs to many folders
     */
    public function folders(): BelongsToMany
    {
        return $this->belongsToMany(
            \Turahe\MailClient\Models\EmailAccountFolder::class,
            'email_account_message_folders',
            'message_id',
            'folder_id'
        )
            ->using(\Turahe\MailClient\Models\EmailAccountMessageFolder::class);
    }

    /**
     * A message has many addresses
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(\Turahe\MailClient\Models\EmailAccountMessageAddress::class, 'message_id');
    }

    /**
     * A message can have many contacts.
     */
    public function contacts(): MorphToMany
    {
        // Check if the class exists to avoid errors in testing
        if (class_exists('\Turahe\Contacts\Models\Contact')) {
            return $this->morphedByMany(
                \Turahe\Contacts\Models\Contact::class,
                'messageable',
                'email_account_messageables',
                'message_id'
            );
        }
        
        // Return empty morph relationship if class doesn't exist
        return $this->morphedByMany(
            \Turahe\MailClient\Tests\Models\TestModel::class,
            'messageable',
            'email_account_messageables',
            'message_id'
        )->whereRaw('0 = 1');
    }

    /**
     * A message can have many companies.
     */
    public function companies(): MorphToMany
    {
        // Check if the class exists to avoid errors in testing
        if (class_exists('\Turahe\Contacts\Models\Company')) {
            return $this->morphedByMany(
                \Turahe\Contacts\Models\Company::class,
                'messageable',
                'email_account_messageables',
                'message_id'
            );
        }
        
        // Return empty morph relationship if class doesn't exist
        return $this->morphedByMany(
            \Turahe\MailClient\Tests\Models\TestModel::class,
            'messageable',
            'email_account_messageables',
            'message_id'
        )->whereRaw('0 = 1');
    }

    /**
     * A message can have many deals.
     */
    public function deals(): MorphToMany
    {
        // Check if the class exists to avoid errors in testing
        if (class_exists('\Turahe\Deals\Models\Deal')) {
            return $this->morphedByMany(
                \Turahe\Deals\Models\Deal::class,
                'messageable',
                'email_account_messageables',
                'message_id'
            );
        }
        
        // Return empty morph relationship if class doesn't exist
        return $this->morphedByMany(
            \Turahe\MailClient\Tests\Models\TestModel::class,
            'messageable',
            'email_account_messageables',
            'message_id'
        )->whereRaw('0 = 1');
    }

    /**
     * A message from address
     */
    public function from(): HasOne
    {
        return $this->hasOne(
            \Turahe\MailClient\Models\EmailAccountMessageAddress::class,
            'message_id'
        )->where('address_type', 'from');
    }

    /**
     * A message sender address
     */
    public function sender(): HasOne
    {
        return $this->hasOne(
            \Turahe\MailClient\Models\EmailAccountMessageAddress::class,
            'message_id'
        )->where('address_type', 'sender');
    }

    /**
     * A message to address
     */
    public function to(): HasMany
    {
        return $this->addresses()->where('address_type', 'to');
    }

    /**
     * A message cc address
     */
    public function cc(): HasMany
    {
        return $this->addresses()->where('address_type', 'cc');
    }

    /**
     * A message bcc address
     */
    public function bcc(): HasMany
    {
        return $this->addresses()->where('address_type', 'bcc');
    }

    /**
     * A message replyTo address
     */
    public function replyTo(): HasMany
    {
        return $this->addresses()->where('address_type', 'replyTo');
    }

    /**
     * A message has many headers
     */
    public function headers(): HasMany
    {
        return $this->hasMany(\Turahe\MailClient\Models\EmailAccountMessageHeader::class, 'message_id');
    }

    /**
     * A message has many link clicks
     */
    public function linksClicks(): HasMany
    {
        return $this->hasMany(\Turahe\MailClient\Models\MessageLinksClick::class, 'message_id');
    }

    /**
     * Get the timeline sort column.
     */
    public function getTimelineSortColumn(): string
    {
        return 'date';
    }

    /**
     * Get the message attachments excluding the inline
     */
    public function attachments(): MorphToMany
    {
        return static::media()->wherePivot('tag', '!=', static::EMBEDDED_ATTACHMENTS_MEDIA_TAG);
    }

    /**
     * Get the message inline attachments
     */
    public function inlineAttachments(): MorphToMany
    {
        return static::media()->wherePivot('tag', '=', static::EMBEDDED_ATTACHMENTS_MEDIA_TAG);
    }

    /**
     * Determine if the message is a reply
     */
    public function isReply(): bool
    {
        return ! is_null($this->headers->firstWhere('name', 'in-reply-to'))
            || ! is_null($this->headers->firstWhere('name', 'references'));
    }

    /**
     * Get the previewText attribute
     */
    protected function previewText(): Attribute
    {
        return Attribute::get(
            fn () => $this->body()->previewText()
        );
    }

    /**
     * Get the visibleText attribute without any quoted content
     *
     * NOTE: Sometimes the EmailParser may fail because it won't be able
     * to recognize the quoted text and will return empty message
     * In this case, just return the original preview text
     */
    protected function visibleText(): Attribute
    {
        return Attribute::get(
            fn () => $this->body()->visibleText()
        );
    }

    /**
     * Get the hiddenText attribute
     */
    protected function hiddenText(): Attribute
    {
        return Attribute::get(
            fn () => $this->body()->hiddenText()
        );
    }

    /**
     * Get the message body
     */
    public function body(): EmailAccountMessageBody
    {
        return once(function () {
            return new EmailAccountMessageBody($this);
        });
    }

    /**
     * Get the relation name when the model is used as activity
     */
    public function getTimelineRelation(): string
    {
        return 'emails';
    }

    /**
     * Mark a message as read
     */
    public function markAsRead(?int $folderId = null): static
    {
        if ($this->is_read) {
            return $this;
        }

        $folders = $folderId ?
            [EmailAccountFolder::with('account')->find($folderId)] :
            $this->folders->loadMissing('account');

        $client = $this->account->createClient();

        foreach ($folders as $folder) {
            $client->getMessage($this->remote_id, $folder->identifier())->markAsRead();
        }

        $this->fill(['is_read' => true])->save();

        return $this;
    }

    /**
     * Mark a message as unread
     */
    public function markAsUnread(?int $folderId = null): static
    {
        if (! $this->is_read) {
            return $this;
        }

        $folders = $folderId ?
            [EmailAccountFolder::with('account')->find($folderId)] :
            $this->folders->loadMissing('account');

        $client = $this->account->createClient();

        foreach ($folders as $folder) {
            $client->getMessage($this->remote_id, $folder->identifier())->markAsUnread();
        }

        $this->fill(['is_read' => false])->save();

        return $this;
    }

    /**
     * Purge the message data.
     */
    public function purge(): void
    {
        foreach (['deals', 'contacts', 'companies', 'folders'] as $relation) {
            $this->{$relation}()->detach();
        }

        ScheduledEmail::where('related_message_id', $this->id)
            ->get()
            ->each(function (ScheduledEmail $message) {
                $message->delete();
            });
    }

    /**
     * Scope a query to include only unread messages.
     */
    public function scopeUnread(Builder $query): void
    {
        // is_read = 0 causes slow performance, use is_read < 1 for better performance
        $query->where('is_read', '<', 1);
    }

    /**
     * Scope a query to include only read messages.
     */
    public function scopeRead(Builder $query): void
    {
        // is_read = 1 causes slow performance, use is_read > 0 for better performance
        $query->where('is_read', '>', 0);
    }

    /**
     * Scope a query to include only messages of the given folder.
     */
    public function scopeOfFolder(Builder $query, int|string $folderId): void
    {
        $query->whereHas('folders', function ($query) use ($folderId) {
            return $query->where('folder_id', $folderId);
        });
    }

    /**
     * Apply where in remote ids
     *
     * @see https://stackoverflow.com/questions/53683774/eloquent-delete-sqlstate22007-invalid-datetime-format-1292-truncated-incor
     */
    public function scopeWhereRemoteIdsIn(Builder $query, array $remoteIds): void
    {
        $query->whereIn('remote_id', array_map(fn ($value) => (string) $value, $remoteIds));
    }

    /**
     * Eager load the relations that are required for the front end response.
     */
    public function scopeWithCommon(Builder $query): void
    {
        $query->with([
            'headers',
            'from',
            'sender',
            'to',
            'cc',
            'bcc',
            'replyTo',
            'attachments',
            'folders',
            'account',
            'account.folders' => fn ($query) => $query->withCount([
                'messages as unread_count' => fn ($query) => $query->unread(),
            ]),
            'tags',
        ]);
    }

    /**
     * Get the activity front-end component
     */
    public function getTimelineComponent(): string
    {
        return 'record-tab-timeline-email';
    }
}
