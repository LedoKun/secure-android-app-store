<!-- Fixed navbar -->
<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="{{ URL::to('/') }}">{{ \App\SiteConfig::getSiteName() }}</a>
    </div>
    <div id="navbar" class="navbar-collapse collapse pull-right">
      <ul class="nav navbar-nav">
        <li><a href="{{ URL::to('/admin') }}">Go to store dashboard</a></li>
      </ul>
    </div><!--/.nav-collapse -->
  </div>
</nav>
