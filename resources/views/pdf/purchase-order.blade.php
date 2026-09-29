@extends('pdf.document')

@section('body')
    <table class="layout">
        <tr>
            <td style="width:50%">
                <span class="muted small">Status</span><br>
                <strong>{{ strtoupper($order->status) }}</strong>
            </td>
            <td style="width:50%">
                <span class="muted small">Jatuh Tempo</span><br>
                <strong>{{ $order->due_date?->format('d M Y') ?? '—' }}</strong>
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
                <td colspan="5" class="num">Total</td>
                <td class="num">Rp {{ rupiah($order->total) }}</td>
            </tr>
        </tbody>
    </table>

    <p class="small muted" style="margin-top:2mm">
        Metode pembayaran: {{ strtoupper($order->payment_method ?? 'transfer') }}
        @if ($order->notes) &middot; Catatan: {{ $order->notes }} @endif
    </p>
@endsection
