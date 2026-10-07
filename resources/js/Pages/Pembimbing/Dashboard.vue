<script setup>
import PembimbingLayout from '@/Layouts/PembimbingLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            total_siswa: 0,
            total_bengkel: 0,
            bengkel_data: []
        })
    },
    pembimbing: {
        type: Object,
        default: null
    }
});

const expandedBengkel = ref(null);

const toggleBengkel = (id) => {
    if (expandedBengkel.value === id) {
        expandedBengkel.value = null;
    } else {
        expandedBengkel.value = id;
    }
};

const getWaLink = (number) => {
    if (!number) return '#';
    let cleaned = ('' + number).replace(/\D/g, '');
    if (cleaned.startsWith('0')) {
        cleaned = '62' + cleaned.substr(1);
    }
    return `https://wa.me/${cleaned}`;
};
</script>

<template>
    <Head title="Dashboard Pembimbing" />

    <PembimbingLayout>
        <template #header>
            Dashboard
        </template>

        <div class="space-y-6 md:space-y-8 animate-fade-in">
            <!-- Welcome Section -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-indigo-600 to-purple-600 p-6 md:p-8 shadow-xl text-white">
                <div class="relative z-10">
                    <h1 class="text-2xl md:text-3xl font-extrabold tracking-tight sm:text-4xl mb-2">
                        Selamat Datang, {{ pembimbing?.nama || 'Pembimbing' }}! 👋
                    </h1>
                    <p class="text-indigo-100 text-sm md:text-lg max-w-2xl">
                        Pantau perkembangan siswa bimbingan dan lokasi bengkel Anda dalam satu tampilan yang mudah.
                    </p>
                </div>
                <!-- Decorative Circles -->
                <div class="absolute top-0 right-0 -mt-10 -mr-10 h-64 w-64 rounded-full bg-white opacity-10 blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 h-64 w-64 rounded-full bg-indigo-400 opacity-20 blur-3xl"></div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-4 md:gap-6 sm:grid-cols-2">
                <!-- Total Siswa Card -->
                <div class="group relative overflow-hidden rounded-3xl bg-white p-1 shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-400 to-indigo-500 opacity-0 transition-opacity duration-300 group-hover:opacity-10"></div>
                    <div class="relative h-full rounded-[20px] bg-white p-5 md:p-7">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs md:text-sm font-bold uppercase tracking-wider text-indigo-500">Total Siswa</p>
                                <div class="mt-2 md:mt-4 flex items-baseline gap-2">
                                    <span class="text-4xl md:text-5xl font-black text-slate-800 tracking-tighter">{{ stats.total_siswa }}</span>
                                    <span class="text-sm md:text-base font-semibold text-slate-400">Siswa Bimbingan</span>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-indigo-50 p-3 md:p-4 text-indigo-600 transition-all duration-300 group-hover:bg-indigo-600 group-hover:text-white group-hover:rotate-6 group-hover:scale-110 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 md:h-10 md:w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Lokasi Card -->
                <div class="group relative overflow-hidden rounded-3xl bg-white p-1 shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl">
                    <div class="absolute inset-0 bg-gradient-to-r from-emerald-400 to-teal-500 opacity-0 transition-opacity duration-300 group-hover:opacity-10"></div>
                    <div class="relative h-full rounded-[20px] bg-white p-5 md:p-7">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs md:text-sm font-bold uppercase tracking-wider text-emerald-600">Total Lokasi</p>
                                <div class="mt-2 md:mt-4 flex items-baseline gap-2">
                                    <span class="text-4xl md:text-5xl font-black text-slate-800 tracking-tighter">{{ stats.total_bengkel }}</span>
                                    <span class="text-sm md:text-base font-semibold text-slate-400">Bengkel Aktif</span>
                                </div>
                            </div>
                            <div class="rounded-2xl bg-emerald-50 p-3 md:p-4 text-emerald-600 transition-all duration-300 group-hover:bg-emerald-600 group-hover:text-white group-hover:-rotate-6 group-hover:scale-110 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bengkel & Siswa List Section -->
            <div class="space-y-6">
                <div class="flex items-center gap-3 px-1">
                    <div class="h-8 w-1.5 rounded-full bg-gradient-to-b from-indigo-500 to-purple-600"></div>
                    <h2 class="text-2xl font-bold text-slate-800">Data Bengkel & Siswa</h2>
                </div>

                <div v-if="stats.bengkel_data && stats.bengkel_data.length > 0" class="grid gap-6">
                    <div v-for="(bengkel, index) in stats.bengkel_data" :key="bengkel.id"
                        class="group bg-white rounded-2xl shadow-md border border-slate-100 overflow-hidden transition-all duration-500 hover:shadow-xl hover:border-indigo-100"
                        :style="{ animationDelay: `${index * 100}ms` }">

                        <!-- Card Header -->
                        <div class="p-5 md:p-6 bg-gradient-to-br from-white to-slate-50">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 md:gap-6">
                                <div class="flex-1 space-y-2 md:space-y-3">
                                    <div class="flex items-start md:items-center gap-3 md:gap-4">
                                        <div class="h-10 w-10 md:h-12 md:w-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-inner shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 md:h-6 md:w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h3 class="text-lg md:text-xl font-bold text-slate-900 group-hover:text-indigo-700 transition-colors">{{ bengkel.nama_bengkel }}</h3>
                                            <div class="flex items-center gap-2 text-slate-500 text-xs md:text-sm mt-1">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 md:h-4 md:w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                </svg>
                                                <span class="line-clamp-1">{{ bengkel.alamat || 'Alamat tidak tersedia' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center flex-wrap gap-2 md:gap-3 mt-2 md:mt-0">
                                    <span class="inline-flex items-center px-3 py-1.5 md:px-4 md:py-2 rounded-xl text-xs md:text-sm font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                                        {{ bengkel.siswa_count }} Siswa
                                    </span>

                                    <a v-if="bengkel.google_maps_link" :href="bengkel.google_maps_link" target="_blank" @click.stop
                                       class="p-2 md:p-2.5 rounded-xl text-slate-500 bg-white border border-slate-200 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition-all shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 md:h-5 md:w-5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                                        </svg>
                                    </a>

                                    <button class="flex-1 md:flex-none inline-flex items-center justify-center px-4 py-2 md:px-5 md:py-2.5 border border-transparent text-xs md:text-sm font-bold rounded-xl text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 shadow-md hover:shadow-lg transform transition-all active:scale-95"
                                            @click="toggleBengkel(bengkel.id)">
                                        {{ expandedBengkel === bengkel.id ? 'Tutup' : 'Lihat Siswa' }}
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 md:h-4 md:w-4 ml-2 transition-transform duration-300"
                                            :class="{'rotate-180': expandedBengkel === bengkel.id}"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Expanded Content -->
                        <div v-show="expandedBengkel === bengkel.id" class="border-t border-slate-100 bg-slate-50/50">
                            <div class="p-6 animate-fade-in-down">
                                <h4 class="font-bold text-slate-700 mb-5 flex items-center gap-2 text-lg">
                                    <span class="w-8 h-8 rounded-lg bg-indigo-100 flex items-center justify-center text-indigo-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </span>
                                    Daftar Siswa PKL
                                </h4>

                                <!-- Desktop Table -->
                                <div class="hidden md:block overflow-hidden rounded-xl border border-slate-200 shadow-sm bg-white">
                                    <table class="min-w-full divide-y divide-slate-200">
                                        <thead class="bg-slate-50">
                                            <tr>
                                                <th scope="col" class="py-4 pl-6 pr-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">No</th>
                                                <th scope="col" class="px-3 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Nama Siswa</th>
                                                <th scope="col" class="px-3 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kelas / Jurusan</th>
                                                <th scope="col" class="px-3 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Kontak</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100 bg-white">
                                            <tr v-for="(siswa, index) in bengkel.siswas" :key="siswa.id" class="hover:bg-indigo-50/30 transition-colors">
                                                <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-medium text-slate-500">{{ index + 1 }}</td>
                                                <td class="whitespace-nowrap px-3 py-4">
                                                    <div class="flex items-center">
                                                        <div class="h-9 w-9 rounded-full bg-gradient-to-tr from-indigo-400 to-purple-500 flex items-center justify-center text-white font-bold text-sm shadow-sm mr-3">
                                                            {{ siswa.nama?.charAt(0) || 'S' }}
                                                        </div>
                                                        <div class="font-bold text-slate-800">{{ siswa.nama }}</div>
                                                    </div>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">
                                                    <div class="font-medium">{{ siswa.kelas }}</div>
                                                    <div v-if="siswa.wali_kelas && siswa.wali_kelas !== '-'" class="text-[11px] text-slate-400 font-semibold mt-0.5">Wali: {{ siswa.wali_kelas }}</div>
                                                    <div class="text-xs text-indigo-500 font-semibold mt-0.5">{{ siswa.jurusan }}</div>
                                                </td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                                    <a v-if="siswa.no_hp && siswa.no_hp !== '-'" :href="getWaLink(siswa.no_hp)" target="_blank"
                                                       class="inline-flex items-center px-3 py-1.5 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 transition-all font-medium text-xs">
                                                        <svg class="h-4 w-4 mr-1.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792 .372-.272 .297-1.04 1.017-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074 .149 .198 2.095 3.2 5.076 4.487 .709 .306 1.263 .489 1.694 .626 .712 .226 1.36 .194 1.872 .118 .571 -.085 1.758 -.719 2.006 -1.413 .248 -.695 .248 -1.29 .173 -1.414 z"/></svg>
                                                        WhatsApp
                                                    </a>
                                                    <span v-else class="text-slate-300">-</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Mobile List -->
                                <div class="md:hidden space-y-4">
                                    <div v-for="(siswa, index) in bengkel.siswas" :key="siswa.id"
                                         class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 hover:border-indigo-200 transition-colors">
                                        <div class="flex items-center gap-4 mb-3">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-sm shadow-sm">
                                                {{ index + 1 }}
                                            </div>
                                            <div>
                                                <div class="font-bold text-slate-800 text-lg">{{ siswa.nama }}</div>
                                                <div class="text-xs text-indigo-500 font-medium">{{ siswa.jurusan }}</div>
                                            </div>
                                        </div>
                                        <div class="space-y-3 pl-14">
                                            <div class="flex justify-between items-center py-2 border-b border-slate-50">
                                                <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Kelas</span>
                                                <span class="font-semibold text-slate-700">{{ siswa.kelas }}</span>
                                            </div>
                                            <div v-if="siswa.wali_kelas && siswa.wali_kelas !== '-'" class="flex justify-between items-center py-2 border-b border-slate-50">
                                                <span class="text-xs text-slate-400 font-medium uppercase tracking-wider">Wali Kelas</span>
                                                <span class="font-semibold text-slate-700">{{ siswa.wali_kelas }}</span>
                                            </div>
                                            <div class="pt-1">
                                                <a v-if="siswa.no_hp && siswa.no_hp !== '-'" :href="getWaLink(siswa.no_hp)" target="_blank"
                                                   class="flex items-center justify-center w-full px-4 py-2.5 rounded-xl bg-green-50 text-green-700 text-sm font-bold hover:bg-green-100 transition-colors">
                                                    <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669 .149-.198 .297-.768 .967-.941 1.165-.173 .198-.347 .223-.644 .074-.297 -.149-1.255 -.462-2.39 -1.475 -.883 -.788 -1.48 -1.761 -1.653 -2.059 -.173 -.297 -.018 -.458 .13 -.606 .134 -.133 .297 -.347 .446 -.521 .151 -.172 .2 -.296 .3 -.495 .099 -.198 .05 -.372 -.025 -.521 -.075 -.148 -.669 -1.611 -.916 -2.206 -.242 -.579 -.487 -.501 -.669 -.51 l-.57 -.01 c-.198 0 -.52 .074 -.792 .372 -.272 .297 -1.04 1.017 -1.04 2.479 0 1.462 1.065 2.875 1.213 3.074 .149 .198 2.095 3.2 5.076 4.487 .709 .306 1.263 .489 1.694 .626 .712 .226 1.36 .194 1.872 .118 .571 -.085 1.758 -.719 2.006 -1.413 .248 -.695 .248 -1.29 .173 -1.414 z"/></svg>
                                                    Chat WhatsApp
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="bengkel.siswas.length === 0" class="text-center py-12">
                                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-slate-900">Belum ada siswa</h3>
                                    <p class="text-slate-500 mt-1">Belum ada data siswa yang terdaftar di bengkel ini.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State for Bengkel -->
                <div v-else class="rounded-3xl border-2 border-dashed border-slate-200 p-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-slate-900">Tidak ada data bengkel</h3>
                    <p class="mt-1 text-sm text-slate-500">Anda belum memiliki bengkel bimbingan.</p>
                </div>
            </div>
        </div>
    </PembimbingLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.8s ease-out;
}

.animate-fade-in-down {
    animation: fadeInDown 0.5s ease-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeInDown {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
