<?php

declare(strict_types=1);

namespace Drupal\whiskeydex\Entity;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Core\Entity\ContentEntityDeleteForm;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\entity\EntityAccessControlHandler;
use Drupal\entity\EntityPermissionProvider;
use Drupal\entity\Menu\DefaultEntityLocalTaskProvider;
use Drupal\entity\Menu\EntityCollectionLocalActionProvider;
use Drupal\entity\QueryAccess\QueryAccessHandler;
use Drupal\entity\QueryAccess\UncacheableQueryAccessHandler;
use Drupal\entity\Routing\AdminHtmlRouteProvider;
use Drupal\entity\Routing\DefaultHtmlRouteProvider;
use Drupal\entity\UncacheableEntityAccessControlHandler;
use Drupal\entity\UncacheableEntityPermissionProvider;
use Drupal\user\EntityOwnerInterface;
use Drupal\whiskeydex\Form\ModelContentEntityForm;
use Symfony\Component\String\Inflector\EnglishInflector;

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

    // @todo there is only an English and French inflector...
    $inflector = new EnglishInflector();
    $label = $this->label;
    if ($label instanceof TranslatableMarkup) {
      $label = $label->getUntranslatedString();
    }
    $label_singular = mb_strtolower($label);
    $label_plural = mb_strtolower($inflector->pluralize($label)[0]);

    // phpcs:ignore Drupal.Semantics.FunctionT.NotLiteralString
    $this->label_collection = new TranslatableMarkup(ucfirst($label_plural));
    // phpcs:ignore Drupal.Semantics.FunctionT.NotLiteralString
    $this->label_singular = new TranslatableMarkup($label_singular);
    // phpcs:ignore Drupal.Semantics.FunctionT.NotLiteralString
    $this->label_plural = new TranslatableMarkup($label_plural);
    $this->label_count = [
      'singular' => "@count $label_singular",
      'plural' => "@count $label_plural",
    ];

    $class = basename(str_replace('\\', '/', $this->class));
    $namespace = "Drupal\\$this->provider";

    $this->entity_keys['id'] = $this->id . '_id';
    $this->entity_keys['uuid'] = 'uuid';
    $this->entity_keys['label'] = 'name';
    if ($this->entityClassImplements(EntityOwnerInterface::class)) {
      $this->entity_keys['owner'] = 'uid';
    }

    $this->admin_permission = 'administer ' . $this->id;
    $this->base_table = $this->id;
    $this->data_table = $this->id . '_data';

    $this->handlers['view_builder'] = $this->getEntityTypeSpecificClass(
      "$namespace\\{$class}ViewBuilder",
      EntityViewBuilder::class
    );

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

      $this->handlers['list_builder'] = $this->getEntityTypeSpecificClass("$namespace\\{$class}ListBuilder", EntityListBuilder::class);

      $default_route_provider = DefaultHtmlRouteProvider::class;
      if ($this->get('admin_ui_routes')) {
        $default_route_provider = AdminHtmlRouteProvider::class;
      }
      $this->handlers['route_provider']['html'] = $this->getEntityTypeSpecificClass(
        "$namespace\\Routing\\{$class}HtmlRouteProvider",
        $default_route_provider
      );

      $this->handlers['form']['default'] = $this->getEntityTypeSpecificClass(
        "$namespace\\Form\\{$class}Form",
        ModelContentEntityForm::class
      );
      $this->handlers['form']['delete'] = $this->getEntityTypeSpecificClass(
        "$namespace\\Form\\{$class}DeleteForm",
        ContentEntityDeleteForm::class
      );
      $forms = [
        'add' => "{$class}AddForm",
        'edit' => "{$class}EditForm",
      ];
      foreach ($forms as $operation => $class_name) {
        if (class_exists("$namespace\\Form\\$class_name")) {
          $this->handlers['form'][$operation] = "$namespace\\Form\\$class_name";
        }
      }

      $this->handlers['local_action_provider']['collection'] = $this->getEntityTypeSpecificClass(
        "$namespace\\Menu\\{$class}CollectionLocalActionProvider",
        EntityCollectionLocalActionProvider::class
      );

      if ($this->get('provide_tasks')) {
        $this->handlers['local_task_provider']['default'] = $this->getEntityTypeSpecificClass(
          "$namespace\\Menu\\{$class}EntityLocalTaskProvider",
          DefaultEntityLocalTaskProvider::class
        );
      }

      // @todo check if has bundles.
      if ($this->links === []) {
        $path_id = str_replace('_', '-', $this->id);
        $this->links = [
          'collection' => sprintf($this->get('admin_ui_routes') ? '/admin/%s' : '/%s', $path_id),
          'canonical' => sprintf('/%s/{%s}', $path_id, $this->id),
          'add-form' => sprintf('/%s/add', $path_id),
          'edit-form' => sprintf('/%s/{%s}/edit', $path_id, $this->id),
          'delete-form' => sprintf('/%s/{%s}/delete', $path_id, $this->id),
        ];
      }
      if ($this->field_ui_base_route === NULL && isset($this->links['collection'])) {
        $this->field_ui_base_route = "entity.$this->id.collection";
      }
    }
  }

  /**
   * @phpstan-param string $class
   * @phpstan-param class-string $default
   *
   * @phpstan-return class-string
   */
  private function getEntityTypeSpecificClass(string $class, string $default): string {
    return class_exists($class) ? $class : $default;
  }

}
