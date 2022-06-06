#!/usr/bin/env bash
set -euo pipefail

php vendor/bin/drush status
# @todo deploy is too expensive, call updb/cim directly.
[[ $(php vendor/bin/drush status bootstrap) =~ "Successful" ]] && php vendor/bin/drush deploy

/usr/sbin/apache2ctl -D FOREGROUND
