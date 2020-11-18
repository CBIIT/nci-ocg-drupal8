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
    if($('fieldset').hasClass('collapsed')) {
      $('fieldset').removeClass('collapsed');
      $(this).text('Collapse All');
      e.preventDefault();
    } else {
      $('fieldset').addClass('collapsed');
      $(this).text('Expand All');
      e.preventDefault();
    }
  });
});
})(jQuery, Drupal, this, this.document);
