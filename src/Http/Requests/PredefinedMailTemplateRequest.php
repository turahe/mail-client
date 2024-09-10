<?php

namespace Turahe\MailClient\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PredefinedMailTemplateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'unique:mail_templates,name'],
            'subject' => ['required', 'string'],
            'body' => ['required', 'string'],
            'is_shared' => ['required', 'boolean'],
        ];
    }
}
