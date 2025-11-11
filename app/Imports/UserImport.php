<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date; // <-- 1. TAMBAHKAN INI
use Throwable;

class UserImport implements 
    ToModel, 
    WithHeadingRow,
    WithValidation
{
    /**
     * Tentukan baris header.
     * @return int
     */
    public function headingRow(): int
    {
        return 3;
    }

    /**
     * Memetakan data dari baris spreadsheet ke User model.
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Cek jika kolom esensial (seperti emp_no atau name) kosong, lewati baris
        if (empty($row['emp_no']) || empty($row['name'])) {
            return null;
        }

        return new User([
            // --- Data dari CSV ---
            'name'         => $row['name'],
            'badge_number' => $row['emp_no'],
            'jabatan'      => $row['position'],
            'status'       => $row['contract_permanent'],
            
            // 3. GUNAKAN HELPER TRANSFORMASI
            'join_date'    => $this->transformDate($row['join_date']), 

            // --- Data Default (Wajib Diisi) ---
            'email'        => strtolower($row['emp_no']) . '@vmes.com', 
            'password'     => Hash::make('password'), 
            'role'         => 'karyawan',

            // --- Data Opsional (Null jika tidak ada di CSV) ---
            'departement'  => $row['department'] ?? 'Office',
            'No_HP'        => null,
            'alamat'       => null,
            'foto'         => null,
        ]);
    }

    /**
     * Aturan validasi untuk setiap baris.
     * @return array
     */
    public function rules(): array
    {
        return [
            // Pastikan badge_number (dari kolom 'Emp. No') unik di tabel users
            'emp_no' => 'required|unique:users,badge_number',
            'name' => 'required|string',
            'position' => 'nullable|string',
            'contract_permanent' => 'nullable|string',

            // 2. HAPUS VALIDASI 'date_format'
            'join_date' => 'nullable', 
        ];
    }

    /**
     * Menangani error validasi.
     * @param Throwable $e
     */
    public function onError(Throwable $e)
    {
        // \Log::error('Gagal impor baris: ' . $e->getMessage());
    }

    // 4. TAMBAHKAN METHOD HELPER BARU INI
    /**
     * Mengubah nilai tanggal (string atau timestamp Excel) menjadi format Y-m-d.
     *
     * @param string|int|null $value
     * @return string|null
     */
    private function transformDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Jika nilainya angka, anggap itu adalah timestamp Excel
            if (is_numeric($value)) {
                // Gunakan helper bawaan Maatwebsite/Excel untuk konversi
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            
            // Jika ini string, biarkan Carbon (pustaka tanggal Laravel)
            // yang mem-parsingnya secara cerdas (dia bisa menangani Y-m-d, d-m-Y, m/d/Y, dll)
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
            
        } catch (\Exception $e) {
            // Jika parsing gagal karena format tidak dikenal, kembalikan null
            return null;
        }
    }
}