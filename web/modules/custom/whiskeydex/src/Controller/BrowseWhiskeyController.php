<?php declare(strict_types=1);

namespace Drupal\whiskeydex\Controller;

use Drupal\Core\Cache\Cache;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Url;
use Drupal\whiskeydex\Form\BrowseWhiskeyForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

final class BrowseWhiskeyController implements ContainerInjectionInterface {

  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly FormBuilderInterface $formBuilder
  ) {
  }

  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
      $container->get('form_builder')
    );
  }

  /**
   * @phpstan-return array<string, mixed>
   */
  public function browser(Request $request): array {
    $keys = $request->query->get('keys', '');
    $entity_type = $this->entityTypeManager->getDefinition('whiskey');
    $storage = $this->entityTypeManager->getStorage('whiskey');
    $query = $storage
      ->getQuery()
      ->accessCheck(TRUE)
      ->pager(10);
    if (is_string($keys) && $keys !== '') {
      $keys = trim($keys);
      $search = $query->orConditionGroup();
      $search->condition('name', "%$keys%", 'LIKE');
      $search->condition('distillery.entity.name', "%$keys%", 'LIKE');
      $query->condition($search);
    }
    $ids = $query->execute();
    $whiskeys = $storage->loadMultiple($ids);

    $build = [
      '#cache' => [
        'contexts' => Cache::mergeContexts(
          $entity_type?->getListCacheContexts() ?? [],
          ['url.query_args:keys']
        ),
        'tags' => $entity_type?->getListCacheTags() ?? [],
      ],
    ];
    $build['form'] = $this->formBuilder->getForm(BrowseWhiskeyForm::class);
    $build['list'] = $this->entityTypeManager
      ->getViewBuilder('whiskey')
      ->viewMultiple($whiskeys, 'browse');
    if ($keys !== '') {
      $build['suggest'] = [
        '#type' => 'link',
        '#title' => 'Add "' . $keys . '" as a new whiskey',
        '#url' => Url::fromRoute(
          'entity.whiskey.community_add_form',
          [],
          [
            'query' => [
              'name' => $keys,
            ],
          ]
        ),
        '#attributes' => [],
      ];
    }
    $build['pager'] = [
      '#type' => 'pager',
    ];
    return $build;
  }

}
