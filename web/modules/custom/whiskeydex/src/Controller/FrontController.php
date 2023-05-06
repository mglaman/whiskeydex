<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class FrontController implements ContainerInjectionInterface {

  public function __construct(
    private readonly AccountInterface $account
  ) {
  }

  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('current_user')
    );
  }

  /**
   * @phpstan-return array<string, mixed>|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function front(Request $request): Response {
    $cacheable_metadata = new CacheableMetadata();
    $cacheable_metadata->addCacheContexts(['user.roles:authenticated']);
    if ($this->account->isAuthenticated()) {
      $generated_url = Url::fromRoute('entity.collection_item.collection')
        ->toString(TRUE);
      return new RedirectResponse(
        $generated_url->getGeneratedUrl(),
        302,
        ['Cache-Control' => 'no-cache']
      );
    }

    $url = Url::fromRoute('user.login')->toString(TRUE);
    return new RedirectResponse(
      $url->getGeneratedUrl(),
      302,
      ['Cache-Control' => 'no-cache']
    );
  }

}
