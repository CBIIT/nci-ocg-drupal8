/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/
function _toConsumableArray(arr) { if (Array.isArray(arr)) { for (var i = 0, arr2 = Array(arr.length); i < arr.length; i++) { arr2[i] = arr[i]; } return arr2; } else { return Array.from(arr); } }

(function ($, Drupal) {
  var _window$location = window.location,
      search = _window$location.search,
      origin = _window$location.origin,
      pathname = _window$location.pathname;


  var lbModeParams = new URLSearchParams(search);
  var isLayoutMode = lbModeParams.has('mode') && lbModeParams.get('mode') === 'layout';
  var isEditingRegion = lbModeParams.has('region');
  var originalLocation = '' + origin + pathname;

  var $lbModeButton = $('#toolbar-item-lb-mode');
  var $viewModeButton = $('.toolbar-item-view-mode');
  var $lbLayout = $('.layout-builder-form');

  function exitLayoutMode() {
    var pathItems = pathname.split('/');

    [].concat(_toConsumableArray(lbModeParams.keys())).forEach(function (param) {
      lbModeParams.delete(param);
    });

    $lbModeButton.removeAttr('data-toolbar-tray');

    if (pathItems[pathItems.length - 1] === 'layout') {
      pathItems.pop();
    }

    var updatedPathItems = pathItems.join('/');

    window.location.href = '' + origin + updatedPathItems;
  }

  Drupal.behaviors.lbEverywhere = {
    attach: function attach(context) {
      var toolbarModel = Drupal.toolbar.models.toolbarModel;

      $('.toolbar-lb-save').on('click', function (event) {
        event.preventDefault();
        $lbLayout.find('#edit-submit').click();
      });

      $('.toolbar-lb-revert').on('click', function (event) {
        event.preventDefault();
        $lbLayout.find('#edit-revert').click();
      });

      $('.toolbar-lb-discard').on('click', function (event) {
        event.preventDefault();
        $lbLayout.find('#edit-discard-changes').click();
      });

      $('.toolbar-item-view-mode').once().on('click', function (event) {
        exitLayoutMode();
        event.preventDefault();
      });

      $('#toolbar-item-lb-mode', context).once().on('click', function (event) {
        if (isEditingRegion) {
          var $activeTabID = $(toolbarModel.get('activeTab')).attr('id');

          if ($activeTabID === 'toolbar-item-lb-mode') {
            exitLayoutMode();
          }
        } else if (isLayoutMode) {
          exitLayoutMode();
        } else {
          lbModeParams.append('mode', 'layout');
          window.location.href = originalLocation + '?' + lbModeParams.toString();
        }

        event.preventDefault();
      });

      $('.region__select-mode', context).once().on('click', function (event) {
        var regionPath = $(event.currentTarget).find('[data-layout-builder-region]').attr('href');
        window.location.href = '' + origin + regionPath;
      });

      $(document).once().on('toolbar.loaded', function () {
        $(toolbarModel.set('activeTab', null));
        $viewModeButton.attr('data-toolbar-mode-active', 'true');

        if (isEditingRegion) {
          $(toolbarModel.set('activeTab', $lbModeButton));
          $lbModeButton.attr('data-toolbar-tray', 'toolbar-item-lb-mode-tray');

          if (!$('#edit-actions #edit-revert').length) {
            $('.toolbar-lb-revert').hide();
          }

          $('html').addClass('region-mode-overlay');
        } else {
          $lbModeButton.removeAttr('data-toolbar-mode-active');
          $lbModeButton.removeAttr('data-toolbar-tray');
        }

        if (isLayoutMode) {
          $('html').addClass('region-mode-overlay');
          $('.toolbar-icon-edit').parent().attr('hidden', true);

          $viewModeButton.removeAttr('data-toolbar-mode-active');
          $lbModeButton.attr('data-toolbar-mode-active', 'true');
        }
      }).trigger('toolbar.loaded');
    }
  };
})(jQuery, Drupal);