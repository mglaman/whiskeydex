<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Routing;

use Drupal\Tests\whiskeydex\Kernel\WhiskeyDexTestBase;

final class RouteSubscriberTest extends WhiskeyDexTestBase {

  public function testAlterRoutes(): void {
    $router = $this->container->get('router');

    $user_edit_form_route = $router->getRouteCollection()->get('entity.user.edit_form');
    self::assertFalse($user_edit_form_route?->getOption('_admin_route'));
  }

}
