<?php

namespace Turahe\MailClient\Client;

use Turahe\MailClient\Client\Contracts\AttachmentInterface;

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
