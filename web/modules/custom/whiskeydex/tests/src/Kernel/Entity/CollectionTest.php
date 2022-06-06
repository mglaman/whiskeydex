<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Entity;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entity\QueryAccess\UncacheableQueryAccessHandler;
use Drupal\entity\UncacheableEntityAccessControlHandler;
use Drupal\entity\UncacheableEntityPermissionProvider;
use Drupal\whiskeydex\Entity\Collection;
use Drupal\whiskeydex\Entity\CollectionItem;
use Drupal\whiskeydex\Entity\Whiskey;
use Drupal\whiskeydex\Routing\CollectionHtmlRouteProvider;

final class CollectionTest extends WhiskeyDexEntityTestBase {

  protected ?string $entityTypeId = 'collection';

  protected ?array $handlers = [
    'access' => UncacheableEntityAccessControlHandler::class,
    'query_access' => UncacheableQueryAccessHandler::class,
    'permission_provider' => UncacheableEntityPermissionProvider::class,
  ];

  protected ?array $baseFieldNames = [
    'collection_id',
    'uuid',
    'uid',
    'name',
    'items',
  ];

  /**
   * {@inheritDoc}
   */
  protected function expectedLinkTemplates(): array {
    return [
      'collection' => '/collections',
      'canonical' => '/collections/{collection}',
      'add-form' => '/collections/add',
      'edit-form' => '/collections/{collection}/edit',
      'delete-form' => '/collections/{collection}/delete',
    ];
  }

  protected ?array $routeProviders = [
    'html' => CollectionHtmlRouteProvider::class,
  ];

  public function testEntity(): void {
    $this->installEntitySchema('collection');
    $this->installEntitySchema('collection_item');

    $etm = $this->container->get('entity_type.manager');
    assert($etm instanceof EntityTypeManagerInterface);
    $user = $this->createUser();
    $collection = $etm->getStorage('collection')->create([
      'name' => 'main',
      'uid' => $user->id(),
    ]);
    assert($collection instanceof Collection);
    self::assertEquals('main', $collection->label());
    self::assertEquals($user->getDisplayName(), $collection->getOwner()->getDisplayName());

    $collection_item = $etm->getStorage('collection_item')->create([
      'name' => 'foo',
      'whiskey' => Whiskey::create(['name' => 'foo']),
    ]);
    assert($collection_item instanceof CollectionItem);
    self::assertEquals(0, $collection->getItemsCount());
    $collection->addItem($collection_item);
    self::assertEquals(1, $collection->getItemsCount());
    self::assertEquals([$collection_item], $collection->getItems());
  }

  public function testLocalTaskProvider(): void {
    $user = $this->createUser([], [
      'view own collection',
      'create collection',
      'update own collection',
      'view own collection_item',
      'create collection_item',
      'update own collection_item',
    ]);
    $this->container->get('current_user')->setAccount($user);
    $manager = $this->container->get('plugin.manager.menu.local_task');
    $tasks = $manager->getLocalTasksForRoute('entity.collection.canonical');
    self::assertCount(3, $tasks[0]);
    self::assertEquals([
      'entity.entity_tasks:entity.collection.canonical',
      'entity.entity_tasks:entity.collection.edit_form',
      'entity.entity_tasks:entity.collection_item.add_form',
    ], array_keys($tasks[0]));
  }

}
