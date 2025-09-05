# Packagist Setup Guide

This guide will help you set up automatic releases to Packagist for the Turahe Mail Client package.

## Prerequisites

1. A Packagist account
2. The package must be registered on Packagist
3. GitHub repository with proper permissions

## Setup Steps

### 1. Register Package on Packagist

1. Go to [Packagist.org](https://packagist.org)
2. Click "Submit" in the top navigation
3. Enter your GitHub repository URL: `https://github.com/turahe/mail-client`
4. Click "Check" to verify the package
5. Click "Submit" to register the package

### 2. Configure GitHub Secrets

Add the following secrets to your GitHub repository:

1. Go to your repository on GitHub
2. Click "Settings" → "Secrets and variables" → "Actions"
3. Click "New repository secret" and add:

   - **Name**: `PACKAGIST_USERNAME`
   - **Value**: Your Packagist username

   - **Name**: `PACKAGIST_TOKEN`
   - **Value**: Your Packagist API token (get from Packagist profile)

### 3. Enable Packagist Auto-Update

1. Go to your package page on Packagist
2. Click "Settings" tab
3. Enable "Auto-update" if not already enabled
4. Add GitHub webhook URL: `https://packagist.org/api/github?username=YOUR_USERNAME&apiToken=YOUR_TOKEN`

### 4. Test the Release

1. Create a new tag: `git tag v1.2.1`
2. Push the tag: `git push origin v1.2.1`
3. Check the GitHub Actions tab to see the release workflow
4. Verify the package is updated on Packagist

## Workflow Features

The release workflow (`release.yml`) includes:

- ✅ Automatic GitHub release creation
- ✅ Package archive generation
- ✅ Packagist notification
- ✅ Release notes from CHANGELOG.md
- ✅ Proper version tagging
- ✅ Error handling and notifications

## Manual Release Process

If you need to manually trigger a release:

1. Update the version in `composer.json` (if needed)
2. Update `CHANGELOG.md` with release notes
3. Commit and push changes
4. Create and push a new tag:
   ```bash
   git tag -a v1.2.1 -m "Release v1.2.1"
   git push origin v1.2.1
   ```

## Troubleshooting

### Common Issues

1. **Packagist not updating**: Check if the webhook is properly configured
2. **Authentication errors**: Verify `PACKAGIST_USERNAME` and `PACKAGIST_TOKEN` secrets
3. **Release fails**: Check GitHub Actions logs for specific error messages

### Support

- [Packagist Documentation](https://packagist.org/about)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Composer Documentation](https://getcomposer.org/doc/)

## Security Notes

- Never commit Packagist credentials to the repository
- Use GitHub Secrets for sensitive information
- Regularly rotate API tokens
- Monitor release logs for any suspicious activity
