<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Lembur;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $tanggal = Carbon::today();
        $tanggalString = $tanggal->format('Y-m-d');

        $alluser = User::count();

        // 1. Ambil semua data aksi HARI INI

        // (A) Aksi Aktif (Prioritas 1, 2, 3)
        $absensiHariIni = Absensi::with('jamKerja')
            ->whereDate('tanggal_waktu', $tanggalString)
            ->get()->groupBy('user_id');

        $cutiAktifHariIni = Cuti::where('status_pengajuan', 'disetujui')
            ->whereDate('tgl_mulai', '<=', $tanggalString)
            ->whereDate('tgl_selesai', '>=', $tanggalString)
            ->get()->keyBy('user_id');

        $lemburAktifHariIni = Lembur::where('status_pengajuan', 'disetujui')
            ->whereDate('tgl_jam_mulai', $tanggalString)
            ->get()->keyBy('user_id');

        // (B) Aksi Pengajuan (Prioritas 4)
        $cutiPengajuanHariIni = Cuti::whereDate('tgl_pengajuan', $tanggalString)
            ->get()->keyBy('user_id');

        $lemburPengajuanHariIni = Lembur::whereDate('tgl_pengajuan', $tanggalString)
            ->get()->keyBy('user_id');

        // 2. Gabungkan semua ID user yang relevan
        $allUserIds = $absensiHariIni->keys()
            ->merge($cutiAktifHariIni->keys())
            ->merge($lemburAktifHariIni->keys())
            ->merge($cutiPengajuanHariIni->keys())
            ->merge($lemburPengajuanHariIni->keys())
            ->unique();

        // 3. Ambil data user yang relevan
        $users = User::find($allUserIds);


        // 4. Loop user yang RELEVAN saja dan terapkan prioritas
        $data = $users->map(function ($user) use ($tanggal, $tanggalString, $absensiHariIni, $cutiAktifHariIni, $lemburAktifHariIni, $cutiPengajuanHariIni, $lemburPengajuanHariIni) {

            $baseData = [
                'name'        => $user->name,
                'departement' => $user->departement,
                'tanggal'     => $tanggal->format('d/m/Y'),
                'jam_masuk'   => '-',
                'jam_pulang'  => '-',
            ];

            // PRIORITAS 1: ABSENSI (Hadir, Terlambat, Tidak Hadir)
            if (isset($absensiHariIni[$user->id])) {
                $absensiUser = $absensiHariIni[$user->id];
                $masuk = $absensiUser->firstWhere('tipe_absen', 'masuk');
                $pulang = $absensiUser->firstWhere('tipe_absen', 'pulang');

                if ($masuk) { // Hanya proses jika ada data 'masuk'
                    $baseData['jam_masuk'] = Carbon::parse($masuk->tanggal_waktu)->format('H:i');
                    $baseData['jam_pulang'] = $pulang ? Carbon::parse($pulang->tanggal_waktu)->format('H:i') : '-';

                    $jamKerja = $masuk->jamKerja;
                    if ($jamKerja) {
                        $jamMasukNormalStr  = $jamKerja->jam_masuk ?? '08:00:00';
                        $jamKeluarNormalStr = $jamKerja->jam_keluar ?? '17:00:00';
                        $jamMasukNormal   = Carbon::parse("$tanggalString $jamMasukNormalStr");
                        $jamKeluarNormal  = Carbon::parse("$tanggalString $jamKeluarNormalStr");
                        $jamAbsen         = Carbon::parse($masuk->tanggal_waktu);
                        $jamMasukToleransi = $jamMasukNormal->copy()->addMinutes(10);

                        if ($jamAbsen->lt($jamMasukToleransi)) {
                            $baseData['status'] = 'Hadir';
                        } elseif ($jamAbsen->between($jamMasukToleransi, $jamKeluarNormal)) {
                            $baseData['status'] = 'Hadir (Terlambat)';
                        } else {
                            $baseData['status'] = 'Tidak Hadir';
                        }
                    } else {
                        $baseData['status'] = 'Hadir';
                    }
                    return $baseData;
                }
            }

            // PRIORITAS 2: Cuti Aktif (Disetujui)
            if (isset($cutiAktifHariIni[$user->id])) {
                $baseData['status'] = 'Cuti (Disetujui)';
                return $baseData;
            }

            // PRIORITAS 3: Lembur Aktif (Disetujui)
            if (isset($lemburAktifHariIni[$user->id])) {
                $lembur = $lemburAktifHariIni[$user->id];
                $baseData['status'] = 'Lembur (Disetujui)';
                $baseData['jam_masuk'] = Carbon::parse($lembur->tgl_jam_mulai)->format('H:i');
                $baseData['jam_pulang'] = $lembur->tgl_jam_selesai ? Carbon::parse($lembur->tgl_jam_selesai)->format('H:i') : '-';
                return $baseData;
            }

            // PRIORITAS 4: Pengajuan Cuti Baru (Diajukan hari ini)
            if (isset($cutiPengajuanHariIni[$user->id])) {
                $cuti = $cutiPengajuanHariIni[$user->id];
                $status_terjemahan = match ($cuti->status_pengajuan) {
                    'menunggu' => 'Cuti (Menunggu)',
                    'disetujui' => 'Cuti (Disetujui)',
                    'ditolak' => 'Cuti (Ditolak)',
                    default => 'Cuti'
                };
                $baseData['status'] = $status_terjemahan;
                return $baseData;
            }

            // PRIORITAS 5: Pengajuan Lembur Baru (Diajukan hari ini)
            if (isset($lemburPengajuanHariIni[$user->id])) {
                $lembur = $lemburPengajuanHariIni[$user->id];
                $status_terjemahan = match ($lembur->status_pengajuan) {
                    'menunggu' => 'Lembur (Menunggu)',
                    'disetujui' => 'Lembur (Disetujui)',
                    'ditolak' => 'Lembur (Ditolak)',
                    default => 'Lembur'
                };
                $baseData['status'] = $status_terjemahan;
                $baseData['jam_masuk'] = Carbon::parse($lembur->tgl_jam_mulai)->format('H:i');
                $baseData['jam_pulang'] = $lembur->tgl_jam_selesai ? Carbon::parse($lembur->tgl_jam_selesai)->format('H:i') : '-';
                return $baseData;
            }

            return null;
        });

        // 5. Filter semua hasil 'null'
        $data = $data->filter();

        $statusCounts = $data->countBy('status');

        // Ambil jumlah spesifik, berikan 0 jika status tidak ada
        $jumlahHadir = $statusCounts->get('Hadir', 0);
        $jumlahTerlambat = $statusCounts->get('Hadir (Terlambat)', 0);
        $jumlahTidakHadir = $statusCounts->get('Tidak Hadir', 0);
        
        // GABUNGKAN Hadir dan Terlambat
        $jumlahHadirTotal = $jumlahHadir + $jumlahTerlambat;

        // 6. Kirim data ke view
        return view('dashboard.hr', compact('data', 'alluser', 'jumlahHadirTotal', 'jumlahTidakHadir'));
    }

    public function direktur()
    {
        $user = Auth::user();
     $cutiDisetujui = Cuti::where('user_id', $user->id)
            ->where('status_pengajuan', 'disetujui')
            ->count();
    $lemburDisetujui = Lembur::where('user_id', $user->id)
            ->where('status_pengajuan', 'disetujui')
            ->count();
        return view('dashboard.direktur', compact('user', 'cutiDisetujui', 'lemburDisetujui'));
    }

    public function atasan()
    {
        $user = Auth::user();
     $cutiDisetujui = Cuti::where('user_id', $user->id)
            ->where('status_pengajuan', 'disetujui')
            ->count();
        $lemburDisetujui = Lembur::where('user_id', $user->id)
            ->where('status_pengajuan', 'disetujui')
            ->count();

        return view('dashboard.atasan', compact('user', 'cutiDisetujui', 'lemburDisetujui'));
    }

    public function karyawan()
    {
        $user = Auth::user();
 
        $absensiHariIni = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal_waktu', Carbon::today()->format('Y-m-d'))
            ->get();
        // Bisa tambahkan detail lain juga
        $cutiDisetujui = Cuti::where('user_id', $user->id)
            ->where('status_pengajuan', 'disetujui')
            ->count();
        $lemburDisetujui = Lembur::where('user_id', $user->id)
            ->where('status_pengajuan', 'disetujui')
            ->count();

        return view('dashboard.karyawan', compact('user', 'cutiDisetujui', 'lemburDisetujui','absensiHariIni'));
    }
}
