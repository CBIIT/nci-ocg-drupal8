<?php

namespace Drupal\lb_everywhere\Form;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\layout_builder\Form\DefaultsEntityForm;

/**
 * Provides a form for Layout Builder Everywhere regions.
 */
class RegionForm extends DefaultsEntityForm {

  /**
   * {@inheritdoc}
   */
  public function getEntityFromRouteMatch(RouteMatchInterface $route_match, $entity_type_id) {
    $storage_id = $route_match->getParameter('storage_id');
    return $this->entityTypeManager->getStorage('entity_view_display')->load("lbeverywhere.$storage_id.default");
  }

}
