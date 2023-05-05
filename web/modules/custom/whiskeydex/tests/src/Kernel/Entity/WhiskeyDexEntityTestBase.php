<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\entity\Routing\AdminHtmlRouteProvider;
use Drupal\Tests\whiskeydex\Kernel\WhiskeyDexTestBase;

abstract class WhiskeyDexEntityTestBase extends WhiskeyDexTestBase {

  protected ?string $entityTypeId;

  /**
   * @phpstan-var array<string, class-string>|null
   */
  protected ?array $handlers = [];

  /**
   * @var string[]
   */
  protected ?array $baseFieldNames = [];

  /**
   * @phpstan-var array<string, class-string>
   */
  protected ?array $routeProviders = [
    'html' => AdminHtmlRouteProvider::class,
  ];

  public function testDefinition(): void {
    $etm = $this->container->get('entity_type.manager');
    self::assertNotNull($this->entityTypeId);
    self::assertTrue($etm->hasDefinition($this->entityTypeId));
    $entity_type = $etm->getDefinition($this->entityTypeId);
    self::assertInstanceOf(EntityTypeInterface::class, $entity_type);
    self::assertEquals($this->handlers['access'] ?? NULL, $entity_type->getHandlerClass('access'));
    self::assertEquals($this->handlers['query_access'] ?? NULL, $entity_type->getHandlerClass('query_access'));
    self::assertEquals($this->handlers['permission_provider'] ?? NULL, $entity_type->getHandlerClass('permission_provider'));
    self::assertEquals($this->routeProviders, $entity_type->getRouteProviderClasses());
    self::assertEquals($this->expectedLinkTemplates(), $entity_type->getLinkTemplates());
  }

  public function testFields(): void {
    $efm = $this->container->get('entity_field.manager');
    self::assertNotNull($this->entityTypeId);
    $base_fields = $efm->getBaseFieldDefinitions($this->entityTypeId);
    self::assertEquals($this->baseFieldNames, array_keys($base_fields));
  }

  /**
   * @phpstan-return array<string, string>
   */
  protected function expectedLinkTemplates(): array {
    return [
      'collection' => '/admin/' . $this->entityTypeId,
      'canonical' => '/' . $this->entityTypeId . '/{' . $this->entityTypeId . '}',
      'add-form' => '/' . $this->entityTypeId . '/add',
      'edit-form' => '/' . $this->entityTypeId . '/{' . $this->entityTypeId . '}/edit',
      'delete-form' => '/' . $this->entityTypeId . '/{' . $this->entityTypeId . '}/delete',
    ];
  }

}
