#!/usr/bin/env bash
# Cloud agent build: PECL ext-mcrypt (mixer tests) + ext-sodium (RandomLib source tests).
set -euo pipefail

repo_root="$(cd "$(dirname "$0")/.." && pwd)"

export DEBIAN_FRONTEND=noninteractive

ensure_ondrej() {
  if ! apt-cache show "php${php_ver}-mcrypt" &>/dev/null 2>&1; then
    sudo apt-get update -qq
    sudo apt-get install -y --no-install-recommends software-properties-common ca-certificates gnupg
    sudo add-apt-repository -y ppa:ondrej/php
    sudo apt-get update -qq
  fi
}

php_ver="$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')"

if ! php -m 2>/dev/null | grep -q '^mcrypt$'; then
  ensure_ondrej
  sudo apt-get install -y --no-install-recommends "php${php_ver}-mcrypt"
fi

if ! php -m 2>/dev/null | grep -q '^sodium$'; then
  ensure_ondrej
  sudo apt-get install -y --no-install-recommends "php${php_ver}-sodium"
fi

php -m | grep -q '^mcrypt$'
php -m | grep -q '^sodium$'

cd "$repo_root"
if [[ -f composer.json ]]; then
  composer install --no-interaction
fi
