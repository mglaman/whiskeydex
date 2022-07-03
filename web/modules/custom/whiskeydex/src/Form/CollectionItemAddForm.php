<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Form;

use Drupal\whiskeydex\Entity\CollectionItem;

final class CollectionItemAddForm extends ModelContentEntityForm {

  protected function prepareEntity(): void {
    parent::prepareEntity();
    assert($this->entity instanceof CollectionItem);
    $whiskey = $this->getRouteMatch()->getRawParameter('whiskey');
    $this->entity->set('whiskey', $whiskey);
  }

}
