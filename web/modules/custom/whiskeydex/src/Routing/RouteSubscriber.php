<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

final class RouteSubscriber extends RouteSubscriberBase {

  protected function alterRoutes(RouteCollection $collection) {
    $user_edit_form = $collection->get('entity.user.edit_form');
    $user_edit_form?->setOption('_admin_route', FALSE);
  }

}
