@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard Karyawan</h1>
        </div>

        <!-- Welcome Section -->
        <div class="mb-4">
            <h5 class="fw-bold">👋 Selamat Datang, <span class="text-primary">{{ Auth::user()->name ?? 'Nama' }}</span></h5>
            <p class="mb-1">
                📅 Hari ini:
                <strong>{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</strong>
            </p>
            <p class="mb-0">
                ✅ Status Kehadiran:

                    <span class="badge bg-success text-white">{{ $absensiHariIni->isNotEmpty() ? 'Sudah Absen' : 'Belum Absen' }}</span>

            </p>

        </div>

        <div class="row">

            <!-- Absen Datang -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <h6 class="fw-bold text-gray-800 mb-3">Absen Datang</h6>
                        <div class="small text-gray-600">
                            <div>Pagi: 07:00:00</div>
                            <div>Normal: 08:00:00</div>
                            <div>Malam: 15:30:00</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Absen Pulang -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <h6 class="fw-bold text-gray-800 mb-3">Absen Pulang</h6>
                        <div class="small text-gray-600">
                            <div>Pagi: 16:00:00</div>
                            <div>Normal: 17:00:00</div>
                            <div>Malam: 00:00:00</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Cuti -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body text-center">
                        <h6 class="fw-bold text-gray-800">Total Cuti</h6>
                        <h3 class="fw-bold text-warning mt-2">{{ $cutiDisetujui }}</h3>
                    </div>
                </div>
            </div>

            <!-- Total OT -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body text-center">
                        <h6 class="fw-bold text-gray-800">Total OT</h6>
                        <h3 class="fw-bold text-success mt-2">{{ $lemburDisetujui }}</h3>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection
