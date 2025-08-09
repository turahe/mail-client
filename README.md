# Turahe Mail Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/turahe/mailclient.svg?style=flat-square)](https://packagist.org/packages/turahe/mailclient)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/turahe/mail-client/ci.yml?branch=master&label=tests&style=flat-square)](https://github.com/turahe/mail-client/actions?query=workflow%3Aci+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/turahe/mailclient.svg?style=flat-square)](https://packagist.org/packages/turahe/mailclient)
[![License](https://img.shields.io/packagist/l/turahe/mailclient.svg?style=flat-square)](https://packagist.org/packages/turahe/mailclient)
[![PHP Version Require](https://img.shields.io/packagist/php-v/turahe/mailclient.svg?style=flat-square)](https://packagist.org/packages/turahe/mailclient)
[![Test Coverage](https://img.shields.io/badge/coverage-566%20assertions-brightgreen?style=flat-square)](https://github.com/turahe/mail-client)

A comprehensive Laravel mail client package for managing email accounts, messages, and folders with support for IMAP, SMTP, Gmail, Outlook, and various email providers.

## ✨ Features

- 🔐 **Multi-Provider Support**: IMAP, SMTP, Gmail API, Outlook/Exchange
- 📧 **Complete Email Management**: Send, receive, organize, and track emails
- 📂 **Folder Hierarchy**: Nested folder structures with full CRUD operations
- 📋 **Email Templates**: Predefined and reusable email templates
- ⏰ **Scheduled Emails**: Queue and schedule emails for future delivery
- 📊 **Link Tracking**: Track email link clicks and analytics
- 🔄 **Sync Management**: Intelligent email synchronization
- 🆔 **ULID Support**: Modern, sortable unique identifiers
- 🧪 **100% Test Coverage**: 173 tests, 566 assertions, rock-solid reliability

## 🚀 Requirements

- **PHP**: 8.4+
- **Laravel**: 12.0+
- **Database**: MySQL 8.0+, PostgreSQL 13+, SQLite 3.35+

## 📦 Installation

Install via Composer:

```bash
composer require turahe/mailclient
```

Publish configuration and run migrations:

```bash
php artisan vendor:publish --provider="Turahe\MailClient\MailClientServiceProvider"
php artisan migrate
```

## ⚙️ Configuration

### Basic Configuration

```php
// config/mail-client.php
return [
    'default_connection_type' => 'imap',
    'sync_batch_size' => 50,
    'max_attachment_size' => 25 * 1024 * 1024, // 25MB
    'allowed_attachment_types' => ['pdf', 'doc', 'docx', 'jpg', 'png'],
];
```

### Environment Variables

```env
# Gmail Configuration
GMAIL_CLIENT_ID=your_gmail_client_id
GMAIL_CLIENT_SECRET=your_gmail_client_secret
GMAIL_REDIRECT_URL=your_callback_url

# Outlook Configuration  
OUTLOOK_CLIENT_ID=your_outlook_client_id
OUTLOOK_CLIENT_SECRET=your_outlook_client_secret
OUTLOOK_REDIRECT_URL=your_callback_url
```

## 📚 Usage Guide

### 1. Email Account Management

#### Creating Email Accounts

```php
use Turahe\MailClient\Models\EmailAccount;
use Turahe\MailClient\Enums\ConnectionType;

// IMAP/SMTP Account
$account = EmailAccount::create([
    'email' => 'user@example.com',
    'password' => 'secure_password',
    'connection_type' => ConnectionType::IMAP,
    'imap_server' => 'imap.example.com',
    'imap_port' => 993,
    'imap_encryption' => 'ssl',
    'smtp_server' => 'smtp.example.com',
    'smtp_port' => 587,
    'smtp_encryption' => 'tls',
    'sync_state' => SyncState::ENABLED,
]);

// Gmail Account  
$gmailAccount = EmailAccount::create([
    'email' => 'user@gmail.com',
    'connection_type' => ConnectionType::GMAIL,
    'access_token' => $accessToken,
    'refresh_token' => $refreshToken,
]);

// Outlook Account
$outlookAccount = EmailAccount::create([
    'email' => 'user@outlook.com', 
    'connection_type' => ConnectionType::OUTLOOK,
    'access_token' => $accessToken,
    'refresh_token' => $refreshToken,
]);
```

#### Account Operations

```php
// Test connection
if ($account->testConnection()) {
    echo "Connection successful!";
}

// Enable/Disable sync
$account->enableSync();
$account->disableSync();

// Check sync status
if (!$account->isSyncDisabled()) {
    $account->syncEmails();
}

// Get account statistics
$stats = $account->getStats();
echo "Total messages: {$stats['total_messages']}";
echo "Unread messages: {$stats['unread_messages']}";
```

### 2. Email Folder Management

#### Working with Folders

```php
use Turahe\MailClient\Models\EmailAccountFolder;

// Create folder
$folder = EmailAccountFolder::create([
    'email_account_id' => $account->id,
    'name' => 'Important',
    'display_name' => 'Important Messages',
    'type' => 'custom',
    'syncable' => true,
]);

// Create nested folder
$subFolder = EmailAccountFolder::create([
    'email_account_id' => $account->id,
    'parent_id' => $folder->id,
    'name' => 'Urgent',
    'display_name' => 'Urgent Items',
]);

// Get folder hierarchy
$rootFolders = $account->folders()->whereNull('parent_id')->get();
foreach ($rootFolders as $folder) {
    echo $folder->name;
    foreach ($folder->children as $child) {
        echo "  └─ {$child->name}";
    }
}

// Folder statistics
$messageCount = $folder->countReadOrUnreadMessages($folder->id, 'unread');
echo "Unread messages: {$messageCount}";
```

### 3. Email Message Management

#### Sending Emails

```php
use Turahe\MailClient\Client\Client;

// Create client
$client = $account->createClient();

// Compose and send email
$message = $client->compose()
    ->to('recipient@example.com')
    ->cc('cc@example.com')
    ->bcc('bcc@example.com')
    ->subject('Important Update')
    ->html('<h1>Hello!</h1><p>This is an HTML email.</p>')
    ->text('Hello! This is a text email.')
    ->attach('/path/to/file.pdf')
    ->send();

// Send with template
$template = PredefinedMailTemplate::find(1);
$message = $client->compose()
    ->to('user@example.com')
    ->fromTemplate($template)
    ->variables(['name' => 'John', 'company' => 'Acme Corp'])
    ->send();
```

#### Receiving and Managing Messages

```php
use Turahe\MailClient\Models\EmailAccountMessage;

// Get messages
$messages = $account->messages()
    ->unread()
    ->orderBy('date', 'desc')
    ->take(10)
    ->get();

// Mark as read/unread
$message = EmailAccountMessage::find($id);
$message->markAsRead();
$message->markAsUnread();

// Move to folder
$message->moveToFolder($folder);

// Add to multiple folders
$message->addToFolders([$folder1, $folder2]);

// Archive/Trash
$message->archive();
$message->trash();
$message->restore();

// Permanent delete
$message->purge();

// Get message body
$htmlBody = $message->getHtmlBody();
$textBody = $message->getTextBody();

// Get attachments
foreach ($message->attachments as $attachment) {
    echo "File: {$attachment->name} ({$attachment->size} bytes)";
    $content = $attachment->getContent();
}
```

### 4. Email Templates

#### Creating Templates

```php
use Turahe\MailClient\Models\PredefinedMailTemplate;

$template = PredefinedMailTemplate::create([
    'name' => 'Welcome Email',
    'subject' => 'Welcome to {{company}}!',
    'html_body' => '<h1>Welcome {{name}}!</h1><p>Thank you for joining {{company}}.</p>',
    'text_body' => 'Welcome {{name}}! Thank you for joining {{company}}.',
    'is_shared' => true,
]);

// Use template
$processedTemplate = $template->process([
    'name' => 'John Doe',
    'company' => 'Acme Corporation'
]);

echo $processedTemplate['subject']; // "Welcome to Acme Corporation!"
echo $processedTemplate['html_body']; // "Welcome John Doe! Thank you for joining Acme Corporation."
```

### 5. Scheduled Emails

#### Scheduling Emails

```php
use Turahe\MailClient\Models\ScheduledEmail;
use Carbon\Carbon;

// Schedule email
$scheduledEmail = ScheduledEmail::create([
    'email_account_id' => $account->id,
    'to' => 'user@example.com',
    'subject' => 'Monthly Report',
    'html_body' => '<h1>Your monthly report is ready!</h1>',
    'scheduled_at' => Carbon::now()->addDays(7),
    'data' => ['report_id' => 123],
]);

// Process due emails (typically in a scheduled job)
$dueEmails = ScheduledEmail::dueForSend()->get();
foreach ($dueEmails as $email) {
    $email->send();
}

// Cancel scheduled email
$scheduledEmail->cancel();
```

### 6. Link Tracking

#### Tracking Email Links

```php
use Turahe\MailClient\Models\MessageLinksClick;

// Links are automatically tracked when emails contain URLs
// View click statistics
$message = EmailAccountMessage::find($id);
$clicks = $message->linkClicks;

foreach ($clicks as $click) {
    echo "URL: {$click->url}";
    echo "Clicked at: {$click->clicked_at}";
    echo "IP: {$click->ip_address}";
    echo "User Agent: {$click->user_agent}";
}

// Get click statistics
$totalClicks = $message->linkClicks()->count();
$uniqueClicks = $message->linkClicks()->distinct('ip_address')->count();
```

## 🔧 Advanced Usage

### Custom Message Processing

```php
use Turahe\MailClient\Services\EmailAccountMessageService;

$messageService = app(EmailAccountMessageService::class);

// Process incoming message
$processedMessage = $messageService->processIncomingMessage($rawMessage, $account);

// Apply custom rules
$messageService->applyRules($message, [
    'move_spam_to_folder' => $spamFolder->id,
    'auto_mark_newsletters' => true,
]);
```

### Bulk Operations

```php
// Bulk move messages
$messages = $account->messages()->unread()->get();
$account->moveMessagesToFolder($messages, $folder);

// Bulk delete
$account->deleteMessages($messages);

// Bulk mark as read
$account->markMessagesAsRead($messages);
```

### Sync Management

```php
use Turahe\MailClient\Services\EmailAccountMessageSyncService;

$syncService = app(EmailAccountMessageSyncService::class);

// Full sync
$syncService->syncAccount($account);

// Incremental sync
$syncService->syncAccountIncremental($account, $lastSyncDate);

// Sync specific folder
$syncService->syncFolder($folder);
```

## 🧪 Testing

The package comes with comprehensive test coverage. Run tests:

```bash
# All tests
vendor/bin/phpunit

# Unit tests only
vendor/bin/phpunit --testsuite=Unit

# Feature tests only  
vendor/bin/phpunit --testsuite=Feature

# With coverage
vendor/bin/phpunit --coverage-html coverage
```

### Using Factories in Tests

```php
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;

// Create test account
$account = EmailAccountFactory::new()->create([
    'email' => 'test@example.com'
]);

// Create with messages
$account = EmailAccountFactory::new()
    ->withMessages(5)
    ->withFolders(3)
    ->create();
```

## 🏗️ Models & Architecture

### Model Relationships

```php
// EmailAccount relationships
$account->folders;           // HasMany EmailAccountFolder
$account->messages;          // HasMany EmailAccountMessage  
$account->scheduledEmails;   // HasMany ScheduledEmail

// EmailAccountMessage relationships
$message->account;           // BelongsTo EmailAccount
$message->folders;           // BelongsToMany EmailAccountFolder
$message->addresses;         // HasMany EmailAccountMessageAddress
$message->headers;           // HasMany EmailAccountMessageHeader
$message->linkClicks;        // HasMany MessageLinksClick

// EmailAccountFolder relationships
$folder->account;            // BelongsTo EmailAccount
$folder->parent;             // BelongsTo EmailAccountFolder
$folder->children;           // HasMany EmailAccountFolder
$folder->messages;           // BelongsToMany EmailAccountMessage
```

### Model Features

- **ULID Primary Keys**: Modern, sortable identifiers
- **Soft Deletes**: Safe deletion with recovery
- **User Stamps**: Automatic user tracking  
- **Type Safety**: Full PHP 8.4 type declarations
- **Factory Support**: Complete testing infrastructure
- **Rich Relationships**: Complex model associations

## 🛠️ Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Run tests (`vendor/bin/phpunit`)
4. Commit your changes (`git commit -m 'Add amazing feature'`)
5. Push to the branch (`git push origin feature/amazing-feature`)
6. Open a Pull Request

### Development Setup

```bash
git clone https://github.com/turahe/mail-client.git
cd mail-client
composer install
cp phpunit.xml.dist phpunit.xml
vendor/bin/phpunit
```

## 🐛 Troubleshooting

### Common Issues

**Connection Timeout**
```php
// Increase timeout in config
'imap_timeout' => 60, // seconds
'smtp_timeout' => 30, // seconds
```

**Memory Issues with Large Attachments**
```php
// Increase memory limit
ini_set('memory_limit', '512M');

// Use streaming for large files
$attachment->streamToFile('/path/to/destination');
```

**OAuth Token Expiry**
```php
// Refresh tokens automatically
if ($account->isTokenExpired()) {
    $account->refreshAccessToken();
}
```

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 🤝 Support

- 📧 **Email**: wachid@outlook.com
- 🐛 **Issues**: [GitHub Issues](https://github.com/turahe/mail-client/issues)
- 📖 **Documentation**: [Wiki](https://github.com/turahe/mail-client/wiki)

## 📈 Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.

---

**Built with ❤️ for the Laravel community**