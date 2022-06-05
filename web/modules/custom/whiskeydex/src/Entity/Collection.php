<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

use function array_map;
use function in_array;
use function iterator_to_array;

/**
 * @\Drupal\whiskeydex\Annotation\Model(
 *   id = "collection",
 *   owner_entity_access = true,
 *   provide_tasks = false,
 *   handlers = {
 *     "local_action_provider" = {
 *       "canonical" = "\Drupal\whiskeydex\Menu\CollectionCanonicalLocalActionProvider"
 *     }
 *   },
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

  public function addItem(CollectionItem $item): self {
    $item_ids = array_map(
      // @phpstan-ignore-next-line
      fn (EntityReferenceItem $item) => (int) $item->get('target_id')->getValue(),
      iterator_to_array($this->get('items'))
    );
    if (!in_array((int) $item->id(), $item_ids, TRUE)) {
      $this->get('items')->appendItem($item);
    }
    return $this;
  }

  public function getItemsCount(): int {
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
      ->setRequired(TRUE)
      ->setDisplayConfigurable('view', TRUE);
    // @todo needs a custom widget for extra meta about the whiskey
    // (specific year, proof variants, etc.) IEF isn't 10.0.x ready.
    $fields['items'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Items')
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setSetting('target_type', 'collection_item')
      ->setDisplayOptions('view', [
        'type' => 'entity_reference_entity_view',
        'label' => 'hidden',
        'settings' => [
          'view_mode' => 'card',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);
    return $fields;
  }

}
