<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Http;

use Drupal\Tests\whiskeydex\Kernel\WhiskeyDexTestBase;
use Drupal\user\Entity\User;
use Drupal\whiskeydex\Entity\Distillery;
use Symfony\Component\HttpFoundation\Request;

final class DistilleryListBuilderTest extends WhiskeyDexTestBase {

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
    $this->createUser();
    if ($permission === '') {
      $test_account = User::getAnonymousUser();
    }
    else {
      $test_account = $this->createUser([], explode(',', $permission));
    }
    $this->container->get('current_user')->setAccount($test_account);

    $request = Request::create('/admin/distillery');
    $response = $this->container->get('http_kernel')->handle($request);
    self::assertEquals($expected_status, $response->getStatusCode());
    if ($expected_status === 200) {
      $this->setRawContent($response->getContent());
      $this->assertNoText('There are no distillery entities yet.');
      $this->assertLink('Woodford Reserve');
    }
  }

  public function providesData() {
    yield [
      'access distillery overview,view distillery',
      200
    ];
    yield [
      'administer distillery',
      200
    ];
    yield [
      'view distillery',
      403
    ];
    yield [
      '',
      403
    ];
  }

}
