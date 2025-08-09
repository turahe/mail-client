<?php

namespace Turahe\MailClient\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Lang;
use Turahe\MailClient\Client\FolderIdentifier;
use Turahe\MailClient\Enums\ConnectionType;
use Turahe\MailClient\Support\EmailAccountFolderCollection;
use Turahe\Media\Models\Media;
use Turahe\UserStamps\Concerns\HasUserStamps;

class EmailAccountFolder extends Model
{
    use HasUlids;
    use HasUserStamps;
    use SoftDeletes;

    protected $dateFormat = 'U';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'parent_id', 'name', 'display_name', 'remote_id',
        'email_account_id', 'syncable', 'selectable', 'type', 'support_move',
    ];

    protected function casts(): array
    {
        return [
            'selectable' => 'boolean',
            'syncable' => 'boolean',
            'support_move' => 'boolean',
            'parent_id' => 'int',
            'email_account_id' => 'string',
        ];
    }

    /**
     * Perform any actions required after the model boots.
     */
    //    protected static function booted(): void
    //    {
    //        static::deleting(function (EmailAccountFolder $model) {
    //            $model->purge();
    //        });
    //    }

    /**
     * A folder belongs to email account
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(\Turahe\MailClient\Models\EmailAccount::class, 'email_account_id');
    }

    /**
     * A folder belongs to email account
     */
    public function messages(): BelongsToMany
    {
        return $this->belongsToMany(
            \Turahe\MailClient\Models\EmailAccountMessage::class,
            'email_account_message_folders',
            'folder_id',
            'message_id'
        );
    }

    /**
     * Get the display name attribute
     *
     * The function check if there is no translation found
     * for the labels, returns the original stored value
     */
    protected function displayName(): Attribute
    {
        return Attribute::get(function ($value) {
            $customLangKey = 'custom.mail.labels.'.$value;
            $primaryLangKey = 'mailclient::mail.labels.'.$value;

            if (Lang::has($customLangKey)) {
                return __($customLangKey);
            } elseif (Lang::has($primaryLangKey)) {
                return __($primaryLangKey);
            }

            return $value;
        });
    }

    /**
     * Get the folder identifier
     */
    public function identifier(): FolderIdentifier
    {
        if ($this->account->connection_type === ConnectionType::Imap) {
            return new FolderIdentifier('name', $this->name);
        }

        return new FolderIdentifier('id', $this->remote_id);
    }

    /**
     * Mark the folder as not selectable and syncable
     */
    public function markAsNotSelectable(): static
    {
        $this->fill(['syncable' => false, 'selectable' => false])->save();

        return $this;
    }

    /**
     * Count the total unread messages for a given folder
     */
    public function countUnreadMessages(): int
    {
        return $this->countReadOrUnreadMessages($this->id, 'unread');
    }

    /**
     * Count the total read messages for a given folder
     */
    public function countReadMessages(): int
    {
        return $this->countReadOrUnreadMessages($this->id, 'read');
    }

    /**
     * Count read or unread messages for a given folder
     */
    protected function countReadOrUnreadMessages(string $folderId, string $scope): int
    {
        return (int) static::select('id')
            ->withCount(['messages' => function ($query) use ($scope) {
                return $query->{$scope}();
            }])->where('id', $folderId)->first()->messages_count ?? 0;
    }

    /**
     * Purge the folder data
     */
    public function purge(): void
    {
        // To prevent looping through all messages and loading them into
        // memory, we will get their id's only and purge the media
        // for the messages where media is available
        $messages = $this->messages()->has('folders', '=', 1)->cursor()
            ->each(function ($message) {
                foreach (['deals', 'contacts', 'companies'] as $relation) {
                    $message->{$relation}()->withTrashedIfUsingSoftDeletes()->detach();
                }
            })
            ->map(fn ($message) => $message->id);

        (new Media)->purgeByMediableIds(EmailAccountMessage::class, $messages);

        $this->messages()->has('folders', '=', 1)->delete();
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function newCollection(array $models = [])
    {
        return (new EmailAccountFolderCollection($models))->sortByType();
    }
}
