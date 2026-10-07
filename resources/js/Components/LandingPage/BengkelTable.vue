<script setup>
import { computed, ref } from "vue";

const props = defineProps({
  bengkels: {
    type: Array,
    default: () => [],
  },
});

const search = ref("");
const selectedCode = ref("Semua");
const perPage = ref(10);
const currentPage = ref(1);

// Dinamis mengambil kode unik dari data yang masuk
const uniqueCodes = computed(() => {
  const codes = props.bengkels
    .map(b => b.kode_bengkel)
    .filter(c => c && c !== '-' && c.trim() !== '');
  return ['Semua', ...new Set(codes)];
});

const filteredRows = computed(() => {
  const query = search.value.trim().toLowerCase();
  let result = props.bengkels;

  // Filter berdasarkan Kode Bengkel
  if (selectedCode.value && selectedCode.value !== 'Semua') {
    result = result.filter(item => item.kode_bengkel === selectedCode.value);
  }

  // Filter berdasarkan pencarian kata kunci (nama, kecamatan, atau kode)
  if (query) {
    result = result.filter((item) =>
      String(item?.nama_bengkel ?? "").toLowerCase().includes(query) ||
      String(item?.kecamatan ?? "").toLowerCase().includes(query) ||
      String(item?.kode_bengkel ?? "").toLowerCase().includes(query)
    );
  }

  return result;
});

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredRows.value.length / perPage.value))
);

const paginatedRows = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  return filteredRows.value.slice(start, start + perPage.value);
});

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const resetPage = () => {
  currentPage.value = 1;
};

const getRowNumber = (index) => {
  return (currentPage.value - 1) * perPage.value + index + 1;
};

const paginationRange = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const range = [];
  const maxShow = 5;
  let start = Math.max(1, current - Math.floor(maxShow / 2));
  let end = Math.min(total, start + maxShow - 1);
  if (end - start + 1 < maxShow) {
    start = Math.max(1, end - maxShow + 1);
  }
  for (let i = start; i <= end; i++) {
    range.push(i);
  }
  return range;
});
</script>

