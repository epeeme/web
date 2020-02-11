<header>
  <div class="collapse bg-dark" id="navbarHeader">
    <div class="container">
      <div class="row">
        <div class="col-sm-7 col-md-6 py-3">
          <h4 class="text-white">About</h4>
          <p class="text-muted">Results & ranking data from LPJS, Elite Epee, BYC, EYC and other UK youth, cadet & junior fencing competitions.</p>
          <p class="text-muted">This site is completely open-source and all the code is freely available to view and download from the <a class="text-white" href="https://github.com/epeeme/web">github repo</a>. You can also see the <a class="text-white" href="https://github.com/epeeme/web/issues">issues</a> I'm currently working on and the current status of the entire epee.me <a class="text-white" href="https://github.com/epeeme/web/projects/1">project</a>.</p>
        </div>
        <div class="col-sm-5 col-md-5 offset-md-1 py-3 text-lg-right">
        <h4 class="text-white">Analysis</h4>
          <ul class="list-unstyled">
            <li><a href="/cadet.php" class="text-white">GBR Cadet Rankings</a></li>
            <li><a href="/efcHistory.php" class="text-white">EFC Cadet Performance</a></li>           
            <li><a href="/efcEventHistory.php" class="text-white">EFC Cadet Event History</a></li>           
          </ul>
          <ul class="list-unstyled">
            <li><a href="/jwcHistory.php" class="text-white">Junior World Cup Performance</a></li>           
            <li><a href="/jwcEventHistory.php" class="text-white">Junior World Cup Event History</a></li>           
          </ul>
          <h4 class="text-white">Contact</h4>
          <ul class="list-unstyled">
            <li><a href="https://twitter.com/epee_me" class="text-white">Follow on Twitter</a></li>
            <li><a href="https://www.facebook.com/epee.me/" class="text-white">Like on Facebook</a></li>
            <li><a href="mailto:dan@epee.me" class="text-white">Email me</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
  <div class="navbar navbar-dark bg-dark box-shadow">
    <div class="container d-flex justify-content-between">
    <form class="form-inline">
      <button class="navbar-toggler mr-3 ml-1" type="button" data-toggle="collapse" data-target="#navbarHeader" aria-controls="navbarHeader" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
      </button>
      <a href="/index.php" class="navbar-brand d-flex align-items-center">
      <div style="margin-top:-20px;">
        <span style="font-family: 'Syncopate', sans-serif; color:#50c6f2; font-size:150%; line-height:80%;">epee</span>
        <span style="font-family: 'Syncopate', sans-serif; color:#fff; font-size:200%; line-height:80%;">.</span>
        <span style="font-family: 'Syncopate', sans-serif; color:#7fd9f7; font-size:150%; line-height:80%;">me</span>
      </div>
      <div style="color:#ddd; font-size:70%; margin:40px 0px 0px -185px; letter-spacing:1px;">A UK Epee Results Database</div>          
      </a>
    </form>
      <form class="form-inline" id="searchForm" action="fencer.php" method="get">
      <input type="hidden" name="f" id="f" value="">
        <div class="input-group my-3">
        <input autocomplete="off" name="fencerName" id="fencerName" class="form-control typeahead" type="search" placeholder="Fencer name..." aria-label="Search" autofocus="autofocus" data-provide="typeahead">
        <button class="btn btn-info ml-3 searchButton" type="submit">Search</button>
        </div>
      </form>
    </div>
  </div>
</header>

