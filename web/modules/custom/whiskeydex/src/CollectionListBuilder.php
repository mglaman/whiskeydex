<?php declare(strict_types=1);

namespace Drupal\whiskeydex;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\whiskeydex\Entity\Collection;

final class CollectionListBuilder extends EntityListBuilder {

  private CacheableMetadata $cacheability;

  protected $limit = 10;

  /**
   * @phpstan-return array<string, mixed>
   */
  public function render(): array {
    // Hack to add more granular cacheability to the list.
    $this->cacheability = new CacheableMetadata();
    $this->cacheability->addCacheContexts(['user']);
    $build = parent::render();
    unset($build['table']['#cache']);
    $this->cacheability->applyTo($build);
    return $build;
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-return array<string, array<string, array<int, string>|\Drupal\Core\StringTranslation\TranslatableMarkup>|\Drupal\Core\StringTranslation\TranslatableMarkup>
   */
  public function buildHeader(): array {
    return [
      'label' => $this->t('Name'),
      'count' => $this->t('Count'),
      'operations' => [
        'data' => $this->t('Manage'),
        'class' => ['sr-only'],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-return array<string, string|\Drupal\Core\StringTranslation\TranslatableMarkup|mixed>
   */
  public function buildRow(EntityInterface $entity): array {
    assert($entity instanceof Collection);
    $this->cacheability->addCacheableDependency($entity);
    $row['label'] = $entity->toLink((string) $entity->label());
    $row['count'] = $entity->itemsCount();
    return $row + parent::buildRow($entity);
  }

}
