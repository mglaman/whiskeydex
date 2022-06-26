<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Asset;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Asset\AssetCollectionOptimizerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;

final class JsCollectionOptimizer implements AssetCollectionOptimizerInterface {

  public function __construct(
    private readonly AssetCollectionOptimizerInterface $inner,
    private readonly TimeInterface $time,
    private readonly ConfigFactoryInterface $configFactory,
    private readonly FileSystemInterface $fileSystem
  ) {
  }

  public function optimize(array $assets) {
    return $this->inner->optimize($assets);
  }

  public function getAll() {
    return $this->inner->getAll();
  }

  public function deleteAll() {
    $this->inner->deleteAll();

    $threshold = $this->configFactory->get('system.performance')->get('stale_file_threshold');
    $delete_stale = function ($uri) use ($threshold) {
      // Default stale file threshold is 30 days.
      if ($this->time->getRequestTime() - filemtime($uri) > $threshold) {
        $this->fileSystem->delete($uri);
      }
    };
    if (is_dir('s3://js')) {
      $this->fileSystem->scanDirectory('s3://js', '/.*/', ['callback' => $delete_stale]);
    }
  }

}
