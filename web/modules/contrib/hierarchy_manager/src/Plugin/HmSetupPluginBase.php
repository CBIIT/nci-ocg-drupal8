<?php

namespace Drupal\hierarchy_manager\Plugin;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Base class for Hierarchy Manager Setup Plugin plugins.
 */
abstract class HmSetupPluginBase extends PluginBase implements HmSetupPluginInterface {
  use StringTranslationTrait;

  /**
   * Display profile ID.
   *
   * @var string
   */
  protected  $displayProfile;

  /**
   * Constructs a new setup plugin object.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin id.
   * @param mixed $plugin_definition
   *   The plugin definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $plugin_settings = \Drupal::config('hierarchy_manager.hmconfig')->get('setup_plugin_settings');
    if (isset($plugin_settings[$this->pluginId])) {
      $this->displayProfile = $plugin_settings[$this->pluginId]['display_profile'];
    }
    else {
      $this->displayProfile = '';
    }
  }

  /**
   * Common methods and abstract methods for HM setup plugin type.
   */
  public function buildConfigurationForm($config, $state) {
    // All display profiles.
    $display_profiles = \Drupal::entityTypeManager()->getStorage('hm_display_profile')->loadMultiple();
    $display_options = [];
    foreach ($display_profiles as $profile) {
      $display_options[$profile->id()] = $profile->label();
    }
    $settings_form['display_profile'] = [
      '#type' => 'select',
      '#title' => $this->t('Display Profile'),
      '#options' => $display_options,
      '#description' => 'Specify the display profile to render the hierarchy tree.',
      '#default_value' => $this->displayProfile,
      '#required' => TRUE,
    ];

    return $settings_form;
  }

  /**
   * Get the display profile ID.
   *
   * @return string
   *   The profile ID.
   */
  public function getDispalyProfileId() {
    return $this->displayProfile;
  }

}
