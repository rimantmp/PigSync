@extends('pdf.base')

@section('content')
    <h1>{{ $title }}</h1>
    <p class="muted small">
        {{ config('app.name', 'Sistem Kandang') }} &middot; {{ $filters->describe() }} &middot;
        Dicetak {{ now()->translatedFormat('d M Y H:i') }} oleh {{ auth()->user()?->name ?? '-' }}
    </p>

    <br>

    <table class="doc-table">
        <thead>
            <tr>
                <th style="width:6mm">#</th>
                @foreach ($table->columns as $column)
                    <th class="{{ ($column['align'] ?? 'left') === 'right' ? 'num' : (($column['align'] ?? 'left') === 'center' ? 'ctr' : '') }}"
                        @if (! empty($column['width'])) style="width:{{ $column['width'] }}" @endif>{{ $column['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($table->rows as $index => $row)
                <tr>
                    <td class="muted">{{ $index + 1 }}</td>
                    @foreach ($table->columns as $column)
                        <td class="{{ ($column['align'] ?? 'left') === 'right' ? 'num' : (($column['align'] ?? 'left') === 'center' ? 'ctr' : '') }}">
                            @php
                                $value = $row[$column['key']] ?? null;
                                echo e(isset($column['format']) ? ($column['format'])($value) : ($value ?? '-'));
                            @endphp
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($table->columns) + 1 }}" class="empty">Tidak ada data untuk filter ini.</td>
                </tr>
            @endforelse

            @if (isset($table->meta['total']))
                <tr class="total-row">
                    <td colspan="{{ count($table->columns) + 1 }}" class="num">
                        {{ $table->meta['total_label'] ?? 'Total' }}: {{ ($table->meta['total_format'] ?? 'number')($table->meta['total']) }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection
