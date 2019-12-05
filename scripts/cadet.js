(function(cadet, $, undefined) {

  cadet.Rankings = function() {

    $('.index').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');
  
        var gender = $('.demographics form select[name="catID"]').val() == 6 ? 'Male' : 'Female';

        var tableTitle = 'British Fencing | ' + gender;
                  
        $.ajax({
            url: 'main/c.php?m=cadet&id=getSeasonSize&' + $('.demographics form').serialize(),
        }).done(function(cols) {
            var tableHtml = '';
            var columns = ['#', 'Pts', 'Dom %', 'Int %', 'Firstname', 'Surname', 'YoB', 'Club'];
            for(var c=0; c < cols; c++) { columns.push(c+1); }
            columns.push('');
  
            columns.forEach(function(column) {
                tableHtml = tableHtml + '<th>' + column + '</th>';
            });
            
            $('.loading').hide();
            
            $('.index table thead').html('<tr>' + tableHtml + '</tr>');
            
            var builtColumns = [];
            $.ajax({
                url: 'main/c.php?m=cadet&id=getSeasonResults&' + $('.demographics form').serialize(),
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
                        if ( data.position <= 15 ) {        
                            $(row.cells[0]).addClass('bg-qualifier')
                        }
                    }
                });
            });
        });
    }

    function constructTable(json) {          

        var columns = [];
  
        columns[0] = { data: "position", responsivePriority: 1 };
        columns[1] = { data: "pts",
                         render: function (data, type, row) {
                            return '<div class="text-right padpos text-danger">'+row.pts.toFixed(1)+'</div>';
                       }, 
                       responsivePriority: 2
                     };
        columns[2] = { data: "domestic",
                         render: function (data, type, row) {
                            return row.domestic == '-' ? '<div class="text-right padpos">-</div>' : '<div class="text-right padpos">' + row.domestic.toFixed(2) + '</div>';
                       }, 
                       responsivePriority: 7 };
        columns[3] = { data: "international",
                         render: function (data, type, row) {
                            return row.international == '-' ? '<div class="text-right padpos">-</div>' : '<div class="text-right padpos">' + row.international.toFixed(2) + '</div>';
                       }, 
                       responsivePriority: 8 };
        columns[4] = { data: "fencerFirstname",
                       render: function (data, type, row) {
                         return '<a href="fencer.php?f=' + row.fencerID+ '">' + data + '</a>';
                       },
                       responsivePriority: 4 };
        columns[5] = { data: "fencerSurname",
                       render: function (data, type, row) {
                          return '<a href="fencer.php?f=' + row.fencerID+ '">' + data + '</a>';
                       },
                       responsivePriority: 3 };
        columns[6] = { data: "yob", responsivePriority: 5 };
        columns[7] = { data: "clubName", responsivePriority: 6 };
        
        var cc = 8;
        var ec = 0;
  
        if (json.data.length > 0) {
            while (eval(json.data[0]['e'+ec+'_points']) !== undefined) {
                var pointsCode = 'var points = \'<span style="color:#555;">\' + row.e'+ec+'_points + \'</span>\';' +
                                'if (row.e'+ec+'_pointsHi === true) {' +
                                '    if (row.e'+ec+'_eventType == \'EFC\') {' +
                                '        if (row.e'+ec+'_points > 0) {' +
                                '            points = \'<span style="color:#f00;font-weight:Bold;">\' + row.e'+ec+'_points + \'</span>\';' +
                                '        } else {' +
                                '            points = \'<span style="color:#f00;">\' + row.e'+ec+'_points + \'</span>\';' +
                                '        }' +
                                '    } else {' +
                                '        if (row.e'+ec+'_points > 0) {' +
                                '            points = \'<span style="font-weight:Bold;">\' + row.e'+ec+'_points + \'</span>\';' +
                                '        } else {' +
                                '            points = \'<span>\' + row.e'+ec+'_points + \'</span>\';' +
                                '        }' +
                                '    }' +
                                '} ';
                var f = new Function('data', 'type', 'row', pointsCode + 'return row.e'+ec+'_place > 0 ? \'<span data-html="true" data-toggle="tooltip" title="\'+row.e'+ec+'_placeSuffix+\' &lt;BR&gt;\'+row.e'+ec+'_eventName+\' &lt;BR&gt;\'+row.e'+ec+'_eventDate+\'">\'+points+\'</span>\' : \'-\';');
                columns[cc++] = { data: "e"+ec+"_points", render: f, responsivePriority: (10000+ec) };
                ec++;
            }
        }
  
        columns[cc] = { data: "blank", responsivePriority: -1 };
        
        return columns;
    }
    
    $('#getRankButton').on('click', function() {
        $('.loading').show();
        $('.index').show();            
        cadet.Rankings();
    });
}(window.cadet = window.cadet || {}, jQuery));
