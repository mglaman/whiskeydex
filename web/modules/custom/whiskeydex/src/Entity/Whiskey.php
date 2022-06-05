<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;

/**
 * @\Drupal\whiskeydex\Annotation\Model(
 *   id = "whiskey",
 * )
 */
final class Whiskey extends ContentEntityBase {

  public function getDistillery(): Distillery {
    $item = $this->get('distillery')->first();
    assert($item instanceof EntityReferenceItem);
    $distillery = $item->get('entity')->getValue();
    assert($distillery instanceof Distillery);
    return $distillery;
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
      ->setRequired(TRUE)
      ->setDisplayConfigurable('view', TRUE);
    $fields['distillery'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Distillery')
      ->setRequired(TRUE)
      ->setSetting('target_type', 'distillery')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => -1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);
    return $fields;
  }

}
