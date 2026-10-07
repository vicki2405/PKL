<script setup>
import PembimbingLayout from '@/Layouts/PembimbingLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, watch, ref } from 'vue';

const props = defineProps({
    bengkels: Array,
    currentDate: String,
    currentDateFormatted: String,
    currentMonthInfo: Object,
    defaultSession: Number,
});

const form = useForm({
    bengkel_id: '',
    session: props.defaultSession || 1,
    tanggal_kunjungan: props.currentDate,
    catatan_monitoring: '',
    student_notes: [],
});

const selectedBengkel = computed(() => {
    return props.bengkels.find(b => b.id === form.bengkel_id);
});

const currentMonthName = computed(() => {
    return selectedBengkel.value?.month_name || props.currentMonthInfo?.month_name || '';
});

const currentMonthNumber = computed(() => {
    return selectedBengkel.value?.current_month || props.currentMonthInfo?.month || 1;
});

watch(() => form.bengkel_id, (newVal) => {
    const bengkel = props.bengkels.find(b => b.id === newVal);
    if (bengkel && bengkel.students) {
        form.student_notes = bengkel.students.map(student => ({
            siswa_id: student.id,
            nama: student.nama,
            catatan: '',
            is_pelanggaran: false,
            hadir: 0,
            izin: 0,
            sakit: 0,
            alpha: 0
        }));
    } else {
        form.student_notes = [];
    }
});

const onPelanggaranToggle = (note) => {
    if (!note.is_pelanggaran) {
        note.catatan = '';
    }
};

const submit = () => {
    form.post(route('pembimbing.monitoring.store'));
};
</script>

