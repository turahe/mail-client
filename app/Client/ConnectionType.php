<?php

namespace Modules\MailClient\Client;

enum ConnectionType: string
{
    case Gmail = 'GMAIL';
    case Outlook = 'OUTLOOK';
    case Imap = 'IMAP';
}
