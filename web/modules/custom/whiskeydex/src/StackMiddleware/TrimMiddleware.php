<?php

declare(strict_types=1);

namespace Drupal\whiskeydex\StackMiddleware;

use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class TrimMiddleware implements HttpKernelInterface {

  public function __construct(
    private readonly HttpKernelInterface $app
  ) {
  }

  public function handle(
    Request $request,
    int $type = self::MAIN_REQUEST,
    bool $catch = TRUE
  ): Response {
    $this->trimBag($request->query);
    $this->trimBag($request->request);
    return $this->app->handle($request);
  }

  private function trimBag(InputBag $bag): void {
    $bag->replace($this->trimArray($bag->all()));
  }

  private function trimArray(array $array): array {
    return array_map(
      function ($value) {
        if (is_array($value)) {
          return $this->trimArray($value);
        }
        return is_string($value) ? trim($value) : $value;
      },
      $array
    );
  }

}
