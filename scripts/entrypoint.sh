#!/usr/bin/env bash
set -euo pipefail

php vendor/bin/drush status
php vendor/bin/drush deploy

/usr/sbin/apache2ctl -D FOREGROUND
