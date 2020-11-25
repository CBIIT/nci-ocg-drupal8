<?php

namespace Drupal\lb_everywhere;

use Drupal\block\BlockInterface;
use Drupal\block\BlockListBuilder;
use Drupal\block\BlockRepositoryInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alters the Block UI to allow Layout Builder Everywhere to take over regions.
 *
 * Note that while the override of ::buildBlocksForm() could be accomplished
 * with a hook_form_alter() implementation, the override of ::load() can only
 * be accomplished by swapping out the list builder.
 */
class LBEverywhereBlockListBuilder extends BlockListBuilder {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type bundle info.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * The region repository.
   *
   * @var \Drupal\lb_everywhere\LBEverywhereRegionRepository
   */
  protected $regionRepository;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    $instance = parent::createInstance($container, $entity_type);
    $instance->configFactory = $container->get('config.factory');
    $instance->entityTypeBundleInfo = $container->get('entity_type.bundle.info');
    $instance->regionRepository = $container->get('lb_everywhere.region_repository');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildBlocksForm() {
    $form = parent::buildBlocksForm();
    $cacheability = CacheableMetadata::createFromRenderArray($form);

    $theme_name = $this->getThemeName();

    $region_list = $this->systemRegionList($this->getThemeName(), BlockRepositoryInterface::REGIONS_VISIBLE);
    foreach ($region_list as $region => $region_label) {
      if ($section_storage = $this->regionRepository->getSectionStorage($theme_name, $region, $cacheability)) {
        $form['region-' . $region . '-message']['message'] = [
          '#markup' => $this->t('The @region region is using Layout Builder', ['@region' => $region_label]),
        ];
        $toggle = FALSE;
        $button_label = $this->t('Stop using Layout Builder for @region', ['@region' => $region_label]);
      }
      else {
        $toggle = TRUE;
        $button_label = $this->t('Start using Layout Builder for @region', ['@region' => $region_label]);
      }
      $form["region-$region"]['title']['lb_everywhere'] = [
        '#type' => 'submit',
        '#theme_name' => $theme_name,
        '#region' => $region,
        '#toggle' => $toggle,
        '#submit' => ['::toggleRegionMap'],
        '#value' => $button_label,
        '#attributes' => [
          'class' => ['button', 'button--small'],
        ],
      ];
    }
    $cacheability->applyTo($form);
    return $form;
  }

  /**
   * Form submission handler.
   */
  public function toggleRegionMap(array &$form, FormStateInterface $form_state) {
    $element = $form_state->getTriggeringElement();
    $config = $this->configFactory->getEditable('lb_everywhere.settings');
    $region = $element['#region'];
    $theme_name = $element['#theme_name'];
    $region_map = $config->get("region_map.$theme_name") ?: [];
    if ($element['#toggle']) {
      $region_map[$region] = $region;
    }
    else {
      unset($region_map[$region]);
    }
    $config->set("region_map.$theme_name", $region_map);
    $config->save();

    // Clear the bundle cache, see lb_everywhere_entity_bundle_info().
    $this->entityTypeBundleInfo->clearCachedBundles();

    $display = $this->regionRepository->getDisplay($theme_name, $region);

    // Configure the display if it is being created for the first time.
    if ($element['#toggle'] && $display->isNew()) {
      // Remove any existing fields from the display.
      foreach ($display->getComponents() as $name => $component) {
        $display->removeComponent($name);
      }
      // @todo Add code here to copy blocks from existing region.
      $display->enableLayoutBuilder()->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entities = parent::load();

    $region_map = $this->configFactory->get('lb_everywhere.settings')->get("region_map.{$this->getThemeName()}") ?: [];
    return array_filter($entities, function (BlockInterface $block) use ($region_map) {
      return !in_array($block->getRegion(), $region_map);
    });
  }

}
