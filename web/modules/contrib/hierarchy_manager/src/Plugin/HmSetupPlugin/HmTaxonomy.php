<?php

namespace Drupal\hierarchy_manager\Plugin\HmSetupPlugin;

use Drupal\hierarchy_manager\Plugin\HmSetupPluginInterface;
use Drupal\hierarchy_manager\Plugin\HmSetupPluginBase;

/**
 * Taxonomy hierarchy setup plugin.
 *
 * @HmSetupPlugin(
 *   id = "hm_setup_taxonomy",
 *   label = @Translation("Taxonomy hierarchy setup plugin")
 * )
 */
class HmTaxonomy extends HmSetupPluginBase implements HmSetupPluginInterface {
}
