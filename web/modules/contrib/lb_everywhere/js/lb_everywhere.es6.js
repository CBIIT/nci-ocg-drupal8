/**
 * @file
 * Provides Javascript for the Layout Builder Everywhere module.
 */

(($, Drupal) => {
  const { search, origin, pathname } = window.location;

  const lbModeParams = new URLSearchParams(search);
  const isLayoutMode =
    lbModeParams.has('mode') && lbModeParams.get('mode') === 'layout';
  const isEditingRegion = lbModeParams.has('region');
  const originalLocation = `${origin}${pathname}`;

  const $lbModeButton = $('#toolbar-item-lb-mode');
  const $viewModeButton = $('.toolbar-item-view-mode');
  const $lbLayout = $('.layout-builder-form');

  /**
   * Redirects user to the original page location and removes any url
   * parameters.
   */
  function exitLayoutMode() {
    const pathItems = pathname.split('/');

    [...lbModeParams.keys()].forEach(param => {
      lbModeParams.delete(param);
    });

    $lbModeButton.removeAttr('data-toolbar-tray');

    if (pathItems[pathItems.length - 1] === 'layout') {
      pathItems.pop();
    }

    const updatedPathItems = pathItems.join('/');

    window.location.href = `${origin}${updatedPathItems}`;
  }

  /**
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.lbEverywhere = {
    attach(context) {
      const { toolbarModel } = Drupal.toolbar.models;

      /* Click save, revert, and discard buttons in the Layout Builder edit area. */
      $('.toolbar-lb-save').on('click', event => {
        event.preventDefault();
        $lbLayout.find('#edit-submit').click();
      });

      $('.toolbar-lb-revert').on('click', event => {
        event.preventDefault();
        $lbLayout.find('#edit-revert').click();
      });

      $('.toolbar-lb-discard').on('click', event => {
        event.preventDefault();
        $lbLayout.find('#edit-discard-changes').click();
      });

      $('.toolbar-item-view-mode')
        .once()
        .on('click', event => {
          exitLayoutMode();
          event.preventDefault();
        });

      /* Toggle layout mode. If layout mode is active and the button is clicked, deactivate layout mode. */
      $('#toolbar-item-lb-mode', context)
        .once()
        .on('click', event => {
          if (isEditingRegion) {
            const $activeTabID = $(toolbarModel.get('activeTab')).attr('id');

            if ($activeTabID === 'toolbar-item-lb-mode') {
              exitLayoutMode();
            }
          } else if (isLayoutMode) {
            /* If the user is in Layout Mode, remove url parameters and go back to viewing the node. */
            exitLayoutMode();

            /* Enter Layout Mode. */
          } else {
            lbModeParams.append('mode', 'layout');
            window.location.href = `${originalLocation}?${lbModeParams.toString()}`;
          }

          event.preventDefault();
        });

      /* Click region to make it 'editable'. */
      $('.region__select-mode', context)
        .once()
        .on('click', event => {
          const regionPath = $(event.currentTarget)
            .find('[data-layout-builder-region]')
            .attr('href');
          window.location.href = `${origin}${regionPath}`;
        });

      /* Check query strings when toolbar is loaded. */
      $(document)
        .once()
        .on('toolbar.loaded', () => {
          /* Hide Layout Mode tray on initial page load. */
          $(toolbarModel.set('activeTab', null));
          $viewModeButton.attr('data-toolbar-mode-active', 'true');

          /* If editing a region, show the tray. */
          if (isEditingRegion) {
            $(toolbarModel.set('activeTab', $lbModeButton));
            $lbModeButton.attr(
              'data-toolbar-tray',
              'toolbar-item-lb-mode-tray',
            );

            if (!$('#edit-actions #edit-revert').length) {
              $('.toolbar-lb-revert').hide();
            }

            /* add overlay to the entire page except for the region being edited. */
            $('html').addClass('region-mode-overlay');
          } else {
            $lbModeButton.removeAttr('data-toolbar-mode-active');
            $lbModeButton.removeAttr('data-toolbar-tray');
          }

          /* Add data attribute to button when in Layout Mode. */
          if (isLayoutMode) {
            $('html').addClass('region-mode-overlay');
            $('.toolbar-icon-edit')
              .parent()
              .attr('hidden', true);

            $viewModeButton.removeAttr('data-toolbar-mode-active');
            $lbModeButton.attr('data-toolbar-mode-active', 'true');
          }
        })
        .trigger('toolbar.loaded');
    },
  };
})(jQuery, Drupal);
