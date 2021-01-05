<?php

namespace Drupal\hierarchy_manager\Plugin\HmDisplayPlugin;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\hierarchy_manager\Plugin\HmDisplayPluginInterface;
use Drupal\hierarchy_manager\Plugin\HmDisplayPluginBase;

/**
 * JsTree display plugin.
 *
 * @HmDisplayPlugin(
 *   id = "hm_display_jstree",
 *   label = @Translation("JsTree")
 * )
 */
class HmDisplayJstree extends HmDisplayPluginBase implements HmDisplayPluginInterface {
  use StringTranslationTrait;
  
  /*
   * Build the tree form.
   */
  public function getForm(string $url_source, string $url_update, array &$form = [], FormStateInterface &$form_state = NULL, $options = NULL) {
    if (!empty($url_source)) {
      if (!empty(($form_state))) {
        $parent_formObj = $form_state->getFormObject();
        $parent_id = $parent_formObj->getFormId();
      }
      
      // The jsTree default theme.
      $theme = 'default';
      
      if (!empty($options)) {
        $jsonObj = json_decode($options);
        if (isset($jsonObj->theme) && isset($jsonObj->theme->name)) {
          $theme = $jsonObj->theme->name;
        }
      }
    
      // Search input.
      $form['search'] = [
        '#type' => 'textfield',
        '#title' => $this
        ->t('Search'),
        '#description' => $this->t('Type in the search keyword here to filter the tree below. Empty the keyword to reset the tree.'),
        '#attributes' => [
          'name' => 'jstree-search',
          'id' => isset($parent_id) ? 'hm-jstree-search-' . $parent_id : 'hm-jstree-search',
          'parent-id' => isset($parent_id) ? $parent_id : '',
          'class' => [
            'hm-jstree-search',
          ],
        ],
        '#size' => 60,
        '#maxlength' => 128,
      ];
      
      $form['jstree'] = [
        '#type' => 'html_tag',
        '#suffix' => '<div class="description">' . $this->t('Click a tree node to edit it.') . '<br>' . $this->t('The tree node is draggable and droppable') . '</div>',
        '#tag' => 'div',
        '#value' => '',
        '#attributes' => [
          'class' => [
            'hm-jstree',
          ],
          'id' => isset($parent_id) ? 'hm-jstree-' . $parent_id : 'hm-jstree',
          'parent-id' => isset($parent_id) ? $parent_id : '',
          'options' => $options,
          'data-source' => $url_source,
          'url-update' => $url_update,
        ],
      ];
      
      $form['#attached']['library'][] = 'hierarchy_manager/libraries.jquery.jstree.' . $theme;
      $form['#attached']['library'][] = 'hierarchy_manager/feature.hm.jstree';
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
    }
    
    return $form;
    
  }
  
  /**
   * Build the data array that JS library accepts.
   */
  public function treeData(array $data) {
    $jstree_data = [];
    
    // The array key of jsTree is different from the data source.
    // So we need to translate them.
    foreach ($data as $tree_node) {
      $jstree_node = $tree_node;
      // The root id for jsTree is #.
      if (empty($tree_node['parent'])) {
        $jstree_node['parent'] = '#';
      }
      // Custom data
      $jstree_node['a_attr'] = [
        'href' => $jstree_node['edit_url'],
      ];
      unset($jstree_node['edit_url']);
      // Add this node into the data array.
      $jstree_data[] = $jstree_node;
    }
    
    return $jstree_data;
  }
}
