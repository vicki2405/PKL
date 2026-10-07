<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import {
  mdiClipboardList,
  mdiMagnify,
  mdiFileExcel,
  mdiFilePdfBox,
  mdiCalendarMonth,
  mdiBookOpenPageVariant,
  mdiPlus,
  mdiPrinter,
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import { computed, watch, ref } from 'vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  siswas: {
    type: Object,
    default: () => ({}),
  },
  pembekalanData: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  kelasOptions: {
    type: Array,
    default: () => [],
  },
  pembimbingOptions: {
    type: Array,
    default: () => [],
  },
  activeTab: {
    type: String,
    default: 'siswa',
  },
})

const currentTab = computed(() => props.activeTab || 'siswa')

const activeData = computed(() => {
  if (currentTab.value === 'pembekalan') {
    return props.pembekalanData
  }
  return props.siswas
})

const activeDataItems = computed(() => {
  if (!activeData.value || !Array.isArray(activeData.value.data)) return []
  return activeData.value.data
})

const currentLinks = computed(() => {
  if (!activeData.value || !Array.isArray(activeData.value.links)) return []
  return activeData.value.links.map((link) => {
    let label = link.label
    label = label.replace('&laquo;', '').replace('&raquo;', '').trim()
    if (label === 'Previous') {
      label = 'Sebelumnya'
    } else if (label === 'Next') {
      label = 'Selanjutnya'
    }
    return { ...link, label }
  })
})

const isSiswaTab = computed(() => currentTab.value === 'siswa')
const isPembekalanTab = computed(() => currentTab.value === 'pembekalan')

const filterForm = useForm({
  search: props.filters?.search || '',
  per_page: normalizeNumericFilter(props.filters?.per_page, 10),
  month: normalizeNumericFilter(props.filters?.month, new Date().getMonth() + 1),
  year: normalizeNumericFilter(props.filters?.year, new Date().getFullYear()),
  kelas_id: normalizeStringFilter(props.filters?.kelas_id),
  pembimbing_id: normalizeStringFilter(props.filters?.pembimbing_id),
})

