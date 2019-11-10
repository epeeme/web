(function(result, $, undefined) {
    
    result.Display = function(eventDataID) {
    
        $.ajax({
            url: 'main/c.php?m=result&id=getEventDetails&ID=' + eventDataID,
        }).done(function(eData) {

            var DataColumns = [];
            var colIndex = 0;
     
            var eventCat = eData.catID;
            var eventID = eData.eventID;
            var dateID = eData.dateID;
            var hasNIFFvalue = eData.NIFFvalue;
            var eventType = eData.eventType;

            DataColumns[colIndex++] = { data: "eventPosition",
                                      render: function (data, type, row) {
                                              if(type == 'display')
                                                  return row.eventPositionDisplay;
                                              else
                                                  return data;
                                      },         
                                      responsivePriority: 1 
                                  };

            DataColumns[colIndex++] = { data: "fencerFirstname", 
                                        render: $.fn.dataTable.render.fencerName(),
                                        responsivePriority: 2 
                                      }; 
            DataColumns[colIndex++] = { data: "fencerSurname",
                                        render: $.fn.dataTable.render.fencerName(),
                                        responsivePriority: 3  
                                      };

            // Cadet nominated event
            if ((eData.nominated == 2) || (eData.nominated == 3)) {
                DataColumns[colIndex++] = { data: "cadetRankingPoints", 
                                            render: function (data, type, row) {
                                                       return '<div class="text-left padpos text-danger">'+row.cadetRankingPoints+'</div>';
                                                    }, 
                                            responsivePriority: 4, 
                                            visible: true }; 
            } else  if (eData.nominated == 4) {
                DataColumns[colIndex++] = { data: "juniorRankingPoints", 
                                            render: function (data, type, row) {
                                                       return '<div class="text-left padpos text-danger">'+row.juniorRankingPoints+'</div>';
                                                    }, 
                                            responsivePriority: 4, 
                                            visible: true }; 
            } else {
                DataColumns[colIndex++] = { data: "blank", visible: false }; 
            }

            // LPJS / Elite Epee event
            if (eData.eventType == 'LPJS') {
                DataColumns[colIndex++] = { data: "lpjsPoints", 
                                            render: function (data, type, row) {
                                                        return '<div class="text-left padpos text-danger">'+row.lpjsPoints+'</div>';
                                                    }, 
                                            responsivePriority: 4, 
                                            visible: true }; 
            } else if (eData.eventType == 'Elite') { 
                DataColumns[colIndex++] = { data: "elitePoints", 
                                            render: function (data, type, row) {
                                                        return '<div class="text-left padpos text-danger">'+row.elitePoints+'</div>';
                                                    }, 
                                            responsivePriority: 4, 
                                            visible: true }; 
            } else if ((eData.eventType == 'Open') && (eData.age == 'Snr')) { 
                var openForumla = eData.year >= 2014 ? "RankingPoints1" : "RankingPoints2";
                DataColumns[colIndex++] = { data: openForumla,
                                            render: function (data, type, row) {
                                                        return hasNIFFvalue > 0 ? '<div class="text-left padpos text-danger">'+data+'</div>' : '-';
                                                    }
                                           };
            } else if ((eData.eventType == 'Sat') || (eData.eventType == 'A-Grade')) { 
                var fieForumla = eData.year >= 2014 ? "RankingPoints1" : "RankingPoints2";
                DataColumns[colIndex++] = { data: fieForumla,
                                            render: function (data, type, row) {        
                                                        if (data == null) data = 0;
                                                        return hasNIFFvalue > 0 ? '<div class="text-left padpos text-danger">'+data+'</div>' : '-';
                                                    }
                                          };
            } else {
                DataColumns[colIndex++] = { data: "blank", visible: false }; 
            }

            DataColumns[colIndex++] = { data: "yob", responsivePriority: 5 };
            DataColumns[colIndex++] = { data: "country",
                                        render: function (data, type, row) {
                                                    return row.country !== null ? '<!--<span style="margin-right:10px;" class="flag flag-' + row.country.toLowerCase() + '">//--></span><img style="width:18px; height:18px; margin-right:10px; margin-top:-3px;" src="flags/'+row.country+'.png\">' + row.country : '<span style="visibility:hidden;">~</span>';
                                                }, 
                                        responsivePriority: 99
                                      };
            DataColumns[colIndex++] = { data: "clubName", responsivePriority: 98 };
            
            var rankingForumla = eData.year >= 2019 ? "RankingPoints3" : "RankingPoints5";
            $.inArray(eData.catID, ["15", "3", "17", "5", "13", "6", "16", "4", "18", "1", "14", "2"]) > -1 && eData.eventType != 'EFC' ?
                DataColumns[colIndex++] = { data: rankingForumla,
                                            render: function (data, type, row) {
                                                        return hasNIFFvalue > 0 ? data : '-';
                                                    }
                                        } : 
                DataColumns[colIndex++] = { data: "blank", visible: false }; 
            
            DataColumns[colIndex++] = { data: "blank", responsivePriority: -1 };

            switch (eData.eventType) {
                case 'LPJS' : { 
                    $('.competition-banner').css('background-image', 'url(img/lpbanner.png)');
                    break;
                }
                case 'BCC' :
                case 'BJC' :
                case 'BSC' :
                case 'BYC' :
                case 'BYCQ' :
                case 'BRC' : {
                    $('.competition-banner').css('background-image', 'url(img/bfbanner.png)');
                    break;
                }
                case 'EYC' : { 
                    $('.competition-banner').css('background-image', 'url(img/efbanner.png)');
                    break;
                }
                case 'Elite' : { 
                    $('.competition-banner').css('background-image', 'url(img/elitebanner.png)');
                    break;
                }
                case 'Open' : { 
                    $('.competition-banner').css('background-image', 'url(img/openbanner.png)');
                    break;
                }
                case 'EFC' : { 
                    $('.competition-banner').css('background-image', 'url(img/efcbanner.png)');
                    break;
                }
                case 'Sat' : 
                case 'A-Grade' : 
                case 'JWC' : { 
                    $('.competition-banner').css('background-image', 'url(img/fiebanner.png)');
                    break;
                }
                case 'Other' : { 
                    if (eData.eventName.toLowerCase().indexOf("commonwealth") >= 0) {
                        $('.competition-banner').css('background-image', 'url(img/cwealthbanner.png)');
                        break;    
                    }
                } 
            }
            eventType = (eventType != 'Open' && eventType != 'A-Grade' && eventType != 'Other') ? eventType : '';

            $('.competition-header').html(eventType+" "+eData.eventName+"<br><span style='font-size:85% !important;' class='text-secondary'>"+eData.age+" "+eData.sex+" - Final Results</span>");
            $('.competition-date').html(eData.eventDate);
            $('.competition-history').html("<span class='oi oi-star pr-2'></span> All "+eventType+" "+eData.eventName+" "+eData.age+" "+eData.sex+" Winners");
            $('.competition-overview').html("<span class='oi oi-vertical-align-bottom pr-2'></span> " + eventType+" "+eData.eventName+" Overview");
            $('.competition-winners').html("<span class='oi oi-check pr-2'></span> All "+eventType+" "+eData.eventName+" "+eData.year+" Results");

            $(document).prop('title', 'epee.me - Result | ' + eData.eventName + " " + eData.year + " " + eData.age + " " + eData.sex);

            if (((eData.eventType == 'Open') && (hasNIFFvalue > 0)) || ((hasNIFFvalue > 0) && (eData.year < 2019))) { 
                $('.competition-nif').html(hasNIFFvalue);
                $('.nif-box').show();
            }

            $('.index').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');

            var tableTitle = eventType+" "+eData.eventName+' | ' +eData.age+" "+eData.sex+' | Result';
            
            var tableHtml = '';
            var columns = ['Place', 'Firstname', 'Surname', 'Points', 'Points', 'YoB', 'Country', 'Club', '<img style="width:18px; height:18px; margin-right:2px; margin-top:-3px;" src="flags/ENG.png\"> Points'];

            columns.forEach(function(column) {
                tableHtml = tableHtml + '<th>' + column + '</th>';
            });
            
            $('.index table thead').html('<tr>' + tableHtml + '<th></th></tr>');
            
            $('.index table').DataTable( {
                dom: '<"toolbar"flBtipr>',
                ajax: 'main/c.php?m=result&id=getResult&ID=' + eventDataID,
                lengthMenu: [[8, 16, 32, 64, 128, -1], [8, 16, 32, 64, 128, "All"]], 
                columnDefs: [ {
                    orderable: false,
                    className: 'control', 
                    targets: -1
                }],
                columns: DataColumns,
                order: [ 0, "asc" ],
                pageLength: -1,
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
                    if ($('button').hasClass('buttons-colvisGroup') === false) {
                        $('.dt-buttons').css('width','auto');                        
                    }
                    $('.index2, .index3').show();                     
                    var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
                    pagination.toggle(this.api().page.info().pages > 1);                }
            });

            $('.index2').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');

            var tableHtml2 = '';
            var columns2 = ['Year', 'Firstname', 'Surname', 'Country', 'Club', 'Entries', 'NIF / Multiplier'];
            columns2.forEach(function(column) {
                tableHtml2 = tableHtml2 + '<th>' + column + '</th>';
            });            
            $('.index2 table thead').html('<tr>' + tableHtml2 + '</tr>');       
            $('.index2 table').DataTable( {
                 dom: 't',          
                 ajax: 'main/c.php?m=result&id=getAllPreviousWinners&catID='+eventCat+'&eventID='+eventID,
                 columnDefs: [ {
                     className: 'control',
                     orderable: false,
                     targets: -1
                 } ],
                 columns: [
                     { data: "year" },
                     { data: "fencerFirstname",
                       render: $.fn.dataTable.render.fencerName(),
                     },
                     { data: "fencerSurname",
                       render: $.fn.dataTable.render.fencerName(),
                     },
                     { data: "country",
                       render: function (data, type, row) {
                                   return row.country !== null ? '<img style="width:18px; height:18px; margin-right:10px; margin-top:-3px;" src="flags/'+row.country+'.png\">' + row.country : '<span style="visibility:hidden;">~</span>';
                               }, 
                        responsivePriority: 99
                     },
                     { data: "clubName" },
                     { data: "entries" },
                     { data: "NIFFvalue" },
                     { data: "blank" }
                 ],
                 order: [ 0, "desc" ],
                 autoWidth: false,
                 pageLength: 50,   
                 responsive: {
                     details: {
                         type: 'column',
                         target: -1
                     },
                 },
            });   

            $('.index3').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');

            var tableHtml3 = '';
            var columns3 = ['Age', 'Sex', 'Firstname', 'Surname', 'Country', 'Club', 'Entries', 'NIF / Multiplier'];
            columns3.forEach(function(column) {
                tableHtml3 = tableHtml3 + '<th>' + column + '</th>';
            });            
            $('.index3 table thead').html('<tr>' + tableHtml3 + '</tr>');       
            $('.index3 table').DataTable( {
                 dom: 't',          
                 ajax: 'main/c.php?m=result&id=getCurrentYearWinners&dateID='+dateID+'&eventID='+eventID,
                 columnDefs: [ {
                     className: 'control',
                     orderable: false,
                     targets: -1
                 } ],
                 columns: [
                     { data: "age",
                       responsivePriority: 1
                     },
                     { data: "sex",
                       responsivePriority: 2
                     },
                     { data: "fencerFirstname",
                       render: $.fn.dataTable.render.fencerName(),
                       responsivePriority: 3
                     },
                     { data: "fencerSurname",
                       render: $.fn.dataTable.render.fencerName(),
                       responsivePriority: 4                     
                     },
                     { data: "country",
                       render: function (data, type, row) {
                                   return row.country !== null ? '<img style="width:18px; height:18px; margin-right:10px; margin-top:-3px;" src="flags/'+row.country+'.png\">' + row.country : '<span style="visibility:hidden;">~</span>';
                               }, 
                        responsivePriority: 99
                     },
                     { data: "clubName" },
                     { data: "entries" },
                     { data: "NIFFvalue" },
                     { data: "blank" }
                 ],
                 order: [[1, 'asc']],
                 rowGroup: {
                    dataSrc: "sex"
                 },
                 autoWidth: false,
                 pageLength: 50,   
                 responsive: {
                     details: {
                         type: 'column',
                         target: -1
                     },
                 },
            });   

            $.ajax({
                url: 'main/c.php?m=result&id=getEventEntryHistory&eventID=' + eventID,
            }).done(function(eventData) {
            
                am4core.useTheme(am4themes_animated);
            
                var chart = am4core.create("chartdiv", am4charts.XYChart);
            
                chart.data = eventData;
                chart.responsive.enabled = true;
                chart.tapToActivate = true;
            
                var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
                categoryAxis.dataFields.category = "eventDate";
                categoryAxis.title.text = "Competition Date";
                categoryAxis.renderer.grid.template.location = 0;
                categoryAxis.renderer.minGridDistance = 50;
            
                var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
                valueAxis.title.text = "Number Of Entries";
            
                var series = chart.series.push(new am4charts.ColumnSeries());
                series.dataFields.valueY = "boysEntries";
                series.dataFields.categoryX = "eventDate";
                series.name = "Male";
                series.tooltipText = "{name}: [bold]{valueY}[/]";
                series.stacked = true;
            
                var series2 = chart.series.push(new am4charts.ColumnSeries());
                series2.dataFields.valueY = "girlsEntries";
                series2.dataFields.categoryX = "eventDate";
                series2.name = "Female";
                series2.tooltipText = "{name}: [bold]{valueY}[/]";
                series2.stacked = true;
            
                chart.cursor = new am4charts.XYCursor();
            });

        });
    }
    
    $.fn.dataTable.render.fencerName = function () {
        return function ( data, type, row ) {
            if (row.fencerFirstname.length + row.fencerSurname.length > 22) {
                var f2 = data.split(' ');
                if (f2.length > 1) {
                    if (row.fencerFirstname === data) {
                        data = f2.shift() + ' <span class="d-none d-lg-inline">' + f2.join(' ') + '</span>';
                    } else {
                        var lastNameTemp = f2.pop();
                        data = '<span class="d-none d-lg-inline">' + f2.join(' ') + '</span> ' + lastNameTemp;
                    }                    
                } 
            }
            return '<a href="fencer.php?f=' + row.fencerID+ '">' + data + '</a>';
        }
    };

}(window.result = window.result || {}, jQuery));