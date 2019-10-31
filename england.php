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
    <title>epee.me - England Epee Rankings</title>
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
                            <span><img style="height:50px;" class="logo" src="img/england.png" alt="England Fencing Epee"></span>
                            <p>Ranking and results data for England fencing selection events. The official England Fencing ranking list for epee can be found at - <a target="blank" href="http://francisfencing.org.uk/wroclaw/epee/">http://francisfencing.org.uk/wroclaw/epee/</a></p>
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
                                $startYear = 2009;
                                do {                                                                        
                                    print "<option value=\"".$currentYear."\">".$currentYear--."</option>";                                    
                                } while ($currentYear >= $startYear);
                              ?>
                            </select></span>
                            <span style="margin-left:12px"><select name="cat" class="cat">
                              <option value="">Age Group</option>
                              <option value="u14">U14</option>
                              <option value="u17">U17</option>
                            </select></span>
                            <span style="margin-left:12px"><select name="sex" class="sex">
                              <option value="">Sex</option>
                              <option value="male">Male</option>
                              <option value="female">Female</option>
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

    <script src="scripts/england.js"></script>
    <script>
        $(document).ready(function() {
            var season = getParameterByName('y');
            var age = getParameterByName('a');
            var sex = getParameterByName('s');
            if ((season) && (age) && (sex)) {
                $('select[name="season"]').val(season);
                $('select[name="cat"]').val(age);
                $('select[name="sex"]').val(sex);
                $('#getRankButton').trigger('click');
            } else {
              england.Rankings();        
              $('select[name="season"]').val(<?php echo date('Y'); ?>);
              $('select[name="season"]').on('change', function() {
                  var cCat = $('select[name="cat"]').val();
                  $('select[name="cat"]').find('option').remove().end();
                  $('select[name="cat"]').append('<option value="">Age Group</option>');
                  if (parseInt(this.value) <= '2018') {
                      $('select[name="cat"]').append('<option value="u13">U13</option>');                     
                      $('select[name="cat"]').append('<option value="u15">U15</option>');                     
                  } else {
                      $('select[name="cat"]').append('<option value="u14">U14</option>');                     
                      $('select[name="cat"]').append('<option value="u17">U17</option>');                     
                  }
                  if ($('select[name="cat"] option[value="'+cCat+'"]').length > 0) {
                      $('select[name="cat"]').val(cCat);
                  }
              });
            }
        });        
    </script>
  </body>
</html>

