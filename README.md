# Turahe Mail Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/turahe/mailclient.svg?style=flat-square)](https://packagist.org/packages/turahe/mailclient)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/turahe/mail-client/ci.yml?branch=master&label=tests&style=flat-square)](https://github.com/turahe/mail-client/actions?query=workflow%3Aci+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/turahe/mailclient.svg?style=flat-square)](https://packagist.org/packages/turahe/mailclient)
[![License](https://img.shields.io/packagist/l/turahe/mailclient.svg?style=flat-square)](https://packagist.org/packages/turahe/mailclient)
[![PHP Version Require](https://img.shields.io/packagist/php-v/turahe/mailclient.svg?style=flat-square)](https://packagist.org/packages/turahe/mailclient)
[![Test Coverage](https://img.shields.io/badge/coverage-566%20assertions-brightgreen?style=flat-square)](https://github.com/turahe/mail-client)

A comprehensive Laravel mail client package for managing email accounts, messages, and folders with support for IMAP, SMTP, Gmail, Outlook, and various email providers.

---

## 📋 Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Quick Start](#-quick-start)
- [Configuration](#️-configuration)
- [Basic Usage](#-basic-usage)
- [Advanced Features](#-advanced-features)
- [Models & Architecture](#️-models--architecture)
- [Testing](#-testing)
- [API Reference](#-api-reference)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#️-contributing)
- [Support](#-support)

---

## ✨ Features

- **🔐 Multi-Provider Support** - IMAP, SMTP, Gmail API, Outlook/Exchange
- **📧 Complete Email Management** - Send, receive, organize, and track emails
- **📂 Folder Hierarchy** - Nested folder structures with full CRUD operations
- **📋 Email Templates** - Predefined and reusable email templates
- **⏰ Scheduled Emails** - Queue and schedule emails for future delivery
- **📊 Link Tracking** - Track email link clicks and analytics
- **🔄 Sync Management** - Intelligent email synchronization
- **🆔 ULID Support** - Modern, sortable unique identifiers
- **🧪 100% Test Coverage** - 173 tests, 566 assertions, rock-solid reliability

---

## 🚀 Requirements

| Component | Version |
|-----------|---------|
| **PHP** | 8.4+ |
| **Laravel** | 12.0+ |
| **Database** | MySQL 8.0+ / PostgreSQL 13+ / SQLite 3.35+ |

---

## ⚡ Quick Start

### 1. Install Package

```bash
composer require turahe/mailclient
```

### 2. Publish & Migrate

```bash
php artisan vendor:publish --provider="Turahe\MailClient\MailClientServiceProvider"
php artisan migrate
```

### 3. Create Your First Email Account

```php
use Turahe\MailClient\Models\EmailAccount;
use Turahe\MailClient\Enums\ConnectionType;

$account = EmailAccount::create([
    'email' => 'user@example.com',
    'password' => 'your_password',
    'connection_type' => ConnectionType::IMAP,
    'imap_server' => 'imap.example.com',
    'imap_port' => 993,
    'smtp_server' => 'smtp.example.com',
    'smtp_port' => 587,
]);
```

### 4. Send Your First Email

```php
$client = $account->createClient();

$message = $client->compose()
    ->to('recipient@example.com')
    ->subject('Hello World!')
    ->html('<h1>Hello from Laravel Mail Client!</h1>')
    ->send();
```

---

## ⚙️ Configuration

### Environment Setup

```env
# Gmail OAuth
GMAIL_CLIENT_ID=your_gmail_client_id
GMAIL_CLIENT_SECRET=your_gmail_client_secret
GMAIL_REDIRECT_URL=your_callback_url

# Outlook OAuth  
OUTLOOK_CLIENT_ID=your_outlook_client_id
OUTLOOK_CLIENT_SECRET=your_outlook_client_secret
OUTLOOK_REDIRECT_URL=your_callback_url
```

### Package Configuration

```php
// config/mail-client.php
return [
    'default_connection_type' => 'imap',
    'sync_batch_size' => 50,
    'max_attachment_size' => 25 * 1024 * 1024, // 25MB
    'allowed_attachment_types' => ['pdf', 'doc', 'docx', 'jpg', 'png'],
];
```

---

## 📧 Basic Usage

<details>
<summary><strong>📥 Email Account Management</strong></summary>

### Creating Different Account Types

#### IMAP/SMTP Account
```php
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
]);
```

#### Gmail Account
```php
$gmailAccount = EmailAccount::create([
    'email' => 'user@gmail.com',
    'connection_type' => ConnectionType::GMAIL,
    'access_token' => $accessToken,
    'refresh_token' => $refreshToken,
]);
```

#### Outlook Account
```php
$outlookAccount = EmailAccount::create([
    'email' => 'user@outlook.com', 
    'connection_type' => ConnectionType::OUTLOOK,
    'access_token' => $accessToken,
    'refresh_token' => $refreshToken,
]);
```

### Account Operations

```php
// Test connection
if ($account->testConnection()) {
    echo "✅ Connection successful!";
}

// Sync management
$account->enableSync();
$account->disableSync();

// Get statistics
$stats = $account->getStats();
echo "📧 Total: {$stats['total_messages']}";
echo "🔵 Unread: {$stats['unread_messages']}";
```

</details>

<details>
<summary><strong>📂 Folder Management</strong></summary>

### Creating and Organizing Folders

```php
// Create main folder
$folder = EmailAccountFolder::create([
    'email_account_id' => $account->id,
    'name' => 'Important',
    'display_name' => 'Important Messages',
    'syncable' => true,
]);

// Create subfolder
$subFolder = EmailAccountFolder::create([
    'email_account_id' => $account->id,
    'parent_id' => $folder->id,
    'name' => 'Urgent',
    'display_name' => 'Urgent Items',
]);

// Browse hierarchy
$rootFolders = $account->folders()->whereNull('parent_id')->get();
foreach ($rootFolders as $folder) {
    echo "📁 {$folder->name}\n";
    foreach ($folder->children as $child) {
        echo "   └─ 📁 {$child->name}\n";
    }
}
```

</details>

<details>
<summary><strong>📨 Sending Emails</strong></summary>

### Basic Email Composition

```php
$client = $account->createClient();

// Simple email
$message = $client->compose()
    ->to('user@example.com')
    ->subject('Meeting Tomorrow')
    ->text('Don\'t forget our meeting at 2 PM tomorrow.')
    ->send();

// Rich HTML email
$message = $client->compose()
    ->to('user@example.com')
    ->cc('manager@example.com')
    ->bcc('archive@example.com')
    ->subject('Project Update')
    ->html('
        <h2>Project Status Update</h2>
        <p>The project is <strong>on track</strong> for completion.</p>
        <ul>
            <li>✅ Phase 1: Complete</li>
            <li>🔄 Phase 2: In Progress</li>
            <li>⏳ Phase 3: Planned</li>
        </ul>
    ')
    ->attach('/path/to/report.pdf')
    ->send();
```

### Using Templates

```php
$template = PredefinedMailTemplate::find(1);
$message = $client->compose()
    ->to('customer@example.com')
    ->fromTemplate($template)
    ->variables([
        'customer_name' => 'John Doe',
        'order_number' => 'ORD-12345',
        'delivery_date' => '2025-01-15'
    ])
    ->send();
```

</details>

<details>
<summary><strong>📬 Managing Messages</strong></summary>

### Reading Messages

```php
// Get recent unread messages
$messages = $account->messages()
    ->unread()
    ->orderBy('date', 'desc')
    ->take(10)
    ->get();

foreach ($messages as $message) {
    echo "📧 {$message->subject}\n";
    echo "👤 From: {$message->from_address}\n";
    echo "📅 Date: {$message->date}\n\n";
}
```

### Message Operations

```php
$message = EmailAccountMessage::find($messageId);

// Status operations
$message->markAsRead();
$message->markAsUnread();

// Organization
$message->moveToFolder($importantFolder);
$message->addToFolders([$folder1, $folder2]);

// Lifecycle management
$message->archive();
$message->trash();
$message->restore();
$message->purge(); // Permanent delete
```

### Working with Content

```php
// Get message content
$htmlBody = $message->getHtmlBody();
$textBody = $message->getTextBody();

// Handle attachments
foreach ($message->attachments as $attachment) {
    echo "📎 {$attachment->name} ({$attachment->size} bytes)\n";
    
    // Download attachment
    $content = $attachment->getContent();
    file_put_contents("/downloads/{$attachment->name}", $content);
}
```

</details>

---

## 🚀 Advanced Features

<details>
<summary><strong>📋 Email Templates</strong></summary>

### Creating Templates

```php
$template = PredefinedMailTemplate::create([
    'name' => 'Order Confirmation',
    'subject' => 'Order {{order_number}} Confirmed',
    'html_body' => '
        <h1>Order Confirmed! 🎉</h1>
        <p>Hello {{customer_name}},</p>
        <p>Your order <strong>#{{order_number}}</strong> has been confirmed.</p>
        <p>Expected delivery: {{delivery_date}}</p>
    ',
    'text_body' => 'Hello {{customer_name}}, your order #{{order_number}} is confirmed. Delivery: {{delivery_date}}',
    'is_shared' => true,
]);
```

### Using Template Variables

```php
$processedTemplate = $template->process([
    'customer_name' => 'Sarah Johnson',
    'order_number' => 'ORD-789',
    'delivery_date' => 'January 20, 2025'
]);

// Result:
// Subject: "Order ORD-789 Confirmed"
// Body: "Hello Sarah Johnson, your order #ORD-789 is confirmed..."
```

</details>

<details>
<summary><strong>⏰ Scheduled Emails</strong></summary>

### Scheduling Emails

```php
$scheduledEmail = ScheduledEmail::create([
    'email_account_id' => $account->id,
    'to' => 'customer@example.com',
    'subject' => 'Weekly Newsletter',
    'html_body' => '<h1>This Week in Tech</h1>...',
    'scheduled_at' => Carbon::now()->addWeek(),
    'data' => ['newsletter_id' => 456],
]);
```

### Processing Scheduled Emails

```php
// In your scheduled job (e.g., daily)
$dueEmails = ScheduledEmail::dueForSend()->get();

foreach ($dueEmails as $email) {
    try {
        $email->send();
        echo "✅ Sent: {$email->subject}\n";
    } catch (Exception $e) {
        echo "❌ Failed: {$e->getMessage()}\n";
    }
}
```

</details>

<details>
<summary><strong>📊 Link Tracking</strong></summary>

### Automatic Link Tracking

```php
// Links in emails are automatically tracked
$message = $client->compose()
    ->to('user@example.com')
    ->subject('Check out our new features!')
    ->html('
        <p>Visit our <a href="https://example.com/features">new features page</a></p>
        <p>Read the <a href="https://example.com/blog/update">latest blog post</a></p>
    ')
    ->send();
```

### Analyzing Click Data

```php
$message = EmailAccountMessage::find($messageId);

// Get all clicks
$clicks = $message->linkClicks;

foreach ($clicks as $click) {
    echo "🔗 URL: {$click->url}\n";
    echo "📅 Clicked: {$click->clicked_at}\n";
    echo "🌍 IP: {$click->ip_address}\n";
    echo "💻 Browser: {$click->user_agent}\n\n";
}

// Statistics
$totalClicks = $message->linkClicks()->count();
$uniqueClicks = $message->linkClicks()->distinct('ip_address')->count();
echo "📊 Total clicks: {$totalClicks} | Unique: {$uniqueClicks}";
```

</details>

<details>
<summary><strong>🔄 Bulk Operations</strong></summary>

### Mass Message Management

```php
// Get messages to process
$messages = $account->messages()
    ->where('subject', 'like', '%newsletter%')
    ->get();

// Bulk operations
$account->moveMessagesToFolder($messages, $newsletterFolder);
$account->markMessagesAsRead($messages);
$account->deleteMessages($messages);
```

### Sync Management

```php
use Turahe\MailClient\Services\EmailAccountMessageSyncService;

$syncService = app(EmailAccountMessageSyncService::class);

// Full account sync
$syncService->syncAccount($account);

// Incremental sync (faster)
$lastSync = $account->last_synced_at;
$syncService->syncAccountIncremental($account, $lastSync);

// Sync specific folder only
$syncService->syncFolder($folder);
```

</details>

---

## 🏗️ Models & Architecture

### Core Models Overview

| Model | Purpose | Key Features |
|-------|---------|--------------|
| **EmailAccount** | Email account management | Multi-provider support, OAuth, sync settings |
| **EmailAccountFolder** | Folder organization | Hierarchical structure, sync control |
| **EmailAccountMessage** | Message storage | Rich content, attachments, metadata |
| **EmailAccountMessageAddress** | Email addresses | From, To, CC, BCC tracking |
| **EmailAccountMessageHeader** | Email headers | Technical metadata storage |
| **PredefinedMailTemplate** | Email templates | Variable substitution, sharing |
| **ScheduledEmail** | Email scheduling | Queue management, retry logic |
| **MessageLinksClick** | Link analytics | Click tracking, user behavior |

### Key Relationships

```php
// EmailAccount (1:N)
$account->folders;           // All folders
$account->messages;          // All messages  
$account->scheduledEmails;   // Scheduled emails

// EmailAccountMessage (N:M)
$message->folders;           // Associated folders
$message->addresses;         // Email addresses
$message->headers;           // Technical headers
$message->linkClicks;        // Click analytics

// EmailAccountFolder (Tree)
$folder->parent;             // Parent folder
$folder->children;           // Child folders
$folder->messages;           // Folder messages
```

---

## 🧪 Testing

### Running Tests

```bash
# All tests (173 tests, 566 assertions)
vendor/bin/phpunit

# Specific test suites
vendor/bin/phpunit --testsuite=Unit     # Unit tests only
vendor/bin/phpunit --testsuite=Feature  # Feature tests only

# With coverage report
vendor/bin/phpunit --coverage-html coverage
```

### Using Test Factories

```php
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;

// Create test account
$account = EmailAccountFactory::new()->create([
    'email' => 'test@example.com'
]);

// Create account with related data
$account = EmailAccountFactory::new()
    ->withMessages(10)      // 10 messages
    ->withFolders(5)        // 5 folders
    ->create();

// Create specific message
$message = EmailAccountMessageFactory::new()
    ->forAccount($account)
    ->create([
        'subject' => 'Test Message',
        'from_address' => 'sender@example.com'
    ]);
```

---

## 📖 API Reference

<details>
<summary><strong>EmailAccount Methods</strong></summary>

```php
// Connection management
$account->testConnection(): bool
$account->createClient(): Client

// Sync control
$account->enableSync(): void
$account->disableSync(): void
$account->isSyncDisabled(): bool

// Statistics
$account->getStats(): array
$account->getUnreadCount(): int

// Token management (OAuth accounts)
$account->refreshAccessToken(): void
$account->isTokenExpired(): bool
```

</details>

<details>
<summary><strong>EmailAccountMessage Methods</strong></summary>

```php
// Status management
$message->markAsRead(): void
$message->markAsUnread(): void
$message->isRead(): bool

// Organization
$message->moveToFolder(EmailAccountFolder $folder): void
$message->addToFolders(array $folders): void
$message->removeFromFolder(EmailAccountFolder $folder): void

// Lifecycle
$message->archive(): void
$message->trash(): void
$message->restore(): void
$message->purge(): void

// Content access
$message->getHtmlBody(): string
$message->getTextBody(): string
$message->hasAttachments(): bool
```

</details>

<details>
<summary><strong>PredefinedMailTemplate Methods</strong></summary>

```php
// Template processing
$template->process(array $variables): array
$template->getProcessedSubject(array $variables): string
$template->getProcessedBody(array $variables): string

// Sharing
$template->makeShared(): void
$template->makePrivate(): void
$template->isShared(): bool
```

</details>

---

## 🐛 Troubleshooting

<details>
<summary><strong>Connection Issues</strong></summary>

**Problem**: Connection timeout errors
```php
// Solution: Increase timeout settings
'imap_timeout' => 60,  // seconds
'smtp_timeout' => 30,  // seconds
```

**Problem**: SSL certificate errors
```php
// Solution: Disable SSL verification (development only)
'imap_options' => [
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]
```

</details>

<details>
<summary><strong>Memory Issues</strong></summary>

**Problem**: Memory exhaustion with large attachments
```php
// Solution 1: Increase memory limit
ini_set('memory_limit', '512M');

// Solution 2: Use streaming
$attachment->streamToFile('/path/to/destination');

// Solution 3: Process in chunks
$account->messages()->chunk(50, function ($messages) {
    // Process 50 messages at a time
});
```

</details>

<details>
<summary><strong>OAuth Token Issues</strong></summary>

**Problem**: Expired access tokens
```php
// Solution: Automatic token refresh
if ($account->isTokenExpired()) {
    $account->refreshAccessToken();
}

// Or handle in exception
try {
    $client->getMessages();
} catch (UnauthorizedException $e) {
    $account->refreshAccessToken();
    $client->getMessages(); // Retry
}
```

</details>

---

## 🛠️ Contributing

We welcome contributions! Here's how to get started:

### Development Setup

```bash
# Clone and setup
git clone https://github.com/turahe/mail-client.git
cd mail-client
composer install

# Prepare testing
cp phpunit.xml.dist phpunit.xml
vendor/bin/phpunit

# Start coding!
```

### Contribution Process

1. **Fork** the repository
2. **Create** feature branch (`git checkout -b feature/amazing-feature`)
3. **Write** tests for your changes
4. **Ensure** all tests pass (`vendor/bin/phpunit`)
5. **Commit** your changes (`git commit -m 'Add amazing feature'`)
6. **Push** to branch (`git push origin feature/amazing-feature`)
7. **Open** a Pull Request

### Code Standards

- ✅ **PHP 8.4+** type declarations
- ✅ **PSR-12** coding standards
- ✅ **100% test coverage** for new features
- ✅ **PHPStan level 8** compliance
- ✅ **Clear documentation** for public methods

---

## 📞 Support

- 📧 **Email**: [wachid@outlook.com](mailto:wachid@outlook.com)
- 🐛 **Issues**: [GitHub Issues](https://github.com/turahe/mail-client/issues)
- 📖 **Wiki**: [Documentation](https://github.com/turahe/mail-client/wiki)
- 💬 **Discussions**: [GitHub Discussions](https://github.com/turahe/mail-client/discussions)

---

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## 📈 Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and updates.

---

<div align="center">

**Built with ❤️ for the Laravel community**

[⭐ Star us on GitHub](https://github.com/turahe/mail-client) • [📦 View on Packagist](https://packagist.org/packages/turahe/mailclient)

</div>