<template>
    <Head title="Tambah Monitoring" />

    <PembimbingLayout>
        <template #header>
            Tambah Monitoring
        </template>

        <div class="max-w-3xl mx-auto p-4 md:p-6 animate-fade-in">
            <div class="mb-8">
                <Link :href="route('pembimbing.monitoring.index')" class="inline-flex items-center text-sm font-medium text-slate-500 hover:text-indigo-600 transition-colors mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Kembali ke Riwayat
                </Link>
                <h1 class="text-3xl font-bold text-slate-800">Laporan Monitoring Baru</h1>
                <p class="text-slate-500 mt-2 text-lg">Catat hasil kunjungan monitoring rutin ke bengkel siswa.</p>
            </div>

            <form @submit.prevent="submit" class="bg-white rounded-3xl shadow-xl border border-slate-100 overflow-hidden">
                <div class="p-8 space-y-8">

                    <!-- Grid: Lokasi Bengkel & Pilihan Monitoring 1 atau 2 -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Lokasi Bengkel Selection -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-slate-700 uppercase tracking-wide">Lokasi Bengkel <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select v-model="form.bengkel_id" class="w-full appearance-none rounded-xl border-slate-200 bg-slate-50 py-3 pl-4 pr-10 text-slate-800 font-medium focus:border-indigo-500 focus:bg-white focus:ring-indigo-500 transition-all cursor-pointer">
                                    <option value="" disabled>Pilih Bengkel Tujuan</option>
                                    <option v-for="bengkel in bengkels" :key="bengkel.id" :value="bengkel.id">
                                        {{ bengkel.nama_bengkel }} {{ bengkel.count_in_month >= 2 ? '(Target 2/2 Selesai)' : '' }}
                                    </option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <p v-if="form.errors.bengkel_id" class="text-red-500 text-sm font-medium mt-1 flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                {{ form.errors.bengkel_id }}
                            </p>
                        </div>

                        <!-- Dropdown Pilihan Monitoring 1 atau 2 -->
                        <div class="space-y-2">
                            <label class="block text-sm font-bold text-slate-700 uppercase tracking-wide">
                                Sesi Monitoring <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select v-model.number="form.session" class="w-full appearance-none rounded-xl border-slate-200 bg-slate-50 py-3 pl-4 pr-10 text-slate-800 font-bold focus:border-indigo-500 focus:bg-white focus:ring-indigo-500 transition-all cursor-pointer">
                                    <option :value="1">Monitoring 1</option>
                                    <option :value="2">Monitoring 2</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-slate-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <p v-if="form.errors.session" class="text-red-500 text-sm font-medium mt-1 flex items-center gap-1">
                                {{ form.errors.session }}
                            </p>
                        </div>
                    </div>

                    <!-- Informasi Bulan (Hanya Nama Bulan, Tanpa Tulisan Bulan 1 dan Tanpa Rentang Tanggal) -->
                    <div v-if="selectedBengkel" class="rounded-2xl border p-4 transition-all duration-300"
                         :class="selectedBengkel.already_input_today ? 'bg-rose-50 border-rose-200' : (selectedBengkel.count_in_month >= 2 ? 'bg-amber-50 border-amber-200' : 'bg-gradient-to-r from-indigo-50/80 to-slate-50 border-indigo-100 shadow-sm')">
                        <!-- Jika Hari ini sudah input -->
                        <div v-if="selectedBengkel.already_input_today" class="flex items-start gap-3 text-rose-800">
                            <div class="p-2 rounded-xl bg-rose-100 text-rose-600 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-rose-900">Laporan Hari Ini Sudah Dibuat</h4>
                                <p class="text-xs text-rose-700 mt-0.5 leading-relaxed">
                                    Laporan monitoring untuk <strong>{{ selectedBengkel.nama_bengkel }}</strong> pada hari ini sudah tersimpan. Sistem hanya mengizinkan 1 kunjungan per hari untuk bengkel yang sama.
                                </p>
                            </div>
                        </div>

                        <!-- Jika Kuota Bulan Ini Sudah 2x -->
                        <div v-else-if="selectedBengkel.count_in_month >= 2" class="flex items-start gap-3 text-amber-800">
                            <div class="p-2 rounded-xl bg-amber-100 text-amber-700 shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div>
                                <h4 class="font-bold text-sm text-amber-900">Target Monitoring Bulan Ini Sudah Terpenuhi</h4>
                                <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                                    Laporan monitoring untuk <strong>{{ selectedBengkel.nama_bengkel }}</strong> pada <strong>Bulan Ke-{{ currentMonthNumber }} ({{ currentMonthName }})</strong> sudah lengkap (<strong>{{ selectedBengkel.count_in_month }} / 2 kunjungan</strong>). Anda tidak dapat menambah laporan baru lagi untuk bengkel ini di bulan ini.
                                </p>
                            </div>
                        </div>

                        <!-- Info Bulan Ke-X dan Nama Bulan (Tanpa Rentang Tanggal) -->
                        <div v-else class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-xs text-slate-500 font-medium">Periode Monitoring:</div>
                                    <div class="text-base font-extrabold text-slate-800 flex items-center gap-2 mt-0.5">
                                        <span class="px-2.5 py-0.5 rounded-lg bg-indigo-100 text-indigo-700 text-xs font-black border border-indigo-200">
                                            Bulan Ke-{{ currentMonthNumber }}
                                        </span>
                                        <span>{{ currentMonthName }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right sm:border-l sm:pl-4 border-indigo-100 flex sm:flex-col items-center sm:items-end justify-between">
                                <span class="text-[11px] font-medium text-slate-500">Progres Bulan Ini (Target 2x):</span>
                                <span class="text-sm font-black text-indigo-700">
                                    {{ selectedBengkel.count_in_month || 0 }} / 2 Monitoring
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Tanggal Kunjungan (Terkunci Otomatis Hari Ini) -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-bold text-slate-700 uppercase tracking-wide">
                                Tanggal Kunjungan <span class="text-red-500">*</span>
                            </label>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                                Terkunci Otomatis (Hari Ini)
                            </span>
                        </div>
                        <div class="relative">
                            <input 
                                type="text" 
                                :value="props.currentDateFormatted || props.currentDate" 
                                readonly 
                                disabled
                                class="w-full rounded-xl border-slate-200 bg-slate-100/80 py-3 px-4 pl-11 text-slate-700 font-bold cursor-not-allowed select-none shadow-inner"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500">
                            Tanggal dikunci otomatis oleh sistem sesuai hari ini untuk menjamin keaslian waktu pelaporan dan mencatat progres di bulan yang sedang berjalan.
                        </p>
                        <p v-if="form.errors.tanggal_kunjungan" class="text-red-500 text-sm font-medium mt-1">{{ form.errors.tanggal_kunjungan }}</p>
                    </div>


                    <!-- Catatan -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-slate-700 uppercase tracking-wide">Catatan Umum Monitoring <span class="text-red-500">*</span></label>
                        <textarea v-model="form.catatan_monitoring" rows="4" class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 px-4 text-slate-800 font-medium focus:border-indigo-500 focus:bg-white focus:ring-indigo-500 transition-all placeholder:text-slate-400" placeholder="Tuliskan detail kegiatan umum, kendala teknis, atau catatan dari pembimbing lapangan..."></textarea>
                        <p v-if="form.errors.catatan_monitoring" class="text-red-500 text-sm font-medium mt-1">{{ form.errors.catatan_monitoring }}</p>
                    </div>

                    <!-- Catatan Per Siswa -->
                    <div v-if="form.student_notes.length > 0" class="space-y-6 pt-6 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xl font-bold text-slate-800">Evaluasi Siswa</h3>
                            <span class="text-sm font-medium text-slate-500 bg-slate-100 px-3 py-1 rounded-full">{{ form.student_notes.length }} Siswa</span>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div v-for="(note, index) in form.student_notes" :key="note.siswa_id"
                                 class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-sm">
                                            {{ note.nama?.charAt(0) || 'S' }}
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-700 leading-tight">{{ note.nama }}</h4>
                                            <p class="text-xs text-slate-400 mt-0.5">Siswa Magang</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Attendance Inputs -->
                                <div class="mb-4">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-2">Evaluasi Kehadiran</label>
                                    <div class="grid grid-cols-2 gap-3">
                                        <!-- Hadir -->
                                        <div class="bg-emerald-50 rounded-xl p-3 border border-emerald-100 focus-within:ring-2 focus-within:ring-emerald-500 focus-within:border-transparent transition-all relative overflow-hidden">
                                            <div class="absolute right-0 top-0 p-1 opacity-20">
                                                <svg class="w-8 h-8 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </div>
                                            <label class="block text-[10px] font-bold text-emerald-700 uppercase mb-1">Hadir</label>
                                            <div class="flex items-center">
                                                <input type="number" min="0" v-model="note.hadir" class="w-full bg-transparent border-none p-0 text-xl font-bold text-emerald-800 focus:ring-0 placeholder-emerald-300" placeholder="0">
                                                <span class="text-xs text-emerald-600 font-medium ml-1">Hari</span>
                                            </div>
                                        </div>

                                        <!-- Izin -->
                                        <div class="bg-blue-50 rounded-xl p-3 border border-blue-100 focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent transition-all relative overflow-hidden">
                                             <div class="absolute right-0 top-0 p-1 opacity-20">
                                                <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                            <label class="block text-[10px] font-bold text-blue-700 uppercase mb-1">Izin</label>
                                            <div class="flex items-center">
                                                <input type="number" min="0" v-model="note.izin" class="w-full bg-transparent border-none p-0 text-xl font-bold text-blue-800 focus:ring-0 placeholder-blue-300" placeholder="0">
                                                <span class="text-xs text-blue-600 font-medium ml-1">Hari</span>
                                            </div>
                                        </div>

                                        <!-- Sakit -->
                                        <div class="bg-amber-50 rounded-xl p-3 border border-amber-100 focus-within:ring-2 focus-within:ring-amber-500 focus-within:border-transparent transition-all relative overflow-hidden">
                                            <div class="absolute right-0 top-0 p-1 opacity-20">
                                                <svg class="w-8 h-8 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                            <label class="block text-[10px] font-bold text-amber-700 uppercase mb-1">Sakit</label>
                                            <div class="flex items-center">
                                                <input type="number" min="0" v-model="note.sakit" class="w-full bg-transparent border-none p-0 text-xl font-bold text-amber-800 focus:ring-0 placeholder-amber-300" placeholder="0">
                                                <span class="text-xs text-amber-600 font-medium ml-1">Hari</span>
                                            </div>
                                        </div>

                                        <!-- Alpha -->
                                        <div class="bg-red-50 rounded-xl p-3 border border-red-100 focus-within:ring-2 focus-within:ring-red-500 focus-within:border-transparent transition-all relative overflow-hidden">
                                            <div class="absolute right-0 top-0 p-1 opacity-20">
                                                <svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </div>
                                            <label class="block text-[10px] font-bold text-red-700 uppercase mb-1">Alpha</label>
                                            <div class="flex items-center">
                                                <input type="number" min="0" v-model="note.alpha" class="w-full bg-transparent border-none p-0 text-xl font-bold text-red-800 focus:ring-0 placeholder-red-300" placeholder="0">
                                                <span class="text-xs text-red-600 font-medium ml-1">Hari</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                    <div>
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wide">Catatan Pelanggaran</span>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" v-model="note.is_pelanggaran" @change="onPelanggaranToggle(note)" class="sr-only peer">
                                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-600"></div>
                                                <span class="ms-2 text-xs font-semibold" :class="note.is_pelanggaran ? 'text-red-600' : 'text-gray-400'">Aktif</span>
                                            </label>
                                        </div>
                                        <textarea v-model="note.catatan" :disabled="!note.is_pelanggaran" rows="3" class="w-full rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3 text-slate-800 text-sm focus:border-indigo-500 focus:bg-white focus:ring-indigo-500 transition-all placeholder:text-slate-400 resize-none disabled:opacity-60 disabled:bg-slate-100 disabled:cursor-not-allowed" :placeholder="note.is_pelanggaran ? 'Jelaskan pelanggaran yang dilakukan...' : 'Catatan pelanggaran dinonaktifkan (aktifkan saklar di atas)'"></textarea>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="bg-slate-50 px-8 py-6 flex items-center justify-end gap-4 border-t border-slate-100">
                    <Link :href="route('pembimbing.monitoring.index')" class="px-6 py-3 rounded-xl text-slate-600 font-bold hover:bg-white hover:text-slate-800 hover:shadow-sm transition-all">
                        Batal
                    </Link>
                    <button type="submit" :disabled="form.processing || Boolean(selectedBengkel?.already_input_today) || Boolean(selectedBengkel?.count_in_month >= 2)" class="inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold shadow-lg hover:shadow-xl hover:from-indigo-700 hover:to-purple-700 transform transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                        <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span v-if="selectedBengkel?.already_input_today">Sudah Input Hari Ini</span>
                        <span v-else-if="selectedBengkel?.count_in_month >= 2">Target Bulan Ini Lengkap (2/2)</span>
                        <span v-else>{{ form.processing ? 'Menyimpan...' : 'Simpan Laporan' }}</span>
                    </button>
                </div>
            </form>
        </div>
    </PembimbingLayout>
</template>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  appearance: none;
  -webkit-appearance: none;
  margin: 0;
}

input[type=number] {
  appearance: textfield;
  -moz-appearance: textfield;
}
</style>
