<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * @\Drupal\whiskeydex\Annotation\Model(
 *   id = "collection",
 *   owner_entity_access = true,
 *   links = {
 *     "collection" = "/collections",
 *     "canonical" = "/collections/{collection}",
 *     "add-form" = "/collections/add",
 *     "edit-form" = "/collections/{collection}/edit",
 *     "delete-form" = "/collections/{collection}/delete",
 *   }
 * )
 */
final class Collection extends ContentEntityBase implements EntityOwnerInterface {
  use EntityOwnerTrait;

  /**
   * @phpstan-return array<int,\Drupal\whiskeydex\Entity\CollectionItem>
   */
  public function getItems(): array {
    $items = $this->get('items');
    assert($items instanceof EntityReferenceFieldItemListInterface);
    // phpcs:ignore Drupal.Commenting.InlineComment.DocBlock
    /** @phpstan-var array<int,\Drupal\whiskeydex\Entity\CollectionItem> */
    return $items->referencedEntities();
  }

  public function itemsCount(): int {
    return $this->get('items')->count();
  }

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
    $fields['items'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Items')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'collection_item')
      // @todo needs a custom widget for extra meta about the whiskey
      // (specific year, proof variants, etc.) IEF isn't 10.0.x ready.
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
