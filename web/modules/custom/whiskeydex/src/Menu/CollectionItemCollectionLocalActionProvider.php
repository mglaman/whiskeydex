<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Menu;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity\Menu\EntityCollectionLocalActionProvider;

final class CollectionItemCollectionLocalActionProvider extends EntityCollectionLocalActionProvider {

  /**
   * @phpstan-return array<string, mixed>
   */
  public function buildLocalActions(EntityTypeInterface $entity_type) {
    // @needs custom action which searches for whiskey first.
    return [
      'whiskeydex.browse_whiskeys' => [
        'title' => 'Add a whiskey',
        'route_name' => 'whiskeydex.browse_whiskeys',
        'appears_on' => [
          'entity.collection_item.collection',
        ],
      ],
    ];
  }

}
