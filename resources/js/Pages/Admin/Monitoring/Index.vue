<script setup>
import { Head, useForm, router, usePage, Link } from '@inertiajs/vue3'
import { mdiMonitor, mdiMagnify, mdiEye, mdiCalendar, mdiAccountTie, mdiOfficeBuilding, mdiClipboardText, mdiTrashCan, mdiCheck, mdiAlertCircle, mdiFileExcel, mdiFilePdfBox, mdiFilterVariant, mdiFilterRemove, mdiCalendarClock, mdiRefresh, mdiArrowRight } from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import CardBox from '@admin/components/CardBox.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import CardBoxWidget from '@admin/components/CardBoxWidget.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import { computed, ref, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  monitorings: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  totalMonitoring: {
    type: Number,
    default: 0,
  },
  totalBengkel: {
    type: Number,
    default: 0,
  },
  totalPembimbing: {
    type: Number,
    default: 0,
  },
  pembimbingOptions: {
    type: Array,
    default: () => [],
  },
  bengkelOptions: {
    type: Array,
    default: () => [],
  },
  periodSetting: {
    type: Object,
    default: () => ({}),
  },
})

const page = usePage()
const canDeleteMonitoring = computed(() => {
  const roles = page.props.auth?.user?.roles
  if (!Array.isArray(roles)) return false
  return roles.includes('admin') || roles.includes('super-admin')
})

const monitoringData = computed(() =>
  props.monitorings && Array.isArray(props.monitorings.data) ? props.monitorings.data : [],
)

const monitoringLinks = computed(() =>
  props.monitorings && Array.isArray(props.monitorings.links) ? props.monitorings.links : [],
)

const filterForm = useForm({
  search: props.filters?.search || '',
  per_page: Number(props.filters?.per_page || 10),
  pembimbing_id: props.filters?.pembimbing_id || '',
  bengkel_id: props.filters?.bengkel_id || '',
  status: props.filters?.status || '',
  session: props.filters?.session || '',
  bulan: props.filters?.bulan || '',
})

const search = () => {
  filterForm.get(route('admin.monitoring.index'), {
    preserveState: true,
    preserveScroll: true,
  })
}

const perPageOptions = [
  { id: 10, label: '10' },
  { id: 20, label: '20' },
  { id: 50, label: '50' },
  { id: 100, label: '100' },
  { id: 250, label: '250' },
  { id: 500, label: '500' },
]

const statusOptions = [
  { id: '', label: 'Semua Status' },
  { id: 'pelanggaran_menunggu', label: 'Pelanggaran Belum Diterima' },
  { id: 'pelanggaran', label: 'Ada Pelanggaran' },
  { id: 'diterima', label: 'Laporan Diterima' },
  { id: 'menunggu', label: 'Menunggu' },
]

const sessionOptions = [
  { id: '', label: 'Semua Sesi' },
  { id: '1', label: 'Monitoring 1' },
  { id: '2', label: 'Monitoring 2' },
]

const bulanOptions = computed(() => {
  const list = [{ id: '', label: 'Semua Bulan' }]
  if (props.periodSetting?.monthly_periods && Array.isArray(props.periodSetting.monthly_periods)) {
    props.periodSetting.monthly_periods.forEach((p) => {
      list.push({
        id: String(p.month),
        label: p.month_name || `Bulan ${p.month}`,
      })
    })
  }
  return list
})

watch(
  () => filterForm.search,
  () => {
    search()
  },
)

watch(
  () => filterForm.per_page,
  () => {
    search()
  },
)

watch(
  () => filterForm.pembimbing_id,
  () => {
    search()
  },
)

watch(
  () => filterForm.bengkel_id,
  () => {
    search()
  },
)

watch(
  () => filterForm.status,
  () => {
    search()
  },
)

watch(
  () => filterForm.session,
  () => {
    search()
  },
)

watch(
  () => filterForm.bulan,
  () => {
    search()
  },
)

const clearFilters = () => {
  filterForm.search = ''
  filterForm.pembimbing_id = ''
  filterForm.bengkel_id = ''
  filterForm.status = ''
  filterForm.session = ''
  filterForm.bulan = ''
  search()
}

