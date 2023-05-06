<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

final class RouteSubscriber extends RouteSubscriberBase {

  protected function alterRoutes(RouteCollection $collection): void {
    $user_edit_form = $collection->get('entity.user.edit_form');
    $user_edit_form?->setOption('_admin_route', FALSE);

    foreach ($collection->all() as $id => $route) {
      // Rename some paths to be less Drupal.
      switch ($id) {
        case 'user.register':
          $route->setPath('/signup');
          $route->setDefault('_title', 'Sign up');
          break;

        case 'user.login':
          $route->setPath('/login');
          break;

        case 'user.pass':
          $route->setPath('/forgot-password');
          $route->setDefault('_title', 'Forgot password');
          break;

        case 'entity.user.canonical':
          $route->setPath('/account/{user}');
          break;

        case 'entity.user.edit_form':
          $route->setPath('/account/{user}/edit');
          $route->setOption('_admin_route', FALSE);
          break;

        case 'user.page':
          $route->setPath('/account');
          break;

        case 'user.logout':
          $route->setPath('/account/logout');
          break;

        case 'user.reset.login':
          $route->setPath('/account/reset/{uid}/{timestamp}/{hash}/login');
          break;

        case 'user.reset':
          $route->setPath('/account/reset/{uid}/{timestamp}/{hash}');
          break;

        case 'user.reset.form':
          $route->setPath('/account/reset/{uid}');
          break;
      }
    }
  }

}
