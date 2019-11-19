(function(club, $, undefined) {
    
    club.Fencers = function(qs) {

        if (qs) {

            $.ajax({
                url: 'main/c.php?m=club&id=isCountry&cID=' + qs,
            }).done(function(cData) {
                
                var countryCol = cData == 1 ? false : true;

                $('.index1').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');

                var tableTitle = 'Search Results';

                var tableHtml = '';
                var columns = ['ID', 'Firstname', 'Surname', 'YoB', 'Country', 'Active', 'First Comp', 'Last Comp', '# Comps'];
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
        }
    }

}(window.club = window.club || {}, jQuery));