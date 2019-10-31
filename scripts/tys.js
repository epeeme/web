(function(tys, $, undefined) {
    
  tys.Rankings = function() {
  
      $('.index').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');
      $('.index2').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');
  
      var tableTitle = 'TYS | ' + $('.demographics form select[name="sex"]').val() + ' | ' + $('.demographics form select[name="cat"]').val();
      $('input[name="series"]').val('TYS');

      $.ajax({
          url: 'main/c.php?m=tys&id=getSeriesSize&' + $('.demographics form').serialize(),
      }).done(function(cols) {
      
          var tableHtml = '';
          var columns = ['#', 'Points', 'Firstname', 'Surname', 'YoB', 'Country', 'Club'];
          for(var c=0; c < cols; c++) { columns.push(c+1); }
          columns.push('');

          columns.forEach(function(column) {
              tableHtml = tableHtml + '<th>' + column + '</th>';
          });
          
          $('.loading').hide();
          
          $('.index table thead').html('<tr>' + tableHtml + '</tr>');
          
          var builtColumns = [];
          $.ajax({
              url: 'main/c.php?m=tys&id=getSeriesResults&' + $('.demographics form').serialize(),
          }).done(function(response) {
              var builtColumns = constructTable(response);
              $('.index table').DataTable( {
                  dom: '<"toolbar"flBtip>',
                  data: response.data,
                  columnDefs: [ {
                      className: 'control',
                      orderable: false,
                      targets: -1
                  } ],
                  columns: builtColumns,                
                  order: [ 0, "asc" ],
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
                                  messageTop: tableTitle
                              },
                              {
                                  extend: 'copyHtml5',
                                  messageTop: tableTitle
                              }                        
                          ],
                      }
                  ],
                  drawCallback: function (settings) {
                      $('[data-toggle="tooltip"]').tooltip();
                  },
                  createdRow: function( row, data, dataIndex ) {
                      if ( data.position <= 8 ) {        
                          $(row.cells[0]).addClass('bg-qualifier')
                      }
                  }
             });

             var tableHtml = '';
             var columns = ['#', 'Event Name', 'Event Date', 'Number of Entries', 'Winner', ''];
             columns.forEach(function(column) {
                 tableHtml = tableHtml + '<th>' + column + '</th>';
             });            
             $('.index2 table thead').html('<tr>' + tableHtml + '</tr>');       
             $.fn.dataTable.moment( 'Do MMM YYYY');
             $('.index2 table').DataTable( {
                  dom: 't',
                  ajax: 'main/c.php?m=tys&id=getSeriesCompetitions&' + $('.demographics form').serialize(),
                  columnDefs: [ {
                      className: 'control',
                      orderable: false,
                      targets: -1
                  } ],
                  columns: [
                      { data: "eventNum" },
                      { data: "eventName",
                        render: function (data, type, row) {
                          return '<a href="event.php?e=' + row.ID+ '&d=' + row.dateID + '">' + data + '</a>';
                        },
                      },
                      { data: "fullDateReal" },
                      { data: "entries" },
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

      columns[0] = { data: "position", responsivePriority: 1 };
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
                          return row.country !== null ? '<img style="width:18px; height:18px; margin-right:10px; margin-top:-3px;" src="flags/'+row.country+'.png\">' + row.country : row.country;
                        }, 
                        responsivePriority: 7
                   };
      columns[6] = { data: "clubName", responsivePriority: 6 };
      
      var cc = 7;
      var ec = 0;

      if (json.data.length > 0) {
          while (eval(json.data[0]['e'+ec+'_points']) !== undefined) {
              var f = new Function('data', 'type', 'row', 'return row.e'+ec+'_points !== null ? \'<span data-html="true" data-toggle="tooltip" title="\'+row.e'+ec+'_placeSuffix+\' &lt;BR&gt;\'+row.e'+ec+'_eventName+\' &lt;BR&gt;\'+row.e'+ec+'_eventDate+\'">\'+row.e'+ec+'_points+\'</span>\' : \'-\';');
              columns[cc++] = { data: "e"+ec+"_points", render: f, responsivePriority: (10000+ec) };
              ec++;
          }
      }

      columns[cc] = { data: "blank", responsivePriority: -1 };
      
      return columns;
  }

  function getCatID(cat, sex) {
      var catID = null;
      if (sex == 'male') {
          switch (cat) {
              case 'u10' : { catID = 11; break; }
              case 'u12' : { catID = 15; break; }
              case 'u14' : { catID = 17; break; }
              case 'u16' : { catID = 13; break; }
          }
      } else {
          switch (cat) {
              case 'u10' : { catID = 12; break; }
              case 'u12' : { catID = 16; break; }
              case 'u14' : { catID = 18; break; }
              case 'u16' : { catID = 14; break; }
          }
      }
      return catID;
  }

  $('#getRankButton').on('click', function() {
      var validRequest = null;
      if ($('select[name="season"]').val() !== '') {
          $('input[name="seasonStart"]').val($('select[name="season"]').val()+'-01-01');
          $('input[name="seasonEnd"]').val($('select[name="season"]').val()+'-12-31');
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
          tys.Rankings();
      }
  });

}(window.tys = window.tys || {}, jQuery));
