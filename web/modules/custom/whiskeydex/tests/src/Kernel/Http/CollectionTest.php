<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Http;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;
use Drupal\system\Entity\Menu;
use Drupal\user\Entity\User;
use Drupal\whiskeydex\Entity\Collection;
use Drupal\whiskeydex\Entity\Whiskey;
use Symfony\Component\HttpFoundation\Request;

final class CollectionTest extends WhiskeyDexHttpTestBase {

  public function register(ContainerBuilder $container): void {
    parent::register($container);
    $container->setParameter('http.response.debug_cacheability_headers', TRUE);
  }

  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('collection');
    $this->installEntitySchema('collection_item');
    $this->installEntitySchema('whiskey');
  }

  /**
   * @dataProvider providesMenuPermissionData
   */
  public function testMenu(string $permission, bool $allowed): void {
    if ($permission === '') {
      $test_account = User::getAnonymousUser();
    }
    else {
      $test_account = $this->createUser([], explode(',', $permission));
    }
    $this->container->get('current_user')->setAccount($test_account);

    Menu::create([
      'id' => 'main',
      'label' => 'main',
    ])->save();
    $this->container->get('plugin.manager.menu.link')->rebuild();
    $menu_tree = $this->container->get('menu.link_tree');
    $tree = $menu_tree->load('main', new MenuTreeParameters());
    $tree = $menu_tree->transform($tree, [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
    ]);
    self::assertArrayHasKey('entity.collection.collection', $tree);
    self::assertEquals($allowed, $tree['entity.collection.collection']->access?->isAllowed());
  }

  /**
   * @phpstan-ignore-next-line
   */
  public function providesMenuPermissionData(): iterable {
    yield [
      'administer collection',
      FALSE,
    ];
    yield [
      'view any collection',
      FALSE,
    ];
    yield [
      'view own collection',
      TRUE,
    ];
    yield [
      '',
      FALSE,
    ];
  }

  public function testAll(): void {
    $this->installEntitySchema('collection');
    $user1 = $this->createUser([], ['view own collection']);
    Collection::create([
      'name' => 'User 1 Collection',
      'uid' => $user1->id(),
    ])->save();
    $user2 = $this->createUser([], ['view own collection']);
    Collection::create([
      'name' => 'User 2 Collection',
      'uid' => $user2->id(),
    ])->save();

    $this->container->get('current_user')->setAccount($user1);
    $request = Request::create('/collections');
    $response = $this->doRequest($request);
    self::assertEquals(200, $response->getStatusCode());
    $this->assertStringContainsString('User 1 Collection', $this->getTextContent());
    $this->assertStringNotContainsString('User 2 Collection', $this->getTextContent());
    self::assertEquals(
      'collection:1 http_response rendered',
      $response->headers->get('x-drupal-cache-tags')
    );
    self::assertEquals(
      'languages:language_interface theme url.query_args.pagers:0 url.query_args:_wrapper_format user',
      $response->headers->get('x-drupal-cache-contexts')
    );

    $this->container->get('current_user')->setAccount($user2);
    $request = Request::create('/collections');
    $response = $this->doRequest($request);
    self::assertEquals(200, $response->getStatusCode());
    $this->assertStringContainsString('User 2 Collection', $this->getTextContent());
    $this->assertStringNotContainsString('User 1 Collection', $this->getTextContent());
    self::assertEquals(
      'collection:2 http_response rendered',
      $response->headers->get('x-drupal-cache-tags')
    );
    self::assertEquals(
      'languages:language_interface theme url.query_args.pagers:0 url.query_args:_wrapper_format user',
      $response->headers->get('x-drupal-cache-contexts')
    );
  }

  public function testAddItem(): void {
    $user = $this->createUser([], [
      'view own collection',
      'create collection',
      'update own collection',
      'view own collection_item',
      'create collection_item',
      'update own collection_item',
      'view whiskey',
    ]);
    $this->container->get('current_user')->setAccount($user);

    $collection = Collection::create([
      'name' => 'main',
      'uid' => $user->id(),
    ]);
    $collection->save();
    assert($collection instanceof Collection);
    $whiskey = Whiskey::create(['name' => 'foo']);
    $whiskey->save();
    assert($whiskey instanceof Whiskey);

    $url = Url::fromRoute('entity.collection_item.add_form', ['collection' => $collection->id()])->toString();
    $request = Request::create($url);
    $response = $this->doRequest($request);
    self::assertEquals(200, $response->getStatusCode());

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

    $collection = $this->reloadEntity($collection);
    assert($collection instanceof Collection);
    self::assertEquals(1, $collection->getItemsCount());
  }

}
