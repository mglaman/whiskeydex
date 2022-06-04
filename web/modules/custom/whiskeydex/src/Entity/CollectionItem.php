<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * @\Drupal\whiskeydex\Annotation\Model(
 *   id = "collection_item",
 *   owner_entity_access = true,
 * )
 */
final class CollectionItem extends ContentEntityBase implements EntityOwnerInterface {
  use EntityOwnerTrait;

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += self::ownerBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel('Name')
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setRequired(TRUE);
    $fields['whiskey'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Whiskey')
      ->setRequired(TRUE)
      ->setSetting('target_type', 'whiskey')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ]);
    $fields['collection'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Collection')
      ->setRequired(TRUE)
      ->setSetting('target_type', 'collection')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ]);
    return $fields;
  }

}
