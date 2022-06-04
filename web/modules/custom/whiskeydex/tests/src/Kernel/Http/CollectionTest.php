<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Http;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\system\Entity\Menu;
use Drupal\user\Entity\User;
use Drupal\whiskeydex\Entity\Collection;
use Symfony\Component\HttpFoundation\Request;

final class CollectionTest extends WhiskeyDexHttpTestBase {

  public function register(ContainerBuilder $container): void {
    parent::register($container);
    $container->setParameter('http.response.debug_cacheability_headers', TRUE);
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
    self::assertEquals($allowed, $tree['entity.collection.collection']->access->isAllowed());
  }

  /**
   * @phpstan-ignore-next-line
   */
  public function providesMenuPermissionData(): iterable {
    yield [
      'administer collection',
      false,
    ];
    yield [
      'view any collection',
      false,
    ];
    yield [
      'view own collection',
      true,
    ];
    yield [
      '',
      false,
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

}
