<?php

namespace Drupal\hierarchy_manager;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\hierarchy_manager\Plugin\HmDisplayPluginManager;
use Drupal\hierarchy_manager\Plugin\HmSetupPluginManager;

class PluginTypeManager {
  
  /**
   * Display plugin manager.
   *
   * @var \Drupal\hierarchy_manager\Plugin\HmDisplayPluginManager
   */
  protected $displayManager;
  
  /**
   * Setup plugin manager.
   *
   * @var \Drupal\hierarchy_manager\Plugin\HmSetupPluginManager
   */
  protected $setupManager;
  
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;
  
  /**
   * {@inheritdoc}
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, HmDisplayPluginManager $display_manager, HmSetupPluginManager $setup_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->displayManager = $display_manager;
    $this->setupManager = $setup_manager;
  }
  
  /**
   * Construct an item inside the hierarchy.
   * 
   * @param string|int $id
   *   Item id.
   * @param string $label
   *   Item text.
   * @param string $parent
   *   Parent id of the item.
   * @param string $edit_url
   *   The URL where to edit this item.
   * @return array
   *   The hierarchy item array.
   */
  public function buildHierarchyItem($id, $label, $parent, $edit_url) {
    return 
    [
    'id' => $id,
    'text' => $label,
    'parent' => $parent,
    'edit_url' => $edit_url,
    ];
  }
  
  /**
   * Get a display plugin instance according to a setup plugin.
   * 
   * @param \Drupal\Core\Config\Entity\ConfigEntityBase $display_profile
   *   Display profile entity.
   * @return NULL|object
   *   The display plugin instance.
   */
  public function getDisplayPluginInstance(ConfigEntityBase $display_profile) {
    if (empty($display_profile)) {
      return NULL;
    }
    // Display plugin ID.
    $display_plugin_id = $display_profile->get("plugin");
    
    return $this->displayManager->createInstance($display_plugin_id);
  }
  
  /**
   * Get a display profile entity according to a setup plugin.
   *
   * @param string $setup_plugin_id
   *   setup plugin ID.
   * @return NULL|\Drupal\Core\Config\Entity\ConfigEntityBase
   *   The display profile entity.
   */
  public function getDisplayProfile(string $setup_plugin_id) {
    // The setup plugin instance.
    $setup_plugin = $this->setupManager->createInstance($setup_plugin_id);
    // Return the display profile.
    return  $this->entityTypeManager->getStorage('hm_display_profile')->load($setup_plugin->getDispalyProfileId());
  }
  
  /**
   * Update the items for a hierarchy
   * 
   * @param int $target_position
   *   Which position the new items will be insert.
   * @param array $all_siblings
   *   All siblings of the new items in an array[$item_id => (int)$weight]
   * @param array $updated_items
   *   IDs of new items inserted.
   * @param int|bool $after
   *   Indicator if new items are inserted after target position.
   * @param int $weight
   *   The initial weight.
   *   
   * @return array
   *   All siblings needed to move and their new weights.
   */
  public function updateHierarchy(int $target_position, array $all_siblings, array $updated_items, $after, int $weight = 0) {
    $new_hierarchy = [];
    $first_half = TRUE;
    
    if (!empty($all_siblings)) {
      $total = count($all_siblings);
      if ($target_position === 0) {
        // The insert postion is the first position.
        // we don't need to move any siblings.
        $weight = (int) reset($all_siblings) - 1;
      }
      elseif ($target_position >= $total - 1) {
        // The insert postion is the end,
        // we don't need to move any siblings.
        $last_item= array_slice($all_siblings, -1, 1, TRUE);
        $weight = (int) reset($last_item) + 1;
      }
      else {
        $target_item = array_slice($all_siblings, $target_position, 1, TRUE);
        $weight = (int) reset($target_item);
        // If the target position is in the second half,
        // we will move all siblings
        // after the target position forward.
        // Otherwise, we will move siblings
        // before the target position backwards.
        if ($target_position >= $total / 2) {
          $first_half = FALSE;
          
          if ($after) {
            // Insert after the target position.
            // The target stay where it is.
            $weight += 1;
            $moving_siblings = array_slice($all_siblings, $target_position + 1, NULL, TRUE);
          }
          else {
            // Insert before the target position.
            // The target need to move forwards.
            $moving_siblings = array_slice($all_siblings, $target_position, NULL, TRUE);
          }
          $step = $weight + count($updated_items);
        }
        else {
          if ($after) {
            // Insert after the target position.
            // The target need to move backwards.
            $moving_siblings = array_slice($all_siblings, 0, $target_position + 1, TRUE);
          }
          else {
            // Insert before the target position.
            // The target stay where it is.
            $weight -= 1;
            $moving_siblings = array_slice($all_siblings, 0, $target_position, TRUE);
          }
          $weight = $step = $weight - count($updated_items);
          // Reverse the siblings_moved array
          // as we will decrease the weight
          // starting from the first element
          // and the new weight should be in
          // an opposite order.
          $moving_siblings = array_reverse($moving_siblings, TRUE);
        }
        
        // Move all siblings that need to move.
        foreach($moving_siblings as $item_id => $item_weight) {
          // Skip all links in the updated array. They will be moved later.
          if (in_array($item_id, $updated_items)) {
            continue;
          }
          if ($first_half) {
            // While moving the first half of the siblings,
            // all moving siblings' weight are decreased,
            // if they are greater than the step.
            if ((int)$item_weight < --$step) {
              // There is planty room, no need to move anymore.
              break;
            }
            else {
              // Update the weight.
              $new_hierarchy[$item_id] = $step;
            }
          }
          else {
            // While moving the second half of the siblings,
            // all moving siblings' weight are increased,
            // if they are less than the step.
            if ((int)$item_weight < ++$step) {
              // Update the weight.
              $new_hierarchy[$item_id] = $step;
            }
            else {
              // There is planty room, no need to move anymore.
              break;
            }
          }
        }
      }
    }
    
    foreach ($updated_items as $item) {
      $new_hierarchy[$item] = $weight++;
    }
    
    return $new_hierarchy;
  }
}

