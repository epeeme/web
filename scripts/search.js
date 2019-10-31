(function(search, $, undefined) {
    
    search.Fencer = function(qs) {

        if (qs) {

            $('.index1').html('<table cellpadding="0" cellspacing="0" border="0" class="display compact nowrap" width="100%"><thead></thead></table>');

            var tableTitle = 'Search Results';

            var tableHtml = '';
            var columns = ['ID', 'Firstname', 'Surname', 'YoB', 'CLub', 'Country', 'BFA'];
            columns.forEach(function(column) {
                tableHtml = tableHtml + '<th>' + column + '</th>';
            });            
            $('.index1 table thead').html('<tr>' + tableHtml + '<th></th></tr>');       
            $('.index1 table').DataTable( {
                    dom: '<"toolbar"flBtipr>',
                    ajax: 'main/c.php?m=search&id=getFencersList&qs=' + qs,
                    columnDefs: [ {
                        className: 'control',
                        orderable: false,
                        targets: -1
                    } ],
                    columns: [
                        { data: "ID" },
                        { data: "fencerFirstname", 
                        render: function (data, type, row) {   
                            return '<a href="fencer.php?f=' + row.ID+ '">' + data + '</a>'
                        }
                        },
                        { data: "fencerSurname", 
                        render: function (data, type, row) {   
                            return '<a href="fencer.php?f=' + row.ID+ '">' + data + '</a>'
                        }
                        },
                        { data: "yob", 
                        render: function (data, type, row) {   
                            return data !== null ? data : '-';
                        }
                        },
                        { data: "clubName", 
                        render: function (data, type, row) {   
                            return data !== null ? data : '-';
                        }
                        },
                        { data: "country", 
                        render: function (data, type, row) {   
                            return data !== null ? '<img style="width:18px; height:18px; margin-right:10px; margin-top:-3px;" src="flags/'+row.country+'.png\">' + data : '-';
                        }
                        },
                        { data: "BFA", 
                        render: function (data, type, row) {   
                            return data > 0 ? data : '-';
                        }
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
        }
    }

}(window.search = window.search || {}, jQuery));