@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-6">Pengaturan Akun</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- KIRI: Target Tabungan & Notifikasi & Abnormal -->
        <div class="space-y-6">

            <!-- Target Tabungan -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Target Tabungan</h2>
                    <span class="text-sm text-gray-500">1 target aktif</span>
                </div>

                <form id="formTarget" class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Target</label>
                        <input type="text" id="targetName" class="w-full border rounded p-2" placeholder="Contoh: Liburan 2026" required>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Jumlah Target (Rp)</label>
                            <input type="number" id="targetAmount" class="w-full border rounded p-2" placeholder="20000000" min="0" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Deadline</label>
                            <input type="date" id="targetDeadline" class="w-full border rounded p-2">
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <button type="submit" id="btnSaveTarget" class="px-4 py-2 bg-green-600 text-white rounded">Simpan Target</button>
                        <button type="button" id="btnClearTarget" class="px-4 py-2 border rounded text-sm">Bersihkan</button>
                        <span id="targetStatus" class="text-sm text-gray-600 ml-auto"></span>
                    </div>
                </form>
            </div>

            <!-- Notifikasi & Abnormal -->
            <div class="bg-white rounded-lg shadow p-6 space-y-4">
                <h2 class="text-lg font-semibold">Pengaturan Lain</h2>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-medium">Notifikasi</p>
                        <p class="text-sm text-gray-500">Terima notifikasi untuk input & alert</p>
                    </div>
                    <label class="flex items-center cursor-pointer">
                        <input id="toggleNotifications" type="checkbox" class="hidden">
                        <div id="toggleNotificationsBox" class="w-12 h-6 bg-gray-300 rounded-full relative transition"></div>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Threshold Pengeluaran Abnormal (Rp)</label>
                    <div class="flex items-center space-x-3 mt-2">
                        <input type="number" id="abnormalThreshold" class="w-full border rounded p-2" placeholder="500000">
                        <button id="btnSaveAbnormal" class="px-4 py-2 bg-yellow-600 text-white rounded">Simpan Threshold</button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Jika pengeluaran melebihi nilai ini, akan dianggap abnormal.</p>
                </div>
            </div>
        </div>

        <!-- KANAN: ATM / Wallet Management -->
        <div class="space-y-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">ATM / Wallet</h2>
                    <div class="flex items-center space-x-2">
                        <button id="btnAddAtm" class="px-3 py-1 bg-blue-600 text-white rounded">+ Tambah ATM</button>
                    </div>
                </div>

                <div id="atmList" class="space-y-3">
                    <!-- ATM items akan di-render via JS -->
                    <p class="text-sm text-gray-500">Memuat daftar ATM...</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold mb-2">Petunjuk</h2>
                <ul class="text-sm list-disc list-inside text-gray-600">
                    <li>Set ATM aktif supaya semua transaksi otomatis tercatat di ATM tersebut.</li>
                    <li>Target tabungan hanya boleh 1 yang aktif — berguna untuk progress di dashboard.</li>
                    <li>Threshold abnormal membantu memberi peringatan pengeluaran besar.</li>
                </ul>
            </div>
        </div>

    </div>
</div>

<!-- Modal ATM (Tambah / Edit) -->
<div id="modalAtm" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <h3 id="atmModalTitle" class="text-lg font-semibold mb-4">Tambah ATM</h3>
        <form id="formAtm" class="space-y-3">
            <input type="hidden" id="atmId">
            <div>
                <label class="block text-sm font-medium">Nama ATM / Wallet</label>
                <input type="text" id="atmName" class="w-full border rounded p-2" required placeholder="Bank A - Tabungan">
            </div>
            <div>
                <label class="block text-sm font-medium">Nomor / Keterangan</label>
                <input type="text" id="atmNote" class="w-full border rounded p-2" placeholder="No. Rek / Catatan (opsional)">
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" id="btnCancelAtm" class="px-4 py-2 border rounded">Batal</button>
                <button type="submit" id="btnSaveAtm" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
