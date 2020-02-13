(function(cadet, $, undefined) {

    cadet.Rankings = function() {
  
        $('.index').html('<table cellpadding="0" cellspacing="0" class="display compact nowrap" width="100%"><thead></thead></table>');

        var gender = $('.demographics form select[name="catID"]').val() == 6 ? 'Male' : 'Female';

        var tableTitle = 'British Fencing | ' + gender;
                
        $.ajax({
            url: 'main/c.php?m=cadet&id=getSeasonSize&' + $('.demographics form').serialize(),
        }).done(function(cols) {
            var tableHtml = '';
            var columns = ['#', 'Pts', 'Dom %', 'Firstname', 'Surname', 'YoB', 'Club'];
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

    cadet.efcHistory = function () {

        $('.index').html('<table cellpadding="0" cellspacing="0" class="display compact nowrap" width="100%"><thead></thead></table>');

        var season = parseInt($('.demographics form select[name="season"]').val());
        var gender = $('.demographics form select[name="catID"]').val() == 6 ? 'Male' : 'Female';
        var country = $('.demographics form select[name="country"]').val();

        var tableTitle = 'EFC Performance History';

        var tableHtml = '<tr><th rowspan=2>Firstname</th><th rowspan=2>Surname</th><th rowspan=2>YoB</th>';
        for(var c = season - 3; c <= season; c++) { 
            tableHtml = tableHtml + '   <th class="rowHide" colspan=2 style="text-align:center;">'+c+'</th>';
        }
        tableHtml = tableHtml + '   <th colspan=2 class="rowHide" style="text-align:center;">Overall</th>';
        tableHtml = tableHtml + '   <th style="display:none;"></th></tr><tr>';
        for(var c = season - 4; c <= season; c++) { 
            tableHtml = tableHtml + '<th style="text-align:center;">#</th>';
            tableHtml = tableHtml + '<th style="text-align:center;">%</th>';
        }
        tableHtml = tableHtml + '<th></th></tr>';
    
        $('.loading').hide();
        
        $('.index table thead').html(tableHtml); 
        $('.index table thead th[colspan]').wrapInner( '<span/>' ).append( '&nbsp;' );
        var builtColumns = [];
        $.ajax({
            url: 'main/c.php?m=cadet&id=efcHistory&' + $('.demographics form').serialize(),
        }).done(function(response) {
            var builtColumns = constructHistoryTable(response, season);
            $('.index table').DataTable( {
                dom: '<"toolbar"flBtip>',
                data: response.data,
                columnDefs: [ {
                    className: 'control',
                    orderable: false,
                    targets: -1
                } ],
                columns: builtColumns,                
                order: [ 12, "asc" ],
                autoWidth: false,
                pageLength: 100,   
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
                    $(".heatmap").hottie({
                        colorArray : [ 
                            "#63BE7B",
                            "#FBE983",
                            "#F8696B"
                        ]
                    });
                },
            });
        });

    }
  
    cadet.efcEventHistory = function () {

        $('.index').html('<table id="efc" cellpadding="0" cellspacing="0" class="display compact nowrap" width="100%"><thead></thead><tbody></tbody><tfoot></tfoot></table>');

        var gender = $('.demographics form select[name="catID"]').val() == 6 ? 'Male' : 'Female';
        var country = $('.demographics form select[name="country"]').val();

        var tableTitle = 'EFC Event History';

        var tableHtml = '<tr><th>Event</th><th>Entries</th><th>Avg Pos.</th><th>Avg % Pos.</th><th><span style="color:#F7F74C;">G</span></th><th><span style="color:#C3C3C3;">S</span></th><th><span style="color:#B58231;">B</span></th><th>L8</th><th>L16</th><th>L32</th><th>L64</th><th>L128</th><th>L256</th><th>L512</th><th></th></tr>';
        $('.index table thead').html(tableHtml); 
        
        tableHtml = '<tr><th>Totals</th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th><th></th></tr>';
        $('.index table tfoot').html(tableHtml); 
        
        $('.index table thead th[colspan]').wrapInner( '<span/>' ).append( '&nbsp;' );
        $('.loading').hide();

        $.ajax({
            url: 'main/c.php?m=cadet&id=efcEventHistory&' + $('.demographics form').serialize(),
        }).done(function(response) {
            $('#efc').DataTable( {
                dom: '<"toolbar"flBtip>',
                data: response.data,
                columnDefs: [ {
                    className: 'control',
                    orderable: false,
                    targets: -1
                } ],
                columns: [
                    { data: "eventName" },
                    { data: "entries" },
                    { data: "position" },
                    { data: null,
                      render: function (data, type, row) {
                        return ((100 / row.entries) * row.position).toFixed(2);
                       } 
                    },
                    { data: "first", className: "heatmap" },
                    { data: "second", className: "heatmap" },
                    { data: "third", className: "heatmap" },
                    { data: "last8", className: "heatmap" },
                    { data: "last16", className: "heatmap" },
                    { data: "last32", className: "heatmap" },
                    { data: "last64", className: "heatmap" },
                    { data: "last128", className: "heatmap" },
                    { data: "last256", className: "heatmap" },
                    { data: "last512", className: "heatmap" },
                    { data: "blank" }
                  ],
                order: [ 3, "asc" ],
                autoWidth: false,
                pageLength: 100,   
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
                    $("tbody .heatmap").hottie({ readValue : 
                                              function(e) { if ($(e)[0].innerText != '0') return $(e)[0].innerText; },
                                           colorArray : 
                                              [  "#8cc6e6", "#FFFFFF" ] });
                },
                footerCallback: function (row, data, start, end, display) {                  
                    var api = this.api();
                    for (var i=4; i <=13; i++) {
                        var columnData = api.column(i).data();                   
                        var theColumnTotal = columnData.reduce(
                            function (a, b) {
                                a = parseFloat(a);
                                b = parseFloat(b);
                                return (a + b);
                            }, 0);
                        $(api.column(i).footer()).html(theColumnTotal);
                    }
                    $(api.column(2).footer()).html(response.ap);
                    $(api.column(3).footer()).html(((100/(response.te/columnData.count()))*response.ap).toFixed(2));                    
                },            
            });
        });

    }
    
    cadet.efcCountryList = function () {
        $.ajax({
            url: 'main/c.php?m=cadet&id=efcCountryList',
        }).done(function(cols) {
            var cty = $('select[name="country"]');
            $(cols).each(function() {
                cty.append($("<option>").attr('value', this.ID).text(this.clubName));
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
        /*columns[3] = { data: "international",
                        render: function (data, type, row) {
                            return row.international == '-' ? '<div class="text-right padpos">-</div>' : '<div class="text-right padpos">' + row.international.toFixed(2) + '</div>';
                        }, 
                        responsivePriority: 8 };*/
        columns[3] = { data: "fencerFirstname",
                        render: function (data, type, row) {
                        return '<a href="fencer.php?f=' + row.fencerID+ '">' + data + '</a>';
                        },
                        responsivePriority: 4 };
        columns[4] = { data: "fencerSurname",
                        render: function (data, type, row) {
                        return '<a href="fencer.php?f=' + row.fencerID+ '">' + data + '</a>';
                        },
                        responsivePriority: 3 };
        columns[5] = { data: "yob", responsivePriority: 5 };
        columns[6] = { data: "clubName", responsivePriority: 999 };
        
        var cc = 7;
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
                columns[cc++] = { data: "e"+ec+"_points", render: f, responsivePriority: (10+ec) };
                ec++;
            }
        }

        columns[cc] = { data: "blank", responsivePriority: -1 };
        
        return columns;
    }
      
    function constructHistoryTable(json, season) {          
  
        var columns = [];

        columns[0] = { data: "fencerFirstname",
                        render: function (data, type, row) {
                            return '<a href="fencer.php?f=' + row.fencerID + '">' + data + '</a>';
                        },
                        responsivePriority: 1 };
        columns[1] = { data: "fencerSurname",
                        render: function (data, type, row) {
                            return '<a href="fencer.php?f=' + row.fencerID + '">' + data + '</a>';
                        },
                        responsivePriority: 1 };
        columns[2] = { data: "yob", responsivePriority: 22 };
        columns[3] = { data: "Season_"+(season - 3)+"_Count", responsivePriority: 99 };
        columns[4] = { data: "Season_"+(season - 3)+"_Total",
                        render: function (data, type, row) {
                            var count = eval('row.Season_' + (season - 3) + '_Count');
                            var total = eval('row.Season_' + (season - 3) + '_Total');
                            if (type == "sort" || type == 'type') return count > 0 ? (total / count).toFixed(2) : '';
                            var euros = eval('row.Season_' + (season - 3) + '_Euros') == 1 ? 'E' : '&nbsp;';
                            var worlds = eval('row.Season_' + (season - 3) + '_Worlds') == 1 ? 'W ' : '&nbsp;';
                            return count > 0 ? (total / count).toFixed(2) + ' <span class="text-primary text-monospace">' + euros + '</span><span class="text-info text-monospace">' + worlds + '</span>' : '';
                        },
                        className: "heatmap",
                        responsivePriority: 98 };
        columns[5] = { data: "Season_"+(season - 2)+"_Count", responsivePriority: 97 };
        columns[6] = { data: "Season_"+(season - 2)+"_Total",
                        render: function (data, type, row) {
                            var count = eval('row.Season_' + (season - 2) + '_Count');
                            var total = eval('row.Season_' + (season - 2) + '_Total');
                            if (type == "sort" || type == 'type') return count > 0 ? (total / count).toFixed(2) : '';
                            var euros = eval('row.Season_' + (season - 2) + '_Euros') == 1 ? 'E' : '&nbsp;';
                            var worlds = eval('row.Season_' + (season - 2) + '_Worlds') == 1 ? 'W ' : '&nbsp;';
                            return count > 0 ? (total / count).toFixed(2) + ' <span class="text-primary text-monospace">' + euros + '</span><span class="text-info text-monospace">' + worlds + '</span>' : '';
                        },
                        className: "heatmap",
                        responsivePriority: 96 };
        columns[7] = { data: "Season_"+(season - 1)+"_Count", responsivePriority: 95 };
        columns[8] = { data: "Season_"+(season - 1)+"_Total",
                        render: function (data, type, row) {
                            var count = eval('row.Season_' + (season - 1) + '_Count');
                            var total = eval('row.Season_' + (season - 1) + '_Total');
                            if (type == "sort" || type == 'type') return count > 0 ? (total / count).toFixed(2) : '';
                            var euros = eval('row.Season_' + (season - 1) + '_Euros') == 1 ? 'E' : '&nbsp;';
                            var worlds = eval('row.Season_' + (season - 1) + '_Worlds') == 1 ? 'W ' : '&nbsp;';
                            return count > 0 ? (total / count).toFixed(2) + ' <span class="text-primary text-monospace">' + euros + '</span><span class="text-info text-monospace">' + worlds + '</span>' : '';
                        },
                        className: "heatmap",
                        responsivePriority: 94 };                
        columns[9] = { data: "Season_"+season+"_Count", responsivePriority: 93 };
        columns[10] = { data: "Season_"+season+"_Total",
                        render: function (data, type, row) {
                            var count = eval('row.Season_' + season + '_Count');
                            var total = eval('row.Season_' + season + '_Total');
                            if (type == "sort" || type == 'type') return count > 0 ? (total / count).toFixed(2) : '';
                            var euros = eval('row.Season_' + season + '_Euros') == 1 ? 'E' : '&nbsp;';
                            var worlds = eval('row.Season_' + season + '_Worlds') == 1 ? 'W ' : '&nbsp;';
                            return count > 0 ? (total / count).toFixed(2) + ' <span class="text-primary text-monospace">' + euros + '</span><span class="text-info text-monospace">' + worlds + '</span>' : '';
                        },
                        className: "heatmap",
                        responsivePriority: 92 };
        columns[11] = { data: "Overall_Count", responsivePriority: 91 };
        columns[12] = { data: "Overall_Total",
                        render: function (data, type, row) {
                            if (type == "sort" || type == 'type') return (row.Overall_Total / row.Overall_Count).toFixed(2);
                            return (row.Overall_Total / row.Overall_Count).toFixed(2) + ' <span class="text-primary text-monospace">&nbsp;&nbsp;</span>';
                        },
                        className: "heatmap",
                        responsivePriority: 90 };
      
        columns[13] = { data: "blank", responsivePriority: -1 };
        
        return columns;
    }
    
    $('#getRankButton').on('click', function() {
        $('.loading').show();
        $('.index').show();            
        cadet.Rankings();
    });

    $('#getHistoryButton').on('click', function() {
        $('.loading').show();
        $('.index').show();            
        cadet.efcHistory();
    });

    $('#getEventHistoryButton').on('click', function() {
        $('.loading').show();
        $('.index').show();            
        cadet.efcEventHistory();
    });

  }(window.cadet = window.cadet || {}, jQuery));
  