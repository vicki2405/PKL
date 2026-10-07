<script setup>
import PembimbingLayout from '@/Layouts/PembimbingLayout.vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const flash = computed(() => page.props.flash);
const dismissed = ref({ message: false, success: false, error: false });

const dismissFlash = (key) => {
    dismissed.value[key] = true;
};

const props = defineProps({
    monitorings: Object,
    progress: Array
});

const expandedMonitorings = ref({});
const expandedBengkels = ref({});
const selectedMonths = ref({});

const showDeleteModal = ref(false);
const monitoringToDelete = ref(null);
const isDeleting = ref(false);

const selectedPhoto = ref(null);

const openPhotoModal = (url) => {
    selectedPhoto.value = url;
};

const closePhotoModal = () => {
    selectedPhoto.value = null;
};

const confirmDelete = (monitoring) => {
    monitoringToDelete.value = monitoring;
    showDeleteModal.value = true;
};

const executeDelete = () => {
    if (!monitoringToDelete.value) return;
    isDeleting.value = true;
    router.delete(route('pembimbing.monitoring.destroy', monitoringToDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            isDeleting.value = false;
            showDeleteModal.value = false;
            monitoringToDelete.value = null;
        }
    });
};

const visibleProgress = computed(() => {
    if (!props.progress) return [];

    const bengkelsWithData = Object.keys(groupedMonitorings.value);

    return [...props.progress].sort((a, b) => {
        const aHasData = bengkelsWithData.includes(String(a.bengkel_id));
        const bHasData = bengkelsWithData.includes(String(b.bengkel_id));
        if (aHasData && !bHasData) return -1;
        if (!aHasData && bHasData) return 1;
        return 0;
    });
});

const groupedMonitorings = computed(() => {
    const list = Array.isArray(props.monitorings) ? props.monitorings : (props.monitorings?.data || []);
    const groups = {};
    list.forEach(m => {
        const key = m.bengkel_id;
        if (!groups[key]) {
            groups[key] = [];
        }
        groups[key].push(m);
    });
    return groups;
});

const toggleBengkel = (bengkelId) => {
    const isCurrentlyOpen = !!expandedBengkels.value[bengkelId];
    
    // Tutup card bengkel lainnya agar hanya 1 card yang terbuka ke bawah
    expandedBengkels.value = {
        [bengkelId]: !isCurrentlyOpen
    };

    if (!isCurrentlyOpen && !selectedMonths.value[bengkelId]) {
        const history = groupedMonitorings.value[bengkelId];
        if (history && history.length > 0) {
            let maxPeriod = 0;
            history.forEach(m => {
                const p = getMonitoringPeriod(m);
                if (p && p > maxPeriod) maxPeriod = p;
            });
            if (maxPeriod > 0) {
                selectedMonths.value[bengkelId] = maxPeriod;
            }
        }
    }
};

const selectMonth = (bengkelId, month) => {
    if (selectedMonths.value[bengkelId] === month) {
        selectedMonths.value[bengkelId] = null;
    } else {
        selectedMonths.value[bengkelId] = month;
        // Buka hanya bengkel ini dan tutup card bengkel lainnya
        expandedBengkels.value = {
            [bengkelId]: true
        };
    }
};

const getFilteredMonitorings = (bengkelId) => {
    const all = groupedMonitorings.value[bengkelId] || [];
    const selected = selectedMonths.value[bengkelId];

    if (!selected) {
        return all;
    }

    return all.filter(m => getMonitoringPeriod(m) === selected);
};

const toggleMonitoringDetails = (id) => {
    expandedMonitorings.value[id] = !expandedMonitorings.value[id];
};

