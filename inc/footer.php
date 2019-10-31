<footer class="text-muted">
        <div class="container">
            <a id="back-to-top" href="#" class="btn btn-primary btn-lg back-to-top" role="button"><i class="fas fa-chevron-up"></i></a>
            <p>This web site is not affiliated in any way with British Fencing, England Fencing or any other official body. Mistakes can be made when processing data (yes, I have a <a href="#privacyModal" data-toggle="modal" data-target="#privacyModal">privacy policy</a>), so please do contact me with your corrections, questions &amp; suggestions - <a href="mailto:dan@epee.me">dan@epee.me</a>.</p>
            <p class="text-center"><img style="padding:32px 0px;" alt="Phone, Tablet &amp; PC" src="img/devices.png"></p>
        </div>
        <div class="modal fade" id="privacyModal" tabindex="-1" role="dialog" aria-labelledby="privacyModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Privacy Policy & GDPR</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">                    
                        <P>After consultation with the ICO, this site falls into the legitimate interest category so I don't need to ask for consent to collect, store, use, disclose, destroy or otherwise 'process' your personal information.                    
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.6/js/dataTables.buttons.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.html5.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/1.5.6/js/buttons.colVis.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.8.4/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.19/sorting/datetime-moment.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/js/bootstrap.bundle.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/rowgroup/1.1.1/js/dataTables.rowGroup.min.js"></script>
    <script type="text/javascript" src="https://www.amcharts.com/lib/4/core.js"></script>
    <script type="text/javascript" src="https://www.amcharts.com/lib/4/charts.js"></script>
    <script type="text/javascript" src="https://www.amcharts.com/lib/4/themes/material.js"></script>
    <script type="text/javascript" src="https://www.amcharts.com/lib/4/themes/animated.js"></script>

    <script src="scripts/common.js"></script>
    <script src="scripts/search.js"></script>
    <script src="scripts//holder.min.js"></script>

    <script src="scripts/typeahead.bundle.js"></script>

    <script>
    var fencers = new Bloodhound({    
        datumTokenizer: Bloodhound.tokenizers.whitespace, 
        queryTokenizer: Bloodhound.tokenizers.whitespace,    
        remote: {
            url: 'main/c.php?m=search&id=getFencers&qs=%F',
            wildcard: '%F'
        }
    });

    $(function() {
        $('.typeahead').typeahead({
            hint: false,
            highlight: true,
            autoselect: true,
            minLength: 2
        }, {
            name: 'fencers',
            display: 'value',
            limit: 5,
            source: fencers,
            templates:  {
                suggestion: function(data) {        
                    return '<div class="sr_data">' +
                                '<div class="sr_name ">' + data.value + '</div><div class="sr_yob">' + data.yob + '</div>' +
                                '<div class="sr_club">' + data.club + '</div><div class="sr_flag"><img src="flags/' + data.cty + '.png"></div>' +
                            '</div>';
                }
            }
        });
    });

    $('.typeahead').bind('typeahead:select', function(ev, suggestion) {
        $('#f').val(suggestion['id']); 
    });

    $('.typeahead').bind('typeahead:render', function(fc, suggestion) {
        $('#searchForm').parent().find('.tt-selectable:first').addClass('tt-cursor');
    });

    $('.searchButton').on('click', function() {
        if ($('input[name="f"]').val() == '') {            
            if ($('input[name="fencerName"]').val().length < 4) {
                alert('Please enter a search of at least 4 characters.')
            } else {
                window.location = 'search.php?qs=' + $('input[name="fencerName"]').val();
            }
            return false;
        }
    });
    </script>
	<!-- Global site tag (gtag.js) - Google Analytics -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=UA-69149880-1"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());
	  gtag('config', 'UA-69149880-1');
    </script>
