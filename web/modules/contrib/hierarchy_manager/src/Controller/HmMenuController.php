<?php

namespace Drupal\hierarchy_manager\Controller;

use Drupal\Core\Url;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class HmMenuController extends ControllerBase {
  
  /**
   * CSRF Token.
   *
   * @var \Drupal\Core\Access\CsrfTokenGenerator
   */
  protected $csrfToken;
  
  /**
   * The menu_link_content storage handler.
   *
   * @var \Drupal\menu_link_content\MenuLinkContentStorageInterface
   */
  protected $storageController;
  
  /**
   * The hierarchy manager plugin type manager.
   *
   * @var \Drupal\hierarchy_manager\PluginTypeManager
   */
  protected $hmPluginTypeManager;
  
  /**
   * The menu tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuTree;
  
  /**
   * The menu tree array.
   *
   * @var array
   */
  protected $overviewTree = [];
  
  /**
   * The menu link manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;
  
  /**
   * {@inheritdoc}
   */
  public function __construct(CsrfTokenGenerator $csrfToken, EntityTypeManagerInterface $entity_type_manager, $plugin_type_manager, MenuLinkTreeInterface $menu_tree, MenuLinkManagerInterface $menu_link_manager) {    
    $this->csrfToken = $csrfToken;
    $this->entityTypeManager = $entity_type_manager;
    $this->storageController = $entity_type_manager->getStorage('menu_link_content');
    $this->hmPluginTypeManager = $plugin_type_manager;
    $this->menuTree = $menu_tree;
    $this->menuLinkManager = $menu_link_manager;
  }
  
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('csrf_token'),
        $container->get('entity_type.manager'),
        $container->get('hm.plugin_type_manager'),
        $container->get('menu.link_tree'),
        $container->get('plugin.manager.menu.link')
        );
  }
  
  /**
   * Callback for menu tree json.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Http request object.
   * @param string $mid
   *   Menu ID.
   */
  public function menuTreeJson(Request $request, string $mid) {
    // Access token.
    $token = $request->get('token');
    
    if (empty($token) || !$this->csrfToken->validate($token, $mid)) {
      return new Response($this->t('Access denied!'));
    }
    
    $parent = $request->get('parent');
    $depth = $request->get('depth');
    $destination = $request->get('destination');
    
    if (empty($depth)) {
      $depth = 0;
    }
    else {
      $depth = intval($depth);
    }
    
    if (empty($parent)) {
      $parent = '';
    }
    
    // We indicate that a menu administrator is running the menu access check.
    $request->attributes->set('_menu_admin', TRUE);
    
    $tree = $this->loadMenuTree($mid, $parent, $depth, $destination);
    
    // menu access check done.
    $request->attributes->set('_menu_admin', FALSE);
    
    if ($tree) {
      // Display plugin instance.
      $display_plugin = $this->getDisplayPlugin();
      
      if (empty($display_plugin)) {
        return new JsonResponse(['result' => 'Display profile has not been set up.']);
      }
      
      if (method_exists($display_plugin, 'treeData')) {
        // Transform the tree data to the structure
        // that display plugin accepts.
        $tree_data = $display_plugin->treeData($tree);
      }
      else {
        $tree_data = $tree;
      }
      
      return new JsonResponse($tree_data);
    }
    
    return new JsonResponse([]);
  }
  
  /**
   * Callback for taxonomy tree json.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Http request object.
   * @param string $vid
   *   Vocabulary ID.
   */
  public function updateMenuLinks(Request $request, string $mid) {
    // Access token.
    $token = $request->get('token');
    if (empty($token) || !$this->csrfToken->validate($token, $mid)) {
      return new Response($this->t('Access denied!'));
    }
    
    $target_position = $request->get('target');
    $parent = $request->get('parent');
    $updated_links = $request->get('keys');
    //$after = $request->get('after');
    $before = $request->get('before');
    $all_siblings = [];
    $insert_after = TRUE;
    
    if (is_array($updated_links) && !empty($updated_links)) {
      if (empty($parent)) {
        // Root is the parent.
        $parent = '';
        $parent_links = $children = $this->loadMenuLinkObjs($mid, $parent, 1);
      }
      else {
        // All children menu links (depth = 1).
        $parent_links = $this->loadMenuLinkObjs($mid, $parent, 1);
      }  
      // In order to make room for menu links inserted,
      // we need to move all children links forward,
      // and work out the weight for links inserted.
      if (empty($parent_links)) {
          // The parent menu doesn't exist.
          return new JsonResponse(['result' => 'fail']);
      }

      if (empty($children)) {
        $parent_link = reset($parent_links);
        $children = $parent_link->subtree;
      }

      if ($children) {
        // The parent menu has children.
        $target_position = intval($target_position);

        $position = 0;
        foreach ($children as $child) {
          $link = $child->link;
          $link_id = $link->getPLuginId();
          // Figure out if the new links are inserted
          // after the target position.
          if ($position++ == $target_position && $link_id !== $before) {
            $insert_after = FALSE;
          }
          
          $all_siblings[$link_id] = (int) $link->getWeight();
        }
      }
      else {
        // The parent link doesn't have children.
        
      }
      
      $new_hierarchy = $this->hmPluginTypeManager->updateHierarchy($target_position, $all_siblings, $updated_links, $insert_after);
      // Update all links need to update.
      foreach ($new_hierarchy as $link_id => $link_weight) {
        $this->menuLinkManager->updateDefinition($link_id, ['weight' => $link_weight, 'parent' => $parent]);
      }
      
      return new JsonResponse(['result' => 'success']);
    }
    
    return new JsonResponse(['result' => 'fail']);
  }
  
  /**
   * Get a display plugin instance.
   * 
   * @return NULL|object
   */
  protected function getDisplayPlugin() {
    $display_profile = $this->hmPluginTypeManager->getDisplayProfile('hm_setup_menu');
    return $this->hmPluginTypeManager->getDisplayPluginInstance($display_profile);
  }
  
  /**
   * Load menu links into one array.
   * 
   * @param string $mid
   *   The menu ID.
   * @param string $parent
   *   parent id
   * @param int $depth
   *   The max depth loaded.
   * @param string $destination
   *   The destination of edit link.
   */
  protected  function loadMenuTree(string $mid, string $parent, int $depth = 0, string $destination = '') {
    $tree = $this->loadMenuLinkObjs($mid, $parent, $depth);
    // Load all menu links into one array.
    $tree = $this->buildMenuLinkArray($tree);
    $links = [];
    foreach ($tree as $element) {
      if (!empty($destination)) {
        $element['url'] = $element['url'] . '?destination=' . $destination;
      }
      $links[] = $this->hmPluginTypeManager->buildHierarchyItem(
          $element['id'],
          $element['title'],
          $element['parent'],
          $element['url']);
    }
    
    return $links;
  }
  
  /**
   * Load menu links into one array.
   *
   * @param string $mid
   *   The menu ID.
   * @param string $parent
   *   parent id
   * @param int $depth
   *   The max depth loaded.
   * @param string $destination
   *   The destination of edit link.
   */
  protected  function loadMenuLinkObjs(string $mid, string $parent, int $depth = 0) {
    $menu_para = new MenuTreeParameters();
    if (!empty($depth)) {
      $menu_para->setMaxDepth($depth);
    }
    if (!empty($parent)) {
      $menu_para->setRoot($parent);
    }
    $tree = $this->menuTree->load($mid, $menu_para);
    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];
 
    return $tree = $this->menuTree->transform($tree, $manipulators);
  }
  
  /**
   * Recursive helper function for loadMenuTree().
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement[] $tree
   *   The tree retrieved by \Drupal\Core\Menu\MenuLinkTreeInterface::load().
   *
   * @return array
   *   The menu links array.
   */
  protected function buildMenuLinkArray($tree) {
//    $tree_access_cacheability = new CacheableMetadata();
    foreach ($tree as $element) {
//      $tree_access_cacheability = $tree_access_cacheability->merge(CacheableMetadata::createFromObject($element->access));
      
      // Only load accessible links.
      if (!$element->access->isAllowed()) {
        continue;
      }
      
      /** @var \Drupal\Core\Menu\MenuLinkInterface $link */
      $link = $element->link;
      if ($link) {
        // The id consistes of plugin ID and link ID. 
        $id = $link->getPluginId();
        $this->overviewTree[$id]['id'] = $id;
        if (!$link->isEnabled()) {
          $this->overviewTree[$id]['title'] = '(' . $this->t('disabled') . ')' . $link->getTitle();
        }
        // @todo Remove this in https://www.drupal.org/node/2568785.
        elseif ($id === 'user.logout') {
          $this->overviewTree[$id]['title'] = ' (' . $this->t('<q>Log in</q> for anonymous users') . ')' . $link->getTitle();
        }
        // @todo Remove this in https://www.drupal.org/node/2568785.
        elseif (($url = $link->getUrlObject()) && $url->isRouted() && $url->getRouteName() == 'user.page') {
          $this->overviewTree[$id]['title'] = ' (' . $this->t('logged in users only') . ')' . $link->getTitle();
        }
        else {
          $this->overviewTree[$id]['title'] = $link->getTitle();
        }
        
        $this->overviewTree[$id]['parent'] = $link->getParent();
        // Build the edit url.
        // Allow for a custom edit link per plugin.
        $edit_route = $link->getEditRoute();
        if ($edit_route) {
          $this->overviewTree[$id]['url'] = $edit_route->toString();
        }
        else {
          // Fall back to the standard edit link.
          $this->overviewTree[$id]['url'] = Url::fromRoute('menu_ui.link_edit', ['menu_link_plugin' => $link->getPluginId()])->toString();
        }
      }
      
      if ($element->subtree) {
        $this->buildMenuLinkArray($element->subtree);
      }
    }
    
 /*    $tree_access_cacheability
    ->merge(CacheableMetadata::createFromRenderArray($this->overviewTree))
    ->applyTo($form); */
    
    return $this->overviewTree;
  }
}

