@extends('layouts.app')

@section('title', 'Pengajuan Lembur')

@section('content')
    <div class="container-fluid">

        <!-- Judul -->
        <h1 class="h3 mb-4 text-gray-800">Pengajuan Lembur</h1>

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
                <form action="{{ route('lembur.store') }}" method="POST">
                    @csrf

                    <!-- Row 1: Date & Section -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date</label>
                            <div class="input-group">
                                <input type="text" name="tgl_pengajuan" class="form-control"
                                    value="{{ now()->format('Y-m-d') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Departemen</label>
                            <div class="input-group">

                                <input type="text" name="nama_karyawan" class="form-control"
                                    value="{{ old('nama_karyawan', Auth::user()->departement ?? '') }}" readonly>

                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Pilih Shift -->


                    <!-- Row 3: Waktu (2 Input Terpisah) -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Waktu</label>
                        <div class="d-flex align-items-center gap-2 p-3">
                            <div class="row g-2 flex-grow-1">
                                <div class="col">
                                    <small class="text-muted">Mulai</small>
                                    <input type="time" name="tgl_jam_mulai" id="jamMasuk" class="form-control" required>
                                </div>
                                <div class="col-auto d-flex align-items-center">
                                    <span>-</span>
                                </div>
                                <div class="col">
                                    <small class="text-muted">Selesai</small>
                                    <input type="time" name="tgl_jam_selesai" id="jamKeluar" class="form-control"
                                        required>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Row 5: Nama Karyawan & Nama Atasan -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Karyawan</label>
                            <input type="text" name="nama_karyawan" class="form-control"
                                value="{{ old('nama_karyawan', Auth::user()->name ?? '') }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Nama Atasan</label>
                            @if (!empty($approvalUsers))
                                <select name="approver_id" class="form-control" required>
                                    <option value="">-- Pilih Atasan --</option>
                                    @foreach ($approvalUsers as $atasan)
                                        <option value="{{ $atasan->id }}">{{ $atasan->name }} ({{ $atasan->role }})
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                        </div>
                    </div>

                    <!-- Row 6: Job Description -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Job Description</label>
                        <textarea name="deskripsi_kerja" class="form-control" rows="4"
                            placeholder="Masukkan deskripsi pekerjaan lembur..." required>{{ old('job_description') }}</textarea>
                    </div>

                    <!-- Row 7: Paraf (Canvas) -->
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
                        <a href="{{ route('lembur.create') }}" class="btn btn-secondary me-2">
                            <i class="fas fa-times"></i> Batal
                        </a>
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="fas fa-paper-plane"></i> Ajukan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <!-- Script -->
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

            // Touch support for mobile
            canvas.addEventListener('touchstart', (e) => {
                e.preventDefault();
                drawing = true;
                ctx.beginPath();
                const rect = canvas.getBoundingClientRect();
                const touch = e.touches[0];
                ctx.moveTo(touch.clientX - rect.left, touch.clientY - rect.top);
            });

            canvas.addEventListener('touchmove', (e) => {
                e.preventDefault();
                if (!drawing) return;
                const rect = canvas.getBoundingClientRect();
                const touch = e.touches[0];
                ctx.lineTo(touch.clientX - rect.left, touch.clientY - rect.top);
                ctx.strokeStyle = "#000";
                ctx.lineWidth = 2;
                ctx.stroke();
            });

            canvas.addEventListener('touchend', (e) => {
                e.preventDefault();
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
