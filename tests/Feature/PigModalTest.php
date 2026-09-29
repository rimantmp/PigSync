<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Pen;
use App\Models\Pig;
use App\Models\PigBreed;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Form modal untuk data babi: registrasi & edit.
 */
class PigModalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Branch $branch;

    private Pen $pen;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->branch = Branch::factory()->create(['code' => 'SKM']);
        $this->pen = Pen::factory()->create([
            'branch_id' => $this->branch->id,
            'capacity' => 20,
        ]);

        PigBreed::create(['code' => 'YSH', 'name' => 'Yorkshire']);

        $this->actingAs($this->admin);
    }

    public function test_halaman_daftar_babi_memuat_modal(): void
    {
        $html = $this->get('/pigs')->assertOk()->getContent();

        $this->assertStringContainsString('formModal()', $html);
        $this->assertStringContainsString('Registrasi Ternak', $html);
        $this->assertStringContainsString('name="pen_id"', $html);
        $this->assertStringContainsString('name="birth_date"', $html);
    }

    public function test_registrasi_lewat_modal_tersimpan_dan_kembali_ke_daftar(): void
    {
        $response = $this->post('/pigs', [
            'form_modal' => 'form-pigs',
            'form_back' => url('/pigs'),
            'sex' => 'jantan',
            'origin_type' => 'internal',
            'birth_date' => now()->subDays(60)->toDateString(),
            'pen_id' => $this->pen->id,
            'initial_weight' => 25,
        ]);

        // Redirect ke pigs.show hanya untuk form halaman penuh; dari modal
        // user harus tetap di daftar.
        $response->assertRedirect(route('pigs.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseCount('pigs', 1);
    }

    public function test_validasi_gagal_membuka_kembali_modal_babi(): void
    {
        $this->post('/pigs', [
            'form_modal' => 'form-pigs',
            'form_back' => url('/pigs'),
            'sex' => 'tidak-valid',
            'origin_type' => 'internal',
            'birth_date' => now()->toDateString(),
            'pen_id' => $this->pen->id,
        ]);

        $this->assertDatabaseCount('pigs', 0);

        $html = $this->get('/pigs')->assertOk()->getContent();
        $this->assertStringContainsString('if (true) { open = true;', $html);
    }

    public function test_edit_lewat_modal_tidak_kirim_field_registrasi(): void
    {
        $pig = Pig::factory()->create(['pen_id' => $this->pen->id]);

        // Edit hanya mengirim field edit; field registrasi (birth_date,
        // pen_id) tidak boleh ikut, kalau tidak penempatan bisa berubah.
        $this->put('/pigs/'.$pig->id, [
            'form_modal' => 'form-pigs',
            'form_back' => url('/pigs'),
            'sex' => 'betina',
            'tag_id' => 'TAG-999',
        ]);

        $pig->refresh();

        $this->assertSame('betina', $pig->sex);
        $this->assertSame('TAG-999', $pig->tag_id);
        $this->assertSame($this->pen->id, $pig->pen_id);
    }

    public function test_update_babi_dari_modal_kembali_ke_daftar(): void
    {
        $pig = Pig::factory()->create(['pen_id' => $this->pen->id]);

        $response = $this->put('/pigs/'.$pig->id, [
            'form_modal' => 'form-pigs',
            'form_back' => url('/pigs'),
            'sex' => 'jantan',
        ]);

        $response->assertRedirect(route('pigs.index'));
    }

    public function test_form_penuh_tetap_menuju_halaman_detail(): void
    {
        // Tanpa form_modal, perilaku lama harus dipertahankan.
        $response = $this->post('/pigs', [
            'sex' => 'jantan',
            'origin_type' => 'internal',
            'birth_date' => now()->subDays(30)->toDateString(),
            'pen_id' => $this->pen->id,
        ]);

        $pig = Pig::firstOrFail();

        $response->assertRedirect(route('pigs.show', $pig));
    }
}
