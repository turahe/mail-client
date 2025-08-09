# Turahe Mail Client

[![Latest Version on Packagist](https://img.shields.io/packagist/v/turahe/mailclient.svg?style=flat-square)](https://packagist.org/packages/turahe/mailclient)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/turahe/mail-client/tests.yml?branch=master&label=tests&style=flat-square)](https://github.com/turahe/mail-client/actions?query=workflow%3Atests+branch%3Amaster)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/turahe/mail-client/fix-php-code-style-issues.yml?branch=master&label=code%20style&style=flat-square)](https://github.com/turahe/mail-client/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/turahe/mailclient.svg?style=flat-square)](https://packagist.org/packages/turahe/mailclient)
[![License](https://img.shields.io/packagist/l/turahe/mailclient.svg?style=flat-square)](https://packagist.org/packages/turahe/mailclient)
[![PHP Version Require](https://img.shields.io/packagist/php-v/turahe/mailclient.svg?style=flat-square)](https://packagist.org/packages/turahe/mailclient)

A comprehensive Laravel mail client package for managing email accounts, messages, and folders with support for IMAP, SMTP, and various email providers.

## 🆕 What's New in v1.1.0

- **Comprehensive Test Suite**: 120+ test methods covering all 9 models
- **Complete Factory Support**: Full factory ecosystem for testing
- **Enhanced Type Safety**: Fixed ULID string casting across all models
- **Model Improvements**: Added HasFactory traits and missing relationships
- **Database Fixes**: Resolved foreign key constraints and ULID handling
- **Test Infrastructure**: CRUD, relationship, validation, and scope testing

## Features

- 🔐 **Multi-Provider Support**: IMAP, SMTP, Gmail, Outlook
- 📧 **Email Management**: Send, receive, reply, forward emails
- 📁 **Folder Management**: Organize emails in folders
- 🔄 **Sync Capabilities**: Automatic email synchronization
- 📊 **Message Tracking**: Read/unread status, attachments
- 🎯 **Laravel Integration**: Seamless Laravel framework integration
- 🧪 **Comprehensive Testing**: Full test coverage with PHPUnit (120+ test methods)
- 🏭 **Factory Support**: Complete factory ecosystem for all models
- 🆔 **ULID Support**: Modern ULID primary keys with proper type casting
- 🔗 **Rich Relationships**: Comprehensive model relationships and associations
- ⚡ **Modern PHP**: PHP 8.3+ features with enhanced type safety

## Installation

```bash
composer require turahe/mailclient
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Turahe\MailClient\MailClientServiceProvider"
```

## Usage

### Creating an Email Account

```php
use Turahe\MailClient\Models\EmailAccount;

$account = EmailAccount::create([
    'email' => 'user@example.com',
    'password' => 'secure_password',
    'connection_type' => ConnectionType::Imap,
    'imap_server' => 'imap.example.com',
    'imap_port' => 993,
    'smtp_server' => 'smtp.example.com',
    'smtp_port' => 587,
]);
```

### Sending Emails

```php
$account = EmailAccount::find(1);
$client = $account->createClient();

$message = $client->compose()
    ->to('recipient@example.com')
    ->subject('Test Email')
    ->body('Hello from Laravel Mail Client!')
    ->send();
```

### Managing Folders

```php
$folders = $account->folders;
$activeFolders = $account->activeFolders();

foreach ($activeFolders as $folder) {
    echo $folder->name . ': ' . $folder->messages()->count() . ' messages';
}
```

## Models & Architecture

The package includes **9 comprehensive models** with full test coverage:

### Core Models

- **`EmailAccount`** - Email account management with IMAP/SMTP configuration
- **`EmailAccountFolder`** - Email folder organization and hierarchy
- **`EmailAccountMessage`** - Email message storage and metadata
- **`EmailAccountMessageAddress`** - Email addresses (from, to, cc, bcc)
- **`EmailAccountMessageHeader`** - Email headers and metadata
- **`EmailAccountMessageFolder`** - Message-folder relationships (pivot)
- **`PredefinedMailTemplate`** - Reusable email templates
- **`ScheduledEmail`** - Scheduled email functionality
- **`MessageLinksClick`** - Email link tracking and analytics

### Key Features

- **ULID Primary Keys**: Modern, sortable unique identifiers
- **Soft Deletes**: Safe data deletion with recovery options
- **User Stamping**: Track creation and modification users
- **Rich Relationships**: Complex associations between models
- **Factory Support**: Complete testing infrastructure
- **Type Safety**: Full PHP 8.4 type declarations

## Testing

This package includes a comprehensive test suite with **120+ test methods** covering all models and functionality.

### Test Coverage

- ✅ **9 Models**: Complete test coverage for all models
- ✅ **CRUD Operations**: Create, Read, Update, Delete for all models
- ✅ **Relationships**: BelongsTo, HasMany, BelongsToMany, MorphToMany
- ✅ **Validation**: Model constraints and field validation
- ✅ **Scopes**: Custom query scopes and model methods
- ✅ **Factories**: Full factory support for all models
- ✅ **Database**: Foreign key constraints and ULID handling

### Running Tests

Run the test suite:

```bash
composer test
```

Run with coverage:

```bash
composer test-coverage
```

Run specific model tests:

```bash
vendor/bin/phpunit tests/Unit/EmailAccountTest.php
vendor/bin/phpunit tests/Unit/EmailAccountMessageTest.php
```

## Development

### Prerequisites

- PHP 8.4+ (recommended for best performance)
- Composer 2.x
- Laravel 11.x or 12.x

### Setup Development Environment

1. Clone the repository:
```bash
git clone https://github.com/turahe/mail-client.git
cd mail-client
```

2. Install dependencies:
```bash
composer install
```

3. Run tests:
```bash
composer test
```

### Code Quality

Run static analysis:
```bash
composer analyse
```

Check code style:
```bash
composer check-style
```

Fix code style:
```bash
composer fix
```

Security check:
```bash
composer security-check
```

## CI/CD Pipeline

This project includes a comprehensive GitHub Actions CI/CD pipeline with the following jobs:

### 🔍 **Test Suite**
- **Matrix Testing**: PHP 8.3 and 8.4 with Laravel 11.x and 12.x
- **Unit Tests**: Complete test coverage
- **Feature Tests**: Integration testing
- **Code Coverage**: Uploaded to Codecov

### 🔬 **Static Analysis**
- **PHPStan**: Level 8 static analysis
- **PHP CS Fixer**: Code style validation

### 🔒 **Security Check**
- **Security Checker**: Vulnerability scanning

### 🏗️ **Build**
- **PHAR Building**: Creates executable package
- **Artifact Upload**: Stores build artifacts

### 🚀 **Release**
- **Automatic Releases**: Triggered by git tags
- **Asset Upload**: PHAR files attached to releases

### 📦 **Publish**
- **Packagist Publishing**: Automatic package publishing

## GitHub Secrets Required

To enable full CI/CD functionality, add these secrets to your GitHub repository:

### `PACKAGIST_TOKEN`
Your Packagist API token for automatic publishing.

### `GITHUB_TOKEN`
Automatically provided by GitHub Actions.

## Release Process

### Latest Release: v1.1.0 🎉

The latest version includes comprehensive test suite improvements and enhanced model functionality.

### Creating a Release

1. **Update Changelog**:
   ```bash
   # Update CHANGELOG.md with new version
   ```

2. **Create Tag**:
   ```bash
   git tag -a v1.1.0 -m "Release v1.1.0: Comprehensive test suite and model improvements"
   git push origin v1.1.0
   ```

3. **Automatic Release**: The CI/CD pipeline will:
   - Run all 120+ tests
   - Perform static analysis
   - Build the PHAR file
   - Create a GitHub release
   - Upload the PHAR as a release asset
   - Publish to Packagist (if on main branch)

### Manual Release

```bash
# Build PHAR
composer build

# Create release
gh release create v1.1.0 mail-client.phar --title "Release v1.1.0" --notes "Comprehensive test suite and model improvements"
```

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

### Development Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation as needed
- Ensure all tests pass before submitting PR

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Support

For support, please contact:
- **Email**: wachid@outlook.com
- **Issues**: [GitHub Issues](https://github.com/turahe/mail-client/issues)

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a list of changes and version history. 