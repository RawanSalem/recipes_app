#!/bin/bash

echo "🚀 Setting up Git and GitHub for Laravel Recipes API"
echo "=================================================="

# Check if Git is configured
if [ -z "$(git config --global user.name)" ]; then
    echo "❌ Git user.name is not configured"
    echo "Please run: git config --global user.name 'Rawan Salem'"
    echo "Please run: git config --global user.email 'rawansd7@gmail.com'"
    exit 1
fi

if [ -z "$(git config --global user.email)" ]; then
    echo "❌ Git user.email is not configured"
    echo "Please run: git config --global user.email 'rawansd7@gmail.com'"
    exit 1
fi

echo "✅ Git is properly configured"
echo "User: $(git config --global user.name)"
echo "Email: $(git config --global user.email)"
echo ""

# Add all files
echo "📁 Adding files to Git..."
git add .

# Check if there are files to commit
if [ -z "$(git status --porcelain)" ]; then
    echo "✅ No changes to commit"
else
    echo "📝 Making initial commit..."
    git commit -m "Initial commit: Laravel Recipes API"
fi

# Rename branch to main
echo "🔄 Renaming branch to main..."
git branch -M main

echo ""
echo "🎯 Next Steps:"
echo "1. Go to GitHub.com and create a new repository named 'recipes_app'"
echo "2. Copy the repository URL (it will look like: https://github.com/yourusername/recipes_app.git)"
echo "3. Run: git remote add origin YOUR_REPOSITORY_URL"
echo "4. Run: git push -u origin main"
echo ""
echo "📋 Repository should include:"
echo "   - Laravel API with authentication"
echo "   - Recipe, Category, Rating, and Favorite management"
echo "   - Comprehensive test suite"
echo "   - GitHub Actions CI/CD"
echo "   - OpenAPI documentation"
echo ""
echo "✨ Your Laravel Recipes API is ready for GitHub!" 