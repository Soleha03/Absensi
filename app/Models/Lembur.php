<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lembur extends Model
{
    use HasFactory;

    protected $table = 'lemburs';

    protected $fillable = [
        'user_id',
        'tgl_pengajuan',
        'approver_id',
        'jam_kerja_id',
        'tgl_jam_mulai',
        'tgl_jam_selesai',
        'nama_atasan',
        'deskripsi_kerja',
        'tanda_tangan',
        'status_pengajuan',
        'total_jam_kerja',
        'tgl_status',
        'tanda_tangan_approver',
    ];

    public function jamKerja()
    {
        return $this->belongsTo(JamKerja::class, 'jam_kerja_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
