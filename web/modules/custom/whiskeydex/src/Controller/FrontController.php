<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Cache\CacheableRedirectResponse;
use Drupal\Core\Cache\CacheableResponseInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\user\Form\UserLoginForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

final class FrontController implements ContainerInjectionInterface {

  public function __construct(
    private readonly AccountInterface $account,
    private readonly HttpKernelInterface $httpKernel,
  ) {
  }

  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('current_user'),
      $container->get('http_kernel')
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
      assert($generated_url instanceof GeneratedUrl);
      return new CacheableRedirectResponse(
        $generated_url->getGeneratedUrl(),
        302,
        ['Cache-Control' => 'no-cache']
      );
    }

    $url = Url::fromRoute('user.login')->toString(TRUE);
    $subrequest = Request::create($url->getGeneratedUrl());
    if ($request->hasSession()) {
      $subrequest->setSession($request->getSession());
    }
    $response = $this->httpKernel->handle($subrequest, HttpKernelInterface::SUB_REQUEST);
    if ($response instanceof CacheableResponseInterface) {
      $response->addCacheableDependency($url);
    }
    return $response;
  }

}
