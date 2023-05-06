<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Field\Plugin\Field\FieldType\IntegerItem;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * @\Drupal\whiskeydex\Annotation\Model(
 *   id = "collection_item",
 *   owner_entity_access = true,
 *   provide_tasks = false,
 *   links = {
 *     "collection" = "/collection",
 *     "canonical" = "/collection/{collection_item}",
 *     "add-form" = "/collection/add/{whiskey}",
 *     "edit-form" = "/collection/{collection_item}/edit",
 *     "delete-form" = "/collection/{collection_item}/delete",
 *   }
 * )
 */
final class CollectionItem extends ContentEntityBase implements EntityOwnerInterface {
  use EntityOwnerTrait;

  public function getWhiskey(): Whiskey {
    $item = $this->get('whiskey')->first();
    assert($item instanceof EntityReferenceItem);
    $whiskey = $item->get('entity')->getValue();
    assert($whiskey instanceof Whiskey);
    return $whiskey;
  }

  public function getYear(): ?int {
    if ($this->get('year')->isEmpty()) {
      return NULL;
    }
    $item = $this->get('year')->first();
    assert($item instanceof IntegerItem);
    // @phpstan-ignore-next-line
    return (int) $item->get('value')->getValue();
  }

  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    $year = $this->getYear();
    $whiskey_label = $this->getWhiskey()->label();
    if ($year === NULL) {
      $this->set('name', $whiskey_label);
    }
    else {
      $this->set('name', "$whiskey_label ($year)");
    }
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += self::ownerBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel('Name')
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'type' => 'string',
        'label' => 'hidden',
        'settings' => [
          'link_to_entity' => TRUE,
        ],
      ]);
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel('Created')
      ->setDescription('The timestamp that the collection item was created.');
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
      ])
      ->setDisplayOptions('view', [
        'type' => 'entity_reference_entity_view',
        'label' => 'hidden',
        'settings' => [
          'view_mode' => 'collection',
        ],
      ]);
    $fields['year'] = BaseFieldDefinition::create('integer')
      ->setLabel('Year')
      ->setRequired(FALSE)
      ->setSettings([
        'size' => 'small',
        'min' => 0,
        'max' => 9999,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number_year',
      ])
      ->setDisplayOptions('view', [
        'type' => 'number_integer',
        'label' => 'inline',
      ]);
    $fields['proof'] = BaseFieldDefinition::create('decimal')
      ->setLabel('Proof')
      ->setRequired(FALSE)
      ->setSettings([
        'precision' => 10,
        'scale' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'number',
      ])
      ->setDisplayOptions('view', [
        'type' => 'number_decimal',
        'label' => 'inline',
      ]);
    $fields['batch'] = BaseFieldDefinition::create('string')
      ->setLabel('Batch')
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
      ])
      ->setDisplayOptions('view', [
        'type' => 'string',
        'label' => 'inline',
      ]);
    $fields['nose'] = BaseFieldDefinition::create('string_long')
      ->setLabel('Nose')
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
      ])
      ->setDisplayOptions('view', [
        'type' => 'basic_string',
      ]);
    $fields['taste'] = BaseFieldDefinition::create('string_long')
      ->setLabel('Taste')
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
      ])
      ->setDisplayOptions('view', [
        'type' => 'basic_string',
      ]);
    $fields['finish'] = BaseFieldDefinition::create('string_long')
      ->setLabel('Finish')
      ->setDisplayOptions('form', [
        'type' => 'string_textarea',
      ])
      ->setDisplayOptions('view', [
        'type' => 'basic_string',
      ]);
    // nose, taste, finish: plain text or tags of flavors.
    return $fields;
  }

}
