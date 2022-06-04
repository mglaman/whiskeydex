<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Entity;

use CommerceGuys\Addressing\AddressFormat\AddressField;
use CommerceGuys\Addressing\AddressFormat\FieldOverride;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\Plugin\Field\FieldType\EmailItem;

/**
 * @\Drupal\whiskeydex\Annotation\Model(
 *   id = "distillery",
 * )
 *
 * @property \Drupal\Core\Field\FieldItemListInterface $email
 * @property \Drupal\Core\Field\FieldItemListInterface $phone
 * @property \Drupal\Core\Field\FieldItemListInterface $website
 * @property \Drupal\Core\Field\FieldItemListInterface $verified
 * @property \Drupal\address\Plugin\Field\FieldType\AddressFieldItemList $address
 */
final class Distillery extends ContentEntityBase {

  public function getEmail(): ?string {
    if ($this->get('email')->isEmpty()) {
      return NULL;
    }
    $item = $this->get('email')->first();
    assert($item instanceof EmailItem);
    $value = $item->get('value')->getValue();
    assert(is_string($value));
    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel('Name')
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setRequired(TRUE);
    $fields['email'] = BaseFieldDefinition::create('email')
      ->setLabel('Email');
    $fields['phone'] = BaseFieldDefinition::create('telephone')
      ->setLabel('Phone');
    $fields['website'] = BaseFieldDefinition::create('uri')
      ->setLabel('Website');
    $fields['address'] = BaseFieldDefinition::create('address')
      ->setLabel('Address')
      ->setRequired(TRUE)
      ->setSetting('field_overrides', [
        AddressField::GIVEN_NAME => ['override' => FieldOverride::HIDDEN],
        AddressField::FAMILY_NAME => ['override' => FieldOverride::HIDDEN],
        AddressField::ADDITIONAL_NAME => ['override' => FieldOverride::HIDDEN],
        AddressField::ORGANIZATION => ['override' => FieldOverride::HIDDEN],
        AddressField::ADDRESS_LINE1 => ['override' => FieldOverride::OPTIONAL],
        AddressField::ADDRESS_LINE2 => ['override' => FieldOverride::OPTIONAL],
      ])
      ->setDisplayOptions('form', [
        'type' => 'address_default',
        'weight' => 4,
      ]);

    // @todo needs custom field access.
    $fields['verified'] = BaseFieldDefinition::create('boolean')
      ->setLabel('Verified')
      ->setDescription('A boolean indicating whether this distillery is verified.');

    return $fields;
  }

}
