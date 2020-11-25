<?php

namespace Drupal\lb_everywhere\Entity;

use Drupal\Core\Entity\ContentEntityBase;

/**
 * Defines the LBEverywhere entity.
 *
 * @ingroup lb_everywhere
 *
 * @ContentEntityType(
 *   id = "lbeverywhere",
 *   label = @Translation("LBEverywhere"),
 *   bundle_label = @Translation("LBEverywhere type"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "access" = "Drupal\lb_everywhere\LBEverywhereAccessControlHandler",
 *     "form" = {
 *       "layout_builder" = "Drupal\lb_everywhere\Form\RegionForm",
 *     },
 *   },
 *   base_table = "lbeverywhere",
 *   data_table = "lbeverywhere_field_data",
 *   admin_permission = "administer lbeverywhere entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/lbeverywhere/{lbeverywhere}",
 *   }
 * )
 */
class LBEverywhere extends ContentEntityBase implements LBEverywhereInterface {

}
