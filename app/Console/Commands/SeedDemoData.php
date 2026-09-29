<?php

namespace App\Console\Commands;

use Database\Seeders\DemoDataSeeder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedDemoData extends Command
{
    protected $signature = 'app:seed-demo
                            {--fresh : Jalankan migrate:fresh lebih dulu (menghapus semua data)}';

    protected $description = 'Isi database dengan data demo untuk preview UI';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            if (! $this->confirm('PERINGATAN: migrate:fresh akan menghapus SEMUA data. Lanjutkan?')) {
                $this->warn('Dibatalkan.');

                return self::SUCCESS;
            }

            // --seed wajib: DemoDataSeeder bergantung pada role & user admin
            // yang dibuat DatabaseSeeder, jadi keduanya harus jalan.
            Artisan::call('migrate:fresh', ['--force' => true, '--seed' => true], $this->getOutput());
        }

        $this->components->info('Menyiapkan data demo…');

        // Jangan pakai callSilent: kalau seeder gagal, pesan errornya
        // ikut hilang dan yang tampil hanya "Data demo siap".
        $exit = $this->call('db:seed', [
            '--class' => DemoDataSeeder::class,
            '--force' => true,
        ]);

        if ($exit !== self::SUCCESS) {
            $this->components->error('Data demo gagal dibuat. Lihat pesan di atas.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->components->info('Data demo siap. Login: admin@sistemkandang.test / password');

        return self::SUCCESS;
    }
}