const setActiveTab = (tab) => {
  if (tab === currentTab.value) return
  router.get(route('admin.rekap-absensi.index'), {
    ...buildFilterParams(),
    tab,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

const search = () => {
  router.get(route('admin.rekap-absensi.index'), {
    ...buildFilterParams(),
    tab: currentTab.value,
  }, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

const resetFilters = () => {
  filterForm.search = ''
  filterForm.per_page = 10
  filterForm.month = new Date().getMonth() + 1
  filterForm.year = new Date().getFullYear()
  filterForm.kelas_id = ''
  filterForm.pembimbing_id = ''
  search()
}

const perPageOptions = [
  10,
  20,
  50,
  100,
]

const monthOptions = [
  { id: 1, label: 'Januari' },
  { id: 2, label: 'Februari' },
  { id: 3, label: 'Maret' },
  { id: 4, label: 'April' },
  { id: 5, label: 'Mei' },
  { id: 6, label: 'Juni' },
  { id: 7, label: 'Juli' },
  { id: 8, label: 'Agustus' },
  { id: 9, label: 'September' },
  { id: 10, label: 'Oktober' },
  { id: 11, label: 'November' },
  { id: 12, label: 'Desember' },
]

function normalizeNumericFilter(value, fallback) {
  if (value && typeof value === 'object') {
    return Number(value.id ?? value.value ?? fallback)
  }

  const normalized = Number(value)
  return Number.isFinite(normalized) && normalized > 0 ? normalized : fallback
}

function normalizeStringFilter(value) {
  if (value && typeof value === 'object') {
    return String(value.id ?? value.value ?? '')
  }

  return value ? String(value) : ''
}

function buildFilterParams() {
  const params = {
    month: normalizeNumericFilter(filterForm.month, new Date().getMonth() + 1),
    year: normalizeNumericFilter(filterForm.year, new Date().getFullYear()),
    per_page: normalizeNumericFilter(filterForm.per_page, 10),
  }

  const search = String(filterForm.search || '').trim()
  const kelasId = normalizeStringFilter(filterForm.kelas_id)
  const pembimbingId = normalizeStringFilter(filterForm.pembimbing_id)

  if (search) {
    params.search = search
  }

  if (kelasId) {
    params.kelas_id = kelasId
  }

  if (pembimbingId) {
    params.pembimbing_id = pembimbingId
  }

  return params
}

const debouncedSearch = debounce(() => {
  search()
}, 250)

watch(
  () => [filterForm.search, filterForm.per_page, filterForm.month, filterForm.year, filterForm.kelas_id, filterForm.pembimbing_id],
  () => {
    debouncedSearch()
  },
)

const exportExcel = () => {
  const month = filterForm.month || new Date().getMonth() + 1
  const year = filterForm.year || new Date().getFullYear()
  const routeName = currentTab.value === 'pembekalan'
    ? 'admin.rekap-absensi.pembekalan.export-excel'
    : 'admin.rekap-absensi.export-excel'
  const params = {
    month,
    year,
    kelas_id: filterForm.kelas_id,
  }
  if (currentTab.value === 'pembekalan') {
    params.pembimbing_id = filterForm.pembimbing_id
  }
  const url = route(routeName, params)
  window.open(url, '_blank')
}

const exportPdf = () => {
  const month = filterForm.month || new Date().getMonth() + 1
  const year = filterForm.year || new Date().getFullYear()
  const routeName = currentTab.value === 'pembekalan'
    ? 'admin.rekap-absensi.pembekalan.export-pdf'
    : 'admin.rekap-absensi.export-pdf'
  const params = {
    month,
    year,
    kelas_id: filterForm.kelas_id,
  }
  if (currentTab.value === 'pembekalan') {
    params.pembimbing_id = filterForm.pembimbing_id
  }
  const url = route(routeName, params)
  window.open(url, '_blank')
}

const showCetakModal = ref(false)
const cetakTanggal = ref(new Date().toISOString().substr(0, 10))
const cetakPembimbingId = ref('')

const cetakAbsen = () => {
  cetakPembimbingId.value = filterForm.pembimbing_id || ''
  showCetakModal.value = true
}

const confirmCetak = () => {
  const params = {
    tanggal: cetakTanggal.value,
  }
  if (cetakPembimbingId.value) {
    params.pembimbing_id = cetakPembimbingId.value
  }
  const url = route('admin.rekap-absensi.pembekalan.cetak', params)
  window.open(url, '_blank')
  showCetakModal.value = false
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Rekap Absensi" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiClipboardList" title="Rekap Absensi" main />

      <!-- Tabs -->
      <div class="mb-6 border-b border-gray-200 dark:border-slate-700">
        <nav class="flex gap-6 -mb-px">
          <button
            @click="setActiveTab('siswa')"
            :class="[
              'inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition-colors',
              isSiswaTab
                ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'
            ]"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
              <path :d="mdiClipboardList" />
            </svg>
            Rekap Absensi Siswa
          </button>
          <button
            @click="setActiveTab('pembekalan')"
            :class="[
              'inline-flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 transition-colors',
              isPembekalanTab
                ? 'border-blue-600 text-blue-600 dark:border-blue-400 dark:text-blue-400'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-200'
            ]"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
              <path :d="mdiBookOpenPageVariant" />
            </svg>
            Rekap Absensi Pembekalan
          </button>
        </nav>
      </div>

      <!-- Action Buttons -->
      <div class="flex justify-end mb-4 gap-2">
        <template v-if="isPembekalanTab">
          <BaseButton
            color="warning"
            :icon="mdiPrinter"
            label="Cetak Absen"
            @click="cetakAbsen"
            rounded-full
            small
          />
          <BaseButton
            color="info"
            :icon="mdiPlus"
            label="Input Absen"
            :to="route('admin.rekap-absensi.pembekalan.create')"
            rounded-full
            small
          />
          <BaseButton
            color="success"
            :icon="mdiFileExcel"
            label="Export Excel"
            @click="exportExcel"
            rounded-full
            small
          />
          <BaseButton
            color="danger"
            :icon="mdiFilePdfBox"
            label="Export PDF"
            @click="exportPdf"
            rounded-full
            small
          />
        </template>
        <template v-else>
          <BaseButton
            color="success"
            :icon="mdiFileExcel"
            label="Export Excel"
            @click="exportExcel"
            rounded-full
            small
          />
          <BaseButton
            color="danger"
            :icon="mdiFilePdfBox"
            label="Export PDF"
            @click="exportPdf"
            rounded-full
            small
          />
        </template>
      </div>

      <!-- Filter Card -->
      <CardBox class="mb-6 shadow-md rounded-lg" is-form>
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
          <div class="md:col-span-2">
             <FormField label="Bulan">
                <div class="relative">
                   <select
                     v-model.number="filterForm.month"
                     class="px-3 py-2 pl-8 max-w-full focus:ring-3 focus:outline-hidden border-gray-700 rounded-sm w-full h-12 border bg-white dark:bg-slate-800 text-black dark:text-white"
                   >
                    <option v-for="month in monthOptions" :key="month.id" :value="month.id">
                      {{ month.label }}
                    </option>
                  </select>
                  <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-2 text-gray-500 dark:text-gray-400">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                      <path :d="mdiCalendarMonth" />
                    </svg>
                  </div>
                </div>
            </FormField>
          </div>
          <div class="md:col-span-2">
            <FormField label="Kelas">
                <select v-model="filterForm.kelas_id" class="px-3 py-2 max-w-full focus:ring-3 focus:outline-hidden border-gray-700 rounded-sm w-full dark:placeholder-gray-400 h-12 border bg-white dark:bg-slate-800 text-black dark:text-white">
                    <option value="">Semua Kelas</option>
                    <option v-for="kelas in kelasOptions" :key="kelas.id" :value="kelas.id">
                        {{ kelas.nama_kelas }}
                    </option>
                </select>
            </FormField>
          </div>
          <div class="md:col-span-4">
            <FormField label="Pembimbing">
                <select v-model="filterForm.pembimbing_id" class="px-3 py-2 max-w-full focus:ring-3 focus:outline-hidden border-gray-700 rounded-sm w-full dark:placeholder-gray-400 h-12 border bg-white dark:bg-slate-800 text-black dark:text-white">
                    <option value="">Semua Pembimbing</option>
                    <option v-for="p in pembimbingOptions" :key="p.id" :value="p.id">
                        {{ p.nama_guru }}
                    </option>
                </select>
            </FormField>
          </div>
          <div class="md:col-span-1">
             <FormField label="Per Halaman">
                   <select
                     v-model.number="filterForm.per_page"
                     class="px-3 py-2 max-w-full focus:ring-3 focus:outline-hidden border-gray-700 rounded-sm w-full h-12 border bg-white dark:bg-slate-800 text-black dark:text-white"
                   >
                     <option v-for="perPage in perPageOptions" :key="perPage" :value="perPage">
                       {{ perPage }}
                     </option>
                   </select>
            </FormField>
          </div>
          <div class="md:col-span-3">
            <FormField label="Pencarian">
                <FormControl
                    v-model="filterForm.search"
                    :icon="mdiMagnify"
                    :placeholder="isSiswaTab ? 'Cari Siswa, Bengkel...' : 'Cari Siswa...'"
                />
            </FormField>
          </div>
        </div>
      </CardBox>

      <!-- Data Table Card -->
      <CardBox class="mb-6 shadow-md rounded-lg" has-table>
        <div class="overflow-x-auto">
            <table class="table-auto w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold tracking-wider">
                <tr v-if="isSiswaTab">
                <th class="px-4 py-3 text-left">No</th>
                <th class="px-4 py-3 text-left">Nama Siswa</th>
                <th class="px-4 py-3 text-left">Kelas</th>
                <th class="px-4 py-3 text-left">Bengkel</th>
                <th class="px-4 py-3 text-left">Pembimbing</th>
                <th class="px-4 py-3 text-center">Hadir</th>
                <th class="px-4 py-3 text-center">Izin</th>
                <th class="px-4 py-3 text-center">Sakit</th>
                <th class="px-4 py-3 text-center">Alpha</th>
                <th class="px-4 py-3 text-center">Total</th>
                </tr>
                <tr v-if="isPembekalanTab">
                <th class="px-4 py-3 text-left">No</th>
                <th class="px-4 py-3 text-left">Nama Siswa</th>
                <th class="px-4 py-3 text-left">Kelas</th>
                <th class="px-4 py-3 text-center">Hadir</th>
                <th class="px-4 py-3 text-center">Izin</th>
                <th class="px-4 py-3 text-center">Sakit</th>
                <th class="px-4 py-3 text-center">Alpha</th>
                <th class="px-4 py-3 text-center">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                <tr v-if="activeDataItems.length === 0">
                <td :colspan="isSiswaTab ? 10 : 8" class="text-center py-8 text-gray-500">
                    <div class="flex flex-col items-center justify-center">
                        <span class="text-lg">Data tidak ditemukan</span>
                        <span class="text-sm mt-1">Coba ubah filter atau kata kunci pencarian</span>
                    </div>
                </td>
                </tr>
                <!-- Siswa Tab Rows -->
                <tr
                    v-for="(siswa, index) in activeDataItems"
                    :key="siswa.id"
                    v-if="isSiswaTab"
                    class="hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors duration-150"
                >
                <td class="px-4 py-3 text-gray-500 text-sm">
                    {{ (activeData.current_page - 1) * activeData.per_page + index + 1 }}
                </td>
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                    {{ siswa.nama }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                    {{ siswa.kelas }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                    {{ siswa.bengkel }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                    {{ siswa.pembimbing }}
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-green-700 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-300">
                        {{ siswa.hadir }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-300">
                        {{ siswa.izin }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-300">
                        {{ siswa.sakit }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-red-700 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-300">
                        {{ siswa.alpha }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="font-bold text-gray-700 dark:text-gray-200">
                        {{ siswa.hadir + siswa.izin + siswa.sakit + siswa.alpha }}
                    </span>
                </td>
                </tr>
                <!-- Pembekalan Tab Rows -->
                <tr
                    v-for="(siswa, index) in activeDataItems"
                    :key="siswa.id"
                    v-if="isPembekalanTab"
                    class="hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors duration-150"
                >
                <td class="px-4 py-3 text-gray-500 text-sm">
                    {{ (activeData.current_page - 1) * activeData.per_page + index + 1 }}
                </td>
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                    {{ siswa.nama }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">
                    {{ siswa.kelas }}
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-green-700 bg-green-100 rounded-full dark:bg-green-900 dark:text-green-300">
                        {{ siswa.hadir }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-blue-700 bg-blue-100 rounded-full dark:bg-blue-900 dark:text-blue-300">
                        {{ siswa.izin }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-yellow-700 bg-yellow-100 rounded-full dark:bg-yellow-900 dark:text-yellow-300">
                        {{ siswa.sakit }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex items-center justify-center w-8 h-8 text-xs font-bold text-red-700 bg-red-100 rounded-full dark:bg-red-900 dark:text-red-300">
                        {{ siswa.alpha }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="font-bold text-gray-700 dark:text-gray-200">
                        {{ siswa.hadir + siswa.izin + siswa.sakit + siswa.alpha }}
                    </span>
                </td>
                </tr>
            </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-900 rounded-b-lg flex flex-col md:flex-row items-center justify-between gap-4">
          <div class="w-full md:w-auto">
             <select
               v-model.number="filterForm.per_page"
               class="px-3 py-2 max-w-full focus:ring-3 focus:outline-hidden border-gray-700 rounded-sm w-full md:w-32 h-12 border bg-white dark:bg-slate-800 text-black dark:text-white"
             >
               <option v-for="perPage in perPageOptions" :key="perPage" :value="perPage">
                 {{ perPage }}
               </option>
             </select>
          </div>

          <BaseLevel class="w-full md:w-auto">
            <BaseButtons>
              <BaseButton
                v-for="(link, index) in currentLinks"
                :key="index"
                :active="link.active"
                :label="link.label.replace(/&laquo; /g, '« ').replace(/ &raquo;/g, ' »').replace(/&laquo;/g, '«').replace(/&raquo;/g, '»')"
                :color="link.active ? 'lightDark' : 'whiteDark'"
                :disabled="link.url === null"
                @click="link.url && router.visit(link.url, { preserveScroll: true })"
                small
              />
            </BaseButtons>
          </BaseLevel>

          <div class="text-xs text-gray-500">
            Halaman {{ activeData.current_page }} dari {{ activeData.last_page }}
          </div>
        </div>
      </CardBox>
      <!-- Cetak Absen Modal -->
      <teleport to="body">
        <div v-if="showCetakModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click.self="showCetakModal = false">
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Pengaturan Tanggal Cetak</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Pilih tanggal untuk daftar hadir pembekalan:</p>
            <FormField label="Tanggal">
              <FormControl
                v-model="cetakTanggal"
                type="date"
              />
            </FormField>
            <FormField label="Pembimbing">
              <select
                v-model="cetakPembimbingId"
                class="px-3 py-2 max-w-full focus:ring-3 focus:outline-hidden border-gray-700 rounded-sm w-full dark:placeholder-gray-400 h-12 border bg-white dark:bg-slate-800 text-black dark:text-white"
              >
                <option value="">Semua Pembimbing</option>
                <option v-for="p in pembimbingOptions" :key="p.id" :value="p.id">
                  {{ p.nama_guru }}
                </option>
              </select>
            </FormField>
            <div class="flex justify-end gap-3 mt-6">
              <BaseButton
                color="whiteDark"
                label="Batal"
                @click="showCetakModal = false"
                rounded-full
                small
              />
              <BaseButton
                color="success"
                label="Cetak"
                :icon="mdiPrinter"
                @click="confirmCetak"
                rounded-full
                small
              />
            </div>
          </div>
        </div>
      </teleport>
    </SectionMain>
  </LayoutAuthenticated>
</template>
