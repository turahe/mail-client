<?php

namespace Modules\MailClient\Client;

use Modules\MailClient\Client\Contracts\AttachmentInterface;
use Turahe\Core\AbstractMask;

abstract class AbstractAttachment extends AbstractMask implements AttachmentInterface
{
    /**
     * Serialize
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * toArray
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'file_name' => $this->getFileName(),
            'content' => $this->getContent(),
            'content_type' => $this->getContentType(),
            'encoding' => $this->getEncoding(),
            'content_id' => $this->getContentId(),
            'size' => $this->getSize(),
            'inline' => $this->isInline(),
        ];
    }
}
