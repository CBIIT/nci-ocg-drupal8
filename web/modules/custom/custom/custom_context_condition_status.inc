<?php
/**
 * Expose the myfield field value as a context condition.
 */
class custom_context_condition_status extends context_condition {
  function condition_values() {
    $values = array();

    // Get the allowed options from our field, and return these to context
    // as the values for our condition.
    $field = field_info_field('field_programs_program_status');
    
    $field_values = list_allowed_values($field);
 
    foreach($field_values as $field_key => $field_value) {
      $values[$field_key] = check_plain($field_value);
    }
    return $values;
  }
 
  function execute($node) {
    // Grab the value this node has stored for our field.
    if ($items = field_get_items('node', $node, 'field_programs_program_status', $node->language)) {
 
      // See if any of the field's values satisfy the condition.
      foreach ($items as $item) {
        foreach ($this->get_contexts($item['value']) as $context) {
          $this->condition_met($context, $item['value']);
        }
      }
    }
  }
}
