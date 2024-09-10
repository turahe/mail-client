<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.5.0
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2024 KONKORD DIGITAL
 */

namespace Turahe\MailClient\Services;

use DateTime;
use Plank\Mediable\Facades\MediaUploader;
use Turahe\Core\Facades\Innoclapps;
use Turahe\MailClient\Models\EmailAccount;
use Turahe\MailClient\Models\EmailAccountMessage;
use Turahe\MailClient\Models\ScheduledEmail;

class EmailScheduler
{
    /**
     * @var \Turahe\Core\Models\Media[]
     */
    protected array $additionalAttachments = [];

    /**
     * Initialize new EmailScheduler instance.
     */
    public function __construct(
        protected string $type,
        protected int $userId,
        protected EmailAccount $account,
        protected array $associations,
        protected string $subject,
        protected string $htmlBody,
        protected array $to,
        protected ?array $cc = null,
        protected ?array $bcc = null,
        protected array $pendingAttachments = [],
        protected ?int $relatedMessageId = null
    ) {}

    /**
     * Schedule an email to be sent at the given date.
     */
    public function schedule(string|DateTime $date): ScheduledEmail
    {
        $message = ScheduledEmail::create([
            'subject' => $this->subject,
            'html_body' => $this->htmlBody,
            'scheduled_at' => $date,
            'user_id' => $this->userId,
            'status' => 'pending',
            'to' => $this->to,
            'cc' => $this->cc,
            'bcc' => $this->bcc,
            'email_account_id' => $this->account->id,
            'associations' => $this->associations,
            'related_message_id' => $this->relatedMessageId,
            'type' => $this->type,
        ]);

        $this->attachAssociationsToMessage($message);

        return $this->attachMediaToMessage($message);
    }

    /**
     * Add additional media attachments to the message.
     *
     * @param  \Turahe\Core\Models\Media[]  $media
     */
    public function attachments(array $media): static
    {
        $this->additionalAttachments = $media;

        return $this;
    }

    /**
     * Attach the associations to the message.
     */
    protected function attachAssociationsToMessage(ScheduledEmail $message): ScheduledEmail
    {
        foreach ($this->associations as $resourceName => $modelIds) {
            $resource = Innoclapps::resourceByName($resourceName);

            if (count($modelIds) > 0) {
                $records = $resource->newQuery()->findMany($modelIds);

                $records->each(function ($model) use ($message) {
                    $model->scheduledEmails()->attach($message);
                });
            }
        }

        return $message;
    }

    /**
     * Attach attachments to the scheduled message.
     */
    protected function attachMediaToMessage(ScheduledEmail $message): ScheduledEmail
    {
        $this->attachPendingMediaToMessage($message);

        foreach ($this->additionalAttachments as $media) {
            $newMedia = MediaUploader::fromString($media->contents())
                ->toDirectory($message->getMediaDirectory())
                ->setAllowedExtensions(Innoclapps::allowedUploadExtensions())
                ->useFilename($media->filename)
                ->onDuplicateIncrement()
                ->upload();

            $message->attachMedia($newMedia, EmailAccountMessage::ATTACHMENTS_MEDIA_TAG);
        }

        return $message;
    }

    /**
     * Attach pending attachments to the scheduled message.
     */
    protected function attachPendingMediaToMessage(ScheduledEmail $message): ScheduledEmail
    {
        foreach ($this->pendingAttachments as $media) {
            $media->unmarkAsPending($message->getMediaDirectory());
            $message->attachMedia($media->attachment, EmailAccountMessage::ATTACHMENTS_MEDIA_TAG);
        }

        return $message;
    }
}
