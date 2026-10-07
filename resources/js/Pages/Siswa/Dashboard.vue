<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { usePage, Head, Link } from '@inertiajs/vue3';
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import BaseIcon from '@admin/components/BaseIcon.vue';
import {
    mdiMapMarker,
    mdiInformation,
    mdiHumanMaleBoard,
    mdiOpenInNew,
    mdiAccountGroup,
    mdiCalendarClock,
    mdiOfficeBuildingMarker,
    mdiMapMarkerRadius,
    mdiSchool,
    mdiTransfer,
    mdiWhatsapp,
    mdiAccountTie,
    mdiBriefcaseCheck,
    mdiStore,
    mdiArrowRight,
    mdiClose,
    mdiCardAccountDetails,
    mdiBullhornOutline
} from '@mdi/js';

const props = defineProps({
    penempatan: Object,
    mutasi: Object,
    informasis: Array,
    siswaProfile: Object,
    karirStatus: Object,
});

const user = computed(() => usePage().props.auth?.user ?? null);
const avatarError = ref(false);

const mutasiStatus = {
    pending: { label: 'Menunggu Review', badge: 'bg-amber-100 text-amber-800 border-amber-200' },
    processing: { label: 'Sedang Diproses', badge: 'bg-sky-100 text-sky-800 border-sky-200' },
    approved: { label: 'Disetujui', badge: 'bg-emerald-100 text-emerald-800 border-emerald-200' },
    rejected: { label: 'Ditolak', badge: 'bg-rose-100 text-rose-800 border-rose-200' }
};

// Safe date formatter
const formatDate = (dateStr, options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) => {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return '-';
        return d.toLocaleDateString('id-ID', options);
    } catch {
        return '-';
    }
};

// Ensure external URLs always start with https:// to prevent 404 relative links
const ensureExternalUrl = (url) => {
    if (!url) return '#';
    const trimmed = String(url).trim();
    if (!/^https?:\/\//i.test(trimmed)) {
        return 'https://' + trimmed;
    }
    return trimmed;
};

// Helper WhatsApp link formatter with minimum length check
const formatWhatsAppLink = (phone, text = '') => {
    if (!phone) return null;
    let clean = String(phone).replace(/\D/g, '');
    if (clean.length < 8) return null;
    if (clean.startsWith('0')) {
        clean = '62' + clean.slice(1);
    } else if (!clean.startsWith('62')) {
        clean = '62' + clean;
    }
    const message = text ? `?text=${encodeURIComponent(text)}` : '';
    return `https://wa.me/${clean}${message}`;
};

// Current Date for Welcome Section
const currentDate = ref('');

// WA Link for Pembimbing
const pembimbingWaLink = computed(() => {
    if (!props.penempatan?.pembimbing?.no_hp) return null;
    const guru = props.penempatan.pembimbing.nama_guru || 'Bapak/Ibu';
    const siswaNama = props.siswaProfile?.nama || user.value?.name || 'Siswa';
    const kelas = props.siswaProfile?.kelas || '-';
    const bengkel = props.penempatan.bengkel?.nama_bengkel || 'Tempat Magang';
    const msg = `Halo ${guru}, saya ${siswaNama} (Kelas ${kelas}), siswa bimbingan PKL di ${bengkel}. Izin menghubungi terkait pelaksanaan kegiatan PKL. Terima kasih.`;
    return formatWhatsAppLink(props.penempatan.pembimbing.no_hp, msg);
});

// WA Link for Bengkel
const bengkelWaLink = computed(() => {
    if (!props.penempatan?.bengkel?.no_hp) return null;
    const bengkel = props.penempatan.bengkel.nama_bengkel || 'Bengkel Mitra';
    const siswaNama = props.siswaProfile?.nama || user.value?.name || 'Siswa';
    const kelas = props.siswaProfile?.kelas || '-';
    const msg = `Halo ${bengkel}, saya ${siswaNama} (Kelas ${kelas}), siswa PKL yang ditempatkan di sini. Izin menghubungi terkait kegiatan PKL. Terima kasih.`;
    return formatWhatsAppLink(props.penempatan.bengkel.no_hp, msg);
});

// Announcement Detail Modal
const selectedPengumuman = ref(null);
const isModalOpen = ref(false);

