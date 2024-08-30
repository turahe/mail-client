<?php

namespace Modules\MailClient\Enums;

enum ConnectionType: string
{
    case Gmail = 'GMAIL';
    case Outlook = 'OUTLOOK';
    case Imap = 'IMAP';

}
