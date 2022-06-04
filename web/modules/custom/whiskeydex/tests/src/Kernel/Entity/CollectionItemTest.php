<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Entity;

use Drupal\entity\QueryAccess\UncacheableQueryAccessHandler;
use Drupal\entity\UncacheableEntityAccessControlHandler;
use Drupal\entity\UncacheableEntityPermissionProvider;
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

}
