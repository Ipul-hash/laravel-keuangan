@extends('layouts.app')

@section('title', "Laporan $bulanNama $tahun")

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold mb-6">Laporan {{ $bulanNama }} {{ $tahun }}</h1>

    <div class="space-y-3">
        @forelse ($transaksi as $item)
            <div class="flex justify-between items-center p-4 bg-white shadow rounded-lg">
                <div>
                    <p class="text-sm text-gray-500">{{ $item->date->format('d-m-Y') }}</p>
                    <p class="font-semibold text-gray-800">{{ $item->description }}</p>
                    <span class="text-xs px-2 py-1 rounded-full {{ $item->type === 'pemasukan' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' }}">
                        {{ ucfirst($item->type) }}
                    </span>
                </div>
                <div class="{{ $item->type === 'pemasukan' ? 'text-green-600' : 'text-red-600' }} font-bold">
                    {{ $item->type === 'pemasukan' ? '+' : '-' }} Rp {{ number_format($item->amount, 0, ',', '.') }}
                </div>
            </div>
        @empty
            <p class="text-gray-500">Tidak ada transaksi bulan ini.</p>
        @endforelse
    </div>
</div>
@endsection
