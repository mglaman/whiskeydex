<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Menu;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity\Menu\EntityCollectionLocalActionProvider;

final class CollectionItemCollectionLocalActionProvider extends EntityCollectionLocalActionProvider {

  public function buildLocalActions(EntityTypeInterface $entity_type) {
    // @needs custom action which searches for whiskey first.
    return [];
  }

}