const openPengumuman = (info) => {
    selectedPengumuman.value = info;
    isModalOpen.value = true;
};

const closePengumuman = () => {
    isModalOpen.value = false;
    selectedPengumuman.value = null;
};

// Keyboard accessibility: ESC key to close modal
const handleKeydown = (e) => {
    if (e.key === 'Escape' && isModalOpen.value) {
        closePengumuman();
    }
};

onMounted(() => {
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    currentDate.value = new Date().toLocaleDateString('id-ID', options);
    window.addEventListener('keydown', handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Head title="Dashboard Siswa" />

    <SiswaLayout>
        <div class="max-w-7xl mx-auto space-y-6 sm:space-y-8">

            <!-- Welcome Banner (Enhanced Responsive Hero) -->
            <div class="relative bg-gradient-to-br from-slate-900 via-slate-800 to-orange-950 rounded-3xl p-6 sm:p-8 overflow-hidden shadow-2xl text-white border border-slate-800">
                <!-- Background Ambient Glows -->
                <div class="absolute top-0 right-0 -mt-12 -mr-12 w-64 h-64 bg-orange-500/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 -mb-12 -ml-12 w-64 h-64 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left w-full sm:w-auto">
                        <!-- Foto Siswa with Ring and Status Indicator -->
                        <div class="relative flex-shrink-0 mx-auto sm:mx-0">
                            <div class="h-28 w-20 sm:h-32 sm:w-24 rounded-2xl overflow-hidden border-2 border-orange-400/40 shadow-xl bg-slate-800 ring-4 ring-white/10 transition-transform duration-300 hover:scale-105">
                                <img
                                    v-if="user && user.profile_photo_url && !avatarError"
                                    :src="user.profile_photo_url"
                                    class="w-full h-full object-cover"
                                    alt="Foto Profil"
                                    @error="avatarError = true"
                                />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-400 font-bold text-2xl sm:text-3xl bg-slate-800">
                                    {{ (siswaProfile?.nama || user?.name || 'S').charAt(0) }}
                                </div>
                            </div>
                            <span class="absolute -bottom-1 -right-1 flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-emerald-500 border-2 border-slate-900"></span>
                            </span>
                        </div>

                        <div class="space-y-2">
                            <!-- Date & Academic Badges -->
                            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-orange-300 text-xs font-semibold border border-white/10 shadow-sm">
                                    <BaseIcon :path="mdiCalendarClock" size="14" />
                                    <span>{{ currentDate }}</span>
                                </span>
                                <span v-if="siswaProfile?.nisn" class="px-2.5 py-1 rounded-full bg-orange-500/20 text-orange-200 border border-orange-400/30 text-xs font-bold">
                                    NISN: {{ siswaProfile.nisn }}
                                </span>
                                <span v-if="siswaProfile?.kelas" class="px-2.5 py-1 rounded-full bg-blue-500/20 text-blue-200 border border-blue-400/30 text-xs font-bold">
                                    {{ siswaProfile.kelas }}
                                </span>
                            </div>

                            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                                Halo, <span class="bg-gradient-to-r from-orange-400 via-amber-300 to-yellow-300 bg-clip-text text-transparent">{{ siswaProfile?.nama || user?.name || 'Siswa' }}</span> 👋
                            </h1>

                            <p v-if="siswaProfile?.jurusan" class="text-orange-200/90 text-xs sm:text-sm font-medium flex items-center justify-center sm:justify-start gap-1.5">
                                <BaseIcon :path="mdiSchool" size="16" class="text-orange-400 flex-shrink-0" />
                                <span>{{ siswaProfile.jurusan }}</span>
                            </p>
                            <p v-else class="text-slate-300 text-xs sm:text-sm max-w-xl leading-relaxed">
                                Selamat datang di Portal PKL. Pantau tempat magang, pembimbing, dan informasi terkini di sini.
                            </p>
                        </div>
                    </div>

                    <!-- Placement Status Badge Box -->
                    <div class="w-full md:w-auto flex-shrink-0">
                        <div v-if="penempatan" class="bg-emerald-500/15 backdrop-blur-md border border-emerald-500/30 rounded-2xl p-4 flex items-center gap-3.5 shadow-lg">
                            <div class="relative bg-emerald-500/25 p-2.5 rounded-xl text-emerald-400">
                                <span class="animate-ping absolute -top-1 -right-1 h-3 w-3 rounded-full bg-emerald-400 opacity-75"></span>
                                <BaseIcon :path="mdiOfficeBuildingMarker" size="24" />
                            </div>
                            <div>
                                <p class="text-[10px] text-emerald-300 font-bold uppercase tracking-wider">Status Magang</p>
                                <p class="font-extrabold text-emerald-400 text-sm sm:text-base flex items-center gap-1.5">
                                    <span>Sedang Berlangsung</span>
                                </p>
                            </div>
                        </div>
                        <div v-else class="bg-amber-500/15 backdrop-blur-md border border-amber-500/30 rounded-2xl p-4 flex items-center gap-3.5 shadow-lg">
                            <div class="bg-amber-500/25 p-2.5 rounded-xl text-amber-400">
                                <BaseIcon :path="mdiInformation" size="24" />
                            </div>
                            <div>
                                <p class="text-[10px] text-amber-300 font-bold uppercase tracking-wider">Status Magang</p>
                                <p class="font-extrabold text-amber-400 text-sm sm:text-base">Belum Ditempatkan</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Action Shortcuts (Aksi Cepat) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
                <!-- Action 1: Profil Saya -->
                <Link
                    :href="route('siswa.profile')"
                    class="group bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-purple-200 transition-all active:scale-[0.98] flex flex-col justify-between"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                            <BaseIcon :path="mdiCardAccountDetails" size="22" />
                        </div>
                    </div>
                    <div>
                        <p class="font-bold text-xs sm:text-sm text-slate-800 group-hover:text-purple-600 transition-colors line-clamp-1">Biodata Profil</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">Kelola foto & data diri</p>
                    </div>
                </Link>

                <!-- Action 2: Status Karier -->
                <Link
                    :href="route('siswa.karir.edit')"
                    class="group bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-orange-200 transition-all active:scale-[0.98] flex flex-col justify-between"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                            <BaseIcon :path="mdiBriefcaseCheck" size="22" />
                        </div>
                        <span
                            v-if="karirStatus?.has_karir"
                            class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-700 uppercase"
                        >
                            Terisi
                        </span>
                        <span
                            v-else
                            class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 text-amber-700 uppercase"
                        >
                            Belum
                        </span>
                    </div>
                    <div>
                        <p class="font-bold text-xs sm:text-sm text-slate-800 group-hover:text-orange-600 transition-colors line-clamp-1">Rencana Karier</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">
                            {{ karirStatus?.has_karir ? (karirStatus.tipe_karir?.toUpperCase() || 'Sudah Terisi') : 'Isi rencana masa depan' }}
                        </p>
                    </div>
                </Link>

                <!-- Action 3: Pengajuan Mutasi -->
                <Link
                    :href="route('siswa.mutasi_pkl.create')"
                    class="group bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-blue-200 transition-all active:scale-[0.98] flex flex-col justify-between"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                            <BaseIcon :path="mdiTransfer" size="22" />
                        </div>
                        <span v-if="mutasi" class="px-2 py-0.5 rounded-full text-[10px] font-extrabold uppercase" :class="mutasiStatus[mutasi.status]?.badge">
                            {{ mutasi.status }}
                        </span>
                    </div>
                    <div>
                        <p class="font-bold text-xs sm:text-sm text-slate-800 group-hover:text-blue-600 transition-colors line-clamp-1">Ajukan Mutasi</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">Pindah lokasi magang</p>
                    </div>
                </Link>

                <!-- Action 4: Navigasi Lokasi Bengkel -->
                <a
                    v-if="penempatan?.bengkel?.google_maps_link"
                    :href="ensureExternalUrl(penempatan.bengkel.google_maps_link)"
                    target="_blank"
                    class="group bg-white p-4 sm:p-5 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md hover:border-emerald-200 transition-all active:scale-[0.98] flex flex-col justify-between"
                >
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-inner">
                            <BaseIcon :path="mdiMapMarkerRadius" size="22" />
                        </div>
                        <BaseIcon :path="mdiOpenInNew" size="16" class="text-slate-400 group-hover:text-emerald-600 transition-colors" />
                    </div>
                    <div>
                        <p class="font-bold text-xs sm:text-sm text-slate-800 group-hover:text-emerald-600 transition-colors line-clamp-1">Petunjuk Arah</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 line-clamp-1">Buka Google Maps</p>
                    </div>
                </a>
                <div
                    v-else
                    class="bg-slate-50/70 p-4 sm:p-5 rounded-2xl border border-dashed border-slate-200 flex flex-col justify-between opacity-60"
                >
                    <div class="w-10 h-10 rounded-xl bg-slate-200 text-slate-400 flex items-center justify-center mb-3">
                        <BaseIcon :path="mdiMapMarkerRadius" size="22" />
                    </div>
                    <div>
                        <p class="font-bold text-xs sm:text-sm text-slate-600">Petunjuk Arah</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Belum ada penempatan</p>
                    </div>
                </div>
            </div>

            <!-- Dashboard Grid (2 Kolom: Kiri Konten Utama, Kanan Widget) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

                <!-- Left Column (Main Content) -->
                <div class="lg:col-span-2 space-y-6 sm:space-y-8">

                    <!-- Status Pengajuan Mutasi Box (If Exists) -->
                    <div v-if="mutasi" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow">
                        <div class="p-5 sm:p-6 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-orange-50/70 via-amber-50/40 to-white">
                            <h2 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
                                <span class="p-1.5 rounded-lg bg-orange-100 text-orange-600">
                                    <BaseIcon :path="mdiTransfer" size="18" />
                                </span>
                                Status Pengajuan Mutasi
                            </h2>
                            <span
                                class="px-3 py-1 rounded-full text-[11px] font-extrabold uppercase tracking-wider border shadow-sm"
                                :class="mutasiStatus[mutasi.status]?.badge || 'bg-slate-100 text-slate-700 border-slate-200'"
                            >
                                {{ mutasiStatus[mutasi.status]?.label || mutasi.status }}
                            </span>
                        </div>
                        <div class="p-5 sm:p-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                                <div>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Bengkel Tujuan</p>
                                    <p class="text-base sm:text-lg font-bold text-slate-800">{{ mutasi.nama_bengkel_baru || '-' }}</p>
                                    <p v-if="mutasi.nama_pemilik_bengkel_baru" class="text-xs text-slate-500 font-medium mt-0.5">
                                        Pemilik / PIC: {{ mutasi.nama_pemilik_bengkel_baru }}
                                    </p>
                                    <div class="flex items-start gap-2 text-slate-600 text-xs sm:text-sm mt-2">
                                        <BaseIcon :path="mdiMapMarker" size="16" class="mt-0.5 flex-shrink-0 text-orange-500" />
                                        <span>{{ mutasi.alamat_bengkel_baru || 'Alamat sedang dikonfirmasi' }}</span>
                                    </div>
                                </div>
                                <div>
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Tanggal Pengajuan</p>
                                    <p class="font-semibold text-slate-700 text-sm sm:text-base">
                                        {{ formatDate(mutasi.created_at) }}
                                    </p>

                                    <div v-if="mutasi.google_maps_link_baru" class="mt-3">
                                        <a :href="ensureExternalUrl(mutasi.google_maps_link_baru)" target="_blank" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-orange-50 text-orange-600 hover:bg-orange-100 font-bold text-xs transition-colors border border-orange-200/50">
                                            <BaseIcon :path="mdiOpenInNew" size="14" />
                                            Buka di Google Maps
                                        </a>
                                    </div>
                                </div>
                                <div class="md:col-span-2" v-if="mutasi.alasan">
                                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Alasan Pengajuan</p>
                                    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 text-slate-700 text-xs sm:text-sm italic">
                                        "{{ mutasi.alasan }}"
                                    </div>
                                </div>
                                <div class="md:col-span-2" v-if="mutasi.admin_note">
                                    <p class="text-[11px] font-bold uppercase tracking-wider mb-1.5" :class="mutasi.status === 'rejected' ? 'text-rose-600' : 'text-blue-600'">
                                        {{ mutasi.status === 'rejected' ? 'Catatan Penolakan Admin' : 'Catatan Admin' }}
                                    </p>
                                    <div class="p-4 rounded-2xl border text-xs sm:text-sm font-medium" :class="mutasi.status === 'rejected' ? 'bg-rose-50 border-rose-100 text-rose-800' : 'bg-blue-50 border-blue-100 text-blue-800'">
                                        {{ mutasi.admin_note }}
                                    </div>
                                </div>
                            </div>

                            <!-- Tindak Lanjut jika Ditolak -->
                            <div v-if="mutasi.status === 'rejected'" class="mt-5 pt-4 border-t border-slate-100 flex justify-end">
                                <Link
                                    :href="route('siswa.mutasi_pkl.create')"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-600 text-white font-bold text-xs hover:bg-orange-700 transition-colors shadow-sm"
                                >
                                    <span>Ajukan Permohonan Baru</span>
                                    <BaseIcon :path="mdiArrowRight" size="14" />
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Active Placement Card (Kartu Detail Penempatan) -->
                    <div v-if="penempatan" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow duration-300">
                        <!-- Card Header & Bengkel Info -->
                        <div class="p-5 sm:p-8 border-b border-slate-100">
                            <div class="flex items-center justify-between gap-4 mb-6">
                                <div class="flex items-center gap-3.5">
                                    <div class="p-3 bg-gradient-to-br from-orange-50 to-amber-100 rounded-2xl text-orange-600 flex-shrink-0 shadow-inner">
                                        <BaseIcon :path="mdiStore" size="26" />
                                    </div>
                                    <div>
                                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-800">Lokasi Penempatan Magang</h2>
                                        <p class="text-xs sm:text-sm text-slate-500">Perusahaan / Bengkel mitra tempat kamu melaksanakan PKL</p>
                                    </div>
                                </div>
                                <span class="hidden sm:inline-flex px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-xs font-bold">
                                    Aktif Magang
                                </span>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-3">
                                    <div>
                                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Bengkel / Industri</p>
                                        <h3 class="text-xl sm:text-2xl font-black text-slate-900 leading-snug">
                                            {{ penempatan.bengkel?.nama_bengkel || '-' }}
                                        </h3>
                                        <p v-if="penempatan.bengkel?.nama_pemilik" class="text-xs font-semibold text-slate-600 mt-1 flex items-center gap-1.5">
                                            <BaseIcon :path="mdiAccountTie" size="14" class="text-slate-400" />
                                            <span>Penanggung Jawab: {{ penempatan.bengkel.nama_pemilik }}</span>
                                        </p>
                                    </div>

                                    <div class="space-y-1.5">
                                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Alamat Lengkap</p>
                                        <div class="flex items-start gap-2 text-slate-700 text-xs sm:text-sm">
                                            <BaseIcon :path="mdiMapMarker" size="18" class="mt-0.5 flex-shrink-0 text-orange-500" />
                                            <span class="leading-relaxed">
                                                {{ penempatan.bengkel?.alamat_lengkap || [penempatan.bengkel?.dusun, penempatan.bengkel?.desa, penempatan.bengkel?.kecamatan].filter(Boolean).join(', ') || 'Alamat belum dilengkapi' }}
                                            </span>
                                        </div>
                                    </div>

                                    <div v-if="penempatan.bengkel?.deskripsi" class="pt-2 text-xs text-slate-500 italic bg-slate-50 p-3 rounded-xl border border-slate-100">
                                        "{{ penempatan.bengkel.deskripsi }}"
                                    </div>
                                </div>

                                <!-- Action Buttons for Bengkel -->
                                <div class="flex flex-col justify-center space-y-3">
                                    <!-- Google Maps Navigation Button -->
                                    <div v-if="penempatan.bengkel?.google_maps_link">
                                        <a :href="ensureExternalUrl(penempatan.bengkel.google_maps_link)" target="_blank" class="block group">
                                            <div class="bg-gradient-to-r from-orange-50 to-amber-50 border border-orange-200/80 rounded-2xl p-4 flex items-center justify-between group-hover:border-orange-400 group-hover:shadow-md transition-all active:scale-[0.98]">
                                                <div class="flex items-center gap-3.5">
                                                    <div class="bg-white p-2.5 rounded-xl shadow-sm group-hover:scale-110 transition-transform">
                                                        <img src="https://www.google.com/images/branding/product/2x/maps_96in128dp.png" alt="Maps" class="w-7 h-7 object-contain">
                                                    </div>
                                                    <div>
                                                        <p class="font-extrabold text-slate-800 text-sm group-hover:text-orange-600 transition-colors">Buka Navigasi Maps</p>
                                                        <p class="text-xs text-slate-500">Rute & lokasi persis bengkel</p>
                                                    </div>
                                                </div>
                                                <BaseIcon :path="mdiOpenInNew" size="20" class="text-orange-500 group-hover:translate-x-0.5 transition-transform" />
                                            </div>
                                        </a>
                                    </div>

                                    <!-- Hubungi Bengkel (WhatsApp / Telepon) -->
                                    <div v-if="bengkelWaLink" class="pt-1">
                                        <a :href="bengkelWaLink" target="_blank" class="block group">
                                            <div class="bg-emerald-50 border border-emerald-200/70 rounded-2xl p-3.5 flex items-center justify-between group-hover:border-emerald-400 group-hover:bg-emerald-100/60 transition-all active:scale-[0.98]">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-lg bg-emerald-500 text-white flex items-center justify-center shadow-sm">
                                                        <BaseIcon :path="mdiWhatsapp" size="18" />
                                                    </div>
                                                    <div>
                                                        <p class="font-bold text-xs text-slate-800 group-hover:text-emerald-800">Hubungi Bengkel via WhatsApp</p>
                                                        <p class="text-[11px] text-emerald-700 font-mono">{{ penempatan.bengkel.no_hp }}</p>
                                                    </div>
                                                </div>
                                                <BaseIcon :path="mdiOpenInNew" size="16" class="text-emerald-600 group-hover:translate-x-0.5 transition-transform" />
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pembimbing Lapangan Box with WhatsApp Contact Action -->
                        <div class="bg-slate-50/80 p-5 sm:p-7 border-t border-slate-100">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-indigo-100 rounded-2xl flex items-center justify-center text-indigo-600 flex-shrink-0 shadow-sm border border-indigo-200/50">
                                        <BaseIcon :path="mdiHumanMaleBoard" size="26" />
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Guru Pembimbing Lapangan</p>
                                        <p class="text-base font-extrabold text-slate-800 truncate">
                                            {{ penempatan.pembimbing?.nama_guru || 'Belum ditugaskan' }}
                                        </p>
                                        <p v-if="penempatan.pembimbing?.no_hp" class="text-xs text-slate-500 font-mono mt-0.5">
                                            No. HP: {{ penempatan.pembimbing.no_hp }}
                                        </p>
                                    </div>
                                </div>

                                <!-- WhatsApp Button to Pembimbing -->
                                <div v-if="pembimbingWaLink">
                                    <a
                                        :href="pembimbingWaLink"
                                        target="_blank"
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md shadow-emerald-600/20 transition-all active:scale-95 w-full sm:w-auto"
                                    >
                                        <BaseIcon :path="mdiWhatsapp" size="18" />
                                        <span>Chat WhatsApp Guru</span>
                                    </a>
                                </div>
                                <div v-else-if="penempatan.pembimbing" class="text-xs text-slate-400 italic">
                                    Nomor kontak pembimbing belum terdaftar
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State (Belum Ditempatkan) -->
                    <div v-else class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 sm:p-12 text-center">
                        <div class="w-20 h-20 bg-amber-50 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-amber-100 shadow-inner">
                            <BaseIcon :path="mdiInformation" size="40" class="text-amber-500" />
                        </div>
                        <h3 class="text-lg sm:text-xl font-black text-slate-800 mb-2">Belum Ada Penempatan PKL</h3>
                        <p class="text-slate-500 text-xs sm:text-sm max-w-md mx-auto mb-6 leading-relaxed">
                            Saat ini kamu belum terdaftar pada lokasi bengkel manapun. Silakan koordinasikan dengan pihak panitia PKL, guru pembimbing, atau wali kelas kamu.
                        </p>
                        <div class="flex justify-center gap-3">
                            <Link
                                :href="route('siswa.profile')"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-colors"
                            >
                                <BaseIcon :path="mdiCardAccountDetails" size="16" />
                                Lengkapi Profil Saya
                            </Link>
                        </div>
                    </div>

                    <!-- Rekan Magang di Bengkel Ini -->
                    <div v-if="penempatan && penempatan.rekan_magang?.length > 0" class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-5 sm:p-6 border-b border-slate-100 flex justify-between items-center bg-slate-50/60">
                            <div class="flex items-center gap-3">
                                <div class="p-2 bg-purple-50 rounded-xl text-purple-600">
                                    <BaseIcon :path="mdiAccountGroup" size="20" />
                                </div>
                                <div>
                                    <h3 class="font-bold text-slate-800 text-sm sm:text-base">Rekan Magang di Bengkel Ini</h3>
                                    <p class="text-[11px] text-slate-500">Siswa lain yang ditempatkan di bengkel yang sama</p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-bold">
                                {{ penempatan.rekan_magang.length }} Siswa
                            </span>
                        </div>

                        <!-- Mobile Card List View (< 640px) -->
                        <div class="block sm:hidden divide-y divide-slate-100">
                            <div
                                v-for="p in penempatan.rekan_magang"
                                :key="p.id"
                                class="p-4 flex items-center justify-between transition-colors"
                                :class="p.is_kamu ? 'bg-orange-50/40' : 'hover:bg-slate-50'"
                            >
                                <div class="flex items-center gap-3 min-w-0">
                                    <div
                                        class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm shadow-sm flex-shrink-0"
                                        :class="p.is_kamu ? 'bg-gradient-to-br from-orange-500 to-amber-500 text-white ring-2 ring-orange-300' : 'bg-gradient-to-br from-slate-200 to-slate-300 text-slate-700'"
                                    >
                                        {{ p.nama?.charAt(0) || '?' }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-800 text-xs truncate">{{ p.nama || '-' }}</p>
                                        <p class="text-[11px] text-slate-500 font-medium">{{ p.kelas || '-' }}</p>
                                    </div>
                                </div>
                                <div>
                                    <span v-if="p.is_kamu" class="px-2.5 py-1 rounded-full bg-orange-100 text-orange-700 text-[10px] font-black tracking-wide uppercase border border-orange-200">Kamu</span>
                                    <span v-else class="text-[11px] text-slate-400 font-medium">Teman</span>
                                </div>
                            </div>
                        </div>

                        <!-- Desktop Table View (≥ 640px) -->
                        <div class="hidden sm:block overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="text-xs text-slate-400 uppercase bg-slate-50/60 tracking-wider">
                                    <tr>
                                        <th class="px-6 py-3.5 font-bold">Nama Siswa</th>
                                        <th class="px-6 py-3.5 font-bold">Kelas</th>
                                        <th class="px-6 py-3.5 font-bold text-right">Peran</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr
                                        v-for="p in penempatan.rekan_magang"
                                        :key="p.id"
                                        class="transition-colors"
                                        :class="p.is_kamu ? 'bg-orange-50/30' : 'hover:bg-slate-50/70'"
                                    >
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold shadow-inner"
                                                    :class="p.is_kamu ? 'bg-gradient-to-br from-orange-500 to-amber-500 text-white ring-2 ring-orange-300' : 'bg-gradient-to-br from-slate-200 to-slate-300 text-slate-700'"
                                                >
                                                    {{ p.nama?.charAt(0) || '?' }}
                                                </div>
                                                <div>
                                                    <p class="font-bold text-slate-800">{{ p.nama || '-' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                                {{ p.kelas || '-' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span v-if="p.is_kamu" class="text-xs font-extrabold text-orange-700 bg-orange-100 border border-orange-200 px-3 py-1 rounded-full uppercase tracking-wider">Kamu</span>
                                            <span v-else class="text-xs text-slate-400 font-medium">Rekan Magang</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- Right Column (Widget Pengumuman & Informasi) -->
                <div class="space-y-6 sm:space-y-8">

                    <!-- Pengumuman Card -->
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden lg:sticky lg:top-8">
                        <div class="p-5 sm:p-6 border-b border-slate-100 bg-gradient-to-r from-orange-600 via-amber-600 to-orange-700 text-white shadow-sm">
                            <div class="flex items-center justify-between">
                                <h3 class="font-extrabold text-base sm:text-lg flex items-center gap-2">
                                    <BaseIcon :path="mdiBullhornOutline" size="20" class="text-orange-200" />
                                    Pengumuman PKL
                                </h3>
                                <span v-if="informasis?.length" class="px-2 py-0.5 rounded-full bg-white/20 text-white text-[11px] font-bold">
                                    {{ informasis.length }} Baru
                                </span>
                            </div>
                            <p class="text-orange-100 text-xs mt-1 font-medium">Informasi & instruksi resmi panitia sekolah</p>
                        </div>

                        <!-- List Pengumuman dengan Aksi Klik Modal -->
                        <div v-if="informasis && informasis.length > 0" class="divide-y divide-slate-100 max-h-[500px] overflow-y-auto">
                            <div
                                v-for="info in informasis"
                                :key="info.id"
                                @click="openPengumuman(info)"
                                class="p-4 sm:p-5 hover:bg-orange-50/50 transition-all cursor-pointer group"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-orange-100 text-orange-800 border border-orange-200/60">
                                        {{ formatDate(info.tanggal_terbit, { day: 'numeric', month: 'short' }) }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-medium">{{ info.tanggal_terbit ? new Date(info.tanggal_terbit).getFullYear() : '-' }}</span>
                                </div>
                                <h5 class="font-bold text-slate-800 text-xs sm:text-sm group-hover:text-orange-600 transition-colors mb-1.5 leading-snug">
                                    {{ info.judul }}
                                </h5>
                                <p class="text-slate-500 text-xs line-clamp-2 leading-relaxed">
                                    {{ info.deskripsi }}
                                </p>
                                <div class="mt-2.5 flex items-center gap-1 text-[11px] font-bold text-orange-600 group-hover:translate-x-1 transition-transform">
                                    <span>Baca selengkapnya</span>
                                    <BaseIcon :path="mdiArrowRight" size="14" />
                                </div>
                            </div>
                        </div>

                        <!-- Empty Announcement State -->
                        <div v-else class="p-8 text-center">
                            <div class="bg-slate-50 w-14 h-14 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-slate-100">
                                <BaseIcon :path="mdiSchool" size="28" class="text-slate-300" />
                            </div>
                            <h4 class="text-slate-800 font-bold text-sm mb-1">Belum Ada Pengumuman</h4>
                            <p class="text-slate-400 text-xs">Pantau terus halaman ini untuk instruksi terbaru.</p>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal Detail Pengumuman -->
            <Teleport to="body">
                <div v-if="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
                    <!-- Backdrop -->
                    <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="closePengumuman"></div>

                    <!-- Modal Container -->
                    <div class="flex min-h-screen items-center justify-center p-4 text-center">
                        <div class="relative w-full max-w-xl transform overflow-hidden rounded-3xl bg-white text-left align-middle shadow-2xl transition-all border border-slate-100">
                            <!-- Modal Header -->
                            <div class="p-6 border-b border-slate-100 bg-gradient-to-r from-orange-500 to-amber-500 text-white flex items-start justify-between gap-4">
                                <div>
                                    <span class="inline-block px-2.5 py-1 rounded-full bg-white/20 text-white text-[10px] font-extrabold uppercase tracking-wider mb-2">
                                        {{ selectedPengumuman?.tanggal_terbit ? formatDate(selectedPengumuman.tanggal_terbit) : '' }}
                                    </span>
                                    <h3 class="text-lg sm:text-xl font-extrabold text-white leading-snug">
                                        {{ selectedPengumuman?.judul }}
                                    </h3>
                                </div>
                                <button
                                    @click="closePengumuman"
                                    class="p-2 rounded-full hover:bg-white/20 text-white transition-colors flex-shrink-0"
                                >
                                    <BaseIcon :path="mdiClose" size="20" />
                                </button>
                            </div>

                            <!-- Modal Body -->
                            <div class="p-6 sm:p-8 space-y-4 max-h-[60vh] overflow-y-auto">
                                <div class="text-slate-700 text-sm sm:text-base leading-relaxed whitespace-pre-line">
                                    {{ selectedPengumuman?.deskripsi }}
                                </div>
                            </div>

                            <!-- Modal Footer -->
                            <div class="p-4 sm:p-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                                <button
                                    type="button"
                                    @click="closePengumuman"
                                    class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs sm:text-sm font-bold shadow-md transition-all active:scale-95"
                                >
                                    Tutup Pengumuman
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </Teleport>

        </div>
    </SiswaLayout>
</template>
