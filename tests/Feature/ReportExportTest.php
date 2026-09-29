<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Death;
use App\Models\Pen;
use App\Models\Pig;
use App\Models\User;
use App\Support\ReportBuilder;
use App\Support\ReportFilters;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportExportTest extends TestCase
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

        $this->branch = Branch::factory()->create(['code' => 'SKM', 'name' => 'Cabang SDM']);
        $this->pen = Pen::factory()->create(['branch_id' => $this->branch->id, 'name' => 'Kandang A1']);

        $this->actingAs($this->admin);
    }

    public static function reportProvider(): array
    {
        return array_map(fn ($r) => [$r], ReportBuilder::REPORTS);
    }

    #[DataProvider('reportProvider')]
    public function test_semua_laporan_bisa_ke_pdf(string $report): void
    {
        $response = $this->get("/reports/{$report}/export/pdf");

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');

        // dompdf gagal diam-diam sering balik halaman error, bukan PDF.
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    #[DataProvider('reportProvider')]
    public function test_semua_laporan_bisa_ke_csv_dengan_bom(string $report): void
    {
        $response = $this->get("/reports/{$report}/export/csv");

        $response->assertOk();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $response->streamedContent() ?: $response->getContent());
    }

    public function test_laporan_tidak_dikenal_ditolak(): void
    {
        $this->get('/reports/bukan-laporan/export/csv')->assertNotFound();
        $this->get('/reports/bukan-laporan/export/pdf')->assertNotFound();
    }

    public function test_filter_tanggal_diterapkan_ke_export(): void
    {
        $pig = Pig::factory()->create(['pen_id' => $this->pen->id]);

        Death::create([
            'pig_id' => $pig->id,
            'pen_id' => $this->pen->id,
            'died_at' => now()->subDays(5),
            'cause' => 'Penyakit Dalam',
            'estimated_loss' => 50000,
        ]);

        // Rentang yang tidak mencakup kematian -> CSV harus kosong (tanpa header data)
        $csv = $this->get('/reports/deaths/export/csv?from='.now()->subDay()->toDateString().'&to='.now()->toDateString())
            ->streamedContent();

        $this->assertStringNotContainsString('Penyakit Dalam', $csv);

        // Rentang yang mencakup -> data muncul
        $csv = $this->get('/reports/deaths/export/csv?from='.now()->subDays(10)->toDateString())
            ->streamedContent();

        $this->assertStringContainsString('Penyakit Dalam', $csv);
    }

    public function test_header_csv_sama_dengan_kolom_pdf(): void
    {
        $csv = $this->get('/reports/deaths/export/csv')->streamedContent();

        // Lepas BOM, ambil baris header
        $firstLine = strtok(substr($csv, 3), "\n");

        foreach ($this->deathColumns() as $label) {
            $this->assertStringContainsString($label, $firstLine);
        }
    }

    public function test_branch_scope_membatasi_export(): void
    {
        $branchB = Branch::factory()->create(['code' => 'BND', 'name' => 'Cabang Barat']);
        $penB = Pen::factory()->create(['branch_id' => $branchB->id, 'name' => 'KandangZ']);

        $manager = User::factory()->create(['branch_scope' => [$this->branch->id]]);
        Role::create(['name' => 'Manajer Cabang', 'guard_name' => 'web']);
        $manager->assignRole('Manajer Cabang');

        $this->actingAs($manager);

        $csv = $this->get('/reports/population/export/csv')->streamedContent();

        $this->assertStringNotContainsString('KandangZ', $csv);
        $this->assertStringContainsString('Kandang A1', $csv);
    }

    /**
     * @return array<int, string>
     */
    private function deathColumns(): array
    {
        $table = app(ReportBuilder::class)->build(
            'deaths',
            new ReportFilters
        );

        return $table->labels();
    }
}
