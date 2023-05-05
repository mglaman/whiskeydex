<?php

declare(strict_types=1);

use Dotenv\Dotenv;


// Note: we cannot use createImmutable() as subprocess commands used by Drush
// are missing $_ENV contents, but work with `getenv`.
// @todo open Drush issue
// @link https://drupal.slack.com/archives/C62H9CWQM/p1654540748519809
$dotenv = Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->safeLoad();
