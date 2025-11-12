{{-- resources/views/dashboard/hr.blade.php --}}
@extends('layouts.app')

@section('title', 'Dashboard HR - PT. Vortex Energy Batam')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3 mt-n2">
        <h1 class="h3 text-gray-800 mb-0">Dashboard HR</h1>
    </div>

    <!-- Row 1 - Statistik -->
    <div class="row equal mb-4">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card card-stats border-left-primary shadow h-100 py-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Kehadiran</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $jumlahHadirTotal }}</div>
                    </div>
                    <i class="fas fa-calendar stats-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card card-stats border-left-success shadow h-100 py-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Karyawan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $alluser }}</div>
                    </div>
                    <i class="fas fa-users stats-icon"></i>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card card-stats border-left-info shadow h-100 py-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Tidak Hadir</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $jumlahTidakHadir }}</div>
                    </div>
                    <i class="fas fa-clipboard-list stats-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 2 - Info Absen -->
    <div class="row mt-2 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Absen Datang</h6>
                    <p class="mb-1">Normal : <strong>08:00:00</strong></p>
                    <p class="mb-0">Malam : <strong>15:30:00</strong></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Hari</h6>
                    @php
                        use Carbon\Carbon;
                        Carbon::setLocale('id');
                        $hari = Carbon::now()->translatedFormat('l'); // Kamis
                        $tanggal = Carbon::now()->translatedFormat('d F Y'); // 09 Oktober 2025
                    @endphp
                    <p class="fw-semibold text-primary mb-0">{{ $hari }}</p>
                    <p class="text-muted mb-0">{{ $tanggal }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body text-center">
                    <h6 class="text-muted mb-2">Absen Pulang</h6>
                    <p class="mb-1">Normal : <strong>17:00:00</strong></p>
                    <p class="mb-0">Malam : <strong>00:00:00</strong></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Kehadiran -->
    <div class="mt-4">
        @include('partials.table-kehadiran')
    </div>
@endsection
