<?php

namespace Drupal\hierarchy_manager\Plugin\HmSetupPlugin;

use Drupal\hierarchy_manager\Plugin\HmSetupPluginInterface;
use Drupal\hierarchy_manager\Plugin\HmSetupPluginBase;

/**
 * Menu link hierarchy setup plugin.
 *
 * @HmSetupPlugin(
 *   id = "hm_setup_menu",
 *   label = @Translation("Menu link hierarchy setup plugin")
 * )
 */
class HmMenu extends HmSetupPluginBase implements HmSetupPluginInterface {
}

