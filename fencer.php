    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">

        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" type="text/css" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/rowgroup/1.1.1/css/rowGroup.dataTables.min.css">
        <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-typeahead/2.10.6/jquery.typeahead.css">
        <link href='https://fonts.googleapis.com/css?family=Syncopate:400,700' rel='stylesheet' type='text/css'>
        <link href="css/common.css" rel="stylesheet">

        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="Results & ranking data from LPJS, Elite Epee, BYC, EYC and other UK youth, cadet & junior fencing competitions">
        <meta name="keywords" content="fencing, fence, fencer, epee, epeeist, results, ranking, leon paul, elite epee, britih fencing, england fencing, britishfencing, database, data, best, top, clubs, cadet, junior, youth">
        <meta name="author" content="Dan Kew">
        <meta name="robots" content="FOLLOW,INDEX">
        <meta name="language" content="english">
        <title></title>
    </head>
    <body>

        <?php include('inc/header.php') ?>

        <main role="main">
        <div class="album py-4 bg-light">
            <div class="container">
                <div class="row no-gutters">
                    <div class="no-gutters col-12 dataTables_length fencer-profile" style="padding-bottom:10px; display:none;">
                        <div class="card flag-box float-left text-center" style="display:none;">
                            <div class="flag-body"></div>
                        </div>
                        <div class="all-medals">
                        <div class="card medal-box float-right text-center">
                            <div class="card-header-bronze">Bronze</div>
                            <div class="card-body-medal bronze"></div>
                        </div>
                        <div class="card medal-box float-right text-center">
                            <div class="card-header-silver">Silver</div>
                            <div class="card-body-medal silver"></div>
                        </div>
                        <div class="card medal-box float-right text-center">
                            <div class="card-header-gold">Gold</div>
                            <div class="card-body-medal gold"></div>
                        </div>
                        </div>
                        <div class="fencer-banner">
                            <h4 class="fencer-header"></h4>
                        </div>
                    </div>
                </div>
                <div class="loading" style="padding:0px 12px;">
                    <div class="d-flex align-items-center"><strong>Loading...</strong><div class="spinner-border ml-auto" role="status" aria-hidden="true"></div></div>
                </div>
                <div class="row no-gutters fencer-graphs" style="display:none;">
                    <div class="col-lg-6">
                        <div class="row no-gutters">
                            <div class="no-gutters col-12 dataTables_length">
                                <div style="border-bottom:2px solid #ddd; margin-bottom:10px; background:#eee; padding:10px 0px 0px 15px;">
                                    <h6 class="fencer-finishing">Finishing Position</h6>
                                </div>
                            </div>
                            <div id="chartdiv1"></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row no-gutters">
                            <div class="no-gutters col-12 dataTables_length">
                                <div style="border-bottom:2px solid #ddd; margin-bottom:10px; background:#eee; padding:10px 0px 0px 15px;">
                                    <h6 class="fencer-finishing">Number Of Competitions</h6>
                                </div>
                            </div>
                            <div id="chartdiv2"></div>
                        </div>
                    </div>
                </div>
                <div class="row no-gutters" style="padding-bottom:10px; padding-top:30px;">
                    <div class="index1 col-sm-12"></div>
                </div>
            </div>
        </div>
        </main>

        <?php include('inc/footer.php') ?>

        <script src="scripts/fencer.js"></script>
        <script>
            $(document).ready(function() {
                fencer.Display(<?php echo htmlspecialchars((int)$_GET['f']); ?>);
            });        
        </script>
    </body>
    </html>

