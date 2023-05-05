<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Entity;

use Drupal\entity\QueryAccess\UncacheableQueryAccessHandler;
use Drupal\entity\UncacheableEntityAccessControlHandler;
use Drupal\entity\UncacheableEntityPermissionProvider;
use Drupal\whiskeydex\Entity\Whiskey;
use Drupal\whiskeydex\Routing\CollectionItemHtmlRouteProvider;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

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
    'created',
    'whiskey',
    'year',
    'proof',
    'batch',
    'nose',
    'taste',
    'finish',
  ];

  protected ?array $routeProviders = [
    'html' => CollectionItemHtmlRouteProvider::class,
  ];

  protected function expectedLinkTemplates(): array {
    return [
      'collection' => '/collection',
      'canonical' => '/collection/{collection_item}',
      'add-form' => '/collection/add/{whiskey}',
      'edit-form' => '/collection/{collection_item}/edit',
      'delete-form' => '/collection/{collection_item}/delete',
    ];
  }

  public function testEntity(): void {
    $this->installEntitySchema('collection_item');
    $this->installEntitySchema('whiskey');

    $etm = $this->container->get('entity_type.manager');
    $user = $this->createUser();

    $whiskey = Whiskey::create(['name' => 'foo']);
    $whiskey->save();

    $collection_item = $etm->getStorage('collection_item')->create([
      'whiskey' => $whiskey->id(),
      'uid' => $user->id(),
    ]);
    $collection_item->save();
    self::assertEquals($whiskey->label(), $collection_item->label());
    self::assertEquals($whiskey->id(), $collection_item->getWhiskey()->id());

    self::assertEquals(
      '/collection/' . $collection_item->id(),
      $collection_item->toUrl('canonical')->toString()
    );

    $this->expectException(MissingMandatoryParametersException::class);
    $this->expectExceptionMessage('Some mandatory parameters are missing ("whiskey") to generate a URL for route "entity.collection_item.add_form".');
    self::assertEquals(
      '/collection/add',
      $collection_item->toUrl('add-form')->toString()
    );

  }

}
