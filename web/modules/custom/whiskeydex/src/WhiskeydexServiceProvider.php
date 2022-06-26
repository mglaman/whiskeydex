<?php declare(strict_types=1);

namespace Drupal\whiskeydex;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

final class WhiskeydexServiceProvider extends ServiceProviderBase {

  public function alter(ContainerBuilder $container): void {
    if (getenv('FILESYSTEM_DRIVER') !== 's3') {
      $container->setParameter('asset_scheme', 'public');
    }
  }

}
