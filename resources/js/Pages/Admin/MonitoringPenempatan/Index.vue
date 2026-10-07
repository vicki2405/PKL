<script setup>
import { ref, watch, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import debounce from 'lodash/debounce'
import {
  mdiAccountSwitch,
  mdiArrowLeft,
  mdiOfficeBuildingCog,
  mdiHumanMaleBoard,
  mdiAccountSchool,
  mdiMapMarker,
  mdiCalendarClock,
  mdiSwapHorizontal,
  mdiCheckCircle,
  mdiClockOutline,
  mdiFilterVariant,
  mdiMagnify,
  mdiClose,
  mdiChevronLeft,
  mdiChevronRight,
  mdiChevronDown,
  mdiCheck,
  mdiHistory,
  mdiFileDocumentCheck,
  mdiShieldCheck,
  mdiRefresh
} from '@mdi/js'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import NumberDynamic from '@admin/components/NumberDynamic.vue'

const props = defineProps({
  siswaList: {
    type: Object,
    required: true,
  },
  pembimbingOptions: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  statistics: {
    type: Object,
    default: () => ({
      totalSiswa: 0,
      totalPernahPindah: 0,
      totalBelumPindah: 0,
    }),
  },
})

const search = ref(props.filters.search || '')
const selectedPembimbing = ref(props.filters.pembimbing_id || '')
const selectedStatusPindah = ref(props.filters.status_pindah || 'all')

const pembimbingSearch = ref('')
const isPembimbingOpen = ref(false)
const pembimbingRef = ref(null)
const pembimbingInputRef = ref(null)

const handleClickOutsidePembimbing = (event) => {
  if (pembimbingRef.value && !pembimbingRef.value.contains(event.target)) {
    isPembimbingOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutsidePembimbing)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutsidePembimbing)
})

watch(isPembimbingOpen, (open) => {
  if (open) {
    pembimbingSearch.value = ''
    nextTick(() => {
      pembimbingInputRef.value?.focus()
    })
  }
})

const filteredPembimbingOptions = computed(() => {
  const q = pembimbingSearch.value.trim().toLowerCase()
  if (!q) return props.pembimbingOptions
  return props.pembimbingOptions.filter((p) =>
    (p.nama_guru || '').toLowerCase().includes(q)
  )
})

const selectedPembimbingName = computed(() => {
  if (!selectedPembimbing.value) return ''
  const found = props.pembimbingOptions.find(p => String(p.id) === String(selectedPembimbing.value))
  return found ? found.nama_guru : ''
})

const executeFilter = () => {
  router.get(
    route('admin.monitoring_penempatan.index'),
    {
      search: search.value || undefined,
      pembimbing_id: selectedPembimbing.value || undefined,
      status_pindah: selectedStatusPindah.value !== 'all' ? selectedStatusPindah.value : undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    }
  )
}

const applyFiltersDebounced = debounce(() => {
  executeFilter()
}, 350)

const applyFiltersNow = () => {
  applyFiltersDebounced.cancel()
  executeFilter()
}

watch(search, () => applyFiltersDebounced())

const choosePembimbing = (id) => {
  selectedPembimbing.value = id
  isPembimbingOpen.value = false
  pembimbingSearch.value = ''
  applyFiltersNow()
}

const setStatusPindah = (status) => {
  selectedStatusPindah.value = status
  applyFiltersNow()
}

const resetFilters = () => {
  search.value = ''
  selectedPembimbing.value = ''
  pembimbingSearch.value = ''
  isPembimbingOpen.value = false
  selectedStatusPindah.value = 'all'
  applyFiltersNow()
}

watch(() => props.filters, (newFilters) => {
  if (newFilters) {
    search.value = newFilters.search || ''
    selectedPembimbing.value = newFilters.pembimbing_id || ''
    selectedStatusPindah.value = newFilters.status_pindah || 'all'
  }
}, { deep: true })

