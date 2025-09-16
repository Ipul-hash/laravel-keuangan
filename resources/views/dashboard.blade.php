@extends('layouts.app')

@section('title', 'Dashboard Keuangan')

@section('content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold mb-4">Dashboard Keuangan</h1>


<div id="welcomeBanner" class="mb-8 p-6 bg-gradient-to-r from-green-500 to-emerald-600 rounded-xl shadow-lg text-white transform transition-all duration-700 ease-out opacity-0 translate-y-4">
    <div class="flex items-center">
        <div class="mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 0 014 0zM7 10a2 2 0 11-4 0 2 0 014 0z" />
            </svg>
        </div>
        <div class="flex-1">
            <h2 class="text-2xl font-bold">Selamat datang, <span id="usernameDisplay">{{ Auth::user()->name }}</span>! 🎉</h2>
            <p id="greetingText" class="text-green-100">Memuat...</p>
        </div>
        <div class="ml-4 text-right text-sm text-green-100">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
</svg>

            <p id="walletNameDisplay">Silahkan pilih ATM/E-Wallet</p>
        </div>
    </div>
</div>

    <!-- Ringkasan + Progress -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-gray-500">Saldo</p>
            <h3 id="saldoCard" class="text-2xl font-bold text-green-600">Rp 0</h3>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-gray-500">Pemasukan</p>
            <h3 id="pemasukanCard" class="text-2xl font-bold text-blue-600">Rp 0</h3>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
            <p class="text-gray-500">Pengeluaran</p>
            <h3 id="pengeluaranCard" class="text-2xl font-bold text-red-600">Rp 0</h3>
        </div>

<div class="bg-white p-6 rounded-lg shadow">
    <h2 class="text-sm font-semibold mb-2">Progress Tabungan</h2>
    <div id="goalsContainer" class="space-y-4"></div>
</div>
    </div>

    <div id="recentTransactions" class="space-y-3"></div>
    <div class="bg-white p-6 rounded-lg shadow">
        <h2 class="text-lg font-semibold mb-4">Reminder Tagihan</h2>
        <ul class="space-y-2 text-sm">
            <li class="flex justify-between p-2 border rounded bg-red-50">
                <span>Listrik</span>
                <span class="text-red-600 font-semibold">Overdue - Jatuh Tempo: 05 Sep</span>
            </li>
            <li class="flex justify-between p-2 border rounded bg-yellow-50">
                <span>Internet</span>
                <span class="text-yellow-600 font-semibold">Upcoming - Jatuh Tempo: 15 Sep</span>
            </li>
            <li class="flex justify-between p-2 border rounded bg-green-50">
                <span>Air</span>
                <span class="text-green-600 font-semibold">Paid - Lunas</span>
            </li>
        </ul>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', async function() {

    let activeWalletId = null;

    function updateGreeting() {
        const now = new Date();
        const hour = now.getHours();

        let greeting;
        if (hour >= 5 && hour <= 11) {
            greeting = "Selamat pagi, semoga hari ini menyenangkan dan produktif.";
        } else if (hour >= 12 && hour <= 15) {
            greeting = "Selamat siang, semoga hari ini menyenangkan dan produktif.";
        } else if (hour >= 16 && hour <= 18) {
            greeting = "Selamat sore, semoga hari ini menyenangkan dan produktif.";
        } else {
            greeting = "Selamat malam, semoga hari ini menyenangkan dan produktif.";
        }

        const greetingEl = document.getElementById("greetingText");
        greetingEl.textContent = greeting;
        greetingEl.classList.add("loaded");
    }

    updateGreeting();

    const style = document.createElement('style');
    style.textContent = `
        #greetingText {
            opacity: 0;
            transition: opacity 0.5s ease;
        }
        #greetingText.loaded {
            opacity: 1;
        }
    `;
    document.head.appendChild(style);

    const banner = document.getElementById('welcomeBanner');
    setTimeout(() => {
        banner.classList.remove('opacity-0', 'translate-y-4');
        banner.classList.add('opacity-100', 'translate-y-0');
    }, 300);

    function formatRupiah(angka) {
    const rounded = Math.round(angka);
    
    return 'Rp ' + rounded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

    async function loadActiveWallet() {
        try {
            const response = await fetch('/settings/wallet/active', {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                }
            });

            if (!response.ok) throw new Error('Gagal ambil wallet aktif');
            const data = await response.json();

            activeWalletId = data.id;

            const walletNameEl = document.getElementById('walletNameDisplay');
if (walletNameEl) {
    walletNameEl.textContent = data.name || 'Tidak diketahui';
}

            document.getElementById('btnTambah')?.removeAttribute('disabled');

        } catch (error) {
            console.warn('Gagal load wallet aktif:', error);
            activeWalletId = null;
            const walletNameEl = document.createElement('p');
            walletNameEl.className = 'text-red-100 text-sm mt-1';
            
            document.getElementById('welcomeBanner').querySelector('div:last-child').appendChild(walletNameEl);
        }

        loadDashboardData();
    }

    async function loadDashboardData() {
        const url = '/dashboard/data' + (activeWalletId ? `?wallet_id=${activeWalletId}` : '');

        try {
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });

            if (!response.ok) throw new Error('Gagal muat data dashboard');

            const result = await response.json();

            document.querySelector('#saldoCard').textContent = formatRupiah(result.saldo || 0);
            document.querySelector('#pemasukanCard').textContent = formatRupiah(result.pemasukan || 0);
            document.querySelector('#pengeluaranCard').textContent = formatRupiah(result.pengeluaran || 0);

            const goalsContainer = document.querySelector('#goalsContainer');
            goalsContainer.innerHTML = '';

            if (result.goals && result.goals.length > 0) {
                result.goals.forEach(goal => {
                    goalsContainer.innerHTML += `
                        <div>
                            <p class="text-xs text-gray-500 mb-1">
                                ${goal.title} - Target: Rp ${goal.target_amount.toLocaleString('id-ID')}
                            </p>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-green-500 h-3 rounded-full" style="width: ${goal.progress}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-gray-600">Tercapai ${goal.progress}%</p>
                        </div>
                    `;
                });
            } else {
                goalsContainer.innerHTML = `<p class="text-gray-500 text-sm">Belum ada target tabungan aktif</p>`;
            }

            const container = document.querySelector('#recentTransactions');
            container.innerHTML = '';

            if (result.recent && result.recent.length > 0) {
                result.recent.forEach(item => {
                    const date = new Date(item.date);
                    const formattedDate = `${String(date.getDate()).padStart(2, '0')}-${String(date.getMonth() + 1).padStart(2, '0')}-${date.getFullYear()}`;
                    
                    container.innerHTML += `
                        <div class="flex justify-between items-center p-4 bg-white shadow rounded-lg">
                            <div>
                                <p class="text-sm text-gray-500">${formattedDate}</p>
                                <p class="font-semibold text-gray-800">${item.description}</p>
                                <span class="text-xs px-2 py-1 rounded-full ${item.type === 'pemasukan' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'}">
                                    ${item.type}
                                </span>
                            </div>
                            <div class="${item.type === 'pemasukan' ? 'text-green-600' : 'text-red-600'} font-bold">
                                ${item.type === 'pemasukan' ? '+' : '-'} ${formatRupiah(item.amount)}
                            </div>
                        </div>
                    `;
                });
            } else {
                container.innerHTML = `
                    <div class="text-center py-10 bg-gray-50 rounded-lg border">
                        <p class="text-gray-500">Belum ada pemasukan/pengeluaran hari ini</p>
                    </div>
                `;
            }

        } catch (error) {
            console.error('Dashboard error:', error);
            document.querySelector('#saldoCard').textContent = 'Rp 0';
            document.querySelector('#pemasukanCard').textContent = 'Rp 0';
            document.querySelector('#pengeluaranCard').textContent = 'Rp 0';
            document.querySelector('#goalsContainer').innerHTML = '<p class="text-gray-500 text-sm">Gagal muat target tabungan</p>';
            document.querySelector('#recentTransactions').innerHTML = `
                <div class="text-center py-10 bg-gray-50 rounded-lg border">
                    <p class="text-gray-500">Gagal muat transaksi terbaru</p>
                </div>
            `;
        }
    }

    window.addEventListener('storage', (e) => {
        if (e.key === 'activeWalletId') {
            console.log('🔄 Wallet aktif berubah di halaman lain, reload dashboard...');
            loadActiveWallet(); 
        }
    });

    loadActiveWallet();

});
</script>
@endsection