const hasActiveFilters = computed(() => {
  return (
    filterForm.pembimbing_id ||
    filterForm.bengkel_id ||
    filterForm.status ||
    filterForm.session ||
    filterForm.bulan
  )
})

const isModalActive = ref(false)
const selectedMonitoring = ref(null)
const isLoadingDetails = ref(false)
const deleteForm = useForm({})

const showDetails = async (monitoring) => {
  isModalActive.value = true
  isLoadingDetails.value = true
  selectedMonitoring.value = null

  try {
    const { data } = await axios.get(route('admin.monitoring.show', monitoring.id))
    const resMonitoring = data?.monitoring || monitoring
    resMonitoring.session_display = resMonitoring.session_display || monitoring.session_display || monitoring.session || 1
    resMonitoring.bulan_name = resMonitoring.bulan_name || monitoring.bulan_name || ''
    selectedMonitoring.value = resMonitoring
  } finally {
    isLoadingDetails.value = false
  }
}

const deleteMonitoring = (monitoringId) => {
  if (!canDeleteMonitoring.value) return
  if (!confirm('Yakin ingin menghapus data monitoring ini?')) return
  deleteForm.delete(route('admin.monitoring.destroy', monitoringId), {
    preserveScroll: true,
  })
}

const terimaLaporan = (monitoringId) => {
  if (!confirm('Terima laporan monitoring ini?')) return
  router.put(route('admin.monitoring.terima', monitoringId), {}, {
    preserveScroll: true,
  })
}

const terimaSemuaPelanggaran = () => {
  const count = Number(page.props?.notifications?.pelanggaran_pending_count || 0)
  if (!confirm(`Terima semua ${count} laporan monitoring dengan pelanggaran ini sekaligus?`)) return
  router.put(route('admin.monitoring.terima-semua-pelanggaran'), {}, {
    preserveScroll: true,
  })
}

const filterPelanggaranMenunggu = () => {
  filterForm.status = 'pelanggaran_menunggu'
  search()
}

const isSavingDetails = ref(false)

const saveDetails = async () => {
  if (!selectedMonitoring.value) return

  isSavingDetails.value = true
  try {
    const payload = {
      details: selectedMonitoring.value.details.map((d) => ({
        id: d.id,
        is_pelanggaran: d.is_pelanggaran,
        catatan: d.catatan,
      })),
    }
    await axios.put(
      route('admin.monitoring.update-details', selectedMonitoring.value.id),
      payload
    )

    router.reload({ preserveScroll: true })
    isModalActive.value = false
    alert('Detail evaluasi monitoring berhasil diperbarui.')
  } catch (error) {
    console.error(error)
    alert(
      'Gagal memperbarui detail: ' +
        (error.response?.data?.message || error.message)
    )
  } finally {
    isSavingDetails.value = false
  }
}

watch(
  () => selectedMonitoring.value?.details,
  (details) => {
    if (details) {
      details.forEach((d) => {
        if (!d.is_pelanggaran && d.catatan) {
          d.catatan = ''
        }
      })
    }
  },
  { deep: true }
)

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(date)
}


</script>

