# GitHub Actions CI Setup Guide

This guide explains how to set up and use the GitHub Actions CI workflow for your Laravel Recipes API.

## 🚀 What the CI Does

The CI workflow automatically runs on:
- **Push** to `main` or `develop` branches
- **Pull Requests** to `main` or `develop` branches

### Jobs Included:

1. **🧪 Test Job**
   - Sets up PHP 8.2 with all required extensions
   - Creates MySQL 8.0 test database
   - Runs Laravel migrations
   - Executes all feature tests
   - Provides test results summary

2. **🔍 Code Quality Job**
   - Validates PHP syntax for all files
   - Checks Composer configuration
   - Verifies Laravel application setup

3. **🛡️ Security Job**
   - Runs `composer audit` to check for known vulnerabilities
   - Scans dependencies for security issues

4. **📢 Notification Job**
   - Provides clear success/failure notifications
   - Shows summary of all checks

## 📋 Prerequisites

Before setting up CI, ensure you have:

1. **GitHub Repository**: Your code must be in a GitHub repository
2. **Branch Structure**: Main branch should be `main` or `master`
3. **Laravel Application**: Properly configured Laravel app with tests

## ⚙️ Setup Instructions

### 1. Push Your Code to GitHub

```bash
# If you haven't already
git init
git add .
git commit -m "Initial commit with CI setup"
git branch -M main
git remote add origin https://github.com/yourusername/recipes_app.git
git push -u origin main
```

### 2. Verify Workflow File

The CI workflow file should be at:
```
.github/workflows/ci.yml
```

### 3. Check GitHub Actions

1. Go to your GitHub repository
2. Click on the **Actions** tab
3. You should see the "Laravel CI" workflow
4. Click on it to see the workflow details

## 🔧 Configuration Options

### Environment Variables

If you need to customize the CI, you can add environment variables in GitHub:

1. Go to your repository **Settings**
2. Click **Secrets and variables** → **Actions**
3. Add any required secrets

### Database Configuration

The CI uses MySQL 8.0 with these default settings:
- **Database**: `recipes_app_test`
- **Username**: `test_user`
- **Password**: `password`
- **Host**: `127.0.0.1`
- **Port**: `3306`

### PHP Version

Currently set to PHP 8.2. To change:
```yaml
php-version: '8.1'  # or '8.0', '8.3'
```

## 📊 Understanding CI Results

### ✅ Success Indicators
- All tests pass (75+ tests)
- No PHP syntax errors
- Composer validation passes
- No security vulnerabilities found

### ❌ Common Failure Points
- **Test Failures**: Check test output for specific failures
- **Database Issues**: Ensure migrations work correctly
- **Syntax Errors**: PHP files with syntax issues
- **Security Issues**: Vulnerable dependencies

### 🔍 Debugging Failed CI

1. **Check the Actions Tab**: See detailed logs for each job
2. **Review Test Output**: Look for specific test failures
3. **Check Database**: Ensure migrations work locally
4. **Verify Dependencies**: Run `composer install` locally

## 🛠️ Local Testing

Before pushing, test locally:

```bash
# Run all tests
php artisan test

# Check PHP syntax
find app -name "*.php" -exec php -l {} \;
find tests -name "*.php" -exec php -l {} \;

# Validate composer
composer validate

# Security check
composer audit
```

## 📈 CI Benefits

1. **Automated Testing**: Every push/PR is automatically tested
2. **Quality Assurance**: Catches issues before they reach production
3. **Security Scanning**: Identifies vulnerable dependencies
4. **Team Collaboration**: Ensures code quality across team members
5. **Deployment Confidence**: Only deploy code that passes all checks

## 🔄 Workflow Triggers

The CI runs automatically on:
- ✅ Push to `main` branch
- ✅ Push to `develop` branch  
- ✅ Pull Request to `main` branch
- ✅ Pull Request to `develop` branch

## 📝 Customization

### Adding More Jobs

To add additional checks, edit `.github/workflows/ci.yml`:

```yaml
# Example: Add a linting job
lint:
  runs-on: ubuntu-latest
  needs: test
  steps:
    - name: Checkout code
      uses: actions/checkout@v4
    # Add your linting steps here
```

### Modifying Test Commands

To change how tests are run:

```yaml
- name: Run Tests
  run: php artisan test --parallel  # Run tests in parallel
```

### Adding Notifications

To add Slack/Discord notifications, add to the notify job:

```yaml
- name: Notify Slack
  uses: 8398a7/action-slack@v3
  with:
    status: ${{ job.status }}
    webhook_url: ${{ secrets.SLACK_WEBHOOK }}
```

## 🎯 Best Practices

1. **Always Test Locally First**: Run tests before pushing
2. **Keep Tests Fast**: Optimize test execution time
3. **Monitor CI Regularly**: Check for failures and fix quickly
4. **Use Feature Branches**: Create PRs for new features
5. **Review CI Output**: Understand what each job does

## 🆘 Troubleshooting

### Common Issues

**Tests Fail in CI but Pass Locally**
- Check database configuration differences
- Verify environment variables
- Ensure all dependencies are installed

**CI Times Out**
- Optimize test execution
- Reduce unnecessary steps
- Use parallel testing if possible

**Security Check Fails**
- Update vulnerable dependencies
- Review `composer audit` output
- Consider using `composer update`

### Getting Help

1. Check the GitHub Actions documentation
2. Review Laravel testing documentation
3. Look at similar projects' CI configurations
4. Ask in Laravel community forums

---

## 🎉 Success!

Once your CI is set up and running, you'll have:
- ✅ Automated testing on every push
- ✅ Code quality checks
- ✅ Security scanning
- ✅ Clear feedback on code changes
- ✅ Confidence in your deployments

Your Laravel Recipes API is now protected by a robust CI pipeline! 🚀 