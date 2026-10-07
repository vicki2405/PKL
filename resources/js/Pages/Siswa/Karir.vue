<script setup>
import { computed, ref } from 'vue';
import { usePage, Head, useForm, Link } from '@inertiajs/vue3';
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import {
    mdiSchool,
    mdiBriefcase,
    mdiStore,
    mdiArrowLeft,
    mdiContentSave,
    mdiAccount,
    mdiDomain,
    mdiBookOpenVariant,
    mdiCertificate,
    mdiCalendarClock,
    mdiOfficeBuilding,
    mdiAccountBadge,
    mdiCheckDecagram,
    mdiCash,
    mdiLightbulbOn,
    mdiMapMarker,
    mdiHandshake,
    mdiCreation,
    mdiCheckCircle,
    mdiCompassOutline,
    mdiInformationOutline,
    mdiPencilOutline,
    mdiLockOutline
} from '@mdi/js';
import FormField from '@admin/components/FormField.vue';
import FormControl from '@admin/components/FormControl.vue';
import BaseIcon from '@admin/components/BaseIcon.vue';

const props = defineProps({
    karir: Object,
    siswa: Object,
    canEdit: {
        type: Boolean,
        default: true
    },
    isPortalOpen: {
        type: Boolean,
        default: true
    },
    portalMessage: {
        type: String,
        default: 'Pengisian rencana karier saat ini sedang ditutup oleh pihak sekolah.'
    }
});

const user = usePage().props.auth.user;
const siswa = computed(() => props.siswa);

const form = useForm({
    tipe_karir: props.karir?.tipe_karir || 'kuliah',

    // Kuliah
    nama_universitas: props.karir?.nama_universitas || '',
    status_universitas: props.karir?.status_universitas || '',
    jenjang_studi: props.karir?.jenjang_studi || '',
    program_studi: props.karir?.program_studi || '',
    jalur_masuk: props.karir?.jalur_masuk || '',
    tahun_mulai: props.karir?.tahun_mulai || new Date().getFullYear(),
    // Kerja
    nama_perusahaan: props.karir?.nama_perusahaan || '',
    posisi_kerja: props.karir?.posisi_kerja || '',
    jenis_pekerjaan: props.karir?.jenis_pekerjaan || '',
    kesesuaian_jurusan: props.karir?.kesesuaian_jurusan || '',
    target_mulai_kerja: props.karir?.target_mulai_kerja || '',
    ekspektasi_gaji: props.karir?.ekspektasi_gaji || '',
    lokasi_kerja: props.karir?.lokasi_kerja || '',

    // Wirausaha
    nama_usaha: props.karir?.nama_usaha || '',
    sektor_usaha: props.karir?.sektor_usaha || '',
    status_kepemilikan: props.karir?.status_kepemilikan || '',
    modal_awal: props.karir?.modal_awal || '',
    lokasi_usaha: props.karir?.lokasi_usaha || '',
});

// Option Lists for Dropdowns
const statusOptions = [
    { id: 'PTN', label: 'PTN (Negeri)', desc: 'Perguruan Tinggi Negeri' },
    { id: 'PTS', label: 'PTS (Swasta)', desc: 'Perguruan Tinggi Swasta' },
    { id: 'Kedinasan', label: 'Kedinasan / Politeknik', desc: 'Politeknik & Akademi Kedinasan' },
    { id: 'Luar Negeri', label: 'Luar Negeri', desc: 'Kampus Internasional' },
];

const jenjangOptions = [
    { id: 'D4', label: 'D4 / Sarjana Terapan', desc: 'Diploma 4 (Vokasi 4 Tahun)' },
    { id: 'S1', label: 'S1 (Sarjana)', desc: 'Sarjana Akademik (4 Tahun)' },
];

const jalurOptions = [
    { id: 'SNBP', label: 'SNBP', desc: 'Jalur Raport / Prestasi Undangan' },
    { id: 'SNBT', label: 'SNBT', desc: 'Tes UTBK Nasional' },
    { id: 'Mandiri', label: 'Mandiri', desc: 'Ujian Mandiri Perguruan Tinggi' },
    { id: 'Beasiswa', label: 'Beasiswa', desc: 'Program Beasiswa Penuh/Parsial' },
    { id: 'Lainnya', label: 'Lainnya', desc: 'Jalur Masuk Lainnya' },
];

const currentYear = new Date().getFullYear();
const tahunOptions = Array.from({ length: 8 }, (_, i) => ({
    id: currentYear - 2 + i,
    label: `${currentYear - 2 + i}`
}));

const jenisKerjaOptions = [
    { id: 'Kontrak', label: 'Kontrak (PKWT)' },
    { id: 'Tetap', label: 'Tetap (PKWTT)' },
    { id: 'Magang', label: 'Magang Industri (Internship)' },
    { id: 'Freelance', label: 'Freelance / Pekerja Lepas' },
];

const kesesuaianOptions = [
    { id: 'Sangat Sesuai', label: 'Sangat Sesuai (Linier 100%)' },
    { id: 'Sesuai', label: 'Sesuai (Masih Relevan)' },
    { id: 'Kurang Sesuai', label: 'Kurang Sesuai' },
    { id: 'Tidak Sesuai', label: 'Tidak Sesuai (Lintas Bidang)' },
];

const mulaiKerjaOptions = [
    { id: 'Segera', label: 'Segera Setelah Lulus' },
    { id: '1-3 Bulan', label: '1 - 3 Bulan Setelah Lulus' },
    { id: '3-6 Bulan', label: '3 - 6 Bulan Setelah Lulus' },
    { id: 'Lainnya', label: 'Waktu Lainnya' },
];

const gajiOptions = [
    { id: '< UMK', label: '< UMK Regional (Di Bawah UMK)' },
    { id: 'Setara UMK', label: 'Setara UMK Regional' },
    { id: '> UMK', label: '> UMK Regional (Di Atas UMK)' },
];

const sektorOptions = [
    { id: 'Jasa', label: 'Jasa & Teknologi Informasi (IT)' },
    { id: 'Manufaktur', label: 'Manufaktur, Otomotif & Bengkel' },
    { id: 'Kuliner', label: 'Kuliner & F&B (Makanan/Minuman)' },
    { id: 'Kreatif', label: 'Industri Kreatif & Desain' },
    { id: 'Perdagangan', label: 'Retail & Perdagangan Umum' },
    { id: 'Lainnya', label: 'Sektor Usaha Lainnya' },
];

