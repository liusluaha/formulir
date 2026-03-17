<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Form Builder</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Icon Library -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
</head>
<body class="bg-gray-100 font-sans">

    <div class="max-w-6xl mx-auto p-6 grid grid-cols-1 md:grid-cols-4 gap-6">
        
        <!-- SIDEBAR: Menu Pilihan -->
        <div class="md:col-span-1 bg-white p-4 rounded-lg shadow h-fit md:sticky top-4 z-10">
            <h3 class="font-bold text-lg mb-4 text-gray-700">Tambahkan Input</h3>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-1 gap-2">
                <button onclick="addField('text')" class="w-full flex items-center gap-2 p-2 md:p-3 border rounded hover:bg-blue-50 hover:border-blue-500 transition">
                    <i class="fas fa-font text-blue-500 w-5 text-center"></i> <span class="text-sm md:text-base text-left leading-tight">Jawaban Singkat</span>
                </button>
                <button onclick="addField('textarea')" class="w-full flex items-center gap-2 p-2 md:p-3 border rounded hover:bg-blue-50 hover:border-blue-500 transition">
                    <i class="fas fa-align-left text-blue-500 w-5 text-center"></i> <span class="text-sm md:text-base text-left leading-tight">Paragraf</span>
                </button>
                <button onclick="addField('radio')" class="w-full flex items-center gap-2 p-2 md:p-3 border rounded hover:bg-blue-50 hover:border-blue-500 transition">
                    <i class="fas fa-dot-circle text-blue-500 w-5 text-center"></i> <span class="text-sm md:text-base text-left leading-tight">Pilihan Berganda</span>
                </button>
                <button onclick="addField('checkbox')" class="w-full flex items-center gap-2 p-2 md:p-3 border rounded hover:bg-blue-50 hover:border-blue-500 transition">
                    <i class="fas fa-check-square text-blue-500 w-5 text-center"></i> <span class="text-sm md:text-base text-left leading-tight">Kotak Centang</span>
                </button>
                <button onclick="addField('select')" class="w-full flex items-center gap-2 p-2 md:p-3 border rounded hover:bg-blue-50 hover:border-blue-500 transition">
                    <i class="fas fa-caret-square-down text-blue-500 w-5 text-center"></i> <span class="text-sm md:text-base text-left leading-tight">Dropdown</span>
                </button>
                <button onclick="addField('file')" class="w-full flex items-center gap-2 p-2 md:p-3 border rounded hover:bg-blue-50 hover:border-blue-500 transition">
                    <i class="fas fa-file-upload text-blue-500 w-5 text-center"></i> <span class="text-sm md:text-base text-left leading-tight">Upload File</span>
                </button>
                <button onclick="addField('date')" class="w-full flex items-center gap-2 p-2 md:p-3 border rounded hover:bg-blue-50 hover:border-blue-500 transition">
                    <i class="fas fa-calendar text-blue-500 w-5 text-center"></i> <span class="text-sm md:text-base text-left leading-tight">Tanggal</span>
                </button>
                <button onclick="addField('time')" class="w-full flex items-center gap-2 p-2 md:p-3 border rounded hover:bg-blue-50 hover:border-blue-500 transition">
                    <i class="fas fa-clock text-blue-500 w-5 text-center"></i> <span class="text-sm md:text-base text-left leading-tight">Waktu</span>
                </button>
                <button onclick="addField('signature')" class="w-full flex items-center gap-2 p-2 md:p-3 border rounded hover:bg-blue-50 hover:border-blue-500 transition">
                    <i class="fas fa-signature text-blue-500 w-5 text-center"></i> <span class="text-sm md:text-base text-left leading-tight">Tanda Tangan</span>
                </button>
            </div>
        </div>

        <!-- CANVAS: Area Editor -->
        <div class="md:col-span-3 space-y-6">
            <!-- Header Form -->
            <div class="bg-white p-6 rounded-lg shadow border-t-8 border-blue-600">
                <input type="text" id="formTitle" class="text-3xl font-bold w-full border-b-2 border-transparent focus:border-blue-500 outline-none pb-2" placeholder="Judul Formulir" value="Formulir Tanpa Judul">
                <input type="text" id="formDesc" class="w-full mt-2 text-gray-500 outline-none border-b border-transparent focus:border-gray-300" placeholder="Deskripsi Formulir">
                
                <!-- Custom Link Input -->
                <div class="mt-4 flex items-center gap-2 text-sm text-gray-600 bg-gray-50 p-2 rounded border border-gray-200">
                    <i class="fas fa-link text-blue-500"></i>
                    <span class="font-semibold">Link URL:</span>
                    <input type="text" id="formLink" class="flex-1 bg-transparent border-b border-gray-300 focus:border-blue-500 outline-none px-1 text-blue-700 font-mono" placeholder="custom-link-anda (kosongkan untuk auto)">
                </div>

                <!-- Limit Response Toggle -->
                <div class="mt-2 flex items-center gap-2 text-sm text-gray-600 bg-gray-50 p-2 rounded border border-gray-200">
                    <input type="checkbox" id="limitResponse" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
                    <span class="font-semibold">Batasi 1 Respon (User hanya bisa mengisi sekali)</span>
                </div>
            </div>

            <!-- Container Field -->
            <div id="fieldsContainer" class="space-y-4">
                <!-- Field akan muncul di sini -->
            </div>

            <!-- Tombol Simpan -->
            <div class="flex justify-end">
                <button onclick="saveForm()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-bold shadow-lg flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Formulir
                </button>
            </div>
        </div>
    </div>

    <script>
        let fields = [];
        let signaturePads = {};
        let editId = null; // Variabel untuk menyimpan ID form yang diedit

        // URL API untuk mengambil dan menyimpan data
        const API_URL = 'simpan.php';

        function generateId() {
            return Math.random().toString(36).substr(2, 9);
        }

        function addField(type) {
            const id = generateId();
            const field = {
                id: id,
                type: type,
                label: 'Pertanyaan Baru',
                options: ['Opsi 1', 'Opsi 2'], // Default options
                required: false,
                maxSize: 10, // Default 10MB
                allowedTypes: [], // Default all
                validationType: 'text' // Default text
            };
            fields.push(field);
            render();
        }

        function removeField(id) {
            fields = fields.filter(f => f.id !== id);
            render();
        }

        function moveFieldUp(index) {
            if (index > 0) {
                [fields[index - 1], fields[index]] = [fields[index], fields[index - 1]];
                render();
            }
        }

        function moveFieldDown(index) {
            if (index < fields.length - 1) {
                [fields[index], fields[index + 1]] = [fields[index + 1], fields[index]];
                render();
            }
        }

        function updateField(id, key, value) {
            const field = fields.find(f => f.id === id);
            if (field) {
                field[key] = value;
                // Jika mengubah opsi string (dipisahkan koma) menjadi array
                if (key === 'optionsString') {
                    field.options = value.split(',').map(s => s.trim());
                }
            }
        }

        function toggleFileType(id, type) {
            const field = fields.find(f => f.id === id);
            if (field) {
                if (!field.allowedTypes) field.allowedTypes = [];
                if (field.allowedTypes.includes(type)) {
                    field.allowedTypes = field.allowedTypes.filter(t => t !== type);
                } else {
                    field.allowedTypes.push(type);
                }
                render();
            }
        }

        function render() {
            const container = document.getElementById('fieldsContainer');
            container.innerHTML = '';

            fields.forEach((field, index) => {
                let inputPreview = '';
                let optionsEditor = '';

                // Logika Preview Input
                if (field.type === 'text') {
                    inputPreview = `<input type="${field.validationType === 'number' ? 'number' : 'text'}" disabled class="w-full p-2 border bg-gray-50 rounded" placeholder="Jawaban singkat (${field.validationType === 'number' ? 'Angka' : 'Teks'})">`;
                    optionsEditor = `
                        <div class="mt-3 p-3 bg-gray-50 rounded border border-gray-200">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Format Input:</label>
                            <select onchange="updateField('${field.id}', 'validationType', this.value)" class="border rounded p-1 text-sm w-full bg-white">
                                <option value="text" ${!field.validationType || field.validationType === 'text' ? 'selected' : ''}>Teks (Bebas)</option>
                                <option value="number" ${field.validationType === 'number' ? 'selected' : ''}>Angka (Number)</option>
                            </select>
                        </div>
                    `;
                }
                else if (field.type === 'textarea') inputPreview = `<textarea disabled class="w-full p-2 border bg-gray-50 rounded" rows="3" placeholder="Teks jawaban panjang"></textarea>`;
                else if (field.type === 'file') {
                    inputPreview = `<input type="file" disabled class="w-full p-2 border bg-gray-50 rounded">`;
                    optionsEditor = `
                        <div class="mt-3 p-3 bg-gray-50 rounded border border-gray-200">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Pengaturan File:</label>
                            
                            <div class="mb-3">
                                <label class="text-xs text-gray-600 block mb-1">Ukuran Maksimal:</label>
                                <select onchange="updateField('${field.id}', 'maxSize', this.value)" class="border rounded p-1 text-sm w-full bg-white">
                                    <option value="1" ${field.maxSize == 1 ? 'selected' : ''}>1 MB</option>
                                    <option value="5" ${field.maxSize == 5 ? 'selected' : ''}>5 MB</option>
                                    <option value="10" ${field.maxSize == 10 ? 'selected' : ''}>10 MB</option>
                                    <option value="100" ${field.maxSize == 100 ? 'selected' : ''}>100 MB</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-xs text-gray-600 block mb-1">Tipe File yang Diizinkan:</label>
                                <div class="grid grid-cols-2 gap-2 text-sm">
                                    ${['PDF', 'Image', 'Document', 'Spreadsheet', 'Presentation'].map(type => `
                                        <label class="flex items-center gap-1 cursor-pointer">
                                            <input type="checkbox" 
                                                ${field.allowedTypes && field.allowedTypes.includes(type) ? 'checked' : ''}
                                                onchange="toggleFileType('${field.id}', '${type}')"
                                                class="text-blue-600 rounded focus:ring-blue-500"
                                            > ${type}
                                        </label>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    `;
                }
                else if (field.type === 'date') inputPreview = `<input type="date" disabled class="w-full p-2 border bg-gray-50 rounded">`;
                else if (field.type === 'time') inputPreview = `<input type="time" disabled class="w-full p-2 border bg-gray-50 rounded">`;
                else if (field.type === 'signature') {
                    inputPreview = `
                        <div>
                            <canvas id="sig-${field.id}" width="400" height="200" style="border: 2px solid #000; width: 400px; height: 200px; background: white; touch-action: none;"></canvas><br>
                            <button type="button" onclick="clearSignature('${field.id}')" class="mt-2 px-3 py-1 bg-gray-200 rounded hover:bg-gray-300">Hapus</button>
                        </div>
                    `;
                }
                else if (['radio', 'checkbox', 'select'].includes(field.type)) {
                    // Editor Opsi untuk tipe pilihan
                    optionsEditor = `
                        <div class="mt-3 p-3 bg-blue-50 rounded border border-blue-100">
                            <label class="text-xs font-bold text-blue-600">Opsi Jawaban (Pisahkan dengan koma):</label>
                            <input type="text" 
                                value="${field.options.join(', ')}" 
                                oninput="updateField('${field.id}', 'optionsString', this.value)"
                                class="w-full p-2 mt-1 border rounded focus:border-blue-500 outline-none"
                            >
                        </div>
                    `;
                    
                    // Preview Opsi
                    if (field.type === 'select') {
                        inputPreview = `<select disabled class="w-full p-2 border bg-gray-50 rounded"><option>Pilih salah satu...</option></select>`;
                    } else {
                        inputPreview = field.options.map(opt => `
                            <div class="flex items-center gap-2 mt-1 text-gray-500">
                                <input type="${field.type === 'radio' ? 'radio' : 'checkbox'}" disabled>
                                <span>${opt}</span>
                            </div>
                        `).join('');
                    }
                }

                const isFirst = index === 0;
                const isLast = index === fields.length - 1;

                // HTML Template untuk Kartu Field
                const html = `
                    <div class="bg-white p-6 rounded-lg shadow relative group border-l-4 border-transparent hover:border-blue-500 transition-all">
                        <div class="flex justify-between items-start mb-4">
                            <div class="w-full mr-4">
                                <input type="text" 
                                    value="${field.label}" 
                                    oninput="updateField('${field.id}', 'label', this.value)"
                                    class="w-full p-2 bg-gray-50 border-b-2 border-gray-200 focus:border-blue-500 outline-none font-medium text-lg"
                                    placeholder="Tulis pertanyaan di sini..."
                                >
                            </div>
                            <div class="flex items-center gap-1">
                                <button onclick="moveFieldUp(${index})" class="text-gray-400 hover:text-blue-600 p-2 ${isFirst ? 'invisible' : ''}" title="Geser Naik">
                                    <i class="fas fa-arrow-up"></i>
                                </button>
                                <button onclick="moveFieldDown(${index})" class="text-gray-400 hover:text-blue-600 p-2 ${isLast ? 'invisible' : ''}" title="Geser Turun">
                                    <i class="fas fa-arrow-down"></i>
                                </button>
                                <button onclick="removeField('${field.id}')" class="text-red-400 hover:text-red-600 p-2" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="mb-2">
                            ${inputPreview}
                        </div>

                        ${optionsEditor}

                        <div class="flex justify-end items-center gap-4 mt-4 pt-4 border-t border-gray-100">
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <span>Wajib Diisi</span>
                                <input type="checkbox" 
                                    ${field.required ? 'checked' : ''} 
                                    onchange="updateField('${field.id}', 'required', this.checked)"
                                    class="w-4 h-4 text-blue-600">
                            </label>
                        </div>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', html);
            });

            // Inisialisasi Signature Pad setelah elemen masuk ke DOM
            setTimeout(initSignaturePads, 0);
        }

        function initSignaturePads() {
            signaturePads = {};
            fields.forEach(field => {
                if (field.type === 'signature') {
                    const canvas = document.getElementById(`sig-${field.id}`);
                    if (canvas) {
                        signaturePads[field.id] = new SignaturePad(canvas);
                    }
                }
            });
        }

        function clearSignature(id) {
            if (signaturePads[id]) signaturePads[id].clear();
        }

        // Fungsi untuk mengambil data form jika dalam mode edit
        async function loadFormForEdit() {
            const urlParams = new URLSearchParams(window.location.search);
            editId = urlParams.get('edit_id');

            if (editId) {
                try {
                    // Kita butuh endpoint baru untuk mengambil data form
                    const response = await fetch(`get_form.php?id=${editId}`);
                    if (!response.ok) throw new Error('Form tidak ditemukan atau akses ditolak.');
                    
                    const formData = await response.json();
                    
                    document.getElementById('formTitle').value = formData.title;
                    document.getElementById('formDesc').value = formData.description;
                    document.getElementById('formLink').value = formData.unique_link_id;
                    document.getElementById('limitResponse').checked = (formData.limit_one_response == 1);
                    fields = JSON.parse(formData.form_schema_json);
                    render();
                } catch (error) {
                    alert(error.message);
                    // Redirect jika gagal load
                    window.location.href = 'dashboard.php';
                }
            }
        }

        async function saveForm() {
            const title = document.getElementById('formTitle').value;
            const desc = document.getElementById('formDesc').value;
            const link = document.getElementById('formLink').value;
            const limitOne = document.getElementById('limitResponse').checked ? 1 : 0;
            
            const formData = {
                title: title,
                description: desc, 
                custom_link: link,
                limit_one_response: limitOne,
                fields: fields
            };

            // Tentukan URL, apakah untuk update atau create
            let url = editId ? `${API_URL}?edit_id=${editId}` : API_URL;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(result.error || 'Terjadi kesalahan');
                }

                alert(result.message);
                // Redirect ke dashboard setelah berhasil
                window.location.href = 'dashboard.php';
            } catch (error) {
                console.error('Error:', error);
                alert('Gagal menyimpan formulir: ' + error.message);
            }
        }

        // Panggil fungsi load saat halaman dimuat
        document.addEventListener('DOMContentLoaded', loadFormForEdit);
    </script>
</body>
</html>
