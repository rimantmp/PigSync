<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Pen;
use App\Models\Pig;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Form modal penimbangan — dipakai sebagai pola untuk form operasional lain.
 */
class WeighingModalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $branch = Branch::factory()->create(['code' => 'SKM']);
        $pen = Pen::factory()->create(['branch_id' => $branch->id, 'capacity' => 20]);
        Pig::factory()->create(['pen_id' => $pen->id]);

        $this->actingAs($this->admin);
    }

    public function test_halaman_penimbangan_memuat_modal(): void
    {
        $this->get('/weighings')
            ->assertOk()
            ->assertSee('Catat Penimbangan')
            ->assertSee('formModal()', false);
    }

    public function test_timbang_lewat_modal_tersimpan(): void
    {
        $this->post('/weighings', [
            'form_modal' => 'form-weighings',
            'form_back' => url('/weighings'),
            'pig_id' => Pig::firstOrFail()->id,
            'weighed_at' => now()->toDateString(),
            'weight' => 45.5,
            'method' => 'individu',
        ])->assertRedirect(route('weighings.index'));

        $this->assertDatabaseCount('pig_weights', 1);
    }

    public function test_validasi_gagal_membuka_kembali_modal(): void
    {
        $this->post('/weighings', [
            'form_modal' => 'form-weighings',
            'form_back' => url('/weighings'),
            'pig_id' => '',
            'weighed_at' => now()->toDateString(),
            'weight' => 0,
        ]);

        $this->assertDatabaseCount('pig_weights', 0);

        $this->get('/weighings')
            ->assertOk()
            ->assertSee('Periksa kembali isian berikut');
    }
}
