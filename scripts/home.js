(function(home, $, undefined) {
    
    home.Intro = function() {
        $.ajax({
            url: 'main/c.php?m=home&id=getIntro',
        }).done(function(data) {
            $('.number-of-fencers').html(data.fencers);
            $('.number-of-results').html(data.results);
        });
    }

    home.Recent = function() {

        $('.index1').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');

        var tableTitle = 'Recent Results';

        var tableHtml = '';
        var columns = ['', '', '', '', '', '', '', '', '', '', '', '', '', '', ''];
        columns.forEach(function(column) {
            tableHtml = tableHtml + '<th>' + column + '</th>';
        });            
        $('.index1 table thead').html('<tr>' + tableHtml + '<th></th></tr>');       

        $.fn.dataTable.moment('DD-MM-YYYY');

        $('.index1 table').DataTable( {
             dom: 't',
             ajax: 'main/c.php?m=home&id=getRecent',
             columns: [
                 { data: "date3", visible: false },
                 { data: "date1", visible: false },
                 { data: "date2",
                   orderable: false,
                 },
                 { data: "eventName",
                   orderable: false,
                   render: function (data, type, row) {
                       return '<a href="event.php?e=' + row.eventID + '&d=' + row.ID + '">' + data + '</a>';
                   }
                },
                 { data: "category1",
                   render: function (data, type, row) {
                       return data !== null ? data : '';
                   },
                   className: "desktop",
                   orderable: false 
                 },
                 { data: "category1age0",
                   render: function (data, type, row) {
                       return data !== null ? '<a href="result.php?r=' + row.category1ID0 + '">' + data + '</a>' : '';
                   },
                   className: "desktop",
                   orderable: false 
                 },
                 { data: "category1age1",
                   render: function (data, type, row) {
                      return data !== null ? '<a href="result.php?r=' + row.category1ID1 + '">' + data + '</a>' : '';
                  },
                  className: "desktop",
                  orderable: false 
                 },
                 { data: "category1age2",
                   render: function (data, type, row) {
                      return data !== null ? '<a href="result.php?r=' + row.category1ID2 + '">' + data + '</a>' : '';
                  },
                  className: "desktop",
                  orderable: false 
                 },
                 { data: "category1age3",
                   render: function (data, type, row) {
                      return data !== null ? '<a href="result.php?r=' + row.category1ID3 + '">' + data + '</a>' : '';
                  },
                  className: "desktop",
                  orderable: false 
                 },
                 { data: "category1age4",
                   render: function (data, type, row) {
                      return data !== null ? '<a href="result.php?r=' + row.category1ID4 + '">' + data + '</a>' : '';
                  },
                  className: "desktop",
                  orderable: false 
                 },
                 { data: "category2",
                   render: function (data, type, row) {
                       return data !== null ? data : '';
                   },
                   className: "desktop",
                   orderable: false 
                 },
                 { data: "category2age0",
                   render: function (data, type, row) {
                      return data !== null ? '<a href="result.php?r=' + row.category2ID0 + '">' + data + '</a>' : '';
                  },
                  className: "desktop",
                  orderable: false 
                 },
                 { data: "category2age1",
                   render: function (data, type, row) {
                      return data !== null ? '<a href="result.php?r=' + row.category2ID1 + '">' + data + '</a>' : '';
                  },
                  className: "desktop",
                  orderable: false 
                 },
                 { data: "category2age2",
                   render: function (data, type, row) {
                      return data !== null ? '<a href="result.php?r=' + row.category2ID2 + '">' + data + '</a>' : '';
                  },
                  className: "desktop",
                  orderable: false 
                 },
                 { data: "category2age3",
                   render: function (data, type, row) {
                      return data !== null ? '<a href="result.php?r=' + row.category2ID3 + '">' + data + '</a>' : '';
                  },
                  className: "desktop",
                  orderable: false 
                 },
                 { data: "category2age4",
                   render: function (data, type, row) {
                      return data !== null ? '<a href="result.php?r=' + row.category2ID4 + '">' + data + '</a>' : '';
                  },
                  className: "desktop",
                  orderable: false 
                 }
             ],
             rowGroup: {
                dataSrc: [ "date3" ]
             },
             responsive: { details: {
                    type: 'none'
                }
             },
             order: [ 1, "desc" ],
             pageLength: 25, 
             autoWidth: false
        });      
    }

    home.Forthcoming = function () {

        $('.index2').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact" width="100%"><thead></thead></table>');

        var tableTitle = 'Event Calendar';

        var tableHtml = '';
        var columns = ['', '', '', '', '', '', '', '', '', ''];
        columns.forEach(function(column) {
            tableHtml = tableHtml + '<th>' + column + '</th>';
        });            
        $('.index2 table thead').html('<tr>' + tableHtml + '</tr>');       

        $.fn.dataTable.moment('DD-MM-YYYY');        
        $('.index2 table').DataTable( {
            dom: 't',
            ajax: 'main/c.php?m=home&id=getForthcoming',
            columns: [
                { data: "date2", visible: false },
                { data: "fullDate", visible: false },
                { data: "date1",
                  orderable: false,
                },
                {
                  data: "blank",                  
                  render: function (data, type, row) {
                    if (row.region == 1) return '<span style="color:#A00;" class="oi oi-map-marker"></span>';
                    else if (row.selected == 1) return '<span style="color:#75d3e7;" class="oi oi-star"></span>';
                    else if (row.tbc == 1) return '<span style="color:#fea526;" class="oi oi-lock-locked"></span>';
                    else if (row.locale == 'international') return '<span style="color:#003399;" class="oi oi-flag"></span>';
                    return data;
                  },
                  orderable: false
                },
                { data: "eventName",
                  orderable: false,
                  render: function (data, type, row) {
                      return '<a target="_blank" href="' + row.infoLink+ '">' + data + '</a>';
                  }                  
                },
                { data: "cat1", orderable: false, className: "desktop" },
                { data: "cat2", orderable: false, className: "desktop" },
                { data: "cat3", orderable: false, className: "desktop" },
                { data: "cat4", orderable: false, className: "desktop" },
                { data: "cat5", orderable: false, className: "desktop" }
            ],
            rowGroup: {
               dataSrc: [ "date2" ]
            },
            responsive: { details: {
                   type: 'none'
               }
            },
            order: [ 1, "asc" ],
            pageLength: 25, 
            autoWidth: false
       });      
    }

}(window.home = window.home || {}, jQuery));