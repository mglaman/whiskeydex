<?php declare(strict_types=1);

namespace Drupal\whiskeydex\StreamWrapper;

use Aws\LruArrayCache;
use Drupal\Core\StreamWrapper\StreamWrapperManager as CoreStreamWrapperManager;

/**
 * Extends vs inner due to static methods on interface.
 */
final class StreamWrapperManager extends CoreStreamWrapperManager {

  /**
   * @phpstan-param class-string $class
   */
  public function registerWrapper($scheme, $class, $type) {
    parent::registerWrapper($scheme, $class, $type);
    if (is_a($class, ObjectStorageStreamWrapper::class, TRUE)) {
      $client = $class::getClient();
      $default = stream_context_get_options(stream_context_get_default());
      $default[$scheme]['client'] = $client;
      // @todo support Drupal's cache layers.
      $default[$scheme]['cache'] = new LruArrayCache();
      $default[$scheme]['ACL'] = 'public-read';
      stream_context_set_default($default);
    }
  }

}
