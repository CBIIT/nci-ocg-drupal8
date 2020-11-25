<?php

namespace Drupal\views_fields_on_off\Plugin\views\filter;

use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Provides a handler that adds the form for Fields On/Off.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("views_fields_on_off_form")
 */
class ViewsFieldsOnOffForm extends InOperator {

  /**
   * {@inheritdoc}
   */
  public function canExpose() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function isExposed() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    if (isset($this->valueOptions)) {
      return $this->valueOptions;
    }
    $this->valueOptions = $this->displayHandler->getFieldLabels();
    return $this->valueOptions;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // This is not a real field and it only affects the query by excluding
    // fields from the display. But Views won't render if the query()
    // method is not present. This doesn't do anything, but it has to be here.
    // This function is a void so it doesn't return anything.
  }

  /**
   * Theme preprocess function.
   *
   * @see views_fields_on_off_preprocess_views_view()
   *
   * @param $variables
   *   Theme variables to be rendered.
   */
  public function preprocess(&$variables) {
    $field_options = $this->view->display_handler->getOption('fields');
    foreach ($field_options as $key => &$field) {
      if (isset($this->value[$key])) {
        continue;
      }

      unset($this->view->field[$key]);
    }
  }
}
