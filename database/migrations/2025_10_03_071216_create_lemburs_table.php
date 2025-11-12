<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('lemburs', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('approver_id')->nullable()->constrained('users')->onDelete('set null');
        $table->date('tgl_pengajuan');
        $table->time('tgl_jam_mulai');
        $table->time('tgl_jam_selesai');
        $table->text('deskripsi_kerja');
        $table->text('nama_atasan');
        $table->string('tanda_tangan');
        $table->foreignId('jam_kerja_id')->constrained('jam_kerjas')->onDelete('cascade');
        $table->integer('total_jam_kerja');
        $table->enum('status_pengajuan', ['menunggu', 'disetujui', 'ditolak'])->default('menunggu');
        $table->datetime('tgl_status')->nullable();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('lemburs');
}

};
