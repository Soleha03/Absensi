<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cuti;
use App\Models\User;
use App\Exports\CutiExport;
use App\Imports\CutiImport;
use Maatwebsite\Excel\Validators\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class CutiController extends Controller
{
    public function index()
    {
        $cutis = Cuti::where('user_id', Auth::id())->get();
        return view('cuti.create', compact('cutis'));
    }

    public function create()
    {
        $user = Auth::user();
        $approvalUsers = [];

        if ($user->role === 'atasan') {

            $approvalUsers = User::where('role', 'hr')
                ->where('departement', $user->departement)
                ->get();
        } elseif ($user->role === 'karyawan') {
            $approvalUsers = User::where('role', 'atasan')
                ->where('departement', $user->departement)
                ->get();
        } elseif ($user->role === 'hr') {
            $approvalUsers = User::where('role', 'direktur')
                ->where('departement', $user->departement)
                ->get();
        } elseif ($user->role === 'direktur') {
            $approvalUsers = User::where('id', $user->id)
                ->get();
        }

        if (empty($approvalUsers)) {
            $approvalUsers = 'Nama Atasan Tidak Tersedia';
        }

        return view('cuti.create', compact('approvalUsers'));
    }

    public function store(Request $request)
    {

        $request->validate([
            'tgl_mulai'     => 'required|date',
            'tgl_selesai'   => 'required|date|after_or_equal:tgl_mulai',
            'alasan'        => 'required|string',
            'jenis_cuti'    => 'required|string',
            'tanda_tangan'  => 'required|string',
            'approver_id'   => 'required|exists:users,id',
        ]);

      
        $user = Auth::user();
        $status = 'menunggu';
        $approverId = $request->approver_id;

        if ($user->role === 'direktur') {
            $status = 'disetujui';
            $approverId = $user->id;
        }

        Cuti::create([
            'user_id'          => $user->id,
            'tgl_pengajuan'    => now(),
            'tgl_mulai'        => $request->tgl_mulai,
            'tgl_selesai'      => $request->tgl_selesai,
            'alasan'           => $request->alasan,
            'jenis_cuti'       => $request->jenis_cuti,
            'status_pengajuan' => $status,
            'approver_id'      => $approverId,
            'tanda_tangan'     => $request->tanda_tangan,
        ]);

        return redirect()
            ->route('cuti.create')
            ->with('success', 'Pengajuan cuti berhasil diajukan!');
    }


    public function approvalIndex(Request $request)
    {
        $user = Auth::user();

        $status = $request->get('status', '');
        $bulan = $request->get('bulan', date('n'));
        $tahun = $request->get('tahun', date('Y'));

        $query = Cuti::with('user', 'approver')
            ->whereMonth('tgl_pengajuan', $bulan)
            ->whereYear('tgl_pengajuan', $tahun)
            ->where('approver_id', $user->id);
        // Optional filter status
        if ($status) {
            $query->where('status_pengajuan', $status);
        }

        $cutis = $query->paginate(10);

        return view('cuti.approval', compact('cutis', 'bulan', 'tahun'));
    }

    /**
     * Menampilkan halaman riwayat cuti user login
     */
    public function riwayat(Request $request)
    {
        $user = Auth::user();

        // Filter
        $status = $request->get('status', '');
        $bulan = $request->get('bulan', date('n'));
        $tahun = $request->get('tahun', date('Y'));

        $query = Cuti::with('approver')
            ->where('user_id', $user->id)
            ->whereMonth('tgl_pengajuan', $bulan)
            ->whereYear('tgl_pengajuan', $tahun);


        if ($status) {
            $query->where('status_pengajuan', $status);
        }

        $cutis = $query->paginate(10);

        return view('cuti.riwayat', compact('cutis', 'user', 'bulan', 'tahun'));
    }

    /**
     * Menampilkan data cuti (untuk HR/admin melihat semua data)
     */
    public function data(Request $request)
    {
        // Filter
        $status = $request->get('status', '');
        $bulan = $request->get('bulan', date('n'));
        $tahun = $request->get('tahun', date('Y'));
        $department = $request->get('department', '');

        $departments = User::select('departement')
            ->whereNotNull('departement')
            ->distinct()
            ->orderBy('departement', 'asc')
            ->pluck('departement');


        $query = Cuti::with('user')
            ->whereMonth('tgl_pengajuan', $bulan)
            ->whereYear('tgl_pengajuan', $tahun);

        if ($status) {
            $query->where('status_pengajuan', $status);
        }

        if ($department) {
            $query->whereHas('user', function ($userQuery) use ($department) {
                $userQuery->where('departement', $department);
            });
        }
        $query -> orderBy('tgl_pengajuan', 'desc');

        $cutis = $query->paginate(10);

        return view('cuti.data', compact(
            'cutis',
            'bulan',
            'tahun',
            'department',
            'departments'
        ));
    }

    public function show($id)
    {
        // Tambahkan relasi 'approver' agar bisa akses nama atasan
        $cuti = Cuti::with(['user', 'approver'])->findOrFail($id);

        return response()->json($cuti);
    }

    public function processApproval(Request $request, Cuti $cuti)
    {
        // 1. Validasi Input dari Form
        $validated = $request->validate([
            'status_pengajuan' => ['required', Rule::in(['disetujui', 'ditolak'])],
            'komentar' => 'nullable|string|max:255',
            'ttd_atasan_base64' => 'required|string', // TTD wajib diisi
        ]);

        $imageData = $validated['ttd_atasan_base64'];


        $imageName = 'paraf_' . time() . '.png';

        // Decode base64 dan simpan di storage/public/paraf/
        $imagePath = 'tanda_tangan_atasan_cuti/' . $imageName;
        $image = str_replace('data:image/png;base64,', '', $imageData);
        $image = str_replace(' ', '+', $image);
        Storage::disk('public')->put($imagePath, base64_decode($image));

        // 3. Update Data Cuti di Database
        $cuti->update([
            'status_pengajuan' => $validated['status_pengajuan'],
            'komentar' => $validated['komentar'],
            'tanda_tangan_approval' => $imagePath, // Simpan URL publik ke file
            'tgl_status' => now(), // Catat tanggal persetujuan
        ]);

        // 4. Kembalikan ke halaman sebelumnya dengan pesan sukses
        return back()->with('success', 'Status pengajuan cuti telah berhasil diperbarui.');
    }

    public function exportCuti(Request $request)
    {
        // --- (MULAI) KODE SALINAN DARI FUNGSI data() ---

        // Filter
        $status = $request->get('status', '');

        // PERBAIKAN: Tambahkan Peta Bulan untuk mencegah error Carbon
        $bulanInput = $request->get('bulan', date('n'));
        $tahun = $request->get('tahun', date('Y'));
        $department = $request->get('department', '');

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

        // Query Cuti (kode Anda sebelumnya sudah benar)
        $query = Cuti::with('user')
            ->whereMonth('tgl_pengajuan', $bulan)
            ->whereYear('tgl_pengajuan', $tahun);

        if ($status) {
            $query->where('status_pengajuan', $status);
        }

        if ($department) {
            $query->whereHas('user', function ($userQuery) use ($department) {
                $userQuery->where('departement', $department);
            });
        }

        // --- (SELESAI) KODE SALINAN DARI FUNGSI data() ---


        // --- GANTI BAGIAN 'paginate' DENGAN 'get()' ---
        $cutis = $query->get(); // Ambil SEMUA data yang terfilter

        // Tentukan nama file
        $namaBulan = Carbon::create()->month($bulan)->format('F'); // Misal: "January"
        $fileName = 'Laporan_Cuti_' . $namaBulan . '_' . $tahun . '.xlsx';

        // Panggil Class Export baru dengan data $cutis yang sudah difilter
        return Excel::download(new CutiExport($cutis), $fileName);
    }

    public function importCuti(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,xls'
    ]);

    try {
        Excel::import(new CutiImport, $request->file('file'));
        
        return redirect()->back()->with('success', 'Data cuti berhasil diimpor!');

    } catch (ValidationException $e) {
         $failures = $e->failures();
         $errorMessages = [];
         foreach ($failures as $failure) {
             // Tampilkan error validasi
             $errorMessages[] = "Baris" . $failure->row() . ": " . implode(", ", $failure->errors());
         }
         return redirect()->back()->with('error', 'Gagal impor data. Periksa baris berikut: <br>' . implode('<br>', $errorMessages));
    
    } catch (Exception $e) {
        // Tangani error umum lainnya
        Log::error('Gagal impor cuti: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat impor: ' . $e->getMessage());
    }
}
}
