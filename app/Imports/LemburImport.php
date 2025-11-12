<?php

namespace App\Imports;

use App\Models\Lembur; // Ganti 'App\Models\Lembur' jika path Anda beda
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
// use Maatwebsite\Excel\Concerns\SkipsOnFailure;
// use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date; // Untuk helper tanggal
use Carbon\Carbon;

class LemburImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation
// SkipsOnFailure // Melewati baris yang gagal validasi
{
    private $userCache = [];
    private $atasanCache = [];

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // 1. Temukan User (Karyawan) berdasarkan 'No ID'
            $badge_number = $row['no_id_karyawan'];
            if (!isset($this->userCache[$badge_number])) {
                $this->userCache[$badge_number] = User::where('badge_number', $badge_number)->first();
            }
            $user = $this->userCache[$badge_number];

            // Jika user tidak ada di DB, lewati baris ini (sesuai permintaan)
            if (!$user) {
                continue;
            }

            // 2. Temukan Atasan (Approver) berdasarkan 'Nama Atasan'
            $nama_atasan = $row['nama_atasan'] ?? '-';
            $approver_id = null;
            if ($nama_atasan !== '-') {
                if (!isset($this->atasanCache[$nama_atasan])) {
                    $this->atasanCache[$nama_atasan] = User::where('name', $nama_atasan)->first();
                }
                $approver = $this->atasanCache[$nama_atasan];
                $approver_id = $approver ? $approver->id : null;
            }

            // 3. Parse Tanggal & Waktu
            $tgl_pengajuan = $this->transformDate($row['tanggal_pengajuan']);
            $tgl_jam_mulai = $this->transformDateTime($row['tanggal_jam_mulai']);
            $tgl_jam_selesai = $this->transformDateTime($row['tanggal_jam_selesai']);
            // 4. Terjemahkan Status
            $status_enum = match (strtolower($row['status_pengajuan'] ?? 'menunggu')) {
                'disetujui' => 'disetujui',
                'ditolak' => 'ditolak',
                default => 'menunggu', // Default jika tidak dikenal
            };

            // 5. Tentukan Tanggal Status
            $tgl_status = ($status_enum !== 'menunggu')
                ? ($this->transformDateTime($row['tanggal_status']) ?? Carbon::now())
                : null;

            
            Lembur::firstOrCreate( 
                [
                    'user_id' => $user->id,
                    'tgl_pengajuan' => $tgl_pengajuan,
                    'tgl_jam_mulai' => $tgl_jam_mulai,
                    'tgl_jam_selesai' => $tgl_jam_selesai,
                ],
                [
                    'approver_id' => $approver_id,
                    'nama_atasan' => $nama_atasan,
                    'deskripsi_kerja' => $row['deskripsi_kerja'] ?? null,
                    'status_pengajuan' => $status_enum,
                    'total_jam_kerja' => $row['total_jam_kerja'] ?? null,
                    'tgl_status' => $tgl_status,

                    // Mengisi null untuk menghindari error 'doesn't have a default value'
                    'tanda_tangan' => 'Approved',
                    'tanda_tangan_approver' => 'Approved',
                ]
            );
        }
    }

    /**
     * Aturan validasi untuk setiap baris di Excel.
     */
    public function rules(): array
    {
        return [
            'no_id_karyawan' => 'required|string|exists:users,badge_number',

            'tanggal_pengajuan' => 'required', // <-- Diubah
            'tanggal_jam_mulai' => 'required', // <-- Diubah
            'tanggal_jam_selesai' => 'required', // <-- Diubah

            'status_pengajuan' => 'required|string',
            'nama_atasan' => 'nullable|string',

            // Tambahkan validasi untuk kolom lain jika perlu
            'tanggal_status' => 'nullable', // <-- Diubah
        ];
    }

    // public function onFailure(Failure ...$failures)
    // {
    //     // Biarkan kosong agar SkipsOnFailure berjalan
    // }

    // Helper untuk konversi TANGGAL
    private function transformDate($value): ?string
    {
        if (empty($value)) return null;
        try {
            return is_numeric($value)
                ? Date::excelToDateTimeObject($value)->format('Y-m-d')
                : Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            } catch (\Exception $e2) {
                return null;
            }
        }
    }

    // Helper untuk konversi TANGGAL & WAKTU
    private function transformDateTime($value): ?string
    {
        if (empty($value)) return null;
        try {
            return is_numeric($value)
                ? Date::excelToDateTimeObject($value)->format('Y-m-d H:i:s')
                : Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            try {
                return Carbon::createFromFormat('d/m/Y H:i', $value)->format('Y-m-d H:i:s');
            } catch (\Exception $e2) {
                return null;
            }
        }
    }
}