const isFilterActive = computed(() => {
  return search.value || selectedPembimbing.value || selectedStatusPindah.value !== 'all'
})

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  try {
    const d = new Date(dateStr)
    return d.toLocaleDateString('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric'
    })
  } catch (e) {
    return dateStr
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Monitoring Penempatan & Riwayat Pindah Siswa" />

    <SectionMain class="space-y-6">

      <!-- ================= 1. HEADER SECTION ================= -->
      <SectionTitleLineWithButton
        :icon="mdiAccountSwitch"
        title="Monitoring Penempatan & Riwayat Pindah Siswa"
        main
      >
        <BaseButton
          :to="route('admin.dashboard')"
          :icon="mdiArrowLeft"
          label="Kembali ke Dashboard"
          color="whiteDark"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>

      <!-- ================= 2. TOP STATISTIC CARDS ================= -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Card 1: Total Siswa -->
        <div class="p-4 sm:p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm flex items-center justify-between">
          <div>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Siswa PKL</span>
            <div class="text-3xl font-black text-slate-800 dark:text-slate-100 tracking-tight mt-1">
              <NumberDynamic :value="statistics.totalSiswa" />
            </div>
            <span class="text-[11px] text-slate-500 font-medium">Peserta terdaftar</span>
          </div>
          <div class="p-3.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
            <BaseIcon :path="mdiAccountSchool" size="28" />
          </div>
        </div>

        <!-- Card 2: Pernah Pindah / Mutasi -->
        <div class="p-4 sm:p-5 rounded-3xl bg-white dark:bg-slate-900 border border-amber-200/80 dark:border-amber-900/50 shadow-sm flex items-center justify-between">
          <div>
            <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Pernah Pindah Bengkel</span>
            <div class="text-3xl font-black text-amber-600 dark:text-amber-400 tracking-tight mt-1">
              <NumberDynamic :value="statistics.totalPernahPindah" />
            </div>
            <span class="text-[11px] text-slate-500 font-medium">Mutasi disetujui</span>
          </div>
          <div class="p-3.5 rounded-2xl bg-amber-50 dark:bg-amber-950/60 text-amber-600 dark:text-amber-400">
            <BaseIcon :path="mdiSwapHorizontal" size="28" />
          </div>
        </div>

        <!-- Card 3: Belum Pernah Pindah -->
        <div class="p-4 sm:p-5 rounded-3xl bg-white dark:bg-slate-900 border border-emerald-200/80 dark:border-emerald-900/50 shadow-sm flex items-center justify-between">
          <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Belum Pernah Pindah</span>
            <div class="text-3xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight mt-1">
              <NumberDynamic :value="statistics.totalBelumPindah" />
            </div>
            <span class="text-[11px] text-slate-500 font-medium">Tetap di bengkel awal</span>
          </div>
          <div class="p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
            <BaseIcon :path="mdiShieldCheck" size="28" />
          </div>
        </div>
      </div>

      <!-- ================= 3. FILTER PANEL ================= -->
      <div class="bg-white dark:bg-slate-900 p-4 sm:p-5 rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm space-y-4">
        
        <!-- Filter Tabs (Pernah Pindah vs Belum) -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800">
          <div class="inline-flex p-1 bg-slate-100 dark:bg-slate-800/80 rounded-2xl gap-1 text-xs">
            <button
              type="button"
              @click="setStatusPindah('all')"
              class="px-4 py-2 font-extrabold rounded-xl transition-all cursor-pointer"
              :class="selectedStatusPindah === 'all' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-800'"
            >
              Semua Siswa ({{ statistics.totalSiswa }})
            </button>
            <button
              type="button"
              @click="setStatusPindah('pernah_pindah')"
              class="px-4 py-2 font-extrabold rounded-xl transition-all cursor-pointer flex items-center gap-1.5"
              :class="selectedStatusPindah === 'pernah_pindah' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-800'"
            >
              <BaseIcon :path="mdiSwapHorizontal" size="16" />
              <span>Pernah Pindah / Mutasi ({{ statistics.totalPernahPindah }})</span>
            </button>
            <button
              type="button"
              @click="setStatusPindah('belum_pindah')"
              class="px-4 py-2 font-extrabold rounded-xl transition-all cursor-pointer flex items-center gap-1.5"
              :class="selectedStatusPindah === 'belum_pindah' ? 'bg-emerald-600 text-white shadow-xs' : 'text-slate-600 dark:text-slate-400 hover:text-slate-800'"
            >
              <BaseIcon :path="mdiShieldCheck" size="16" />
              <span>Belum Pernah Pindah ({{ statistics.totalBelumPindah }})</span>
            </button>
          </div>

          <button
            v-if="isFilterActive"
            type="button"
            @click="resetFilters"
            class="text-xs font-bold text-rose-500 hover:text-rose-700 flex items-center gap-1 cursor-pointer transition-colors"
          >
            <BaseIcon :path="mdiRefresh" size="15" />
            <span>Reset Filter</span>
          </button>
        </div>

        <!-- Filter Controls: Search & Dropdown Pembimbing -->
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
          
          <!-- Search Box (Kolom 7) -->
          <div class="sm:col-span-7 relative">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
              <BaseIcon :path="mdiMagnify" size="18" />
            </div>
            <input
              v-model="search"
              type="text"
              class="w-full pl-10 pr-10 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 shadow-xs"
              placeholder="Cari nama siswa, NISN, nama bengkel sekarang, atau bengkel asal..."
            />
            <button
              v-if="search"
              type="button"
              @click="search = ''"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
            >
              <BaseIcon :path="mdiClose" size="16" />
            </button>
          </div>

          <!-- Dropdown Filter Pembimbing (Kolom 5) -->
          <div class="sm:col-span-5 relative" ref="pembimbingRef">
            <div class="flex items-center gap-2">
              <div class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 shrink-0">
                <BaseIcon :path="mdiHumanMaleBoard" size="18" />
              </div>

              <!-- Combobox Trigger Button -->
              <div class="relative w-full">
                <button
                  type="button"
                  @click="isPembimbingOpen = !isPembimbingOpen"
                  @keydown.esc="isPembimbingOpen = false"
                  class="w-full px-3.5 py-2.5 text-xs sm:text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-left font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-xs flex items-center justify-between transition-all cursor-pointer"
                  :class="selectedPembimbing ? 'text-indigo-600 dark:text-indigo-400 font-semibold border-indigo-300 dark:border-indigo-700' : 'text-slate-700 dark:text-slate-300'"
                >
                  <span class="truncate pr-2">
                    {{ selectedPembimbingName || '-- Semua Guru Pembimbing --' }}
                  </span>
                  <div class="flex items-center gap-1 shrink-0 text-slate-400">
                    <span
                      v-if="selectedPembimbing"
                      @click.stop="choosePembimbing('')"
                      class="hover:text-rose-500 p-0.5 rounded cursor-pointer transition-colors"
                      title="Hapus filter pembimbing"
                    >
                      <BaseIcon :path="mdiClose" size="16" />
                    </span>
                    <BaseIcon
                      :path="mdiChevronDown"
                      size="18"
                      class="transition-transform duration-200"
                      :class="{ 'rotate-180': isPembimbingOpen }"
                    />
                  </div>
                </button>

                <!-- Popover Menu -->
                <div
                  v-if="isPembimbingOpen"
                  class="absolute z-50 mt-1.5 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl overflow-hidden py-1.5 text-xs sm:text-sm"
                >
                  <!-- Search input inside dropdown -->
                  <div class="p-2 border-b border-slate-100 dark:border-slate-800">
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-slate-400">
                        <BaseIcon :path="mdiMagnify" size="15" />
                      </div>
                      <input
                        ref="pembimbingInputRef"
                        v-model="pembimbingSearch"
                        type="text"
                        placeholder="Ketik nama pembimbing..."
                        class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        @click.stop
                        @keydown.esc="isPembimbingOpen = false"
                      />
                    </div>
                  </div>

                  <!-- Options List with Max Height and Scrollbar -->
                  <div class="max-h-56 overflow-y-auto divide-y divide-slate-50 dark:divide-slate-800/50">
                    <button
                      type="button"
                      @click="choosePembimbing('')"
                      class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/70 flex items-center justify-between cursor-pointer transition-colors"
                      :class="!selectedPembimbing ? 'text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50/50 dark:bg-indigo-950/30' : 'text-slate-600 dark:text-slate-400'"
                    >
                      <span>-- Semua Guru Pembimbing --</span>
                      <BaseIcon v-if="!selectedPembimbing" :path="mdiCheck" size="16" />
                    </button>

                    <button
                      v-for="p in filteredPembimbingOptions"
                      :key="p.id"
                      type="button"
                      @click="choosePembimbing(p.id)"
                      class="w-full text-left px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/70 flex items-center justify-between cursor-pointer transition-colors"
                      :class="String(selectedPembimbing) === String(p.id) ? 'text-indigo-600 dark:text-indigo-400 font-bold bg-indigo-50/50 dark:bg-indigo-950/30' : 'text-slate-700 dark:text-slate-200'"
                    >
                      <span class="truncate">{{ p.nama_guru }}</span>
                      <BaseIcon v-if="String(selectedPembimbing) === String(p.id)" :path="mdiCheck" size="16" />
                    </button>

                    <div
                      v-if="filteredPembimbingOptions.length === 0"
                      class="px-3 py-4 text-center text-xs text-slate-400 italic"
                    >
                      Pembimbing "{{ pembimbingSearch }}" tidak ditemukan
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>

      <!-- ================= 4. MAIN DATA TABLE ================= -->
      <CardBox class="rounded-3xl border border-slate-200/80 dark:border-slate-800 shadow-sm overflow-hidden" has-table>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs sm:text-sm">
            <thead>
              <tr class="bg-slate-50/80 dark:bg-slate-800/60 border-b border-slate-200/80 dark:border-slate-800 text-slate-600 dark:text-slate-400 font-extrabold uppercase text-[11px] tracking-wider">
                <th class="py-3.5 px-4 w-12 text-center">No</th>
                <th class="py-3.5 px-4 min-w-[200px]">Data Siswa</th>
                <th class="py-3.5 px-4 min-w-[260px]">Bengkel Sekarang (Aktif)</th>
                <th class="py-3.5 px-4 min-w-[280px]">Riwayat Bengkel Sebelumnya</th>
                <th class="py-3.5 px-4 min-w-[130px] text-center">Status Pindah</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-slate-700 dark:text-slate-300">
              
              <tr
                v-for="(siswa, idx) in siswaList.data"
                :key="siswa.id"
                class="hover:bg-slate-50/70 dark:hover:bg-slate-800/50 transition-colors"
              >
                <!-- 1. Nomor -->
                <td class="py-4 px-4 text-center font-bold text-slate-400">
                  {{ (siswaList.current_page - 1) * siswaList.per_page + idx + 1 }}
                </td>

                <!-- 2. Identitas Siswa -->
                <td class="py-4 px-4">
                  <div class="space-y-1">
                    <span class="font-extrabold text-sm text-slate-900 dark:text-slate-100 block">
                      {{ siswa.nama }}
                    </span>
                    <div class="flex flex-wrap items-center gap-1.5 text-xs text-slate-500">
                      <span class="px-1.5 py-0.5 rounded bg-slate-100 dark:bg-slate-800 font-mono text-[10px] font-bold text-slate-600 dark:text-slate-300">
                        NISN: {{ siswa.nisn || '-' }}
                      </span>
                      <span v-if="siswa.kelas" class="px-1.5 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/50 text-[10px] font-bold text-indigo-700 dark:text-indigo-300">
                        {{ siswa.kelas.nama_kelas }}
                      </span>
                      <span v-if="siswa.kelas?.jurusan" class="text-[10px] text-slate-400">
                        ({{ siswa.kelas.jurusan.kode_jurusan || siswa.kelas.jurusan.nama_jurusan }})
                      </span>
                    </div>
                  </div>
                </td>

                <!-- 3. Bengkel Saat Ini (Aktif) & Pembimbing Saat Ini -->
                <td class="py-4 px-4">
                  <div v-if="siswa.penempatan?.bengkel" class="space-y-1.5">
                    <div class="flex items-center gap-1.5">
                      <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0 animate-pulse"></span>
                      <span class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">
                        {{ siswa.penempatan.bengkel.nama_bengkel }}
                      </span>
                    </div>

                    <!-- Lokasi Bengkel -->
                    <div class="flex items-center gap-1 text-[11px] text-slate-500 dark:text-slate-400">
                      <BaseIcon :path="mdiMapMarker" size="13" class="text-rose-500 shrink-0" />
                      <span>Kec. {{ siswa.penempatan.bengkel.kecamatan || '-' }} {{ siswa.penempatan.bengkel.desa ? `• Desa ${siswa.penempatan.bengkel.desa}` : '' }}</span>
                    </div>

                    <!-- Pembimbing Sekarang -->
                    <div class="flex items-center gap-1 text-xs text-emerald-800 dark:text-emerald-300 bg-emerald-50/80 dark:bg-emerald-950/40 px-2.5 py-1 rounded-xl border border-emerald-200/60 dark:border-emerald-800/50 w-fit">
                      <BaseIcon :path="mdiHumanMaleBoard" size="14" class="text-emerald-600 shrink-0" />
                      <span class="font-semibold text-[11px]">
                        {{ siswa.penempatan.pembimbing ? siswa.penempatan.pembimbing.nama_guru : 'Belum Ada Pembimbing' }}
                      </span>
                    </div>
                  </div>

                  <div v-else class="text-slate-400 italic text-xs flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                    <span>Belum Ditempatkan di Bengkel</span>
                  </div>
                </td>

                <!-- 4. Riwayat Bengkel Sebelumnya & Tanggal Pindah -->
                <td class="py-4 px-4">
                  <!-- Jika Siswa Pernah Mutasi -->
                  <div v-if="siswa.mutasis && siswa.mutasis.length > 0" class="space-y-2">
                    <div
                      v-for="(mutasi, mIdx) in siswa.mutasis"
                      :key="mutasi.id"
                      class="p-2.5 rounded-2xl bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200/70 dark:border-amber-800/50 space-y-1.5"
                    >
                      <!-- Bengkel Asal -->
                      <div class="flex items-center gap-1.5">
                        <BaseIcon :path="mdiOfficeBuildingCog" size="14" class="text-amber-600 shrink-0" />
                        <span class="font-bold text-slate-800 dark:text-slate-200 text-xs">
                          {{ mutasi.bengkel_asal?.nama_bengkel || 'Bengkel Asal Tidak Tercatat' }}
                        </span>
                        <span class="text-[10px] text-amber-700 dark:text-amber-400 font-bold px-1.5 py-0.2 bg-amber-100 dark:bg-amber-900/60 rounded">
                          Bengkel Lama
                        </span>
                      </div>

                      <!-- Pembimbing Sebelumnya & Tanggal Pindah -->
                      <div class="flex flex-wrap items-center gap-2 text-[11px] text-slate-600 dark:text-slate-400 pt-0.5">
                        <div class="flex items-center gap-1">
                          <BaseIcon :path="mdiHumanMaleBoard" size="13" class="text-slate-400" />
                          <span>Guru: <strong>{{ mutasi.pembimbing_asal?.nama_guru || 'Tidak Tercatat' }}</strong></span>
                        </div>
                        <span class="text-slate-300">•</span>
                        <div class="flex items-center gap-1 text-indigo-600 dark:text-indigo-400 font-semibold">
                          <BaseIcon :path="mdiCalendarClock" size="13" />
                          <span>Pindah: {{ formatDate(mutasi.tanggal_surat || mutasi.updated_at) }}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Jika Belum Pernah Mutasi -->
                  <div v-else class="text-slate-400 text-xs flex items-center gap-1.5 py-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-600"></span>
                    <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500">
                      Belum pernah pindah (Tetap)
                    </span>
                  </div>
                </td>

                <!-- 5. Status Perpindahan -->
                <td class="py-4 px-4 text-center">
                  <span
                    v-if="siswa.mutasis && siswa.mutasis.length > 0"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-extrabold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-300 dark:border-amber-700"
                  >
                    <BaseIcon :path="mdiSwapHorizontal" size="13" />
                    Pernah Mutasi ({{ siswa.mutasis.length }}x)
                  </span>
                  <span
                    v-else
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400 border border-slate-200 dark:border-slate-700"
                  >
                    <BaseIcon :path="mdiShieldCheck" size="13" />
                    Tetap
                  </span>
                </td>

              </tr>

              <tr v-if="siswaList.data.length === 0">
                <td colspan="5" class="py-12 text-center text-slate-400 text-xs space-y-2">
                  <BaseIcon :path="mdiAccountSchool" size="36" class="mx-auto text-slate-300" />
                  <p class="font-bold text-slate-600 dark:text-slate-400 text-sm">Tidak ada data siswa yang cocok dengan kriteria filter</p>
                  <p class="text-[11px]">Silakan ubah kata kunci pencarian atau reset filter untuk menampilkan data.</p>
                </td>
              </tr>

            </tbody>
          </table>
        </div>

        <!-- ================= 5. PAGINATION NAV ================= -->
        <div v-if="siswaList.total > 0" class="p-4 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
          <div>
            Menampilkan <strong>{{ (siswaList.current_page - 1) * siswaList.per_page + 1 }}</strong> sampai <strong>{{ Math.min(siswaList.current_page * siswaList.per_page, siswaList.total) }}</strong> dari <strong>{{ siswaList.total }}</strong> siswa
          </div>

          <div class="flex items-center gap-1">
            <template v-for="(link, index) in siswaList.links" :key="index">
              <Link
                v-if="link.url"
                :href="link.url"
                class="px-3 py-1.5 rounded-lg border font-bold transition-colors cursor-pointer"
                :class="link.active ? 'bg-indigo-600 border-indigo-600 text-white' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 hover:bg-slate-50'"
                v-html="link.label"
              />
              <span
                v-else
                class="px-2 py-1 text-slate-300 dark:text-slate-600"
                v-html="link.label"
              />
            </template>
          </div>
        </div>

      </CardBox>

    </SectionMain>
  </LayoutAuthenticated>
</template>
