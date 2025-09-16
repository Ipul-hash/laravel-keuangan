<aside id="sidebar" 
       class="flex flex-col items-center w-16 min-h-screen overflow-y-auto text-gray-800 bg-white shadow-xl transition-all duration-300 ease-in-out hover:w-56">
    <!-- Logo -->
    <a href="{{ route('dashboard') }}" class="flex items-center justify-center mt-4">
        <svg class="w-8 h-8 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4z"/>
        </svg>
    </a>

    <!-- Menu -->
    <div class="flex flex-col items-center mt-6 space-y-2 w-full">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" 
           class="flex items-center justify-center w-full px-2 py-3 rounded-lg hover:bg-gray-200 hover:text-green-600 group {{ request()->routeIs('dashboard') ? 'bg-gray-200 text-green-600' : '' }}">
            <svg class="w-6 h-6 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6"/>
            </svg>
            <span class="ml-4 text-sm font-semibold whitespace-nowrap hidden group-hover:inline">Dashboard</span>
        </a>

        <!-- Pemasukan -->
        <a href="{{ route('pemasukan.view') }}" 
           class="flex items-center justify-center w-full px-2 py-3 rounded-lg hover:bg-gray-200 hover:text-green-600 group {{ request()->routeIs('pemasukan.view') ? 'bg-gray-200 text-green-600' : '' }}">
            <svg class="w-6 h-6 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M12 8v8m0 0l-3-3m3 3l3-3M5 19h14"/>
            </svg>
            <span class="ml-4 text-sm font-semibold whitespace-nowrap hidden group-hover:inline">Pemasukan</span>
        </a>

        <!-- Pengeluaran -->
        <a href="{{ route('pengeluaran.view') }}" 
           class="flex items-center justify-center w-full px-2 py-3 rounded-lg hover:bg-gray-200 hover:text-green-600 group {{ request()->routeIs('pengeluaran.view') ? 'bg-gray-200 text-green-600' : '' }}">
            <svg class="w-6 h-6 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M12 16V8m0 0l-3 3m3-3l3 3M5 5h14"/>
            </svg>
            <span class="ml-4 text-sm font-semibold whitespace-nowrap hidden group-hover:inline">Pengeluaran</span>
        </a>

        <!-- Laporan Keuangan -->
        <a href="{{ route('laporan.index') }}" 
           class="flex items-center justify-center w-full px-2 py-3 rounded-lg hover:bg-gray-200 hover:text-green-600 group {{ request()->routeIs('laporan.view') ? 'bg-gray-200 text-green-600' : '' }}">
            <svg class="w-6 h-6 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path d="M9 17v-6h13v6M3 11h6"/>
            </svg>
            <span class="ml-4 text-sm font-semibold whitespace-nowrap hidden group-hover:inline">Laporan</span>
        </a>

        <!-- Anggaran -->
        <a href="{{ route('settings.view') }}" 
           class="flex items-center justify-center w-full px-2 py-3 rounded-lg hover:bg-gray-200 hover:text-green-600 group {{ request()->routeIs('anggaran.view') ? 'bg-gray-200 text-green-600' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
</svg>

            <span class="ml-4 text-sm font-semibold whitespace-nowrap hidden group-hover:inline">Settings</span>
        </a>
    </div>

    <!-- Footer Akun (Dropdown Toggle) -->
    <div class="relative flex items-center justify-center w-full h-16 mt-auto bg-gray-100 hover:bg-gray-200 hover:text-green-600 group cursor-pointer"
         onclick="toggleDropdown()" id="account-toggle">
        <svg class="w-6 h-6 stroke-current group-hover:scale-110 transition-transform duration-300" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M5.121 17.804A13.937 13.937 0 0112 15c2.21 0 4.305.5 6.121 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span class="ml-4 text-sm font-semibold whitespace-nowrap hidden group-hover:inline">Akun</span>

        <!-- Dropdown Menu -->
        <div id="account-dropdown" class="absolute bottom-16 left-0 w-full bg-white shadow-lg rounded-t-lg border-t-2 border-green-600 hidden z-10 transition-all duration-200 ease-in-out transform scale-y-95 origin-bottom opacity-0">
            <a href="#" 
               id="logoutBtn"
               class="block px-4 py-3 text-sm font-medium text-gray-800 hover:bg-gray-100 hover:text-green-600 transition">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Logout
            </a>
        </div>
    </div>
</aside>

<script>
    function toggleDropdown() {
        const dropdown = document.getElementById('account-dropdown');
        dropdown.classList.toggle('hidden');
        if (!dropdown.classList.contains('hidden')) {
            dropdown.classList.remove('scale-y-95', 'opacity-0');
            dropdown.classList.add('scale-y-100', 'opacity-100');
        } else {
            dropdown.classList.remove('scale-y-100', 'opacity-100');
            dropdown.classList.add('scale-y-95', 'opacity-0');
        }
    }

    // Tutup dropdown kalau klik di luar
    document.addEventListener('click', function(event) {
        const accountToggle = document.getElementById('account-toggle');
        const accountDropdown = document.getElementById('account-dropdown');
        if (!accountToggle.contains(event.target)) {
            accountDropdown.classList.add('hidden');
            accountDropdown.classList.remove('scale-y-100', 'opacity-100');
            accountDropdown.classList.add('scale-y-95', 'opacity-0');
        }
    });

     document.addEventListener('click', function(event) {
        if (event.target.id === 'logoutBtn' || event.target.closest('#logoutBtn')) {
            event.preventDefault();

            const username = localStorage.getItem('username') || 'User';

            // ✅ SweetAlert2 Konfirmasi Logout
            Swal.fire({
                title: 'Konfirmasi Logout',
                html: `<div class="text-center">
                         <i class="fas fa-sign-out-alt text-5xl text-red-500 mb-4"></i>
                         <p class="text-lg">👋 Hai <strong>{{ Auth::user()->name }}</strong>!</p>
                         <p>Apakah Anda yakin ingin keluar?</p>
                       </div>`,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-sign-out-alt mr-2"></i> Logout',
                cancelButtonText: '<i class="fas fa-times mr-2"></i> Batal',
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-xl',
                    title: 'text-xl font-bold',
                    actions: 'flex justify-center gap-3',
                    confirmButton: 'px-6 py-3 text-white font-medium rounded-lg',
                    cancelButton: 'px-6 py-3 text-white font-medium rounded-lg'
                },
                didOpen: () => {
                    // Optional: Tambah animasi
                    const icon = Swal.getIcon();
                    if (icon) {
                        icon.classList.add('animate-bounce');
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Proses logout
                    fetch('/logout', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        // ✅ Notyf sukses
                        const notyf = new Notyf({
                            duration: 3000,
                            position: { x: 'right', y: 'top' },
                            dismissible: true,
                            ripple: true,
                        });
                        notyf.success({
                            message: '✅ Logout berhasil! Sampai jumpa.',
                            icon: { className: 'fas fa-sign-out-alt', tagName: 'i', color: 'white' }
                        });

                        localStorage.removeItem('username');
                        setTimeout(() => {
                            window.location.href = '/login';
                        }, 2000);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Logout gagal. Coba lagi.', 'error');
                    });
                }
            });
        }
    });
</script>