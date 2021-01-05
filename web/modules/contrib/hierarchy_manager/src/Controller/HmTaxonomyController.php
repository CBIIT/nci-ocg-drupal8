<?php

namespace Drupal\hierarchy_manager\Controller;

use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\taxonomy\VocabularyInterface;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Taxononmy controller class.
 */
class HmTaxonomyController extends ControllerBase {

  /**
   * CSRF Token.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfToken;

  /**
   * The term storage handler.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $storageController;
  
  /**
   * The hierarchy manager plugin type manager.
   *
   * @var \Drupal\hierarchy_manager\PluginTypeManager
   */
  protected $hmPluginTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(CsrfTokenGenerator $csrfToken, EntityTypeManagerInterface $entity_type_manager, $plugin_type_manager) {

    $this->csrfToken = $csrfToken;
    $this->entityTypeManager = $entity_type_manager;
    $this->storageController = $entity_type_manager->getStorage('taxonomy_term');
    $this->hmPluginTypeManager = $plugin_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('csrf_token'),
        $container->get('entity_type.manager'),
        $container->get('hm.plugin_type_manager')
        );
  }

  /**
   * Callback for taxonomy tree json.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Http request object.
   * @param string $vid
   *   Vocabulary ID.
   */
  public function taxonomyTreeJson(Request $request, string $vid) {
    // Access token.
    $token = $request->get('token');
    // The term array will be returned.
    $term_array = [];

    if (empty($token) || !$this->csrfToken->validate($token, $vid)) {
      return new Response($this->t('Access denied!'));
    }
    $parent = $request->get('parent') ?: 0;
    $depth = $request->get('depth');
    $destination = $request->get('destination');
    
    if(!empty($depth)) {
      $depth = intval($depth);
    }

    $vocabulary_hierarchy = $this->storageController->getVocabularyHierarchyType($vid);
    // Taxonomy tree must not be multiple parent tree.
    if ($vocabulary_hierarchy !== VocabularyInterface::HIERARCHY_MULTIPLE) {
      $tree = $this->storageController->loadTree($vid, $parent, $depth, TRUE);

      $access_control_handler = $this->entityTypeManager->getAccessControlHandler('taxonomy_term');
      
      foreach ($tree as $term) {
        if ($term instanceof Term) {
          // User can only access the terms that they can update.
          if ($access_control_handler->access($term, 'update')) {
            if (empty($destination)) {
              $url = $term->toUrl('edit-form')->toString();
            }
            else {
              $url = $term->toUrl('edit-form', ['query' => ['destination' => $destination]])->toString();
            }

            $term_array[] = $this->hmPluginTypeManager->buildHierarchyItem(
                $term->id(), 
                $term->label(), 
                $term->parents[0], 
                $url);
          }
        }
      }
    }
    
    // Display profile.
    $display_profile = $this->hmPluginTypeManager->getDisplayProfile('hm_setup_taxonomy');
    // Display plugin instance.
    $display_plugin = $this->hmPluginTypeManager->getDisplayPluginInstance($display_profile);
    
    if (empty($display_plugin)) {
      return new JsonResponse(['result' => 'Display profile has not been set up.']);
    }

    if (method_exists($display_plugin, 'treeData')) {
      // Convert the tree data to the structure
      // that display plugin accepts.
      $tree_data = $display_plugin->treeData($term_array);
    }
    else {
      $tree_data = $term_array;
    }

    return new JsonResponse($tree_data);
  }

  /**
   * Callback for taxonomy tree json.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Http request object.
   * @param string $vid
   *   Vocabulary ID.
   */
  public function updateTerms(Request $request, string $vid) {
    // Access token.
    $token = $request->get('token');
    if (empty($token) || !$this->csrfToken->validate($token, $vid)) {
      return new Response($this->t('Access denied!'));
    }

    $target_position = $request->get('target');
    $parent_id = $request->get('parent');
    $updated_terms = $request->get('keys');
    //$after = $request->get('after');
    $before = $request->get('before');
    $success = FALSE;
    $insert_after = TRUE;
    $all_siblings = [];

    if (is_array($updated_terms) && !empty($updated_terms)) {
      // Taxonomy access control.
      $access_control_handler = $this->entityTypeManager->getAccessControlHandler('taxonomy_term');

      // Children of the parent term in weight and name alphabetically order.
      $children = $this->storageController->loadTree($vid, $parent_id, 1);
      if (empty($children)) {
        if (Term::load($parent_id)) {
          // The parent term hasn't had any children.
        }
        else {
          // The parent term doesn't exist.
          return new JsonResponse(['result' => 'fail']);
        }
      }
      else {
        // The parent term has children.
        $target_position = intval($target_position);       
        $position = 0;
        
        foreach ($children as $child) {
          // Figure out if the new links are inserted
          // after the target position.
          if ($position++ == $target_position && $child->tid !== $before) {
            $insert_after = FALSE;
          }
          
          $all_siblings[$child->tid] = (int) $child->weight;
        }
      }
      
      $new_hierarchy = $this->hmPluginTypeManager->updateHierarchy($target_position, $all_siblings, $updated_terms, $insert_after);
      $tids = array_keys($new_hierarchy);
      
      // Load all terms needed to update.
      $terms = Term::loadMultiple($tids);
      // Update all terms.
      foreach ($terms as $term) {
        if ($access_control_handler->access($term, 'update')) {
          $term->set('parent', ['target_id' => $parent_id]);
          $term->setWeight($new_hierarchy[$term->id()]);
          $success = $term->save();
        }
      }
    }

    if ($success) {
      return new JsonResponse(['result' => 'success']);
    }

    return new JsonResponse(['result' => 'fail']);
  }

}
