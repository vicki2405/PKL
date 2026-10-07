<script setup>
import { Head, router } from '@inertiajs/vue3'
import {
  mdiHumanMaleBoard,
  mdiOfficeBuildingCog,
  mdiAccountGroup,
  mdiArrowRightBoldCircleOutline,
  mdiRefresh,
  mdiFileExcel,
  mdiFilePdfBox,
  mdiArrowLeft,
  mdiMagnify,
  mdiAlertCircleOutline,
  mdiCheckCircle,
  mdiMapMarker,
  mdiSchoolOutline,
  mdiGoogleMaps,
} from '@mdi/js'
import { computed, ref, watch } from 'vue'
import { debounce } from 'lodash'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import FormField from '@admin/components/FormField.vue'
import FormControlSearchable from '@admin/components/FormControlSearchable.vue'

const props = defineProps({
  rows: {
    type: Object,
    default: () => ({ data: [], links: [], current_page: 1, last_page: 1, from: 0, to: 0, total: 0 }),
  },
  stats: {
    type: Object,
    default: () => ({
      pembimbing_total: 0,
      bengkel_terisi_total: 0,
      bengkel_belum_total: 0,
      siswa_terisi_total: 0,
      siswa_belum_total: 0,
    }),
  },
  filters: {
    type: Object,
    default: () => ({ search: '', jabatan: '', month: '', per_page: 20 }),
  },
  jabatanOptions: {
    type: Array,
    default: () => [],
  },
  unassignedBengkels: {
    type: Array,
    default: () => [],
  },
  pembimbingOptions: {
    type: Array,
    default: () => [],
  },
})

const activeTab = ref('pembimbing') // 'pembimbing' | 'unassigned'

const ensureExternalUrl = (url) => {
  if (!url) return '#'
  const trimmed = String(url).trim()
  if (!/^https?:\/\//i.test(trimmed)) {
    return 'https://' + trimmed
  }
  return trimmed
}

const search = ref(props.filters.search || '')
const selectedJabatan = ref(props.filters.jabatan || '')
const selectedMonth = ref(props.filters.month || '')
const perPage = ref(Number(props.filters.per_page) || 20)
const perPageOptions = [20, 50, 100]

const monthOptions = [
  { id: 'all', label: 'Semua Bulan', value: 'all' },
  { id: '1', label: 'Januari', value: '1' },
  { id: '2', label: 'Februari', value: '2' },
  { id: '3', label: 'Maret', value: '3' },
  { id: '4', label: 'April', value: '4' },
  { id: '5', label: 'Mei', value: '5' },
  { id: '6', label: 'Juni', value: '6' },
  { id: '7', label: 'Juli', value: '7' },
  { id: '8', label: 'Agustus', value: '8' },
  { id: '9', label: 'September', value: '9' },
  { id: '10', label: 'Oktober', value: '10' },
  { id: '11', label: 'November', value: '11' },
  { id: '12', label: 'Desember', value: '12' },
]

const jabatanFilterOptions = computed(() => {
  const list = [{ id: 'all', label: 'Semua Jabatan', value: 'all' }]
  for (const j of props.jabatanOptions) {
    list.push({ id: j, label: j, value: j })
  }
  return list
})

const exportExcel = () => {
  const params = {}
  if (search.value) params.search = search.value
  if (selectedJabatan.value && selectedJabatan.value !== 'all') params.jabatan = selectedJabatan.value
  if (selectedMonth.value && selectedMonth.value !== 'all') params.month = selectedMonth.value
  window.open(route('admin.input_data_pkl.input_pembimbing.export_excel', params), '_blank')
}

const exportPdf = () => {
  const params = {}
  if (search.value) params.search = search.value
  if (selectedJabatan.value && selectedJabatan.value !== 'all') params.jabatan = selectedJabatan.value
  if (selectedMonth.value && selectedMonth.value !== 'all') params.month = selectedMonth.value
  window.open(route('admin.input_data_pkl.input_pembimbing.export_pdf', params), '_blank')
}

