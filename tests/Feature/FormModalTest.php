<?php

namespace Tests\Feature;

use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Alur form modal: tambah & edit master data tanpa halaman form terpisah.
 */
class FormModalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->actingAs($this->admin);
    }

    public function test_halaman_master_memuat_modal_dengan_field_form(): void
    {
        $html = $this->get('/units')->assertOk()->getContent();

        // Modal + state Alpine-nya harus ada di halaman index.
        $this->assertStringContainsString('formModal()', $html);
        $this->assertStringContainsString('open-form-modal', $html);
        $this->assertStringContainsString('form_modal', $html);
        $this->assertStringContainsString('name="code"', $html);
        $this->assertStringContainsString('name="name"', $html);
    }

    public function test_tombol_edit_membawa_data_baris_ke_modal(): void
    {
        $unit = Unit::create(['code' => 'kg', 'name' => 'Kilogram', 'conversion' => 1]);

        $html = $this->get('/units')->assertOk()->getContent();

        $edit = collect($this->modalPayloads($html))
            ->firstWhere('mode', 'edit');

        $this->assertNotNull($edit, 'Tidak ada payload bermode edit di halaman.');
        $this->assertSame('form-units', $edit['modal']);
        $this->assertSame('Edit Satuan', $edit['title']);
        // URL update ikut ter-serialize ke payload modal.
        $this->assertStringEndsWith('/units/'.$unit->id, $edit['action']);
        $this->assertSame('kg', $edit['values']['code']);
        $this->assertSame('Kilogram', $edit['values']['name']);
    }

    /**
     * Payload modal yang ter-serialize di halaman, sudah di-decode.
     *
     * Assertion di sini tidak boleh cocok dengan bentuk string mentah:
     * `alpineData()` mengembalikan `JSON.parse('...')` yang isinya ter-escape
     * (`"`) dan kutipnya jadi `&#039;` setelah Blade. Cocokkan substring
     * seperti "mode: 'edit'" tidak akan pernah kena dan hanya memaksa
     * someone melonggarkan escape-nya. Decode dulu, baru nilai isinya.
     *
     * Perlu dua lapis decode: isinya string literal JS (`\\\/` = backslash
     * harfiah + slash, untuk `"` yang di-decode JSON.parse), jadi
     * html_entity_decode -> json_decode (buka literal JS) -> json_decode (JSON).
     *
     * @return array<int, array<string, mixed>>
     */
    private function modalPayloads(string $html): array
    {
        preg_match_all('/JSON\.parse\(&#039;(.*?)&#039;\)/s', $html, $matches);

        $payloads = [];

        foreach ($matches[1] as $raw) {
            $json = json_decode('"'.html_entity_decode($raw, ENT_QUOTES).'"', true);

            if (is_string($json)) {
                $decoded = json_decode($json, true);

                if (is_array($decoded)) {
                    $payloads[] = $decoded;
                }
            }
        }

        return $payloads;
    }

    public function test_tambah_master_lewat_modal_tersimpan(): void
    {
        $response = $this->post('/units', [
            'form_modal' => 'form-units',
            'form_back' => url('/units'),
            'code' => 'pcs',
            'name' => 'Buah',
            'conversion' => 1,
        ]);

        $response->assertRedirect(route('units.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('units', ['code' => 'pcs', 'name' => 'Buah']);
    }

    public function test_validasi_gagal_membuka_kembali_modal_dengan_error(): void
    {
        $this->post('/units', [
            'form_modal' => 'form-units',
            'form_back' => url('/units'),
            // name kosong -> validasi gagal
            'code' => 'kg',
            'name' => '',
        ]);

        $this->assertDatabaseCount('units', 0);

        // Flash bersifat sekali-pakai: assertion pada response POST akan
        // consume-nya. Jadi langsung render halaman tujuan.
        $html = $this->get('/units')->assertOk()->getContent();

        $this->assertStringContainsString('if (true) { open = true;', $html);
        $this->assertStringContainsString('Periksa kembali isian berikut', $html);
    }

    public function test_kode_duplikat_ditolak_saat_tambah(): void
    {
        Unit::create(['code' => 'kg', 'name' => 'Kilogram']);

        $response = $this->from('/units')->post('/units', [
            'form_modal' => 'form-units',
            'form_back' => url('/units'),
            'code' => 'kg',
            'name' => 'Duplikat',
        ]);

        $response->assertSessionHasErrors(['code']);
        $this->assertDatabaseCount('units', 1);
    }

    public function test_error_yang_tersimpan_memakai_flash_form_keys(): void
    {
        // Dua field kosong -> dua kunci error harus ikut ter-flash supaya
        // modal bisa menampilkan keduanya.
        $this->post('/units', [
            'form_modal' => 'form-units',
            'form_back' => url('/units'),
            'code' => '',
            'name' => '',
        ]);

        $keys = session('form_keys');

        $this->assertIsArray($keys);
        $this->assertContains('code', $keys);
        $this->assertContains('name', $keys);
    }

    public function test_edit_master_lewat_modal_memperbarui(): void
    {
        $unit = Unit::create(['code' => 'kg', 'name' => 'Kilogram']);

        $response = $this->put('/units/'.$unit->id, [
            'form_modal' => 'form-units',
            'form_back' => url('/units'),
            'code' => 'kg',
            'name' => 'Kilogram (diperbarui)',
        ]);

        $response->assertRedirect(route('units.index'));
        $this->assertDatabaseHas('units', ['id' => $unit->id, 'name' => 'Kilogram (diperbarui)']);
    }

    public function test_error_dari_form_lain_tidak_membuka_modal_ini(): void
    {
        // Error milik branches tidak boleh ikut membuka modal units.
        $this->post('/branches', [
            'form_modal' => 'form-branches',
            'form_back' => url('/branches'),
            'code' => '',
            'name' => '',
        ]);

        $html = $this->get('/units')->assertOk()->getContent();

        $this->assertStringContainsString('if (false) { open = true;', $html);
    }

    public function test_form_back_mengarahkan_kembali_ke_halaman_asal(): void
    {
        $response = $this->post('/units', [
            'form_modal' => 'form-units',
            'form_back' => url('/units'),
            'code' => '',
            'name' => '',
        ]);

        $response->assertRedirect(url('/units'));
    }
}
