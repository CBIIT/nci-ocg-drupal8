/**
 * @file
 * Hierarchy Manager jsTree JavaScript file.
 */

// Codes run both on normal page loads and when data is loaded by AJAX (or BigPipe!)
// @See https://www.drupal.org/docs/8/api/javascript-api/javascript-api-overview
(function($, Drupal) {
  Drupal.behaviors.hmJSTree = {
    attach: function(context, settings) {
      $(".hm-jstree", context)
        .once("jstreeBehavior")
        .each(function() {
          const treeContainer = $(this);
          const parentID = treeContainer.attr('parent-id');
          const searchTextID = (parentID) ? '#hm-jstree-search-' + parentID : '#hm-jstree-search';
          const optionsJson = treeContainer.attr("options");
          const dataURL = treeContainer.attr('data-source') + '&parent=0';
          const updateURL = treeContainer.attr('url-update');
          const newWindow = false;
          let $popDialog = [];
          let reload = true;
          let rollback = false;
          let themes = {
              dots: false,
              name: 'default'
          };
          let options;
          var offset = 0;
          
          if (optionsJson) {
            options = JSON.parse(optionsJson);
            if (options.theme) {
              themes = options.theme;
            }
          }
          // Ajax callback to refresh the tree.
          if (reload) {
            // Build the tree.
            treeContainer.jstree({
              core: {
                data: {
                  url: function(node) {
                    return node.id === '#' ?
                        dataURL :
                        dataURL;
                  },
                  data: function(node) {
                    return node;
                  }
                },
                themes: themes,
                "check_callback" : true,
                "multiple": false,
              },
              search: {
                show_only_matches: true
              },
              plugins: ["search", "dnd"]
            });
            
           // Node move event.
            treeContainer.on("move_node.jstree", function(event, data) {
              const thisTree = data.instance;
              const movedNode = data.node;
              
              if (!rollback) {
                let list = thisTree.get_node(data.parent).children;
                let before = '';
                let after = '';
                if (data.position > 0) {
                  before = list[data.position - 1];
                }
                
                if (data.position < list.length - 1) {
                  after = list[data.position + 1];
                }

                let parent = data.parent === '#' ? 0 : data.parent;
                // Update the data on server side.
                $.post(updateURL, {
                  keys: [movedNode.id],
                  target: data.position,
                  parent: parent,
                  after: after,
                  before: before
                })
                  .done(function(response) {
                    if (response.result !== "success") {
                      alert("Server error:" + response.result);
                      rollback = true;
                      thisTree.move_node(movedNode, data.old_parent, data.old_position);
                    }
                  })
                  .fail(function() {
                    alert("Error: Can't connect to the server.");
                    rollback = true;
                    thisTree.move_node(movedNode, data.old_parent, data.old_position);
                  }); 
              }
              else {
                rollback = false;
              }
            });
            
         // Node selected event.
            treeContainer.on("select_node.jstree", function(event, data) {
              var href = data.node.a_attr.href;
              if (newWindow) {
                window.open(href, "_self");
              }
              else {
                Drupal.ajax({
                  url: href,
                  success: function(response) {
                    response.forEach(function(element) {
                      if (element.command && element.data) {
                        if (element.command === 'insert' && element.selector === null) {
                          $popDialog[offset] = $('<div>' + element.data + '</div>').appendTo('body');
                        }
                      }
                    });
                    
                    if ($popDialog[offset]) {
                      let margin = parseInt(offset * 10 % 40);
                      let options = {
                          title: 'Edit ' + data.node.text,
                          minWidth: 600,
                          draggable: true,
                          resizable: true,
                          autoResize: false,
                          position: {'my': 'right bottom', 'at':'right-' + margin + ' bottom-' + margin},
                      };
                      Drupal.dialog($popDialog[offset++], options).show(); 
                    }
                  }
                }).execute(); 
              }   
            });

            // Search filter box.
            let to = false;
            $(searchTextID).keyup(function() {
              const searchInput = $(this);
              if (to) {
                clearTimeout(to);
              }
              to = setTimeout(function() {
                const v = searchInput.val();
                treeContainer.jstree(true).search(v);
              }, 250);
            });
          }
        });
    }
  };
})(jQuery, Drupal);
