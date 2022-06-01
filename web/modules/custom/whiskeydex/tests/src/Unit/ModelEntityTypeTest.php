<?php declare(strict_types=1);

namespace Drupal\Tests\whiskeydex\Unit;

use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityAccessControlHandler as CoreEntityAccessControlHandler;
use Drupal\entity\QueryAccess\UncacheableQueryAccessHandler;
use Drupal\entity\Routing\AdminHtmlRouteProvider;
use Drupal\entity\UncacheableEntityAccessControlHandler;
use Drupal\entity\UncacheableEntityPermissionProvider;
use Drupal\entity_module_test\Entity\EnhancedEntity;
use Drupal\entity_module_test\Entity\EnhancedEntityWithOwner;
use Drupal\entity_module_test\Form\EnhancedEntityForm;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\Tests\UnitTestCase;
use Drupal\user\EntityOwnerInterface;
use Drupal\whiskeydex\Entity\ModelEntityType;
use Drupal\entity\EntityAccessControlHandler;
use Drupal\entity\QueryAccess\QueryAccessHandler;
use Drupal\entity\EntityPermissionProvider;

final class ModelEntityTypeTest extends UnitTestCase {

  /**
   * @dataProvider provideDefinitions
   */
  public function testDefinitions(array $definition): void {
    self::assertArrayHasKey('id', $definition);
    $entity_type = new ModelEntityType($definition);
    $keys = [
      'revision' => '',
      'bundle' => '',
      'langcode' => '',
      'default_langcode' => 'default_langcode',
      'revision_translation_affected' => 'revision_translation_affected',
      'id' => $definition['id'] . '_id',
      'uuid' => 'uuid',
      'label' => 'name',
    ];
    if (is_subclass_of($definition['class'], EntityOwnerInterface::class)) {
      $keys['owner'] = 'uid';
    }
    self::assertEquals($keys, $entity_type->getKeys());
    self::assertEquals('administer ' . $definition['id'], $entity_type->getAdminPermission());

    self::assertEquals($definition['id'], $entity_type->getBaseTable());
    self::assertEquals($definition['id'] . '_data', $entity_type->getDataTable());

    if ($definition['enhanced_entity_access'] === false) {
      self::assertEquals(CoreEntityAccessControlHandler::class, $entity_type->getHandlerClass('access'));
      self::assertNull($entity_type->getHandlerClass('query_access'));
      self::assertNull($entity_type->getHandlerClass('permission_provider'));
    }
    elseif ($definition['owner_entity_access'] === true) {
      self::assertEquals(UncacheableEntityAccessControlHandler::class, $entity_type->getHandlerClass('access'));
      self::assertEquals(UncacheableQueryAccessHandler::class, $entity_type->getHandlerClass('query_access'));
      self::assertEquals(UncacheableEntityPermissionProvider::class, $entity_type->getHandlerClass('permission_provider'));
    }
    else {
      self::assertEquals(EntityAccessControlHandler::class, $entity_type->getHandlerClass('access'));
      self::assertEquals(QueryAccessHandler::class, $entity_type->getHandlerClass('query_access'));
      self::assertEquals(EntityPermissionProvider::class, $entity_type->getHandlerClass('permission_provider'));
    }

    if ($definition['has_ui'] === false) {
      self::assertFalse($entity_type->hasFormClasses());
      self::assertFalse($entity_type->hasRouteProviders());
    }
    else {
      self::assertTrue($entity_type->hasFormClasses());

      if ($definition['class'] === EnhancedEntity::class) {
        self::assertEquals(EnhancedEntityForm::class, $entity_type->getFormClass('default'));
      }
      else {
        self::assertEquals(ContentEntityForm::class, $entity_type->getFormClass('default'));
      }
      self::assertEquals(null, $entity_type->getFormClass('add'));
      self::assertEquals(null, $entity_type->getFormClass('edit'));
      self::assertEquals(ContentEntityDeleteForm::class, $entity_type->getFormClass('delete'));
      self::assertTrue($entity_type->hasRouteProviders());
      self::assertEquals([
        'html' => AdminHtmlRouteProvider::class,
      ], $entity_type->getRouteProviderClasses());
    }
  }

  public function provideDefinitions(): \Generator {
    yield [
      [
        'id' => 'foo',
        'class' => EntityTest::class,
        'enhanced_entity_access' => true,
        'owner_entity_access' => false,
        'has_ui' => false,
      ],
    ];
    yield [
      [
        'id' => 'foo',
        'class' => EntityTest::class,
        'enhanced_entity_access' => false,
        'owner_entity_access' => false,
        'has_ui' => false,
      ],
    ];
    yield [
      [
        'id' => 'foo',
        'class' => EntityTest::class,
        'enhanced_entity_access' => false,
        'owner_entity_access' => false,
        'provider' => 'entity_test',
        'has_ui' => true,
      ],
    ];
    yield [
      [
        'id' => 'foo',
        'class' => EntityTest::class,
        'enhanced_entity_access' => true,
        'owner_entity_access' => true,
        'provider' => 'entity_test',
        'has_ui' => true,
      ],
    ];
    yield [
      [
        'id' => 'entity_test_enhanced',
        'class' => EnhancedEntity::class,
        'enhanced_entity_access' => true,
        'owner_entity_access' => false,
        'provider' => 'entity_module_test',
        'has_ui' => true,
      ],
    ];
    yield [
      [
        'id' => 'entity_test_enhanced',
        'class' => EnhancedEntityWithOwner::class,
        'enhanced_entity_access' => true,
        'owner_entity_access' => true,
        'provider' => 'entity_module_test',
        'has_ui' => true,
      ],
    ];
  }

}
