<script setup>
import PembimbingLayout from '@/Layouts/PembimbingLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const flash = computed(() => page.props.flash);
const dismissed = ref({ success: false, error: false });

const dismissFlash = (key) => {
    dismissed.value[key] = true;
};

const props = defineProps({
    informasis: Array,
    mutasis: Array
});

const activeTab = ref('mutasi');

function formatDate(dateStr) {
    if (!dateStr) return '-';
    const d = new Date(dateStr);
    return isNaN(d.getTime()) ? '-' : d.toLocaleDateString('id-ID', {
        year: 'numeric', month: 'short', day: 'numeric'
    });
}

function statusLabel(status) {
    return status === 'pending' ? 'Menunggu'
        : status === 'processing' ? 'Diproses'
        : status === 'approved' ? 'Disetujui'
        : 'Ditolak';
}

function statusClass(status) {
    return status === 'pending' ? 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'
        : status === 'processing' ? 'bg-blue-50 text-blue-700 ring-1 ring-blue-200'
        : status === 'approved' ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200'
        : 'bg-red-50 text-red-700 ring-1 ring-red-200';
}
</script>

<template>
    <Head title="Informasi" />

    <PembimbingLayout>
        <template #header>
            Informasi
        </template>

        <div class="space-y-6 animate-fade-in p-4 md:p-6 max-w-6xl mx-auto">
            <!-- Flash Messages -->
            <div v-if="flash.success && !dismissed.success"
                 class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl flex items-center justify-between">
                <span class="font-medium">{{ flash.success }}</span>
                <button @click="dismissFlash('success')" class="text-emerald-600 hover:text-emerald-800 shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div v-if="flash.error && !dismissed.error"
                 class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl flex items-center justify-between">
                <span class="font-medium">{{ flash.error }}</span>
                <button @click="dismissFlash('error')" class="text-red-600 hover:text-red-800 shrink-0">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Page Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-slate-800 tracking-tight">Informasi</h1>
                    <p class="text-slate-500 mt-1 text-sm">Semua informasi dan pengajuan mutasi untuk Anda.</p>
                </div>
            </div>

            <!-- Tabs -->
            <div class="border-b border-slate-200">
                <nav class="flex gap-6 -mb-px">
                    <button @click="activeTab = 'mutasi'"
                            class="pb-3 text-sm font-medium transition-colors relative"
                            :class="activeTab === 'mutasi' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            Pengajuan Mutasi
                            <span v-if="mutasis?.length"
                                  class="text-[11px] font-semibold px-1.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700">
                                {{ mutasis.length }}
                            </span>
                        </span>
                        <span v-if="activeTab === 'mutasi'"
                              class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600 rounded-full"></span>
                    </button>
                    <button @click="activeTab = 'pengumuman'"
                            class="pb-3 text-sm font-medium transition-colors relative"
                            :class="activeTab === 'pengumuman' ? 'text-indigo-600' : 'text-slate-400 hover:text-slate-600'">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Pengumuman
                            <span v-if="informasis?.length"
                                  class="text-[11px] font-semibold px-1.5 py-0.5 rounded-full bg-indigo-100 text-indigo-700">
                                {{ informasis.length }}
                            </span>
                        </span>
                        <span v-if="activeTab === 'pengumuman'"
                              class="absolute bottom-0 left-0 right-0 h-0.5 bg-indigo-600 rounded-full"></span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content: Mutasi -->
            <div v-if="activeTab === 'mutasi'">
                <template v-if="mutasis && mutasis.length > 0">
                    <!-- Desktop Table -->
                    <div class="hidden md:block bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wider">#</th>
                                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wider">Tanggal</th>
                                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wider">Siswa</th>
                                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wider">Bengkel Asal</th>
                                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wider">Bengkel Tujuan</th>
                                    <th class="text-center px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="(mutasi, idx) in mutasis" :key="mutasi.id"
                                    class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-5 py-4 text-slate-400 font-mono text-xs">{{ idx + 1 }}</td>
                                    <td class="px-5 py-4 text-slate-500 whitespace-nowrap">{{ formatDate(mutasi.created_at) }}</td>
                                    <td class="px-5 py-4 font-medium text-slate-800">{{ mutasi.siswa?.nama }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ mutasi.bengkel_asal?.nama_bengkel }}</td>
                                    <td class="px-5 py-4 font-medium text-indigo-600">{{ mutasi.bengkel_tujuan?.nama_bengkel }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-block text-[11px] font-semibold px-2.5 py-1 rounded-full"
                                              :class="statusClass(mutasi.status)">
                                            {{ statusLabel(mutasi.status) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile/Tablet Cards -->
                    <div class="md:hidden space-y-3">
                        <div v-for="(mutasi, idx) in mutasis" :key="mutasi.id"
                             class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-[11px] font-medium text-slate-400">#{{ idx + 1 }}</span>
                                <span class="inline-block text-[11px] font-semibold px-2 py-1 rounded-full"
                                      :class="statusClass(mutasi.status)">
                                    {{ statusLabel(mutasi.status) }}
                                </span>
                            </div>
                            <h3 class="font-bold text-slate-800 mb-2">{{ mutasi.siswa?.nama }}</h3>
                            <div class="space-y-1.5 text-sm text-slate-600">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-slate-400 w-16 shrink-0">Tanggal:</span>
                                    <span>{{ formatDate(mutasi.created_at) }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-slate-400 w-16 shrink-0">Asal:</span>
                                    <span>{{ mutasi.bengkel_asal?.nama_bengkel }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-medium text-slate-400 w-16 shrink-0">Tujuan:</span>
                                    <span class="font-medium text-indigo-600">{{ mutasi.bengkel_tujuan?.nama_bengkel }}</span>
                                </div>
                                <div v-if="mutasi.alasan" class="flex items-start gap-2">
                                    <span class="text-xs font-medium text-slate-400 w-16 shrink-0 mt-0.5">Alasan:</span>
                                    <span class="text-slate-500 leading-relaxed">{{ mutasi.alasan }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div v-else
                     class="rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center bg-slate-50/50">
                    <div class="mx-auto h-16 w-16 rounded-full bg-white shadow-sm flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Belum ada pengajuan mutasi</h3>
                    <p class="text-slate-500 mt-1 text-sm">Pengajuan mutasi akan muncul di sini.</p>
                </div>
            </div>

            <!-- Tab Content: Pengumuman -->
            <div v-if="activeTab === 'pengumuman'">
                <template v-if="informasis && informasis.length > 0">
                    <!-- Desktop Table -->
                    <div class="hidden md:block bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-100">
                                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wider">#</th>
                                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wider">Tanggal Terbit</th>
                                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wider">Judul</th>
                                    <th class="text-left px-5 py-3.5 font-semibold text-slate-600 text-xs uppercase tracking-wider">Deskripsi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                <tr v-for="(item, idx) in informasis" :key="item.id"
                                    class="hover:bg-slate-50/50 transition-colors">
                                    <td class="px-5 py-4 text-slate-400 font-mono text-xs align-top">{{ idx + 1 }}</td>
                                    <td class="px-5 py-4 text-slate-500 whitespace-nowrap align-top">{{ formatDate(item.tanggal_terbit) }}</td>
                                    <td class="px-5 py-4 font-medium text-slate-800 align-top max-w-[200px]">
                                        <p class="line-clamp-2">{{ item.judul }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600 align-top max-w-sm">
                                        <p class="line-clamp-3 leading-relaxed whitespace-pre-line">{{ item.deskripsi }}</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile/Tablet Cards -->
                    <div class="md:hidden space-y-3">
                        <div v-for="(item, idx) in informasis" :key="item.id"
                             class="bg-white rounded-xl border border-slate-100 shadow-sm p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-[11px] font-medium text-slate-400">#{{ idx + 1 }}</span>
                                <span class="text-[11px] text-slate-400">{{ formatDate(item.tanggal_terbit) }}</span>
                            </div>
                            <h3 class="font-bold text-slate-800 mb-1.5">{{ item.judul }}</h3>
                            <p class="text-sm text-slate-600 leading-relaxed whitespace-pre-line line-clamp-4">{{ item.deskripsi }}</p>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div v-else
                     class="rounded-2xl border-2 border-dashed border-slate-200 p-12 text-center bg-slate-50/50">
                    <div class="mx-auto h-16 w-16 rounded-full bg-white shadow-sm flex items-center justify-center mb-4">
                        <svg class="h-8 w-8 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900">Belum ada pengumuman</h3>
                    <p class="text-slate-500 mt-1 text-sm">Pengumuman dari admin akan muncul di sini.</p>
                </div>
            </div>
        </div>
    </PembimbingLayout>
</template>