const getMonitoringPeriod = (monitoring) => {
    if (monitoring.bulan_ke) return monitoring.bulan_ke;
    if (!props.progress) return null;

    const progressData = props.progress.find(p => p.bengkel_id === monitoring.bengkel_id);
    if (!progressData || !progressData.start_date) return null;

    const visitDate = new Date(monitoring.tanggal_kunjungan);
    const start = new Date(progressData.start_date);

    for (let i = 0; i < 6; i++) {
        const periodStart = new Date(start.getFullYear(), start.getMonth() + i, start.getDate());
        const periodEnd = new Date(start.getFullYear(), start.getMonth() + i + 1, start.getDate() - 1, 23, 59, 59);

        visitDate.setHours(12, 0, 0, 0);

        if (visitDate >= periodStart && visitDate <= periodEnd) {
            return i + 1;
        }
    }

    return null;
};
</script>

<template>
    <Head title="Monitoring PKL" />

    <PembimbingLayout>
        <template #header>
            Monitoring PKL
        </template>

        <div class="space-y-6 animate-fade-in p-4 md:p-6 pb-24 lg:pb-6 relative">
            <!-- Flash Messages -->
            <div v-if="flash.message && !dismissed.message" class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between">
                <span class="font-medium text-sm">{{ flash.message }}</span>
                <button @click="dismissFlash('message')" class="text-emerald-600 hover:text-emerald-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div v-if="flash.success && !dismissed.success" class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between">
                <span class="font-medium text-sm">{{ flash.success }}</span>
                <button @click="dismissFlash('success')" class="text-emerald-600 hover:text-emerald-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div v-if="flash.error && !dismissed.error" class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center justify-between">
                <span class="font-medium text-sm">{{ flash.error }}</span>
                <button @click="dismissFlash('error')" class="text-red-600 hover:text-red-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-800 tracking-tight">Riwayat Monitoring</h1>
                    <p class="text-xs md:text-sm text-slate-500 mt-0.5">Daftar kunjungan monitoring ke bengkel mitra.</p>
                </div>
                <Link :href="route('pembimbing.monitoring.create')"
                      class="hidden sm:inline-flex items-center justify-center px-5 py-2.5 border border-transparent text-sm font-bold rounded-xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-lg shadow-indigo-500/20 active:scale-95 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Laporan Kunjungan
                </Link>
            </div>

            <!-- Combined Progress & Monitoring List -->
            <div v-if="progress && progress.length > 0">
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3 mb-8 items-start">
                    <div v-for="item in visibleProgress" :key="item.bengkel_id"
                         class="bg-white rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden transition-all duration-300 hover:shadow-md flex flex-col">

                        <!-- Progress Content -->
                        <div class="p-5 relative z-10">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-bold text-slate-700 text-base sm:text-lg leading-tight mb-1 line-clamp-1" :title="item.nama_bengkel">{{ item.nama_bengkel }}</h3>
                                    <p class="text-xs text-slate-500 font-medium">Target: {{ item.target }} Kunjungan</p>
                                </div>
                                <div class="relative h-14 w-14 sm:h-16 sm:w-16 flex items-center justify-center shrink-0">
                                    <svg class="h-full w-full transform -rotate-90" viewBox="0 0 36 36">
                                        <path class="text-slate-100" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                        <path :class="item.percentage >= 100 ? 'text-emerald-500' : 'text-indigo-600'"
                                            stroke-dasharray="100, 100"
                                            :stroke-dashoffset="100 - item.percentage"
                                            stroke-width="3" stroke-linecap="round" stroke="currentColor" fill="none"
                                            d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                                    </svg>
                                    <div class="absolute inset-0 flex items-center justify-center flex-col">
                                        <span class="text-xs font-bold" :class="item.percentage >= 100 ? 'text-emerald-600' : 'text-indigo-600'">{{ item.percentage }}%</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 mb-4">
                                <span class="text-xs font-bold px-2.5 py-1 rounded-lg whitespace-nowrap"
                                    :class="item.count >= item.target ? 'bg-emerald-100 text-emerald-700' : 'bg-indigo-50 text-indigo-700'">
                                    {{ item.count }} / {{ item.target }} Selesai
                                </span>
                            </div>

                            <!-- Monthly Breakdown (Touch Scrollable) -->
                            <div v-if="item.monthly_progress && item.monthly_progress.length > 0">
                                <div class="flex gap-1.5 overflow-x-auto pb-1 -mx-0.5 px-0.5 scrollbar-hide">
                                    <div v-for="month in item.monthly_progress" :key="month.month"
                                        @click="selectMonth(item.bengkel_id, month.month)"
                                        class="flex-1 min-w-[54px] p-1.5 sm:p-2 rounded-xl text-center border transition-all duration-200 flex flex-col items-center justify-center gap-1 group relative cursor-pointer active:scale-95"
                                        :class="[
                                            month.is_completed ? 'bg-emerald-50 border-emerald-100' : 'bg-slate-50 border-slate-100',
                                            selectedMonths[item.bengkel_id] === month.month ? 'ring-2 ring-indigo-500 ring-offset-2' : 'hover:border-indigo-200'
                                        ]">

                                        <div class="text-[9px] sm:text-[10px] uppercase font-bold tracking-wider leading-none"
                                             :class="selectedMonths[item.bengkel_id] === month.month ? 'text-indigo-700' : 'text-slate-500'">
                                            Bulan {{ month.month }}
                                        </div>
                                        <div v-if="month.month_name" class="text-[8px] sm:text-[9px] font-semibold text-slate-400 capitalize leading-tight">
                                            {{ month.month_name.split(' ')[0] }}
                                        </div>
                                        <div class="font-black text-xs sm:text-sm" :class="month.is_completed ? 'text-emerald-600' : 'text-slate-600'">
                                            {{ month.count }}/{{ month.target }}
                                        </div>
                                        <!-- Monitoring 1 & Monitoring 2 Dots (2 Minggu Sekali) -->
                                        <div class="flex items-center gap-1 my-0.5">
                                            <span class="h-1.5 w-1.5 rounded-full"
                                                :class="(month.has_session_1 || month.count >= 1) ? 'bg-emerald-500' : 'bg-slate-300'"
                                                title="Monitoring 1: Minggu 1-2"></span>
                                            <span class="h-1.5 w-1.5 rounded-full"
                                                :class="(month.has_session_2 || month.count >= 2) ? 'bg-emerald-500' : 'bg-slate-300'"
                                                title="Monitoring 2: Minggu 3-4"></span>
                                        </div>
                                        <div class="h-1 w-full bg-slate-200 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all"
                                                :class="month.is_completed ? 'bg-emerald-500' : 'bg-indigo-500'"
                                                :style="{ width: Math.min(100, (month.count / month.target) * 100) + '%' }"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- History Accordion -->
                        <div v-if="groupedMonitorings[item.bengkel_id] && groupedMonitorings[item.bengkel_id].length > 0" class="mt-auto border-t border-slate-100">
                            <div @click="toggleBengkel(item.bengkel_id)"
                                 class="w-full px-5 py-3 bg-indigo-600 hover:bg-indigo-700 transition-colors flex items-center justify-between cursor-pointer group select-none">
                                <span class="text-xs font-bold text-white flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span v-if="selectedMonths[item.bengkel_id]">
                                        Riwayat Bulan {{ selectedMonths[item.bengkel_id] }} ({{ getFilteredMonitorings(item.bengkel_id).length }})
                                    </span>
                                    <span v-else>
                                        Semua Riwayat ({{ groupedMonitorings[item.bengkel_id].length }})
                                    </span>
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-indigo-100 group-hover:text-white transition-transform duration-300"
                                     :class="{ 'rotate-180': expandedBengkels[item.bengkel_id] }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            <!-- History List -->
                            <div v-show="expandedBengkels[item.bengkel_id]" class="bg-slate-50 p-4 space-y-3 max-h-[500px] overflow-y-auto custom-scrollbar border-t border-slate-200">
                                <div v-for="monitoring in getFilteredMonitorings(item.bengkel_id)" :key="monitoring.id"
                                     class="bg-white rounded-xl shadow-sm border border-slate-200/80 p-4 relative group/card">

                                    <!-- Date Badge & Actions -->
                                    <div class="flex justify-between items-start mb-3">
                                        <div class="flex items-center gap-3">
                                            <div class="h-10 w-10 rounded-xl bg-indigo-50 text-indigo-600 flex flex-col items-center justify-center border border-indigo-100 shrink-0">
                                                <span class="text-[10px] font-bold uppercase leading-none">{{ new Date(monitoring.tanggal_kunjungan).toLocaleString('id-ID', { month: 'short' }) }}</span>
                                                <span class="text-sm font-black leading-none mt-0.5">{{ new Date(monitoring.tanggal_kunjungan).getDate() }}</span>
                                            </div>
                                            <div>
                                                 <div class="flex items-center gap-1 flex-wrap">
                                                     <span v-if="monitoring.bulan_ke" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-indigo-100 text-indigo-700 border border-indigo-200">
                                                         Bulan {{ monitoring.bulan_ke }}
                                                     </span>
                                                     <span v-if="monitoring.monitoring_ke" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200">
                                                         Ke-{{ monitoring.monitoring_ke }}
                                                     </span>
                                                     <span v-if="monitoring.laporan_diterima" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-100 text-emerald-700 border border-emerald-200">
                                                         Diterima
                                                     </span>
                                                     <span v-else class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-amber-100 text-amber-700 border border-amber-200">
                                                         Menunggu
                                                     </span>
                                                 </div>
                                                <p class="text-[10px] text-slate-400 mt-1">
                                                    {{ new Date(monitoring.created_at || monitoring.tanggal_kunjungan).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }} WIB
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Actions -->
                                        <div class="flex items-center gap-1">
                                            <Link :href="route('pembimbing.monitoring.edit', monitoring.id)" class="p-2 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-all active:scale-95" title="Edit Laporan">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                </svg>
                                            </Link>
                                            <button type="button" @click="confirmDelete(monitoring)" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-all active:scale-95" title="Hapus Laporan">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Content -->
                                    <div class="space-y-3">
                                        <p class="text-xs text-slate-600 line-clamp-3 italic bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                            "{{ monitoring.catatan_monitoring }}"
                                        </p>

                                        <!-- Foto Dokumentasi Thumbnail -->
                                        <div v-if="monitoring.foto_kegiatan" class="pt-1">
                                            <button type="button" @click="openPhotoModal(`/storage/${monitoring.foto_kegiatan}`)" class="inline-flex items-center gap-2 text-xs font-semibold text-indigo-600 hover:text-indigo-800 bg-indigo-50/80 hover:bg-indigo-100 px-3 py-1.5 rounded-lg border border-indigo-100 transition-colors">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                <span>Lihat Foto Dokumentasi</span>
                                            </button>
                                        </div>

                                        <!-- Attendance Summary -->
                                        <div v-if="monitoring.details && monitoring.details.length > 0" class="bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                            <div class="flex items-center gap-1 mb-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                                Status Siswa ({{ monitoring.details.length }})
                                            </div>
                                            <div class="space-y-2">
                                                <div v-for="detail in (expandedMonitorings[monitoring.id] ? monitoring.details : monitoring.details.slice(0, 2))" :key="detail.id" class="p-2 rounded-lg bg-white border border-slate-200/70 text-xs">
                                                    <div class="flex items-center justify-between gap-2">
                                                        <span class="font-bold text-slate-700 truncate">{{ detail.siswa?.nama }}</span>
                                                        <span v-if="detail.siswa?.kelas" class="shrink-0 px-1.5 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600 border border-slate-200 uppercase">
                                                            {{ detail.siswa.kelas.nama_kelas }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                            H: {{ detail.hadir ?? 0 }} hr
                                                        </span>
                                                        <span v-if="detail.izin > 0" class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                            I: {{ detail.izin }} hr
                                                        </span>
                                                        <span v-if="detail.sakit > 0" class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200">
                                                            S: {{ detail.sakit }} hr
                                                        </span>
                                                        <span v-if="detail.alpha > 0" class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                            A: {{ detail.alpha }} hr
                                                        </span>
                                                        <span v-if="detail.is_pelanggaran" class="px-2 py-0.5 rounded text-[10px] font-bold bg-red-100 text-red-700 border border-red-300">
                                                            ⚠️ Pelanggaran
                                                        </span>
                                                    </div>
                                                    <div v-if="detail.is_pelanggaran && detail.catatan" class="mt-1.5 text-[11px] text-red-600 bg-red-50/60 p-1.5 rounded border border-red-100">
                                                        <span class="font-bold">Catatan:</span> {{ detail.catatan }}
                                                    </div>
                                                </div>
                                                <button v-if="monitoring.details.length > 2"
                                                        @click.stop="toggleMonitoringDetails(monitoring.id)"
                                                        class="text-[10px] text-indigo-600 font-bold pl-1 hover:underline focus:outline-none text-left w-full">
                                                    {{ expandedMonitorings[monitoring.id] ? 'Sembunyikan' : `+ ${monitoring.details.length - 2} siswa lainnya` }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else class="rounded-3xl border-2 border-dashed border-slate-200 p-8 sm:p-12 text-center bg-white">
                <div class="mx-auto h-16 w-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4">
                    <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Belum ada laporan monitoring</h3>
                <p class="text-slate-500 text-xs sm:text-sm mt-1 max-w-xs mx-auto">Tercatat 0 kunjungan. Mulai catat monitoring tempat magang di sini.</p>
                <Link :href="route('pembimbing.monitoring.create')" class="mt-6 inline-flex items-center justify-center px-5 py-2.5 text-xs font-bold rounded-xl text-white bg-indigo-600 hover:bg-indigo-700 transition-colors shadow-md">
                    Tambah Monitoring Sekarang
                </Link>
            </div>

            <!-- Floating Action Button (FAB) Mobile -->
            <div class="fixed sm:hidden bottom-20 right-4 z-40">
                <Link :href="route('pembimbing.monitoring.create')"
                      class="flex items-center justify-center h-14 w-14 rounded-full bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-xl shadow-indigo-600/40 active:scale-95 transition-transform border-2 border-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                </Link>
            </div>

            <!-- Modal Konfirmasi Hapus -->
            <div v-if="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-fade-in-up">
                    <div class="h-12 w-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-800">Hapus Laporan Monitoring?</h3>
                    <p class="text-sm text-slate-500 mt-2">
                        Tindakan ini tidak dapat dibatalkan. Laporan kunjungan tanggal <span class="font-bold text-slate-700">{{ monitoringToDelete?.tanggal_kunjungan ? String(monitoringToDelete.tanggal_kunjungan).substring(0, 10) : '' }}</span> beserta seluruh evaluasi siswa akan dihapus permanen.
                    </p>
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button type="button" @click="showDeleteModal = false" class="px-4 py-2 text-sm font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition-colors">
                            Batal
                        </button>
                        <button type="button" :disabled="isDeleting" @click="executeDelete" class="px-5 py-2.5 text-sm font-bold rounded-xl text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-600/20 active:scale-95 transition-all disabled:opacity-70 flex items-center gap-2">
                            <svg v-if="isDeleting" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ isDeleting ? 'Menghapus...' : 'Ya, Hapus' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Preview Foto Kegiatan -->
            <div v-if="selectedPhoto" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm" @click="closePhotoModal">
                <div class="relative max-w-2xl w-full bg-white rounded-3xl overflow-hidden shadow-2xl p-4" @click.stop>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                        <h4 class="font-bold text-slate-800 text-sm">Foto Dokumentasi Monitoring</h4>
                        <button type="button" @click="closePhotoModal" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <img :src="selectedPhoto" alt="Foto Dokumentasi" class="w-full max-h-[75vh] object-contain rounded-2xl bg-slate-50">
                </div>
            </div>

        </div>
    </PembimbingLayout>
</template>
