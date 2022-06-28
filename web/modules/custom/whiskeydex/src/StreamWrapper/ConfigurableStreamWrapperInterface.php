<?php declare(strict_types=1);

namespace Drupal\whiskeydex\StreamWrapper;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface ConfigurableStreamWrapperInterface {

  public static function getContextDefaults(ContainerInterface $container): array;

}
