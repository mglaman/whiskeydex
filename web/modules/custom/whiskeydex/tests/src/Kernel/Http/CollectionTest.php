<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Http;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Url;
use Drupal\whiskeydex\Entity\CollectionItem;
use Drupal\whiskeydex\Entity\Whiskey;
use Symfony\Component\HttpFoundation\Request;

final class CollectionTest extends WhiskeyDexHttpTestBase {

  public function register(ContainerBuilder $container): void {
    parent::register($container);
    $container->setParameter('http.response.debug_cacheability_headers', TRUE);
  }

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('collection_item');
    $this->installEntitySchema('whiskey');
  }

  public function testAll(): void {
    $whiskey1 = Whiskey::create(['name' => 'user1 whiskey']);
    $whiskey1->save();
    $whiskey2 = Whiskey::create(['name' => 'user2 whiskey']);
    $whiskey2->save();

    $user1 = $this->createUser([], ['view own collection_item', 'view whiskey']);
    CollectionItem::create([
      'uid' => $user1->id(),
      'whiskey' => $whiskey1->id(),
    ])->save();

    $user2 = $this->createUser([], ['view own collection_item', 'view whiskey']);
    CollectionItem::create([
      'uid' => $user2->id(),
      'whiskey' => $whiskey2->id(),
    ])->save();

    $this->container->get('current_user')->setAccount($user1);
    $request = Request::create('/collection');
    $response = $this->doRequest($request);
    self::assertEquals(200, $response->getStatusCode());
    $this->assertStringContainsString('user1 whiskey', $this->getTextContent());
    $this->assertStringNotContainsString('user2 whiskey', $this->getTextContent());
    self::assertEquals(
      'collection_item:1 collection_item_view http_response rendered whiskey:1 whiskey_view',
      $response->headers->get('x-drupal-cache-tags')
    );
    self::assertEquals(
      'languages:language_interface theme url.query_args.pagers:0 url.query_args:_wrapper_format user',
      $response->headers->get('x-drupal-cache-contexts')
    );

    $this->container->get('current_user')->setAccount($user2);
    $request = Request::create('/collection');
    $response = $this->doRequest($request);
    self::assertEquals(200, $response->getStatusCode());
    $this->assertStringContainsString('user2 whiskey', $this->getTextContent());
    $this->assertStringNotContainsString('user1 whiskey', $this->getTextContent());
    self::assertEquals(
      'collection_item:2 collection_item_view http_response rendered whiskey:2 whiskey_view',
      $response->headers->get('x-drupal-cache-tags')
    );
    self::assertEquals(
      'languages:language_interface theme url.query_args.pagers:0 url.query_args:_wrapper_format user',
      $response->headers->get('x-drupal-cache-contexts')
    );
  }

  public function testAddItem(): void {
    $user = $this->createUser([], [
      'view own collection_item',
      'create collection_item',
      'update own collection_item',
      'view whiskey',
    ]);
    $this->container->get('current_user')->setAccount($user);

    $whiskey = Whiskey::create(['name' => $this->randomMachineName()]);
    $whiskey->save();

    $url = Url::fromRoute(
      'entity.collection_item.add_form',
      ['whiskey' => $whiskey->id()]
    )->toString();
    $request = Request::create($url);
    $response = $this->doRequest($request);
    self::assertEquals(200, $response->getStatusCode());
    $this->setRawContent((string) $response->getContent());
    $whiskey_element = $this->cssSelect('[name="whiskey[0][target_id]"]');
    self::assertCount(1, $whiskey_element);
    self::assertEquals(
      "{$whiskey->label()} ({$whiskey->id()})",
      // @phpstan-ignore-next-line
      $whiskey_element[0]->attributes()->value[0]
    );

    $request = Request::create($url, 'POST', [
      'whiskey' => [
        0 => ['target_id' => sprintf('%s (%s)', $whiskey->label(), $whiskey->id())],
      ],
      // @phpstan-ignore-next-line
      'form_build_id' => (string) $this->cssSelect('input[name="form_build_id"]')[0]->attributes()->value[0],
      // @phpstan-ignore-next-line
      'form_token' => (string) $this->cssSelect('input[name="form_token"]')[0]->attributes()->value[0],
      // @phpstan-ignore-next-line
      'form_id' => (string) $this->cssSelect('input[name="form_id"]')[0]->attributes()->value[0],
      'op' => 'Save',
    ]);
    $response = $this->doRequest($request);
    self::assertEquals(303, $response->getStatusCode(), $this->content);

    $request = Request::create('/collection');
    $response = $this->doRequest($request);
    self::assertEquals(200, $response->getStatusCode());
    $this->assertStringContainsString((string) $whiskey->label(), $this->getTextContent());
  }

}
