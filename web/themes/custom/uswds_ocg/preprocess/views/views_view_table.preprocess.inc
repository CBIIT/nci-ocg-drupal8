<?php

/**
 * @file
 * Preprocess function for this hook.
 */

/**
 * Implements hook_preprocess_views_view_table().
 */
function uswds_ocg_preprocess_views_view_table(&$variables) {
  $view = $variables["view"];
  $id = $view->id();
  if ($id == 'target_data_matrix' && $view->current_display == 'sequence') {
    $variables['column1'] = array_search(1, array_column($view->result, 'paragraphs_item_field_data_paragraph__field_column_header__p'));
    $variables['column2'] = array_search(2, array_column($view->result, 'paragraphs_item_field_data_paragraph__field_column_header__p'));
    $variables['column3'] = array_search(3, array_column($view->result, 'paragraphs_item_field_data_paragraph__field_column_header__p'));
    $variables['column4'] = array_search(4, array_column($view->result, 'paragraphs_item_field_data_paragraph__field_column_header__p'));
  }
}
