<script setup>
import axios from 'axios'
import { Head, router } from '@inertiajs/vue3'
import {
  mdiTable,
  mdiFileExcel,
  mdiFilePdfBox,
  mdiAccountTie,
  mdiWrench,
  mdiAccountSchool,
  mdiCalendarMonth,
  mdiCalendar,
  mdiEye
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import { ref, watch, computed } from 'vue'
import Modal from '@/Components/Modal.vue'
import PieChart from '@admin/components/Charts/PieChart.vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  data: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const totalPembimbing = computed(() => props.data.length)
const totalBengkel = computed(() => props.data.reduce((sum, item) => sum + item.jumlah_bengkel, 0))
const totalSiswa = computed(() => props.data.reduce((sum, item) => sum + item.jumlah_siswa, 0))

const modeOptions = [
  { id: '6_bulan', label: 'Rekap 6 Bulan' },
  { id: 'bulanan', label: 'Rekap Bulanan' },
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

const yearOptions = []
const currentYear = new Date().getFullYear()
for (let i = currentYear - 2; i <= currentYear + 1; i++) {
  yearOptions.push({ id: i, label: String(i) })
}

const selectedMode = ref(props.filters?.mode || '6_bulan')

const initialMonthId = Number(props.filters?.month || new Date().getMonth() + 1)
const selectedMonth = ref(monthOptions.find(m => m.id === initialMonthId) || monthOptions[0])

const initialYearId = Number(props.filters?.year || new Date().getFullYear())
const selectedYear = ref(yearOptions.find(y => y.id === initialYearId) || yearOptions[0])

const periodeLabel = computed(() => {
  if (selectedMode.value !== 'bulanan') return 'Periode 6 Bulan'
  const mId = selectedMonth.value?.id ?? selectedMonth.value
  const yId = selectedYear.value?.id ?? selectedYear.value
  const month = monthOptions.find(m => m.id == mId)?.label || '-'
  const year = yearOptions.find(y => y.id == yId)?.label || yId
  return `${month} ${year}`
})

const monitoringHeader = computed(() =>
  selectedMode.value === 'bulanan' ? 'Monitoring (Bulan)' : 'Monitoring (6 Bulan)',
)

const monitoringStatus = (item) => {
  const done = item.monitoring ?? 0
  const target = item.target ?? 0
  if (target > 0 && done >= target) return 'complete'
  if (done > 0) return 'progress'
  return 'empty'
}

const monitoringBadgeClass = (item) => {
  const status = monitoringStatus(item)
  if (status === 'complete')
    return 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400'
  if (status === 'progress')
    return 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'
  return 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400'
}

const applyFilters = () => {
  const params = { mode: selectedMode.value }
  if (selectedMode.value === 'bulanan') {
    params.month = selectedMonth.value?.id ?? selectedMonth.value
    params.year = selectedYear.value?.id ?? selectedYear.value
  }

  router.get(route('admin.rekap_data_pkl.index'), params, {
    preserveState: true,
    preserveScroll: true,
    replace: true,
  })
}

watch([selectedMode, selectedMonth, selectedYear], () => {
  debouncedApplyFilters()
})

const debouncedApplyFilters = debounce(() => {
  applyFilters()
}, 250)

const exportExcel = () => {
  const params = { mode: selectedMode.value }
  if (selectedMode.value === 'bulanan') {
    params.month = selectedMonth.value?.id ?? selectedMonth.value
    params.year = selectedYear.value?.id ?? selectedYear.value
  }
  window.open(route('admin.rekap_data_pkl.export_excel', params), '_blank')
}

const exportPdf = () => {
  const params = { mode: selectedMode.value }
  if (selectedMode.value === 'bulanan') {
    params.month = selectedMonth.value?.id ?? selectedMonth.value
    params.year = selectedYear.value?.id ?? selectedYear.value
  }
  window.open(route('admin.rekap_data_pkl.export_pdf', params), '_blank')
}

const showDetail = ref(false)
const detailTitle = ref('')
const chartData = ref({ labels: [], datasets: [{ data: [] }] })
const detailRows = ref([])
const isLoadingDetail = ref(false)

const openDetail = async (item) => {
  detailTitle.value = `Detail Monitoring — ${item.nama_pembimbing}`
  showDetail.value = true
  isLoadingDetail.value = true
  detailRows.value = []
  chartData.value = { labels: [], datasets: [{ data: [] }] }

  if (!item.pembimbing_id) {
    isLoadingDetail.value = false
    return
  }

  try {
    const params = { mode: selectedMode.value }
    if (selectedMode.value === 'bulanan') {
      params.month = selectedMonth.value?.id ?? selectedMonth.value
      params.year = selectedYear.value?.id ?? selectedYear.value
    }

    const { data } = await axios.get(route('admin.rekap_data_pkl.detail', item.pembimbing_id), {
      params,
    })

    const rows = (data?.data?.monitoring_detail || []).sort((a, b) => b.kunjungan_unik - a.kunjungan_unik)
    detailRows.value = rows
    chartData.value = {
      labels: rows.map(r => r.bengkel_nama),
      datasets: [
        {
          data: rows.map(r => r.kunjungan_unik),
        }
      ]
    }
  } catch (error) {
    detailRows.value = []
    chartData.value = { labels: [], datasets: [{ data: [] }] }
  } finally {
    isLoadingDetail.value = false
  }
}

const closeDetail = () => {
  showDetail.value = false
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Data Pembimbing PKL" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiTable"
        title="Data Pembimbing PKL"
        main
      >
      </SectionTitleLineWithButton>

      <!-- Modern 2026 Filter Section -->
      <div class="mb-8">
        <!-- Tabs for Mode -->
        <div class="flex justify-center mb-6">
          <div class="inline-flex bg-slate-100 dark:bg-slate-800 rounded-full p-1 shadow-inner">
            <button
              @click="selectedMode = '6_bulan'"
              :class="[
                'px-8 py-2.5 rounded-full text-sm font-semibold transition-all duration-300 ease-in-out',
                selectedMode === '6_bulan'
                  ? 'bg-indigo-600 text-white shadow-md transform scale-105'
                  : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-200 dark:hover:bg-slate-700'
              ]"
            >
              Rekap 6 Bulan
            </button>
            <button
              @click="selectedMode = 'bulanan'"
              :class="[
                'px-8 py-2.5 rounded-full text-sm font-semibold transition-all duration-300 ease-in-out',
                selectedMode === 'bulanan'
                  ? 'bg-indigo-600 text-white shadow-md transform scale-105'
                  : 'text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-200 dark:hover:bg-slate-700'
              ]"
            >
              Rekap Bulanan
            </button>
          </div>
        </div>

        <!-- Animated Filter Bar for Bulanan -->
        <div
          v-if="selectedMode === 'bulanan'"
          class="transform transition-all duration-500 ease-in-out origin-top"
          :class="selectedMode === 'bulanan' ? 'scale-y-100 opacity-100' : 'scale-y-0 opacity-0 h-0 overflow-hidden'"
        >
          <CardBox class="shadow-lg border-none bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl ring-1 ring-slate-200 dark:ring-slate-700 rounded-2xl">
            <div class="p-4 flex flex-col md:flex-row items-center justify-center gap-6">
              <div class="w-full md:w-1/3">
                <FormField label="Pilih Bulan" class="mb-0">
                  <FormControl v-model="selectedMonth" :options="monthOptions" type="select" :icon="mdiCalendarMonth" />
                </FormField>
              </div>
              <div class="w-full md:w-1/3">
                <FormField label="Pilih Tahun" class="mb-0">
                  <FormControl v-model="selectedYear" :options="yearOptions" type="select" :icon="mdiCalendar" />
                </FormField>
              </div>
            </div>
          </CardBox>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <CardBox class="bg-gradient-to-br from-blue-500 to-indigo-600 text-white shadow-xl shadow-blue-500/20 transform transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-blue-500/40 rounded-2xl border-none">
          <div class="flex items-center justify-between p-2">
            <div>
              <h3 class="text-sm font-medium text-blue-100 uppercase tracking-wider mb-2">Total Pembimbing</h3>
              <p class="text-4xl font-black tracking-tight">{{ totalPembimbing }}</p>
            </div>
            <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-sm">
              <BaseIcon :path="mdiAccountTie" size="36" class="text-white" />
            </div>
          </div>
        </CardBox>

        <CardBox class="bg-gradient-to-br from-emerald-400 to-teal-600 text-white shadow-xl shadow-teal-500/20 transform transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-teal-500/40 rounded-2xl border-none">
          <div class="flex items-center justify-between p-2">
            <div>
              <h3 class="text-sm font-medium text-teal-100 uppercase tracking-wider mb-2">Total Bengkel</h3>
              <p class="text-4xl font-black tracking-tight">{{ totalBengkel }}</p>
            </div>
            <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-sm">
              <BaseIcon :path="mdiWrench" size="36" class="text-white" />
            </div>
          </div>
        </CardBox>

        <CardBox class="bg-gradient-to-br from-purple-500 to-fuchsia-600 text-white shadow-xl shadow-purple-500/20 transform transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl hover:shadow-purple-500/40 rounded-2xl border-none">
          <div class="flex items-center justify-between p-2">
            <div>
              <h3 class="text-sm font-medium text-purple-100 uppercase tracking-wider mb-2">Total Siswa</h3>
              <p class="text-4xl font-black tracking-tight">{{ totalSiswa }}</p>
            </div>
            <div class="p-4 bg-white/20 rounded-2xl backdrop-blur-sm">
              <BaseIcon :path="mdiAccountSchool" size="36" class="text-white" />
            </div>
          </div>
        </CardBox>
      </div>

      <!-- Actions -->
      <div class="mb-6 flex flex-col md:flex-row items-center justify-between gap-4 bg-white dark:bg-slate-800 p-4 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
        <div class="flex items-center gap-3">
          <div class="p-2 bg-indigo-100 dark:bg-indigo-900/30 rounded-lg">
            <BaseIcon :path="mdiTable" size="24" class="text-indigo-600 dark:text-indigo-400" />
          </div>
          <div class="text-lg font-bold text-slate-800 dark:text-slate-100">
            Rekap: <span class="text-indigo-600 dark:text-indigo-400">{{ periodeLabel }}</span>
          </div>
        </div>

        <div class="flex flex-wrap gap-3 justify-end w-full md:w-auto">
          <BaseButton
            :icon="mdiFileExcel"
            label="Export Excel"
            color="success"
            rounded-full
            class="w-full md:w-auto shadow-md hover:shadow-lg transition-shadow"
            @click="exportExcel"
          />
          <BaseButton
            :icon="mdiFilePdfBox"
            label="Export PDF"
            color="danger"
            rounded-full
            class="w-full md:w-auto shadow-md hover:shadow-lg transition-shadow"
            @click="exportPdf"
          />
        </div>
      </div>

      <CardBox has-table class="shadow-xl rounded-2xl overflow-hidden border-none">
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
            <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 border-b border-slate-200 dark:border-slate-700">
              <tr>
                <th scope="col" class="px-6 py-4 font-bold tracking-wider">No</th>
                <th scope="col" class="px-6 py-4 font-bold tracking-wider">Nama Pembimbing</th>
                <th scope="col" class="px-6 py-4 text-center font-bold tracking-wider">Jumlah Bengkel</th>
                <th scope="col" class="px-6 py-4 text-center font-bold tracking-wider">Jumlah Siswa</th>
                <th scope="col" class="px-6 py-4 text-center font-bold tracking-wider">{{ monitoringHeader }}</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
              <tr
                v-for="(item, index) in data"
                :key="index"
                class="bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors duration-200"
              >
                <td class="px-6 py-4 font-semibold text-slate-500 dark:text-slate-400">{{ index + 1 }}</td>
                <td class="px-6 py-4 font-medium text-slate-800 dark:text-slate-100 whitespace-nowrap">
                  {{ item.nama_pembimbing }}
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center justify-center px-3 py-1 text-xs font-bold leading-none text-emerald-700 bg-emerald-100 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-full">
                    {{ item.jumlah_bengkel }}
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                  <span class="inline-flex items-center justify-center px-3 py-1 text-xs font-bold leading-none text-purple-700 bg-purple-100 dark:bg-purple-900/30 dark:text-purple-400 rounded-full">
                    {{ item.jumlah_siswa }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <div class="flex items-center justify-center gap-3">
                    <span
                      class="inline-flex items-center justify-center px-3 py-1 text-xs font-bold leading-none rounded-full"
                      :class="monitoringBadgeClass(item)"
                    >
                      {{ item.monitoring ?? 0 }} / {{ item.target ?? 0 }}
                    </span>
                    <BaseButton
                      small
                      rounded-full
                      :icon="mdiEye"
                      color="info"
                      @click="openDetail(item)"
                      label="Detail"
                      class="!py-1 !px-3 shadow-sm"
                    />
                  </div>
                </td>
              </tr>
              <tr v-if="data.length === 0">
                <td colspan="5" class="px-6 py-16 text-center text-slate-500 dark:text-slate-400">
                  <div class="flex flex-col items-center justify-center">
                    <div class="p-4 bg-slate-100 dark:bg-slate-800 rounded-full mb-3">
                      <BaseIcon :path="mdiTable" size="48" class="text-slate-400 dark:text-slate-500" />
                    </div>
                    <p class="text-lg font-medium text-slate-700 dark:text-slate-300">Tidak ada data untuk periode ini.</p>
                    <p class="text-sm mt-1">Silakan ubah filter di atas.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardBox>

      <Modal :show="showDetail" @close="closeDetail" maxWidth="4xl">
        <div class="p-6">
          <div class="flex items-start justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
              {{ detailTitle }}
            </h3>
            <BaseButton color="lightDark" label="Tutup" @click="closeDetail" />
          </div>
          <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
            <div class="h-64 md:col-span-5 lg:col-span-4">
              <PieChart :data="chartData" subtext="Kunjungan" variant="mutasi" />
            </div>
            <div class="overflow-auto md:col-span-7 lg:col-span-8">
              <div v-if="isLoadingDetail" class="py-8 text-center text-gray-500">
                Memuat detail monitoring...
              </div>
              <table class="w-full text-sm text-left">
                <thead class="text-xs uppercase text-gray-600 dark:text-gray-300">
                  <tr>
                    <th class="py-2">Bengkel</th>
                    <th class="py-2">Alamat</th>
                    <th class="py-2">Tanggal Ditambahkan</th>
                    <th class="py-2 text-right">Target</th>
                    <th class="py-2 text-right">Kunjungan</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="row in detailRows" :key="row.bengkel_id" class="border-b border-gray-100 dark:border-slate-700">
                    <td class="py-2 pr-4 font-medium">{{ row.bengkel_nama }}</td>
                    <td class="py-2 pr-4 text-gray-500 text-xs">{{ [row.desa, row.kecamatan].filter(Boolean).join(', ') || '-' }}</td>
                    <td class="py-2 pr-4 text-gray-500 text-xs whitespace-nowrap">{{ row.tanggal_ditambahkan || '-' }}</td>
                    <td class="py-2 text-right text-gray-500">{{ row.target_kunjungan }}</td>
                    <td class="py-2 text-right font-semibold">{{ row.kunjungan_unik }}</td>
                  </tr>
                  <tr v-if="!isLoadingDetail && detailRows.length === 0">
                    <td colspan="5" class="py-4 text-center text-gray-500">Belum ada monitoring pada periode PKL</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </Modal>
    </SectionMain>
  </LayoutAuthenticated>
</template>
