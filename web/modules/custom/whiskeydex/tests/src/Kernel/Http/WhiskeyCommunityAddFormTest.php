<?php

declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel;

use Drupal\Core\Url;
use Drupal\Tests\whiskeydex\Kernel\Http\WhiskeyDexHttpTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\whiskeydex\Form\WhiskeyCommunityAddForm
 */
final class WhiskeyCommunityAddFormTest extends WhiskeyDexHttpTestBase {

  /**
   * @testWith [""]
   *           ["Woodford Reserve Rye Whiskey"]
   *           [" leading empty space"]
   *           ["trailing empty space  "]
   */
  public function testPrefillOfName(string $name): void {
    $test_account = $this->createUser([], ['view whiskey']);
    $this->container->get('current_user')->setAccount($test_account);

    $query = [];
    if ($name !== '') {
      $query['query'] = [
        'name' => $name,
      ];
    }
    $url = Url::fromRoute(
      'entity.whiskey.community_add_form',
      [],
      $query,
    )->toString();
    $request = Request::create($url);
    $response = $this->doRequest($request);
    self::assertEquals(200, $response->getStatusCode());
    $this->setRawContent((string) $response->getContent());
    $whiskey_element = $this->cssSelect('[name="name[0][value]"]');
    self::assertCount(1, $whiskey_element);
    self::assertEquals(
      trim($name),
      // @phpstan-ignore-next-line
      $whiskey_element[0]->attributes()->value[0]
    );
  }

}
