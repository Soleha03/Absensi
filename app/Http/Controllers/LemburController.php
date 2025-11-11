<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Lembur;
use App\Models\JamKerja;
use App\Models\User;
use App\Exports\LemburExport;
use App\Imports\LemburImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Validators\ValidationException;
use Illuminate\Support\Facades\Log;
use Exception;


class LemburController extends Controller
{
    /**
     * Menampilkan halaman form pengajuan lembur
     */
    public function create()
    {
        $jamKerjas = JamKerja::all(); // ambil daftar shift

        $user = Auth::user();
        $approvalUsers = collect(); // default kosong, tapi tetap collection

        if ($user->role === 'atasan') {

            $approvalUsers = User::where('role', 'hr')->get();
        } elseif ($user->role === 'karyawan') {
            if ($user->departement === 'Office') {

                $approvalUsers = User::whereIn('name', ['Yeni', 'Nadirman'])->get();
            } elseif ($user->departement === 'Sales') {
                $approvalUsers = User::whereIn('name', ['Nadirman', 'Defri'])->get();
            } elseif ($user->departement === 'Production') {
                $approvalUsers = User::whereIn('name', ['Zainuddin', 'Darwin'])->get();
            } elseif ($user->departement === 'Engineering') {
                $approvalUsers = User::whereIn('name', ['Rafly', 'Defri'])->get();
            }
        } elseif ($user->role === 'hr') {
            $approvalUsers = User::where('role', 'direktur')->get();
        } elseif ($user->role === 'direktur') {
            $approvalUsers = User::where('role', 'direktur')->get();
        }

        // Tambahan: Handle jika setelah semua logika, $approvalUsers masih kosong
        if ($approvalUsers->isEmpty()) {
            $approvalUsers = collect(['Nama Atasan Tidak Tersedia']);
        }

        return view('lembur.create', compact('jamKerjas', 'approvalUsers'));
    }


    /**
     * Menyimpan data lembur ke database
     */
    public function store(Request $request)
    {
        $request->validate([
            'tgl_pengajuan' => 'required|date',
            'tgl_jam_mulai' => 'required',
            'tgl_jam_selesai' => 'required',
            'approver_id' => 'required',
            'deskripsi_kerja' => 'required|string',
            'tanda_tangan' => 'required|string',

        ]);

        $approverId = $request->approver_id;

        $mulai = Carbon::createFromFormat('H:i', $request->tgl_jam_mulai);
        $selesai = Carbon::createFromFormat('H:i', $request->tgl_jam_selesai);


        if ($selesai->lessThan($mulai)) {
            $selesai->addDay();
        }

        // Hitung total jam (dengan desimal, misal 8.5 jam)
        $totalJamKerja = $mulai->floatDiffInHours($selesai);


        // Simpan paraf sebagai file gambar
        $imageData = $request->input('tanda_tangan');
        $imageName = 'paraf_' . time() . '.png';

        // Decode base64 dan simpan di storage/public/paraf/
        $imagePath = 'paraf/' . $imageName;
        $image = str_replace('data:image/png;base64,', '', $imageData);
        $image = str_replace(' ', '+', $image);
        Storage::disk('public')->put($imagePath, base64_decode($image));

        // Simpan ke database
        Lembur::create([
            'user_id' => Auth::id(),
            'tgl_pengajuan' => $request->tgl_pengajuan,
            'tgl_jam_mulai' => $request->tgl_jam_mulai,
            'tgl_jam_selesai' => $request->tgl_jam_selesai,
            'approver_id' => $approverId,
            'total_jam_kerja' => $totalJamKerja,
            'tanda_tangan' => $imagePath,
            'deskripsi_kerja' => $request->deskripsi_kerja,
            'status_pengajuan' => 'menunggu',
        ]);

        return redirect()->route('lembur.create')->with('success', 'Pengajuan lembur berhasil dikirim.');
    }

    /**
     * Halaman untuk HR/atasan melihat semua pengajuan lembur
     */
    public function data(Request $request)
    {
        // Filter
        $status = $request->get('status', '');
        $bulan = $request->get('bulan', date('n'));
        $tahun = $request->get('tahun', date('Y'));
        $department = $request->get('department', '');
        $tanggal = $request->get('tanggal',);

        $departments = User::select('departement')
            ->whereNotNull('departement')
            ->distinct()
            ->orderBy('departement', 'asc')
            ->pluck('departement');

        // Query Cuti (kode Anda sebelumnya sudah benar)
        $query = lembur::with('user')
            ->whereMonth('tgl_pengajuan', $bulan)
            ->whereYear('tgl_pengajuan', $tahun);

        if ($status) {
            $query->where('status_pengajuan', $status);
        }

        if ($tanggal) {
            $query->whereDate('tgl_pengajuan', $tanggal);
        }

        if ($department) {
            $query->whereHas('user', function ($userQuery) use ($department) {
                $userQuery->where('departement', $department);
            });
        }



        $lemburs = $query->paginate(10);

        // --- 3. KIRIMKAN $departments KE VIEW ---
        return view('lembur.data', compact(
            'lemburs',
            'bulan',
            'tahun',
            'department',
            'departments' // <-- Tambahkan ini
        ));
    }

