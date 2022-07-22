<?php
declare(strict_types=1);

namespace Drupal\whiskeydex\Controller;

use Drupal\Component\Datetime\DateTimePlus;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

final class ProgressiveWebAppController implements ContainerInjectionInterface {

  public function __construct(
    private readonly ThemeExtensionList $themeExtensionList,
    private readonly FileUrlGeneratorInterface $fileUrlGenerator,
    private readonly TimeInterface $time
  ) {
  }

  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('extension.list.theme'),
      $container->get('file_url_generator'),
      $container->get('datetime.time')
    );
  }

  public function manifest(): CacheableJsonResponse {
    $icon = $this->themeExtensionList->getPath('distilled') . '/logo.svg';
    $manifest = [
      'short_name' => 'WhiskeyDex',
      'orientation' => 'portrait',
      'start_url' => '/collection?from=pwa',
      'background_color' => '#e7e5e4',
      'theme_color' => '#44403c',
      'display' => 'standalone',
      'scope' => '/',
      'icons' => [
        [
          'src' => $this->fileUrlGenerator->generateAbsoluteString($icon),
          'type' => 'image/svg+xml',
          'sizes' => '171x171',
        ],
      ],
    ];
    $response = new CacheableJsonResponse($manifest);
    $response->getCacheableMetadata()->addCacheTags(['manifest']);
    $this->makeResponseCacheable($response);
    return $response;
  }

  public function serviceWorker(): CacheableResponse {
    $data = file_get_contents(__DIR__ . '/../../js/service-worker.js');
    $response = new CacheableResponse((string) $data, 200, [
      'Content-Type' => 'application/javascript',
      'Service-Worker-Allowed' => '/',
    ]);
    $this->makeResponseCacheable($response);
    return $response;
  }

  private function makeResponseCacheable(Response $response): void {
    $timestamp = $this->time->getRequestTime();
    $response->headers->set('Cache-Control', 'public, max-age=86400');
    $response->setLastModified(new \DateTime(gmdate(DateTimePlus::RFC7231, $timestamp)));
    $response->setEtag((string) $timestamp);
  }

}
