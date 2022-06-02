<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Entity;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\entity\EntityAccessControlHandler;
use Drupal\entity\EntityPermissionProvider;
use Drupal\entity\Menu\DefaultEntityLocalTaskProvider;
use Drupal\entity\Menu\EntityCollectionLocalActionProvider;
use Drupal\entity\QueryAccess\QueryAccessHandler;
use Drupal\entity\QueryAccess\UncacheableQueryAccessHandler;
use Drupal\entity\Routing\AdminHtmlRouteProvider;
use Drupal\entity\UncacheableEntityAccessControlHandler;
use Drupal\entity\UncacheableEntityPermissionProvider;
use Drupal\user\EntityOwnerInterface;

final class ModelEntityType extends ContentEntityType {

  /**
   * @phpstan-param array<string, bool|string> $definition
   */
  public function __construct(array $definition) {
    if (empty($definition['label'])) {
      if (!is_string($definition['class'])) {
        throw new \InvalidArgumentException();
      }
      $class_path = explode('\\', $definition['class']);
      $value = preg_replace('/\s+/u', '', ucwords(array_pop($class_path)));
      if ($value !== NULL) {
        $definition['label'] = preg_replace('/(.)(?=[A-Z])/u', '$1 ', $value);
      }
    }

    parent::__construct($definition);
    $this->entity_keys['id'] = $this->id . '_id';
    $this->entity_keys['uuid'] = 'uuid';
    $this->entity_keys['label'] = 'name';
    if ($this->entityClassImplements(EntityOwnerInterface::class)) {
      $this->entity_keys['owner'] = 'uid';
    }

    $this->admin_permission = 'administer ' . $this->id;
    $this->base_table = $this->id;
    $this->data_table = $this->id . '_data';

    // Entity API contrib access improvements.
    if ($this->get('enhanced_entity_access')) {
      $this->handlers['access'] = EntityAccessControlHandler::class;
      $this->handlers['query_access'] = QueryAccessHandler::class;
      $this->handlers['permission_provider'] = EntityPermissionProvider::class;
    }
    if ($this->get('owner_entity_access')) {
      if (!$this->entityClassImplements(EntityOwnerInterface::class)) {
        throw new InvalidPluginDefinitionException(
          $this->id,
          sprintf("Entity %s must implement %s to use `owner_entity_access.", $this->id, EntityOwnerInterface::class)
        );
      }
      $this->handlers['access'] = UncacheableEntityAccessControlHandler::class;
      $this->handlers['query_access'] = UncacheableQueryAccessHandler::class;
      $this->handlers['permission_provider'] = UncacheableEntityPermissionProvider::class;
    }

    if ($this->get('has_ui')) {
      $class = basename(str_replace('\\', '/', $this->class));
      $namespace = "Drupal\\{$this->getProvider()}";

      $this->handlers['list_builder'] = EntityListBuilder::class;
      if (class_exists("$namespace\\{$class}ListBuilder")) {
        $this->handlers['list_builder'] = "$namespace\\{$class}ListBuilder";
      }

      $this->handlers['route_provider']['html'] = AdminHtmlRouteProvider::class;
      if (class_exists("$namespace\\Routing\\{$class}HtmlRouteProvider")) {
        $this->handlers['route_provider']['html'] = "$namespace\\Routing\\{$class}HtmlRouteProvider";
      }

      $this->handlers['form']['default'] = ContentEntityForm::class;
      $this->handlers['form']['delete'] = ContentEntityDeleteForm::class;
      $forms = [
        'default' => "{$class}Form",
        'add' => "{$class}AddForm",
        'edit' => "{$class}EditForm",
        'delete' => "{$class}DeleteForm",
      ];
      foreach ($forms as $operation => $class_name) {
        if (class_exists("$namespace\\Form\\$class_name")) {
          $this->handlers['form'][$operation] = "$namespace\\Form\\$class_name";
        }
      }

      $this->handlers['local_action_provider']['collection'] = EntityCollectionLocalActionProvider::class;
      $this->handlers['local_task_provider']['default'] = DefaultEntityLocalTaskProvider::class;

      // @todo check if has bundles.
      $this->links = [
        'collection' => '/admin/' . $this->id,
        'canonical' => '/' . $this->id . '/{' . $this->id . '}',
        'add-form' => '/' . $this->id . '/add',
        'edit-form' => '/' . $this->id . '/{' . $this->id . '}/edit',
        'delete-form' => '/' . $this->id . '/{' . $this->id . '}/delete',
      ];
      $this->field_ui_base_route = "entity.$this->id.collection";
    }
  }

}
