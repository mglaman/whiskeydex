<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity\Routing\DefaultHtmlRouteProvider;
use Symfony\Component\Routing\Route;

final class CollectionHtmlRouteProvider extends DefaultHtmlRouteProvider {

  protected function getCollectionRoute(EntityTypeInterface $entity_type): ?Route {
    $route = parent::getCollectionRoute($entity_type);
    $route?->setRequirement('_permission', 'view own collection');
    return $route;
  }

}
