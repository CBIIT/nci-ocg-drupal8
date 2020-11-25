/**
 * @file
 * A JavaScript file for the theme.
 *
 * In order for this JavaScript to be loaded on pages, see the instructions in
 * the README.txt next to this file.
 */

// JavaScript should be made compatible with libraries other than jQuery by
// wrapping it with an "anonymous closure". See:
// - http://drupal.org/node/1446420
// - http://www.adequatelygood.com/2010/3/JavaScript-Module-Pattern-In-Depth
(function ($, Drupal, window, document, undefined) {

	
$(document).ready (function (){

  $('.views-accordion.view-e-newsletter .view-grouping-header h3').addClass('collapsed');
  $('.views-accordion.view-e-newsletter .view-grouping-content').hide();
  $('.views-accordion.view-e-newsletter h3 .toggle').click(function(e){
    e.preventDefault();
    $(this).closest('.view-grouping').find('.view-grouping-content').slideToggle('slow');
    $(this).closest('h3').toggleClass('collapsed');
  });
  
  $(".expand-collapse").click(function(e) {
    $(".ui-accordion-content").show();
    if($('.views-accordion-header').hasClass('ui-accordion-header-collapsed')) {
      $(".ui-accordion-content").show();
      $('.views-accordion-header').removeClass('ui-accordion-header-collapsed').attr('aria-selected', 'true').attr('aria-expanded', 'true').addClass('ui-state-active').addClass('ui-accordion-header-active');
      $('.ui-accordion-header-icon').removeClass('ui-icon ui-icon-triangle-1-e').addClass('ui-icon ui-icon-triangle-1-s');
      $(this).text('Collapse All');
      e.preventDefault();
    } else {
      $(".ui-accordion-content").hide();
      $('.views-accordion-header').addClass('ui-accordion-header-collapsed').attr('aria-selected', 'false').attr('aria-expanded', 'false').removeClass('ui-state-active').removeClass('ui-accordion-header-active');
      $('.ui-accordion-header-icon').removeClass('ui-icon ui-icon-triangle-1-s').addClass('ui-icon ui-icon-triangle-1-e');
      $(this).text('Expand All');
      e.preventDefault();
    }
  });
});
})(jQuery, Drupal, this, this.document);
