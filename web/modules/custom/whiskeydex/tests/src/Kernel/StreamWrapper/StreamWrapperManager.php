<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel\StreamWrapper;

use Aws\CacheInterface;
use Drupal\Tests\whiskeydex\Kernel\Http\WhiskeyDexHttpTestBase;

final class StreamWrapperManager extends WhiskeyDexHttpTestBase {

  public function testContextDefaults(): void {
    $this->container->get('stream_wrapper_manager')->register();
    $default = stream_context_get_options(stream_context_get_default());
    self::assertArrayHasKey('s3', $default);
    $object_storage = $default['s3'];
    self::assertArrayHasKey('client', $object_storage);
    self::assertArrayHasKey('cache', $object_storage);
    self::assertInstanceOf(CacheInterface::class, $object_storage['cache']);
    self::assertArrayHasKey('ACL', $object_storage);
    self::assertEquals('public-read', $object_storage['ACL']);
  }

}
