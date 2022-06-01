<?php

$settings['config_sync_directory'] = '../config/export';

// @todo it'd be great if the config exclude service wasn't locked to settings
// but instead service parameters or another way to influence.
$settings['config_exclude_modules'] = [
  'devel',
  'stage_file_proxy',
];

$settings['hash_salt'] = $_ENV['DRUPAL_HASH_SALT'] ?: '';

$settings['deployment_identifier'] = $_ENV['DEPLOYMENT_IDENTIFIER'] ?: \Drupal::VERSION;

$settings['update_free_access'] = FALSE;
$settings['allow_authorize_operations'] = FALSE;

$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
  'vendor',
];
$settings['entity_update_batch_size'] = 50;
$settings['entity_update_backup'] = TRUE;
$settings['migrate_node_migrate_type_classic'] = FALSE;

/**
 * Database connection information.
 */
if ($_ENV['DB_CONNECTION'] !== 'sqlite') {
  $databases['default']['default'] = [
    'driver' => $_ENV['DB_CONNECTION'],
    'database' => $_ENV['DRUPAL_DATABASE_NAME'],
    'username' => $_ENV['DRUPAL_DATABASE_USERNAME'],
    'password' => $_ENV['DRUPAL_DATABASE_PASSWORD'],
    'host' => $_ENV['DRUPAL_DATABASE_HOST'],
    'port' => $_ENV['DRUPAL_DATABASE_PORT'],
  ];
}
else {
  $databases['default']['default'] = array (
    'database' => '../private/db.sqlite',
    'prefix' => '',
    'namespace' => 'Drupal\\Core\\Database\\Driver\\sqlite',
    'driver' => 'sqlite',
  );
}

/**
 * Include local environment overrides.
 */
if (file_exists(__DIR__ . '/settings.local.php')) {
  include __DIR__ . '/settings.local.php';
}
