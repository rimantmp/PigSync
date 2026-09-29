<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseReceipt;
use App\Services\PdfService;
use App\Support\BranchScope;
use App\Support\ItemCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class PurchaseDocumentController extends Controller
{
    public function __construct(private readonly PdfService $pdf) {}

    public function order(Request $request, PurchaseOrder $purchaseOrder): Response
    {
        $order = $this->guard($purchaseOrder->load(['branch', 'supplier', 'items.unit']));

        return $this->pdf->portrait('pdf.purchase-order', [
            'docTitle' => 'PURCHASE ORDER',
            'docNumber' => $order->po_number,
            'docDate' => 'Tanggal: '.($order->po_date?->format('d M Y') ?? '-'),
            'branch' => $order->branch,
            'supplier' => $order->supplier,
            'order' => $order,
            'itemNames' => $this->labelsFor($order->items),
            'signerLeft' => $this->signer('Dicetak Oleh', $request),
            'signerRight' => [
                'label' => 'Disetujui Oleh',
                'name' => $order->branch?->pic ?? '....................',
                'detail' => 'PIC '.$order->branch?->name,
            ],
        ], 'PO-'.$order->po_number.'.pdf');
    }

    public function invoice(Request $request, PurchaseInvoice $purchaseInvoice): Response
    {
        $invoice = $purchaseInvoice->load(['order.branch', 'order.supplier', 'order.items.unit']);

        $this->guard($invoice->order);

        $payments = Payment::where('payable_type', PurchaseInvoice::class)
            ->where('payable_id', $invoice->id)
            ->orderBy('paid_at')
            ->get();

        return $this->pdf->portrait('pdf.purchase-invoice', [
            'docTitle' => 'INVOICE',
            'docNumber' => $invoice->invoice_number,
            'docDate' => 'Tanggal: '.($invoice->invoice_date?->format('d M Y') ?? '-'),
            'branch' => $invoice->order?->branch,
            'supplier' => $invoice->order?->supplier,
            'order' => $invoice->order,
            'invoice' => $invoice,
            'payments' => $payments,
            'itemNames' => $this->labelsFor($invoice->order?->items ?? collect()),
            'signerLeft' => $this->signer('Dicetak Oleh', $request),
            'signerRight' => [
                'label' => 'Diterima Oleh',
                'name' => $invoice->order?->branch?->pic ?? '....................',
                'detail' => 'PIC '.$invoice->order?->branch?->name,
            ],
        ], $invoice->invoice_number.'.pdf');
    }

    public function receipt(Request $request, PurchaseReceipt $purchaseReceipt): Response
    {
        $receipt = $purchaseReceipt->load([
            'receiver',
            'items.orderItem',
            'items.unit',
            'order.branch',
            'order.supplier',
        ]);

        $this->guard($receipt->order);

        return $this->pdf->portrait('pdf.purchase-receipt', [
            'docTitle' => 'BUKTI PENERIMAAN BARANG',
            // purchase_receipts tidak punya kolom nomor; pakai GR-{id},
            // mengikuti konvensi PR-{id} yang sudah dipakai di app.
            'docNumber' => 'GR-'.$receipt->id,
            'docDate' => 'Tanggal: '.($receipt->received_at?->format('d M Y') ?? '-'),
            'branch' => $receipt->order?->branch,
            'supplier' => $receipt->order?->supplier,
            'order' => $receipt->order,
            'receipt' => $receipt,
            'itemNames' => $this->labelsFor($receipt->items),
            'signerLeft' => $this->signer('Diterima Oleh', $request, $receipt->receiver?->name),
            'signerRight' => [
                'label' => 'Disetujui Oleh',
                'name' => $receipt->order?->branch?->pic ?? '....................',
                'detail' => 'PIC '.$receipt->order?->branch?->name,
            ],
        ], 'GR-'.$receipt->id.'.pdf');
    }

    /**
     * Tolak dokumen yang cabangnya di luar scope user.
     */
    private function guard(?PurchaseOrder $order): PurchaseOrder
    {
        abort_if($order === null, 404);

        abort_unless(
            BranchScope::can(request()->user(), (int) $order->branch_id),
            404,
            'Dokumen tidak ditemukan.'
        );

        return $order;
    }

    /**
     * Nama item yang sudah ter-resolve per baris item, untuk dipakai view.
     *
     * @param  Collection<int, Model>  $items
     * @return array<int, string>
     */
    private function labelsFor($items): array
    {
        $names = ItemCatalog::namesFor($items);

        return $items->mapWithKeys(fn ($item) => [
            $item->id => $names[ItemCatalog::key($item->item_type, $item->item_id)]
                ?? ItemCatalog::labelFor($item->item_type).' #'.$item->item_id,
        ])->all();
    }

    /**
     * @return array{label:string, name:string, detail:string}
     */
    private function signer(string $label, Request $request, ?string $name = null): array
    {
        $user = $request->user();

        return [
            'label' => $label,
            'name' => $name ?? $user?->name ?? '....................',
            'detail' => $name ? '' : (string) ($user?->roleName ?? ''),
        ];
    }
}
