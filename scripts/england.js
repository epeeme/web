(function(england, $, undefined) {

  england.Rankings = function() {

      var numberOfQualifiers = 8;
  
      if ($('select[name="season"]').val() <= 2018) {
          if (($('select[name="season"]').val() < 2015) && ($('select[name="cat"]').val() == 'u13')) { 
              numberOfQualifiers = 0;
          } else {
              numberOfQualifiers = $('select[name="cat"]').val() == 'u15' ? 12 : 6;
          }
      }

      $('.index').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');
      $('.index2').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');
  
      var tableTitle = 'England Fencing | ' + $('.demographics form select[name="sex"]').val() + ' | ' + $('.demographics form select[name="cat"]').val();

      $.ajax({
          url: 'main/c.php?m=england&id=getSeriesSize&' + $('.demographics form').serialize(),
      }).done(function(cols) {
                  
          $.ajax({
              url: 'main/c.php?m=england&id=getSeriesResults&' + $('.demographics form').serialize(),
          }).done(function(response) {

              $('.loading').hide();
              
              var tableHtml = '';
              var columns = ['#', 'Points', 'Firstname', 'Surname', 'YoB', 'Country', 'Club'];
              for(var c=0; c < cols; c++) { columns.push(c+1); }
  
              columns.forEach(function(column) {
                  tableHtml = tableHtml + '<th>' + column + '</th>';
              });
  
              tableHtml = tableHtml + '<th>1</th><th>2</th><th>3</th><th>4</th><th>5</th><th></th>';
  
              $('.index table thead').html('<tr>' + tableHtml + '</tr>');

              var builtColumns = constructTable(response);
              var highlightedColumns = 0;
              var sh = '';
              for(j=7;j<(builtColumns.length-6); j++) sh = sh + j+',';
              sh = sh.substring(0, sh.length - 1);
              $('.index table').DataTable( {
                  dom: '<"toolbar"flBtipr>',
                  data: response.data,
                  processing: true,
                  columnDefs: [ {
                      orderable: false,
                      className: 'control', 
                      targets: -1
                  }, {
                      targets: [ 0 ],
                      orderData: [ 0, 5 ]
                  } ],
                  columns: builtColumns,                
                  
                  pageLength: 50,   
                  autoWidth: false,
                  responsive: {
                      details: {
                          type: 'column',
                          target: -1
                      },
                  },
                  language: {
                      searchPlaceholder: "Filter results",
                      emptyTable: "Select from the options above to view rankings",
                      search: "_INPUT_",
                      lengthMenu: "_MENU_ rows",
                      processing: "DataTables is currently busy"
                  },            
                  buttons: [
                      {
                          extend: 'collection',
                          text: 'Export',
                          buttons: [
                              {
                                  extend: 'excelHtml5',
                                  messageTop: tableTitle
                              },
                              {
                                  extend: 'csvHtml5',
                              },
                              {
                                  extend: 'pdfHtml5',
                                  messageTop: tableTitle,
                                  exportOptions: {
                                      columns: [ 0, 1, 2, 3, 4, 5, 6, -6, -5, -4, -3, -2 ]
                                  }
                              },
                              {
                                  extend: 'copyHtml5',
                                  messageTop: tableTitle
                              }                        
                          ],
                      },
                      {
                          extend: 'colvisGroup',
                          text: 'Best 5 Scores',
                          show: [ 0, 1, 2, 3, 4, 5, 6, -2, -3, -4, -5, -6 ],
                          hide: [ sh ]
                      },
                      {
                          extend: 'colvisGroup',
                          text: 'All Scores',
                          show: [ 0, 1, 2, 3, 4, 5, sh ],
                          hide: [ 6, -2, -3, -4, -5, -6 ]
                      },
                  ],
                  drawCallback: function (settings) {
                      $('.index table').on('column-sizing.dt', function () {
                          $('[data-toggle="tooltip"]').tooltip();
                      });                   
                      $('.index table').on('responsive-display.dt',  function () {
                          $('[data-toggle="tooltip"]').tooltip();
                      });                    
                      $('.paginate_button:not(.disabled)', this.api().table().container()).on('click', function() {
                          $('[data-toggle="tooltip"]').tooltip();
                          $('html, body').animate({
                              scrollTop: $(".index table").offset().top - 80
                             }, 'slow');                      
                      });       
                      $('.index table').on('search.dt', function () {
                          $('[data-toggle="tooltip"]').tooltip();
                      });
                      $('.index table').on('order.dt', function () {
                          $('[data-toggle="tooltip"]').tooltip();
                      });

                      var api = this.api();
                      api.rows().every( function (rowIdx, tableLoop, rowLoop) {
                          var data = this.data();
                          if ((highlightedColumns < numberOfQualifiers) && (data.country == 'ENG') && (data.goldenTicket === false)) {
                              highlightedColumns += 1;
                              $(this.row(rowIdx).node().cells[0]).addClass('bg-qualifier');
                          }
                      });

                   },
                  createdRow: function( row, data, dataIndex ) {
                      if ((data.country == 'ENG') && (data.goldenTicket !== false)) {        
                          highlightedColumns += 1;
                          $(row.cells[0]).addClass('bg-golden-ticket');
                      }
                  }
                  
             });
                           
             var tableHtml = '';
             var columns = ['#', 'Event Name', 'Cat', 'Event Date', 'Number of Entries', 'Multiplier', 'Winner'];
             columns.forEach(function(column) {
                 tableHtml = tableHtml + '<th>' + column + '</th>';
             });            
             $('.index2 table thead').html('<tr>' + tableHtml + '</tr>');       
             $.fn.dataTable.moment('Do MMM YYYY');
             $('.index2 table').DataTable( {
                  dom: 't',
                  ajax: 'main/c.php?m=england&id=getSeriesCompetitions&' + $('.demographics form').serialize(),
                  columnDefs: [ {
                      className: 'control',
                      orderable: false,
                      targets: -1
                  } ],
                  columns: [
                      { data: "eventNum" },
                      { data: "eventName", 
                        render: function (data, type, row) {   
                          return '<a href="event.php?d='+row.dateID+'&e='+row.ID+'">'+data+'</a>';
                        }
                      },
                      { data: "catID", 
                        render: function (data, type, row) {  
                            var ageGroup = null;
                            switch (parseInt(row.catID)) {
                                case 6:   case 2: { ageGroup = 'U17'; break; }
                                case 13:  case 14 :  { ageGroup = 'U16'; break; }
                                case 5:   case 1 :  { ageGroup = 'U15'; break; }
                                case 17:  case 18 :  { ageGroup = 'U14'; break; }
                                case 3:   case 4 :  { ageGroup = 'U13'; break; }
                                case 15:  case 16 :  { ageGroup = 'U12'; break; }
                            }
                            return '<a href="result.php?r='+row.eventDataID+'">'+ageGroup+'</a>';
                        }  
                      },
                      { data: "fullDateReal" },
                      { data: "entries" },
                      { data: "NIFFvalue" },
                      { data: "winner",
                        render: function (data, type, row) {
                          return '<a href="fencer.php?f=' + row.fencerID+ '">' + data + '</a>';
                        },
                      },
                      { data: "blank" }
                  ],
                  order: [ 0, "asc" ],
                  pageLength: 50,   
                  autoWidth: false,
                  responsive: {
                      details: {
                          type: 'column',
                          target: -1
                      },
                  },
             });      
          });
      });
  }

  function constructTable(json) {          

      var columns = [];

      columns[0] = { data: "position",   
                      render: function (data, type, row) {
                          if(type == 'display')
                              return row.positionDisplay;
                          else
                              return data;
                      },         
                     responsivePriority: 1 
                   };
      columns[1] = { data: "pts",
                      render: function (data, type, row) {
                          return '<div class="text-right padpos text-danger">'+row.pts+'</div>';
                      }, 
                     responsivePriority: 2
                   };
      columns[2] = { data: "fencerFirstname",
                     render: function (data, type, row) {
                        return '<a href="fencer.php?f=' + row.fencerID+ '">' + data + '</a>';
                     },
                     responsivePriority: 4 };
      columns[3] = { data: "fencerSurname",
                     render: function (data, type, row) {
                        return '<a href="fencer.php?f=' + row.fencerID+ '">' + data + '</a>';
                     },
                     responsivePriority: 3 };
      columns[4] = { data: "yob", responsivePriority: 5 };
      columns[5] = { data: "country",
                      render: function (data, type, row) {
                          return row.country !== null ? '<img style="width:18px; height:18px; margin-right:10px; margin-top:-3px;" src="flags/'+row.country+'.png\">' + row.country : '<span style="visibility:hidden;">~</span>';
                      }, 
                       responsivePriority: 98
                   };
      columns[6] = { data: "clubName", responsivePriority: 99};
      
      var cc = 7;
      var ec = 0;

      if (json.data.length > 0) {
          while (eval(json.data[0]['e'+ec+'_points']) !== undefined) {
              var f = new Function('data', 'type', 'row', 'return row.e'+ec+'_points !== null ? \'<span style="cursor: help;" data-html="true" data-toggle="tooltip" title="\'+row.e'+ec+'_placeSuffix+\' &lt;BR&gt;\'+row.e'+ec+'_eventName+\' &lt;BR&gt;\'+row.e'+ec+'_eventDate+\'">\'+row.e'+ec+'_points+\'</span>\' : \'-\';');
              columns[cc++] = { data: "e"+ec+"_points", render: f, orderSequence: [ "desc", "asc"], responsivePriority: (10000+ec), visible: false };
              ec++;
          }
      }
      
      var j = 0;
      while (j < 5) {
          var f = new Function('data', 'type', 'row', 'return row.e'+j+'_pointsHi != \'-\' ? \'<span style="cursor: help;" data-html="true" data-toggle="tooltip" title="\'+row.e'+j+'_pointsplaceSuffix+\' &lt;BR&gt;\'+row.e'+j+'_pointseventName+\' &lt;BR&gt;\'+row.e'+j+'_pointseventDate+\'">\'+row.e'+j+'_pointsHi+\'</span>\' : \'-\';');
          columns[cc] = { data: "e"+j+"_pointsHi", render: f, orderSequence: [ "desc", "asc"] };
          j++;
          cc++;
      }

      columns[cc] = { data: "blank", responsivePriority: -1 };
      
      return columns;
  }

  function getCatID(cat, sex) {
      var catID = null;
      if (sex == 'male') {
          switch (cat) {
              case 'u13' : { catID = 3; break; }
              case 'u14' : { catID = 17; break; }
              case 'u15' : { catID = 5; break; }
              case 'u17' : { catID = 6; break; }
          }
      } else {
          switch (cat) {
              case 'u13' : { catID = 4; break; }
              case 'u14' : { catID = 18; break; }
              case 'u15' : { catID = 1; break; }
              case 'u17' : { catID = 2; break; }
          }
      }
      return catID;
  }

  $('#getRankButton').on('click', function() {        
      var validRequest = null;        
      if ($('select[name="season"]').val() !== '') {
          if ($('select[name="season"]').val() >= 2017) {
              $('input[name="seasonStart"]').val($('select[name="season"]').val()-1+'-12-01');
              $('input[name="seasonEnd"]').val($('select[name="season"]').val()+'-11-30');    
          } else {
              $('input[name="seasonStart"]').val($('select[name="season"]').val()+'-01-01');
              $('input[name="seasonEnd"]').val($('select[name="season"]').val()+'-12-31');                    
          }
      } else {
          validRequest = false;
      }
      if ($('select[name="cat"]').val() !== '' && $('select[name="sex"]').val() !== '') {
          $('input[name="catID"]').val(getCatID($('select[name="cat"]').val(), $('select[name="sex"]').val()));
          validRequest = validRequest === null ? true : false;
      }
      if (validRequest) {
          $('.loading').show();
          $('.index').show();            
          $('.index2').show();            
          england.Rankings();
      }
  });

  
}(window.england = window.england || {}, jQuery));
