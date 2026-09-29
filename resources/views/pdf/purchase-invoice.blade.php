@extends('pdf.document')

@section('body')
    <table class="layout">
        <tr>
            <td style="width:50%">
                <span class="muted small">No. PO</span><br>
                <strong>{{ $order->po_number }}</strong>
            </td>
            <td style="width:50%">
                <span class="muted small">Status</span><br>
                <strong>{{ $invoice->status === 'lunas' ? 'LUNAS' : 'BELUM BAYAR' }}</strong>
            </td>
        </tr>
    </table>

    <br>

    <table class="doc-table">
        <thead>
            <tr>
                <th style="width:8mm">#</th>
                <th>Item</th>
                <th class="num" style="width:22mm">Qty</th>
                <th>Satuan</th>
                <th class="num" style="width:28mm">Harga</th>
                <th class="num" style="width:32mm">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $index => $item)
                <tr>
                    <td class="muted">{{ $index + 1 }}</td>
                    <td>{{ $itemNames[$item->id] ?? '—' }}</td>
                    <td class="num">{{ qty($item->qty) }}</td>
                    <td>{{ $item->unit?->name ?? '—' }}</td>
                    <td class="num">{{ rupiah($item->price) }}</td>
                    <td class="num">{{ rupiah($item->subtotal) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="num">Total Invoice</td>
                <td class="num">Rp {{ rupiah($invoice->total) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($payments->isNotEmpty())
        <br>
        <h2>Riwayat Pembayaran</h2>
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Metode</th>
                    <th class="num">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at?->format('d M Y') }}</td>
                        <td>{{ strtoupper($payment->method) }}</td>
                        <td class="num">Rp {{ rupiah($payment->amount) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
