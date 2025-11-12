<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow" >
    <button id="sidebarToggleTop" class="btn btn-link rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <h1 class="h6 mb-0 text-gray-800 font-weight-bold">PT. Vortex Energy Batam</h1>

    <ul class="navbar-nav ml-auto">
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown">
                <img class="img-profile rounded-circle mr-2"
                    src="{{ Auth::user()->foto ? asset('storage/' . Auth::user()->foto) : 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png' }}"
                    width="35" height="35">
                <div class="d-none d-lg-block text-end">
                    <div class="fw-semibold text-dark">{{ Auth::user()->name }}</div>
                    <div class="text-muted small text-uppercase">{{ Auth::user()->role }}</div>
                </div>

            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in">
                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profil
                </a>
                <div class="dropdown-divider"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
