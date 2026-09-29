<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Equipment;
use App\Models\FeedType;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Models\PurchaseRequest;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Support\ItemCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchaseDocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Branch $branch;

    private PurchaseService $purchases;

    private PurchaseOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Super Admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Super Admin');

        $this->branch = Branch::factory()->create(['code' => 'SKM', 'name' => 'Cabang SDM']);

        $feed = FeedType::create(['code' => 'F-01', 'name' => 'Pakan Grower']);
        $supplier = Supplier::create(['code' => 'S-01', 'name' => 'CV Sumber Pakan']);

        $this->purchases = app(PurchaseService::class);

        $this->order = $this->purchases->createOrder($this->branch->id, $supplier->id, null, [
            ['item_type' => 'feed', 'item_id' => $feed->id, 'qty' => 100, 'price' => 25000],
        ]);

        $this->actingAs($this->admin);
    }

    public function test_pdf_purchase_order_valid(): void
    {
        $response = $this->get(route('purchase.order.pdf', $this->order));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_pdf_invoice_valid(): void
    {
        $invoice = $this->purchases->invoice($this->order);

        $response = $this->get(route('purchase.invoice.pdf', $invoice));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_pdf_struk_penerimaan_valid(): void
    {
        $this->purchases->receive($this->order, [
            'received_qty' => [$this->order->items->first()->id => 40],
        ]);

        $receipt = PurchaseReceipt::latest('id')->first();

        $response = $this->get(route('purchase.receipt.pdf', $receipt));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_struk_menampilkan_qty_penerimaan_khusus_bukan_kumulatif(): void
    {
        $itemId = $this->order->items->first()->id;

        // Terima 40, lalu 25 lagi pada PO yang sama.
        $this->purchases->receive($this->order, ['received_qty' => [$itemId => 40]]);
        $this->purchases->receive($this->order, ['received_qty' => [$itemId => 25]]);

        $first = PurchaseReceipt::orderBy('id')->first();
        $second = PurchaseReceipt::orderByDesc('id')->first();

        // Setiap struk menyimpan baris penerimaannya sendiri.
        $this->assertSame('40.00', $first->items->first()->qty);
        $this->assertSame('25.00', $second->items->first()->qty);

        // Totale kumulatif di PO tetap 65 dari 100.
        $this->assertSame('65.00', $this->order->items->first()->refresh()->received_qty);

        $this->get(route('purchase.receipt.pdf', $first))->assertOk();
        $this->get(route('purchase.receipt.pdf', $second))->assertOk();
    }

    public function test_dokumen_cabang_lain_tidak_bisa_diakses(): void
    {
        $branchB = Branch::factory()->create(['code' => 'BND', 'name' => 'Cabang Barat']);

        $orderB = PurchaseOrder::create([
            'branch_id' => $branchB->id,
            'supplier_id' => $this->order->supplier_id,
            'po_date' => now()->toDateString(),
            'po_number' => 'PO-B-001',
            'status' => 'draft',
            'total' => 100,
        ]);

        $invoiceB = PurchaseInvoice::create([
            'po_id' => $orderB->id,
            'invoice_number' => 'INV-B-001',
            'invoice_date' => now()->toDateString(),
            'total' => 100,
        ]);

        $manager = User::factory()->create(['branch_scope' => [$this->branch->id]]);
        Role::create(['name' => 'Manajer Cabang', 'guard_name' => 'web']);
        $manager->assignRole('Manajer Cabang');

        $this->actingAs($manager);

        $this->get(route('purchase.order.pdf', $orderB))->assertNotFound();
        $this->get(route('purchase.invoice.pdf', $invoiceB))->assertNotFound();
    }

    public function test_equipment_bisa_dipakai_sebagai_item_po(): void
    {
        $equipment = Equipment::create([
            'code' => 'EQ-01',
            'name' => 'Tempat Pakan',
            'category' => 'kandang',
        ]);

        $order = $this->purchases->createOrder($this->branch->id, $this->order->supplier_id, null, [
            ['item_type' => 'equipment', 'item_id' => $equipment->id, 'qty' => 5, 'price' => 75000],
        ]);

        $names = ItemCatalog::namesFor($order->items);

        $this->assertSame('Tempat Pakan', $names[ItemCatalog::key('equipment', $equipment->id)]);
        $this->get(route('purchase.order.pdf', $order))->assertOk();
    }

    public function test_store_penerimaan_menolak_po_cabang_lain(): void
    {
        $branchB = Branch::factory()->create(['code' => 'BND', 'name' => 'Cabang Barat']);

        $orderB = PurchaseOrder::create([
            'branch_id' => $branchB->id,
            'supplier_id' => $this->order->supplier_id,
            'po_date' => now()->toDateString(),
            'po_number' => 'PO-B-002',
            'status' => 'draft',
            'total' => 100,
        ]);

        $this->scopedManager();

        // Data sengaja valid agar penolakan datang dari branch scope,
        // bukan dari aturan validasi.
        $this->post(route('purchase.receipt.store'), [
            'po_id' => $orderB->id,
            'received_at' => now()->toDateString(),
            'received_qty' => [1 => 10],
        ])->assertNotFound();

        $this->assertDatabaseCount('purchase_receipts', 0);
    }

    public function test_store_invoice_menolak_po_cabang_lain(): void
    {
        $branchB = Branch::factory()->create(['code' => 'BND', 'name' => 'Cabang Barat']);

        $orderB = PurchaseOrder::create([
            'branch_id' => $branchB->id,
            'supplier_id' => $this->order->supplier_id,
            'po_date' => now()->toDateString(),
            'po_number' => 'PO-B-003',
            'status' => 'received',
            'total' => 100,
        ]);

        $this->scopedManager();

        $this->post(route('purchase.invoice.store'), [
            'po_id' => $orderB->id,
            'invoice_date' => now()->toDateString(),
            'total' => 100,
        ])->assertNotFound();

        $this->assertDatabaseCount('purchase_invoices', 0);
    }

    public function test_store_pembayaran_menolak_invoice_cabang_lain(): void
    {
        $branchB = Branch::factory()->create(['code' => 'BND', 'name' => 'Cabang Barat']);

        $orderB = PurchaseOrder::create([
            'branch_id' => $branchB->id,
            'supplier_id' => $this->order->supplier_id,
            'po_date' => now()->toDateString(),
            'po_number' => 'PO-B-004',
            'status' => 'invoiced',
            'total' => 100,
        ]);

        $invoiceB = PurchaseInvoice::create([
            'po_id' => $orderB->id,
            'invoice_number' => 'INV-B-002',
            'invoice_date' => now()->toDateString(),
            'total' => 100,
        ]);

        $this->scopedManager();

        $this->post(route('payments.store'), [
            'payable_type' => 'invoice',
            'payable_id' => $invoiceB->id,
            'amount' => 100,
            'method' => 'transfer',
            'paid_at' => now()->toDateString(),
        ])->assertNotFound();

        $this->assertDatabaseCount('payments', 0);
        $this->assertSame('belum_bayar', $invoiceB->refresh()->status);
    }

    public function test_item_id_harus_ada_di_master(): void
    {
        $feed = FeedType::create(['code' => 'F-02', 'name' => 'Pakan Starter']);

        $this->actingAs($this->admin);

        // id yang tidak ada di master feed
        $this->post(route('purchase.order.store'), [
            'branch_id' => $this->branch->id,
            'supplier_id' => $this->order->supplier_id,
            'po_date' => now()->toDateString(),
            'items' => [
                ['item_type' => 'feed', 'item_id' => 999999, 'qty' => 5, 'price' => 1000],
            ],
        ])->assertSessionHasErrors('items.0.item_id');

        $this->assertDatabaseCount('purchase_orders', 1); // hanya PO dari setUp

        // id yang benar
        $this->post(route('purchase.order.store'), [
            'branch_id' => $this->branch->id,
            'supplier_id' => $this->order->supplier_id,
            'po_date' => now()->toDateString(),
            'items' => [
                ['item_type' => 'feed', 'item_id' => $feed->id, 'qty' => 5, 'price' => 1000],
            ],
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('purchase_orders', 2);
    }

    public function test_approve_pr_cabang_lain_ditolak(): void
    {
        $branchB = Branch::factory()->create(['code' => 'BND', 'name' => 'Cabang Barat']);

        $prB = PurchaseRequest::create([
            'branch_id' => $branchB->id,
            'request_date' => now()->toDateString(),
            'status' => 'draft',
        ]);

        $this->scopedManager();

        $this->post(route('purchase-requests.approve', $prB))->assertNotFound();
        $this->assertSame('draft', $prB->refresh()->status);
    }

    public function test_pdf_po_memuat_nama_item_dan_kop_surat(): void
    {
        // Teks dalam PDF ter-kompresi & hex-encoded UTF-16, jadi assertion
        // dilakukan pada view yang jadi sumber dompdf, bukan pada file PDF.
        $html = view('pdf.purchase-order', $this->documentViewData('pdf.purchase-order'))->render();

        $this->assertStringContainsString('PURCHASE ORDER', $html);
        $this->assertStringContainsString('Pakan Grower', $html);
        $this->assertStringContainsString('CV Sumber Pakan', $html);
        $this->assertStringContainsString('Cabang SDM', $html);
        $this->assertStringContainsString('2.500.000', $html); // 100 x 25.000
    }

    public function test_pdf_struk_menampilkan_qty_yang_diterima_saat_itu(): void
    {
        $itemId = $this->order->items->first()->id;
        $this->purchases->receive($this->order, ['received_qty' => [$itemId => 40]]);

        $receipt = PurchaseReceipt::latest('id')->first();

        $html = view('pdf.purchase-receipt', $this->receiptViewData($receipt))->render();

        $this->assertStringContainsString('BUKTI PENERIMAAN BARANG', $html);
        $this->assertStringContainsString('GR-'.$receipt->id, $html);
        // Diterima 40 dari 100 — bukan angka kumulatif.
        $this->assertMatchesRegularExpression('/<strong>40<\/strong>/', $html);
        $this->assertStringNotContainsString('<strong>100</strong>', $html);
    }

    /**
     * @return array<string, mixed>
     */
    private function documentViewData(string $view): array
    {
        $order = $this->order->load(['branch', 'supplier', 'items.unit']);

        return [
            'docTitle' => 'PURCHASE ORDER',
            'docNumber' => $order->po_number,
            'docDate' => 'Tanggal: '.$order->po_date->format('d M Y'),
            'branch' => $order->branch,
            'supplier' => $order->supplier,
            'order' => $order,
            'itemNames' => $order->items->mapWithKeys(fn ($i) => [
                $i->id => ItemCatalog::nameFor($i->item_type, $i->item_id),
            ])->all(),
            'signerLeft' => ['label' => 'Dicetak Oleh', 'name' => 'Admin', 'detail' => 'Super Admin'],
            'signerRight' => ['label' => 'Disetujui Oleh', 'name' => 'PIC', 'detail' => 'Cabang SDM'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function receiptViewData(PurchaseReceipt $receipt): array
    {
        $loaded = $receipt->load(['receiver', 'items.orderItem', 'items.unit', 'order.branch', 'order.supplier']);

        return [
            'docTitle' => 'BUKTI PENERIMAAN BARANG',
            'docNumber' => 'GR-'.$loaded->id,
            'docDate' => 'Tanggal: '.$loaded->received_at->format('d M Y'),
            'branch' => $loaded->order->branch,
            'supplier' => $loaded->order->supplier,
            'order' => $loaded->order,
            'receipt' => $loaded,
            'itemNames' => $loaded->items->mapWithKeys(fn ($i) => [
                $i->id => ItemCatalog::nameFor($i->item_type, $i->item_id),
            ])->all(),
            'signerLeft' => ['label' => 'Diterima Oleh', 'name' => 'Admin', 'detail' => ''],
            'signerRight' => ['label' => 'Disetujui Oleh', 'name' => 'PIC', 'detail' => 'Cabang SDM'],
        ];
    }

    /**
     * Manajer cabang yang hanya punya akses ke $this->branch.
     */
    private function scopedManager(): User
    {
        $manager = User::factory()->create(['branch_scope' => [$this->branch->id]]);
        Role::create(['name' => 'Manajer Cabang', 'guard_name' => 'web']);
        $manager->assignRole('Manajer Cabang');

        $this->actingAs($manager);

        return $manager;
    }
}
