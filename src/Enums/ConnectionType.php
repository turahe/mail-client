<?php

namespace Turahe\MailClient\Enums;

enum ConnectionType: string
{
    case Gmail = 'GMAIL';
    case Outlook = 'OUTLOOK';
    case Imap = 'IMAP';

}