const kepemilikanOptions = [
    { id: 'Mandiri', label: 'Milik Sendiri (Solo Entrepreneur)' },
    { id: 'Keluarga', label: 'Usaha Keluarga' },
    { id: 'Kemitraan', label: 'Kemitraan / Partnership' },
];

const modalOptions = [
    { id: '< 5 Juta', label: '< Rp 5 Juta (Skala Kecil)' },
    { id: '5 - 20 Juta', label: 'Rp 5 - 20 Juta (Skala Menengah)' },
    { id: '> 20 Juta', label: '> Rp 20 Juta (Skala Besar)' },
];

const submit = () => {
    form.post(route('siswa.karir.update'), {
        preserveScroll: true,
        onSuccess: () => {
            showForm.value = false;
        }
    });
};

const hasSavedKarir = !!props.karir;

const tipeLabel = (tipe) => tipe === 'kuliah' ? 'Kuliah' : tipe === 'kerja' ? 'Kerja' : 'Wirausaha';

const formatDateFull = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }) + ' WIB'
}

const showForm = ref(false);

const formVisible = computed(() => {
    if (!props.canEdit) return false;
    if (!hasSavedKarir) return true;
    return showForm.value;
});

const openEdit = () => {
    if (!props.canEdit || !hasSavedKarir) return;
    showForm.value = true;
};

const cancelEdit = () => {
    showForm.value = false;
};

const switchPathway = (tipe) => {
    if (!props.canEdit) return;
    form.tipe_karir = tipe;
};
</script>

