/* global a, b */
var app = angular.module("app", []);
app.controller('datacontroller', function ($scope, $timeout, $http) {
  $http.get('/ctd2-json').then(function (result) {
    $scope.ctd2nodes = result.data;
    const assayList = [];
    var row_count = 0;
    angular.forEach($scope.ctd2nodes, function (nodes, key) {
      angular.forEach(nodes, function (node, key) {
        angular.forEach(node.node.row, function (row, key) {
          if (row.project_title !== null) {
            row_count++;
          }
          angular.forEach(row.assay_type, function (assay_type, key) {
            assayList.push({assay: assay_type.name});
          });
        });
        
      });
    });
    $scope.row_count = row_count;
    assays = assayList.reduce(function (tally, method) {
      tally[method.assay] = (tally[method.assay] || 0) + 1;
      return tally;
    }, {});

    var assaysObj = [];
    angular.forEach(assays, function (number, key) {
      assaysObj.push({assay: key, number: number, node: {}});
    });

    assaysObj = assaysObj.sort(function (a, b) {a.assay.localeCompare(b.assay);});

    angular.forEach(assaysObj, function (assays, assayObjKey) {
      var count = 0;
      angular.forEach($scope.ctd2nodes.nodes, function (nodes, nodesKey) {
        angular.forEach(nodes.node.row, function (row, rowKey) {
          angular.forEach(row.assay_type, function (assay_type, assayKey) {
            if (assays.assay === assay_type.name) {
              var dppCount = 0;
              var dataCount = 0;
              var investigatorCount = 0;
              var contactCount = 0;
              var paperCount = 0;
              if (row.dpp) {
                var dppTitle = row.dpp.title;
                var dppBody = row.dpp.body;
              } else {
                var dppTitle = '';
                var dppBody = '';
              }
              assaysObj[assayObjKey].node[count] = {
                id: nodes.node.id,
                row_number: row.row_number,
                project_title: row.project_title.title,
                project_title_url: row.project_title.url,
                dpp_title: dppTitle,
                dpp_body: dppBody,
                dpp: {},
                data: {},
                investigator: {},
                contact: {},
                submission_date: row.submission_date,
                paper: {}
              };
              if (row.dpp) {
                angular.forEach(row.dpp, function (dppRow, dppKey) {
                  assaysObj[assayObjKey].node[count].dpp[dppCount] = {dpp_title: dppRow.dpp_title, dpp_body: dppRow.dpp_body};
                  dppCount++;
                });
              }
              ;
              if (typeof row.data[0] !== 'undefined') {
                angular.forEach(row.data, function (dataRow, dataKey) {
                  if (dataRow.data_link !== null) {
                    assaysObj[assayObjKey].node[count].data[dataCount] = {data_title: dataRow.data_link.title, data_url: dataRow.data_link.url};
                    dataCount++;
                  }
                  ;
                });
              }
              ;
              if (typeof row.investigator !== 'undefined') {
                if (typeof row.investigator[0] !== 'undefined') {
                  angular.forEach(row.investigator, function (investigatorRow, investigatorKey) {
                    if (investigatorRow.investigator !== null) {
                      assaysObj[assayObjKey].node[count].investigator[investigatorCount] = {investigator: investigatorRow.investigator};
                      investigatorCount++;
                    }
                    ;
                  });
                }
                ;
              }
              ;
              if (typeof row.contact !== 'undefined') {
                if (typeof row.contact[0] !== 'undefined') {
                  angular.forEach(row.contact, function (contactRow, contactKey) {
                    assaysObj[assayObjKey].node[count].contact[contactCount] = contactRow;
                    //angular.forEach(contactRow, function (contactLinkRow, contactLinkKey) {
                      //if (contactLinkRow.title !== null) {
                        //assaysObj[assayObjKey].node[count].contact[contactCount] = {contact_title: contactLinkRow.title, contact_url: contactLinkRow.url};
                        //contactCount++;
                      //};
                      //console.log(contactLinkRow);
                    //});
                  });
                }
                ;
              }
              ;
              if (typeof row.paper !== 'undefined') {
                if (typeof row.paper[0] !== 'undefined') {
                  angular.forEach(row.paper, function (paperRow, paperKey) {
                    if (paperRow.paper_link !== null) {
                      assaysObj[assayObjKey].node[count].paper[paperCount] = {paper_title: paperRow.paper_link.title, paper_url: paperRow.paper_link.url};
                      paperCount++;
                    }
                    ;
                  });
                }
                ;
              }
              ;
              count++;
            }
            ;
          });
        });
      });
    });
    $scope.methods = assaysObj;
    
    var keepGoing = true;
    var title = $scope.ctd2nodes.nodes[0].node.title.title;
    
    $scope.stop = function(title) {
      keepGoing = false;
      $scope.idSelectedProjectTitle = title;
    };
    
    var timeoutTimer = 3000;
    
    angular.forEach($scope.ctd2nodes.nodes, function (item, idx) {
      if(idx == 0 || item.node.row[0].project_title == null){
        
      } else {
        var landingLoop = $timeout(function () {
          if(keepGoing) {
            $scope.idSelectedCenter = item.node.id;
            $scope.idSelectedProjectTitle = item.node.title.title;
            $scope.idSelectedProject = item.node.row[0].project_title.title;
          };
        }, timeoutTimer);
        timeoutTimer += 3000;
       };
    });
    
    $scope.idSelectedCenter = $scope.ctd2nodes.nodes[0].node.id;
    $scope.setSelectedCenter = function (idSelectedCenter) {
      $scope.idSelectedCenter = idSelectedCenter;
    };
    $scope.idSelectedProject = $scope.ctd2nodes.nodes[0].node.row[0].project_title.title;
    $scope.setSelectedProject = function (idSelectedProject) {
      $scope.idSelectedProject = idSelectedProject;
    };
    $scope.idSelectedAssay = $scope.methods[0].assay;
    $scope.setSelectedAssay = function (idSelectedAssay) {
      $scope.idSelectedAssay = idSelectedAssay;
    };
    $scope.idSelectedAssayProject = $scope.methods[0].node[0].project_title;
    $scope.setSelectedAssayProject = function (idSelectedAssayProject) {
      $scope.idSelectedAssayProject = idSelectedAssayProject;
    };
  });

  $scope.clickChoice = 'centers';
  $scope.revealData = function (value) {
    if (value === 'centers')
      return true;
    else
      return false;
  };
  $scope.filterRow = '';
  $scope.filterId = '';
  $scope.setRow = function (row) {
    $scope.filterRow = row;
  };

  $scope.setMethod = function (id, row) {
    $scope.filterId = id;
    $scope.filterRow = row;
  };
  
  $scope.filterMethodRow = function(items) {
    //console.log(items);
    var result = {};
    if ($scope.filterRow && $scope.filterId) {
      angular.forEach(items, function(value, key) {
        if (value.id  === $scope.filterId && value.row_number === $scope.filterRow) {
          result[key] = value;
        };
      });
    } else {
      result = items;
    };
    //console.log(result);
    return result;
  };
  
  $scope.class = ['highlight'];
  $scope.changeClass = function(){
    if($scope.class.indexOf('highlight') === -1)
      $scope.class.push('highlight');
    else
      $scope.class.pop('highlight');
  };
}).filter('sameRowNumber', function () {
  return function (values, rowNumber) {
    if(!Array.isArray(values)){
      Object.entries(values);
    }
    if (!rowNumber) {
      // initially don't filter
      return values;
    }
    //values = Object.entries(values);
    // filter when we have a selected groupId
    return values.filter(function (value) {
      return value.row_number === rowNumber;
    });
  };
}).filter('crop', ['$sce',function($sce) {
  return function (input, limit, respectWordBoundaries, suffix) {
    if (input === null || input === undefined || limit === null || limit === undefined || limit === '') {
      return input;
    }
    if (angular.isUndefined(respectWordBoundaries)) {
      respectWordBoundaries = true;
    }
    if (angular.isUndefined(suffix)) {
      suffix = ' ';
    }

    if (input.length <= limit) {
      return input;
    }

    limit = limit - suffix.length;

    var trimmedString = input.substr(0, limit);
    if (respectWordBoundaries) {
      return $sce.trustAsHtml(trimmedString.substr(0, Math.min(trimmedString.length, trimmedString.lastIndexOf(" "))) + suffix);
    }
    return $sce.trustAsHtml(trimmedString + suffix);
  };
}]);

jQuery(document).ready(function () {
  //angular.bootstrap(document.getElementById('ng-app'), ['app']);
});