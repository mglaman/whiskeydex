<?php declare(strict_types=1);

namespace Drupal\whiskeydex;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityHandlerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilderInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Component\Utility\SortArray;

final class CollectionListBuilder implements EntityListBuilderInterface, EntityHandlerInterface {

  use StringTranslationTrait;

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager
  ) {
  }

  public static function createInstance(
    ContainerInterface $container,
    EntityTypeInterface $entity_type
  ): self {
    $instance = new self(
      $container->get('entity_type.manager'),
    );
    $instance->setStringTranslation($container->get('string_translation'));
    return $instance;
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function render(): array {
    $cacheability = new CacheableMetadata();
    $cacheability->addCacheContexts(['user']);
    $view_builder = $this->entityTypeManager->getViewBuilder('collection');
    $build = [
      'list' => $view_builder->viewMultiple($this->load(), 'card'),
      '#theme_wrappers' => [
        'container__collection',
      ],
    ];
    $cacheability->applyTo($build);
    return $build;
  }

  public function getStorage(): EntityStorageInterface {
    return $this->entityTypeManager->getStorage('collection');
  }

  public function load(): array {
    $query = $this->getStorage()->getQuery()
      ->accessCheck(TRUE)
      ->sort('collection_id')
      ->pager(10);
    $ids = $query->execute();
    return $this->getStorage()->loadMultiple($ids);
  }

  public function getOperations(EntityInterface $entity): array {
    $operations = [];
    if ($entity->access('update') && $entity->hasLinkTemplate('edit-form')) {
      $operations['edit'] = [
        'title' => $this->t('Edit'),
        'weight' => 10,
        'url' => $entity->toUrl('edit-form'),
      ];
    }
    if ($entity->access('delete') && $entity->hasLinkTemplate('delete-form')) {
      $operations['delete'] = [
        'title' => $this->t('Delete'),
        'weight' => 100,
        'url' => $entity->toUrl('delete-form'),
      ];
    }
    uasort($operations, [SortArray::class, 'sortByWeightElement']);

    return $operations;

  }

}
