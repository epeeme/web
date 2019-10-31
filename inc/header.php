    <header>
      <div class="collapse bg-dark" id="navbarHeader">
        <div class="container">
          <div class="row">
            <div class="col-sm-8 col-md-7 py-4">
              <h4 class="text-white">About</h4>
              <p class="text-muted">Results & ranking data from LPJS, Elite Epee, BYC, EYC and other UK youth, cadet & junior fencing competitions.</p>
              <p class="text-muted">Additional text and links will appear in this header space in due course.</p>
            </div>
            <div class="col-sm-4 offset-md-1 py-4">
              <h4 class="text-white">Contact</h4>
              <ul class="list-unstyled">
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

