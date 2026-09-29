@extends('pdf.document')

@section('body')
    <table class="layout">
        <tr>
            <td style="width:50%">
                <span class="muted small">No. PO</span><br>
                <strong>{{ $order->po_number }}</strong>
            </td>
            <td style="width:50%">
                <span class="muted small">Diterima Oleh</span><br>
                <strong>{{ $receipt->receiver?->name ?? '—' }}</strong>
            </td>
        </tr>
    </table>

    <br>

    <table class="doc-table">
        <thead>
            <tr>
                <th style="width:8mm">#</th>
                <th>Item</th>
                <th class="num" style="width:24mm">Dipesan</th>
                <th class="num" style="width:24mm">Diterima</th>
                <th>Satuan</th>
            </tr>
        </thead>
        <tbody>
            {{-- Qty "Diterima" diambil dari purchase_receipt_items, bukan dari
                 received_qty di PO: yang itu kumulatif seluruh penerimaan. --}}
            @forelse ($receipt->items as $index => $item)
                @php $ordered = $item->orderItem?->qty ?? 0; @endphp
                <tr>
                    <td class="muted">{{ $index + 1 }}</td>
                    <td>{{ $itemNames[$item->id] ?? '—' }}</td>
                    <td class="num">{{ qty($ordered) }}</td>
                    <td class="num"><strong>{{ qty($item->qty) }}</strong></td>
                    <td>{{ $item->unit?->name ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty">Tidak ada detail penerimaan untuk dokumen ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($receipt->notes)
        <p class="small muted" style="margin-top:2mm">Catatan: {{ $receipt->notes }}</p>
    @endif
@endsection
