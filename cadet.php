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
    <link href='https://fonts.googleapis.com/css?family=Syncopate:400,700' rel='stylesheet' type='text/css'>
    <meta name="description" content="Results & ranking data from LPJS, Elite Epee, BYC, EYC and other UK youth, cadet & junior fencing competitions">
    <meta name="keywords" content="fencing, fence, fencer, epee, epeeist, results, ranking, leon paul, elite epee, britih fencing, england fencing, britishfencing, database, data, best, top, clubs, cadet, junior, youth">
    <meta name="author" content="Dan Kew">
    <link href="css/common.css" rel="stylesheet">
    <title>epee.me - British Cadet Rankings</title>
  </head>
  <body>

    <?php include('inc/header.php') ?>

    <main role="main">
      <div class="album py-4 bg-light">
        <div class="container">
            <div class="row no-gutters">
                <div class="no-gutters col-12 dataTables_length" style="padding-bottom:10px;">
                    <div style="border-bottom:2px solid #ddd; margin-bottom:10px;">
                        <div style="margin-top:-0.2em;border-bottom:1px solid #ddd; margin-bottom:20px;">
                            <!--<span><img style="height:50px;" class="logo" src="img/england.png" alt="England Fencing Epee"></span>//-->
                            <p>Ranking and results data for British Fencing ranking and nominated events. The official British Fencing ranking list for cadet epee can be found at - <a target="blank" href="http://francisfencing.org.uk/">http://francisfencing.org.uk/</a></p>
                        </div>
                        <div class="extra-fields demographics" style="margin-bottom:20px; margin-left: 0.5em;">
                            <form class="filters">
                            <input type="hidden" name="catID">
                            <input type="hidden" name="seasonStart" value="">
                            <input type="hidden" name="seasonEnd" value=""> 
                            <input type="hidden" name="series" value=""> 
                            <div class="mainFilters">
                            <span class="season"><select name="season">
                              <option value="">Season</option>
                              <?php 
                                $currentYear = date('Y');
                                $currentYearEnd = date('y') + 1;
                                $startYear = 2019;
                                do {                                                                        
                                    print "<option value=\"".$currentYear."\">".$currentYear--." / ".$currentYearEnd."</option>";
                                } while ($currentYear >= $startYear);
                              ?>
                            </select></span>
                            <span style="margin-left:12px"><select name="catID" class="sex">
                              <option value="">Sex</option>
                              <option value="6">Male</option>
                              <option value="2">Female</option>
                            </select></span>
                            <button id="getRankButton" type="button" class="btn btn-primary"> Go! </button>
                            </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="loading" style="display:none; padding:0px 12px;">
                <div class="d-flex align-items-center"><strong>Loading...</strong><div class="spinner-border ml-auto" role="status" aria-hidden="true"></div></div>
            </div>
            <div class="row no-gutters">
                <div class="index col-sm-12"></div>
            </div>
            <div class="row no-gutters">
                <div class="index2 col-sm-12" style="display:none; padding-top:60px;"></div>
            </div>
        </div>
      </div>
    </main>

    <?php include('inc/footer.php') ?>

    <script src="scripts/cadet.js"></script>
    <script>
        $(document).ready(function() {
            var season = getParameterByName('y');
            var sex = getParameterByName('s');
            if ((season) && (sex)) {
                $('select[name="season"]').val(season);
                $('select[name="catID"]').val(sex);
                $('#getRankButton').trigger('click');
            } else {
              cadet.Rankings();        
              // $('select[name="season"]').val(<?php echo date('Y'); ?>);
            }
        });        
    </script>
  </body>
</html>

