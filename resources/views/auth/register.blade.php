@extends('layouts.auth')

@section('title', 'Register')

@section('content')
<div class="flex items-center justify-center min-h-screen bg-gray-50">
    <div class="w-full max-w-md bg-white rounded-xl shadow p-8">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Daftar Akun</h1>
        
        <form id="registerForm" class="space-y-4">
            @csrf
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                <input type="text" name="name" id="username" 
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" 
                       placeholder="john_doe" required>
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="email" 
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" 
                       placeholder="you@example.com" required>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" name="password" id="password" 
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" 
                       placeholder="••••••••" required>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" 
                       class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-2" 
                       placeholder="••••••••" required>
            </div>

            <button type="submit" 
                    class="w-full bg-green-600 text-white py-2 px-4 rounded-lg shadow hover:bg-green-700 transition">
                Daftar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-600">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Masuk di sini</a>
        </p>
    </div>
</div>

<script>
    // Inisialisasi Notyf
    const notyf = new Notyf({
        duration: 3500,
        position: {
            x: 'right',
            y: 'top',
        },
        dismissible: true,
        ripple: true,
    });

    document.getElementById('registerForm').addEventListener('submit', async function(event) {
        event.preventDefault();
        
        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);
        
        try {
            const response = await fetch('/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data),
            });
            
            const result = await response.json();
            console.log('Response status:', response.status);
            console.log('Response data:', result);
            
            if (response.status == 200) {
                // ✅ Notifikasi sukses pakai Notyf
                notyf.success({
                    message: '✅ Registrasi berhasil! Silakan login.',
                    duration: 3000,
                    icon: {
                        className: 'fas fa-check-circle', // opsional, butuh Font Awesome
                        tagName: 'i',
                        color: 'white'
                    }
                });

                // Redirect setelah notifikasi
                setTimeout(() => {
                    window.location.href = '/login';
                }, 3000);
                
            } else {
                // ❌ Notifikasi error
                let errorMessage = 'Registrasi gagal.';

                if (result.errors) {
                    // Ambil error pertama aja biar simpel, atau gabungkan
                    const firstError = Object.values(result.errors)[0][0];
                    errorMessage = firstError;
                } else if (result.message) {
                    errorMessage = result.message;
                }

                notyf.error({
                    message: '❌ ' + errorMessage,
                    duration: 5000,
                    dismissible: true
                });

                console.error('Error:', result);
            }
        } catch (error) {
            console.error('Network or parsing error:', error);
            notyf.error({
                message: '⚠️ Terjadi kesalahan jaringan. Coba lagi nanti.',
                duration: 5000,
                dismissible: true
            });
        }
    });
</script>
@endsection