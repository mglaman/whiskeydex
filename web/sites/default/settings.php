<?php

use Drupal\Core\Installer\InstallerKernel;

$settings['config_sync_directory'] = '../config/export';

// @todo it'd be great if the config exclude service wasn't locked to settings
// but instead service parameters or another way to influence.
$settings['config_exclude_modules'] = [
  'devel',
  'stage_file_proxy',
];

$settings['hash_salt'] = getenv('DRUPAL_HASH_SALT') ?: NULL;

$settings['deployment_identifier'] = getenv('DEPLOYMENT_IDENTIFIER')?: NULL;

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
if (getenv('DB_CONNECTION') !== 'sqlite') {
  $databases['default']['default'] = [
    'driver' => getenv('DB_CONNECTION'),
    'database' => getenv('DRUPAL_DATABASE_NAME'),
    'username' => getenv('DRUPAL_DATABASE_USERNAME'),
    'password' => getenv('DRUPAL_DATABASE_PASSWORD'),
    'host' => getenv('DRUPAL_DATABASE_HOST'),
    'port' => getenv('DRUPAL_DATABASE_PORT'),
  ];
}
else {
  $databases['default']['default'] = [
    'database' => '../private/db.sqlite',
    'prefix' => '',
    'namespace' => 'Drupal\\Core\\Database\\Driver\\sqlite',
    'driver' => 'sqlite',
  ];
}

// Reverse proxy detection.
// Stolen from the trusted_reverse_proxy module.
if (isset($_SERVER['HTTP_X_FORWARDED_FOR'], $_SERVER['REMOTE_ADDR'])) {
  $settings['reverse_proxy'] = TRUE;

  // First hop is assumed to be a reverse proxy in its own right.
  $proxies = [$_SERVER['REMOTE_ADDR']];
  // We may be further behind another reverse proxy (e.g., Traefik, Varnish)
  // Commas may or may not be followed by a space.
  // @see https://tools.ietf.org/html/rfc7239#section-7.1
  $forwardedFor = explode(
    ',',
    str_replace(', ', ',', $_SERVER['HTTP_X_FORWARDED_FOR'])
  );
  if (count($forwardedFor) > 1) {
    // The first value will be the actual client IP.
    array_shift($forwardedFor);
    array_unshift($proxies, ...$forwardedFor);
  }

  $settings['reverse_proxy_addresses'] = $proxies;
}

// If a filesystem driver is defined and uses object storage, include config.
if (!empty(getenv('FILESYSTEM_DRIVER')) && getenv('FILESYSTEM_DRIVER') === 's3' && !InstallerKernel::installationAttempted()) {
  // If the filesystem isn't local, move Twig to the temporary directory.
  $settings['php_storage']['twig']['directory'] = sys_get_temp_dir();
  // We cannot support a private file path if using object storage.
  unset($settings['file_private_path']);
  // Set s3:// as default scheme.
  $config['system.file']['default_scheme'] = 's3';
}


if (file_exists($app_root . '/' . $site_path . '/settings.platformsh.php')) {
  include $app_root . '/' . $site_path . '/settings.platformsh.php';
}

/**
 * Include local environment overrides.
 */
if (file_exists(__DIR__ . '/settings.local.php')) {
  include __DIR__ . '/settings.local.php';
}

// Automatically generated include for settings managed by ddev.
$ddev_settings = __DIR__ . '/settings.ddev.php';
if (getenv('IS_DDEV_PROJECT') === 'true' && is_readable($ddev_settings)) {
  require $ddev_settings;
}
