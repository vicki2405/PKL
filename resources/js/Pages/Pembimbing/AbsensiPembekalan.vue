<script setup>
import { Head, useForm, Link, router, usePage } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted } from 'vue'
import PembimbingLayout from '@/Layouts/PembimbingLayout.vue'

const page = usePage()
const flash = computed(() => page.props.flash || {})
const showFlash = ref(true)

const props = defineProps({
  aktivasiAktif: {
    type: Array,
    default: () => [],
  },
  siswas: {
    type: Array,
    default: () => [],
  },
  tanggal: {
    type: String,
    default: '',
  },
  existingAttendances: {
    type: Object,
    default: () => ({}),
  },
  pembimbing: {
    type: Object,
    default: () => ({}),
  },
})

const selectedTanggal = ref(props.tanggal || '')
const showSidebar = ref(true)

const form = useForm({
  tanggal: props.tanggal || '',
  attendances: {},
})

const syncAttendances = () => {
  form.tanggal = props.tanggal || ''
  selectedTanggal.value = props.tanggal || ''
  showFlash.value = true

  const initial = {}
  props.siswas.forEach((s) => {
    const existing = props.existingAttendances ? props.existingAttendances[s.id] : null
    if (existing) {
      if (existing.hadir) initial[s.id] = 'hadir'
      else if (existing.izin) initial[s.id] = 'izin'
      else if (existing.sakit) initial[s.id] = 'sakit'
      else if (existing.alpha) initial[s.id] = 'alpha'
      else initial[s.id] = 'hadir'
    } else {
      initial[s.id] = 'hadir'
    }
  })
  form.attendances = initial
}

onMounted(() => {
  syncAttendances()
})

watch(() => [props.tanggal, props.existingAttendances, props.siswas], () => {
  syncAttendances()
}, { deep: true })

const searchQuery = ref('')

const filteredSiswas = computed(() => {
  if (!searchQuery.value) return props.siswas
  const q = searchQuery.value.toLowerCase()
  return props.siswas.filter(
    (s) => s.nama.toLowerCase().includes(q) || s.kelas.toLowerCase().includes(q),
  )
})

const filledCount = computed(() => Object.keys(form.attendances).length)

const statusOptions = [
  { value: 'hadir', label: 'Hadir', singkatan: 'H', color: 'text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300', border: 'border-green-400', bg: 'bg-green-500' },
  { value: 'izin', label: 'Izin', singkatan: 'I', color: 'text-blue-700 bg-blue-100 dark:bg-blue-900 dark:text-blue-300', border: 'border-blue-400', bg: 'bg-blue-500' },
  { value: 'sakit', label: 'Sakit', singkatan: 'S', color: 'text-yellow-700 bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-300', border: 'border-yellow-400', bg: 'bg-yellow-500' },
  { value: 'alpha', label: 'Alpha', singkatan: 'A', color: 'text-red-700 bg-red-100 dark:bg-red-900 dark:text-red-300', border: 'border-red-400', bg: 'bg-red-500' },
]

const getStatus = (siswaId) => {
  return form.attendances[siswaId] || 'hadir'
}

const setStatus = (siswaId, status) => {
  form.attendances[siswaId] = status
}

const allHadir = () => {
  props.siswas.forEach((s) => {
    form.attendances[s.id] = 'hadir'
  })
}

const applyFilter = () => {
  if (!selectedTanggal.value) return
  router.get(route('pembimbing.absensi-pembekalan.index'), {
    tanggal: selectedTanggal.value,
  }, {
    preserveState: false,
    preserveScroll: true,
    replace: true,
  })
}

