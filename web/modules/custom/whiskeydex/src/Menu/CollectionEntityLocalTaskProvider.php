<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Menu;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity\Menu\DefaultEntityLocalTaskProvider;

final class CollectionEntityLocalTaskProvider extends DefaultEntityLocalTaskProvider {

  /**
   * @phpstan-return array<string, array<string, string|int>>
   */
  public function buildLocalTasks(EntityTypeInterface $entity_type): array {
    $tasks = parent::buildLocalTasks($entity_type);
    $tasks['entity.collection_item.add_form'] = [
      'title' => 'Add whiskey',
      'route_name' => 'entity.collection_item.add_form',
      'base_route' => 'entity.collection.canonical',
      'weight' => count($tasks) * 10,
    ];
    return $tasks;
  }

}