    /**
     * Menampilkan halaman approval untuk atasan
     */
    public function approvalIndex(Request $request)
    {
        $user = Auth::user();

        $status_pengajuan = $request->get('status', '');
        $bulan = $request->get('bulan', date('n'));
        $tahun = $request->get('tahun', date('Y'));

        $query = Lembur::with('user', 'approver')
            ->whereMonth('tgl_pengajuan', $bulan)
            ->whereYear('tgl_pengajuan', $tahun)
            ->where('approver_id', $user->id); // ✅ hanya tampilkan pengajuan untuk approver yang sedang login

        // Optional filter status
        if ($status_pengajuan) {
            $query->where('status_pengajuan', $status_pengajuan);
        }

        $lemburs = $query->paginate(10);

        return view('lembur.approval', compact('lemburs', 'bulan', 'tahun'));
    }
    public function riwayat(Request $request)
    {
        $user = Auth::user();

        // Filter
        $status = $request->get('status', '');
        $bulan = $request->get('bulan', date('n'));
        $tahun = $request->get('tahun', date('Y'));
        $tanggal = $request->get('tanggal',);


        $query = lembur::with('approver')
            ->where('user_id', $user->id)
            ->whereMonth('tgl_pengajuan', $bulan)
            ->whereYear('tgl_pengajuan', $tahun);



        if ($status) {
            $query->where('status_pengajuan', $status);
        }

        if ($tanggal) {
            $query->whereDate('tgl_pengajuan', $tanggal);
        }

        $lemburs = $query->paginate(10);

        return view('lembur.riwayat', compact('lemburs', 'user', 'bulan', 'tahun'));
    }

    /**
     * Menampilkan detail lembur
     */
    public function show($id)
    {
        $lembur = Lembur::with(['user', 'approver'])->find($id);

        if (!$lembur) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan.'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $lembur
        ]);
    }


    public function processApproval(Request $request, Lembur $Lembur)
    {
        // 1. Validasi Input dari Form
        $validated = $request->validate([
            'status_pengajuan' => ['required', Rule::in(['disetujui', 'ditolak'])],
            'ttd_atasan_base64' => 'required|string', // TTD wajib diisi
        ]);

        // 2. Proses dan Simpan Tanda Tangan (Base64) sebagai File Gambar
        $imageData = $validated['ttd_atasan_base64'];


        $imageName = 'paraf_' . time() . '.png';

        // Decode base64 dan simpan di storage/public/paraf/
        $imagePath = 'tanda_tangan_atasan/' . $imageName;
        $image = str_replace('data:image/png;base64,', '', $imageData);
        $image = str_replace(' ', '+', $image);
        Storage::disk('public')->put($imagePath, base64_decode($image));

        // 3. Update Data Lembur di Database
        $Lembur->update([
            'status_pengajuan' => $validated['status_pengajuan'],
            'tanda_tangan_approver' => $imagePath, // Simpan URL publik ke file
            'tgl_status' => now(), // Catat tanggal persetujuan
        ]);

        // 4. Kembalikan ke halaman sebelumnya dengan pesan sukses
        return back()->with('success', 'Status pengajuan Lembur telah berhasil diperbarui.');
    }

    public function exportLembur(Request $request)
    {
        // --- (MULAI) KODE SALINAN DARI FUNGSI data() DENGAN PERBAIKAN ---

        // Filter
        $status = $request->get('status', '');
        $department = $request->get('department', '');
        $tanggal = $request->get('tanggal', ''); // Perbaikan: Menambahkan default ''
        $tahun = $request->get('tahun', date('Y'));

        // PERBAIKAN: Tambahkan Peta Bulan untuk mencegah error Carbon
        $bulanInput = $request->get('bulan', date('n'));
        $bulanMap = [
            'Januari' => 1,
            'Februari' => 2,
            'Maret' => 3,
            'April' => 4,
            'Mei' => 5,
            'Juni' => 6,
            'Juli' => 7,
            'Agustus' => 8,
            'September' => 9,
            'Oktober' => 10,
            'November' => 11,
            'Desember' => 12
        ];
        $bulan = $bulanMap[$bulanInput] ?? (int)$bulanInput; // $bulan pasti angka

        // $departments tidak diperlukan untuk export

        // Query Lembur
        $query = Lembur::with(['user', 'approver']) // Diubah: Muat relasi approver
            ->whereMonth('tgl_pengajuan', $bulan)
            ->whereYear('tgl_pengajuan', $tahun);

        if ($status) {
            $query->where('status_pengajuan', $status);
        }

        if ($tanggal) {
            $query->whereDate('tgl_pengajuan', $tanggal);
        }

        if ($department) {
            $query->whereHas('user', function ($userQuery) use ($department) {
                $userQuery->where('departement', $department);
            });
        }

        // --- (SELESAI) KODE SALINAN ---


        // --- GANTI BAGIAN 'paginate' DENGAN 'get()' ---
        $lemburs = $query->get(); // Ambil SEMUA data yang terfilter

        // Tentukan nama file
        $namaBulan = Carbon::create()->month($bulan)->format('F'); // Misal: "January"
        $fileName = 'Laporan_Lembur_' . $namaBulan . '_' . $tahun . '.xlsx';

        // Panggil Class Export baru dengan data $lemburs yang sudah difilter
        return Excel::download(new LemburExport($lemburs), $fileName);
    }

    public function importLembur(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);

        try {
            Excel::import(new LemburImport, $request->file('file'));

            return redirect()->back()->with('success', 'Data lembur berhasil diimpor!');
        } catch (ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris " . $failure->row() . ": " . implode(", ", $failure->errors());
            }
            return redirect()->back()->with('error', 'Gagal impor data. Periksa baris berikut: <br>' . implode('<br>', $errorMessages));
        } catch (Exception $e) {
            // Tangani error umum lainnya
            Log::error('Gagal impor lembur: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat impor.');
        }
    }
}
