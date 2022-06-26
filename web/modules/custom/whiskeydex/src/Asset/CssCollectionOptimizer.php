<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Asset;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Asset\AssetCollectionOptimizerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;

final class CssCollectionOptimizer implements AssetCollectionOptimizerInterface {

  public function __construct(
    private readonly AssetCollectionOptimizerInterface $inner,
    private readonly TimeInterface $time,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly FileSystemInterface $fileSystem,
    private readonly string $scheme
  ) {
  }

  /**
   * @phpstan-param array<string, mixed> $assets
   * @phpstan-return  array<string, mixed>
   */
  public function optimize(array $assets): array {
    return $this->inner->optimize($assets);
  }

  public function getAll(): array {
    return $this->inner->getAll();
  }

  public function deleteAll(): void {
    $this->inner->deleteAll();

    $threshold = $this->configFactory->get('system.performance')->get('stale_file_threshold');
    $delete_stale = function ($uri) use ($threshold) {
      // Default stale file threshold is 30 days.
      if ($this->time->getRequestTime() - filemtime($uri) > $threshold) {
        $this->fileSystem->delete($uri);
      }
    };
    // The trailing slash is important for compatibility with object storage,
    // where the concept of directories differs between providers.
    if (is_dir($this->scheme . '://css/')) {
      $this->fileSystem->scanDirectory($this->scheme . '://css/', '/.*/', ['callback' => $delete_stale]);
    }
  }

}
