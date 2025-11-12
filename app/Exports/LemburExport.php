<?php

namespace App\Exports;

use App\Models\Lembur; // Pastikan Anda use model Lembur
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class LemburExport implements FromCollection, WithHeadings, WithMapping
{
    protected $lemburs;
    protected $rowNumber = 0;

    public function __construct($lemburs)
    {
        // Ini adalah data $lemburs yang SUDAH difilter dari controller
        $this->lemburs = $lemburs;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return $this->lemburs;
    }

    /**
     * Definisikan Header
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Karyawan',
            'No ID Karyawan',
            'Departemen',
            'Tanggal Pengajuan',
            'Tanggal Jam Mulai',
            'Tanggal Jam Selesai',
            'Total Jam Kerja',
            'Deskripsi Kerja',
            'Nama Atasan',
            'Status Pengajuan',
            'Tanggal Status'
        ];
    }

    /**
     * Petakan data untuk setiap baris
     * $lembur adalah satu item dari koleksi $this->lemburs
     */
    public function map($lembur): array
    {
        $this->rowNumber++;
        $user = $lembur->user; // Relasi user
        
        // Relasi approver (menggunakan nama_atasan jika approver_id null)
        $approver = $lembur->approver;
        $nama_atasan = $approver ? $approver->name : ($lembur->nama_atasan ?? '-');
        
        // Terjemahkan Status
        $status_terjemahan = match ($lembur->status_pengajuan) {
            'menunggu' => 'Menunggu',
            'disetujui' => 'Disetujui',
            'ditolak' => 'Ditolak',
            default => ucfirst($lembur->status_pengajuan)
        };
        
        // Format Tanggal Status
        $tanggal_status = $lembur->tgl_status 
            ? Carbon::parse($lembur->tgl_status)->format('d/m/Y H:i') 
            : '-';

        return [
            $this->rowNumber,
            $user ? $user->name : 'User Dihapus',
            $user ? $user->badge_number : '-',
            $user ? $user->departement : '-',
            Carbon::parse($lembur->tgl_pengajuan)->format('d/m/Y'),
            Carbon::parse($lembur->tgl_jam_mulai)->format('d/m/Y H:i'),
            Carbon::parse($lembur->tgl_jam_selesai)->format('d/m/Y H:i'),
            $lembur->total_jam_kerja ?? '-',
            $lembur->deskripsi_kerja ?? '-',
            $nama_atasan,
            $status_terjemahan,
            $tanggal_status
        ];
    }
}