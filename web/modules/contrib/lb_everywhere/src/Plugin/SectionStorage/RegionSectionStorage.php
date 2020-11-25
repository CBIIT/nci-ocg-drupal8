<?php

namespace Drupal\lb_everywhere\Plugin\SectionStorage;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\layout_builder\Plugin\SectionStorage\SectionStorageBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides section storage for Layout Builder Everywhere regions.
 *
 * @SectionStorage(
 *   id = "lb_everywhere",
 *   context_definitions = {
 *     "display" = @ContextDefinition("entity:entity_view_display"),
 *   },
 * )
 */
class RegionSectionStorage extends SectionStorageBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getSectionList() {
    return $this->getContextValue('display');
  }

  /**
   * Gets the entity storing the defaults.
   *
   * @return \Drupal\layout_builder\Entity\LayoutEntityDisplayInterface
   *   The entity storing the defaults.
   */
  protected function getDisplay() {
    return $this->getSectionList();
  }

  /**
   * {@inheritdoc}
   */
  public function getStorageId() {
    return $this->getDisplay()->getTargetBundle();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRoutes(RouteCollection $collection) {
    $entity_route = $collection->get('lbeverywhere.controller');

    $path = $entity_route->getPath() . '/{storage_id}';

    $defaults = [];
    $requirements = [];
    $options = $entity_route->getOptions();
    $options['_admin_route'] = FALSE;

    $this->buildLayoutRoutes($collection, $this->getPluginDefinition(), $path, $defaults, $requirements, $options, '', 'lbeverywhere');
  }

  /**
   * Returns an array of relevant entity types.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface[]
   *   An array of entity types.
   */
  protected function getEntityTypes() {
    return array_filter($this->entityTypeManager->getDefinitions(), function (EntityTypeInterface $entity_type) {
      return $entity_type->entityClassImplements(FieldableEntityInterface::class) && $entity_type->hasHandlerClass('form', 'layout_builder') && $entity_type->hasViewBuilderClass();
    });
  }

  /**
   * {@inheritdoc}
   */
  public function getRedirectUrl() {
    return $this->getLayoutBuilderUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function getLayoutBuilderUrl($rel = 'view') {
    // Viewing the region is always done on the current page.
    if ($rel === 'view') {
      return Url::fromRouteMatch($this->routeMatch);
    }
    return Url::fromRoute("layout_builder.{$this->getStorageType()}.$rel", ['storage_id' => $this->getStorageId()]);
  }

  /**
   * {@inheritdoc}
   */
  public function deriveContextsFromRoute($value, $definition, $name, array $defaults) {
    $contexts = [];
    if ($entity = $this->extractEntityFromRoute($value, $defaults)) {
      $contexts['display'] = EntityContext::fromEntity($entity);
    }
    return $contexts;
  }

  /**
   * Extracts an entity from the route values.
   *
   * @param mixed $value
   *   The raw value from the route.
   * @param array $defaults
   *   The route defaults array.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity for the route, or NULL if none exist.
   *
   * @see \Drupal\layout_builder\SectionStorageInterface::deriveContextsFromRoute()
   * @see \Drupal\Core\ParamConverter\ParamConverterInterface::convert()
   */
  private function extractEntityFromRoute($value, array $defaults) {
    if ($value) {
      $storage_id = $value;
    }
    elseif (!empty($defaults['storage_id'])) {
      $storage_id = $defaults['storage_id'];
    }
    else {
      return NULL;
    }

    $storage = $this->entityTypeManager->getStorage('entity_view_display');
    // If the display does not exist, create a new one.
    if (!$display = $storage->load("lbeverywhere.$storage_id.default")) {
      $display = $storage->create([
        'targetEntityType' => 'lbeverywhere',
        'bundle' => $storage_id,
        'mode' => 'default',
        'status' => TRUE,
      ]);
    }
    return $display;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    return $this->getDisplay()->label();
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    return $this->getDisplay()->save();
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(RefinableCacheableDependencyInterface $cacheability) {
    $cacheability->addCacheableDependency($this);
    return $this->getDisplay()->getTargetEntityTypeId() === 'lbeverywhere';
  }

  /**
   * {@inheritdoc}
   */
  public function access($operation, AccountInterface $account = NULL, $return_as_object = FALSE) {
    $result = AccessResult::allowedIf($this->getDisplay()->isLayoutBuilderEnabled())->addCacheableDependency($this);
    return $return_as_object ? $result : $result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function getSectionListFromId($id) {
    @trigger_error('\Drupal\layout_builder\SectionStorageInterface::getSectionListFromId() is deprecated in drupal:8.7.0. It will be removed before drupal:9.0.0. The section list should be derived from context. See https://www.drupal.org/node/3016262', E_USER_DEPRECATED);
  }

  /**
   * {@inheritdoc}
   */
  public function extractIdFromRoute($value, $definition, $name, array $defaults) {
    @trigger_error('\Drupal\layout_builder\SectionStorageInterface::extractIdFromRoute() is deprecated in drupal:8.7.0. It will be removed before drupal:9.0.0. \Drupal\layout_builder\SectionStorageInterface::deriveContextsFromRoute() should be used instead. See https://www.drupal.org/node/3016262', E_USER_DEPRECATED);
  }

}
