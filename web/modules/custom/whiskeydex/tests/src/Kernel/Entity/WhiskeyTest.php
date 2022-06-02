<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Entity\Kernel;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\whiskeydex\Kernel\WhiskeyDexTestBase;
use Drupal\whiskeydex\Entity\Distillery;
use Drupal\whiskeydex\Entity\Whiskey;

final class WhiskeyTest extends WhiskeyDexTestBase {

  public function testDefinition(): void {
    $etm = $this->container->get('entity_type.manager');
    assert($etm instanceof EntityTypeManagerInterface);
    self::assertTrue($etm->hasDefinition('whiskey'));
    $entity_type = $etm->getDefinition('whiskey');
    self::assertInstanceOf(EntityTypeInterface::class, $entity_type);
    self::assertEquals('Drupal\entity\EntityAccessControlHandler', $entity_type->getHandlerClass('access'));
    self::assertEquals('Drupal\entity\QueryAccess\QueryAccessHandler', $entity_type->getHandlerClass('query_access'));
    self::assertEquals('Drupal\entity\EntityPermissionProvider', $entity_type->getHandlerClass('permission_provider'));
  }

  public function testFields(): void {
    $efm = $this->container->get('entity_field.manager');
    assert($efm instanceof EntityFieldManagerInterface);
    $base_fields = $efm->getBaseFieldDefinitions('whiskey');
    self::assertEquals([
      'whiskey_id',
      'uuid',
      'name',
      'distillery',
    ], array_keys($base_fields));
  }

  /**
   * @dataProvider entityValues
   *
   * @phpstan-param array<string, mixed> $values
   */
  public function testEntity(array $values, string $expected_name): void {
    $this->installEntitySchema('distillery');
    Distillery::create([
      'name' => 'Woodford Reserve',
    ])->save();

    $etm = $this->container->get('entity_type.manager');
    assert($etm instanceof EntityTypeManagerInterface);
    $distillery = $etm->getStorage('whiskey')->create($values);
    assert($distillery instanceof Whiskey);
    self::assertEquals($distillery->label(), $expected_name);
    self::assertEquals($distillery->getDistillery()->label(), 'Woodford Reserve');
  }

  public function entityValues(): \Generator {
    yield [
      [
        'name' => 'Woodford Reserve Straight Bourbon Whiskey',
        'distillery' => '1',
      ],
      'Woodford Reserve Straight Bourbon Whiskey',
    ];
  }

}