const submit = () => {
  if (!props.tanggal) {
    alert('Silakan pilih tanggal pembekalan terlebih dahulu.')
    return
  }

  const attendancesArray = Object.entries(form.attendances).map(([siswaId, status]) => ({
    siswa_id: parseInt(siswaId),
    status,
  }))

  if (attendancesArray.length === 0) {
    alert('Belum ada data absensi yang diisi.')
    return
  }

  form.transform(() => ({
    tanggal: props.tanggal,
    attendances: attendancesArray,
  }))

  form.post(route('pembimbing.absensi-pembekalan.store'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <PembimbingLayout>
    <Head title="Absensi Pembekalan" />

    <div class="max-w-7xl mx-auto pb-24 md:pb-8">
      <!-- Flash Alert -->
      <div v-if="flash.success && showFlash" class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
          <svg class="h-5 w-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm font-semibold">{{ flash.success }}</span>
        </div>
        <button type="button" @click="showFlash = false" class="text-emerald-500 hover:text-emerald-700">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <div v-if="flash.error && showFlash" class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center justify-between shadow-sm">
        <div class="flex items-center gap-2">
          <svg class="h-5 w-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm font-semibold">{{ flash.error }}</span>
        </div>
        <button type="button" @click="showFlash = false" class="text-rose-500 hover:text-rose-700">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>

      <!-- Header -->
      <div class="mb-4 md:mb-6">
        <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">Absensi Pembekalan</h1>
        <p class="text-xs md:text-sm text-gray-500 mt-0.5 md:mt-1">Catat kehadiran siswa pada kegiatan pembekalan PKL</p>
      </div>

      <!-- Filter Card -->
      <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md p-4 md:p-6 mb-4 md:mb-6">
        <div class="space-y-3 md:space-y-0 md:grid md:grid-cols-12 md:gap-4 md:items-end">
          <div class="md:col-span-7">
            <label class="block text-xs md:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pilih Tanggal Aktif</label>
            <div v-if="aktivasiAktif.length > 0" class="flex flex-wrap gap-1.5 md:gap-2">
              <button
                v-for="a in aktivasiAktif"
                :key="a.id"
                @click="selectedTanggal = a.tanggal; applyFilter()"
                :class="[
                  'px-3 md:px-4 py-1.5 md:py-2 rounded-lg md:rounded-xl text-xs md:text-sm font-bold transition-all duration-200 border-2',
                  selectedTanggal === a.tanggal
                    ? 'bg-indigo-600 text-white border-indigo-600 shadow-lg shadow-indigo-500/20'
                    : 'bg-white dark:bg-slate-700 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-slate-600 hover:border-indigo-300 hover:text-indigo-600'
                ]"
              >
                {{ a.tanggal_formatted }}
              </button>
            </div>
            <div v-else class="p-3 md:p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg md:rounded-xl text-xs md:text-sm text-yellow-700 dark:text-yellow-300">
              Belum ada jadwal pembekalan yang aktif. Silakan hubungi admin.
            </div>
          </div>

          <div class="md:col-span-5">
            <label class="block text-xs md:text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Atau pilih manual</label>
            <div class="flex gap-2">
              <input
                v-model="selectedTanggal"
                type="date"
                class="flex-1 px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-lg md:rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
              />
              <button
                @click="applyFilter"
                class="px-4 md:px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg md:rounded-xl font-semibold text-sm transition-all duration-200 shadow-md hover:shadow-lg active:scale-95 whitespace-nowrap"
              >
                Tampilkan
              </button>
            </div>
          </div>
        </div>
      </div>

      <form @submit.prevent="submit">
        <!-- Mobile Toolbar (Search + All Hadir) -->
        <div v-if="tanggal && filteredSiswas.length > 0" class="md:hidden flex items-center gap-2 mb-3">
          <div class="relative flex-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Cari nama/kelas..."
              class="w-full pl-9 pr-3 py-2.5 border border-gray-300 dark:border-slate-600 rounded-xl bg-white dark:bg-slate-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
            />
          </div>
          <button
            type="button"
            @click="allHadir"
            class="px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold text-sm transition-all active:scale-95 shadow-md whitespace-nowrap flex items-center gap-1.5"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            Semua Hadir
          </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 md:gap-6">
          <!-- Left sidebar - Desktop only -->
          <div class="hidden md:block md:col-span-1 space-y-4">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md p-6">
              <div v-if="tanggal">
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Cari Siswa</label>
                <input
                  v-model="searchQuery"
                  type="text"
                  placeholder="Cari nama/kelas..."
                  class="px-3 py-2 border border-gray-300 dark:border-slate-600 rounded-xl w-full bg-white dark:bg-slate-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-transparent mb-4"
                />
                <button
                  type="button"
                  @click="allHadir"
                  class="w-full px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-xl font-bold text-sm transition-all shadow-md hover:shadow-lg active:scale-95 mb-4 flex items-center justify-center gap-2"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                  Semua Hadir
                </button>
                <div class="text-xs text-gray-500 dark:text-gray-400 space-y-1 border-t border-gray-100 dark:border-slate-700 pt-3">
                  <p>Total siswa: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ siswas.length }}</span></p>
                  <p>Terisi: <span class="font-semibold text-gray-700 dark:text-gray-300">{{ filledCount }}</span> / {{ siswas.length }}</p>
                  <div v-if="filledCount > 0" class="mt-2 w-full bg-gray-200 dark:bg-slate-600 rounded-full h-1.5">
                    <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-300" :style="{ width: (filledCount / siswas.length * 100) + '%' }"></div>
                  </div>
                </div>
              </div>
              <div v-else class="text-sm text-gray-500 text-center py-4">
                Pilih tanggal terlebih dahulu
              </div>
            </div>
            <!-- Legend Desktop -->
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md p-6">
              <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-3">Keterangan</h4>
              <div class="space-y-2.5">
                <div v-for="opt in statusOptions" :key="opt.value" class="flex items-center gap-3">
                  <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold" :class="opt.color">
                    {{ opt.singkatan }}
                  </span>
                  <span class="text-sm text-gray-600 dark:text-gray-400">{{ opt.label }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Student List -->
          <div class="md:col-span-3">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md overflow-hidden">
              <!-- Empty states -->
              <div v-if="!tanggal" class="py-12 md:py-16 text-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 md:h-16 md:w-16 mx-auto mb-3 md:mb-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-base md:text-lg font-medium">Silakan pilih tanggal terlebih dahulu</p>
              </div>
              <div v-else-if="filteredSiswas.length === 0" class="py-12 md:py-16 text-center text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 md:h-16 md:w-16 mx-auto mb-3 md:mb-4 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-base md:text-lg font-medium">Tidak ada siswa bimbingan Anda</p>
                <p v-if="searchQuery" class="text-sm mt-1">Coba ubah kata kunci pencarian</p>
              </div>

              <!-- Student list: Mobile cards + Desktop table -->
              <div v-else>
                <!-- MOBILE: Card-based layout -->
                <div class="md:hidden divide-y divide-gray-100 dark:divide-slate-700">
                  <div class="px-4 py-2.5 bg-gray-50 dark:bg-slate-700/50 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <span>{{ filteredSiswas.length }} siswa</span>
                    <span class="flex items-center gap-1">
                      <span class="inline-block w-2 h-2 rounded-full" :class="filledCount === filteredSiswas.length ? 'bg-green-500' : 'bg-indigo-500'"></span>
                      {{ filledCount }}/{{ filteredSiswas.length }} terisi
                    </span>
                  </div>

                  <div
                    v-for="(siswa, index) in filteredSiswas"
                    :key="siswa.id"
                    class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors"
                  >
                    <div class="flex items-start justify-between mb-2.5">
                      <div class="flex-1 min-w-0 mr-2">
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ siswa.nama }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                          {{ siswa.kelas }}
                          <span v-if="siswa.bengkel" class="mx-1">•</span>
                          <span v-if="siswa.bengkel">{{ siswa.bengkel }}</span>
                        </p>
                      </div>
                    </div>
                    <div class="grid grid-cols-4 gap-1.5">
                      <button
                        type="button"
                        v-for="opt in statusOptions"
                        :key="opt.value"
                        @click="setStatus(siswa.id, opt.value)"
                        :class="[
                          'flex flex-col items-center justify-center py-2.5 px-1 rounded-xl text-xs font-bold border-2 transition-all duration-150',
                          getStatus(siswa.id) === opt.value
                            ? opt.color + ' ' + opt.border + ' shadow-sm'
                            : 'text-gray-400 border-gray-200 dark:border-slate-600 hover:border-gray-400 dark:hover:border-slate-400 bg-gray-50 dark:bg-slate-700/30'
                        ]"
                      >
                        <span class="text-base leading-none mb-1">{{ opt.singkatan }}</span>
                        <span class="text-[10px] leading-tight">{{ opt.label }}</span>
                      </button>
                    </div>
                  </div>
                </div>

                <!-- TABLET/DESKTOP: Table layout -->
                <div class="hidden md:block overflow-x-auto">
                  <table class="w-full">
                    <thead>
                      <tr class="bg-gray-50 dark:bg-slate-700 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold tracking-wider">
                        <th class="px-4 py-3 text-left w-10">No</th>
                        <th class="px-4 py-3 text-left">Nama Siswa</th>
                        <th class="px-4 py-3 text-left w-24">Kelas</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Bengkel</th>
                        <th class="px-4 py-3 text-center">Hadir</th>
                        <th class="px-4 py-3 text-center">Izin</th>
                        <th class="px-4 py-3 text-center">Sakit</th>
                        <th class="px-4 py-3 text-center">Alpha</th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                      <tr
                        v-for="(siswa, index) in filteredSiswas"
                        :key="siswa.id"
                        class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors duration-150"
                      >
                        <td class="px-4 py-3 text-gray-500 text-sm">{{ index + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100 text-sm">{{ siswa.nama }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ siswa.kelas }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 hidden lg:table-cell">{{ siswa.bengkel }}</td>
                        <td class="px-1.5 py-2 text-center">
                          <button
                            type="button"
                            v-for="opt in statusOptions"
                            :key="opt.value"
                            @click="setStatus(siswa.id, opt.value)"
                            :class="[
                              'inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold border-2 transition-all duration-150 mx-0.5',
                              getStatus(siswa.id) === opt.value
                                ? opt.color + ' ' + opt.border
                                : 'text-gray-400 border-gray-200 dark:border-slate-600 hover:border-gray-400 dark:hover:border-slate-400 bg-transparent'
                            ]"
                          >
                            {{ opt.singkatan }}
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Mobile sticky bottom submit -->
              <div v-if="tanggal && filteredSiswas.length > 0" class="md:hidden fixed bottom-16 left-0 right-0 z-40 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 shadow-[0_-4px_12px_rgba(0,0,0,0.08)] px-4 py-3">
                <div class="flex items-center justify-between mb-2 text-xs text-gray-500">
                  <span>{{ filledCount }}/{{ filteredSiswas.length }} siswa terisi</span>
                  <div class="w-24 bg-gray-200 dark:bg-slate-600 rounded-full h-1.5">
                    <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-300" :style="{ width: (filledCount / filteredSiswas.length * 100) + '%' }"></div>
                  </div>
                </div>
                <button
                  type="submit"
                  :disabled="form.processing || filledCount === 0"
                  class="w-full py-3 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2 text-sm"
                >
                  <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                  </svg>
                  <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                  </svg>
                  {{ form.processing ? 'Menyimpan...' : 'Simpan Absensi' }}
                </button>
              </div>

              <!-- Desktop submit button -->
              <div v-if="tanggal && filteredSiswas.length > 0" class="hidden md:block p-4 border-t border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-800 rounded-b-2xl">
                <div class="flex items-center justify-between">
                  <div class="text-xs text-gray-500">
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ filledCount }}</span> dari <span class="font-semibold text-gray-700 dark:text-gray-300">{{ filteredSiswas.length }}</span> siswa terisi
                  </div>
                  <button
                    type="submit"
                    :disabled="form.processing || filledCount === 0"
                    class="px-6 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-white rounded-xl font-bold shadow-lg shadow-indigo-500/20 transition-all hover:shadow-indigo-500/40 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                  >
                    <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                    </svg>
                    <svg v-else class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    {{ form.processing ? 'Menyimpan...' : 'Simpan Absensi' }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </form>

      <!-- Mobile Legend (always visible at bottom) -->
      <div v-if="tanggal && filteredSiswas.length > 0" class="md:hidden mt-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-md p-4">
          <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">Keterangan Status</h4>
          <div class="grid grid-cols-2 gap-2">
            <div v-for="opt in statusOptions" :key="opt.value" class="flex items-center gap-2">
              <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-[10px] font-bold" :class="opt.color">
                {{ opt.singkatan }}
              </span>
              <span class="text-xs text-gray-600 dark:text-gray-400">{{ opt.label }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </PembimbingLayout>
</template>
