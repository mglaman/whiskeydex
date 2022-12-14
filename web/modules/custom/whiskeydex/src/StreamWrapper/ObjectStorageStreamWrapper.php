<?php declare(strict_types=1);

namespace Drupal\whiskeydex\StreamWrapper;

use Aws\Credentials\Credentials;
use Aws\LruArrayCache;
use Aws\S3\S3Client;
use Aws\S3\StreamWrapper;
use Drupal\Component\Utility\Crypt;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\image\Entity\ImageStyle;
use GuzzleHttp\Psr7\Uri;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @phpcs:disable Drupal.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
 * @phpcs:disable DrupalPractice.Objects.GlobalClass.GlobalClass
 * @phpcs:disable DrupalPractice.Objects.GlobalDrupal.GlobalDrupal
 * @phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
 */
final class ObjectStorageStreamWrapper extends StreamWrapper implements StreamWrapperInterface, ConfigurableStreamWrapperInterface {

  private string $uri;

  private function addBucketToPath(string $path): string {
    $split = explode('://', $path, 2);
    $path = $split[0] . '://' . getenv('S3_BUCKET') . '/' . $split[1];
    if (pathinfo($path, PATHINFO_EXTENSION) === '') {
      $path .= '/';
    }
    return $path;
  }

  public function url_stat($path, $flags) {
    return parent::url_stat($this->addBucketToPath($path), $flags);
  }

  public function mkdir($path, $mode, $options) {
    $path = rtrim($this->addBucketToPath($path), '\/') . '/';
    return parent::mkdir($path, $mode, $options);
  }

  public function rmdir($path, $options) {
    $path = rtrim($this->addBucketToPath($path), '\/') . '/';
    return parent::rmdir($path, $options);
  }

  public function dir_opendir($path, $options) {
    return parent::dir_opendir($this->addBucketToPath($path), $options);
  }

  public function stream_open($path, $mode, $options, &$opened_path) {
    return parent::stream_open($this->addBucketToPath($path), $mode, $options, $opened_path);
  }

  public function rename($path_from, $path_to) {
    // @todo does this need `addBucketToPath`? Only if renaming within S3?
    return parent::rename($path_from, $path_to);
  }

  public function unlink($path) {
    return parent::unlink($this->addBucketToPath($path));
  }

  public function stream_lock($operation) {
    return TRUE;
  }

  public function stream_metadata($path, $option, $value) {
    return match ($option) {
      STREAM_META_ACCESS => TRUE,
      default => FALSE,
    };
  }

  public function stream_set_option($option, $arg1, $arg2) {
    return FALSE;
  }

  public function stream_truncate($new_size) {
    return FALSE;
  }

  public static function getType(): int {
    return self::WRITE_VISIBLE;
  }

  public function getName(): string {
    return 'Object storage (S3)';
  }

  public function getDescription(): string {
    return 'Flysystem S3 stream wrapper for object storage support';
  }

  public function setUri($uri): void {
    $this->uri = $uri;
  }

  public function getUri(): string {
    return $this->uri;
  }

  public function getExternalUrl(): string {
    $target = $this->getTarget($this->uri);
    if (str_starts_with($target, 'styles/') && !file_exists($this->uri)) {
      $this->generateImageStyle($target);
    }
    $uri = (new Uri)
      ->withScheme('https')
      ->withHost(getenv('S3_CNAME'));
    if (getenv('S3_USE_PATH_STYLE_ENDPOINT') === 'true') {
      $target = getenv('S3_BUCKET') . '/' . $target;
    }
    return (string) $uri->withPath('/' . $target);
  }

  public function realpath() {
    return FALSE;
  }

  public function dirname($uri = NULL): string {
    if ($uri === NULL) {
      $uri = $this->uri;
    }
    $scheme = StreamWrapperManager::getScheme($uri);
    $dirname = dirname(StreamWrapperManager::getTarget($uri));
    if ($dirname === '.') {
      $dirname = '';
    }

    return "$scheme://$dirname";

  }

  private function getTarget(string $uri): string {
    return substr($uri, strpos($uri, '://') + 3);
  }

  public static function getContextDefaults(ContainerInterface $container): array {
    try {
      // @todo inject from factory from container.
      // @todo test (https://github.com/aws/aws-sdk-php/issues/2023, https://github.com/aws/aws-sdk-php/issues/1043)
      $client = new S3Client([
        'version' => 'latest',
        'region' => getenv('AWS_DEFAULT_REGION'),
        'endpoint' => getenv('S3_ENDPOINT'),
        'use_path_style_endpoint' => getenv('S3_USE_PATH_STYLE_ENDPOINT') === 'true',
        'credentials' => new Credentials(
          getenv('AWS_ACCESS_KEY_ID'),
          getenv('AWS_SECRET_ACCESS_KEY'),
        ),
      ]);
    }
    // @todo bubble error for production debugging.
    catch (\Exception $exception) {
      $client = NULL;
    }
    $defaults['client'] = $client;
    // @todo support Drupal's cache layers.
    $defaults['cache'] = new LruArrayCache();
    $defaults['ACL'] = 'public-read';
    return $defaults;
  }

  protected function generateImageStyle($target) {
    if (!str_starts_with($target, 'styles/') || substr_count($target, '/') < 3) {
      return FALSE;
    }

    [, $style, $scheme, $file] = explode('/', $target, 4);

    if (!$image_style = ImageStyle::load($style)) {
      return FALSE;
    }

    $image_uri = $scheme . '://' . $file;

    $derivative_uri = $image_style->buildUri($image_uri);

    if (!file_exists($image_uri)) {
      $path_info = pathinfo($image_uri);
      $converted_image_uri = $path_info['dirname'] . '/' . $path_info['filename'];

      if (!file_exists($converted_image_uri)) {
        return FALSE;
      }

      // The converted file does exist, use it as the source.
      $image_uri = $converted_image_uri;
    }

    $lock_name = 'image_style_deliver:' . $image_style->id() . ':' . Crypt::hashBase64($image_uri);

    if (!file_exists($derivative_uri)) {
      $lock_acquired = \Drupal::lock()->acquire($lock_name);
      if (!$lock_acquired) {
        return FALSE;
      }
    }

    $success = file_exists($derivative_uri) || $image_style->createDerivative($image_uri, $derivative_uri);

    if (!empty($lock_acquired)) {
      \Drupal::lock()->release($lock_name);
    }

    return $success;
  }

}
