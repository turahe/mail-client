<?php

namespace Turahe\MailClient\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PredefinedMailTemplateResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     */
    public function toArray(Request $request): array
    {
        return $this->withCommonData([
            'name' => $this->name,
            'subject' => $this->subject,
            'body' => $this->body,
            'is_shared' => $this->is_shared,
            'user_id' => $this->user_id,
            'user' => new UserResource($this->whenLoaded('user')),
        ], $request);
    }
}
