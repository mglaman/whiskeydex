<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Entity;

use Drupal\entity\QueryAccess\UncacheableQueryAccessHandler;
use Drupal\entity\UncacheableEntityAccessControlHandler;
use Drupal\entity\UncacheableEntityPermissionProvider;
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

}
