<?php

namespace App\Imports;

use App\Models\Cuti;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class CutiImport implements 
    ToCollection, 
    WithHeadingRow, 
    WithValidation,
    SkipsOnFailure // Ini akan melewati baris yang gagal validasi
{
    /**
    * @param Collection $rows
    */
    public function collection(Collection $rows)
    {
       
        $userCache = [];
        $atasanCache = [];

        foreach ($rows as $row) 
        {
            // 1. Temukan User (Karyawan) berdasarkan 'No ID Karyawan'
            // Ini PENTING untuk memenuhi aturan Anda
            $badge_number = $row['no_id_karyawan'];
            if (!isset($userCache[$badge_number])) {
                $userCache[$badge_number] = User::where('badge_number', $badge_number)->first();
            }
            $user = $userCache[$badge_number];

            // Jika user tidak ada di DB, lewati baris ini (sesuai permintaan)
            if (!$user) {
                continue; 
            }

            // 2. Temukan Atasan (Approver) berdasarkan 'Nama Atasan'
            $nama_atasan = $row['nama_atasan'];
            $approver_id = null;
            if ($nama_atasan !== '-') {
                if (!isset($atasanCache[$nama_atasan])) {
                    // Peringatan: Ini mengasumsikan nama atasan unik.
                    $atasanCache[$nama_atasan] = User::where('name', $nama_atasan)->first();
                }
                $approver = $atasanCache[$nama_atasan];
                $approver_id = $approver ? $approver->id : null;
            }

            // 3. Parse Tanggal
            $tgl_pengajuan = $this->transformDate($row['tanggal_pengajuan']);
            $tgl_mulai = $this->transformDate($row['tanggal_mulai']);
            $tgl_selesai = $this->transformDate($row['tanggal_selesai']);

            // 4. Terjemahkan Status
            $status_enum = match (strtolower($row['status_pengajuan'])) {
                'menunggu' => 'menunggu',
                'disetujui' => 'disetujui',
                'ditolak' => 'ditolak',
                default => 'menunggu', // Default jika tidak dikenal
            };

            Cuti::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'tgl_pengajuan' => $tgl_pengajuan,
                    'tgl_mulai' => $tgl_mulai,
                    'tgl_selesai' => $tgl_selesai,
                ],
                [
                    'jenis_cuti' => $row['jenis_cuti'] ?? 'Cuti', 
                    'status_pengajuan' => $status_enum,
                    'approver_id' => $approver_id,
                    // Jika keterangan '-', simpan sebagai null
                    'alasan' => ($row['keterangan'] === '-') ? null : $row['keterangan'],
                    // --- INI ADALAH PERBAIKANNYA ---
                    'tanda_tangan' => 'Approve',
                    'tanda_tangan_atasan' => 'Approve',
                    // ---------------------------------
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
            // Ini adalah aturan utama Anda: 'no_id_karyawan' HARUS ada di tabel users
            'no_id_karyawan' => 'required|string|exists:users,badge_number',
            
            // Validasi sisa kolom
            'tanggal_pengajuan' => 'required',
            'tanggal_mulai' => 'required',
            'tanggal_selesai' => 'required',
            'status_pengajuan' => 'required|string',

            // Validasi nama atasan (opsional, boleh null)
            // 'nullable' berarti boleh kosong atau '-'
            'nama_atasan' => 'nullable|string', 
        ];
    }

    /**
     * Menangani kegagalan validasi.
     * SkipsOnFailure akan menggunakan ini untuk melewati baris
     */
    public function onFailure(Failure ...$failures)
    {
        // Biarkan kosong untuk melewati baris secara diam-diam
        // atau log jika perlu: \Log::error('Gagal impor cuti baris: ' . $failures[0]->row());
    }

    /**
     * Helper untuk mengubah nilai tanggal (string atau timestamp Excel) menjadi format Y-m-d.
     */
    private function transformDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                // Coba paksa baca format d/m/Y jika parsing otomatis gagal
                return Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
            } catch (\Exception $e2) {
                return null; // Gagal parsing
            }
        }
    }
}