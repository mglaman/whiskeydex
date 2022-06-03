<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\whiskeydex\CollectionRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class CollectionController implements ContainerInjectionInterface {

  public function __construct(
    private readonly CollectionRepository $collectionRepository
  ) {
  }

  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('whiskeydex.collection_repository')
    );
  }

  public function all(): array {
    $cacheability = new CacheableMetadata();
    $cacheability->addCacheContexts(['user']);
    $collections = $this->collectionRepository->getUsersCollections();
    $build = [
      '#type' => 'table',
      '#header' => [
        'name' => 'Name'
      ],
      '#rows' => [],
      '#empty' => 'Need to create a collection',
    ];
    foreach ($collections as $collection) {
      $cacheability->addCacheableDependency($collection);
      $build['#rows'][$collection->id()] = [
        'name' => $collection->label(),
      ];
    }
    $cacheability->applyTo($build);
    return $build;
  }


}
