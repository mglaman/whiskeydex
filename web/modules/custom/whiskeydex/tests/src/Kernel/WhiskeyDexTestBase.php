<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Kernel;

use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;

abstract class WhiskeyDexTestBase extends EntityKernelTestBase {

  /**
   * @var string[]
   */
  protected static $modules = [
    'entity',
    'telephone',
    'address',
    'whiskeydex',
  ];

  protected function setUp(): void {
    parent::setUp();
    // Burn uid1.
    $this->createUser();
  }

}
