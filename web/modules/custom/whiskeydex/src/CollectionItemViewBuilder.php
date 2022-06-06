<?php declare(strict_types=1);

namespace Drupal\whiskeydex;

use Drupal\Core\Entity\EntityViewBuilder;

final class CollectionItemViewBuilder extends EntityViewBuilder {

  /**
   * @phpstan-param array<int|string, array<string, mixed>> $build
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode): void {
    parent::buildComponents($build, $entities, $displays, $view_mode);
    foreach ($entities as $id => $entity) {
      $display = $displays[$entity->bundle()];
      if ($display->getComponent('links')) {
        $build[$id]['links'] = [
          '#type' => 'operations',
          '#links' => [
            'edit' => [
              'title' => $this->t('Edit'),
              'url' => $entity->toUrl('edit-form'),
            ],
            'delete' => [
              'title' => $this->t('Delete'),
              'url' => $entity->toUrl('delete-form'),
            ],
          ],
        ];
      }
    }
  }

}
