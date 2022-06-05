<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Entity;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entity\QueryAccess\UncacheableQueryAccessHandler;
use Drupal\entity\UncacheableEntityAccessControlHandler;
use Drupal\entity\UncacheableEntityPermissionProvider;
use Drupal\whiskeydex\Entity\Collection;
use Drupal\whiskeydex\Entity\CollectionItem;
use Drupal\whiskeydex\Entity\Whiskey;
use Drupal\whiskeydex\Routing\CollectionItemHtmlRouteProvider;

final class CollectionItemTest extends WhiskeyDexEntityTestBase {

  protected ?string $entityTypeId = 'collection_item';

  protected ?array $handlers = [
    'access' => UncacheableEntityAccessControlHandler::class,
    'query_access' => UncacheableQueryAccessHandler::class,
    'permission_provider' => UncacheableEntityPermissionProvider::class,
  ];

  protected ?array $baseFieldNames = [
    'collection_item_id',
    'uuid',
    'uid',
    'name',
    'whiskey',
    'collection',
  ];

  protected ?array $routeProviders = [
    'html' => CollectionItemHtmlRouteProvider::class,
  ];

  protected function expectedLinkTemplates(): array {
    return [
      'canonical' => '/collections/{collection}',
      'add-form' => '/collections/{collection}/add',
      'edit-form' => '/collections/{collection}/{collection_item}/edit',
      'delete-form' => '/collections/{collection}/{collection_item}/delete',
    ];
  }

  public function testEntity(): void {
    $this->installEntitySchema('collection');
    $this->installEntitySchema('collection_item');
    $this->installEntitySchema('whiskey');

    $etm = $this->container->get('entity_type.manager');
    assert($etm instanceof EntityTypeManagerInterface);
    $user = $this->createUser();

    $collection = Collection::create([
      'name' => 'main',
      'uid' => $user->id(),
    ]);
    $collection->save();
    assert($collection instanceof Collection);
    $whiskey = Whiskey::create(['name' => 'foo']);
    $whiskey->save();
    assert($whiskey instanceof Whiskey);

    $collection_item = $etm->getStorage('collection_item')->create([
      'whiskey' => $whiskey->id(),
      'collection' => $collection->id(),
    ]);
    $collection_item->save();
    assert($collection_item instanceof CollectionItem);
    self::assertEquals($whiskey->label(), $collection_item->label());
    self::assertEquals($whiskey->id(), $collection_item->getWhiskey()->id());
    self::assertEquals((int) $collection->id(), $collection_item->getCollectionId());
    self::assertEquals(1, $collection_item->getCollection()->getItemsCount());

    $collection_items = $collection_item->getCollection()->getItems();
    self::assertCount(1, $collection_items);
    self::assertEquals($collection_item->id(), $collection_items[0]->id());

    self::assertEquals(
      '/collections/' . $collection->id() . '?collection_item=' . $collection_item->id(),
      $collection_item->toUrl('canonical')->toString()
    );
    self::assertEquals(
      '/collections/' . $collection->id() . '/add',
      $collection_item->toUrl('add-form')->toString()
    );

  }

}
