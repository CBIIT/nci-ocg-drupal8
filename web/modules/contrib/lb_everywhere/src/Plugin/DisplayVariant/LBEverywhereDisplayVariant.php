<?php

namespace Drupal\lb_everywhere\Plugin\DisplayVariant;

use Drupal\block\Plugin\DisplayVariant\BlockPageVariant;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\Core\Url;
use Drupal\layout_builder\Context\LayoutBuilderContextTrait;
use Drupal\layout_builder\OverridesSectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Extends the block page variant to override selected regions.
 *
 * @PageDisplayVariant(
 *   id = "lb_everywhere",
 *   admin_label = @Translation("Layout Builder everywhere")
 * )
 */
class LBEverywhereDisplayVariant extends BlockPageVariant {

  use LayoutBuilderContextTrait;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The section storage manager.
   *
   * @var \Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface
   */
  protected $sectionStorageManager;

  /**
   * The region repository.
   *
   * @var \Drupal\lb_everywhere\LBEverywhereRegionRepository
   */
  protected $regionRepository;

  /**
   * The layout tempstore repository.
   *
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutTempstoreRepository;

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->requestStack = $container->get('request_stack');
    $instance->themeManager = $container->get('theme.manager');
    $instance->regionRepository = $container->get('lb_everywhere.region_repository');
    $instance->layoutTempstoreRepository = $container->get('layout_builder.tempstore_repository');
    $instance->entityFormBuilder = $container->get('entity.form_builder');
    $instance->currentUser = $container->get('current_user');
    $instance->routeMatch = $container->get('current_route_match');
    $instance->sectionStorageManager = $container->get('plugin.manager.layout_builder.section_storage');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = parent::build();
    $cacheability = CacheableMetadata::createFromRenderArray($build);
    $cacheability->addCacheContexts([
      'url.query_args',
      'user.permissions',
    ]);

    $current_request = $this->requestStack->getCurrentRequest();
    $in_layout_mode = $current_request->get('mode') === 'layout' && $this->currentUser->hasPermission('configure any layout');
    $in_region_mode = $current_request->get('region');
    $sections = NULL;

    // If this page represents an entity that is controlled by Layout Builder,
    // link the content region to the entity's layout form.
    if ($in_layout_mode && !$in_region_mode && preg_match('/^entity\.([a-z_]+)\.canonical$/', $this->routeMatch->getRouteName(), $matches)) {
      $entity = $this->routeMatch->getParameter($matches[1]);

      // @todo Take into account other view modes in
      //   https://www.drupal.org/node/3008924.
      $view_mode = EntityViewDisplay::collectRenderDisplay($entity, 'full')->getMode();

      $section_storage = $this->sectionStorageManager->load('overrides', [
        'entity' => EntityContext::fromEntity($entity),
        'view_mode' => new Context(new ContextDefinition('string'), $view_mode),
      ]);
      if ($section_storage instanceof OverridesSectionStorageInterface && $section_storage->getDefaultSectionStorage()->isOverridable()) {
        $build['content']['#attributes']['class'][] = 'region__select-mode';
        $build['content']['lbeverywhere'] = [
          '#type' => 'link',
          '#url' => $section_storage->getLayoutBuilderUrl()->setOption('query', [
            'mode' => 'layout',
            'region' => 'content',
          ]),
          '#title' => $this->t('Edit layout for the page content'),
          '#attributes' => [
            'class' => [
              'visually-hidden',
            ],
            'data-layout-builder-region' => TRUE,
          ],
        ];
      }
    }

    $active_theme = $this->themeManager->getActiveTheme();
    foreach ($this->regionRepository->getSectionStorages($active_theme->getName(), $cacheability) as $region => $section_storage) {
      // If this is the active region in layout mode, render the section form.
      if ($in_layout_mode && $in_region_mode === $region) {
        $section_storage = $this->layoutTempstoreRepository->get($section_storage);
        $display = $section_storage->getContextValue('display');
        $build[$region]['lbeverywhere'] = $this->entityFormBuilder->getForm($display, 'layout_builder', [
          'build_info' => [
            'args' => [
              $section_storage,
            ],
          ],
        ]);
      }
      // If layout mode is not active, or layout mode is active and there is a
      // selected region that is not this region, render the sections normally.
      else {
        $sections = $section_storage->getSections();
        foreach ($sections as $delta => $section) {
          $build[$region]['lbeverywhere'][$delta] = $section->toRenderArray($this->getAvailableContexts($section_storage));
        }
      }

      // If in layout mode with no active region, render the region selector.
      if ($in_layout_mode && !$in_region_mode) {
        $region_list = system_region_list($active_theme->getName());
        $build[$region]['#attributes']['class'][] = 'region__select-mode';
        $lb_link = [
          '#type' => 'link',
          '#url' => Url::fromRoute('<current>', [], [
            'query' => [
              'mode' => 'layout',
              'region' => $region,
            ],
          ]),
          '#title' => $this->t('Enable Layout Builder for the %region region', ['%region' => $region_list[$region]]),
          '#attributes' => [
            'data-layout-builder-region' => TRUE,
          ],
        ];
        if ($sections) {
          $lb_link['#attributes']['class'][] = 'visually-hidden';
        }

        if (!isset($build[$region]['lbeverywhere'])) {
          $build[$region]['lbeverywhere'] = [];
        }
        array_unshift($build[$region]['lbeverywhere'], $lb_link);
      }
    }
    $cacheability->applyTo($build);
    return $build;
  }

}
