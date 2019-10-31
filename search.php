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
    <title>epee.me - Search Results</title>
  </head>
  <body>

    <?php include('inc/header.php') ?>

    <main role="main">
      <div class="album py-4 bg-light">
        <div class="container">
            <div class="row no-gutters pb-3">
                <div class="no-gutters col-12 dataTables_length">
                    <div style="margin-top:10px;" class="subBar">
                        <h6 name="results" class="search-results"><span class="oi oi-pulse pr-2"></span> Search Results - <strong><?php echo $_GET['qs']; ?></strong></h6>
                    </div>
                </div>
            </div>
            <div class="row no-gutters">
                <div class="index1 col-sm-12"></div>
            </div>
        </div>
      </div>
    </main>

    <?php include('inc/footer.php') ?>

    <script src="scripts/search.js"></script>
    <script>
        search.Fencer('<?php echo $_GET['qs']; ?>');        
    </script>
  </body>
</html>