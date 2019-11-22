(function(club, $, undefined) {
    
    club.Fencers = function(qs) {

        if (qs) {

            $.ajax({
                url: 'main/c.php?m=club&id=isCountry&cID=' + qs,
            }).done(function(cData) {
                
                var countryCol = cData == 1 ? false : true;

                $.ajax({
                    url: 'main/c.php?m=club&id=getClubData&cID=' + qs,
                }).done(function(clubIntro) {

                    $('.club-header').append(clubIntro.clubName);

                    $('.index1').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');

                    var tableTitle = 'Search Results';

                    var tableHtml = '';
                    var columns = ['ID', 'Firstname', 'Surname', 'YoB', 'Country', 'Active', 'First Comp', 'Last Comp', '# Comps', '<span style="color:#F7F74C;">G</span>', '<span style="color:#C3C3C3;">S</span>', '<span style="color:#B58231;">B</span>'];
                    columns.forEach(function(column) {
                        tableHtml = tableHtml + '<th>' + column + '</th>';
                    });            
                    $('.index1 table thead').html('<tr>' + tableHtml + '<th></th></tr>');       

                    $.fn.dataTable.moment('DD-MM-YYYY');

                    $('.index1 table').DataTable( {
                            dom: '<"toolbar"flBtipr>',
                            ajax: 'main/c.php?m=club&id=getClubFencers&cID=' + qs,
                            columnDefs: [ {
                                className: 'control',
                                orderable: false,
                                targets: -1
                            } ],
                            columns: [
                                { data: "fencerID" },
                                { data: "fencerFirstname", 
                                    render: function (data, type, row) {   
                                        return '<a href="fencer.php?f=' + row.fencerID+ '">' + data + '</a>'
                                    }
                                },
                                { data: "fencerSurname", 
                                    render: function (data, type, row) {   
                                        return '<a href="fencer.php?f=' + row.fencerID+ '">' + data + '</a>'
                                    }
                                },
                                { data: "yob" },
                                { data: "country",
                                    render: function (data, type, row) {
                                        return row.country !== null ? '<img style="width:18px; height:18px; margin-right:10px; margin-top:-3px;" src="flags/'+row.country+'.png\">' + row.country : '<span style="visibility:hidden;">~</span>';
                                    },
                                    visible: countryCol
                                },  
                                { data: "Active",
                                    render: function (data, type, row) {
                                        return data !== false ? 'Yes' : '<span style="color:#aaa;">No</span>';
                                    },
                                },
                                { data: "FirstRep",
                                    render: function (data, type, row) {
                                        return row.Active !== false ? data : '<span style="color:#aaa;">' + data + '</span>';
                                    },
                                },
                                { data: "LastRep",
                                    render: function (data, type, row) {
                                        return row.Active !== false ? data : '<span style="color:#aaa;">' + data + '</span>';
                                    },
                                },
                                { data: "TimesRep",
                                    render: function (data, type, row) {
                                        return row.Active !== false ? data : '<span style="color:#aaa;">' + data + '</span>';
                                    },
                                },
                                { data: "Gold" },
                                { data: "Silver" },
                                { data: "Bronze" },
                                { data: "blank" }
                            ],
                            order: [ 2, "asc" ],
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
                                var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
                                pagination.toggle(this.api().page.info().pages > 1);                
                            }
                    });      
                });
            });

            $.ajax({
                url: 'main/c.php?m=club&id=getNumberOfComps&cID=' + qs,
            }).done(function(eventData) {
            
                $('.fencer-graphs').show();

                am4core.useTheme(am4themes_animated);
            
                var chart = am4core.create("chartdiv1", am4charts.XYChart);
            
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
                series.tooltipText = "20{categoryX}: [bold]{valueY}[/] competition entries";
                        
                var lineSeries = chart.series.push(new am4charts.LineSeries());
                lineSeries.name = "Fencers";
                lineSeries.dataFields.valueY = "cCount2";
                lineSeries.dataFields.categoryX = "yearShort";
                
                lineSeries.stroke = am4core.color("#3874ff");
                lineSeries.strokeWidth = 3;
                lineSeries.propertyFields.strokeDasharray = "lineDash";
                lineSeries.tooltip.label.textAlign = "middle";
                
                var bullet = lineSeries.bullets.push(new am4charts.Bullet());
                bullet.fill = am4core.color("#3874ff"); 
                bullet.tooltipText = "20{categoryX}: [bold]{valueY}[/] active fencers";
                var circle = bullet.createChild(am4core.Circle);
                circle.radius = 4;
                circle.fill = am4core.color("#fff");
                circle.strokeWidth = 3;
                
                chart.cursor = new am4charts.XYCursor();
            });
        }
    }

}(window.club = window.club || {}, jQuery));