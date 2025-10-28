#!/bin/bash

# Initialize Git repository
git init

# Configure user
git config user.name "Claude"
git config user.email "claude@anthropic.com"

# Add all files
git add -A

# Create initial commit
git commit -m "✨ Initialize TWELVY project with POST/GET API testing

- Next.js 16 project structure
- API proxy routes for OVH integration
- Test page with POST and GET buttons
- Complete CLAUDE.md documentation
- Environment configuration templates

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"

# Add remote
git remote add origin https://github.com/yakeeniacloud/TWELVY.git

# Push to GitHub
git branch -M main
git push -u origin main

echo "✅ Git repository initialized and pushed to GitHub!"
