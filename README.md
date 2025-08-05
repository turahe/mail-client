# Turahe Mail Client

A comprehensive Laravel mail client package for managing email accounts, messages, and folders with support for IMAP, SMTP, and various email providers.

## Features

- 🔐 **Multi-Provider Support**: IMAP, SMTP, Gmail, Outlook
- 📧 **Email Management**: Send, receive, reply, forward emails
- 📁 **Folder Management**: Organize emails in folders
- 🔄 **Sync Capabilities**: Automatic email synchronization
- 📊 **Message Tracking**: Read/unread status, attachments
- 🎯 **Laravel Integration**: Seamless Laravel framework integration
- 🧪 **Comprehensive Testing**: Full test coverage with PHPUnit

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

## Testing

Run the test suite:

```bash
composer test
```

Run with coverage:

```bash
composer test-coverage
```

## Development

### Prerequisites

- PHP 8.3 or 8.4
- Composer
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

### Creating a Release

1. **Update Version**:
   ```bash
   # Update version in composer.json
   composer version 1.0.0
   ```

2. **Create Tag**:
   ```bash
   git tag v1.0.0
   git push origin v1.0.0
   ```

3. **Automatic Release**: The CI/CD pipeline will:
   - Run all tests
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
gh release create v1.0.0 mail-client.phar --title "Release v1.0.0" --notes "Initial release"
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