const refreshData = () => {
  router.get(route('admin.input_data_pkl.input_pembimbing'), {}, {
    preserveScroll: true,
    preserveState: false,
  })
}

const pembimbingData = computed(() =>
  props.rows?.data && Array.isArray(props.rows.data) ? props.rows.data : [],
)

const pembimbingLinks = computed(() => {
  if (!props.rows?.links || !Array.isArray(props.rows.links)) return []
  return props.rows.links.map((link) => {
    let label = link.label
    label = label.replace('&laquo;', '').replace('&raquo;', '').trim()
    if (label === 'Previous') { label = 'Sebelumnya' }
    else if (label === 'Next') { label = 'Selanjutnya' }
    return { ...link, label }
  })
})

const applyFilters = () => {
  router.get(route('admin.input_data_pkl.input_pembimbing'), {
    search: search.value,
    jabatan: selectedJabatan.value === 'all' ? '' : selectedJabatan.value,
    month: selectedMonth.value === 'all' ? '' : selectedMonth.value,
    per_page: Number(perPage.value),
    page: 1,
  }, {
    preserveScroll: true,
    replace: true,
  })
}

const handleSearch = debounce(() => {
  applyFilters()
}, 300)

const handleFilterChange = () => {
  applyFilters()
}

const handlePerPageChange = () => {
  applyFilters()
}

watch(() => props.filters, (newFilters) => {
  search.value = newFilters.search || ''
  selectedJabatan.value = newFilters.jabatan || 'all'
  selectedMonth.value = newFilters.month || 'all'
  perPage.value = Number(newFilters.per_page) || 20
}, { immediate: true, deep: true })

// Unassigned Bengkels handling
const searchUnassigned = ref('')
const filteredUnassignedBengkels = computed(() => {
  const list = props.unassignedBengkels || []
  if (!searchUnassigned.value) return list
  const q = searchUnassigned.value.toLowerCase()
  return list.filter((b) => {
    const matchName = b.nama_bengkel && b.nama_bengkel.toLowerCase().includes(q)
    const matchCode = b.kode_bengkel && b.kode_bengkel.toLowerCase().includes(q)
    const matchKec = b.kecamatan && b.kecamatan.toLowerCase().includes(q)
    const matchDesa = b.desa && b.desa.toLowerCase().includes(q)
    const matchSiswa = b.siswa && b.siswa.some((s) => s.nama.toLowerCase().includes(q))
    return matchName || matchCode || matchKec || matchDesa || matchSiswa
  })
})

// Modal Quick Assign Pembimbing
const isAssignModalActive = ref(false)
const selectedBengkelToAssign = ref(null)
const selectedPembimbingForAssign = ref(null)
const isSubmittingAssign = ref(false)

const openAssignModal = (bengkel) => {
  selectedBengkelToAssign.value = bengkel
  selectedPembimbingForAssign.value = null
  isAssignModalActive.value = true
}

