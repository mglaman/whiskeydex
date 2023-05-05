<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Annotation;

use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\whiskeydex\Entity\ModelEntityType;

/**
 * @Annotation
 *
 * @phpcs:disable Drupal.NamingConventions.ValidVariableName.LowerCamelName
 */
final class Model extends ContentEntityType {

  /**
   * @phpstan-var class-string
   */
  public $entity_type_class = ModelEntityType::class;

  public bool $enhanced_entity_access = TRUE;

  public bool $owner_entity_access = FALSE;

  public bool $has_ui = TRUE;

  public bool $admin_ui_routes = TRUE;

  public bool $provide_tasks = TRUE;

  /**
   * @phpstan-var array<string, string>
   */
  public array $links = [];

}