/*
  Settings FE (final fix dengan format rupiah diperbaiki)
  - Format angka menggunakan titik sebagai pemisah ribuan (50.000, 500.000)
  - Endpoint sudah disesuaikan dengan route web.php (/settings/...)
  - Pakai Notyf untuk notifikasi & SweetAlert2 untuk konfirmasi hapus
*/
function formatRupiah(angka) {
    // Pastikan angka adalah number, lalu bulatkan ke integer
    const rounded = Math.round(Number(angka));
    return rounded.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Fungsi untuk mengubah format rupiah kembali ke angka
function parseRupiah(rupiahString) {
    if (!rupiahString) return 0;
    return Number(rupiahString.toString().replace(/\./g, '')) || 0;
}

document.addEventListener('DOMContentLoaded', () => {
    // Helpers
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const notyf = new Notyf({ duration: 3000 });
    const atmListEl = document.getElementById('atmList');

    // Toggle UI helper
    const toggleBox = (el, checked) => {
        el.style.backgroundColor = checked ? '#34D399' : '#d1d5db'; 
        el.innerHTML = checked ? '<div class="absolute right-0.5 top-0.5 bg-white w-5 h-5 rounded-full transform translate-x-0.5"></div>' : '';
    };

    // Elements
    const btnAddAtm = document.getElementById('btnAddAtm');
    const modalAtm = document.getElementById('modalAtm');
    const formAtm = document.getElementById('formAtm');
    const atmModalTitle = document.getElementById('atmModalTitle');
    const atmIdInput = document.getElementById('atmId');
    const atmNameInput = document.getElementById('atmName');
    const atmNoteInput = document.getElementById('atmNote');
    const btnCancelAtm = document.getElementById('btnCancelAtm');

    const formTarget = document.getElementById('formTarget');
    const targetName = document.getElementById('targetName');
    const targetAmount = document.getElementById('targetAmount');
    const targetDeadline = document.getElementById('targetDeadline');
    const btnClearTarget = document.getElementById('btnClearTarget');
    const targetStatus = document.getElementById('targetStatus');

    const toggleNotifications = document.getElementById('toggleNotifications');
    const toggleNotificationsBox = document.getElementById('toggleNotificationsBox');
    const abnormalThreshold = document.getElementById('abnormalThreshold');
    const btnSaveAbnormal = document.getElementById('btnSaveAbnormal');

    // Inisialisasi toggle box UI
    toggleBox(toggleNotificationsBox, false);

    // ---------- API helpers ----------
    async function apiGet(path) {
        const res = await fetch(path, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        if (!res.ok) throw new Error('Gagal ambil data');
        return res.json();
    }

    async function apiPost(path, body) {
        const res = await fetch(path, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(body)
        });
        return res;
    }

    async function apiPut(path, body) {
        const res = await fetch(path, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify(body)
        });
        return res;
    }

    async function apiDelete(path) {
        const res = await fetch(path, {
            method: 'DELETE',
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        return res;
    }

    // ---------- Load initial settings ----------
    async function loadSettings() {
        try {
            const data = await apiGet('/settings');
            applySettingsToUI(data);
        } catch (err) {
            console.error(err);
            notyf.error('Gagal memuat pengaturan.');
            renderAtmList([]);
        }
    }

    function applySettingsToUI(data) {
        if (data.target) {
            targetName.value = data.target.name || '';
            // Format angka target ke format rupiah
            targetAmount.value = data.target.amount ? formatRupiah(data.target.amount) : '';
            targetDeadline.value = data.target.deadline ? data.target.deadline.slice(0,10) : '';
            targetStatus.textContent = data.target.active ? 'Aktif' : 'Tidak aktif';
        } else {
            targetName.value = '';
            targetAmount.value = '';
            targetDeadline.value = '';
            targetStatus.textContent = 'Belum ada target';
        }

        const notif = !!data.notifications;
        toggleNotifications.checked = notif;
        toggleBox(toggleNotificationsBox, notif);

        // Format threshold abnormal ke format rupiah
        abnormalThreshold.value = data.abnormal_threshold ? formatRupiah(data.abnormal_threshold) : '';
        renderAtmList(data.atms || []);
    }

    // ---------- Render ATM list ----------
    function renderAtmList(atms) {
        if (!atms || atms.length === 0) {
            atmListEl.innerHTML = '<p class="text-sm text-gray-500">Belum ada ATM / Wallet. Tambah untuk mulai.</p>';
            return;
        }

        atmListEl.innerHTML = '';
        atms.forEach(atm => {
            const div = document.createElement('div');
            div.className = 'flex items-center justify-between p-3 border rounded';

            const left = document.createElement('div');
            left.className = 'flex items-center space-x-3';

            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'activeAtm';
            radio.checked = !!atm.active;
            radio.className = 'mr-2';
            radio.addEventListener('change', () => setActiveAtm(atm.id));

            const info = document.createElement('div');
            info.innerHTML = `<div class="font-medium">${escapeHtml(atm.name)}</div>
                              <div class="text-xs text-gray-500">${escapeHtml(atm.note || '')}</div>`;

            left.appendChild(radio);
            left.appendChild(info);

            const right = document.createElement('div');
            right.className = 'flex items-center space-x-2';

            const editBtn = document.createElement('button');
            editBtn.className = 'px-3 py-1 bg-gray-100 rounded text-sm';
            editBtn.textContent = 'Edit';
            editBtn.addEventListener('click', () => openAtmModal('edit', atm));

            const delBtn = document.createElement('button');
            delBtn.className = 'px-3 py-1 bg-red-600 text-white rounded text-sm';
            delBtn.textContent = 'Hapus';
            delBtn.addEventListener('click', () => confirmDeleteAtm(atm.id));

            right.appendChild(editBtn);
            right.appendChild(delBtn);

            div.appendChild(left);
            div.appendChild(right);

            atmListEl.appendChild(div);
        });
    }

    function escapeHtml(s) {
        if (!s) return '';
        return s.replace(/[&<>"'`]/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#x60;'}[m]));
    }

    // ---------- Actions ----------
    function openAtmModal(mode = 'add', atm = null) {
        atmModalTitle.textContent = mode === 'add' ? 'Tambah ATM / Wallet' : 'Edit ATM';
        atmIdInput.value = atm ? atm.id : '';
        atmNameInput.value = atm ? atm.name : '';
        atmNoteInput.value = atm ? atm.note : '';
        modalAtm.classList.remove('hidden');
    }

    function closeAtmModal() {
        modalAtm.classList.add('hidden');
        formAtm.reset();
    }

    formAtm.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = atmIdInput.value;
        const payload = {
            name: atmNameInput.value.trim(),
            note: atmNoteInput.value.trim()
        };
        try {
            let res;
            if (id) {
                res = await apiPut(`/settings/wallets/${id}`, payload);
            } else {
                res = await apiPost('/settings/wallets', payload);
            }
            if (!res.ok) {
                const err = await res.json().catch(()=>({message:'Gagal simpan ATM'}));
                throw new Error(err.message || 'Gagal simpan ATM');
            }
            notyf.success('ATM tersimpan');
            closeAtmModal();
            await loadSettings();
        } catch (err) {
            console.error(err);
            notyf.error(err.message || 'Error');
        }
    });

    btnCancelAtm.addEventListener('click', closeAtmModal);
    btnAddAtm.addEventListener('click', () => openAtmModal('add'));

    async function confirmDeleteAtm(id) {
        const { isConfirmed } = await Swal.fire({
            title: 'Hapus ATM?',
            text: 'ATM ini akan dihapus dari daftar.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
        });
        if (!isConfirmed) return;
        try {
            const res = await apiDelete(`/settings/wallets/${id}`);
            if (!res.ok) throw new Error('Gagal hapus ATM');
            notyf.success('ATM dihapus');
            await loadSettings();
        } catch (err) {
            console.error(err);
            notyf.error('Gagal hapus ATM');
        }
    }

    async function setActiveAtm(id) {
        try {
            const res = await apiPut(`/settings/wallets/${id}/activate`, {});
            if (!res.ok) throw new Error('Gagal set active');
            notyf.success('ATM di-set aktif');
            await loadSettings();
        } catch (err) {
            console.error(err);
            notyf.error('Gagal set active ATM');
        }
    }

    // ---------- Target actions ----------
    formTarget.addEventListener('submit', async (e) => {
        e.preventDefault();
        const payload = {
            name: targetName.value.trim(),
            // Parse format rupiah kembali ke angka untuk dikirim ke server
            amount: parseRupiah(targetAmount.value),
            deadline: targetDeadline.value || null,
        };
        try {
            const res = await apiPost('/settings/saving-goals', payload);
            if (!res.ok) {
                const err = await res.json().catch(()=>({message:'Gagal simpan target'}));
                throw new Error(err.message || 'Gagal simpan target');
            }
            notyf.success('Target disimpan');
            await loadSettings();
        } catch (err) {
            console.error(err);
            notyf.error(err.message || 'Error simpan target');
        }
    });

    btnClearTarget.addEventListener('click', async () => {
        try {
            const res = await apiDelete('/settings/saving-goals');
            if (!res.ok) throw new Error('Gagal hapus target');
            notyf.success('Target dihapus');
            await loadSettings();
        } catch (err) {
            console.error(err);
            notyf.error('Gagal hapus target');
        }
    });

    // ---------- Notifications toggle ----------
    toggleNotifications.addEventListener('change', async () => {
        const checked = toggleNotifications.checked;
        toggleBox(toggleNotificationsBox, checked);
        try {
            const res = await apiPut('/settings/notifications', { notifications: checked });
            if (!res.ok) throw new Error('Gagal update notifikasi');
            notyf.success(checked ? 'Notifikasi diaktifkan' : 'Notifikasi dimatikan');
        } catch (err) {
            console.error(err);
            notyf.error('Gagal update notifikasi');
            toggleNotifications.checked = !checked;
            toggleBox(toggleNotificationsBox, !checked);
        }
    });

    // ---------- Abnormal threshold ----------
    btnSaveAbnormal.addEventListener('click', async () => {
        // Parse format rupiah kembali ke angka untuk dikirim ke server
        const val = parseRupiah(abnormalThreshold.value);
        try {
            const res = await apiPut('/settings/abnormal-threshold', { threshold: val });
            if (!res.ok) throw new Error('Gagal simpan threshold');
            notyf.success('Threshold disimpan');
        } catch (err) {
            console.error(err);
            notyf.error('Gagal simpan threshold');
        }
    });

    // 👇 FORMAT INPUT ANGKA SECARA REAL-TIME (DIPERBAIKI)
    function setupRupiahInput(inputId) {
        const input = document.getElementById(inputId);
        if (!input) return;

        let isUpdating = false;

        input.addEventListener('input', function(e) {
            if (isUpdating) return; // Hindari loop

            // Ambil posisi cursor sebelum update
            const cursorPosition = e.target.selectionStart;
            const oldLength = e.target.value.length;

            // Hapus semua karakter non-digit
            let value = e.target.value.replace(/[^0-9]/g, '');

            // Jika tidak ada angka, kosongkan
            if (value === '') {
                e.target.value = '';
                return;
            }

            // Hindari leading zero kecuali angka 0 saja
            if (value.length > 1 && value.startsWith('0')) {
                value = value.replace(/^0+/, '');
            }

            // Format ke rupiah
            isUpdating = true;
            const formattedValue = formatRupiah(value);
            e.target.value = formattedValue;
            
            // Hitung posisi cursor baru
            const newLength = formattedValue.length;
            const lengthDiff = newLength - oldLength;
            const newCursorPosition = cursorPosition + lengthDiff;
            
            // Set posisi cursor
            e.target.setSelectionRange(newCursorPosition, newCursorPosition);
            
            isUpdating = false;
        });

        // Ketika fokus keluar, pastikan format benar
        input.addEventListener('blur', function() {
            if (this.value === '') return;
            
            const cleanValue = this.value.replace(/[^0-9]/g, '');
            if (cleanValue) {
                this.value = formatRupiah(cleanValue);
            } else {
                this.value = '';
            }
        });

        // Handle paste event
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const cleanPaste = paste.replace(/[^0-9]/g, '');
            
            if (cleanPaste && cleanPaste !== '0'.repeat(cleanPaste.length)) {
                // Hindari leading zeros
                const cleanedPaste = cleanPaste.replace(/^0+/, '') || '0';
                this.value = formatRupiah(cleanedPaste);
            }
        });

        // Handle keydown untuk navigasi dan delete
        input.addEventListener('keydown', function(e) {
            // Allow: backspace, delete, tab, escape, enter
            if ([8, 9, 27, 13, 46].indexOf(e.keyCode) !== -1 ||
                // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Allow: home, end, left, right
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    }

    // Terapkan ke kedua input
    setupRupiahInput('targetAmount');
    setupRupiahInput('abnormalThreshold');

    modalAtm.addEventListener('click', (e) => {
        if (e.target === modalAtm) closeAtmModal();
    });

    // Init
    loadSettings();
});
</script>

@endpush
