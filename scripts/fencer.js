(function(fencer, $, undefined) {
    
    fencer.Display = function(fencerID) {
    
        $.ajax({
            url: 'main/c.php?m=fencer&id=getFencerProfile&fencerID=' + fencerID,
        }).done(function(fData) {            
            var flag = fData[0].country !== null ? '<img style="margin:10px;" src="flags/'+fData[0].country+'.png">' : '';
            var country = fData[0].country !== null ? fData[0].country : '';
            var region = fData[1].clubName !== null && typeof fData[1].clubName !== 'undefined' ? fData[1].clubName : '';            
            var strapLine = '';
            if (country !== '') strapLine = country;
            if ((region !== '') && (strapLine !== '')) strapLine = strapLine + " - " + region;
            if ((region !== '') && (strapLine === '')) strapLine = region;
            if ((fData[0].yob !== null) && (strapLine !== '')) strapLine = strapLine + " - " + fData[0].yob;
            if ((fData[0].yob !== null) && (strapLine === '')) strapLine = fData[0].yob;

            if (flag !== '') { 
                $('.flag-body').html(flag);
                $('.flag-box ').show();
            }
            
            $('.fencer-header').html(fData[0].fencerFullname + "<br><span style='font-size:87%; color:#777;'>" + strapLine + "</span");
            $(document).prop('title', 'epee.me - Fencer Profile | ' + fData[0].fencerFullname);
            $("meta[name='description']").attr('content', 'Results & ranking data for ' + fData[0].fencerFullname + ' from LPJS, Elite Epee, BYC, EYC, EFC, FIE and other youth, cadet, junior & senior fencing competitions.');

            $.ajax({
                url: 'main/c.php?m=fencer&id=getFencerClub&fencerID=' + fencerID,
            }).done(function(cData) {            
                $('.fencer-banner').append(cData.clubName);
            });

            $.ajax({
                url: 'main/c.php?m=fencer&id=getFencerMedals&fencerID=' + fencerID,
            }).done(function(mData) {
                mData.Gold !== null ? $('.gold').html(mData.Gold) : $('.gold').html('-')
                mData.Silver !== null ? $('.silver').html(mData.Silver) : $('.silver').html('-')
                mData.Bronze !== null ? $('.bronze').html(mData.Bronze) : $('.bronze').html('-')
            });

            $('.loading').hide();
            $('.fencer-profile').show();            
        });

        $.ajax({
            url: 'main/c.php?m=fencer&id=getEventCounts&fencerID=' + fencerID,
        }).done(function(ecdata) {
        
            var tableTitle = '';

            var visibleLPJSCol = ecdata.LPJS > 0 ? true : false;
            var visibleEliteCol = ecdata.Elite > 0 ? true : false;
            var visibleENGCol = ecdata.ENG > 0 ? true : false;
            var visibleGBRCol = ecdata.GBR > 0 ? true : false;

            $('.index1').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact" width="100%"><thead></thead></table>');
        
            var tableHtml = '';
            var columns = ['Year', 'Date', 'Event', 'Age', 'Sex', 'Place', 'Entries', 'LPJS', 'Elite', '<img style="width:18px; height:18px; margin-right:2px; margin-top:-3px;" src="flags/ENG.png\"> ENG', '<img style="width:18px; height:18px; margin-right:2px; margin-top:-3px;" src="flags/GBR.png\"> GBR', 'NIF', '<img style="width:18px; height:18px; margin-right:2px; margin-top:-3px;" src="flags/GBR.png\"> Rank', '&#177; Chg'];
            columns.forEach(function(column) {
                tableHtml = tableHtml + '<th>' + column + '</th>';
            });            
            $('.index1 table thead').html('<tr>' + tableHtml + '<th></th></tr>');

            $.fn.dataTable.moment('DD-MM-YYYY');

            $('.index1 table').DataTable( {
                dom: '<"toolbar"flBtipr>',
                ajax: 'main/c.php?m=fencer&id=getHistory&fencerID=' + fencerID,
                lengthMenu: [[8, 16, 32, 64, 128, -1], [8, 16, 32, 64, 128, "All"]], 
                columnDefs: [ {
                    orderable: false,
                    className: 'control', 
                    targets: -1
                }],
                columns: [
                    { data: "year", visible: false },
                    { data: "fDate",
                        render: function (data, type, row) {
                                if (type == 'display')
                                    return data.substring(0, 5)+"<span class='hideYear'>"+data.substring(5, 10)+"</span>";
                                else
                                    return data;
                            },                      
                        responsivePriority: 3
                    },
                    { data: "eventName",
                        render: function (data, type, row) {
                                if ((row.eventType == 'LPJS') || (row.eventType == 'EFC') || (row.eventType == 'BRC'))
                                    return '<a href="event.php?d=' + row.dateID +'&e=' + row.eventID + '">' + row.eventType + " " + data + '</a>';
                                else if ((row.eventType == 'BCC') || (row.eventType == 'BJC') || (row.eventType == 'BYC'))
                                    return '<a href="event.php?d=' + row.dateID +'&e=' + row.eventID + '"><img class="eventLogo" src="img/bf20.png">' + data + '</a>';
                                else if (row.eventType == 'EYC')
                                    return '<a href="event.php?d=' + row.dateID +'&e=' + row.eventID + '"><img class="eventLogo" src="img/ef20.png">' + data + '</a>';
                                else
                                    return '<a href="event.php?d=' + row.dateID +'&e=' + row.eventID + '">' + data + '</a>';
                                },
                        responsivePriority: 1
                    },
                    { data: "age",               
                        responsivePriority: 4 
                    },
                    { data: "sex",                      
                        className: "not-mobile",
                        responsivePriority: 6
                    },
                    { data: "eventPosition",
                        render: function (data, type, row) {
                                if(type == 'display')
                                    return '<a href="result.php?r=' + row.eventDataID + '">' + row.eventPositionDisplay + '</a>';
                                else
                                    return data;
                            },                            
                        responsivePriority: 2
                    },
                    { data: "entries",                      
                    className: "not-mobile",
                    responsivePriority: 5 
                    },
                    { data: "lpjsPoints",
                        render: function (data, type, row) {
                            var mf = row.sex == 'Girls' || row.sex == 'Womens' ? 'female' : 'male';
                            return row.eventType == 'LPJS' ? '<a href="lpjs.php?y=' + row.year + '&a=' + row.age.toLowerCase() + '&s=' + mf + '">' + data + '</a>' : '-';
                        },
                        className: "desktop",
                        visible: visibleLPJSCol,
                        responsivePriority: 90 
                    },
                    { data: "elitePoints",
                        render: function (data, type, row) {   
                            if (row.year >= 2017) {
                                var mf = row.sex == 'Girls' || row.sex == 'Womens' ? 'female' : 'male';
                                return row.eventType == 'Elite' ? '<a href="elite.php?y=' + row.year + '&a=' + row.age.toLowerCase() + '&s=' + mf + '">' + data + '</a>' : '-';
                            } else {
                                return row.eventType == 'Elite' ? data : '-';
                            }
                        },
                        className: "desktop",
                        visible: visibleEliteCol,
                        responsivePriority: 91
                    },
                    { data: "RankingPoints3",                  
                        render: function (data, type, row) {
                            if (row.year < 2019) data = row.RankingPoints5;
                            if ($.inArray(row.catID, ["15", "3", "17", "5", "13", "6", "16", "4", "18", "1", "14", "2"]) > -1 && row.eventType != 'EFC') {
                                return row.NIFFvalue > 0 ? data : '-';
                            } else {
                                return '-';
                            }
                        },
                        className: "desktop",
                        visible: visibleENGCol,
                        responsivePriority: 92
                    },
                    { data: "RankingPoints1",
                        render: function (data, type, row) {
                            if ($.inArray(row.catID, ["25", "26"]) > -1) {
                                if (row.NIFFvalue > 0 ) {
                                    if (row.year <= 2013) 
                                        return row.RankingPoints2;
                                    else
                                        return data;
                                } else {
                                    return '-';
                                }
                            } else {
                                return '-';
                            }
                        },
                        className: "desktop",
                        visible: visibleGBRCol,
                        responsivePriority: 93
                    },
                    { data: "NIFFvalue",
                        render: function (data, type, row) {
                            return data > 0 ? data : '-';
                        },
                        className: "desktop",
                    },
                    { data: "Rank2",
                        render: function (data, type, row) {
                            return data > 0 ? row.Rank2Display : '-';
                        },
                        className: "desktop",
                        visible: visibleGBRCol
                    },
                    { data: "Rank1",
                        render: function (data, type, row) {
                            var change = row.Rank1 - row.Rank2;
                            var cc = null;
                            if (change < 0) cc = '#f00'; 
                            if (change == 0) cc = '#aaa';
                            if (change > 0) cc= '#009933';
                            return data > 0 ? '<span style="color:'+cc+';">'+change+'</span>' : '-';
                        },
                        visible: false
                    },
                    { data: "blank" }
                ],
                rowGroup: {
                    dataSrc: [ "year" ]
                },
                order: [[0, 'desc']],
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
                                messageTop: tableTitle,
                                exportOptions: {
                                    columns: [ 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 12 ]
                                }
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
                    var api = this.api();
                    $('.index1 table').on('search.dt', function () {
                        var currentSort = api.order();
                        if (currentSort[0][0] > 1) {
                            api.rowGroup().disable();
                            $('.hideYear').show();
                        } else {
                            api.rowGroup().enable();
                            $('.hideYear').hide();
                        }
                    });
                    var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
                    pagination.toggle(this.api().page.info().pages > 1);                
                }
            });                   
        });                   

        $.ajax({
            url: 'main/c.php?m=fencer&id=getFinishingPositions&fencerID=' + fencerID,
        }).done(function(eventData) {

            $('.fencer-graphs').show();
        
            am4core.useTheme(am4themes_animated);
        
            var chart = am4core.create("chartdiv1", am4charts.XYChart);
        
            chart.data = eventData;
            chart.responsive.enabled = true;
            chart.tapToActivate = true;
        
            var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
            categoryAxis.dataFields.category = "Position";
            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.renderer.minGridDistance = 30;
        
            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
        
            var series = chart.series.push(new am4charts.ColumnSeries());
            series.dataFields.valueY = "PC";
            series.dataFields.categoryX = "Position";
            series.tooltipText = "{categoryX}: [bold]{valueY}[/]";
                    
            chart.cursor = new am4charts.XYCursor();
        });

        $.ajax({
            url: 'main/c.php?m=fencer&id=getNumberOfComps&fencerID=' + fencerID,
        }).done(function(eventData) {
        
            am4core.useTheme(am4themes_animated);
        
            var chart = am4core.create("chartdiv2", am4charts.XYChart);
        
            chart.data = eventData;
            chart.responsive.enabled = true;
            chart.tapToActivate = true;
        
            var categoryAxis = chart.xAxes.push(new am4charts.CategoryAxis());
            categoryAxis.dataFields.category = "yearShort";
            categoryAxis.renderer.grid.template.location = 0;
            categoryAxis.renderer.minGridDistance = 30;
        
            var valueAxis = chart.yAxes.push(new am4charts.ValueAxis());
        
            var series = chart.series.push(new am4charts.ColumnSeries());
            series.dataFields.valueY = "cCount";
            series.dataFields.categoryX = "yearShort";
            series.tooltipText = "20{categoryX}: [bold]{valueY}[/] competitions";
                    
            chart.cursor = new am4charts.XYCursor();
        });

    }

}(window.fencer = window.fencer || {}, jQuery));