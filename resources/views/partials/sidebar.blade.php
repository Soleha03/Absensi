{{-- resources/views/partials/sidebar.blade.php --}}
<nav class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Brand -->
    <a class="sidebar-brand d-flex flex-column align-items-center justify-content-center py-3"
        href="">
        <div class="sidebar-brand-icon mb-1">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" class="img-fluid" style="width: 90px; height: auto;">
        </div>
    </a>

    @php
        $user = Auth::user();
    @endphp

    <hr class="sidebar-divider my-0">


    <li class="nav-item">
        @if ($user->role === 'hr')
            <a class="nav-link" href="{{ route('dashboard.hr') }}">
            @elseif ($user->role === 'direktur')
                <a class="nav-link" href="{{ route('dashboard.direktur') }}">
                @elseif ($user->role === 'atasan')
                    <a class="nav-link" href="{{ route('dashboard.atasan') }}">
                    @elseif ($user->role === 'karyawan')
                        <a class="nav-link" href="{{ route('dashboard.karyawan') }}">
                        @else
                            <a class="nav-link" href="#">
        @endif
        <i class="fas fa-fw fa-tachometer-alt"></i>
        <span>Dashboard</span>
        </a>
    </li>


    <hr class="sidebar-divider">

    {{-- ======================================================
        SIDEBAR UNTUK HR
    ======================================================= --}}
    @if (Auth::user()->role === 'hr')
        {{-- ABSENSI --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#absensiHR"
                aria-expanded="false" aria-controls="absensiHR">
                <i class="fas fa-fw fa-calendar-check"></i>
                <span>Absensi</span>
            </a>
            <div id="absensiHR" class="collapse" >
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('absensi.create') }}">Absensi</a>
                    <a class="collapse-item" href="{{ route('absensi.riwayat') }}">Riwayat Absensi</a>
                    <a class="collapse-item" href="{{ route('absensi.data') }}">Data Absensi</a>
                </div>
            </div>
        </li>

        {{-- CUTI --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#cutiHR"
                aria-expanded="false" aria-controls="cutiHR">
                <i class="fas fa-calendar-day"></i>
                <span>Cuti</span>
            </a>
            <div id="cutiHR" class="collapse">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('cuti.create') }}">Pengajuan Cuti</a>
                    <a class="collapse-item" href="{{ route('cuti.riwayat') }}">Riwayat Cuti</a>
                    <a class="collapse-item" href="{{ route('cuti.approval') }}">Approval Cuti</a>
                    <a class="collapse-item" href="{{ route('cuti.data') }}">Data Cuti</a>
                </div>
            </div>
        </li>

        {{-- LEMBUR --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#lemburHR"
                aria-expanded="false" aria-controls="lemburHR">
                <i class="fas fa-fw fa-clock"></i>
                <span>Lembur</span>
            </a>
            <div id="lemburHR" class="collapse" >
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('lembur.create') }}">Pengajuan Lembur</a>
                    <a class="collapse-item" href="{{ route('lembur.riwayat') }}">Riwayat Lembur</a>
                    <a class="collapse-item" href="{{ route('lembur.approval') }}">Approval Lembur</a>
                    <a class="collapse-item" href="{{ route('lembur.data') }}">Data Lembur</a>
                </div>
            </div>
        </li>

        {{-- DATA PENGGUNA --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ route('pengguna.index') }}">
                <i class="fas fa-fw fa-users-cog"></i>
                <span>Data Pengguna</span>
            </a>
        </li>

        {{-- PROFILE --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="fas fa-fw fa-user"></i>
                <span>Profil</span>
            </a>
        </li>

        {{-- ======================================================
        SIDEBAR UNTUK KARYAWAN
    ======================================================= --}}
    @elseif (Auth::user()->role === 'karyawan')
        {{-- ABSENSI --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#absensiKaryawan">
                <i class="fas fa-fw fa-calendar-day"></i>
                <span>Absensi</span>
            </a>
            <div id="absensiKaryawan" class="collapse">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('absensi.create') }}">Absensi</a>
                    <a class="collapse-item" href="{{ route('absensi.riwayat') }}">Riwayat Absensi</a>
                </div>
            </div>
        </li>

        {{-- CUTI --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#cutiKaryawan">
                <i class="fas fa-fw fa-plane"></i>
                <span>Cuti</span>
            </a>
            <div id="cutiKaryawan" class="collapse">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('cuti.create') }}">Pengajuan Cuti</a>
                    <a class="collapse-item" href="{{ route('cuti.riwayat') }}">Riwayat Cuti</a>
                </div>
            </div>
        </li>

        {{-- LEMBUR --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#lemburKaryawan">
                <i class="fas fa-fw fa-clock"></i>
                <span>Lembur</span>
            </a>
            <div id="lemburKaryawan" class="collapse">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('lembur.create') }}">Pengajuan Lembur</a>
                    <a class="collapse-item" href="{{ route('lembur.riwayat') }}">Riwayat Lembur</a>
                </div>
            </div>
        </li>

        {{-- PROFILE --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="fas fa-fw fa-user"></i>
                <span>Profil</span>
            </a>
        </li>

        {{-- ======================================================
        SIDEBAR UNTUK ATASAN
    ======================================================= --}}
    @elseif (Auth::user()->role === 'atasan')
        {{-- ABSENSI --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#absensiAtasan">
                <i class="fas fa-fw fa-calendar"></i>
                <span>Absensi</span>
            </a>
            <div id="absensiAtasan" class="collapse">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('absensi.create') }}">Absensi</a>
                    <a class="collapse-item" href="{{ route('absensi.riwayat') }}">Riwayat Absensi</a>
                </div>
            </div>
        </li>

        {{-- CUTI --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#cutiAtasan">
                <i class="fas fa-calendar-day"></i>
                <span>Cuti</span>
            </a>
            <div id="cutiAtasan" class="collapse">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('cuti.create') }}">Pengajuan Cuti</a>
                    <a class="collapse-item" href="{{ route('cuti.riwayat') }}">Riwayat Cuti</a>
                    <a class="collapse-item" href="{{ route('cuti.approval') }}">Approval dari Karyawan</a>
                </div>
            </div>
        </li>

        {{-- LEMBUR --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#lemburAtasan">
                <i class="fas fa-fw fa-clock"></i>
                <span>Lembur</span>
            </a>
            <div id="lemburAtasan" class="collapse">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('lembur.create') }}">Pengajuan Lembur</a>
                    <a class="collapse-item" href="{{ route('lembur.riwayat') }}">Riwayat Lembur</a>
                    <a class="collapse-item" href="{{ route('lembur.approval') }}">Approval dari Karyawan</a>
                </div>
            </div>
        </li>

        {{-- PROFILE --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="fas fa-fw fa-user"></i>
                <span>Profil</span>
            </a>
        </li>

        {{-- ======================================================
        SIDEBAR UNTUK DIREKTUR
    ======================================================= --}}
    @elseif (Auth::user()->role === 'direktur')
        {{-- ABSENSI --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#absensiDirektur">
                <i class="fas fa-fw fa-calendar-alt"></i>
                <span>Absensi</span>
            </a>
            <div id="absensiDirektur" class="collapse">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('absensi.create') }}">Absensi</a>
                    <a class="collapse-item" href="{{ route('absensi.riwayat') }}">Riwayat Absensi</a>
                </div>
            </div>
        </li>

        {{-- CUTI --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#cutiDirektur">
                <i class="fas fa-calendar-day"></i>
                <span>Cuti</span>
            </a>
            <div id="cutiDirektur" class="collapse">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('cuti.create') }}">Pengajuan Cuti</a>
                    <a class="collapse-item" href="{{ route('cuti.riwayat') }}">Riwayat Cuti</a>
                    <a class="collapse-item" href="{{ route('cuti.approval') }}">Approval dari HR</a>
                </div>
            </div>
        </li>

        {{-- LEMBUR --}}
        <li class="nav-item">
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#lemburDirektur">
                <i class="fas fa-fw fa-clock"></i>
                <span>Lembur</span>
            </a>
            <div id="lemburDirektur" class="collapse">
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item" href="{{ route('lembur.create') }}">Pengajuan Lembur</a>
                    <a class="collapse-item" href="{{ route('lembur.riwayat') }}">Riwayat Lembur</a>
                    <a class="collapse-item" href="{{ route('lembur.approval') }}">Approval dari HR</a>
                </div>
            </div>
        </li>

        {{-- PROFILE --}}
        <li class="nav-item">
            <a class="nav-link" href="{{ route('profile.edit') }}">
                <i class="fas fa-fw fa-user"></i>
                <span>Profil</span>
            </a>
        </li>
    @endif

    <hr class="sidebar-divider d-none d-md-block">

</nav>
