<?php

namespace Modules\MailClient\Client;

enum ConnectionType: string
{
    case Gmail = 'Gmail';
    case Outlook = 'Outlook';
    case Imap = 'Imap';
}
