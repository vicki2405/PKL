<script setup>
import PembimbingLayout from '@/Layouts/PembimbingLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, watch, onMounted, ref } from 'vue';

const props = defineProps({
    monitoring: Object,
    bengkels: Array,
    visitDateFormatted: String,
});

const form = useForm({
    _method: 'PUT',
    bengkel_id: props.monitoring.bengkel_id,
    session: props.monitoring.session || 1,
    tanggal_kunjungan: props.monitoring.tanggal_kunjungan ? (typeof props.monitoring.tanggal_kunjungan === 'string' ? props.monitoring.tanggal_kunjungan.substring(0, 10) : props.monitoring.tanggal_kunjungan) : '',
    catatan_monitoring: props.monitoring.catatan_monitoring,
    student_notes: [],
});

const selectedBengkel = computed(() => {
    return props.bengkels.find(b => b.id === form.bengkel_id);
});

function updateStudentNotes(bengkelId, isInit = false) {
    const bengkel = props.bengkels.find(b => b.id === bengkelId);
    if (bengkel && bengkel.students) {
        form.student_notes = bengkel.students.map(student => {
            // If init, try to find existing note
            let existingNote = '';
            let existingPelanggaran = false;
            let existingHadir = 0;
            let existingIzin = 0;
            let existingSakit = 0;
            let existingAlpha = 0;

            if (isInit && props.monitoring.details) {
                const detail = props.monitoring.details.find(d => d.siswa_id === student.id);
                if (detail) {
                    existingNote = detail.catatan || '';
                    existingPelanggaran = detail.is_pelanggaran || false;
                    existingHadir = detail.hadir || 0;
                    existingIzin = detail.izin || 0;
                    existingSakit = detail.sakit || 0;
                    existingAlpha = detail.alpha || 0;
                }
            }

            return {
                siswa_id: student.id,
                nama: student.nama,
                catatan: existingNote,
                is_pelanggaran: existingPelanggaran,
                hadir: existingHadir,
                izin: existingIzin,
                sakit: existingSakit,
                alpha: existingAlpha
            };
        });
    } else {
        form.student_notes = [];
    }
}

// Initialize student notes
onMounted(() => {
    updateStudentNotes(props.monitoring.bengkel_id, true);
});

watch(() => form.bengkel_id, (newVal) => {
    if (newVal !== props.monitoring.bengkel_id) {
        updateStudentNotes(newVal, false);
    }
});

const onPelanggaranToggle = (note) => {
    if (!note.is_pelanggaran) {
        note.catatan = '';
    }
};

const submit = () => {
    form.post(route('pembimbing.monitoring.update', props.monitoring.id), {
        forceFormData: true,
    });
};

