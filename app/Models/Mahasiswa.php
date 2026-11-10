<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';

    protected $fillable = [
        'nim',
        'nama',
        'jenis_kelamin',
        'email',
        'no_hp',
        'telegram_id',
        'tempat_lahir',
        'tanggal_lahir',
        'tahun_masuk',
        'tahun_keluar',
        'id_fakultas',
        'fakultas',
        'id_sms',
        'prodi',
        'id_kurikulum',
        'id_wali',
        'wali',
        'nidn',
        'ipk',
        'sks_ketuntasan',
        'semester',
        'status',
        'catatan',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'ipk' => 'float',
            'sks_ketuntasan' => 'integer',
            'semester' => 'integer',
            'synced_at' => 'datetime',
        ];
    }

    public const STATUSES = [
        'aktif' => 'Aktif',
        'lulus' => 'Lulus',
        'cuti' => 'Cuti',
        'nonaktif' => 'Non-aktif',
    ];

    public function scopeCari(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('nim', 'like', "%{$term}%")
                ->orWhere('nama', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('prodi', 'like', "%{$term}%");
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['prodi'] ?? null, fn (Builder $q, $v) => $q->where('prodi', $v))
            ->when($filters['fakultas'] ?? null, fn (Builder $q, $v) => $q->where('fakultas', $v))
            ->when($filters['tahun_masuk'] ?? null, fn (Builder $q, $v) => $q->where('tahun_masuk', $v))
            ->when($filters['status'] ?? null, fn (Builder $q, $v) => $q->where('status', $v));
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            'aktif' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
            'lulus' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
            'cuti' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
            default => 'bg-slate-100 text-slate-600 ring-slate-500/20',
        };
    }

    public function angkatan(): string
    {
        $tahun = (string) $this->tahun_masuk;

        return strlen($tahun) >= 4 ? substr($tahun, 0, 4) : ($tahun ?: '-');
    }
}
