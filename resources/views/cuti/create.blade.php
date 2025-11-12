@extends('layouts.app')
@section('title', 'Pengajuan Cuti')

@section('content')
    <div class="container-fluid">

        <!-- Judul -->
        <h1 class="h3 mb-4 text-gray-800">Pengajuan Cuti</h1>

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>✓ Berhasil!</strong> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>✗ Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>✗ Validasi Gagal!</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Card Form -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form action="{{ route('cuti.store') }}" method="POST">
                    @csrf

                    <!-- Row 1 -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">No ID</label>
                            <input type="text" name="badge_number" class="form-control"
                                value="{{ old('badge_number', auth()->user()->badge_number ?? '') }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Tanggal Pengajuan</label>
                            <input type="text" class="form-control" value="{{ now()->format('d/m/Y H:i') }}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Departemen</label>
                            <input type="text" class="form-control text-uppercase" value="{{ Auth::user()->departement }}"
                                readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Nama Karyawan</label>
                            <input type="text" class="form-control" value="{{ Auth::user()->name }}" readonly>
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Atasan yang Menerima Laporan</label>

                            @if (!empty($approvalUsers))
                                <select name="approver_id" class="form-control" required>
                                    <option value="">-- Pilih Atasan --</option>
                                    @foreach ($approvalUsers as $atasan)
                                        <option value="{{ $atasan->id }}">{{ $atasan->name }} ({{ $atasan->role }})
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <input type="text" name="approval_id" class="form-control"
                                    placeholder="Masukkan nama atasan" required>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Cuti</label>
                            <select name="jenis_cuti" id="jenis_cuti" class="form-control" required>
                                <option value="">-- Pilih Jenis Cuti --</option>
                                <option value="Cuti Tahunan">Cuti Tahunan</option>
                                <option value="Cuti Sakit">Cuti Sakit</option>
                                <option value="Cuti Melahirkan">Cuti Melahirkan</option>
                                <option value="Cuti Lainnya">Cuti Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <!-- Row 3 -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Start Date</label>
                            <input type="date" name="tgl_mulai" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">End Date</label>
                            <input type="date" name="tgl_selesai" class="form-control" required>
                        </div>
                    </div>

                    <!-- Row 4 -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Alasan Izin Cuti</label>
                        <textarea name="alasan" class="form-control" rows="3" placeholder="Tuliskan alasan cuti..." required>{{ old('alasan') }}</textarea>
                    </div>

                    <!-- Row 5 - Tanda Tangan -->
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Tanda Tangan</label>
                        <div class="border rounded bg-light p-3 d-flex justify-content-center">
                            <canvas id="signature-pad" width="400" height="200"
                                style="border:1px solid #ccc; background:white; border-radius:6px;"></canvas>
                        </div>
                        <div class="mt-2 text-end">
                            <button type="button" class="btn btn-sm btn-secondary" id="clear-signature">Hapus</button>
                        </div>
                        <input type="hidden" name="tanda_tangan" id="tanda_tangan">
                        <small class="text-muted d-block mt-1">Tanda tangan langsung di atas kotak, klik "Hapus" untuk
                            mengulang.</small>
                    </div>

                    <!-- Submit -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4">Ajukan</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Script Tanda Tangan -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const canvas = document.getElementById('signature-pad');
            const ctx = canvas.getContext('2d');
            const clearBtn = document.getElementById('clear-signature');
            const input = document.getElementById('tanda_tangan');
            let drawing = false;

            // Background putih
            ctx.fillStyle = "#fff";
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            canvas.addEventListener('mousedown', (e) => {
                drawing = true;
                ctx.beginPath();
                ctx.moveTo(e.offsetX, e.offsetY);
            });
            canvas.addEventListener('mousemove', (e) => {
                if (!drawing) return;
                ctx.lineTo(e.offsetX, e.offsetY);
                ctx.strokeStyle = "#000";
                ctx.lineWidth = 2;
                ctx.stroke();
            });
            canvas.addEventListener('mouseup', () => {
                drawing = false;
                input.value = canvas.toDataURL('image/png');
            });
            clearBtn.addEventListener('click', () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                ctx.fillStyle = "#fff";
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                input.value = "";
            });
        });
    </script>
@endsection
