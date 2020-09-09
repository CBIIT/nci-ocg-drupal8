<?php

/**
 * @file
 * Preprocess function for this hook.
 */

/**
 * Implements hook_preprocess_page().
 */
function uswds_ocg_preprocess_page(&$variables) {
  // Set the path to the base theme.
  $variables['active_theme_path'] = base_path() . \Drupal::theme()->getActiveTheme()->getPath();

  // Provide the agency information for the footer.
  $variables['display_footer_agency'] = theme_get_setting('uswds_footer_agency');
  $footer_variables = [
    'footer_agency_name',
    'footer_agency_url',
    'footer_agency_logo',
    'contact_center',
    'phone',
    'email',
    'facebook',
    'twitter',
    'youtube',
    'rss',
  ];
  foreach ($footer_variables as $footer_variable) {
    $variables[$footer_variable] = theme_get_setting('uswds_' . $footer_variable);
  }
  // Fix the footer logo path.
  if ($variables['footer_agency_logo']) {
    $variables['footer_agency_logo'] = base_path() . uswds_theme_trail_file($variables['footer_agency_logo']);
  }

  // Decide what width the content area should have.
  $variables['main_class'] = 'grid-container';
  $sidebar_first = (!empty($variables['page']['sidebar_first']));
  $sidebar_second = (!empty($variables['page']['sidebar_second']));
  if ($sidebar_first && $sidebar_second) {
    $variables['content_class'] = 'tablet:grid-col-7';
  }
  elseif ($sidebar_second && !$sidebar_first) {
    $variables['content_class'] = 'tablet:grid-col-9';
  }
  elseif ($sidebar_first && !$sidebar_second) {
    $variables['content_class'] = 'tablet:grid-col-10';
  }
  elseif (!$sidebar_first && !$sidebar_second) {
    $variables['content_class'] = 'tablet:grid-col-12';
  }

  // Decide on the type of header.
  $header_style = theme_get_setting('uswds_header_style');
  if (empty($header_style)) {
    $header_style = 'extended';
  }
  $variables['header_style'] = 'usa-header--' . $header_style;
  $variables['header_classes'] = 'usa-header ' . $variables['header_style'];
  if (theme_get_setting('uswds_header_mega')) {
    if ($header_style == 'basic') {
      $variables['header_classes'] = 'usa-header ' . $variables['header_style'] . ' usa-header--megamenu';
    }
  }

  // And some helpful flags for the header.
  $variables['header_basic'] = ('basic' === $header_style);
  $variables['header_extended'] = ('extended' === $header_style);

  // Hide the secondary menu if using the basic header.
  if ($variables['header_basic']) {
    $variables['page']['secondary_menu'] = FALSE;
  }

  // Show the official U.S. Government banner?
  if (theme_get_setting('uswds_government_banner')) {
    $variables['government_banner'] = [
      '#theme' => 'government_banner',
      '#image_base' => $variables['active_theme_path'] . '/assets/img',
    ];
  }

  // Decide on the type of footer.
  $footer_style = theme_get_setting('uswds_footer_style');
  if (empty($footer_style)) {
    $footer_style = 'slim';
  }
  $variables['footer_style'] = 'usa-footer--' . $footer_style;
  $variables['footer_classes'] = 'usa-footer ' . $variables['footer_style'];
  $variables['footer_menu_width'] = 'mobile-lg:grid-col-12';
  $variables['footer_menu_padding'] = '';
  $variables['footer_logo_gap'] = 'grid-gap';

  // Add some helpful flags for the footer.
  $variables['footer_slim'] = ('slim' == $footer_style);
  $variables['footer_medium'] = ('medium' == $footer_style);
  $variables['footer_big'] = ('big' == $footer_style);

  // Some differences for the slim footer.
  if ($variables['footer_slim']) {
    $variables['footer_menu_padding'] = 'desktop:padding-x-4';
    $variables['footer_logo_gap'] = 'grid-gap-2';
    if ($variables['email'] || $variables['phone']) {
      $variables['footer_menu_width'] = 'mobile-lg:grid-col-8';
    }
  }

  // Whether to display the primary footer section.
  $display_primary_footer = (!empty($variables['page']['footer']));
  $display_primary_footer |= (!empty($variables['page']['footer_menu']));
  $display_primary_footer |= ($variables['footer_slim'] && ($variables['email'] || $variables['phone']));
  $variables['display_footer_primary'] = $display_primary_footer;

  // Whether to display the secondary footer section.
  $display_secondary_footer = (!empty($variables['page']['footer_secondary']));
  $display_secondary_footer |= $variables['display_footer_agency'];
  $variables['display_footer_secondary'] = $display_secondary_footer;

  // Check to see if we need to turn on edge-to-edge mode.
  if (_uswds_edge_to_edge_mode()) {
    // If edge-to-edge mode is on, then the content area will not be wrapped in
    // "usa-grid", and the sidebars will be disabled.
    $variables['edge_to_edge'] = TRUE;
    $variables['main_class'] = '';
    $variables['content_class'] = 'grid-col-12';
    $variables['page']['sidebar_first'] = [];
    $variables['page']['sidebar_second'] = [];
  }
}