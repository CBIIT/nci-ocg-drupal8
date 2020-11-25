<?php

namespace Drupal\lb_everywhere;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface;

/**
 * Provides methods to retrieve displays and section storages for regions.
 */
class LBEverywhereRegionRepository {

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $displayRepository;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The section storage manager.
   *
   * @var \Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface
   */
  protected $sectionStorageManager;

  /**
   * Constructs a new LBEverywhereRegionRepository.
   *
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $display_repository
   *   The display repository.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\layout_builder\SectionStorage\SectionStorageManagerInterface $section_storage_manager
   *   The section storage manager.
   */
  public function __construct(EntityDisplayRepositoryInterface $display_repository, ConfigFactoryInterface $config_factory, SectionStorageManagerInterface $section_storage_manager) {
    $this->displayRepository = $display_repository;
    $this->configFactory = $config_factory;
    $this->sectionStorageManager = $section_storage_manager;
  }

  /**
   * Gets all the section storages for a given theme.
   *
   * @param string $theme_name
   *   The theme machine name.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $cacheability
   *   Refinable cacheability object, which will be populated based on the
   *   cacheability of each section storage candidate. After calling this method
   *   this parameter will reflect the cacheability information used to
   *   determine the correct section storages. This must be associated with any
   *   output that uses the result of this method.
   *
   * @return \Drupal\layout_builder\SectionStorageInterface[]
   *   An array of section storages keyed by region.
   */
  public function getSectionStorages($theme_name, RefinableCacheableDependencyInterface $cacheability) {
    $config = $this->configFactory->get('lb_everywhere.settings');
    $region_map = $config->get("region_map.$theme_name") ?: [];
    $section_storages = [];
    foreach ($region_map as $region) {
      $section_storages[$region] = $this->doGetSectionStorage($theme_name, $region, $cacheability);
    }
    return $section_storages;
  }

  /**
   * Gets the section storage for a theme and region if it is enabled.
   *
   * @param string $theme_name
   *   The theme machine name.
   * @param string $region
   *   The region machine name.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $cacheability
   *   Refinable cacheability object, which will be populated based on the
   *   cacheability of each section storage candidate. After calling this method
   *   this parameter will reflect the cacheability information used to
   *   determine the correct section storage. This must be associated with any
   *   output that uses the result of this method.
   *
   * @return \Drupal\layout_builder\SectionStorageInterface|null
   *   The corresponding section if it exists, NULL otherwise.
   */
  public function getSectionStorage($theme_name, $region, RefinableCacheableDependencyInterface $cacheability) {
    $config = $this->configFactory->get('lb_everywhere.settings');
    if ($config->get("region_map.$theme_name.$region")) {
      return $this->doGetSectionStorage($theme_name, $region, $cacheability);
    }
  }

  /**
   * Gets the section storage regardless of status.
   *
   * @param string $theme_name
   *   The theme machine name.
   * @param string $region
   *   The region machine name.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $cacheability
   *   Refinable cacheability object, which will be populated based on the
   *   cacheability of each section storage candidate. After calling this method
   *   this parameter will reflect the cacheability information used to
   *   determine the correct section storage. This must be associated with any
   *   output that uses the result of this method.
   *
   * @return \Drupal\layout_builder\SectionStorageInterface
   *   The corresponding section storage.
   */
  protected function doGetSectionStorage($theme_name, $region, RefinableCacheableDependencyInterface $cacheability) {
    $display = $this->getDisplay($theme_name, $region);
    $contexts = [
      'display' => EntityContext::fromEntity($display),
    ];
    return $this->sectionStorageManager->findByContext($contexts, $cacheability);
  }

  /**
   * Gets the entity display for the given theme and region.
   *
   * @return \Drupal\layout_builder\Entity\LayoutEntityDisplayInterface
   *   The entity display.
   */
  public function getDisplay($theme_name, $region) {
    return $this->displayRepository->getViewDisplay('lbeverywhere', $theme_name . '__' . $region);
  }

}
