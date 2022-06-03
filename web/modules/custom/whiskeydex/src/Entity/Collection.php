<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * @\Drupal\whiskeydex\Annotation\Model(
 *   id = "collection",
 *   owner_entity_access = true,
 *   links = {
 *     "canonical" = "/user/{user}/collection/{collection}",
 *     "add-form" = "/user/{user}/collection/add",
 *     "edit-form" = "/user/{user}/collection/{collection}/edit",
 *     "delete-form" = "/user/{user}/collection/{collection}/delete",
 *   }
 * )
 */
final class Collection extends ContentEntityBase implements EntityOwnerInterface {
  use EntityOwnerTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += self::ownerBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel('Name')
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setRequired(TRUE);

    return $fields;
  }
}
