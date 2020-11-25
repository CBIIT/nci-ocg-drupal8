<?php

namespace Drupal\lb_everywhere;

use Drupal\block\BlockRepositoryInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Hides blocks in regions controlled by Layout Builder Everywhere.
 */
class LBEverywhereBlockRepository implements BlockRepositoryInterface {

  /**
   * The decorated block repository.
   *
   * @var \Drupal\block\BlockRepositoryInterface
   */
  protected $decorated;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(BlockRepositoryInterface $decorated, ThemeManagerInterface $theme_manager, ConfigFactoryInterface $config_factory) {
    $this->decorated = $decorated;
    $this->themeManager = $theme_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getVisibleBlocksPerRegion(array &$cacheable_metadata_list = []) {
    $blocks = $this->decorated->getVisibleBlocksPerRegion($cacheable_metadata_list);

    $config = $this->configFactory->get('lb_everywhere.settings');
    $cacheable_metadata_list[] = CacheableMetadata::createFromObject($config);

    $active_theme = $this->themeManager->getActiveTheme();
    $region_map = $config->get("region_map.{$active_theme->getName()}") ?: [];

    return array_diff_key($blocks, $region_map);
  }

}
