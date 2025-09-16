@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<div class="p-6 bg-gray-50 min-h-screen">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Laporan Keuangan</h1>

    <!-- Ringkasan Total -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg border shadow-sm">
            <p class="text-sm text-gray-600">Saldo</p>
            <p class="text-xl font-bold text-green-600" id="saldoCard">
                Rp {{ number_format($totalSaldo, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white p-4 rounded-lg border shadow-sm">
            <p class="text-sm text-gray-600">Pemasukan</p>
            <p class="text-xl font-bold text-blue-600" id="pemasukanCard">
                Rp {{ number_format($totalPemasukan, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white p-4 rounded-lg border shadow-sm">
            <p class="text-sm text-gray-600">Pengeluaran</p>
            <p class="text-xl font-bold text-red-600" id="pengeluaranCard">
                Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}
            </p>
        </div>
        <div class="bg-white p-4 rounded-lg border shadow-sm">
            <p class="text-sm text-gray-600">Progress Tabungan</p>
            <p class="text-xs text-gray-600">Target: Rp 20.000.000</p>
            @php
                $target = 20000000;
                $progress = $target > 0 ? round(($totalSaldo / $target) * 100, 2) : 0;
            @endphp
            <div class="w-full bg-gray-200 rounded-full h-2 mt-1">
                <div class="bg-green-500 h-2 rounded-full" id="progressBar" style="width: {{ $progress }}%;"></div>
            </div>
            <p class="text-xs text-gray-600 mt-1" id="progressText">
                Tercapai {{ $progress }}%
            </p>
        </div>
    </div>

    <!-- Filter & Search -->
    <div class="flex flex-col sm:flex-row gap-4 mb-6">
        <div class="flex-1">
            <input 
                type="text" 
                id="searchInput" 
                placeholder="Cari bulan..." 
                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </div>
        <select id="tahunFilter" class="px-4 py-2 border border-gray-300 rounded-lg">
            <option value="">Semua Tahun</option>
            <option value="2025">2025</option>
            <option value="2024">2024</option>
        </select>
    </div>

    <!-- Daftar Bulan -->
    <div id="laporanList" class="space-y-4">
    @forelse ($laporan as $item)
        <a 
            href="{{ route('laporan.show', [$item['tahun'], str_pad($item['bulan'], 2, '0', STR_PAD_LEFT)]) }}" 
            class="block bg-white p-5 rounded-lg border hover:shadow transition-shadow duration-200 laporan-item" 
            data-bulan-nama="{{ strtolower($item['bulan_nama']) }}" 
            data-tahun="{{ $item['tahun'] }}"
            data-pemasukan="{{ $item['pemasukan'] }}"
            data-pengeluaran="{{ $item['pengeluaran'] }}"
        >
            <div class="flex justify-between items-start mb-3">
                <h2 class="text-lg font-semibold text-gray-800">{{ $item['bulan_nama'] }} {{ $item['tahun'] }}</h2>
                <span class="text-blue-500 text-sm font-medium">Lihat →</span>
            </div>
            <div class="space-y-1 text-sm">
                <p class="text-green-600">Pemasukan: Rp {{ number_format($item['pemasukan'], 0, ',', '.') }}</p>
                <p class="text-red-500">Pengeluaran: Rp {{ number_format($item['pengeluaran'], 0, ',', '.') }}</p>
                <p class="text-blue-600 font-medium">Saldo: Rp {{ number_format($item['saldo'], 0, ',', '.') }}</p>
            </div>
        </a>
    @empty
        <p class="text-center text-gray-500">Belum ada laporan.</p>
    @endforelse
    </div>

    <!-- Pesan jika tidak ada hasil -->
    <div id="noResults" class="text-center text-gray-500 hidden mt-8">
        <p>Tidak ada laporan yang ditemukan.</p>
    </div>
</div>

<!-- JavaScript Interaktif -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const tahunFilter = document.getElementById('tahunFilter');
    const laporanItems = document.querySelectorAll('.laporan-item');
    const noResults = document.getElementById('noResults');

    // Card elements
    const saldoCard = document.getElementById('saldoCard');
    const pemasukanCard = document.getElementById('pemasukanCard');
    const pengeluaranCard = document.getElementById('pengeluaranCard');
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const target = 20000000;

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID').format(angka);
    }

    function recalcSummary() {
        let totalPemasukan = 0;
        let totalPengeluaran = 0;

        laporanItems.forEach(item => {
            if (item.style.display !== 'none') {
                totalPemasukan += parseInt(item.dataset.pemasukan);
                totalPengeluaran += parseInt(item.dataset.pengeluaran);
            }
        });

        let saldo = totalPemasukan - totalPengeluaran;
        let progress = target > 0 ? Math.round((saldo / target) * 100) : 0;

        saldoCard.textContent = "Rp " + formatRupiah(saldo);
        pemasukanCard.textContent = "Rp " + formatRupiah(totalPemasukan);
        pengeluaranCard.textContent = "Rp " + formatRupiah(totalPengeluaran);
        progressBar.style.width = progress + "%";
        progressText.textContent = "Tercapai " + progress + "%";
    }

    function filterLaporan() {
        const searchText = searchInput.value.toLowerCase();
        const selectedTahun = tahunFilter.value;

        let visibleCount = 0;

        laporanItems.forEach(item => {
            const bulanNama = item.dataset.bulanNama;
            const tahun = item.dataset.tahun;
            const matchesSearch = bulanNama.includes(searchText);
            const matchesTahun = !selectedTahun || tahun === selectedTahun;

            if (matchesSearch && matchesTahun) {
                item.style.display = 'block';
                visibleCount++;
            } else {
                item.style.display = 'none';
            }
        });

        noResults.style.display = visibleCount === 0 ? 'block' : 'none';

        // Update summary setiap filter jalan
        recalcSummary();
    }

    searchInput.addEventListener('input', filterLaporan);
    tahunFilter.addEventListener('change', filterLaporan);

    filterLaporan(); // initial
});
</script>
@endsection
