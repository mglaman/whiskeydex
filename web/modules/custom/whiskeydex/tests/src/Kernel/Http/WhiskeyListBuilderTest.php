<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Http;

use Drupal\user\Entity\User;
use Drupal\whiskeydex\Entity\Distillery;
use Drupal\whiskeydex\Entity\Whiskey;
use Symfony\Component\HttpFoundation\Request;

final class WhiskeyListBuilderTest extends WhiskeyDexHttpTestBase {

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('distillery');
    $this->installEntitySchema('whiskey');
    Distillery::create([
      'name' => 'Woodford Reserve',
    ])->save();
    Whiskey::create([
      'name' => 'Woodford Reserve Straight Bourbon Whiskey',
      'distillery' => '1',
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

    $request = Request::create('/admin/whiskey');
    $response = $this->doRequest($request);
    self::assertEquals($expected_status, $response->getStatusCode());
    if ($expected_status === 200) {
      $this->assertNoText('There are no whiskey entities yet.');
      $this->assertLink('Woodford Reserve Straight Bourbon Whiskey');
    }
  }

  /**
   * @phpstan-ignore-next-line
   */
  public function providesData(): iterable {
    yield [
      'access whiskey overview,view whiskey',
      200,
    ];
    yield [
      'administer whiskey',
      200,
    ];
    yield [
      'view whiskey',
      403,
    ];
    yield [
      '',
      403,
    ];
  }

}
