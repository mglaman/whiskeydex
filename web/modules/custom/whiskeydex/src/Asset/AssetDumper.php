<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Asset;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Asset\AssetDumperInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;

/**
 * Fixes hardcoded `public://` in \Drupal\Core\Asset\AssetDumper::dump().
 *
 * @see \Drupal\Core\Asset\AssetDumper::dump()
 */
final class AssetDumper implements AssetDumperInterface {

  public function __construct(
    private readonly FileSystemInterface $fileSystem,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly string $scheme
  ) {
  }

  /**
   * Drupal core is broken, should return string but parent returns false.
   */
  public function dump($data, $file_extension): string {
    $filename = $file_extension . '_' . Crypt::hashBase64($data) . '.' . $file_extension;
    $path = $this->scheme . '://' . $file_extension;
    $uri = $path . '/' . $filename;
    // Create the CSS or JS file.
    $this->fileSystem->prepareDirectory($path, FileSystemInterface::CREATE_DIRECTORY);
    try {
      if (!file_exists($uri) && !$this->fileSystem->saveData($data, $uri, FileSystemInterface::EXISTS_REPLACE)) {
        return '';
      }
    }
    catch (FileException $e) {
      return '';
    }
    if (extension_loaded('zlib') && $this->configFactory->get('system.performance')->get($file_extension . '.gzip')) {
      try {
        $encoded = gzencode($data, 9, FORCE_GZIP);
        if ($encoded === FALSE) {
          return '';
        }
        if (!file_exists($uri . '.gz') && !$this->fileSystem->saveData($encoded, $uri . '.gz', FileSystemInterface::EXISTS_REPLACE)) {
          return '';
        }
      }
      catch (FileException $e) {
        return '';
      }
    }
    return $uri;
  }

}
