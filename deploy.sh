#!/bin/bash

set -e

VPS_USER="root"
VPS_HOST="72.61.112.137"
PROJECT_DIR="/var/www/sira.sardarit.cloud"

echo ""
echo "╔══════════════════════════════════════╗"
echo "║        Deploying to VPS...           ║"
echo "╚══════════════════════════════════════╝"
echo ""
echo "→ Host:    $VPS_HOST"
echo "→ User:    $VPS_USER"
echo "→ Project: $PROJECT_DIR"
echo ""
echo "Connecting to VPS..."

ssh $VPS_USER@$VPS_HOST bash << EOF
  set -e

  echo ""
  echo "✔ Connected to VPS"
  echo ""

  echo "──────────────────────────────────────"
  echo " [1/5] Pulling latest code from GitHub"
  echo "──────────────────────────────────────"
  cd $PROJECT_DIR
  git fetch origin
  git reset --hard origin/main
  echo "✔ Code updated to: \$(git log -1 --pretty=format:'%h - %s (%an, %ar)')"

  echo ""
  echo "──────────────────────────────────────"
  echo " [2/5] Installing PHP dependencies"
  echo "──────────────────────────────────────"
  composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
  echo "✔ Composer dependencies installed"

  echo ""
  echo "──────────────────────────────────────"
  echo " [3/5] Installing Node dependencies & building assets"
  echo "──────────────────────────────────────"
  npm ci
  npm run build
  echo "✔ Assets built successfully"

  echo ""
  echo "──────────────────────────────────────"
  echo " [4/5] Running database migrations"
  echo "──────────────────────────────────────"
  php artisan migrate --force
  echo "✔ Migrations completed"

  echo ""
  echo "──────────────────────────────────────"
  echo " [5/5] Optimizing Laravel"
  echo "──────────────────────────────────────"
  php artisan optimize
  echo "✔ Laravel optimized"

  echo ""
  echo "╔══════════════════════════════════════╗"
  echo "║     ✓ Deployment Successful!         ║"
  echo "╚══════════════════════════════════════╝"
  echo ""
  echo "→ Deployed at: \$(date)"
  echo "→ Commit: \$(git log -1 --pretty=format:'%h - %s')"
  echo ""
EOF
