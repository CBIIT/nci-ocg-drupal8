<?php

namespace Drupal\lb_everywhere\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\lb_everywhere\LBEverywhereRegionRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a controller to provide the Layout Builder Everywhere admin UI.
 */
class LBEverywhereController implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The region repository.
   *
   * @var \Drupal\lb_everywhere\LBEverywhereRegionRepository
   */
  protected $regionRepository;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Constructs a new LBEverywhereController.
   *
   * @param \Drupal\lb_everywhere\LBEverywhereRegionRepository $region_repository
   *   The region repository.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(LBEverywhereRegionRepository $region_repository, ThemeHandlerInterface $theme_handler) {
    $this->regionRepository = $region_repository;
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('lb_everywhere.region_repository'),
      $container->get('theme_handler')
    );
  }

  /**
   * Builds the Layout Builder Everywhere page.
   *
   * @return array|\Symfony\Component\HttpFoundation\Response
   *   Either a response object or a render array.
   */
  public function build() {
    $links = [];
    $cacheability = new CacheableMetadata();
    $themes = $this->themeHandler->listInfo();
    foreach ($themes as $theme_name => $theme) {
      $region_list = system_region_list($theme);
      foreach ($this->regionRepository->getSectionStorages($theme_name, $cacheability) as $region => $section_storage) {
        $links[] = [
          'url' => $section_storage->getLayoutBuilderUrl(),
          'title' => $this->t('@theme: @region', [
            '@theme' => $theme->info['name'],
            '@region' => $region_list[$region],
          ]),
        ];
      }
    }
    $build = [
      '#theme' => 'links',
      '#links' => $links,
    ];
    $cacheability->applyTo($build);
    return $build;
  }

}
