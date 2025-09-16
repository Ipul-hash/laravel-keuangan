<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Keuangan App')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
    <script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    #logoutModal.show {
        display: flex;
    }

    #logoutModal.show .bg-white {
        transform: scale(1);
        opacity: 1;
    }
    </style>
</head>
<!-- Modal Konfirmasi Logout -->
<div id="logoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-xl p-6 w-96 shadow-2xl transform transition-all duration-300 scale-95 opacity-0">
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-red-100 text-red-600 mb-4">
                <i class="fas fa-sign-out-alt text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Konfirmasi Logout</h3>
            <p class="text-gray-600 mb-6" id="logoutMessage">👋 Hai <span id="logoutUsername">User</span>!<br>Apakah Anda yakin ingin keluar?</p>
            
            <div class="flex justify-center space-x-4">
                <button id="cancelLogout" class="px-6 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300 transition font-medium">
                    Batal
                </button>
                <button id="confirmLogout" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium flex items-center">
                    <i class="fas fa-sign-out-alt mr-2"></i> Logout
                </button>
            </div>
        </div>
    </div>
</div>
<body class="bg-gray-50">

    <div class="min-h-screen flex flex-col">
    

        <div class="flex flex-1 overflow-hidden">
            {{-- Sidebar --}}
            @include('layouts.sidebar')

            {{-- Main Content --}}
            <main id="main-content" class="flex-1 overflow-auto p-6 transition-all duration-300">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
