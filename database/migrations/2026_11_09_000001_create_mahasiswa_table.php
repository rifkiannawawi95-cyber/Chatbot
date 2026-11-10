<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();

            // Data dari SSO UNUJA
            $table->string('nim', 30)->unique();
            $table->string('nama', 150);
            $table->char('jenis_kelamin', 1)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('no_hp', 30)->nullable();
            $table->string('telegram_id', 30)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('tahun_masuk', 10)->nullable();
            $table->string('tahun_keluar', 10)->nullable();
            $table->string('id_fakultas', 20)->nullable();
            $table->string('fakultas', 100)->nullable();
            $table->string('id_sms', 50)->nullable();
            $table->string('prodi', 100)->nullable();
            $table->string('id_kurikulum', 30)->nullable();
            $table->string('id_wali', 30)->nullable();
            $table->string('wali', 150)->nullable();
            $table->string('nidn', 30)->nullable();
            $table->decimal('ipk', 4, 2)->nullable();
            $table->unsignedSmallInteger('sks_ketuntasan')->nullable();
            $table->unsignedSmallInteger('semester')->nullable();

            // Dikelola admin (tidak ditimpa saat sinkronisasi)
            $table->string('status', 20)->default('aktif');
            $table->text('catatan')->nullable();

            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index('nama');
            $table->index('prodi');
            $table->index('fakultas');
            $table->index('tahun_masuk');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
