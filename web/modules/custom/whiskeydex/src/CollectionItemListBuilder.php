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

final class CollectionItemListBuilder implements EntityListBuilderInterface, EntityHandlerInterface {

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
    $view_builder = $this->entityTypeManager->getViewBuilder('collection_item');
    $build = [
      'list' => [
        '#theme_wrappers' => [
          'container__collection',
        ],
      ] + $view_builder->viewMultiple($this->load(), 'card'),
    ];
    $build['pager'] = [
      '#type' => 'pager',
    ];
    $cacheability->applyTo($build);
    return $build;
  }

  public function getStorage(): EntityStorageInterface {
    return $this->entityTypeManager->getStorage('collection_item');
  }

  public function load(): array {
    $query = $this->getStorage()->getQuery()
      ->accessCheck(TRUE)
      ->sort('created', 'DESC')
      ->pager(10);
    $ids = $query->execute();
    // phpstan-drupal detection broke here??
    return $this->getStorage()->loadMultiple($ids);
  }

  /**
   * @phpstan-return array<string, array<string, \Drupal\Core\StringTranslation\TranslatableMarkup|int|\Drupal\Core\Url>>
   */
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
