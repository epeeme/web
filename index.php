<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/1.5.6/css/buttons.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/open-iconic/1.1.1/font/css/open-iconic-bootstrap.css">
    <link href='https://fonts.googleapis.com/css?family=Syncopate:400,700' rel='stylesheet' type='text/css'>
    <meta name="description" content="Results & ranking data from LPJS, Elite Epee, BYC, EYC and other UK youth, cadet & junior fencing competitions">
    <meta name="keywords" content="fencing, fence, fencer, epee, epeeist, results, ranking, leon paul, elite epee, britih fencing, england fencing, britishfencing, database, data, best, top, clubs, cadet, junior, youth">
    <meta name="author" content="Dan Kew">
    <link href="css/common.css" rel="stylesheet">
    <title>epee.me - UK Fencing Results Database</title>
  </head>
  <body>

    <?php include('inc/header.php') ?>

    <main role="main">
      <div class="album py-3 bg-light">
        <div class="container">
            <div class="row no-gutters">
                <div class="no-gutters col-12 dataTables_length">
                    <div class="alert alert-dark text-center mb-lg-0" role="alert" style="padding:2% 8%;">
                        <span class="number-of-fencers font-weight-bold"></span> fencers producing <span class="number-of-results font-weight-bold"></span> individual results from LPJS, Elite Epee, BYC, EYC, EFC, FIE and other UK, European & World fencing competitions (2005 - <?php echo date('Y'); ?>)
                    </div>
                    <div class="row no-gutters bg-white py-lg-3 mb-lg-3">
                        <div class="col-2 offset-1 d-none d-lg-block">
                            <p style="font-size:90% !important; color:#555;"><a href="#resultsLink">Event results</a> are checked daily ensuring your fencing data and statistics are always up to date.</p>
                        </div>
                        <div class="col-2 text-center d-none d-lg-block"><img alt="Ranking Data" src="img/icon1.png"></div>
                        <div class="col-2 d-none d-lg-block">
                            <p style="font-size:90% !important; color:#555;">Up to the minute <a href="#rankingLink">ranking data</a>, tracked and cross referenced against official sources.</p>
                        </div>
                        <div class="col-2 text-center d-none d-lg-block"><img alt="Events Data" src="img/icon2.png"></div>
                        <div class="col-2 d-none d-lg-block">
                            <p style="font-size:90% !important; color:#555;">Plan your year using <a href="#eventsLink">the calendar</a>. All regional & national youth epee events listed.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row no-gutters" id="resultsLink">
                <div class="no-gutters col-12 dataTables_length">
                    <div style="margin-top:10px;" class="subBar">
                        <span class="float-right pr-3" style="margin-top:-2px;"><a class="text-danger" href="/liveResults"><span class="oi oi-warning text-danger"></span> Live Results</a></span>
                        <h6 name="results" class="home-results"><span class="oi oi-list pr-2"></span> Recently Added Results</h6>
                    </div>
                </div>
            </div>
            <div class="row no-gutters">
                <div class="index1 col-sm-12"></div>
            </div>
            <div class="row no-gutters" id="rankingLink">
                <div class="no-gutters col-12 dataTables_length">
                    <div style="margin-top:30px;" class="subBar">
                        <h6 name="results" class="home-rankings"><span class="oi oi-graph pr-2"></span> Current Rankings</h6>
                    </div>
                </div>
            </div>
            <div class="row bg-white no-gutters">
                <div class="col-12 col-lg-3 px-2 px-lg-3 pt-2 pb-4">
                    <div style="height:135px;">
                        <img src="img/england.png" alt="England Fencing">
                        <p>Current (U14 / U17) and historical England (U13 / U15) epee rankings.</p>
                    </div>
                    <table id="england" class="display compact" style="width:100%">
                    <thead><tr><th></th><th></th><th></th></tr></thead>
                    <tbody>
                        <tr>
                          <td>Boys</td>
                          <td><a href="england.php?y=2019&a=u14&s=male">U14</a></td>
                          <td><a href="england.php?y=2019&a=u17&s=male">U17</a></td>
                        </tr>
                        <tr>
                          <td>Girls</td>
                          <td><a href="england.php?y=2019&a=u14&s=female">U14</a></td>
                          <td><a href="england.php?y=2019&a=u17&s=female">U17</a></td>
                        </tr>
                    </tbody>
                    </table>
                </div>
                <div class="col-12 col-lg-3 px-2 px-lg-3 pt-2 pb-4">
                    <div style="height:135px;">
                        <img src="img/eliteEpeeLogo.png" alt="Elite Epee" height="40" class="mt-2 mb-1">
                        <p>Elite Epee Rankings from 2017 through to the current <?php echo date("Y"); ?> season.</p>
                    </div>
                    <table id="elite" class="display compact" style="width:100%">
                    <thead><tr><th></th><th></th><th></th><th></th><th></th><th></th></tr></thead>
                    <tbody>
                        <tr>
                          <td>Boys</td>
                          <td><a href="elite.php?y=2019&a=u10&s=male">U10</td>
                          <td><a href="elite.php?y=2019&a=u12&s=male">U12</td>
                          <td><a href="elite.php?y=2019&a=u14&s=male">U14</td>
                          <td><a href="elite.php?y=2019&a=u17&s=male">U17</td>
                          <td><a href="elite.php?y=2019&a=senior&s=male">Snr</td>
                        </tr>
                        <tr>
                          <td>Girls</td>
                          <td><a href="elite.php?y=2019&a=u10&s=female">U10</td>
                          <td><a href="elite.php?y=2019&a=u12&s=female">U12</td>
                          <td><a href="elite.php?y=2019&a=u14&s=female">U14</td>
                          <td><a href="elite.php?y=2019&a=u17&s=female">U17</td>
                          <td><a href="elite.php?y=2019&a=senior&s=female">Snr</td>
                        </tr>
                        </tbody>
                    </table>
                </div>                  
                <div class="col-12 col-lg-3 px-2 px-lg-3 pt-2 pb-4">
                    <div style="height:135px;">
                        <img src="img/lpjs.png" alt="Leon Paul Junior Series" height="45" class="mt-2">
                        <p>Leon Paul Junior Series rankings from 2008 to the current <?php echo date("Y"); ?> season.</p>
                    </div>
                    <table id="lpjs" class="display compact" style="width:100%">
                    <thead><tr><th></th><th></th><th></th><th></th><th></th></tr></thead>
                    <tbody>
                        <tr>
                          <td>Boys</td>
                          <td><a href="lpjs.php?y=2019&a=u9&s=male">U9</a></td>
                          <td><a href="lpjs.php?y=2019&a=u11&s=male">U11</a></td>
                          <td><a href="lpjs.php?y=2019&a=u13&s=male">U13</a></td>
                          <td><a href="lpjs.php?y=2019&a=u15&s=male">U15</a></td>
                        </tr>
                        <tr>
                          <td>Girls</td>
                          <td><a href="lpjs.php?y=2019&a=u9&s=female">U9</a></td>
                          <td><a href="lpjs.php?y=2019&a=u11&s=female">U11</a></td>
                          <td><a href="lpjs.php?y=2019&a=u13&s=female">U13</a></td>
                          <td><a href="lpjs.php?y=2019&a=u15&s=female">U15</a></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-12 col-lg-3 px-2 px-lg-3 pt-2 pb-4">
                    <div style="height:135px;">
                        <img src="img/tys.png" alt="The Youth Series" height="100" class="pr-3" style="float:left;">
                        <p>The Youth Series rankings from 2019 to the current <?php echo date("Y"); ?> season.</p>
                    </div>
                    <table id="tys" class="display compact" style="width:100%">
                    <thead><tr><th></th><th></th><th></th><th></th><th></th></tr></thead>
                    <tbody>
                        <tr>
                          <td>Boys</td>
                          <td><a href="tys.php?y=2019&a=u10&s=male">U10</a></td>
                          <td><a href="tys.php?y=2019&a=u12&s=male">U12</a></td>
                          <td><a href="tys.php?y=2019&a=u14&s=male">U14</a></td>
                          <?php 
                            if (date("Y") == 2019) {
                                echo '<td><a href="tys.php?y=2019&a=u16&s=male">U16</a></td>';
                            } else {
                                echo '<td><a href="tys.php?y=2019&a=u17&s=male">U17</a></td>';
                            }
                          ?>                          
                        </tr>
                        <tr>
                          <td>Girls</td>
                          <td><a href="tys.php?y=2019&a=u10&s=female">U10</a></td>
                          <td><a href="tys.php?y=2019&a=u12&s=female">U12</a></td>
                          <td><a href="tys.php?y=2019&a=u14&s=female">U14</a></td>
                          <?php 
                            if (date("Y") == 2019) {
                                echo '<td><a href="tys.php?y=2019&a=u16&s=female">U16</a></td>';
                            } else {
                                echo '<td><a href="tys.php?y=2019&a=u17&s=female">U17</a></td>';
                            }
                          ?>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="row no-gutters" id="eventsLink">
                <div class="no-gutters col-12 dataTables_length">
                    <div style="margin-top:30px;" class="subBar">
                        <h6 name="results" class="home-events"><span class="oi oi-calendar pr-2"></span> Event Calendar</h6>
                    </div>
                    <div class="row no-gutters pl-2">
                        <div class="col-12 col-lg-5 py-2 pt-lg-0"><span style="color:#A00;" class="oi oi-map-marker"></span> <span class="pr-3" style="color:#666;">- Regional</span>
                        <span style="color:#75d3e7;" class="oi oi-star"></span> <span style="color:#666;">- England Fencing Selection</span></div>
                        <div class="col-12 col-lg-7 py-2 pt-lg-0"><span style="color:#fea526;" class="oi oi-lock-locked"></span> <span class="pr-3" style="color:#666;">- Date TBC</span>
                        <span style="color:#003399;" class="oi oi-flag"></span> <span style="color:#666;">- International Event</span></div>
                    </div>
                </div>
            </div>
            <div class="row no-gutters">
                <div class="index2 col-sm-12"></div>
            </div>
        </div>
      </div>
    </main>

    <?php include('inc/footer.php') ?>

    <script src="scripts/home.js"></script>
    <script>
        
        $(document).ready(function() {

            home.Intro();
            home.Recent();
            home.Forthcoming();

            $('#england').DataTable({
                "paging":   false,
                "searching": false,
                "ordering": false,
                "info":     false                
            });
            $('#elite').DataTable({
                "paging":   false,
                "searching": false,
                "ordering": false,
                "info":     false                
            });
            $('#lpjs').DataTable({
                "paging":   false,
                "searching": false,
                "ordering": false,
                "info":     false                
            });
            $('#tys').DataTable({
                "paging":   false,
                "searching": false,
                "ordering": false,
                "info":     false                
            });
        });
        
    </script>
  </body>
</html>