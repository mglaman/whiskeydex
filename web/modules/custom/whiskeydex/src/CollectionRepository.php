<?php declare(strict_types=1);

namespace Drupal\whiskeydex;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\whiskeydex\Entity\Collection;

final class CollectionRepository {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  )
  {
  }

  public function getUsersCollections(): array {
    $storage = $this->entityTypeManager->getStorage('collection');
    $ids = $storage->getQuery()->accessCheck(TRUE)->execute();
    return $storage->loadMultiple($ids);
  }

  /**
   * @phpstan-return array<int, \Drupal\whiskeydex\Entity\CollectionItem>
   */
  public function getItems(Collection $collection): array {
    $storage = $this->entityTypeManager->getStorage('collection_item');
    $item_ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('collection', $collection->id())
      ->execute();
    return $storage->loadMultiple($item_ids);
  }

}
