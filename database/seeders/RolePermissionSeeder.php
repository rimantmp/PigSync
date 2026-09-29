<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    /**
     * Daftar role sesuai PRD §6.1.
     *
     * @var array<string, string>
     */
    private array $roles = [
        'Super Admin' => 'Semua modul + kelola pengguna/role/scope',
        'Admin Pusat' => 'Semua modul operasional, master data global',
        'Manajer Peternakan' => 'Monitoring KPI + approval transaksi besar',
        'Manajer Cabang' => 'Operasional harian satu cabang',
        'Supervisor Kandang' => 'Validasi input petugas, atur jadwal',
        'Petugas Kandang' => 'Input harian lapangan',
        'Petugas Kesehatan' => 'Kesehatan, vaksinasi, pengobatan',
        'Petugas Gudang' => 'Stok, penerimaan, pengeluaran, opname',
        'Bagian Pembelian' => 'PR, PO, penerimaan, pemasok',
        'Bagian Penjualan' => 'Penjualan, pelanggan',
        'Bagian Keuangan' => 'Biaya, pendapatan, pembayaran',
        'Auditor' => 'Baca saja',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach ($this->roles as $name => $desc) {
            Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }
}
