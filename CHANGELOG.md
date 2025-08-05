# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Comprehensive test suite for EmailAccount model
- GitHub Actions CI/CD pipeline
- Static analysis with PHPStan
- Code style checking with PHP CS Fixer
- Security vulnerability scanning
- PHAR building capability
- Automatic release management
- Packagist publishing automation
- **PHP 8.3 Features**: Enhanced enums with methods and improved functionality
- **PHP 8.3 Features**: New EmailAccountManager service with modern PHP patterns
- **PHP 8.3 Features**: EmailAccountConfig DTO with readonly properties and constructor validation
- **PHP 8.3 Features**: HasEmailAccountValidation trait with match expressions and improved validation

### Changed
- Enhanced composer.json with additional dev dependencies
- Improved package description and documentation
- Added composer scripts for development workflow
- Updated license from proprietary to MIT
- Updated CI/CD matrix to support only PHP 8.3 and 8.4
- Updated CI/CD matrix to support only Laravel 11 and 12
- Removed support for PHP 8.1 and 8.2
- Removed support for Laravel 10
- **Enhanced Enums**: Added methods to ConnectionType, SyncState, and EmailAccountType enums
- **Improved Type Safety**: Better type declarations throughout the codebase
- **Modern PHP Patterns**: Used match expressions, readonly properties, and constructor property promotion

### Fixed
- Polymorphic relationship handling in tests
- Database migration compatibility issues
- Factory implementations for test data
- Box package name in CI configuration
- **Type Declaration Issues**: Fixed property type redeclaration conflicts with Laravel Model class

## [1.0.0] - 2024-01-XX

### Added
- Initial release of Turahe Mail Client package
- Email account management with IMAP/SMTP support
- Multi-provider support (Gmail, Outlook, IMAP)
- Folder management and organization
- Message synchronization capabilities
- Laravel integration with service provider
- Comprehensive model relationships
- Soft delete functionality
- User stamping support
- ULID primary keys

### Features
- Email sending and receiving
- Message reply and forward functionality
- Attachment handling
- Read/unread message tracking
- Email account synchronization states
- Polymorphic relationships for user associations
- Scheduled email functionality
- Message composition and formatting

### Technical
- Laravel 11.x and 12.x compatibility
- PHP 8.3 and 8.4 support only
- PSR-12 coding standards
- Comprehensive test coverage
- Database migrations and seeders
- Service provider integration 