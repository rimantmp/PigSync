<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\FeedType;
use App\Models\Pen;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ReportPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->branch = Branch::factory()->create(['code' => 'SKM', 'name' => 'Cabang SDM']);
        Pen::factory()->create(['branch_id' => $this->branch->id, 'name' => 'Kandang A1']);

        $this->actingAs($this->admin);
    }

    public static function pageProvider(): array
    {
        return [
            ['/reports'],
            ['/reports/population'],
            ['/reports/growth'],
            ['/reports/deaths'],
            ['/reports/movements'],
            ['/reports/health'],
            ['/purchase'],
            ['/purchase/order'],
            ['/purchase/receipt'],
            ['/purchase/invoice'],
            ['/payments/create'],
            ['/purchase-requests'],
            ['/purchase-requests/create'],
        ];
    }

    #[DataProvider('pageProvider')]
    public function test_halaman_render(string $url): void
    {
        $this->get($url)->assertOk();
    }

    public function test_halaman_laporan_punya_link_export_csv_dan_pdf(): void
    {
        foreach (['population', 'growth', 'deaths', 'movements', 'health'] as $report) {
            $html = $this->get("/reports/{$report}")->getContent();

            $this->assertStringContainsString("/reports/{$report}/export/csv", $html, "link CSV {$report}");
            $this->assertStringContainsString("/reports/{$report}/export/pdf", $html, "link PDF {$report}");
        }
    }

    public function test_form_po_memakai_select_item_bukan_input_angka(): void
    {
        $html = $this->get('/purchase/order')->getContent();

        // Nilai item diambil dari master, bukan diketik bebas.
        $this->assertStringNotContainsString('placeholder="item id"', $html);
        $this->assertStringContainsString("items['+i+'][item_id]", $html);
    }

    public function test_index_pembelian_punya_link_pdf_dokumen(): void
    {
        $feed = FeedType::create(['code' => 'F-01', 'name' => 'Pakan Grower']);
        $supplier = Supplier::create(['code' => 'S-01', 'name' => 'CV Sumber Pakan']);

        $order = app(PurchaseService::class)->createOrder($this->branch->id, $supplier->id, null, [
            ['item_type' => 'feed', 'item_id' => $feed->id, 'qty' => 10, 'price' => 5000],
        ]);

        $html = $this->get('/purchase')->getContent();

        $this->assertStringContainsString(route('purchase.order.pdf', $order), $html);
    }
}
