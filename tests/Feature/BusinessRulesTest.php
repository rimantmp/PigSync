<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\FeedType;
use App\Models\Pen;
use App\Models\Pig;
use App\Models\Supplier;
use App\Models\User;
use App\Services\BirthService;
use App\Services\DeathService;
use App\Services\MovementService;
use App\Services\PigService;
use App\Services\PopulationService;
use App\Services\PurchaseService;
use App\Support\BranchScope;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BusinessRulesTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private Branch $branchA;

    private Branch $branchB;

    private Pen $penA1;

    private Pen $penA2;

    private PigService $pigs;

    private MovementService $movements;

    private DeathService $deaths;

    private BirthService $births;

    private PopulationService $population;

    private PurchaseService $purchases;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('Super Admin');

        $this->branchA = Branch::factory()->create(['code' => 'SKM']);
        $this->branchB = Branch::factory()->create(['code' => 'BND']);

        $this->penA1 = Pen::factory()->create(['branch_id' => $this->branchA->id, 'code' => 'A1', 'capacity' => 3]);
        $this->penA2 = Pen::factory()->create(['branch_id' => $this->branchA->id, 'code' => 'A2', 'capacity' => 100]);

        $this->pigs = app(PigService::class);
        $this->movements = app(MovementService::class);
        $this->deaths = app(DeathService::class);
        $this->births = app(BirthService::class);
        $this->population = app(PopulationService::class);
        $this->purchases = app(PurchaseService::class);

        $this->actingAs($this->superAdmin);
    }

    private function registerPig(Pen $pen, string $sex = 'betina'): Pig
    {
        return $this->pigs->register([
            'sex' => $sex,
            'birth_date' => now()->subDays(80)->toDateString(),
            'origin_type' => 'internal',
            'pen_id' => $pen->id,
            'initial_weight' => 30,
        ]);
    }

    public function test_registrasi_menghasilkan_kode_unik_dan_populasi_bertambah(): void
    {
        $pig = $this->registerPig($this->penA1);

        $this->assertMatchesRegularExpression('/^SKM-[A-Z]+-\d{4}-\d{4}$/', $pig->code);
        $this->assertSame('aktif', $pig->status);
        $this->assertSame(1, $this->population->penPopulation($this->penA1));
    }

    public function test_perpindahan_memperbarui_populasi_kedua_kandang(): void
    {
        $pig = $this->registerPig($this->penA1);

        $this->movements->move($pig, $this->penA2, ['moved_at' => now()->toDateString(), 'reason' => 'uji']);

        $this->assertSame(0, $this->population->penPopulation($this->penA1));
        $this->assertSame(1, $this->population->penPopulation($this->penA2));
        $this->assertSame($this->penA2->id, $pig->fresh()->pen_id);
    }

    public function test_kematian_mengeluarkan_dari_populasi_dan_tidak_bisa_pindah(): void
    {
        $pig = $this->registerPig($this->penA1);

        $this->deaths->record($pig, ['died_at' => now()->toDateString(), 'cause' => 'penyakit']);

        $this->assertSame('mati', $pig->fresh()->status);
        $this->assertSame(0, $this->population->penPopulation($this->penA1));

        $this->expectException(\DomainException::class);
        $this->movements->move($pig->refresh(), $this->penA2, ['moved_at' => now()->toDateString(), 'reason' => 'x']);
    }

    public function test_babi_mati_tidak_bisa_dijual_atau_dicatat_kematian_lagi(): void
    {
        $pig = $this->registerPig($this->penA1);
        $this->deaths->record($pig, ['died_at' => now()->toDateString(), 'cause' => 'penyakit']);

        $this->expectException(\DomainException::class);
        $this->deaths->record($pig->fresh(), ['died_at' => now()->toDateString(), 'cause' => 'penyakit']);
    }

    public function test_kandang_penuh_menolak_penempatan(): void
    {
        $this->penA1->update(['capacity' => 1]);
        $this->registerPig($this->penA1);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('penuh');

        $this->registerPig($this->penA1);
    }

    public function test_registrasi_wajib_kandang(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->pigs->register([
            'sex' => 'betina',
            'birth_date' => now()->subDays(80)->toDateString(),
            'origin_type' => 'internal',
            'pen_id' => 999999,
        ]);
    }

    public function test_kelahiran_mendaftarkan_piglet_dan_menambah_populasi(): void
    {
        $sow = $this->registerPig($this->penA2);
        $sow->update(['status' => 'bunting']);

        $birth = $this->births->farrow($sow, [
            'farrowed_at' => now()->toDateString(),
            'born_alive' => 3,
            'born_dead' => 1,
            'pen_id' => $this->penA2->id,
        ]);

        $this->assertSame(3, $birth->born_alive);
        $this->assertSame('menyusui', $sow->fresh()->status);
        $this->assertSame(4, $this->population->penPopulation($this->penA2)); // sow + 3 piglet
        $this->assertSame(3, Pig::where('origin_ref', $birth->id)->count());
    }

    public function test_kelahiran_tanpa_induk_diperbolehkan(): void
    {
        $birth = $this->births->farrow(null, [
            'farrowed_at' => now()->toDateString(),
            'born_alive' => 2,
            'pen_id' => $this->penA2->id,
        ]);

        $this->assertNull($birth->sow_id);
        $this->assertSame(2, $this->population->penPopulation($this->penA2));
    }

    public function test_sow_mati_tidak_bisa_melahirkan(): void
    {
        $sow = $this->registerPig($this->penA2);
        $this->deaths->record($sow, ['died_at' => now()->toDateString(), 'cause' => 'penyakit']);

        $this->expectException(\DomainException::class);
        $this->births->farrow($sow->fresh(), ['farrowed_at' => now()->toDateString(), 'born_alive' => 1, 'pen_id' => $this->penA2->id]);
    }

    public function test_isolasi_cabang_membatasi_scope_user(): void
    {
        $manager = User::factory()->create(['branch_scope' => [$this->branchA->id]]);
        Role::create(['name' => 'Manajer Cabang', 'guard_name' => 'web']);
        $manager->assignRole('Manajer Cabang');

        $this->actingAs($manager);

        $response = $this->get('/pigs?branch_id='.$this->branchB->id);
        $response->assertOk();

        $penB = Pen::factory()->create(['branch_id' => $this->branchB->id]);
        $pigB = Pig::factory()->create(['pen_id' => $penB->id]);

        // Query scope: babi cabang B tidak muncul untuk scope cabang A
        $html = $this->get('/pigs')->getContent();
        $this->assertStringNotContainsString($pigB->code, $html);
    }

    public function test_scope_kosong_ditolak_jadi_bukan_akses_semua_cabang(): void
    {
        // Regression: blank([]) dulu membuat branch_scope kosong teraca "semua cabang".
        $user = User::factory()->create(['branch_scope' => []]);
        Role::create(['name' => 'Staf Gudang', 'guard_name' => 'web']);
        $user->assignRole('Staf Gudang');

        $this->assertSame([], BranchScope::ids($user));
        $this->assertFalse(BranchScope::can($user, $this->branchA->id));

        $this->actingAs($user);
        $this->get('/pigs')->assertForbidden();
    }

    public function test_penerimaan_kumulatif_tidak_melebihi_jumlah_po(): void
    {
        $feed = FeedType::create(['code' => 'F-01', 'name' => 'Pakan Grower']);
        $supplier = Supplier::create(['code' => 'S-01', 'name' => 'CV Pakan']);

        $po = $this->purchases->createOrder($this->branchA->id, $supplier->id, null, [
            ['item_type' => 'feed', 'item_id' => $feed->id, 'qty' => 100, 'price' => 1000],
        ]);

        $poItemId = $po->items->first()->id;

        // Terima 60 dari 100, lalu 60 lagi harus ditolak (total 120 > 100).
        $this->purchases->receive($po, ['received_qty' => [$poItemId => 60]]);

        $this->expectException(\DomainException::class);

        $this->purchases->receive($po, ['received_qty' => [$poItemId => 60]]);
    }
}
