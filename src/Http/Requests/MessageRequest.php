<?php

namespace Turahe\MailClient\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MessageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'to' => 'bail|required|array',
            'cc' => 'bail|nullable|array',
            'bcc' => 'bail|nullable|array',
            // If changing the validation for recipients check the front-end too
            'to.*.address' => 'email',
            'cc.*.address' => 'email',
            'bcc.*.address' => 'email',
            'subject' => ['required', 'string'],
            'via_resource' => Rule::requiredIf($this->filled('task_date')),
            'via_resource_id' => Rule::requiredIf($this->filled('task_date')),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'to.*.address' => 'email address',
        ];
    }

    /**
     * Get the pending media attachments.
     *
     * @return \Modules\Core\Models\Media[]
     */
    public function pendingAttachments(): array
    {
        if (! $this->attachments_draft_id) {
            return [];
        }

        return PendingMedia::with('attachment')
            ->ofDraftId($this->attachments_draft_id)
            ->get()
            ->all();
    }

    /**
     * Get the associations when sending, replying or forwarding to a message.
     */
    public function associations(): array
    {
        return $this->authorizeAssociations('emails', $this->input('associations', []));
    }

    /**
     * Create new scheduler instance from the current request.
     */
    public function scheduler(EmailAccount $account, string $type, ?int $relatedMessageId = null): EmailScheduler
    {
        return new EmailScheduler(
            type: $type,
            userId: $this->user()->getKey(),
            account: $account,
            associations: $this->associations(),
            subject: $this->subject,
            htmlBody: $this->message,
            to: $this->to,
            cc: $this->cc,
            bcc: $this->bcc,
            pendingAttachments: $this->pendingAttachments(),
            relatedMessageId: $relatedMessageId,
        );
    }
}
