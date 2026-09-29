@extends('pdf.base')

@section('content')
    {{-- Kop surat --}}
    <table class="layout">
        <tr>
            <td style="width:60%">
                <h1>{{ $branch->name ?? config('app.name') }}</h1>
                @if ($branch?->address)
                    <p class="small muted">{{ $branch->address }}</p>
                @endif
                <p class="small muted">
                    @if ($branch?->phone)Telp. {{ $branch->phone }}@endif
                    @if ($branch?->pic) &middot; PIC: {{ $branch->pic }}@endif
                </p>
                @if ($branch?->code)
                    <p class="small muted">Kode cabang: {{ $branch->code }}</p>
                @endif
            </td>
            <td class="num" style="text-align:right; vertical-align:top">
                <h2>{{ $docTitle }}</h2>
                <p class="small muted">
                    No. {{ $docNumber }}<br>
                    {{ $docDate }}
                </p>
            </td>
        </tr>
    </table>

    <br>

    @isset($supplier)
        <table class="layout">
            <tr>
                <td style="width:25%" class="muted small">Kepada</td>
                <td style="width:75%">
                    <strong>{{ $supplier->name }}</strong><br>
                    <span class="small muted">
                        @if ($supplier->code)Kode: {{ $supplier->code }}<br>@endif
                        @if ($supplier->contact)Kontak: {{ $supplier->contact }}<br>@endif
                        @if ($supplier->phone)Telp. {{ $supplier->phone }}@endif
                    </span>
                </td>
            </tr>
        </table>
        <br>
    @endisset

    @yield('body')

    <br>
    <br>

    {{-- Tanda tangan. purchase_orders tidak punya user_id, jadi labelnya
         "Dicetak oleh" — bukan "Dibuat oleh" — supaya dokumen tidak
         mengesahkan hal yang tidak tercatat. --}}
    <table class="layout signature">
        <tr>
            <td style="width:50%">
                <p class="small muted">{{ $signerLeft['label'] }}</p>
                <div class="line"></div>
                <p class="small"><strong>{{ $signerLeft['name'] }}</strong></p>
                <p class="small muted">{{ $signerLeft['detail'] }}</p>
            </td>
            <td style="width:50%">
                <p class="small muted">{{ $signerRight['label'] }}</p>
                <div class="line"></div>
                <p class="small"><strong>{{ $signerRight['name'] }}</strong></p>
                <p class="small muted">{{ $signerRight['detail'] }}</p>
            </td>
        </tr>
    </table>
@endsection
