@extends('layouts.app') {{-- Pastikan layout sb-admin2 sudah digunakan --}}

@section('title', 'Data Cuti')

@section('content')
    <div class="container-fluid">

        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Data Cuti</h1>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Filter Section -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('cuti.data') }}" method="GET" class="row g-3 align-items-center">

                    <div class="col-md-3">
                        <label for="department" class="form-label small fw-bold">Department</label>
                        <select name="department" id="department" class="form-control">
                            <option value="">Semua</option>

                            {{-- Ganti <option> statis Anda dengan loop ini --}}
                            @foreach ($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach

                        </select>
                    </div>

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
                        <label for="bulan">Bulan</label>
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
                        <label class="form-label fw-semibold">Tahun</label>
                        <select name="tahun" class="form-control">
                            @for ($i = 2020; $i <= 2030; $i++)
                                <option value="{{ $i }}"
                                    {{ request('tahun', date('Y')) == $i ? 'selected' : '' }}>
                                    {{ $i }}
                                </option>
                            @endfor
                        </select>
                    </div>

                    <div class="col-md-12 d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-filter me-1"></i> Terapkan
                        </button>
                        <a href="{{ route('cuti.export.data', request()->query()) }}" class="btn btn-success btn-sm me-2">
                            <i class="fas fa-file-excel me-1"></i> Export
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#importCutiModal">
                            <i class="fas fa-file-import me-1"></i> Import
                        </button>
                    </div>

                </form>
            </div>
        </div>

        <!-- Table Section -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-primary text-center text-dark">
                            <tr>
                                <th>No</th>
                                <th>No ID</th>
                                <th>Departemen</th>
                                <th>Nama</th>
                                <th>Nama Atasan</th>
                                <th>Jenis Cuti</th>
                                <th>Tanggal Pengajuan</th>
                                <th>Mulai</th>
                                <th>Selesai</th>
                                <th>Alasan</th>
                                <th>Status</th>
                                <th>Tanggal Disetujui</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $no = 0; @endphp
                            @forelse ($cutis as $cuti)
                                <tr>
                                    <td>{{ ++$no }}</td>
                                    <td>{{ $cuti->user->badge_number }}</td>
                                    <td>{{ $cuti->user->departement }}</td>
                                    <td>{{ $cuti->user->name }}</td>
                                    <td>{{ $cuti->approver->name ?? '-' }}</td>
                                    <td>{{ $cuti->jenis_cuti }}</td>
                                    <td>{{ \Carbon\Carbon::parse($cuti->tgl_pengajuan)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($cuti->tgl_mulai)->format('d/m/Y') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($cuti->tgl_selesai)->format('d/m/Y') }}</td>
                                    <td>{{ $cuti->alasan }}</td>
                                    <td>
                                        @if ($cuti->status_pengajuan == 'disetujui')
                                            <span class="badge bg-success">Disetujui</span>
                                        @elseif($cuti->status_pengajuan == 'menunggu')
                                            <span class="badge bg-warning text-dark">Menunggu</span>
                                        @elseif($cuti->status_pengajuan == 'diajukan')
                                            <span class="badge bg-warning text-dark">Diajukan</span>
                                        @else
                                            <span class="badge bg-danger">Ditolak</span>
                                        @endif
                                    </td>
                                    <td>{{ $cuti->tgl_status ? \Carbon\Carbon::parse($cuti->tgl_status)->format('d/m/Y') : '-' }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-info text-white btn-lihat"
                                            data-id="{{ $cuti->id }}">
                                            <i class="fas fa-eye"></i> Lihat
                                        </button>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center text-muted">Belum ada data cuti</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            {{ $cutis->appends(request()->query())->links() }}
                        </div>
                    </div>
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
                                <th>Nama Atasan</th>
                                <td id="detail-atasan"></td>
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

    <div class="modal fade" id="importCutiModal" tabindex="-1" aria-labelledby="importCutiModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('cuti.import.data') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="importCutiModalLabel">Import Data Cuti</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="fileImportCuti" class="form-label">Pilih file (Excel .xlsx):</label>
                            <input type="file" name="file" id="fileImportCuti" class="form-control" required>
                        </div>
                        <div class="alert alert-info p-2">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Template Wajib:</strong> Gunakan file Excel dari "Export Cuti".
                                Data akan di-update berdasarkan "No ID Karyawan" dan "Tgl Mulai".
                            </small>
                        </div>
                        <div class="alert alert-warning p-2">
                            <small>
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                <strong>Pencocokan Atasan:</strong> "Nama Atasan" akan dicocokkan berdasarkan nama. Pastikan
                                nama di Excel sama persis dengan nama di database.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-file-import me-1"></i> Import & Update
                        </button>
                    </div>
                </div>
            </form>
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

        // ✅ Fungsi format tanggal (contoh: 10 Oktober 2025)
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
                        document.getElementById('detail-atasan').textContent = data.approver
                            ?.name ?? '-';
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
                        document.getElementById('detail-status').innerHTML =
                            statusBadge;

                        

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
