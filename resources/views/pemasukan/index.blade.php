@extends('layouts.app')

@section('title', 'Pemasukan')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Daftar Pemasukan</h1>
        <button id="btnTambah" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded shadow">
            + Tambah Pemasukan
        </button>
    </div>

    <!-- Total Pemasukan -->
    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
        <h3 class="text-lg font-semibold text-green-800">Total Pemasukan Hari Ini</h3>
        <p id="totalPemasukan" class="text-2xl font-bold text-green-600">Rp 0</p>
    </div>

    <!-- List Pemasukan -->
    <div class="bg-white p-6 rounded-lg shadow">
        <ul id="listPemasukan" class="space-y-2 text-sm">
            <!-- Data akan diisi otomatis via JS -->
        </ul>
    </div>
</div>

<!-- Modal -->
<div id="modalTambah" 
    class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <h2 class="text-lg font-semibold mb-4">Tambah Pemasukan</h2>
        <form id="formPemasukan" class="space-y-4">
            <input type="hidden" id="wallet_id" name="wallet_id"> 
            <div>
                <label class="block text-sm font-medium">Deskripsi</label>
                <input type="text" id="deskripsi" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Kategori</label>
                <input type="text" id="kategori" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Jumlah (Rp)</label>
                <input type="number" id="jumlah" class="w-full border rounded p-2" required min="1">
            </div>
            <div>
                <label class="block text-sm font-medium">Tanggal</label>
                <input type="date" id="tanggal" class="w-full border rounded p-2" required>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" id="btnBatal" class="px-4 py-2 rounded border">Batal</button>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="modalEdit" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <h2 class="text-lg font-semibold mb-4">Edit Pemasukan</h2>
        <form id="formEdit" class="space-y-4">
            <input type="hidden" id="editId">
            <div>
                <label class="block text-sm font-medium">Deskripsi</label>
                <input type="text" id="editDeskripsi" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Kategori</label>
                <input type="text" id="editKategori" class="w-full border rounded p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium">Jumlah (Rp)</label>
                <input type="number" id="editJumlah" class="w-full border rounded p-2" required min="1">
            </div>
            <div>
                <label class="block text-sm font-medium">Tanggal</label>
                <input type="date" id="editTanggal" class="w-full border rounded p-2" required>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" id="btnBatalEdit" class="px-4 py-2 rounded border">Batal</button>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Update</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// 🚫 HAPUS SEMUA LOGIKA "new Date()" — ganti dengan ambil dari server!

let serverToday = null;

// Fungsi untuk ambil tanggal dari server (hanya sekali saat pertama kali)
async function loadServerDate() {
    try {
        const response = await fetch('/api/debug-time');
        if (!response.ok) throw new Error('Gagal ambil waktu server');
        const data = await response.json();
        serverToday = data.today; // Format: YYYY-MM-DD
    } catch (error) {
        console.warn('Fallback: pakai tanggal browser karena gagal ambil dari server', error);
        serverToday = new Date().toISOString().slice(0, 10);
    }
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

        if (!data || !data.id) {
            throw new Error('Data wallet tidak valid');
        }

        activeWalletId = data.id;
        document.getElementById('wallet_id').value = activeWalletId;

        // Optional: Tampilkan nama wallet aktif di UI
        const walletName = document.getElementById('activeWalletName');
        if (walletName) walletName.textContent = data.name;

    } catch (error) {
        console.error('Error load active wallet:', error);
        const notyf = new Notyf();
        notyf.error('Gagal memuat wallet aktif. Coba ulangi.');
        
        // Fallback: gunakan wallet pertama dari user
        activeWalletId = null; // biarkan kosong, nanti kita cek lagi
    }

    // Reload data setelah wallet aktif diperbarui
    loadPemasukan();
}