const getInitials = (name) => {
    if (!name) return 'S';
    return name
        .trim()
        .split(/\s+/)
        .map(word => word[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
};

const getRandomColor = (id) => {
    const colors = [
        'from-blue-500 to-indigo-600',
        'from-emerald-500 to-teal-600',
        'from-orange-500 to-red-600',
        'from-purple-500 to-pink-600',
        'from-cyan-500 to-blue-600',
    ];
    return colors[id % colors.length];
};
</script>

<template>
    <Head title="Edit Monitoring" />

    <PembimbingLayout>
        <template #header>
            <div class="flex items-center gap-2">
                <Link :href="route('pembimbing.monitoring.index')" class="p-2 rounded-full hover:bg-slate-100 transition-colors text-slate-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <span class="font-bold text-slate-800">Edit Monitoring</span>
            </div>
        </template>

        <div class="max-w-7xl mx-auto p-4 md:p-6 lg:p-8 animate-fade-in-up">
            <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8">

                <!-- Left Column: Informasi Utama -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-24">
                        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Informasi Kunjungan
                        </h2>

                        <div class="space-y-5">
                            <!-- Bengkel -->
                            <div class="group">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5 ml-1">Lokasi Bengkel</label>
                                <div class="relative">
                                    <select v-model="form.bengkel_id" class="w-full appearance-none rounded-xl border-slate-200 bg-slate-50 py-3 px-4 pl-11 text-slate-700 font-medium focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-all cursor-pointer hover:bg-slate-100">
                                        <option value="" disabled>Pilih Bengkel</option>
                                        <option v-for="bengkel in bengkels" :key="bengkel.id" :value="bengkel.id">
                                            {{ bengkel.nama_bengkel }}
                                        </option>
                                    </select>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </div>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                                <p v-if="form.errors.bengkel_id" class="text-red-500 text-xs mt-1 ml-1">{{ form.errors.bengkel_id }}</p>
                            </div>

                            <!-- Sesi Monitoring (Monitoring 1 atau 2) -->
                            <div class="group">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5 ml-1">Sesi Monitoring</label>
                                <div class="relative">
                                    <select v-model.number="form.session" class="w-full appearance-none rounded-xl border-slate-200 bg-slate-50 py-3 px-4 text-slate-700 font-bold focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-all cursor-pointer hover:bg-slate-100">
                                        <option :value="1">Monitoring 1</option>
                                        <option :value="2">Monitoring 2</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </div>
                                <p v-if="form.errors.session" class="text-red-500 text-xs mt-1 ml-1">{{ form.errors.session }}</p>
                            </div>

                            <!-- Tanggal Kunjungan (Terkunci) -->
                            <div class="group">
                                <div class="flex items-center justify-between mb-1.5 ml-1">
                                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide">Tanggal Kunjungan</label>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        Terkunci
                                    </span>
                                </div>
                                <div class="relative">
                                    <input 
                                        type="text" 
                                        :value="props.visitDateFormatted || (form.tanggal_kunjungan ? new Date(form.tanggal_kunjungan).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) : '-')" 
                                        readonly 
                                        disabled
                                        class="w-full rounded-xl border-slate-200 bg-slate-100/80 py-3 px-4 pl-11 text-slate-600 font-bold cursor-not-allowed select-none shadow-inner"
                                    >
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-[11px] text-slate-400 mt-1 ml-1">
                                    Tanggal kunjungan dikunci dan tidak dapat diubah setelah laporan dibuat.
                                </p>
                            </div>


                            <!-- Catatan Umum -->
                            <div class="group">
                                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5 ml-1">Catatan Umum</label>
                                <div class="relative">
                                    <textarea v-model="form.catatan_monitoring" rows="4" class="w-full rounded-xl border-slate-200 bg-slate-50 py-3 px-4 pl-11 text-slate-700 text-sm focus:bg-white focus:border-indigo-500 focus:ring-indigo-500 transition-all placeholder:text-slate-400 resize-none hover:bg-slate-100" placeholder="Tulis catatan umum mengenai kondisi bengkel..."></textarea>
                                    <div class="absolute top-3 left-0 pl-3 pointer-events-none">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 group-focus-within:text-indigo-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </div>
                                </div>
                                <p v-if="form.errors.catatan_monitoring" class="text-red-500 text-xs mt-1 ml-1">{{ form.errors.catatan_monitoring }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Evaluasi Siswa -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 min-h-[calc(100vh-8rem)]">
                         <div class="flex items-center justify-between mb-6">
                            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                Evaluasi Siswa
                            </h2>
                            <div class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-full">
                                {{ form.student_notes.length }} Siswa
                            </div>
                        </div>

                        <div v-if="form.student_notes.length === 0" class="text-center py-12 bg-slate-50 rounded-xl border border-dashed border-slate-200">
                            <p class="text-slate-500 font-medium">Belum ada siswa di bengkel ini.</p>
                            <p class="text-slate-400 text-sm mt-1">Silakan pilih bengkel lain.</p>
                        </div>

                        <div v-else class="grid grid-cols-1 gap-6">
                            <div v-for="(note, index) in form.student_notes" :key="note.siswa_id"
                                 class="relative bg-slate-50/50 rounded-2xl border border-slate-200 p-5 hover:bg-white hover:shadow-md hover:border-indigo-100 transition-all duration-300 group">

                                <!-- Student Header -->
                                <div class="flex items-start gap-4 mb-5">
                                    <div class="h-12 w-12 rounded-xl bg-gradient-to-br shadow-sm flex items-center justify-center text-white font-bold text-lg shrink-0"
                                         :class="getRandomColor(index)">
                                        {{ getInitials(note.nama) }}
                                    </div>
                                    <div class="flex-1 min-w-0 pt-1">
                                        <h3 class="font-bold text-slate-800 text-lg leading-tight truncate">{{ note.nama }}</h3>
                                        <p class="text-xs text-slate-500 font-medium mt-1">Siswa Magang</p>
                                    </div>
                                </div>

                                <div class="grid md:grid-cols-2 gap-6">
                                    <!-- Attendance Stats Inputs -->
                                    <div class="space-y-3">
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide">Rekap Kehadiran</label>
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

                                    <!-- Pelanggaran Input -->
                                    <div class="flex flex-col h-full">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="block text-xs font-bold text-slate-500 uppercase tracking-wide">Catatan Pelanggaran</span>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" v-model="note.is_pelanggaran" @change="onPelanggaranToggle(note)" class="sr-only peer">
                                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-600"></div>
                                                <span class="ms-2 text-xs font-semibold" :class="note.is_pelanggaran ? 'text-red-600' : 'text-gray-400'">Aktif</span>
                                            </label>
                                        </div>
                                        <div class="relative flex-1">
                                            <textarea v-model="note.catatan"
                                                       :disabled="!note.is_pelanggaran"
                                                       class="w-full h-full min-h-[120px] rounded-xl border-slate-200 bg-white py-3 px-4 text-slate-700 text-sm focus:border-indigo-500 focus:ring-indigo-500 transition-all placeholder:text-slate-400 resize-none leading-relaxed disabled:opacity-60 disabled:bg-slate-100 disabled:cursor-not-allowed"
                                                       :placeholder="note.is_pelanggaran ? 'Jelaskan pelanggaran yang dilakukan...' : 'Catatan pelanggaran dinonaktifkan (aktifkan saklar di atas)'"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons (Sticky on Mobile) -->
                <div class="lg:col-span-3 sticky bottom-4 z-10">
                    <div class="bg-white/80 backdrop-blur-md border border-slate-200 shadow-lg rounded-2xl p-4 flex items-center justify-between gap-4 max-w-7xl mx-auto">
                        <div class="text-sm text-slate-500 hidden md:block">
                            Pastikan semua data sudah benar sebelum menyimpan.
                        </div>
                        <div class="flex items-center gap-3 w-full md:w-auto">
                            <Link :href="route('pembimbing.monitoring.index')" class="flex-1 md:flex-none px-6 py-3 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 font-bold transition-colors text-center">
                                Batal
                            </Link>
                            <button type="submit" :disabled="form.processing" class="flex-1 md:flex-none px-8 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-xl font-bold shadow-lg hover:shadow-indigo-500/30 hover:from-indigo-700 hover:to-purple-700 transform transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                <svg v-if="form.processing" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                            </button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </PembimbingLayout>
</template>
