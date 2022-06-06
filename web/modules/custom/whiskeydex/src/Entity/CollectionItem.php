<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * @\Drupal\whiskeydex\Annotation\Model(
 *   id = "collection_item",
 *   owner_entity_access = true,
 *   links = {
 *     "canonical" = "/collections/{collection}/{collection_item}",
 *     "add-form" = "/collections/{collection}/add",
 *     "edit-form" = "/collections/{collection}/{collection_item}/edit",
 *     "delete-form" = "/collections/{collection}/{collection_item}/delete",
 *   }
 * )
 */
final class CollectionItem extends ContentEntityBase implements EntityOwnerInterface {
  use EntityOwnerTrait;

  /**
   * @phpstan-param string $rel
   * @phpstan-return array<string, string|int>
   */
  protected function urlRouteParameters($rel): array {
    $parameters = parent::urlRouteParameters($rel);
    $parameters['collection'] = $this->getCollectionId();
    return $parameters;
  }

  public function getCollectionId(): int {
    $collection = $this->get('collection')->first();
    assert($collection instanceof EntityReferenceItem);
    $collection_id = $collection->get('target_id')->getValue();
    assert(is_string($collection_id));
    return (int) $collection_id;
  }

  public function getCollection(): Collection {
    $item = $this->get('collection')->first();
    assert($item instanceof EntityReferenceItem);
    $collection = $item->get('entity')->getValue();
    assert($collection instanceof Collection);
    return $collection;
  }

  public function getWhiskey(): Whiskey {
    $item = $this->get('whiskey')->first();
    assert($item instanceof EntityReferenceItem);
    $whiskey = $item->get('entity')->getValue();
    assert($whiskey instanceof Whiskey);
    return $whiskey;
  }

  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    $this->set('name', $this->getWhiskey()->label());
  }

  public function postSave(EntityStorageInterface $storage, $update = TRUE): void {
    parent::postSave($storage, $update);
    $this->getCollection()
      ->addItem($this)
      ->save();
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += self::ownerBaseFieldDefinitions($entity_type);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel('Name')
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
      ])
      ->setDisplayOptions('view', [
        'type' => 'entity_reference_entity_view',
        'label' => 'hidden',
        'settings' => [
          'view_mode' => 'collection',
        ],
      ]);
    $fields['collection'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Collection')
      ->setRequired(TRUE)
      ->setSetting('target_type', 'collection');
    return $fields;
  }

}