<template>
  <div class="max-w-screen-xl px-6 sm:px-8 lg:px-16 mx-auto flex flex-col w-full my-16 scroll-mt-24" id="bengkel-table">
    <div class="flex flex-col w-full text-center mb-8">
      <h3 class="text-2xl sm:text-3xl lg:text-4xl font-medium text-black-600 leading-relaxed">
        Data Bengkel
      </h3>
      <p class="leading-normal w-10/12 sm:w-7/12 lg:w-6/12 mx-auto my-2 text-center text-gray-500">
        Informasi sebaran lokasi bengkel, program keahlian, dan kuota peserta aktif saat ini.
      </p>
    </div>

    <!-- Filter Bar -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6">
      <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
        <!-- Search Input -->
        <div class="w-full sm:w-80 relative">
          <div class="pointer-events-none absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1010.5 18.5a7.5 7.5 0 006.15-1.85z" />
            </svg>
          </div>
          <input
            v-model="search"
            type="text"
            placeholder="Cari nama bengkel atau kecamatan..."
            @input="resetPage"
            class="w-full rounded-xl border-gray-200 pl-11 pr-10 py-3 text-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-all duration-200"
          />
          <button
            v-if="search"
            type="button"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
            @click="search = ''; resetPage()"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Filter Kode Bengkel Dropdown -->
        <div class="w-full sm:w-48">
          <select
            v-model="selectedCode"
            @change="resetPage"
            class="w-full rounded-xl border-gray-200 py-3 px-4 text-sm focus:border-orange-500 focus:ring focus:ring-orange-200 focus:ring-opacity-50 transition-all duration-200"
          >
            <option value="Semua">Semua Kode</option>
            <option v-for="code in uniqueCodes.slice(1)" :key="code" :value="code">
              {{ code }}
            </option>
          </select>
        </div>
      </div>

      <div class="text-sm text-gray-500 w-full sm:w-auto text-center sm:text-right">
        Ditemukan <span class="font-medium text-gray-700">{{ filteredRows.length }}</span> bengkel
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-100">

      <!-- Desktop/Tablet Table View -->
      <div class="hidden md:block overflow-x-auto">
        <table class="w-full text-sm text-left">
          <thead class="bg-orange-500 text-white font-semibold uppercase text-xs tracking-wider border-b border-orange-600">
            <tr>
              <th scope="col" class="px-6 py-4 text-center w-16 text-white">No</th>
              <th scope="col" class="px-6 py-4 text-white w-40">Kode Bengkel</th>
              <th scope="col" class="px-6 py-4 text-white">Nama Bengkel</th>
              <th scope="col" class="px-6 py-4 text-white w-48">
                <div class="flex items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Kecamatan
                </div>
              </th>
              <th scope="col" class="px-6 py-4 text-center text-white w-40">Lokasi</th>
              <th scope="col" class="px-6 py-4 text-center text-white w-44">
                <div class="flex items-center justify-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                  Jumlah Peserta
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-for="(item, index) in paginatedRows" :key="item.id" class="hover:bg-orange-50 transition-colors duration-200 group">
              <td class="px-6 py-4 text-center font-medium text-gray-500 group-hover:text-orange-500">
                {{ getRowNumber(index) }}
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold uppercase bg-orange-100 text-orange-800 border border-orange-200">
                  {{ item.kode_bengkel || '-' }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="font-medium text-gray-800 text-base">{{ item.nama_bengkel }}</div>
              </td>
              <td class="px-6 py-4">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-800">
                  {{ item.kecamatan || 'Belum diatur' }}
                </span>
              </td>
              <td class="px-6 py-4 text-center">
                <a
                  v-if="item.google_maps_link"
                  :href="item.google_maps_link"
                  target="_blank"
                  class="inline-flex items-center px-3 py-1.5 rounded-lg bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 transition-all font-semibold text-xs border border-green-200"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Buka Maps ↗
                </a>
                <span v-else class="text-gray-400">-</span>
              </td>
              <td class="px-6 py-4 text-center">
                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full font-bold text-sm"
                  :class="item.jumlah_peserta > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'">
                  {{ item.jumlah_peserta }}
                </span>
              </td>
            </tr>
            <tr v-if="filteredRows.length === 0">
              <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                <div class="flex flex-col items-center justify-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span class="text-lg font-medium">{{ search ? 'Bengkel tidak ditemukan.' : 'Belum ada data bengkel yang tersedia.' }}</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile Card View -->
      <div class="md:hidden bg-gray-50 p-4">
        <div v-if="paginatedRows.length > 0" class="flex flex-col space-y-4">
          <div v-for="(item, index) in paginatedRows" :key="item.id" class="p-5 bg-white rounded-xl shadow-sm border border-gray-100 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-3 bg-orange-50 rounded-bl-xl text-xs font-bold text-orange-500">
              #{{ getRowNumber(index) }}
            </div>

            <h4 class="font-bold text-gray-800 text-lg mb-1 pr-8 group-hover:text-orange-500 transition-colors">{{ item.nama_bengkel }}</h4>
            <div class="w-12 h-1 bg-orange-500 rounded-full mb-4 opacity-20 group-hover:opacity-100 transition-opacity"></div>

            <div class="flex flex-col gap-3">
              <!-- Kode Bengkel Row -->
              <div class="flex items-center gap-3 text-sm">
                <div class="w-8 h-8 rounded-full bg-orange-50 flex items-center justify-center flex-shrink-0">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                  </svg>
                </div>
                <span class="font-bold text-orange-700 bg-orange-50 px-2 py-0.5 rounded border border-orange-100 text-xs uppercase">
                  Kode: {{ item.kode_bengkel || '-' }}
                </span>
              </div>

              <!-- Kecamatan Row -->
              <div class="flex items-center gap-3 text-sm">
                <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <span class="font-medium text-gray-600">
                  {{ item.kecamatan || 'Belum diatur' }}
                </span>
              </div>

              <!-- Jumlah Peserta Row -->
              <div class="flex items-center gap-3 text-sm">
                <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center flex-shrink-0">
                   <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="flex items-center gap-2">
                  <span class="text-gray-600">Jumlah Peserta:</span>
                  <span class="font-bold text-gray-800">
                    {{ item.jumlah_peserta }}
                  </span>
                </div>
              </div>

              <!-- Google Maps Link Button -->
              <div v-if="item.google_maps_link" class="pt-2">
                <a
                  :href="item.google_maps_link"
                  target="_blank"
                  class="flex items-center justify-center w-full px-4 py-2.5 rounded-xl bg-green-50 text-green-700 text-sm font-bold hover:bg-green-100 transition-colors border border-green-200"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Buka di Google Maps ↗
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile Empty State -->
        <div v-else class="p-8 text-center text-gray-500">
           <div class="flex flex-col items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="text-lg font-medium">{{ search ? 'Bengkel tidak ditemukan.' : 'Belum ada data bengkel.' }}</span>
            </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4" v-if="filteredRows.length > 0 && totalPages > 1">
         <span class="text-sm text-gray-500">
            Menampilkan <span class="font-medium">{{ (currentPage - 1) * perPage + 1 }}</span> - <span class="font-medium">{{ Math.min(currentPage * perPage, filteredRows.length) }}</span> dari <span class="font-medium">{{ filteredRows.length }}</span> data
         </span>

         <div class="w-full sm:w-auto overflow-x-auto -mx-6 px-6 sm:mx-0 sm:px-0 overscroll-x-contain touch-pan-x">
            <div class="flex items-center gap-1 whitespace-nowrap min-w-max pb-1">
              <button
                :disabled="currentPage === 1"
                @click="goToPage(currentPage - 1)"
                class="px-3 py-1 text-sm border rounded transition-colors duration-200"
                :class="currentPage === 1 ? 'text-gray-400 border-transparent cursor-not-allowed' : 'bg-white text-gray-700 border-gray-300 hover:bg-orange-50 hover:text-orange-600 hover:border-orange-300'"
              >
                &laquo;
              </button>
              <button
                v-for="page in paginationRange"
                :key="page"
                @click="goToPage(page)"
                class="px-3 py-1 text-sm border rounded transition-colors duration-200"
                :class="page === currentPage ? 'bg-orange-500 text-white border-orange-500' : 'bg-white text-gray-700 border-gray-300 hover:bg-orange-50 hover:text-orange-600 hover:border-orange-300'"
              >
                {{ page }}
              </button>
              <button
                :disabled="currentPage === totalPages"
                @click="goToPage(currentPage + 1)"
                class="px-3 py-1 text-sm border rounded transition-colors duration-200"
                :class="currentPage === totalPages ? 'text-gray-400 border-transparent cursor-not-allowed' : 'bg-white text-gray-700 border-gray-300 hover:bg-orange-50 hover:text-orange-600 hover:border-orange-300'"
              >
                &raquo;
              </button>
            </div>
         </div>
      </div>
    </div>
  </div>
</template>