<template>
    <Head title="Rencana Karier Siswa" />

    <SiswaLayout>
        <div class="max-w-6xl mx-auto space-y-6 pb-12">

            <!-- Hero Banner -->
            <div class="relative bg-gradient-to-r from-slate-900 via-slate-800 to-orange-950 rounded-3xl p-6 sm:p-8 overflow-hidden shadow-xl text-white border border-slate-800">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-48 h-48 bg-orange-500 opacity-15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-48 h-48 bg-amber-500 opacity-15 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="space-y-2">
                        <div class="inline-flex items-center gap-2 px-3 py-1 bg-orange-500/20 border border-orange-500/30 rounded-full text-orange-300 text-xs font-bold uppercase tracking-wider">
                            <BaseIcon :path="mdiCompassOutline" size="14" />
                            <span>Perencanaan Masa Depan</span>
                        </div>
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                            Aspirasi & Rencana Karier Siswa
                        </h1>
                        <p class="text-slate-300 text-xs sm:text-sm max-w-2xl leading-relaxed">
                            Pilih dan isi rencana karier Anda setelah lulus SMK (Kuliah, Bekerja, atau Wirausaha). Data ini digunakan oleh pihak sekolah & BKK untuk pendataan serta bimbingan karier.
                        </p>
                    </div>

                    <!-- Saved Status Badge -->
                    <div class="flex-shrink-0">
                        <div v-if="!isPortalOpen" class="bg-rose-500/20 border border-rose-500/40 rounded-2xl p-4 flex items-center gap-3 backdrop-blur-md">
                            <div class="p-2 bg-rose-500/20 rounded-xl text-rose-300">
                                <BaseIcon :path="mdiLockOutline" size="24" />
                            </div>
                            <div>
                                <p class="text-[10px] text-rose-300 font-bold uppercase tracking-wider">Status Portal</p>
                                <p class="font-bold text-rose-200 text-xs sm:text-sm">Ditutup Sekolah</p>
                            </div>
                        </div>
                        <div v-else-if="hasSavedKarir && !canEdit" class="bg-emerald-500/20 border border-emerald-500/40 rounded-2xl p-4 flex items-center gap-3 backdrop-blur-md">
                            <div class="p-2 bg-emerald-500/20 rounded-xl text-emerald-400">
                                <BaseIcon :path="mdiCheckCircle" size="24" />
                            </div>
                            <div>
                                <p class="text-[10px] text-emerald-300 font-bold uppercase tracking-wider">Status Pendataan</p>
                                <p class="font-bold text-emerald-400 text-xs sm:text-sm">Terkunci — {{ tipeLabel(props.karir?.tipe_karir) }}</p>
                            </div>
                        </div>
                        <div v-else-if="hasSavedKarir && canEdit" class="bg-blue-500/20 border border-blue-500/40 rounded-2xl p-4 flex items-center gap-3 backdrop-blur-md">
                            <div class="p-2 bg-blue-500/20 rounded-xl text-blue-400">
                                <BaseIcon :path="mdiPencilOutline" size="24" />
                            </div>
                            <div>
                                <p class="text-[10px] text-blue-300 font-bold uppercase tracking-wider">Status Pendataan</p>
                                <p class="font-bold text-blue-400 text-xs sm:text-sm">Bisa Diubah 1x — {{ tipeLabel(props.karir?.tipe_karir) }}</p>
                            </div>
                        </div>
                        <div v-else class="bg-amber-500/20 border border-amber-500/40 rounded-2xl p-4 flex items-center gap-3 backdrop-blur-md">
                            <div class="p-2 bg-amber-500/20 rounded-xl text-amber-400">
                                <BaseIcon :path="mdiInformationOutline" size="24" />
                            </div>
                            <div>
                                <p class="text-[10px] text-amber-300 font-bold uppercase tracking-wider">Status Pendataan</p>
                                <p class="font-bold text-amber-400 text-xs sm:text-sm">Belum Mengisi Rencana</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Portal Closed Notification Banner (Warna Terang, Teks Pekat & Tegas) -->
            <div v-if="!isPortalOpen" class="bg-rose-50 border-2 border-rose-500 rounded-3xl p-5 shadow-sm flex items-start gap-4">
                <div class="w-10 h-10 rounded-2xl bg-rose-600 text-white flex items-center justify-center shrink-0 shadow-md shadow-rose-600/30">
                    <BaseIcon :path="mdiLockOutline" size="22" />
                </div>
                <div class="flex-1 space-y-1">
                    <h4 class="font-black text-rose-950 text-sm sm:text-base">
                        Pengisian Formulir Rencana Karier Sedang Ditutup
                    </h4>
                    <p class="text-rose-900 text-xs sm:text-sm font-bold leading-relaxed">
                        {{ portalMessage }}
                    </p>
                    <p class="text-rose-800 text-[11px] font-semibold pt-1">
                        Silakan hubungi pihak BKK atau Admin Sekolah jika Anda membutuhkan bantuan terkait rencana karier Anda.
                    </p>
                </div>
            </div>

            <!-- Notification Banner -->
            <div v-if="$page.props.flash?.message" class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-4 text-emerald-800 text-xs sm:text-sm font-bold flex items-center gap-3">
                <BaseIcon :path="mdiCheckCircle" class="text-emerald-600 flex-shrink-0" size="20" />
                <span>{{ $page.props.flash.message }}</span>
            </div>

            <!-- Grid Layout Form & Pathway Selector -->
            <div class="grid grid-cols-1 gap-6 sm:gap-8" :class="{ 'lg:grid-cols-3': canEdit && formVisible }">

                <!-- Left Column: Pathway Selector Buttons -->
                <div v-if="canEdit && formVisible" class="lg:col-span-1 space-y-4">
                    <div class="bg-white rounded-3xl p-5 shadow-sm border border-slate-200/80 sticky top-6">
                        <div class="mb-4">
                            <h2 class="text-base font-extrabold text-slate-800">Langkah 1: Pilih Jalur Karier</h2>
                            <p class="text-slate-500 text-xs mt-0.5">Tentukan arah tujuan utama Anda setelah lulus SMK.</p>
                        </div>

                        <div class="space-y-3">
                            <!-- 1. KULIAH BUTTON -->
                            <button
                                type="button"
                                @click="switchPathway('kuliah')"
                                :class="[
                                    form.tipe_karir === 'kuliah'
                                        ? 'bg-gradient-to-br from-orange-500 to-amber-600 text-white shadow-lg shadow-orange-500/25 ring-2 ring-orange-400 border-transparent'
                                        : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200/70 hover:border-slate-300'
                                ]"
                                class="w-full flex items-center text-left gap-3.5 p-4 rounded-2xl border transition-all duration-200 active:scale-95 group relative overflow-hidden"
                            >
                                <div
                                    :class="[
                                        form.tipe_karir === 'kuliah'
                                            ? 'bg-white/20 text-white'
                                            : 'bg-orange-100 text-orange-600 group-hover:scale-110'
                                    ]"
                                    class="p-2.5 rounded-xl transition-transform duration-200 flex-shrink-0"
                                >
                                    <BaseIcon :path="mdiSchool" size="22" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-xs sm:text-sm tracking-wide">Kuliah</div>
                                    <div
                                        :class="form.tipe_karir === 'kuliah' ? 'text-orange-100' : 'text-slate-500'"
                                        class="text-[10px] sm:text-xs mt-0.5 leading-tight truncate"
                                    >
                                        Pendidikan Tinggi (PTN/PTS/D4/S1)
                                    </div>
                                </div>
                                <div v-if="form.tipe_karir === 'kuliah'">
                                    <span class="w-2.5 h-2.5 rounded-full bg-white block shadow-sm"></span>
                                </div>
                            </button>

                            <!-- 2. KERJA BUTTON -->
                            <button
                                type="button"
                                @click="switchPathway('kerja')"
                                :class="[
                                    form.tipe_karir === 'kerja'
                                        ? 'bg-gradient-to-br from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-500/25 ring-2 ring-indigo-400 border-transparent'
                                        : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200/70 hover:border-slate-300'
                                ]"
                                class="w-full flex items-center text-left gap-3.5 p-4 rounded-2xl border transition-all duration-200 active:scale-95 group relative overflow-hidden"
                            >
                                <div
                                    :class="[
                                        form.tipe_karir === 'kerja'
                                            ? 'bg-white/20 text-white'
                                            : 'bg-indigo-100 text-indigo-600 group-hover:scale-110'
                                    ]"
                                    class="p-2.5 rounded-xl transition-transform duration-200 flex-shrink-0"
                                >
                                    <BaseIcon :path="mdiBriefcase" size="22" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-xs sm:text-sm tracking-wide">Kerja</div>
                                    <div
                                        :class="form.tipe_karir === 'kerja' ? 'text-indigo-100' : 'text-slate-500'"
                                        class="text-[10px] sm:text-xs mt-0.5 leading-tight truncate"
                                    >
                                        Bekerja di Perusahaan / DUDI
                                    </div>
                                </div>
                                <div v-if="form.tipe_karir === 'kerja'">
                                    <span class="w-2.5 h-2.5 rounded-full bg-white block shadow-sm"></span>
                                </div>
                            </button>

                            <!-- 3. WIRAUSAHA BUTTON -->
                            <button
                                type="button"
                                @click="switchPathway('wirausaha')"
                                :class="[
                                    form.tipe_karir === 'wirausaha'
                                        ? 'bg-gradient-to-br from-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-500/25 ring-2 ring-emerald-400 border-transparent'
                                        : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200/70 hover:border-slate-300'
                                ]"
                                class="w-full flex items-center text-left gap-3.5 p-4 rounded-2xl border transition-all duration-200 active:scale-95 group relative overflow-hidden"
                            >
                                <div
                                    :class="[
                                        form.tipe_karir === 'wirausaha'
                                            ? 'bg-white/20 text-white'
                                            : 'bg-emerald-100 text-emerald-600 group-hover:scale-110'
                                    ]"
                                    class="p-2.5 rounded-xl transition-transform duration-200 flex-shrink-0"
                                >
                                    <BaseIcon :path="mdiStore" size="22" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="font-bold text-xs sm:text-sm tracking-wide">Wirausaha</div>
                                    <div
                                        :class="form.tipe_karir === 'wirausaha' ? 'text-emerald-100' : 'text-slate-500'"
                                        class="text-[10px] sm:text-xs mt-0.5 leading-tight truncate"
                                    >
                                        Memulai Bisnis / Mandiri
                                    </div>
                                </div>
                                <div v-if="form.tipe_karir === 'wirausaha'">
                                    <span class="w-2.5 h-2.5 rounded-full bg-white block shadow-sm"></span>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Dynamic Form Content or Premium View Mode -->
                <div class="lg:col-span-2" :class="{ 'lg:col-span-3': !formVisible }">
                    <form v-if="formVisible" @submit.prevent="submit">
                        <div class="bg-white rounded-3xl shadow-sm border border-slate-200/80 overflow-hidden mb-6">
                            <!-- Card Section Header -->
                            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/70 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div
                                        :class="{
                                            'bg-orange-500 text-white': form.tipe_karir === 'kuliah',
                                            'bg-indigo-600 text-white': form.tipe_karir === 'kerja',
                                            'bg-emerald-600 text-white': form.tipe_karir === 'wirausaha'
                                        }"
                                        class="p-2 rounded-xl shadow-xs"
                                    >
                                        <BaseIcon :path="form.tipe_karir === 'kuliah' ? mdiSchool : (form.tipe_karir === 'kerja' ? mdiBriefcase : mdiStore)" size="18" />
                                    </div>
                                    <div>
                                        <h3 class="font-extrabold text-slate-800 text-sm sm:text-base capitalize">
                                            Detail Rencana: {{ form.tipe_karir }}
                                        </h3>
                                        <p class="text-[11px] text-slate-500">Pilih opsi dan lengkapi formulir di bawah ini</p>
                                    </div>
                                </div>

                                <span class="text-[11px] font-bold px-2.5 py-1 bg-orange-100 text-orange-700 rounded-full">Langkah 2</span>
                            </div>

                            <div class="p-5 sm:p-7 space-y-6">

                                <!-- ==================== 1. FORM KULIAH ==================== -->
                                <div v-if="form.tipe_karir === 'kuliah'" class="space-y-6">
                                    <!-- Group 1: Perguruan Tinggi Target -->
                                    <div class="p-4 sm:p-5 rounded-2xl bg-orange-50/40 border border-orange-100/80 space-y-5">
                                        <div class="text-xs font-bold text-orange-800 uppercase tracking-wider flex items-center gap-2">
                                            <BaseIcon :path="mdiDomain" size="16" class="text-orange-500" />
                                            <span>Informasi Perguruan Tinggi Target</span>
                                        </div>

                                        <FormField label="Nama Perguruan Tinggi Target" help="Masukkan nama universitas, politeknik, atau institut tujuan Anda.">
                                            <FormControl v-model="form.nama_universitas" :icon="mdiSchool" placeholder="Contoh: Universitas Airlangga / Politeknik Negeri Malang" required />
                                            <div v-if="form.errors.nama_universitas" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.nama_universitas }}</div>
                                        </FormField>

                                        <!-- Status Perguruan Tinggi (Clean Select Dropdown) -->
                                        <FormField label="Status Perguruan Tinggi" help="Pilih jenis atau status kampus target Anda.">
                                            <select
                                                v-model="form.status_universitas"
                                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all text-slate-800"
                                                required
                                            >
                                                <option value="" disabled>-- Pilih Status Perguruan Tinggi --</option>
                                                <option v-for="opt in statusOptions" :key="opt.id" :value="opt.id">
                                                    {{ opt.label }} — {{ opt.desc }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.status_universitas" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.status_universitas }}</div>
                                        </FormField>

                                        <!-- Jenjang Pendidikan Target (Wide Radio Cards) -->
                                        <FormField label="Jenjang Pendidikan Target">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div
                                                    v-for="opt in jenjangOptions"
                                                    :key="opt.id"
                                                    @click="form.jenjang_studi = opt.id"
                                                    :class="[
                                                        form.jenjang_studi === opt.id
                                                            ? 'bg-orange-50/90 border-orange-500 ring-2 ring-orange-500/20 shadow-sm'
                                                            : 'bg-white hover:bg-slate-50 border-slate-200 hover:border-slate-300'
                                                    ]"
                                                    class="flex items-center justify-between p-4 rounded-2xl border cursor-pointer transition-all active:scale-95"
                                                >
                                                    <div class="flex items-center gap-3">
                                                        <div
                                                            :class="form.jenjang_studi === opt.id ? 'bg-orange-500 text-white' : 'bg-slate-100 text-slate-400'"
                                                            class="p-2.5 rounded-xl transition-colors flex-shrink-0"
                                                        >
                                                            <BaseIcon :path="mdiSchool" size="20" />
                                                        </div>
                                                        <div>
                                                            <div class="font-extrabold text-xs sm:text-sm text-slate-900">{{ opt.label }}</div>
                                                            <div class="text-[11px] text-slate-500 mt-0.5 font-medium">{{ opt.desc }}</div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        :class="form.jenjang_studi === opt.id ? 'border-orange-500 bg-orange-500 text-white' : 'border-slate-300 bg-white'"
                                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all"
                                                    >
                                                        <BaseIcon v-if="form.jenjang_studi === opt.id" :path="mdiCheckCircle" size="12" />
                                                    </div>
                                                </div>
                                            </div>
                                            <div v-if="form.errors.jenjang_studi" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.jenjang_studi }}</div>
                                        </FormField>
                                    </div>

                                    <!-- Group 2: Program Studi & Jalur Masuk -->
                                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/60 border border-slate-200/60 space-y-5">
                                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                            <BaseIcon :path="mdiBookOpenVariant" size="16" class="text-slate-500" />
                                            <span>Detail Program Studi & Rencana Masuk</span>
                                        </div>

                                        <FormField label="Program Studi / Jurusan Target" help="Masukkan nama jurusan kuliah yang ingin Anda ambil.">
                                            <FormControl v-model="form.program_studi" :icon="mdiBookOpenVariant" placeholder="Contoh: Teknik Sipil / Teknik Informatika / Manajemen" required />
                                            <div v-if="form.errors.program_studi" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.program_studi }}</div>
                                        </FormField>

                                        <!-- Jalur Masuk (Clean Select Dropdown) -->
                                        <FormField label="Rencana Jalur Masuk" help="Pilih jalur seleksi atau mekanisme pendaftaran yang Anda rencanakan.">
                                            <select
                                                v-model="form.jalur_masuk"
                                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all text-slate-800"
                                                required
                                            >
                                                <option value="" disabled>-- Pilih Rencana Jalur Masuk --</option>
                                                <option v-for="opt in jalurOptions" :key="opt.id" :value="opt.id">
                                                    {{ opt.label }} — {{ opt.desc }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.jalur_masuk" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.jalur_masuk }}</div>
                                        </FormField>

                                        <!-- Tahun Mulai Kuliah (Clean Select Dropdown) -->
                                        <FormField label="Rencana Tahun Mulai Kuliah">
                                            <select
                                                v-model="form.tahun_mulai"
                                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all text-slate-800"
                                                required
                                            >
                                                <option v-for="opt in tahunOptions" :key="opt.id" :value="opt.id">
                                                    Tahun {{ opt.label }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.tahun_mulai" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.tahun_mulai }}</div>
                                        </FormField>
                                    </div>
                                </div>

                                <!-- ==================== 2. FORM KERJA ==================== -->
                                <div v-else-if="form.tipe_karir === 'kerja'" class="space-y-6">
                                    <!-- Group 1: Perusahaan & Posisi -->
                                    <div class="p-4 sm:p-5 rounded-2xl bg-indigo-50/40 border border-indigo-100/80 space-y-5">
                                        <div class="text-xs font-bold text-indigo-800 uppercase tracking-wider flex items-center gap-2">
                                            <BaseIcon :path="mdiOfficeBuilding" size="16" class="text-indigo-500" />
                                            <span>Informasi Perusahaan & Posisi</span>
                                        </div>

                                        <FormField label="Target Perusahaan / Industri DUDI" help="Masukkan nama perusahaan atau instansi yang ingin Anda lamar.">
                                            <FormControl v-model="form.nama_perusahaan" :icon="mdiOfficeBuilding" placeholder="Contoh: PT. Astra Honda Motor / Bank Mandiri" required />
                                            <div v-if="form.errors.nama_perusahaan" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.nama_perusahaan }}</div>
                                        </FormField>

                                        <FormField label="Rencana Posisi / Jabatan">
                                            <FormControl v-model="form.posisi_kerja" :icon="mdiAccountBadge" placeholder="Contoh: Operator Perakitan / Programmer" required />
                                            <div v-if="form.errors.posisi_kerja" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.posisi_kerja }}</div>
                                        </FormField>

                                        <!-- Jenis Pekerjaan (Clean Select Dropdown) -->
                                        <FormField label="Jenis Pekerjaan">
                                            <select
                                                v-model="form.jenis_pekerjaan"
                                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-slate-800"
                                                required
                                            >
                                                <option value="" disabled>-- Pilih Jenis Pekerjaan --</option>
                                                <option v-for="opt in jenisKerjaOptions" :key="opt.id" :value="opt.id">
                                                    {{ opt.label }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.jenis_pekerjaan" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.jenis_pekerjaan }}</div>
                                        </FormField>
                                    </div>

                                    <!-- Group 2: Relevansi & Ekspektasi -->
                                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/60 border border-slate-200/60 space-y-5">
                                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                            <BaseIcon :path="mdiCheckDecagram" size="16" class="text-slate-500" />
                                            <span>Kesesuaian & Ekspektasi Kerja</span>
                                        </div>

                                        <!-- Kesesuaian Jurusan (Clean Select Dropdown) -->
                                        <FormField label="Kesesuaian Pekerjaan dengan Jurusan SMK" help="Relevansi posisi pekerjaan dengan kompetensi jurusan Anda di sekolah.">
                                            <select
                                                v-model="form.kesesuaian_jurusan"
                                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-slate-800"
                                                required
                                            >
                                                <option value="" disabled>-- Pilih Kesesuaian Jurusan --</option>
                                                <option v-for="opt in kesesuaianOptions" :key="opt.id" :value="opt.id">
                                                    {{ opt.label }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.kesesuaian_jurusan" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.kesesuaian_jurusan }}</div>
                                        </FormField>

                                        <!-- Target Mulai Kerja (Clean Select Dropdown) -->
                                        <FormField label="Target Waktu Mulai Bekerja">
                                            <select
                                                v-model="form.target_mulai_kerja"
                                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-slate-800"
                                                required
                                            >
                                                <option value="" disabled>-- Pilih Target Mulai Bekerja --</option>
                                                <option v-for="opt in mulaiKerjaOptions" :key="opt.id" :value="opt.id">
                                                    {{ opt.label }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.target_mulai_kerja" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.target_mulai_kerja }}</div>
                                        </FormField>

                                        <!-- Ekspektasi Gaji (Clean Select Dropdown) -->
                                        <FormField label="Ekspektasi Pendapatan Awal">
                                            <select
                                                v-model="form.ekspektasi_gaji"
                                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all text-slate-800"
                                                required
                                            >
                                                <option value="" disabled>-- Pilih Ekspektasi Gaji --</option>
                                                <option v-for="opt in gajiOptions" :key="opt.id" :value="opt.id">
                                                    {{ opt.label }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.ekspektasi_gaji" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.ekspektasi_gaji }}</div>
                                        </FormField>

                                        <FormField label="Rencana Lokasi Kerja yang Diinginkan" help="Masukkan nama kota, kabupaten, atau wilayah target lokasi kerja.">
                                            <FormControl v-model="form.lokasi_kerja" :icon="mdiMapMarker" placeholder="Contoh: Surabaya / Jabodetabek / Luar Negeri" required />
                                            <div v-if="form.errors.lokasi_kerja" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.lokasi_kerja }}</div>
                                        </FormField>
                                    </div>
                                </div>

                                <!-- ==================== 3. FORM WIRAUSAHA ==================== -->
                                <div v-else-if="form.tipe_karir === 'wirausaha'" class="space-y-6">
                                    <!-- Group 1: Identitas Usaha -->
                                    <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50/40 border border-emerald-100/80 space-y-5">
                                        <div class="text-xs font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-2">
                                            <BaseIcon :path="mdiLightbulbOn" size="16" class="text-emerald-500" />
                                            <span>Identitas Ide Usaha / Bisnis</span>
                                        </div>

                                        <FormField label="Nama / Merk Bisnis yang Direncanakan" help="Nama toko, merk produk, atau usaha yang ingin didirikan.">
                                            <FormControl v-model="form.nama_usaha" :icon="mdiLightbulbOn" placeholder="Contoh: Bengkel Mandiri Jaya / Coffee Shop" required />
                                            <div v-if="form.errors.nama_usaha" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.nama_usaha }}</div>
                                        </FormField>

                                        <!-- Sektor Usaha (Clean Select Dropdown) -->
                                        <FormField label="Sektor Usaha">
                                            <select
                                                v-model="form.sektor_usaha"
                                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-slate-800"
                                                required
                                            >
                                                <option value="" disabled>-- Pilih Sektor Usaha --</option>
                                                <option v-for="opt in sektorOptions" :key="opt.id" :value="opt.id">
                                                    {{ opt.label }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.sektor_usaha" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.sektor_usaha }}</div>
                                        </FormField>

                                        <!-- Status Kepemilikan (Clean Select Dropdown) -->
                                        <FormField label="Status Kepemilikan Usaha">
                                            <select
                                                v-model="form.status_kepemilikan"
                                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-slate-800"
                                                required
                                            >
                                                <option value="" disabled>-- Pilih Status Kepemilikan --</option>
                                                <option v-for="opt in kepemilikanOptions" :key="opt.id" :value="opt.id">
                                                    {{ opt.label }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.status_kepemilikan" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.status_kepemilikan }}</div>
                                        </FormField>
                                    </div>

                                    <!-- Group 2: Modal & Operasional -->
                                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/60 border border-slate-200/60 space-y-5">
                                        <div class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-2">
                                            <BaseIcon :path="mdiCash" size="16" class="text-slate-500" />
                                            <span>Modal Awal & Operasional</span>
                                        </div>

                                        <!-- Modal Awal (Clean Select Dropdown) -->
                                        <FormField label="Estimasi Kebutuhan Modal Awal">
                                            <select
                                                v-model="form.modal_awal"
                                                class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm font-semibold focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition-all text-slate-800"
                                                required
                                            >
                                                <option value="" disabled>-- Pilih Estimasi Modal Awal --</option>
                                                <option v-for="opt in modalOptions" :key="opt.id" :value="opt.id">
                                                    {{ opt.label }}
                                                </option>
                                            </select>
                                            <div v-if="form.errors.modal_awal" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.modal_awal }}</div>
                                        </FormField>

                                        <FormField label="Rencana Lokasi Usaha" help="Lokasi fisik ruko/rumah atau link toko online (Instagram/Shopee).">
                                            <FormControl v-model="form.lokasi_usaha" :icon="mdiMapMarker" placeholder="Contoh: Ruko Jl. Merdeka No. 10 atau Online Store" required />
                                            <div v-if="form.errors.lokasi_usaha" class="text-rose-600 text-xs mt-1 font-semibold">{{ form.errors.lokasi_usaha }}</div>
                                        </FormField>
                                    </div>
                                </div>

                            </div>

                            <!-- Submit Button Section (Desktop View) -->
                            <div class="bg-slate-50/90 backdrop-blur border-t border-slate-100 p-5 rounded-b-3xl flex items-center justify-between gap-3">
                                <div class="text-xs text-slate-500 hidden sm:flex items-center gap-2">
                                    <BaseIcon :path="mdiInformationOutline" size="16" class="text-orange-500" />
                                    <span v-if="hasSavedKarir">Perubahan ini adalah kesempatan terakhir. Setelah disimpan, data terkunci permanen.</span>
                                    <span v-else>Data karier dapat diisi dan diubah satu kali lagi setelah disimpan.</span>
                                </div>

                                <div class="flex items-center gap-2.5">
                                    <button
                                        v-if="hasSavedKarir"
                                        type="button"
                                        @click="cancelEdit"
                                        class="inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-white border border-slate-200 text-slate-600 font-bold text-sm rounded-2xl hover:bg-slate-50 transition-all active:scale-95"
                                    >
                                        <BaseIcon :path="mdiArrowLeft" size="18" />
                                        <span>Batal</span>
                                    </button>
                                    <button
                                        type="submit"
                                        :disabled="form.processing"
                                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 text-white font-extrabold text-sm rounded-2xl hover:from-orange-600 hover:to-amber-600 transition-all shadow-lg shadow-orange-500/25 active:scale-95"
                                        :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                                    >
                                        <BaseIcon :path="mdiContentSave" size="18" />
                                        <span v-if="hasSavedKarir">Simpan Perubahan</span>
                                        <span v-else>Simpan Aspirasi Karier</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- View Mode: If portal closed and student hasn't filled yet -->
                    <div v-else-if="!hasSavedKarir" class="bg-white rounded-3xl shadow-sm border border-slate-200 p-8 sm:p-12 text-center space-y-4">
                        <div class="w-16 h-16 rounded-3xl bg-rose-100 text-rose-600 flex items-center justify-center mx-auto shadow-sm">
                            <BaseIcon :path="mdiLockOutline" size="32" />
                        </div>
                        <div class="max-w-md mx-auto space-y-2">
                            <h3 class="text-lg font-black text-slate-900">
                                Formulir Pengisian Sedang Ditutup
                            </h3>
                            <p class="text-xs sm:text-sm font-bold text-slate-700 leading-relaxed">
                                {{ portalMessage }}
                            </p>
                            <p class="text-[11px] font-medium text-slate-500 pt-2">
                                Anda belum mengisi data karier dan portal pengisian saat ini sedang dinonaktifkan oleh pihak sekolah.
                            </p>
                        </div>
                    </div>

                    <!-- View Mode: Premium Showcase Card if already has saved data -->
                    <div v-else-if="hasSavedKarir" class="bg-white rounded-3xl shadow-lg border border-slate-200/80 overflow-hidden">
                        <!-- Header Banner with Rich Gradient & Glassmorphism -->
                        <div
                            :class="{
                                'bg-gradient-to-r from-orange-600 via-amber-600 to-orange-700': karir?.tipe_karir === 'kuliah',
                                'bg-gradient-to-r from-indigo-600 via-blue-600 to-indigo-700': karir?.tipe_karir === 'kerja',
                                'bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700': karir?.tipe_karir === 'wirausaha'
                            }"
                            class="p-6 sm:p-8 text-white relative overflow-hidden"
                        >
                            <!-- Ambient Glow Circles -->
                            <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
                            <div class="absolute -left-8 -bottom-8 w-40 h-40 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div class="space-y-1.5">
                                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-white/20 backdrop-blur-md rounded-full text-white text-xs font-bold tracking-wide">
                                        <BaseIcon :path="karir?.tipe_karir === 'kuliah' ? mdiSchool : (karir?.tipe_karir === 'kerja' ? mdiBriefcase : mdiStore)" size="14" />
                                        <span>Rencana Utama: {{ tipeLabel(karir?.tipe_karir) }}</span>
                                    </div>
                                    <h2 class="text-xl sm:text-2xl font-extrabold tracking-tight">Aspirasi Karier Terdaftar</h2>
                                    <p class="text-white/90 text-xs sm:text-sm font-bold" v-if="!isPortalOpen">
                                        Pengisian dan perubahan saat ini ditutup oleh pihak sekolah.
                                    </p>
                                    <p class="text-white/80 text-xs sm:text-sm" v-else-if="canEdit">
                                        Anda masih memiliki 1x kesempatan untuk mengubah data ini.
                                    </p>
                                    <p class="text-white/80 text-xs sm:text-sm" v-else>
                                        Data karier ini telah dikunci permanen di database sekolah.
                                    </p>
                                </div>

                                <!-- Glass Edit Button -->
                                <div v-if="!isPortalOpen" class="px-4 py-1.5 bg-rose-500/30 backdrop-blur-md border border-rose-400/40 text-rose-100 rounded-full font-bold text-xs flex items-center gap-1.5">
                                    <BaseIcon :path="mdiLockOutline" size="14" />
                                    <span>Status: Ditutup Sekolah</span>
                                </div>
                                <button
                                    v-else-if="canEdit"
                                    type="button"
                                    @click="openEdit"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-md text-white font-extrabold text-xs rounded-2xl border border-white/30 transition-all shadow-md active:scale-95 flex-shrink-0 cursor-pointer"
                                >
                                    <BaseIcon :path="mdiPencilOutline" size="16" />
                                    <span>Edit Rencana</span>
                                </button>
                                <div v-else class="px-4 py-1.5 bg-emerald-500/30 backdrop-blur-md border border-emerald-400/40 text-emerald-100 rounded-full font-bold text-xs flex items-center gap-1.5">
                                    <BaseIcon :path="mdiCheckCircle" size="14" />
                                    <span>Status: Terkunci Permanen</span>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content Details -->
                        <div class="p-6 sm:p-8 space-y-6">

                            <!-- 1. KULIAH SHOWCASE -->
                            <div v-if="karir.tipe_karir === 'kuliah'" class="space-y-6">
                                <!-- Hero Target Showcase Box -->
                                <div class="p-6 rounded-3xl bg-gradient-to-br from-orange-50/90 via-amber-50/50 to-orange-50/30 border border-orange-200/80 shadow-xs space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-orange-700 bg-orange-100 px-3 py-1 rounded-full">
                                            🎓 Perguruan Tinggi Target
                                        </span>
                                        <span class="text-xs font-bold text-orange-600 flex items-center gap-1">
                                            <BaseIcon :path="mdiCheckDecagram" size="16" />
                                            <span>Target Utama</span>
                                        </span>
                                    </div>

                                    <div>
                                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight">
                                            {{ karir.nama_universitas }}
                                        </h3>
                                        <p class="text-slate-600 text-xs sm:text-sm font-semibold mt-1">
                                            Program Studi / Jurusan: <span class="text-orange-700 font-extrabold">{{ karir.program_studi }}</span>
                                        </p>
                                    </div>

                                    <!-- Showcase Pill Badges -->
                                    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-orange-200/60">
                                        <span class="px-3 py-1.5 bg-white border border-orange-200 text-orange-800 rounded-xl font-bold text-xs shadow-2xs">
                                            🏫 Status Kampus: {{ karir.status_universitas }}
                                        </span>
                                        <span class="px-3 py-1.5 bg-white border border-orange-200 text-orange-800 rounded-xl font-bold text-xs shadow-2xs">
                                            📜 Jenjang: {{ karir.jenjang_studi }}
                                        </span>
                                        <span class="px-3 py-1.5 bg-white border border-orange-200 text-orange-800 rounded-xl font-bold text-xs shadow-2xs">
                                            🚀 Jalur Masuk: {{ karir.jalur_masuk }}
                                        </span>
                                        <span class="px-3 py-1.5 bg-white border border-orange-200 text-orange-800 rounded-xl font-bold text-xs shadow-2xs">
                                            📅 Mulai Tahun: {{ karir.tahun_mulai }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Grid Detail Cards -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                                        <div class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Perguruan Tinggi</div>
                                        <div class="font-extrabold text-slate-900 text-sm">{{ karir.nama_universitas }}</div>
                                        <div class="text-[11px] text-orange-600 font-semibold">{{ karir.status_universitas }}</div>
                                    </div>

                                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                                        <div class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Program Studi & Jenjang</div>
                                        <div class="font-extrabold text-slate-900 text-sm">{{ karir.program_studi }}</div>
                                        <div class="text-[11px] text-slate-600 font-medium">Jenjang {{ karir.jenjang_studi }}</div>
                                    </div>

                                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                                        <div class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Rencana Masuk</div>
                                        <div class="font-extrabold text-slate-900 text-sm">Jalur {{ karir.jalur_masuk }}</div>
                                        <div class="text-[11px] text-slate-600 font-medium">Tahun {{ karir.tahun_mulai }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. KERJA SHOWCASE -->
                            <div v-else-if="karir.tipe_karir === 'kerja'" class="space-y-6">
                                <!-- Hero Target Showcase Box -->
                                <div class="p-6 rounded-3xl bg-gradient-to-br from-indigo-50/90 via-blue-50/50 to-indigo-50/30 border border-indigo-200/80 shadow-xs space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-700 bg-indigo-100 px-3 py-1 rounded-full">
                                            💼 Target Perusahaan / Industri
                                        </span>
                                        <span class="text-xs font-bold text-indigo-600 flex items-center gap-1">
                                            <BaseIcon :path="mdiCheckDecagram" size="16" />
                                            <span>Target Utama</span>
                                        </span>
                                    </div>

                                    <div>
                                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight">
                                            {{ karir.nama_perusahaan }}
                                        </h3>
                                        <p class="text-slate-600 text-xs sm:text-sm font-semibold mt-1">
                                            Posisi / Jabatan Incaran: <span class="text-indigo-700 font-extrabold">{{ karir.posisi_kerja }}</span>
                                        </p>
                                    </div>

                                    <!-- Showcase Pill Badges -->
                                    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-indigo-200/60">
                                        <span class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-xl font-bold text-xs shadow-2xs">
                                            🏢 Jenis Pekerjaan: {{ karir.jenis_pekerjaan }}
                                        </span>
                                        <span class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-xl font-bold text-xs shadow-2xs">
                                            🎯 Kesesuaian Jurusan: {{ karir.kesesuaian_jurusan }}
                                        </span>
                                        <span class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-xl font-bold text-xs shadow-2xs">
                                            ⏱️ Target Waktu: {{ karir.target_mulai_kerja }}
                                        </span>
                                        <span class="px-3 py-1.5 bg-white border border-indigo-200 text-indigo-800 rounded-xl font-bold text-xs shadow-2xs">
                                            💰 Gaji: {{ karir.ekspektasi_gaji }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Grid Detail Cards -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                                        <div class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Perusahaan & Posisi</div>
                                        <div class="font-extrabold text-slate-900 text-sm">{{ karir.nama_perusahaan }}</div>
                                        <div class="text-[11px] text-indigo-600 font-semibold">{{ karir.posisi_kerja }} ({{ karir.jenis_pekerjaan }})</div>
                                    </div>

                                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                                        <div class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Kesesuaian & Gaji</div>
                                        <div class="font-extrabold text-slate-900 text-sm">{{ karir.kesesuaian_jurusan }}</div>
                                        <div class="text-[11px] text-slate-600 font-medium">Ekspektasi {{ karir.ekspektasi_gaji }}</div>
                                    </div>

                                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                                        <div class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Target Lokasi Kerja</div>
                                        <div class="font-extrabold text-indigo-700 text-sm flex items-center gap-1">
                                            <BaseIcon :path="mdiMapMarker" size="14" />
                                            <span>{{ karir.lokasi_kerja || '-' }}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-medium">Target Mulai: {{ karir.target_mulai_kerja }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. WIRAUSAHA SHOWCASE -->
                            <div v-else-if="karir.tipe_karir === 'wirausaha'" class="space-y-6">
                                <!-- Hero Target Showcase Box -->
                                <div class="p-6 rounded-3xl bg-gradient-to-br from-emerald-50/90 via-teal-50/50 to-emerald-50/30 border border-emerald-200/80 shadow-xs space-y-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-extrabold uppercase tracking-wider text-emerald-700 bg-emerald-100 px-3 py-1 rounded-full">
                                            🏪 Nama / Merk Bisnis Mandiri
                                        </span>
                                        <span class="text-xs font-bold text-emerald-600 flex items-center gap-1">
                                            <BaseIcon :path="mdiCheckDecagram" size="16" />
                                            <span>Target Utama</span>
                                        </span>
                                    </div>

                                    <div>
                                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 leading-tight">
                                            {{ karir.nama_usaha }}
                                        </h3>
                                        <p class="text-slate-600 text-xs sm:text-sm font-semibold mt-1">
                                            Sektor Usaha: <span class="text-emerald-700 font-extrabold">{{ karir.sektor_usaha }}</span>
                                        </p>
                                    </div>

                                    <!-- Showcase Pill Badges -->
                                    <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-emerald-200/60">
                                        <span class="px-3 py-1.5 bg-white border border-emerald-200 text-emerald-800 rounded-xl font-bold text-xs shadow-2xs">
                                            🏷️ Kepemilikan: {{ karir.status_kepemilikan }}
                                        </span>
                                        <span class="px-3 py-1.5 bg-white border border-emerald-200 text-emerald-800 rounded-xl font-bold text-xs shadow-2xs">
                                            💵 Estimasi Modal: {{ karir.modal_awal }}
                                        </span>
                                        <span class="px-3 py-1.5 bg-white border border-emerald-200 text-emerald-800 rounded-xl font-bold text-xs shadow-2xs">
                                            📍 Lokasi Usaha: {{ karir.lokasi_usaha }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Grid Detail Cards -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                                        <div class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Nama & Sektor Usaha</div>
                                        <div class="font-extrabold text-slate-900 text-sm">{{ karir.nama_usaha }}</div>
                                        <div class="text-[11px] text-emerald-600 font-semibold">Sektor {{ karir.sektor_usaha }}</div>
                                    </div>

                                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                                        <div class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Kepemilikan & Modal</div>
                                        <div class="font-extrabold text-slate-900 text-sm">{{ karir.status_kepemilikan }}</div>
                                        <div class="text-[11px] text-slate-600 font-medium">Modal {{ karir.modal_awal }}</div>
                                    </div>

                                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                                        <div class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">Rencana Lokasi Usaha</div>
                                        <div class="font-extrabold text-emerald-800 text-sm flex items-center gap-1">
                                            <BaseIcon :path="mdiMapMarker" size="14" />
                                            <span>{{ karir.lokasi_usaha }}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 font-medium">Bisnis Mandiri Siswa</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Footer Timestamp & Verification Banner -->
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col sm:flex-row items-center justify-between gap-2 text-slate-600 text-xs">
                                <div class="flex items-center gap-2">
                                    <BaseIcon :path="mdiCheckCircle" class="text-emerald-500" size="18" />
                                    <span class="font-bold text-slate-800">Tersimpan Resmi di Database Sekolah & BKK</span>
                                </div>
                                <div v-if="karir.updated_at" class="text-[11px] font-medium text-slate-500 flex items-center gap-1.5">
                                    <BaseIcon :path="mdiCalendarClock" size="14" class="text-orange-500" />
                                    <span>Terakhir Diperbarui: <strong class="text-slate-800">{{ formatDateFull(karir.updated_at) }}</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </SiswaLayout>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