// Load pemasukan berdasarkan tanggal server
async function loadPemasukan() {
    if (!serverToday) {
        await loadServerDate(); // Tunggu sampai tanggal server siap
    }

    const filterTanggal = document.getElementById('filterTanggal')?.value;
    const today = filterTanggal || serverToday;

    try {
        const response = await fetch(`/api/pemasukan?tanggal=${today}&wallet_id=${activeWalletId}&_t=${Date.now()}`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
            }
        });

        if (!response.ok) throw new Error('Gagal load data');
        const result = await response.json();

        // Update total
        document.getElementById('totalPemasukan').textContent = `Rp ${result.total.toLocaleString()}`;

        const container = document.getElementById('listPemasukan');
        container.innerHTML = '';

        if (result.data.length === 0) {
            container.innerHTML = `
                <div class="text-center py-10 bg-gray-50 rounded-lg border">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-500">Belum ada pemasukan pada ${today}</p>
                </div>
            `;
        } else {
            let html = `<ul class="divide-y divide-gray-200">`;

            result.data.forEach(item => {
                const date = new Date(item.date);
                const formattedDate = `${String(date.getDate()).padStart(2, '0')}-${String(date.getMonth() + 1).padStart(2, '0')}-${date.getFullYear()}`;

                html += `
                    <li class="flex justify-between py-4">
                        <div>
                            <p class="font-semibold text-gray-800">${item.description}</p>
                            <p class="text-gray-500 text-sm">${formattedDate}</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-green-600 font-bold">+ Rp ${Number(item.amount).toLocaleString("id-ID")}</span>
                            <button class="text-blue-500 hover:text-blue-700 text-sm edit-btn" data-id="${item.id}">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </button>
                            <button class="text-red-500 hover:text-red-700 text-sm delete-btn" data-id="${item.id}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                            </button>
                        </div>
                    </li>
                `;
            });

            html += `</ul>`;
            container.innerHTML = html;

            // Attach event listeners to new buttons
            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    openEditModal(id);

                    async function openEditModal(id) {
    try {
        const response = await fetch(`/api/pemasukan/${id}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
            }
        });

        if (!response.ok) throw new Error('Gagal load data');
        const result = await response.json();
        const item = result.data || result;

        document.getElementById('editId').value = item.id;
        document.getElementById('editDeskripsi').value = item.description || '';
        document.getElementById('editKategori').value = item.category || '';
        document.getElementById('editJumlah').value = item.amount || '';
        document.getElementById('editTanggal').value = item.date || '';

        document.getElementById('modalEdit').classList.remove('hidden');

    } catch (error) {
        console.error('Error:', error);
        const notyf = new Notyf();
        notyf.error('Gagal membuka modal edit.');
    }
}
                });
            });

            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    confirmDelete(id);
                });
            });
        }

    } catch (error) {
        console.error('Error:', error);
        const notyf = new Notyf();
        notyf.error('Gagal memuat data pemasukan.');
    }
}

// Update Pemasukan
document.getElementById('formEdit').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const id = document.getElementById('editId').value;
    const description = document.getElementById('editDeskripsi').value.trim(); 
    const category = document.getElementById('editKategori').value.trim();       
    const amount = parseFloat(document.getElementById('editJumlah').value);    
    const date = document.getElementById('editTanggal').value.trim();          

    try {
        const response = await fetch(`/api/pemasukan/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
            },
            body: JSON.stringify({ 
                wallet_id: activeWalletId,
                description: description,  
                category: category,        
                amount: amount,            
                date: date                 
            }),
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Gagal update');
        }

        const notyf = new Notyf();
        notyf.success('✅ Pemasukan berhasil diupdate!');

        loadPemasukan();
        closeModalEdit();

    } catch (error) {
        console.error('Error:', error);
        const notyf = new Notyf();
        notyf.error('❌ ' + error.message);
    }
});

// Tambah Pemasukan - INI YANG DIPERBAIKI!
document.getElementById('formPemasukan').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const description = document.getElementById('deskripsi').value.trim();  
    const category = document.getElementById('kategori').value.trim();      
    const amount = parseFloat(document.getElementById('jumlah').value);
    const date = document.getElementById('tanggal').value.trim();          

    const finalDate = date || serverToday; // ← ini bisa undefined jika belum di-load

    try {
        const response = await fetch(`/api/pemasukan`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
            },
            body: JSON.stringify({
                wallet_id: activeWalletId,
                description: description,                           
                category: category,                                   
                amount: amount,                                     
                date: finalDate, 
            }),
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Gagal tambah pemasukan');
        }

        const notyf = new Notyf();
        notyf.success('✅ Pemasukan berhasil ditambahkan!');

        loadPemasukan();
        closeModalTambah();
        document.getElementById('formPemasukan').reset();

    } catch (error) {
        console.error('Error:', error);
        const notyf = new Notyf();
        notyf.error('❌ ' + error.message);
    }
});

// Confirm Delete with SweetAlert2
function confirmDelete(id) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: "Apakah Anda yakin ingin menghapus pemasukan ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
    }).then((result) => {
        if (result.isConfirmed) {
            deletePemasukan(id);
        }
    });
}

// Delete Pemasukan
async function deletePemasukan(id) {
    try {
        const response = await fetch(`/api/pemasukan/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        });

        if (!response.ok) throw new Error('Gagal hapus');

        const notyf = new Notyf();
        notyf.success('✅ Pemasukan berhasil dihapus!');

        loadPemasukan();

    } catch (error) {
        console.error('Error:', error);
        const notyf = new Notyf();
        notyf.error('❌ Gagal menghapus pemasukan.');
    }
}

// Modal logic
const modalTambah = document.getElementById('modalTambah');
const modalEdit = document.getElementById('modalEdit');

document.getElementById('btnTambah').onclick = () => modalTambah.classList.remove('hidden');
document.getElementById('btnBatal').onclick = () => modalTambah.classList.add('hidden');
document.getElementById('btnBatalEdit').onclick = () => modalEdit.classList.add('hidden');

function closeModalTambah() { modalTambah.classList.add('hidden'); }
function closeModalEdit() { modalEdit.classList.add('hidden'); }

// Filter tanggal
document.getElementById('filterTanggal')?.addEventListener('change', loadPemasukan);

// 👇 INI YANG PENTING: LOAD SERVER DATE SAAT HALAMAN DIMUAT
// 👇 load wallet aktif dulu baru load pemasukan
// 👇 GANTI dari:
// loadActiveWallet().then(() => {
//     loadServerDate().then(() => loadPemasukan());
// });

// 👇 JADI:
loadActiveWallet(); // ← ini akan load wallet → lalu loadPemasukan()



</script>
@endpush