const submitAssignPembimbing = () => {
  if (!selectedBengkelToAssign.value) return
  if (!selectedPembimbingForAssign.value?.id) {
    alert('Silakan pilih guru pembimbing terlebih dahulu.')
    return
  }

  isSubmittingAssign.value = true
  router.put(
    route('admin.input_data_pkl.set_pembimbing', selectedBengkelToAssign.value.id),
    {
      pembimbing_id: Number(selectedPembimbingForAssign.value.id),
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        isAssignModalActive.value = false
        selectedBengkelToAssign.value = null
        selectedPembimbingForAssign.value = null
        isSubmittingAssign.value = false
      },
      onError: () => {
        isSubmittingAssign.value = false
      },
    }
  )
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Input Pembimbing" />
    <SectionMain>
      <div class="relative overflow-hidden rounded-3xl mb-6 border border-slate-200/70 dark:border-slate-700/70">
        <div class="absolute inset-0 bg-[radial-gradient(1100px_circle_at_18%_18%,rgba(34,211,238,0.22),transparent_55%),radial-gradient(900px_circle_at_82%_24%,rgba(99,102,241,0.20),transparent_60%),radial-gradient(900px_circle_at_50%_120%,rgba(34,197,94,0.14),transparent_55%)]" />
        <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(255,255,255,0.70),rgba(255,255,255,0.96))] dark:bg-[linear-gradient(to_bottom,rgba(2,6,23,0.55),rgba(2,6,23,0.92))]" />
        <div class="relative p-6 md:p-8">
          <SectionTitleLineWithButton :icon="mdiHumanMaleBoard" title="Input Pembimbing" main>
            <BaseButtons>
              <BaseButton
                :to="route('admin.input_data_pkl.index')"
                :icon="mdiArrowLeft"
                label="Kembali"
                color="whiteDark"
                rounded-full
                small
              />
              <BaseButton
                :icon="mdiFileExcel"
                label="Excel"
                color="success"
                rounded-full
                small
                @click="exportExcel"
              />
              <BaseButton
                :icon="mdiFilePdfBox"
                label="PDF"
                color="danger"
                rounded-full
                small
                @click="exportPdf"
              />
              <BaseButton
                :icon="mdiRefresh"
                label="Refresh"
                color="info"
                rounded-full
                small
                @click="refreshData"
              />
              <BaseButton
                :to="route('admin.input_data_pkl.input_pembimbing.form')"
                :icon="mdiArrowRightBoldCircleOutline"
                label="Input Pembimbing"
                color="warning"
                rounded-full
                small
                class="shadow-lg hover:shadow-xl transition-all duration-300"
              />
            </BaseButtons>
          </SectionTitleLineWithButton>

          <p class="mt-2 text-sm md:text-base text-slate-600 dark:text-slate-300 max-w-3xl">
            Menu ini menampilkan ringkasan pembimbing beserta data bengkel dan siswa yang sudah maupun belum mendapatkan pembimbing.
          </p>

          <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl p-4 bg-white/70 dark:bg-slate-900/40 border border-slate-200/70 dark:border-slate-700/70">
              <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Total Pembimbing</div>
                <div class="w-9 h-9 rounded-xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 flex items-center justify-center">
                  <BaseIcon :path="mdiHumanMaleBoard" :size="18" />
                </div>
              </div>
              <div class="mt-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                {{ stats.pembimbing_total }}
              </div>
              <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">Guru pembimbing terdaftar</div>
            </div>

            <div class="rounded-2xl p-4 bg-white/70 dark:bg-slate-900/40 border border-slate-200/70 dark:border-slate-700/70">
              <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Bengkel Terisi</div>
                <div class="w-9 h-9 rounded-xl bg-indigo-600 text-white flex items-center justify-center">
                  <BaseIcon :path="mdiOfficeBuildingCog" :size="18" />
                </div>
              </div>
              <div class="mt-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                {{ stats.bengkel_terisi_total }}
              </div>
              <div class="mt-1 text-xs text-indigo-600 dark:text-indigo-400 font-medium">Sudah ada pembimbing</div>
            </div>

            <div class="rounded-2xl p-4 bg-white/70 dark:bg-slate-900/40 border border-slate-200/70 dark:border-slate-700/70">
              <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Siswa Terdata</div>
                <div class="w-9 h-9 rounded-xl bg-emerald-600 text-white flex items-center justify-center">
                  <BaseIcon :path="mdiAccountGroup" :size="18" />
                </div>
              </div>
              <div class="mt-3 text-3xl font-black tracking-tight text-slate-900 dark:text-white">
                {{ stats.siswa_terisi_total }}
              </div>
              <div class="mt-1 text-xs text-emerald-600 dark:text-emerald-400 font-medium">Siswa sudah dibimbing</div>
            </div>

            <!-- 4th Stat Card: Bengkel Belum Ada Pembimbing -->
            <div
              :class="[
                'rounded-2xl p-4 transition-all duration-200 cursor-pointer border',
                stats.bengkel_belum_total > 0
                  ? 'bg-amber-50/80 dark:bg-amber-950/20 border-amber-300 dark:border-amber-700/60 hover:shadow-md hover:border-amber-400'
                  : 'bg-white/70 dark:bg-slate-900/40 border-slate-200/70 dark:border-slate-700/70'
              ]"
              @click="activeTab = 'unassigned'"
            >
              <div class="flex items-center justify-between">
                <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Belum Dibimbing</div>
                <div
                  :class="[
                    'w-9 h-9 rounded-xl flex items-center justify-center text-white',
                    stats.bengkel_belum_total > 0 ? 'bg-amber-500' : 'bg-slate-400'
                  ]"
                >
                  <BaseIcon :path="mdiAlertCircleOutline" :size="18" />
                </div>
              </div>
              <div
                :class="[
                  'mt-3 text-3xl font-black tracking-tight',
                  stats.bengkel_belum_total > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-900 dark:text-white'
                ]"
              >
                {{ stats.bengkel_belum_total }}
              </div>
              <div class="mt-1 text-xs text-amber-700 dark:text-amber-300 font-medium flex items-center justify-between">
                <span>{{ stats.siswa_belum_total }} siswa menunggu</span>
                <span class="underline">Klik lihat</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <CardBox class="shadow-xl border-0 rounded-2xl overflow-hidden">
        <!-- Tab Navigation Header -->
        <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-white/60 dark:bg-slate-900/40">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
              <button
                type="button"
                :class="[
                  'px-4 py-2 text-sm font-bold rounded-xl transition-all duration-200 flex items-center gap-2',
                  activeTab === 'pembimbing'
                    ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900 shadow-md'
                    : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
                ]"
                @click="activeTab = 'pembimbing'"
              >
                <BaseIcon :path="mdiHumanMaleBoard" :size="18" />
                <span>Daftar Pembimbing</span>
                <span
                  class="text-xs px-2 py-0.5 rounded-full font-bold"
                  :class="activeTab === 'pembimbing' ? 'bg-slate-800 dark:bg-slate-200 text-white dark:text-slate-900' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300'"
                >
                  {{ props.rows.total || pembimbingData.length }}
                </span>
              </button>

              <button
                type="button"
                :class="[
                  'px-4 py-2 text-sm font-bold rounded-xl transition-all duration-200 flex items-center gap-2',
                  activeTab === 'unassigned'
                    ? 'bg-amber-500 text-white shadow-md shadow-amber-500/20'
                    : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800'
                ]"
                @click="activeTab = 'unassigned'"
              >
                <BaseIcon :path="mdiAlertCircleOutline" :size="18" />
                <span>Bengkel Belum Ada Pembimbing</span>
                <span
                  class="text-xs px-2 py-0.5 rounded-full font-bold"
                  :class="stats.bengkel_belum_total > 0
                    ? (activeTab === 'unassigned' ? 'bg-white text-amber-600' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/60 dark:text-amber-300')
                    : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300'"
                >
                  {{ stats.bengkel_belum_total }}
                </span>
              </button>
            </div>

            <div class="text-xs text-slate-500 dark:text-slate-400">
              <span v-if="activeTab === 'pembimbing'">Ringkasan pembimbing beserta kuota bengkel dan siswa</span>
              <span v-else>Daftar bengkel yang sudah memiliki siswa namun belum ditentukan guru pembimbingnya</span>
            </div>
          </div>
        </div>

        <!-- TAB 1: DAFTAR PEMBIMBING -->
        <div v-show="activeTab === 'pembimbing'" class="p-5">
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
            <div class="md:col-span-2">
              <FormField label="Cari Pembimbing">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <BaseIcon :path="mdiMagnify" :size="16" class="text-slate-400" />
                  </div>
                  <input
                    v-model="search"
                    type="text"
                    placeholder="Cari nama pembimbing..."
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500/60 transition-all"
                    @input="handleSearch"
                  />
                </div>
              </FormField>
            </div>
            <div>
              <FormField label="Bulan">
                <select
                  v-model="selectedMonth"
                  class="w-full px-4 py-2 text-sm rounded-xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500/60 transition-all appearance-none"
                  @change="handleFilterChange"
                >
                  <option v-for="opt in monthOptions" :key="opt.id" :value="opt.value">{{ opt.label }}</option>
                </select>
              </FormField>
            </div>
            <div>
              <FormField label="Jabatan">
                <select
                  v-model="selectedJabatan"
                  class="w-full px-4 py-2 text-sm rounded-xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500/60 transition-all appearance-none"
                  @change="handleFilterChange"
                >
                  <option v-for="opt in jabatanFilterOptions" :key="opt.id" :value="opt.value">{{ opt.label }}</option>
                </select>
              </FormField>
            </div>
            <div>
              <FormField label="Tampilkan">
                <select
                  v-model="perPage"
                  class="w-full px-4 py-2 text-sm rounded-xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500/60 transition-all appearance-none"
                  @change="handlePerPageChange"
                >
                  <option v-for="value in perPageOptions" :key="value" :value="value">{{ value }}</option>
                </select>
              </FormField>
            </div>
          </div>

          <div v-if="pembimbingData.length === 0" class="py-10 text-center text-slate-500 dark:text-slate-400">
            Belum ada data pembimbing.
          </div>

          <div v-else>
            <div class="overflow-auto">
              <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                  <tr>
                    <th class="px-4 py-3 text-center w-12">No</th>
                    <th class="px-4 py-3">Pembimbing</th>
                    <th class="px-4 py-3">Jabatan</th>
                    <th class="px-4 py-3 text-center">Bengkel</th>
                    <th class="px-4 py-3 text-center">Siswa</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="(row, index) in pembimbingData"
                    :key="row.id"
                    class="border-b border-slate-100 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/40 transition-colors"
                  >
                    <td class="px-4 py-3 text-center text-slate-500 text-sm">
                      {{ (props.rows.current_page - 1) * props.rows.per_page + index + 1 }}
                    </td>
                    <td class="px-4 py-3">
                      <div class="font-semibold text-slate-900 dark:text-white">
                        {{ row.nama_guru }}
                      </div>
                    </td>
                    <td class="px-4 py-3">
                      <span
                        class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/30 text-slate-700 dark:text-slate-200"
                      >
                        {{ row.jabatan || '-' }}
                      </span>
                    </td>
                    <td class="px-4 py-3 text-center font-extrabold text-slate-900 dark:text-white">
                      {{ row.bengkel_count }}
                    </td>
                    <td class="px-4 py-3 text-center font-extrabold text-slate-900 dark:text-white">
                      {{ row.siswa_count }}
                    </td>
                    <td class="px-4 py-3 text-center">
                      <BaseButton
                        :to="route('admin.input_data_pkl.input_pembimbing.show', row.id)"
                        label="Lihat"
                        color="info"
                        rounded-full
                        small
                      />
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="pembimbingLinks.length > 3" class="mt-4 p-4 border-t border-slate-100 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30 rounded-b-2xl">
              <BaseLevel>
                <BaseButtons>
                  <BaseButton
                    v-for="(link, key) in pembimbingLinks"
                    :key="key"
                    :active="link.active"
                    :label="link.label"
                    :color="link.active ? 'info' : 'whiteDark'"
                    small
                    :disabled="!link.url"
                    @click="link.url && router.visit(link.url)"
                  />
                </BaseButtons>
                <small class="text-slate-500 font-medium">
                  Halaman {{ props.rows.current_page }} dari {{ props.rows.last_page }}
                </small>
              </BaseLevel>
            </div>
            <div v-else class="mt-4 p-4 text-center text-sm text-slate-500">
              Menampilkan {{ props.rows.from || 0 }} - {{ props.rows.to || 0 }} dari {{ props.rows.total || 0 }} data
            </div>
          </div>
        </div>

        <!-- TAB 2: BENGKEL BELUM ADA PEMBIMBING -->
        <div v-show="activeTab === 'unassigned'" class="p-5">
          <div class="mb-4">
            <div class="w-full sm:w-80">
              <FormField label="Cari Bengkel / Siswa">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <BaseIcon :path="mdiMagnify" :size="16" class="text-slate-400" />
                  </div>
                  <input
                    v-model="searchUnassigned"
                    type="text"
                    placeholder="Cari nama bengkel, lokasi, atau siswa..."
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40 focus:border-amber-500/60 transition-all"
                  />
                </div>
              </FormField>
            </div>
          </div>

          <div v-if="filteredUnassignedBengkels.length === 0" class="py-12 text-center">
            <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-100 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-3">
              <BaseIcon :path="mdiCheckCircle" :size="24" />
            </div>
            <div class="text-base font-bold text-slate-800 dark:text-slate-200">
              {{ (props.unassignedBengkels || []).length === 0 ? 'Semua Bengkel Sudah Memiliki Pembimbing!' : 'Tidak ada bengkel yang cocok dengan pencarian.' }}
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1">
              {{ (props.unassignedBengkels || []).length === 0 ? 'Tidak ada bengkel bersiswa yang belum mendapatkan guru pembimbing.' : 'Coba ubah kata kunci pencarian Anda.' }}
            </div>
          </div>

          <div v-else class="overflow-auto">
            <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
              <thead class="text-xs uppercase bg-amber-50/60 dark:bg-amber-950/30 text-amber-900 dark:text-amber-200 border-b border-amber-200/60 dark:border-amber-800/40">
                <tr>
                  <th class="px-4 py-3 text-center w-12">No</th>
                  <th class="px-4 py-3">Nama Bengkel</th>
                  <th class="px-4 py-3">Lokasi / Alamat</th>
                  <th class="px-4 py-3 text-center w-28">Jumlah Siswa</th>
                  <th class="px-4 py-3">Daftar Siswa</th>
                  <th class="px-4 py-3 text-center w-40">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(bengkel, index) in filteredUnassignedBengkels"
                  :key="bengkel.id"
                  class="border-b border-slate-100 dark:border-slate-800 hover:bg-amber-50/20 dark:hover:bg-amber-950/10 transition-colors"
                >
                  <td class="px-4 py-3 text-center text-slate-500 text-sm">
                    {{ index + 1 }}
                  </td>
                  <td class="px-4 py-3">
                    <div class="font-bold text-slate-900 dark:text-white">
                      {{ bengkel.nama_bengkel }}
                    </div>
                    <div v-if="bengkel.kode_bengkel" class="mt-0.5">
                      <span class="inline-block text-[11px] font-mono px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                        {{ bengkel.kode_bengkel }}
                      </span>
                    </div>
                  </td>
                  <td class="px-4 py-3 align-top">
                    <div class="flex items-start gap-1.5 text-xs text-slate-600 dark:text-slate-300">
                      <BaseIcon :path="mdiMapMarker" :size="14" class="text-slate-400 shrink-0 mt-0.5" />
                      <span>{{ [bengkel.dusun, bengkel.desa, bengkel.kecamatan].filter(Boolean).join(', ') || 'Lokasi tidak tersedia' }}</span>
                    </div>
                    <div v-if="bengkel.google_maps_link" class="mt-2">
                      <a
                        :href="ensureExternalUrl(bengkel.google_maps_link)"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition-colors shadow-sm"
                      >
                        <BaseIcon :path="mdiGoogleMaps" :size="14" class="text-emerald-600 dark:text-emerald-400" />
                        <span>Buka Maps</span>
                      </a>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-center align-top">
                    <span class="inline-flex items-center justify-center px-2.5 py-1 text-xs font-black rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300">
                      {{ bengkel.siswa_count }} Siswa
                    </span>
                  </td>
                  <td class="px-4 py-3 align-top">
                    <div class="space-y-1.5">
                      <div
                        v-for="(s, sIdx) in bengkel.siswa"
                        :key="s.id"
                        class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-200"
                      >
                        <span class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 flex items-center justify-center text-[10px] font-bold shrink-0 border border-slate-200 dark:border-slate-700">
                          {{ sIdx + 1 }}
                        </span>
                        <span class="font-semibold text-slate-900 dark:text-white">{{ s.nama }}</span>
                        <span class="text-[11px] text-slate-400 font-medium shrink-0">({{ s.kelas }})</span>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 py-3 text-center align-top">
                    <BaseButtons type="justify-center" no-wrap>
                      <BaseButton
                        label="Tentukan Pembimbing"
                        color="warning"
                        rounded-full
                        small
                        @click="openAssignModal(bengkel)"
                      />
                    </BaseButtons>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </CardBox>

      <!-- MODAL QUICK ASSIGN PEMBIMBING -->
      <CardBoxModal
        v-model="isAssignModalActive"
        title="Tentukan Guru Pembimbing"
        button="info"
        button-label="Simpan Pembimbing"
        has-cancel
        modal-class="w-11/12 md:w-3/5 lg:w-1/2 xl:w-2/5"
        @confirm="submitAssignPembimbing"
      >
        <div v-if="selectedBengkelToAssign" class="space-y-4">
          <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/70 dark:border-slate-700/70 text-sm">
            <div class="font-semibold text-slate-800 dark:text-slate-100 flex items-center justify-between">
              <span>{{ selectedBengkelToAssign.nama_bengkel }}</span>
              <span v-if="selectedBengkelToAssign.kode_bengkel" class="text-xs px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-900/50 text-indigo-600 dark:text-indigo-300 font-mono">
                {{ selectedBengkelToAssign.kode_bengkel }}
              </span>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-start gap-1">
              <BaseIcon :path="mdiMapMarker" :size="13" class="text-slate-400 shrink-0 mt-0.5" />
              <span>{{ [selectedBengkelToAssign.dusun, selectedBengkelToAssign.desa, selectedBengkelToAssign.kecamatan].filter(Boolean).join(', ') || 'Lokasi tidak tersedia' }}</span>
            </div>
            <div v-if="selectedBengkelToAssign.google_maps_link" class="mt-2">
              <a
                :href="ensureExternalUrl(selectedBengkelToAssign.google_maps_link)"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 transition-colors shadow-sm"
              >
                <BaseIcon :path="mdiGoogleMaps" :size="13" class="text-emerald-600 dark:text-emerald-400" />
                <span>Buka di Google Maps</span>
              </a>
            </div>
            <div class="mt-3 pt-2.5 border-t border-slate-200/50 dark:border-slate-700/50 text-xs">
              <span class="font-semibold text-slate-700 dark:text-slate-200 block mb-1.5">
                Daftar {{ selectedBengkelToAssign.siswa_count }} Siswa:
              </span>
              <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1">
                <div
                  v-for="(s, sIdx) in selectedBengkelToAssign.siswa"
                  :key="s.id"
                  class="flex items-center gap-2 text-slate-700 dark:text-slate-200 text-xs"
                >
                  <span class="w-4 h-4 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center text-[10px] font-bold shrink-0">
                    {{ sIdx + 1 }}
                  </span>
                  <span class="font-medium text-slate-900 dark:text-white">{{ s.nama }}</span>
                  <span class="text-slate-400 text-[11px]">({{ s.kelas }})</span>
                </div>
              </div>
            </div>
          </div>

          <FormField label="Pilih Guru Pembimbing">
            <FormControlSearchable
              v-model="selectedPembimbingForAssign"
              :options="props.pembimbingOptions"
              :icon="mdiHumanMaleBoard"
              placeholder="Cari nama guru pembimbing..."
            />
          </FormField>

          <div v-if="selectedPembimbingForAssign" class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-xs text-emerald-800 dark:text-emerald-300 flex items-center gap-2">
            <BaseIcon :path="mdiCheckCircle" :size="16" />
            <span>Guru terpilih: <strong>{{ selectedPembimbingForAssign.label || selectedPembimbingForAssign.nama_guru }}</strong></span>
          </div>

          <div class="p-3 rounded-xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 text-xs text-amber-800 dark:text-amber-300">
            <strong>Perhatian:</strong> Seluruh {{ selectedBengkelToAssign.siswa_count }} siswa di bengkel ini akan langsung terhubung ke guru pembimbing yang Anda pilih. Satu bengkel tidak boleh memiliki 2 pembimbing berbeda.
          </div>
        </div>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template>