<template>
  <LayoutAuthenticated>
    <Head title="Monitoring PKL" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiMonitor" title="Monitoring PKL" main />

      <!-- Banner Ringkasan Jadwal Periode PKL Sekolah -->
      <div class="mb-6 bg-white dark:bg-slate-900/90 rounded-2xl p-4 sm:p-5 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow transition-shadow duration-300">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <!-- Left: Info Identitas Jadwal -->
          <div class="flex items-start sm:items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-gradient-to-tr from-indigo-600 to-indigo-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-indigo-500/20">
              <BaseIcon :path="mdiCalendarClock" w="w-6" h="h-6" />
            </div>
            <div>
              <div class="flex items-center gap-2.5 flex-wrap">
                <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm sm:text-base">
                  Jadwal & Periode PKL ({{ periodSetting?.monthly_periods?.length || 6 }} Bulan)
                </h3>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60">
                  <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                  </span>
                  Aktif
                </span>
              </div>
              <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400 mt-1">
                <span>
                  Periode PKL: <strong class="text-slate-700 dark:text-slate-200 font-semibold">{{ formatDate(periodSetting?.tanggal_mulai_pkl) }} s.d. {{ formatDate(periodSetting?.tanggal_selesai_pkl) }}</strong>
                </span>
                <span class="hidden sm:inline text-slate-300 dark:text-slate-700">•</span>
                <span>
                  Mulai Monitoring: <strong class="text-slate-700 dark:text-slate-200 font-semibold">{{ formatDate(periodSetting?.tanggal_mulai_monitoring || periodSetting?.tanggal_mulai_pkl) }}</strong>
                </span>
                <span class="hidden sm:inline text-slate-300 dark:text-slate-700">•</span>
                <span class="text-indigo-600 dark:text-indigo-400 font-semibold">
                  Target: 2x Kunjungan / Bulan
                </span>
              </div>
            </div>
          </div>

          <!-- Right: CTA Button -->
          <div class="flex items-center justify-end">
            <Link
              :href="route('admin.monitoring.setting')"
              class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold bg-indigo-50 hover:bg-indigo-100 text-indigo-700 dark:bg-indigo-950/60 dark:hover:bg-indigo-900/60 dark:text-indigo-300 border border-indigo-200/80 dark:border-indigo-800/60 transition-all duration-200 group cursor-pointer"
            >
              <BaseIcon :path="mdiCalendarClock" w="w-4" h="h-4" class="text-indigo-600 dark:text-indigo-400 group-hover:rotate-12 transition-transform" />
              <span>Kelola Jadwal Periode</span>
              <BaseIcon :path="mdiArrowRight" w="w-3.5" h="h-3.5" class="group-hover:translate-x-0.5 transition-transform" />
            </Link>
          </div>
        </div>

        <!-- Dynamic Month Timeline Pills (Responsive Grid) -->
        <div v-if="periodSetting?.monthly_periods && periodSetting.monthly_periods.length > 0" class="mt-4 pt-3.5 border-t border-slate-100 dark:border-slate-800/80">
          <div
            class="grid gap-2"
            :class="periodSetting.monthly_periods.length === 5 ? 'grid-cols-2 sm:grid-cols-3 md:grid-cols-5' : (periodSetting.monthly_periods.length <= 4 ? 'grid-cols-2 sm:grid-cols-4' : 'grid-cols-2 sm:grid-cols-3 md:grid-cols-6')"
          >
            <div
              v-for="p in periodSetting.monthly_periods"
              :key="p.month"
              class="px-2.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60 flex flex-col justify-center transition-all hover:border-indigo-300 dark:hover:border-indigo-700 hover:bg-indigo-50/30 dark:hover:bg-indigo-950/20"
            >
              <div class="flex items-center justify-between gap-1">
                <span class="text-[10px] uppercase font-extrabold text-indigo-600 dark:text-indigo-400 tracking-wider">
                  Bulan {{ p.month }}
                </span>
                <span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400">
                  2x Kunjungan
                </span>
              </div>
              <div class="font-bold text-xs text-slate-800 dark:text-slate-200 truncate mt-0.5" :title="p.month_name">
                {{ p.month_name }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <CardBoxWidget :number="totalMonitoring" label="Total Monitoring" :icon="mdiClipboardText" color="text-blue-500" />
        <CardBoxWidget :number="totalBengkel" label="Bengkel Terpantau" :icon="mdiOfficeBuilding" color="text-emerald-500" />
        <CardBoxWidget :number="totalPembimbing" label="Pembimbing Aktif" :icon="mdiAccountTie" color="text-rose-500" />
      </div>

      <NotificationBar
        v-if="Number($page.props?.notifications?.pelanggaran_pending_count || 0) > 0"
        color="warning"
        :icon="mdiAlertCircle"
        class="mb-6 shadow-md"
      >
        <span>
          Ada <b>{{ Number($page.props?.notifications?.pelanggaran_pending_count || 0) }}</b> laporan monitoring dengan pelanggaran yang belum diterima.
        </span>
        <template #right>
          <div class="flex items-center gap-2">
            <BaseButton
              label="Filter Laporan Ini"
              color="whiteDark"
              small
              @click="filterPelanggaranMenunggu"
            />
            <BaseButton
              label="Terima Semua Pelanggaran"
              color="success"
              small
              @click="terimaSemuaPelanggaran"
            />
          </div>
        </template>
      </NotificationBar>

      <CardBox class="mb-6" has-table>
        <!-- Filter Bar -->
        <div class="p-4 flex flex-col gap-4 bg-gray-50 dark:bg-slate-800/40 rounded-lg">
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-12 gap-4">
            <div class="sm:col-span-1 md:col-span-1 lg:col-span-2">
              <FormField label="Per Halaman">
                <FormControl
                  v-model="filterForm.per_page"
                  :options="perPageOptions"
                  type="select"
                />
              </FormField>
            </div>
            <div class="sm:col-span-2 md:col-span-2 lg:col-span-3">
              <FormField label="Kata Kunci / Pencarian">
                <FormControl
                  v-model="filterForm.search"
                  :icon="mdiMagnify"
                  placeholder="Cari Bengkel atau Pembimbing..."
                />
              </FormField>
            </div>
            <div class="sm:col-span-1 md:col-span-1 lg:col-span-3">
              <FormField label="Guru Pembimbing">
                <FormControl
                  v-model="filterForm.pembimbing_id"
                  :options="[{ id: '', label: 'Semua Pembimbing' }, ...pembimbingOptions]"
                  type="select"
                />
              </FormField>
            </div>
            <div class="sm:col-span-1 md:col-span-1 lg:col-span-2">
              <FormField label="Sesi Monitoring">
                <FormControl
                  v-model="filterForm.session"
                  :options="sessionOptions"
                  type="select"
                />
              </FormField>
            </div>
            <div class="sm:col-span-1 md:col-span-1 lg:col-span-2">
              <FormField label="Bulan">
                <FormControl
                  v-model="filterForm.bulan"
                  :options="bulanOptions"
                  type="select"
                />
              </FormField>
            </div>
          </div>

          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
              <span class="text-xs font-medium text-slate-500 flex items-center gap-1">
                <BaseIcon :path="mdiFilterVariant" w="w-3.5" h="h-3.5" />
                Status:
              </span>
              <button v-for="opt in statusOptions" :key="opt.id"
                      @click="filterForm.status = opt.id"
                      class="text-xs font-semibold px-3 py-1.5 rounded-full transition-colors"
                      :class="filterForm.status === opt.id
                        ? 'bg-indigo-600 text-white shadow-sm'
                        : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'">
                {{ opt.label }}
              </button>
              <button v-if="hasActiveFilters"
                      @click="clearFilters"
                      class="text-xs font-medium px-3 py-1.5 rounded-full bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 transition-colors flex items-center gap-1">
                <BaseIcon :path="mdiFilterRemove" w="w-3.5" h="h-3.5" />
                Hapus Filter
              </button>
            </div>

            <div class="flex items-center gap-2">
              <a :href="route('admin.monitoring.export-excel', {
                search: filterForm.search,
                pembimbing_id: filterForm.pembimbing_id,
                bengkel_id: filterForm.bengkel_id,
                status: filterForm.status,
                session: filterForm.session,
                bulan: filterForm.bulan,
              })"
                 class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold transition-colors bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm">
                <BaseIcon :path="mdiFileExcel" w="w-4" h="h-4" />
                Excel
              </a>
              <a :href="route('admin.monitoring.export-pdf', {
                search: filterForm.search,
                pembimbing_id: filterForm.pembimbing_id,
                bengkel_id: filterForm.bengkel_id,
                status: filterForm.status,
                session: filterForm.session,
                bulan: filterForm.bulan,
              })"
                 class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold transition-colors bg-red-600 text-white hover:bg-red-700 shadow-sm">
                <BaseIcon :path="mdiFilePdfBox" w="w-4" h="h-4" />
                PDF
              </a>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead class="bg-gray-50 dark:bg-slate-800">
              <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">No</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Tanggal</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Monitoring</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Pembimbing</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Bengkel</th>
                <th class="px-4 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Pelanggaran</th>
                <th class="px-4 py-3 text-center font-semibold text-slate-600 dark:text-slate-300">Status</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-600 dark:text-slate-300">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in monitoringData" :key="item.id" class="border-t dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-800/50">
                <td class="px-4 py-3">
                  {{ (monitorings.current_page - 1) * monitorings.per_page + index + 1 }}
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <BaseIcon :path="mdiCalendar" w="w-4" h="h-4" class="text-indigo-500" />
                    <span>{{ formatDate(item.tanggal_kunjungan) }}</span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div class="flex flex-col gap-1">
                    <span
                      class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold w-fit"
                      :class="(item.session_display || item.session || 1) === 1
                        ? 'bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800/50'
                        : 'bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800/50'"
                    >
                      <BaseIcon :path="mdiClipboardText" w="w-3.5" h="h-3.5" />
                      <span>Monitoring {{ item.session_display || item.session || 1 }}</span>
                    </span>
                    <span v-if="item.bulan_name" class="text-[11px] font-medium text-slate-500 dark:text-slate-400 pl-0.5">
                      {{ item.bulan_name }}
                    </span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <BaseIcon :path="mdiAccountTie" w="w-4" h="h-4" class="text-rose-500" />
                    <span>{{ item.pembimbing?.nama_guru || item.pembimbing?.user?.name || '-' }}</span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-2">
                    <BaseIcon :path="mdiOfficeBuilding" w="w-4" h="h-4" class="text-emerald-500" />
                    <span>{{ item.bengkel?.nama_bengkel || '-' }}</span>
                  </div>
                </td>
                <td class="px-4 py-3 text-center">
                  <span v-if="item.pelanggaran_count > 0" class="inline-flex items-center gap-1 text-red-600 dark:text-red-400 font-bold" title="Ada Pelanggaran Siswa">
                    <BaseIcon :path="mdiAlertCircle" w="w-5" h="h-5" />
                    <span>{{ item.pelanggaran_count }}</span>
                  </span>
                  <span v-else class="text-slate-300 dark:text-slate-700">-</span>
                </td>
                <td class="px-4 py-3 text-center">
                  <span v-if="item.laporan_diterima" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300 text-xs font-bold">
                    <BaseIcon :path="mdiCheck" w="w-3.5" h="h-3.5" />
                    Diterima
                  </span>
                  <span v-else class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-orange-100 text-orange-700 dark:bg-orange-500/15 dark:text-orange-300 text-xs font-bold">
                    Menunggu
                  </span>
                </td>
                <td class="px-4 py-3">
                  <BaseButtons type="justify-start lg:justify-end" no-wrap>
                    <BaseButton
                      color="info"
                      :icon="mdiEye"
                      label="Detail"
                      small
                      @click="showDetails(item)"
                    />
                    <BaseButton
                      v-if="!item.laporan_diterima"
                      color="success"
                      :icon="mdiCheck"
                      label="Terima"
                      small
                      @click="terimaLaporan(item.id)"
                    />
                    <BaseButton
                      v-if="canDeleteMonitoring"
                      color="danger"
                      :icon="mdiTrashCan"
                      label="Hapus"
                      small
                      @click="deleteMonitoring(item.id)"
                    />
                  </BaseButtons>
                </td>
              </tr>
              <tr v-if="monitoringData.length === 0">
                <td colspan="8" class="text-center py-10 text-gray-500">
                  Belum ada data monitoring.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
          <BaseLevel>
            <BaseButtons>
              <BaseButton
                v-for="link in monitoringLinks"
                :key="link.label"
                :active="link.active"
                :label="link.label"
                :color="link.active ? 'lightDark' : 'whiteDark'"
                small
                @click="link.url && router.visit(link.url)"
                :disabled="!link.url"
                v-html="link.label"
              />
            </BaseButtons>
            <small>Halaman {{ monitorings.current_page }} dari {{ monitorings.last_page }}</small>
          </BaseLevel>
        </div>
      </CardBox>
    </SectionMain>

    <CardBoxModal
      v-model="isModalActive"
      :title="selectedMonitoring ? 'Detail Monitoring ' + (selectedMonitoring.session_display || selectedMonitoring.session || 1) : 'Detail Monitoring'"
      has-cancel
      button-label="Tutup"
      modal-class="w-11/12 md:w-[95%] lg:w-[90%] xl:w-[85%]"
    >
      <div v-if="isLoadingDetails" class="p-6 text-center text-gray-500">
        Loading...
      </div>
      <div v-else-if="selectedMonitoring" class="space-y-4 max-h-[70vh] overflow-y-auto p-2">
        <!-- Banner Monitoring Sesi & Bulan -->
        <div class="p-4 rounded-xl bg-blue-50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30 flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-blue-600 rounded-lg text-white">
                    <BaseIcon :path="mdiClipboardText" w="w-5" h="h-5" />
                </div>
                <div>
                    <div class="font-extrabold text-blue-900 dark:text-blue-100">
                      Monitoring {{ selectedMonitoring.session_display || selectedMonitoring.session || 1 }}
                    </div>
                    <div class="text-xs text-blue-700/80 dark:text-blue-300/80 font-bold">
                      {{ selectedMonitoring.bulan_name ? selectedMonitoring.bulan_name : 'Monitoring PKL' }}
                    </div>
                </div>
            </div>
            <div class="text-right">
                <div class="text-xs font-semibold text-slate-500">Tanggal Kunjungan</div>
                <div class="text-sm font-extrabold text-slate-800 dark:text-slate-200">{{ formatDate(selectedMonitoring.tanggal_kunjungan) }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <h4 class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <BaseIcon :path="mdiAccountTie" w="w-4" h="h-4" class="text-rose-500" />
              <span>Pembimbing</span>
            </h4>
             <p>{{ selectedMonitoring.pembimbing?.nama_guru || selectedMonitoring.pembimbing?.user?.name || '-' }}</p>
          </div>
          <div class="md:col-span-2">
            <h4 class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <BaseIcon :path="mdiOfficeBuilding" w="w-4" h="h-4" class="text-emerald-500" />
              <span>Bengkel</span>
            </h4>
            <p>{{ selectedMonitoring.bengkel?.nama_bengkel }}</p>
             <p class="text-sm text-gray-500">{{ selectedMonitoring.bengkel?.alamat_bengkel }}</p>
          </div>
          <div class="md:col-span-2">
            <h4 class="font-bold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <BaseIcon :path="mdiClipboardText" w="w-4" h="h-4" class="text-blue-500" />
              <span>Catatan Monitoring</span>
            </h4>
            <p class="whitespace-pre-line">{{ selectedMonitoring.catatan_monitoring }}</p>
          </div>
          <div class="md:col-span-2" v-if="selectedMonitoring.foto_kegiatan">
             <h4 class="font-bold text-gray-700 dark:text-gray-300 mb-2">Foto Kegiatan</h4>
             <img :src="`/storage/${selectedMonitoring.foto_kegiatan}`" alt="Foto Kegiatan" class="rounded-lg max-w-full h-auto max-h-64 object-cover" />
          </div>

           <div class="md:col-span-2" v-if="selectedMonitoring.details && selectedMonitoring.details.length > 0">
            <h4 class="font-bold text-gray-700 dark:text-gray-300 mb-2">Evaluasi Siswa</h4>
            
            <!-- Desktop Table View -->
            <div class="hidden md:block border rounded-md overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-4 py-2">Nama Siswa</th>
                            <th class="px-4 py-2">Kelas</th>
                            <th class="px-4 py-2">Kehadiran</th>
                            <th class="px-4 py-2">Pelanggaran</th>
                            <th class="px-4 py-2">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="detail in selectedMonitoring.details" :key="detail.id" class="border-t dark:border-slate-700">
                            <td class="px-4 py-2 align-middle">{{ detail.siswa?.nama }}</td>
                            <td class="px-4 py-2 align-middle">
                                  <div>{{ detail.siswa?.kelas?.nama_kelas || '-' }}</div>
                                  <div v-if="detail.siswa?.kelas?.wali_kelas" class="text-[10px] text-gray-500 dark:text-gray-400 font-semibold mt-0.5">
                                      Wali: {{ detail.siswa?.kelas?.wali_kelas }}
                                  </div>
                              </td>
                            <td class="px-4 py-2 align-middle">
                                <div class="flex gap-2">
                                    <span class="px-2 py-1 rounded text-xs font-bold bg-green-100 text-green-700" title="Hadir">H: {{ detail.hadir }}</span>
                                    <span class="px-2 py-1 rounded text-xs font-bold bg-blue-100 text-blue-700" title="Izin">I: {{ detail.izin }}</span>
                                    <span class="px-2 py-1 rounded text-xs font-bold bg-yellow-100 text-yellow-700" title="Sakit">S: {{ detail.sakit }}</span>
                                    <span class="px-2 py-1 rounded text-xs font-bold bg-red-100 text-red-700" title="Alpha">A: {{ detail.alpha }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-2 align-middle">
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" v-model="detail.is_pelanggaran" class="sr-only peer">
                                    <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-600"></div>
                                    <span class="ms-2 text-xs font-semibold" :class="detail.is_pelanggaran ? 'text-red-600' : 'text-gray-400'">Aktif</span>
                                </label>
                            </td>
                            <td class="px-4 py-2 align-middle">
                                <input
                                    type="text"
                                    v-model="detail.catatan"
                                    :disabled="!detail.is_pelanggaran"
                                    class="w-full text-xs rounded-lg border-slate-200 bg-slate-50 py-1.5 px-2 text-slate-800 dark:bg-slate-900 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500 transition-all disabled:opacity-50 disabled:bg-slate-100 disabled:cursor-not-allowed"
                                    placeholder="Jelaskan pelanggaran..."
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Card List View -->
            <div class="md:hidden space-y-4">
                <div v-for="detail in selectedMonitoring.details" :key="detail.id" class="p-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm space-y-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-100 text-sm">{{ detail.siswa?.nama }}</div>
                            <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                Kelas: {{ detail.siswa?.kelas?.nama_kelas || '-' }}
                                <span v-if="detail.siswa?.kelas?.wali_kelas" class="block text-[10px] text-indigo-600 dark:text-indigo-400 font-semibold mt-0.5">
                                    Wali: {{ detail.siswa?.kelas?.wali_kelas }}
                                </span>
                            </div>
                        </div>
                        
                        <!-- Pelanggaran Toggle (Touch target minimum 40px) -->
                        <div class="flex items-center min-h-[40px]">
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" v-model="detail.is_pelanggaran" class="sr-only peer">
                                <div class="w-9 h-5 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-red-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-600"></div>
                                <span class="ms-2 text-xs font-bold" :class="detail.is_pelanggaran ? 'text-red-600' : 'text-gray-400'">Pelanggaran</span>
                            </label>
                        </div>
                    </div>

                    <!-- Kehadiran Badges -->
                    <div class="flex flex-wrap gap-1.5 pt-1">
                        <span class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400 border border-green-200/50 dark:border-green-500/20">Hadir: {{ detail.hadir }}</span>
                        <span class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 border border-blue-200/50 dark:border-blue-500/20">Izin: {{ detail.izin }}</span>
                        <span class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-yellow-50 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400 border border-yellow-200/50 dark:border-yellow-500/20">Sakit: {{ detail.sakit }}</span>
                        <span class="px-2.5 py-1.5 rounded-lg text-xs font-bold bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400 border border-red-200/50 dark:border-red-500/20">Alpha: {{ detail.alpha }}</span>
                    </div>

                    <!-- Catatan Pelanggaran Input (Touch target minimum 40px) -->
                    <div v-if="detail.is_pelanggaran" class="pt-1">
                        <input
                            type="text"
                            v-model="detail.catatan"
                            class="w-full text-sm rounded-xl border-slate-200 bg-slate-50 py-2.5 px-3.5 text-slate-800 dark:bg-slate-800 focus:border-indigo-500 focus:bg-white focus:ring-indigo-500 transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500"
                            placeholder="Jelaskan jenis pelanggaran..."
                        />
                    </div>
                </div>
            </div>
          </div>

        </div>
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
          <BaseButton label="Tutup" color="whiteDark" :auto-permission="false" @click="isModalActive = false" />
          <BaseButton
            v-if="selectedMonitoring"
            label="Simpan Perubahan"
            color="info"
            :auto-permission="false"
            :loading="isSavingDetails"
            @click="saveDetails"
          />
        </div>
      </template>
    </CardBoxModal>

  </LayoutAuthenticated>
</template>
