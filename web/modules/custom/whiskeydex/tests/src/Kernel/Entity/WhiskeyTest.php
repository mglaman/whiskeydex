<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Entity;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\entity\EntityAccessControlHandler;
use Drupal\entity\EntityPermissionProvider;
use Drupal\entity\QueryAccess\QueryAccessHandler;
use Drupal\whiskeydex\Entity\Distillery;
use Drupal\whiskeydex\Entity\Whiskey;

final class WhiskeyTest extends WhiskeyDexEntityTestBase {

  protected ?string $entityTypeId = 'whiskey';

  protected ?array $handlers = [
    'access' => EntityAccessControlHandler::class,
    'query_access' => QueryAccessHandler::class,
    'permission_provider' => EntityPermissionProvider::class,
  ];

  protected ?array $baseFieldNames = [
    'whiskey_id',
    'uuid',
    'name',
    'distillery',
    'community',
  ];

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
    $whiskey = $etm->getStorage('whiskey')->create($values);
    assert($whiskey instanceof Whiskey);
    self::assertEquals($expected_name, $whiskey->label());
    self::assertEquals('Woodford Reserve', $whiskey->getDistillery()->label());
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
