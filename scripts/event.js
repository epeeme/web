(function(event, $, undefined) {
    
    event.Display = function(eventDateID, eventID) {
    
        $.ajax({
            url: 'main/c.php?m=event&id=getEventDetails&dateID=' + eventDateID + '&eventID=' + eventID,
        }).done(function(eData) {

            var eventType = eData.eventType;
            window.eventType = eventType;
            var hasNIFFvalue = eData.NIFFvalue;
            var columns = ['Place', 'Firstname', 'Surname', 'Sex', 'Cat', 'Points', 'Points', 'YoB', 'Country', 'Club', '<img style="width:18px; height:18px; margin-right:2px; margin-top:-3px;" src="flags/ENG.png\"> Points'];
            switch (eData.eventType) {
                case 'LPJS' : { 
                    $('.competition-banner').css('background-image', 'url(img/lpbanner.png)');
                    columns[6] = ['LPJS Points'];
                    break;
                }
                case 'BCC' :
                case 'BSC' :
                case 'BJC' :
                case 'BRC' : {
                    columns[5] = ['Cadet Points'];
                    $('.competition-banner').css('background-image', 'url(img/bfbanner.png)');
                    break;
                }
                case 'BYC' : {
                    columns[9] = ['Region'];
                    $('.competition-banner').css('background-image', 'url(img/bfbanner.png)');
                    break;
                }
                case 'BYCQ' : {
                    $('.competition-banner').css('background-image', 'url(img/bfbanner.png)');
                    break;
                }
                case 'EYC' : { 
                    $('.competition-banner').css('background-image', 'url(img/efbanner.png)');
                    break;
                }
                case 'Elite' : { 
                    $('.competition-banner').css('background-image', 'url(img/elitebanner.png)');
                    columns[6] = ['Elite Points'];
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

            $('.competition-header').html(eventType+" "+eData.eventName+"<br><span style='font-size:85% !important;' class='text-secondary'>Final Results</span>");
            $('.competition-date').html(eData.eventDate);
            $('.competition-history').html("All "+eventType+" "+eData.eventName+" "+eData.age+" "+eData.sex+" Winners");
            $('.competition-overview').html(eventType+" "+eData.eventName+" Overview");
            $('.competition-winners').html("All "+eventType+" "+eData.eventName+" "+eData.year+" Results");

            $(document).prop('title', 'epee.me - All Results | ' + eData.eventName + " " + eData.year);

            var DataColumns = [];
            var colIndex = 0;
        
            var eventID = eData.eventID;

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
                                        render: function (data, type, row) {
                                            return '<a href="fencer.php?f=' + row.fencerID + '">' + data + '</a>';
                                        },                                        
                                        responsivePriority: 2 }; 
            DataColumns[colIndex++] = { data: "fencerSurname", 
                                        render: function (data, type, row) {
                                            return '<a href="fencer.php?f=' + row.fencerID + '">' + data + '</a>';
                                        },                     
                                        responsivePriority: 3  };

            DataColumns[colIndex++] = { data: "sex", visible: false };
            DataColumns[colIndex++] = { data: "age", visible: false };

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
            } else if (eData.eventType == 'Open') { 
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
                                                        return hasNIFFvalue > 0 ? '<div class="text-left padpos text-danger">'+data+'</div>' : '-';
                                                    }
                                            };
            } else {
                DataColumns[colIndex++] = { data: "blank", visible: false }; 
            }

            DataColumns[colIndex++] = { data: "yob", responsivePriority: 5 };
            DataColumns[colIndex++] = { data: "country",
                                        render: function (data, type, row) {
                                                    return row.country !== null ? '<img style="width:18px; height:18px; margin-right:10px; margin-top:-3px;" src="flags/'+row.country+'.png\">' + row.country : '<span style="visibility:hidden;">~</span>';
                                                }, 
                                        responsivePriority: 99
                                        };
            DataColumns[colIndex++] = { data: "clubName", responsivePriority: 98 };
            
            if ($.inArray(eData.eventType, 'LPJS', 'BCC', 'BSC', 'BRC', 'BYC', 'EYC', 'Elite') > -1) {
                var rankingForumla = eData.year >= 2019 ? "RankingPoints3" : "RankingPoints5";            
                DataColumns[colIndex++] = { data: rankingForumla,
                                                render: function (data, type, row) {
                                                        var rp = '-';
                                                        if ($.inArray(row.catID, ["15", "3", "17", "5", "13", "6", "16", "4", "18", "1", "14", "2"]) > -1 && eData.eventType != 'EFC') {
                                                            if (row.NIFF > 0 && row.efr == 0) rp = data
                                                        } 
                                                        return rp;
                                                    }
                                          }    
            } else {
                DataColumns[colIndex++] = { data: "blank", visible: false };
            }
            
            
            
            DataColumns[colIndex++] = { data: "blank", responsivePriority: -1 };

            $('.index1').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');
            var tableTitle = eventType+" "+eData.eventName+' | Result';
    
            var tableHtml = '';

            columns.forEach(function(column) {
                tableHtml = tableHtml + '<th>' + column + '</th>';
            });
            
            $('.index1 table thead').html('<tr>' + tableHtml + '<th></th></tr>');
    
            $('.index1 table').DataTable( {
                dom: '<"toolbar"flBtipr>',
                ajax: 'main/c.php?m=event&id=getResult&dateID=' + eventDateID + '&eventID=' + eventID,
                lengthMenu: [[8, 16, 32, 64, 128, -1], [8, 16, 32, 64, 128, "All"]], 
                columnDefs: [ {
                    orderable: false,
                    className: 'control', 
                    targets: -1
                }],
                columns: DataColumns,
                rowGroup: {
                    dataSrc: [ "sex", "age" ],
                    startRender: function (rows, group, level) {
                                    if (rows.data()[0].NIFF == 1) {                                                                                
                                        return level > 0 ? group +' <span class="groupEntries">(' + rows.count() + ' fencers)</span> - NIF / Multiplier [' + rows.data()[0].NIFFvalue+']' : group;
                                    } else {
                                        return level > 0 ? group +' <span class="groupEntries">(' + rows.count() + ' fencers)</span>' : group;
                                    }                
                                 } 
                },
                order: [[3, 'asc'], [4, 'asc'], [0, 'asc']],
                ordering: false,
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
                    var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');
                    pagination.toggle(this.api().page.info().pages > 1);                
                }
            });                   
        });
    }

}(window.event = window.event || {}, jQuery));