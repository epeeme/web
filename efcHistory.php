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
    <title>epee.me - EFC Cadet Performance Analysis</title>
    <style>
    th {
        position: relative;
        min-height: 41px;
    }
    th span {
        display: block;
        position: absolute;
        left: 0;
        right: 0;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }
    @media screen and (max-width: 640px) {
        .country {
            clear:left;
            margin:12px 0px 0px -12px;
        }
        .rowHide {
            display:none;
        }
    }
    </style>
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
                            <span><img style="height:100px; margin-bottom:12px;" class="logo" src="img/efc.png" alt="EFC European Fencing Eurofencing"></span>
                            <p>Performance analysis of every cadet fencer that has competed in an EFC event since 2008 broken down by season and country. Results are expressed as a percentage finishing position - <code>(100 / entries) * finishing position</code> - and heat mapped for improved visual representation.</p>
                        </div>
                        <div class="extra-fields demographics" style="margin-bottom:20px; margin-left: 0.5em;">
                            <form class="filters">
                            <div class="mainFilters">
                            <span class="season"><select name="season">
                              <option value="">Season</option>
                              <?php 
                                $currentYear = date('Y');
                                $currentYearEnd = date('y') + 1;
                                $startYear = 2005;
                                do {                                                                        
                                    print "<option value=\"".$currentYear."\">".$currentYear--." / ".$currentYearEnd--."</option>";
                                } while ($currentYear >= $startYear);
                              ?>
                            </select></span>
                            <span style="margin-left:12px"><select name="catID" class="sex">
                              <option value="">Sex</option>
                              <option value="6">Male</option>
                              <option value="2">Female</option>
                            </select></span>
                            <span style="margin-left:12px"><select name="country" class="country">
                              <option value="">Country</option>
                              <option value="415">Great Britain</option>
                            </select></span>
                            <button id="getHistoryButton" type="button" class="btn btn-primary"> Go! </button>
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
            cadet.efcCountryList();
            var season = getParameterByName('y');
            var catID = getParameterByName('s');
            var country = getParameterByName('c');
            if ((season) && (catID) && (country)) {
                $('select[name="season"]').val(season);
                $('select[name="catID"]').val(catID);
                $('select[name="country"]').val(country);
                $('#getRankButton').trigger('click');
                $('.loading').show();
            } else {
                var d = new Date();
                var m = d.getMonth();
                var y = d.getFullYear();
                if (m < 4) y = y - 1;
                $('select[name="season"]').val(y);
                $('select[name="country"]').val(415);
            }
        });        
    </script>
  </body>
</html>

