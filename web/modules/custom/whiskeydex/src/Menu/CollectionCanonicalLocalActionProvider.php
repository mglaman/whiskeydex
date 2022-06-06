<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Menu;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity\Menu\EntityLocalActionProviderInterface;

final class CollectionCanonicalLocalActionProvider implements EntityLocalActionProviderInterface {

  /**
   * @phpstan-return  array<string, array<string, array<int, string>|string>>
   */
  public function buildLocalActions(EntityTypeInterface $entity_type): array {
    $actions = [];
    $actions['entity.collection_item.add_form'] = [
      'title' => 'Add whiskey',
      'route_name' => 'entity.collection_item.add_form',
      'appears_on' => ['entity.collection.canonical'],
    ];
    $actions['entity.collection.edit_form'] = [
      'title' => 'Edit',
      'route_name' => 'entity.collection.edit_form',
      'appears_on' => ['entity.collection.canonical'],
    ];
    return $actions;
  }

}
