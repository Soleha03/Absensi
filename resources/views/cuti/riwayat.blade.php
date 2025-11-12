@extends('layouts.app')

@section('title', 'Riwayat Cuti')

@section('content')
    <div class="container-fluid">

        <!-- Judul Halaman -->
        <h4 class="mb-4 text-gray-800 fw-bold">Riwayat Cuti</h4>

        <!-- Filter -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form action="{{ route('cuti.riwayat') }}" method="GET" class="row g-3 align-items-end">

                    {{-- Filter Status Cuti --}}
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Status Cuti</label>
                        <select name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="menunggu" {{ request('status_pengajuan') == 'menunggu' ? 'selected' : '' }}>
                                Menunggu</option>
                            <option value="disetujui" {{ request('status_pengajuan') == 'disetujui' ? 'selected' : '' }}>
                                Disetujui
                            </option>
                            <option value="ditolak" {{ request('status_pengajuan') == 'ditolak' ? 'selected' : '' }}>
                                Ditolak
                            </option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="bulan" class="form-label small fw-bold">Bulan</label>
                        <select class="form-control" id="bulan" name="bulan">
                            @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $namaBulan)
                                {{-- value diubah menjadi $loop->iteration (1, 2, 3, ...) --}}
                                <option value="{{ $loop->iteration }}" {{-- Logika 'selected' diubah untuk membandingkan angka (date('n')) --}}
                                    {{ request('bulan', date('n')) == $loop->iteration ? 'selected' : '' }}>

                                    {{ $namaBulan }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="tahun" class="form-label small fw-bold">Tahun</label>
                        <select name="tahun" class="form-control">
                            @for ($i = 2020; $i <= 2030; $i++)
                                <option value="{{ $i }}"
                                    {{ request('tahun', date('Y')) == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    {{-- Tombol Terapkan --}}
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Terapkan
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Tabel Riwayat Cuti -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead class="table-primary text-dark">
                            <tr>
                                <th>No</th>
                                <th>No ID</th>
                                <th>Departement</th>
                                <th>Nama</th>
                                <th>Nama Atasan</th>
                                <th>Jenis Cuti</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Tanggal Mulai</th>
                                <th>Tanggal Selesai</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th>Tanggal Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cutis as $cuti)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $cuti->user->badge_number }}</td>
                                    <td>{{ $cuti->user->departement }}</td>
                                    <td>{{ $cuti->user->name }}</td>
                                    <td>{{ $cuti->approver ? $cuti->approver->name : '-' }}</td>
                                    <td>{{ $cuti->jenis_cuti }}</td>
                                    <td>{{ \Carbon\Carbon::parse($cuti->tgl_pengajuan)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($cuti->tgl_mulai)->format('d M Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($cuti->tgl_selesai)->format('d M Y') }}</td>
                                    <td>{{ $cuti->alasan }}</td>
                                    <td>
                                        @if ($cuti->status_pengajuan == 'menunggu')
                                            <span class="badge bg-warning text-dark">Menunggu</span>
                                        @elseif($cuti->status_pengajuan == 'disetujui')
                                            <span class="badge bg-success">Disetujui</span>
                                        @elseif($cuti->status_pengajuan == 'ditolak')
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($cuti->tgl_status)
                                            {{ \Carbon\Carbon::parse($cuti->tgl_status)->format('d M Y') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info text-white btn-lihat"
                                            data-id="{{ $cuti->id }}">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted py-3">Belum ada data cuti</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal Lihat Detail Cuti -->
    <div class="modal fade" id="modalDetailCuti" tabindex="-1" aria-labelledby="detailCutiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="detailCutiLabel">
                        <i class="fas fa-file-alt me-2"></i> Detail Pengajuan Cuti
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th width="30%">Nama Pegawai</th>
                                <td id="detail-nama"></td>
                            </tr>
                            <tr>
                                <th>No ID</th>
                                <td id="detail-badge"></td>
                            </tr>
                            <tr>
                                <th>Jenis Cuti</th>
                                <td id="detail-jenis"></td>
                            </tr>
                            <tr>
                                <th>Tanggal Mulai</th>
                                <td id="detail-mulai"></td>
                            </tr>
                            <tr>
                                <th>Tanggal Selesai</th>
                                <td id="detail-selesai"></td>
                            </tr>
                            <tr>
                                <th>Lama Cuti</th>
                                <td id="detail-lama"></td>
                            </tr>
                            <tr>
                                <th>Alasan</th>
                                <td id="detail-alasan"></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td id="detail-status"></td>
                            </tr>
                            <tr>
                                <th>Diajukan Pada</th>
                                <td id="detail-dibuat"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="btnCloseModal">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                    <div id="action-buttons"></div>
                </div>
            </div>
        </div>
    </div>

@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalElement = document.getElementById('modalDetailCuti');
        const modal = new bootstrap.Modal(modalElement);
        const buttons = document.querySelectorAll('.btn-lihat');
        const closeButton = document.getElementById('btnCloseModal');
        const actionDiv = document.getElementById('action-buttons');

        function formatTanggal(dateString) {
            if (!dateString) return '-';
            const options = {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            };
            return new Date(dateString).toLocaleDateString('id-ID', options);
        }


        async function hitungLamaCuti(tglMulai, tglSelesai) {
            if (!tglMulai || !tglSelesai) return '-';

            const start = new Date(tglMulai);
            const end = new Date(tglSelesai);

            const response = await fetch('https://api-harilibur.vercel.app/api');
            const data = await response.json();

            // Ambil hanya tanggal merah (is_national_holiday = true)
            const tanggalMerah = data
                .filter(item => item.is_national_holiday)
                .map(item => item.holiday_date);

            let lamaCuti = 0;
            let current = new Date(start);

            while (current <= end) {
                const day = current.getDay(); // 0 = Minggu, 6 = Sabtu
                const dateStr = current.toISOString().split('T')[0];

                const isWeekend = (day === 0 || day === 6);
                const isHoliday = tanggalMerah.includes(dateStr);

                if (!isWeekend && !isHoliday) {
                    lamaCuti++;
                }

                current.setDate(current.getDate() + 1);
            }

            return lamaCuti;
        }

        // ✅ Tutup modal
        function closeModal() {
            modal.hide();
        }

        // Klik tombol “Lihat”
        buttons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;

                fetch(`/cuti/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        // Isi data ke modal
                        document.getElementById('detail-nama').textContent = data.user
                            ?.name ?? '-';
                        document.getElementById('detail-badge').textContent = data.user
                            ?.badge_number ?? '-';
                        document.getElementById('detail-jenis').textContent = data
                            .jenis_cuti ?? '-';
                        document.getElementById('detail-mulai').textContent = formatTanggal(
                            data.tgl_mulai);
                        document.getElementById('detail-selesai').textContent =
                            formatTanggal(data.tgl_selesai);


                        async function tampilkanDetail(data) {
                            const lamaCuti = await hitungLamaCuti(data.tgl_mulai, data
                                .tgl_selesai);
                            document.getElementById('detail-lama').textContent =
                                `${lamaCuti} hari`;
                        }
                        tampilkanDetail(data);

                        document.getElementById('detail-alasan').textContent = data
                            .alasan ?? '-';
                        document.getElementById('detail-dibuat').textContent =
                            formatTanggal(data.created_at);

                        // Status badge
                        let statusBadge = '';
                        switch (data.status_pengajuan) {
                            case 'disetujui':
                                statusBadge =
                                    '<span class="badge bg-success">Disetujui</span>';
                                break;
                            case 'ditolak':
                                statusBadge =
                                    '<span class="badge bg-danger">Ditolak</span>';
                                break;
                            default:
                                statusBadge =
                                    '<span class="badge bg-warning text-dark">Menunggu</span>';
                        }
                        document.getElementById('detail-status').innerHTML = statusBadge;

                        // Tombol aksi (hanya jika status masih menunggu)
                        if (data.status === 'menunggu') {
                            actionDiv.innerHTML = `
                            <form method="POST" action="/cuti/${data.id}/approve" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check"></i> Setujui
                                </button>
                            </form>
                            <form method="POST" action="/cuti/${data.id}/reject" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                            </form>
                        `;
                        } else {
                            actionDiv.innerHTML = '';
                        }

                        // ✅ Tampilkan modal
                        modal.show();
                    })
                    .catch(error => {
                        console.error('❌ Gagal mengambil data cuti:', error);
                        alert('Terjadi kesalahan saat mengambil data.');
                    });
            });
        });

        // ✅ Tutup modal ketika tombol “Tutup” diklik
        closeButton.addEventListener('click', closeModal);
    });
</script>
