<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Http;

use Drupal\user\Entity\User;
use Drupal\whiskeydex\Entity\Distillery;
use Symfony\Component\HttpFoundation\Request;

final class DistilleryListBuilderTest extends WhiskeyDexHttpTestBase {

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('distillery');
    Distillery::create([
      'name' => 'Woodford Reserve',
    ])->save();
  }

  /**
   * @dataProvider providesData
   */
  public function testList(string $permission, int $expected_status): void {
    if ($permission === '') {
      $test_account = User::getAnonymousUser();
    }
    else {
      $test_account = $this->createUser([], explode(',', $permission));
    }
    $this->container->get('current_user')->setAccount($test_account);

    $request = Request::create('/admin/distillery');
    $response = $this->doRequest($request);
    self::assertEquals($expected_status, $response->getStatusCode());
    if ($expected_status === 200) {
      $this->assertNoText('There are no distillery entities yet.');
      $this->assertLink('Woodford Reserve');
    }
  }

  /**
   * @phpstan-ignore-next-line
   */
  public function providesData(): iterable {
    yield [
      'access distillery overview,view distillery',
      200,
    ];
    yield [
      'administer distillery',
      200,
    ];
    yield [
      'view distillery',
      403,
    ];
    yield [
      '',
      403,
    ];
  }

}
