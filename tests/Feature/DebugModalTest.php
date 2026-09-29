<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Pen;
use App\Models\Pig;
use App\Models\PigBreed;
use App\Models\PigPhase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Guard: tag Blade yang bocor mentah ke HTML.
 *
 * Dua pola yang sama-sama senyap — tidak ada error, tidak ada exception, test
 * lain tetap hijau, tapi field atau tombolnya hilang di browser:
 *
 *  1. `@js()` mentah di dalam attribute HTML. Directive-nya tidak diproses,
 *     string yang sampai ke browser adalah "@js($x)", bukan nilainya.
 *  2. `<x-...>` yang lolos ke output. ComponentTagCompiler memindai tag
 *     SEBELUM directive apa pun dikompilasi jadi PHP, jadi `@required(...)`
 *     di dalam sebuah tag komponen lolos dari scan — lalu PHP-nya
 *     disisipkan ke tag yang sudah compiles, hasilnya tag utuh yang
 *     bocor ke HTML dan diabaikan browser.
 */
class DebugModalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Penutup tag PHP, dirakit dari kode char supaya file ini sendiri tidak
     * memuat tag itu secara literal. PHP menutup blok di penutup tag PHP
     * bahkan di dalam string, jadi menuliskannya langsung — bahkan di
     * komentar atau string — sudah cukup untuk memotong file ini.
     */
    private static function phpClose(): string
    {
        return '?'.chr(62);
    }

    public function test_dump(): void
    {
        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);
        $u = User::factory()->create();
        $u->assignRole('Super Admin');
        $b = Branch::factory()->create(['code' => 'SKM']);
        $pen = Pen::factory()->create(['branch_id' => $b->id, 'capacity' => 20]);
        Pig::factory()->create(['pen_id' => $pen->id]);
        PigBreed::create(['code' => 'YSH', 'name' => 'Yorkshire']);
        PigPhase::create(['code' => 'GR', 'name' => 'Grower', 'sort_order' => 1]);
        $this->actingAs($u);

        $pages = [
            '/units', '/branches', '/pens', '/feed-types', '/medicines',
            '/diseases', '/suppliers', '/customers', '/breeds', '/phases', '/areas',
            '/pigs', '/weighings', '/movements', '/health', '/births', '/deaths',
        ];

        $problems = [];

        foreach ($pages as $page) {
            $html = $this->get($page)->assertOk()->getContent();

            // 1. Tidak boleh ada @js() mentah yang belum diproses
            if (preg_match_all('/@js\(/', $html, $m)) {
                $problems[] = $page.': '.count($m[0]).' @js() belum diproses';
            }

            // 2. Tag komponen yang bocor ke HTML
            if (preg_match_all('/<x-[a-z0-9-]+\s/i', $html, $m)) {
                $names = array_values(array_unique(array_map(
                    fn (string $t) => '<'.trim(substr($t, 0, -1)),
                    $m[0]
                )));
                $problems[] = $page.': tag komponen bocor: '.implode(', ', $names);
            }

            // 3. Directive Blade yang belum terkompilasi
            if (preg_match_all('/@(?:required|selected|checked|disabled|readonly)\(/', $html, $m)) {
                $problems[] = $page.': '.count($m[0]).' directive @... belum diproses';
            }

            // 4. Tombol trigger harus punya payload yang benar-benar ter-escape
            if (str_contains($html, 'open-form-modal')
                && ! str_contains($html, 'JSON.parse')) {
                $problems[] = $page.': payload modal tidak ter-serialize';
            }

            // 5. Isi blok `@php` yang bocor jadi teks. Blade menutup blok itu
            //    di penutup tag PHP pertama yang ditemukannya, termasuk yang
            //    ada di dalam komentar `//` — jadi satu baris komentar yang
            //    memuat literal tag PHP cukup untuk mencuri isi blok dan
            //    menampilkannya di halaman.
            //
            //    Penutup tag-nya sengaja tidak ditulis literal di file ini:
            //    PHP menutup blok di tag itu bahkan di dalam string, jadi
            //    menulisnya langsung akan merusak file ini dengan cara yang sama.
            if (str_contains($html, self::phpClose())) {
                $problems[] = $page.': penutup tag PHP bocor ke output';
            }
        }

        fwrite(STDERR, "\n".($problems ? implode("\n", $problems) : 'SEMUA HALAMAN BERSIH')."\n");

        $this->assertSame([], $problems);
    }
}
