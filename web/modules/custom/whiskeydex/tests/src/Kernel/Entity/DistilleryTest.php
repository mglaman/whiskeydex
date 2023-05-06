<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Entity;

use Drupal\entity\EntityAccessControlHandler;
use Drupal\entity\QueryAccess\QueryAccessHandler;
use Drupal\entity\EntityPermissionProvider;

final class DistilleryTest extends WhiskeyDexEntityTestBase {

  protected ?string $entityTypeId = 'distillery';

  protected ?array $handlers = [
    'access' => EntityAccessControlHandler::class,
    'query_access' => QueryAccessHandler::class,
    'permission_provider' => EntityPermissionProvider::class,
  ];

  protected ?array $baseFieldNames = [
    'distillery_id',
    'uuid',
    'name',
    'email',
    'phone',
    'website',
    'address',
    'verified',
  ];

  /**
   * @dataProvider entityValues
   *
   * @phpstan-param array<string, mixed> $values
   */
  public function testEntity(array $values, string $expected_name, ?string $expected_email): void {
    $etm = $this->container->get('entity_type.manager');
    $distillery = $etm->getStorage('distillery')->create($values);
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
