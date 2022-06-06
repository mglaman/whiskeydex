<?php declare(strict_types=1);

use Drupal\Core\Utility\UpdateException;

/**
 * @file
 * There is no difference between hook_update_N and hook_post_update_N.
 *
 * The value of \Drupal::CORE_MINIMUM_SCHEMA_VERSION is 8000, for Drupal 8. The
 * only benefit that hook_update_N provides is guaranteeing order of execution
 * due to numbering. They are sorted numerically by N whereas post_update is
 * sorted alphabetically.
 *
 * @link https://www.drupal.org/project/drupal/issues/3106712
 * @link https://mglaman.dev/blog/hookupdaten-or-hookpostupdatename
 */

/**
 * Install `collection` and `collection_item`.
 */
function whiskeydex_post_update_20220606_install_collection_entity_types(): void {
  $etm = \Drupal::entityTypeManager();
  $edum = \Drupal::entityDefinitionUpdateManager();
  foreach (['collection_item', 'collection'] as $entity_type_id) {
    $entity_type = $etm->getDefinition($entity_type_id);
    if ($entity_type === NULL) {
      throw new UpdateException("Cannot get definition for '$entity_type_id' entity type.");
    }
    $edum->installEntityType($entity_type);
  }
}
