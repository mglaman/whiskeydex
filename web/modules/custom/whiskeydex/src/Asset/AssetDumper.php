<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Asset;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Asset\AssetDumperInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;

/**
 * Fixes hardcoded `public://` in \Drupal\Core\Asset\AssetDumper::dump().
 *
 * @see \Drupal\Core\Asset\AssetDumper::dump()
 */
final class AssetDumper implements AssetDumperInterface {

  public function __construct(
    private readonly FileSystemInterface $fileSystem
  ) {
  }

  public function dump($data, $file_extension) {
    $filename = $file_extension . '_' . Crypt::hashBase64($data) . '.' . $file_extension;
    $path = 's3://' . $file_extension;
    $uri = $path . '/' . $filename;
    // Create the CSS or JS file.
    $this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);
    try {
      if (!file_exists($uri) && !$this->fileSystem->saveData($data, $uri, FileSystemInterface::EXISTS_REPLACE)) {
        return FALSE;
      }
    }
    catch (FileException $e) {
      return FALSE;
    }
    if (extension_loaded('zlib') && \Drupal::config('system.performance')->get($file_extension . '.gzip')) {
      try {
        if (!file_exists($uri . '.gz') && !$this->fileSystem->saveData(gzencode($data, 9, FORCE_GZIP), $uri . '.gz', FileSystemInterface::EXISTS_REPLACE)) {
          return FALSE;
        }
      }
      catch (FileException $e) {
        return FALSE;
      }
    }
    return $uri;
  }

}
