<?php

namespace Turahe\MailClient\Enums;

enum SyncState: string
{
    case Disabled = 'DISABLED';
    case Stopped = 'STOPPED';
    case Enabled = 'ENABLED';
}
