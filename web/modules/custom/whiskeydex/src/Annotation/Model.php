<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Annotation;

use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\whiskeydex\Entity\ModelEntityType;

/**
 * @Annotation
 */
final class Model extends ContentEntityType {

  public $entity_type_class = ModelEntityType::class;

  public $enhanced_entity_access = true;

  public $owner_entity_access = false;

  public $has_ui = true;

}
