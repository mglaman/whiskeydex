<?php declare(strict_types=1);

namespace Drupal\whiskeydex;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

final class CollectionListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   *
   * @phpstan-return array<string, string|\Drupal\Core\StringTranslation\TranslatableMarkup>
   */
  public function buildHeader(): array {
    $header['label'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   *
   * @phpstan-return array<string, string|\Drupal\Core\StringTranslation\TranslatableMarkup|mixed>
   */
  public function buildRow(EntityInterface $entity): array {
    $row['label'] = $entity->toLink((string) $entity->label(), 'canonical');
    return $row + parent::buildRow($entity);
  }

}
