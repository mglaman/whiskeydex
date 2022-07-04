<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity\Routing\DefaultHtmlRouteProvider;
use Symfony\Component\Routing\Route;

final class CollectionItemHtmlRouteProvider extends DefaultHtmlRouteProvider {

  protected function getCollectionRoute(EntityTypeInterface $entity_type): ?Route {
    $route = parent::getCollectionRoute($entity_type);
    $route?->setRequirement('_permission', 'view own collection_item');
    return $route;
  }

  protected function getAddFormRoute(EntityTypeInterface $entity_type) {
    $route = parent::getAddFormRoute($entity_type);
    if ($route) {
      /** @var array<string, mixed> $parameters */
      $parameters = $route->getOption('parameters');
      $parameters['whiskey'] = [
        'type' => 'entity:whiskey',
      ];
      $route->setOption('parameters', $parameters);
    }
    return $route;
  }

}
