<aside id="left-panel" class="left-panel">
    <nav class="navbar navbar-expand-sm navbar-default">

        <div class="navbar-header">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-menu"
                aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa fa-bars"></i>
            </button>
            <a class="navbar-brand" href="./"><img src="{{ asset('admin/images/logo.png') }}" alt="Logo"></a>
            <a class="navbar-brand hidden" href="./"><img src="{{ asset('admin/images/logo2.png') }}"
                    alt="Logo"></a>
        </div>

        <div id="main-menu" class="main-menu collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <li class="{{ Request::route()->getName() == 'dashboard' ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}"> <i class="menu-icon fa fa-dashboard"></i>Dashboard </a>
                </li>
                <li class="{{ Request::route()->getName() == 'user.list' ? 'active' : '' }}">
                    <a href="{{route('user.list')}}"> <i class="menu-icon fa fa-user"></i>Users </a>
                </li>
                <li class="{{ Request::route()->getName() == 'add-animal' ? 'active' : '' }}">
                    <a href="{{ route('add-animal') }}"> <i class="menu-icon fa fa-align-justify"></i>Add Animal </a>
                </li>
                <li class="{{ Request::route()->getName() == 'add-reptile' ? 'active' : '' }}">
                    <a href="{{ route('add-reptile') }}"> <i class="menu-icon fa fa-align-justify"></i>Add Reptile </a>
                </li>
                <li class="{{ Request::route()->getName() == 'add-snake' ? 'active' : '' }}">
                    <a href="{{ route('add-snake') }}"> <i class="menu-icon fa fa-align-justify"></i>Add Snake Species
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</aside>
