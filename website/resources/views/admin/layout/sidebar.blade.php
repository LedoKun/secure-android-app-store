<div class="col-sm-3 col-md-2 sidebar">
  <ul class="nav nav-sidebar">
    <li class="{{ (Request::is('admin') ? 'active' : '') }}"><a href="{{ URL::to('admin') }}" class="{{ (Request::is('admin') ? 'active' : '') }}"><i class="fa fa-dashboard fa-fw"></i> Dashboard</a></li>
  </ul>
  <ul class="nav nav-sidebar">
    <li class="{{ (Request::is('admin/published') ? 'active' : '') }}"><a href="{{ URL::to('admin/published') }}" class="{{ (Request::is('admin/published') ? 'active' : '') }}"><i class="fa fa-cloud fa-fw"></i> Published Apps</a></li>
    <li class="{{ (Request::is('admin/upload') ? 'active' : '') }}"><a href="{{ URL::to('admin/upload') }}" class="{{ (Request::is('admin/upload') ? 'active' : '') }}"><i class="fa fa-cogs fa-fw"></i> Analysis Status</a></li>
    <li class="{{ (Request::is('admin/upload/create') ? 'active' : '') }}"><a href="{{ URL::to('admin/upload/create') }}" class="{{ (Request::is('admin/upload/create') ? 'active' : '') }}"><i class="fa fa-cloud-upload fa-fw"></i> Upload Apps</a></li>
  </ul>
  <ul class="nav nav-sidebar">
    <li class="{{ (Request::is('admin/rules') ? 'active' : '') }}"><a href="{{ URL::to('/admin/rules') }}" class="{{ (Request::is('admin/rules') ? 'active' : '') }}"><i class="fa fa-bars fa-fw"></i> Analysis Options</a></li>
    <li class="{{ (Request::is('admin/rules/create') ? 'active' : '') }}"><a href="{{ URL::to('admin/rules/create') }}" class="{{ (Request::is('admin/rules/create') ? 'active' : '') }}"><i class="fa fa-wrench fa-fw"></i> Create Analysis Options</a></li>
  </ul>
  <ul class="nav nav-sidebar">
    <li class="{{ (Request::is('admin/site/edit') ? 'active' : '') }}"><a href="{{ URL::to('admin/config/edit') }}" class="{{ (Request::is('admin/config/edit') ? 'active' : '') }}"><i class="fa fa-cog fa-fw"></i> Web Store Settings</a></li>
  </ul>
  <ul class="nav nav-sidebar">
    <li class="{{ (Request::is('logout') ? 'active' : '') }}"><a href="{{ route('logout') }}" class="{{ (Request::is('logout') ? 'active' : '') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fa fa-sign-out fa-fw"></i> Logout</a></li>
  </ul>

  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">{{ csrf_field() }}</form>

</div>
