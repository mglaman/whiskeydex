<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\Core\Routing\LocalRedirectResponse;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

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
  public function front(): array|RedirectResponse {
    $cacheable_metadata = new CacheableMetadata();
    $cacheable_metadata->addCacheContexts(['user.roles:authenticated']);
    if ($this->account->isAuthenticated()) {
      $generated_url = Url::fromRoute('entity.collection_item.collection')
        ->toString(TRUE);
      assert($generated_url instanceof GeneratedUrl);
      $cacheable_metadata->addCacheableDependency($generated_url);
      return new LocalRedirectResponse($generated_url->getGeneratedUrl());
    }

    $build = [
      '#theme' => 'whiskeydex_home',
    ];
    $cacheable_metadata->applyTo($build);
    return $build;
  }

}
