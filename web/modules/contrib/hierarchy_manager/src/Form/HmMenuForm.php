<?php

namespace Drupal\hierarchy_manager\Form;

use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;
use Drupal\menu_ui\MenuForm;

class HmMenuForm extends MenuForm {
  
  /**
   * The indicator if the menu hierarchy manager is enabled.
   * 
   * @var bool|NULL
   */
  private $isEnabled = NULL;
  
  /**
   * The hierarchy manager plugin type manager.
   *
   * @var \Drupal\hierarchy_manager\PluginTypeManager
   */
  private $hmPluginTypeManager = NULL;
  
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    
    // If the menu hierarchy manager plugin is enabled.
    // Override the menu overview form.
    if ($this->isMenuPluginEnabled() && $this->loadPluginManager()) {
          $menu = $this->entity;
          
          // Add menu links administration form for existing menus.
          if (!$menu->isNew() || $menu->isLocked()) {
            // We are removing the menu link overview form
            // and using our own hierarchy manager tree instead.
            // The overview form implemented by Drupal Menu UI module
            // @see \Drupal\menu_ui\MenuForm::form()
            unset($form['links']);
            $form['hm_links'] = $this->buildOverviewTree([], $form_state);
          }
    }
    
    return $form;
  }
  
  /**
   * Submit handler for the menu overview form.
   *
   * The hierarchy manager tree is a pure front-end solution in which
   * we don't need to deal with the submission data from the back-end.
   * Therefore nothing need to do,
   * if the menu hierarchy plugin is enabled.
   */
  protected function submitOverviewForm(array $complete_form, FormStateInterface $form_state) {
    if (!$this->isMenuPluginEnabled()) {
      parent::submitOverviewForm($complete_form, $form_state);
    }
  }
  
  /**
   * Build a menu links overview tree element.
   * 
   * @param array $form
   *   Parent form array.
   * @param FormStateInterface $form_state
   *   Form state object.
   * @return NULL|array
   */
  protected function buildOverviewTree(array $form, FormStateInterface $form_state) {
    
    $display_profile = $this->hmPluginTypeManager->getDisplayProfile('hm_setup_menu');
    
    if (empty($display_profile)) {
      return [];
    }

    $display_plugin_instance = $this->hmPluginTypeManager->getDisplayPluginInstance($display_profile);
    
    if (!empty($display_plugin_instance)) {
      if (method_exists($display_plugin_instance, 'getForm')) {
        // Menu ID.
        $mid = $this->entity->id();
        // CSRF token.
        $token = \Drupal::csrfToken()->get($mid);
        // Destination for edit link.
        $destination = $this->getDestinationArray();
        if (isset($destination['destination'])) {
          $destination = $destination['destination'];
        }
        else {
          $destination = '/';
        }
        // Urls
        $source_url = Url::fromRoute('hierarchy_manager.menu.tree.json', ['mid' => $mid], ['query' => ['token' => $token, 'destination' => $destination]])->toString();
        $update_url = Url::fromRoute('hierarchy_manager.menu.tree.update', ['mid' => $mid], ['query' => ['token' => $token]])->toString();       
        $config = $display_profile->get("config");
        return $display_plugin_instance->getForm($source_url, $update_url, $form, $form_state, $config);
      }
    }
    
    return [];
  }
  
  /**
   * Create a hierarchy manager plugin manager.
   * 
   * @return \Drupal\hierarchy_manager\PluginTypeManager
   */
  protected function loadPluginManager() {
    if (empty($this->hmPluginTypeManager)) {
      $this->hmPluginTypeManager = \Drupal::service('hm.plugin_type_manager');
    }
    
    return $this->hmPluginTypeManager;
  }
  
  /**
   * Check if the menu hierarchy plugin is enabled.
   * 
   * @return boolean|NULL
   *   Return TRUE if the menu plugin is enabled,
   *   otherwise return FALSE.
   */
  protected function isMenuPluginEnabled() {
    if ($this->isEnabled === NULL) {
      if ($config = \Drupal::config('hierarchy_manager.hmconfig')) {
        if ($allowed_setup_plugins = $config->get('allowed_setup_plugins')) {
          if (!empty($allowed_setup_plugins['hm_setup_menu'])) {
            $this->isEnabled = TRUE;
          }
          else {
            $this->isEnabled = FALSE;
          }
        }
      }
    }
    
    return $this->isEnabled;
  }
}

