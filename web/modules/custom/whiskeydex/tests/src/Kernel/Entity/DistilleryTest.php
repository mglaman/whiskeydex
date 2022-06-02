<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Entity\Kernel;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\whiskeydex\Kernel\WhiskeyDexTestBase;
use Drupal\whiskeydex\Entity\Distillery;
use Drupal\entity\EntityAccessControlHandler;
use Drupal\entity\QueryAccess\QueryAccessHandler;
use Drupal\entity\EntityPermissionProvider;

final class DistilleryTest extends WhiskeyDexTestBase {

  public function testDefinition(): void {
    $etm = $this->container->get('entity_type.manager');
    assert($etm instanceof EntityTypeManagerInterface);
    self::assertTrue($etm->hasDefinition('distillery'));
    $entity_type = $etm->getDefinition('distillery');
    self::assertInstanceOf(EntityTypeInterface::class, $entity_type);
    self::assertEquals(EntityAccessControlHandler::class, $entity_type->getHandlerClass('access'));
    self::assertEquals(QueryAccessHandler::class, $entity_type->getHandlerClass('query_access'));
    self::assertEquals(EntityPermissionProvider::class, $entity_type->getHandlerClass('permission_provider'));
    self::assertEquals([
      'collection' => '/admin/distillery',
      'canonical' => '/distillery/{distillery}',
      'add-form' => '/distillery/add',
      'edit-form' => '/distillery/{distillery}/edit',
      'delete-form' => '/distillery/{distillery}/delete',
    ], $entity_type->getLinkTemplates());
  }

  public function testFields(): void {
    $efm = $this->container->get('entity_field.manager');
    assert($efm instanceof EntityFieldManagerInterface);
    $base_fields = $efm->getBaseFieldDefinitions('distillery');
    self::assertEquals([
      'distillery_id',
      'uuid',
      'name',
      'email',
      'phone',
      'website',
      'address',
      'verified',
    ], array_keys($base_fields));
  }

  /**
   * @dataProvider entityValues
   *
   * @phpstan-param array<string, mixed> $values
   */
  public function testEntity(array $values, string $expected_name, ?string $expected_email): void {
    $etm = $this->container->get('entity_type.manager');
    assert($etm instanceof EntityTypeManagerInterface);
    $distillery = $etm->getStorage('distillery')->create($values);
    assert($distillery instanceof Distillery);
    self::assertEquals($distillery->label(), $expected_name);
    self::assertEquals($distillery->getEmail(), $expected_email);
  }

  public function entityValues(): \Generator {
    yield [
      [
        'name' => 'Woodford Reserve',
        'email' => 'contact@woodfordreserve.com',
        'phone' => '859.879.1812',
        'website' => 'www.woodfordreserve.com',
        'address' => [
          'country_code' => 'US',
          'locality' => 'Versailles',
          'administrative_area' => 'KY',
          'postal_code' => '40383',
          'address_line1' => '7855 McCracken Pike',
        ],
        'verified' => TRUE,
      ],
      'Woodford Reserve',
      'contact@woodfordreserve.com',
    ];
    yield [
      [
        'name' => 'Woodford Reserve',
        'phone' => '859.879.1812',
        'website' => 'www.woodfordreserve.com',
        'address' => [
          'country_code' => 'US',
          'locality' => 'Versailles',
          'administrative_area' => 'KY',
          'postal_code' => '40383',
          'address_line1' => '7855 McCracken Pike',
        ],
        'verified' => TRUE,
      ],
      'Woodford Reserve',
      NULL,
    ];
  }

}
