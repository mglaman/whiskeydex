<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\Http;

use Drupal\Tests\whiskeydex\Kernel\WhiskeyDexTestBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class WhiskeyDexHttpTestBase extends WhiskeyDexTestBase {

  protected function doRequest(Request $request): Response {
    $response = $this->container->get('http_kernel')->handle($request);
    $content = $response->getContent();
    self::assertNotFalse($content);
    $this->setRawContent($content);
    return $response;
  }

}
