<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { mdiSchool, mdiTrashCan, mdiAlertCircle, mdiPlus, mdiPencil, mdiMagnify, mdiRefresh, mdiDomain, mdiGoogleClassroom, mdiFormatListNumbered, mdiUpload, mdiFileExcel, mdiDownload, mdiArrowUp, mdiArrowDown } from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import CardBox from '@admin/components/CardBox.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import FormFilePicker from '@admin/components/FormFilePicker.vue'
import { computed, ref, watch, onMounted, onUnmounted, nextTick } from 'vue'
import { format } from 'date-fns'
import { id } from 'date-fns/locale'

const props = defineProps({
  siswas: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  jurusanOptions: {
    type: Array,
    default: () => [],
  },
  kelasOptions: {
    type: Array,
    default: () => [],
  },
})

const deleteForm = useForm({})
const bulkForm = useForm({ ids: [] })
const importForm = useForm({
  file: null,
})

const siswaData = computed(() =>
  props.siswas && Array.isArray(props.siswas.data) ? props.siswas.data : [],
)

const siswaLinks = computed(() =>
  props.siswas && Array.isArray(props.siswas.links) ? props.siswas.links : [],
)

const filterForm = useForm({
  search: props.filters?.search || '',
  jurusan_id: props.filters?.jurusan_id || '',
  kelas_id: props.filters?.kelas_id || '',
  jenis_kelamin: props.filters?.jenis_kelamin || '',
  progress: props.filters?.progress || '',
  per_page: Number(props.filters?.per_page || 10),
  sort_by: props.filters?.sort_by || 'nama',
  sort_order: props.filters?.sort_order || 'asc',
})

const selectedJurusan = ref('')
const selectedKelas = ref('')
const selectedIds = ref([])
const isAllSelected = computed(() => siswaData.value.length > 0 && selectedIds.value.length === siswaData.value.length)
const isImportModalActive = ref(false)

const topScroll = ref(null)
const tableContainer = ref(null)
const tableRef = ref(null)
const tableWidth = ref(0)

const updateTableWidth = () => {
  if (tableRef.value) {
     tableWidth.value = tableRef.value.scrollWidth
  }
}

const syncScroll = (source) => {
  if (source === 'top' && topScroll.value && tableContainer.value) {
    tableContainer.value.scrollLeft = topScroll.value.scrollLeft
  } else if (source === 'table' && topScroll.value && tableContainer.value) {
    topScroll.value.scrollLeft = tableContainer.value.scrollLeft
  }
}

onMounted(() => {
  nextTick(() => {
    updateTableWidth()
    window.addEventListener('resize', updateTableWidth)
    // Create a ResizeObserver to watch for changes in the table size
    if (window.ResizeObserver && tableRef.value) {
      const resizeObserver = new ResizeObserver(() => {
        updateTableWidth()
      })
      resizeObserver.observe(tableRef.value)
    }
  })
})

onUnmounted(() => {
  window.removeEventListener('resize', updateTableWidth)
})

const perPageOptions = [10, 20, 50, 100, 250, 500, 700]

const genderOptions = [
  { id: 'L', label: 'Laki-laki' },
  { id: 'P', label: 'Perempuan' },
]

const progressOptions = [
  { id: '', label: 'Semua' },
  { value: 'complete', label: 'Lengkap (100%)' },
  { value: 'below_50', label: '< 50%' },
  { value: 'incomplete', label: 'Belum lengkap' },
]

const jurusanSelectOptions = computed(() =>
  (props.jurusanOptions || []).map((j) => ({
    id: j.id,
    label: j.kode_jurusan ? `${j.kode_jurusan} - ${j.nama_jurusan}` : j.nama_jurusan,
  })),
)

const filteredKelasOptions = computed(() => {
  const jurusanId = selectedJurusan.value || filterForm.jurusan_id || ''
  const list = (props.kelasOptions || []).filter((k) => !jurusanId || String(k.jurusan_id) === String(jurusanId))
  return list.map((k) => ({ id: k.id, label: k.nama_kelas }))
})

const initSelected = () => {
  if (filterForm.kelas_id) {
    selectedKelas.value = String(filterForm.kelas_id)
    if (!filterForm.jurusan_id) {
      const k = (props.kelasOptions || []).find(
        (kk) => String(kk.id) === String(filterForm.kelas_id),
      )
      if (k) {
        filterForm.jurusan_id = String(k.jurusan_id)
      }
    }
  }
  if (filterForm.jurusan_id) {
    selectedJurusan.value = String(filterForm.jurusan_id)
  }
}
initSelected()

  watch(
    selectedJurusan,
    (val) => {
      filterForm.jurusan_id = val || ''
      selectedKelas.value = ''
      filterForm.kelas_id = ''
    },
  )

  watch(
    selectedKelas,
    (val) => {
      filterForm.kelas_id = val || ''
    },
  )

  const debounce = (fn, delay) => {
    let timeout
    return (...args) => {
      clearTimeout(timeout)
      timeout = setTimeout(() => fn(...args), delay)
    }
  }

  watch(
    () => filterForm.search,
    debounce(() => {
      applyFilters()
    }, 500),
  )

  watch(
    [() => filterForm.jurusan_id, () => filterForm.kelas_id],
    debounce(() => {
      applyFilters()
    }, 100),
  )

  watch(
    () => filterForm.progress,
    () => {
      applyFilters()
    },
  )

  watch(
    () => filterForm.per_page,
    () => {
      applyFilters()
    }
  )

watch(
  siswaData,
  () => {
    selectedIds.value = selectedIds.value.filter((id) => siswaData.value.some((r) => r.id === id))
  },
  { deep: true },
)

const applyFilters = () => {
  router.get(route('admin.siswa.index'), filterForm.data(), { preserveState: true, preserveScroll: true, replace: true })
}

const resetFilters = () => {
  filterForm.search = ''
  filterForm.jurusan_id = ''
  filterForm.kelas_id = ''
  filterForm.jenis_kelamin = ''
  filterForm.progress = ''
  selectedJurusan.value = ''
  selectedKelas.value = ''
  applyFilters()
}

const deleteSiswa = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus data siswa ini?')) {
    deleteForm.delete(route('admin.siswa.destroy', id))
  }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return format(new Date(dateString), 'd MMMM yyyy', { locale: id })
}

const toggleSelectAll = (e) => {
  if (e.target.checked) {
    selectedIds.value = siswaData.value.map((r) => r.id)
  } else {
    selectedIds.value = []
  }
}

const exportExcel = () => {
  const url = new URL(route('admin.siswa.export'));

  if (filterForm.search) url.searchParams.append('search', filterForm.search);
  if (filterForm.jurusan_id) url.searchParams.append('jurusan_id', filterForm.jurusan_id);
  if (filterForm.kelas_id) url.searchParams.append('kelas_id', filterForm.kelas_id);
  if (filterForm.jenis_kelamin) url.searchParams.append('jenis_kelamin', filterForm.jenis_kelamin);

  window.open(url.toString(), '_blank');
}

const bulkDelete = () => {
  if (selectedIds.value.length === 0) return
  if (confirm('Apakah Anda yakin ingin menghapus data yang dipilih?')) {
    bulkForm.ids = selectedIds.value
    bulkForm.post(route('admin.siswa.bulkDestroy'), {
      preserveScroll: true,
      onSuccess: () => {
        selectedIds.value = []
      },
    })
  }
}

const completenessFields = [
  { key: 'nisn', label: 'NISN', get: (r) => r.nisn },
  { key: 'nipd', label: 'NIPD', get: (r) => r.nipd },
  { key: 'nama', label: 'Nama', get: (r) => r.nama },
  { key: 'tempat_lahir', label: 'Tempat Lahir', get: (r) => r.tempat_lahir },
  { key: 'tanggal_lahir', label: 'Tanggal Lahir', get: (r) => r.tanggal_lahir },
  { key: 'jenis_kelamin', label: 'Jenis Kelamin', get: (r) => r.jenis_kelamin },
  { key: 'agama', label: 'Agama', get: (r) => r.agama },
  { key: 'no_hp', label: 'No HP', get: (r) => r.no_hp },
  { key: 'alamat', label: 'Alamat', get: (r) => r.alamat },
  { key: 'rt', label: 'RT', get: (r) => r.rt },
  { key: 'rw', label: 'RW', get: (r) => r.rw },
  { key: 'dusun', label: 'Dusun', get: (r) => r.dusun },
  { key: 'kelurahan', label: 'Kelurahan', get: (r) => r.kelurahan },
  { key: 'kecamatan', label: 'Kecamatan', get: (r) => r.kecamatan },
  { key: 'email', label: 'Email', get: (r) => r.user?.email || r.account?.email },
  { key: 'photo', label: 'Foto Profil', get: (r) => (Number(r.profile_photo_count || 0) > 0 ? true : '') },
]

const getCompletenessMeta = (row) => {
  const missing = []
  let filled = 0

  for (const f of completenessFields) {
    const v = f.get(row)
    const ok = v !== null && v !== undefined && v !== ''
    if (ok) {
      filled++
    } else {
      missing.push(f.label)
    }
  }

  const total = completenessFields.length
  const percentage = total > 0 ? Math.round((filled / total) * 100) : 0
  return { percentage, missing, filled, total }
}

const getCompleteness = (row) => {
  return getCompletenessMeta(row).percentage
}

const submitImport = () => {
  if (!importForm.file) return
  importForm.post(route('admin.siswa.import'), {
    preserveScroll: true,
    onSuccess: () => {
      importForm.reset()
      isImportModalActive.value = false
    },
  })
}

const toggleSort = (field) => {
  if (filterForm.sort_by === field) {
    filterForm.sort_order = filterForm.sort_order === 'asc' ? 'desc' : 'asc'
  } else {
    filterForm.sort_by = field
    filterForm.sort_order = 'asc'
  }
  applyFilters()
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Data Siswa" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiSchool" title="Data Siswa" main>
        <BaseButtons>
          <BaseButton
            :icon="mdiDownload"
            label="Export Excel"
            color="info"
            rounded-full
            small
            @click="exportExcel"
          />
          <BaseButton
            :icon="mdiFileExcel"
            label="Import Excel"
            color="success"
            rounded-full
            small
            @click="isImportModalActive = true"
          />
          <BaseButton
            :icon="mdiPlus"
            label="Tambah Siswa"
            color="contrast"
            rounded-full
            small
            :to="route('admin.siswa.create')"
          />
        </BaseButtons>
      </SectionTitleLineWithButton>

      <NotificationBar
        v-if="$page.props.flash && $page.props.flash.message"
        color="success"
        :icon="mdiAlertCircle"
        class="mb-6 shadow-sm"
      >
        {{ $page.props.flash.message }}
      </NotificationBar>

      <CardBox class="mb-6 shadow-xl border-0 overflow-hidden rounded-2xl">
        <div class="p-6 grid grid-cols-1 md:grid-cols-12 gap-5 items-end">
          <div class="md:col-span-4 relative">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pencarian</label>
            <div class="relative">
              <input
                v-model="filterForm.search"
                type="text"
                placeholder="Cari Nama / NISN / NIPD..."
                class="w-full pl-11 pr-4 py-2.5 bg-gray-50 dark:bg-slate-800/50 border border-gray-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm shadow-sm"
              />
              <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none">
                <BaseIcon :path="mdiMagnify" w="w-5" h="h-5" class="text-gray-400" />
              </div>
            </div>
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Jurusan</label>
            <FormControl
              v-model="selectedJurusan"
              :options="jurusanSelectOptions"
              :icon="mdiDomain"
              type="select"
              placeholder="Semua Jurusan"
              class="w-full rounded-xl shadow-sm"
            />
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Kelas</label>
            <FormControl
              v-model="selectedKelas"
              :options="filteredKelasOptions"
              :icon="mdiGoogleClassroom"
              type="select"
              placeholder="Semua Kelas"
              class="w-full rounded-xl shadow-sm"
            />
          </div>

          <div class="md:col-span-2">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Progress</label>
            <FormControl
              v-model="filterForm.progress"
              :options="progressOptions"
              :icon="mdiFormatListNumbered"
              type="select"
              placeholder="Semua"
              class="w-full rounded-xl shadow-sm"
            />
          </div>

          <div class="md:col-span-1">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">J. Kelamin</label>
            <FormControl
              v-model="filterForm.jenis_kelamin"
              :options="genderOptions"
              placeholder="L/P"
              type="select"
              class="w-full rounded-xl shadow-sm"
            />
          </div>

          <div class="md:col-span-1">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Tampil</label>
            <FormControl
              v-model="filterForm.per_page"
              :options="perPageOptions"
              type="select"
              class="w-full rounded-xl shadow-sm"
            />
          </div>

          <div class="md:col-span-12 flex justify-end gap-3 mt-2">
            <BaseButton
              @click="resetFilters"
              :icon="mdiRefresh"
              color="whiteDark"
              label="Reset Filter"
              rounded-full
              class="shadow-sm hover:shadow-md transition-all duration-300"
            />
            <BaseButton
              v-if="selectedIds.length > 0"
              :icon="mdiTrashCan"
              color="danger"
              label="Hapus Terpilih"
              @click="bulkDelete"
              rounded-full
              class="shadow-sm hover:shadow-md transition-all duration-300 animate-fade-in"
            />
          </div>
        </div>
      </CardBox>

      <CardBoxModal
        v-model="isImportModalActive"
        title="Import Data Siswa"
        button="info"
        button-label="Mulai Import"
        has-cancel
        modal-class="w-11/12 md:w-3/5 lg:w-1/2 xl:w-1/3"
        @confirm="submitImport"
      >
        <p class="mb-2 text-sm text-gray-500 dark:text-slate-400">
          Silakan download template berikut untuk memastikan format data benar.
        </p>
        <div class="mb-6">
          <BaseButton
            :icon="mdiDownload"
            label="Download Template"
            color="success"
            small
            outline
            :href="route('admin.siswa.template')"
          />
        </div>
        <FormField label="File Excel" :error="importForm.errors.file">
          <FormFilePicker
            v-model="importForm.file"
            :icon="mdiUpload"
            label="Pilih file Excel"
            accept=".xlsx,.xls,.csv"
          />
        </FormField>
      </CardBoxModal>

      <div class="lg:hidden mb-6 space-y-4">
        <div v-if="siswaData.length === 0" class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 p-8 text-center">
          <div class="w-16 h-16 bg-gray-50 dark:bg-slate-800 rounded-full flex items-center justify-center mx-auto mb-4 shadow-inner">
            <BaseIcon :path="mdiSchool" w="w-8" h="h-8" class="text-gray-300 dark:text-gray-600" />
          </div>
          <p class="text-lg font-bold text-gray-800 dark:text-gray-200">Data siswa belum tersedia</p>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Silakan tambah data baru atau import dari Excel.</p>
        </div>

        <CardBox
          v-for="(row, index) in siswaData"
          :key="row.id"
          class="shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden rounded-2xl border-0"
        >
          <div class="p-5">
            <div class="flex items-start justify-between gap-4">
              <div class="min-w-0 flex-1">
                <div class="flex items-center gap-3">
                  <input
                    type="checkbox"
                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer shadow-sm"
                    v-model="selectedIds"
                    :value="row.id"
                    aria-label="Pilih siswa"
                  />
                  <div class="h-12 w-12 rounded-full bg-gradient-to-br from-indigo-100 to-blue-100 dark:from-indigo-900/50 dark:to-blue-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold flex-shrink-0 shadow-sm border border-indigo-50 dark:border-indigo-800/30 text-lg">
                    {{ row.nama.charAt(0).toUpperCase() }}
                  </div>
                  <div class="min-w-0">
                    <p class="font-bold text-gray-900 dark:text-white truncate text-base">{{ row.nama }}</p>
                    <p class="text-xs font-semibold text-indigo-600/80 dark:text-indigo-400/80 truncate mt-0.5">{{ row.kelas?.nama_kelas || '-' }} • {{ row.kelas?.jurusan?.nama_jurusan || '-' }}</p>
                    <p v-if="row.kelas?.wali_kelas" class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 bg-gray-50 dark:bg-slate-800 px-1.5 py-0.5 rounded w-fit border border-gray-200 dark:border-slate-700">
                      Wali: {{ row.kelas.wali_kelas }} <span v-if="row.kelas.no_wa_wali" class="text-indigo-600 dark:text-indigo-400">({{ row.kelas.no_wa_wali }})</span>
                    </p>
                  </div>
                </div>
              </div>

              <BaseButtons no-wrap>
                <BaseButton
                  color="info"
                  :icon="mdiPencil"
                  small
                  :to="route('admin.siswa.edit', row.id)"
                  class="shadow-sm"
                />
                <BaseButton
                  color="danger"
                  :icon="mdiTrashCan"
                  small
                  @click="deleteSiswa(row.id)"
                  class="shadow-sm"
                />
              </BaseButtons>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-4 text-sm">
              <div class="bg-gray-50/80 dark:bg-slate-800/60 rounded-xl p-3.5 border border-gray-100 dark:border-slate-700/50 shadow-sm">
                <p class="text-[10px] uppercase font-bold text-gray-500 dark:text-gray-400 mb-1 tracking-wider">NIPD / NISN</p>
                <p class="font-bold text-gray-800 dark:text-gray-200">{{ row.nipd || '-' }}</p>
                <p class="text-xs text-gray-500 font-mono mt-0.5">{{ row.nisn }}</p>
              </div>
              <div class="bg-gray-50/80 dark:bg-slate-800/60 rounded-xl p-3.5 border border-gray-100 dark:border-slate-700/50 shadow-sm">
                <p class="text-[10px] uppercase font-bold text-gray-500 dark:text-gray-400 mb-1 tracking-wider">Kelengkapan</p>
                <div class="mt-1.5 flex items-center gap-2">
                  <div
                    class="flex-1 bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 overflow-hidden shadow-inner"
                    :title="getCompletenessMeta(row).missing.length ? `Belum diisi: ${getCompletenessMeta(row).missing.join(', ')}` : 'Lengkap'"
                  >
                    <div
                      class="h-2.5 rounded-full transition-all duration-500"
                      :class="getCompleteness(row) === 100 ? 'bg-emerald-500' : (getCompleteness(row) >= 50 ? 'bg-amber-400' : 'bg-rose-500')"
                      :style="`width: ${getCompleteness(row)}%`"
                    />
                  </div>
                  <span
                    class="text-xs font-bold"
                    :class="getCompleteness(row) === 100 ? 'text-emerald-600 dark:text-emerald-400' : (getCompleteness(row) >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400')"
                  >
                    {{ getCompleteness(row) }}%
                  </span>
                </div>
              </div>
            </div>

            <div class="mt-4 flex flex-wrap items-center gap-2">
              <span
                class="px-2.5 py-1 rounded-md text-[10px] font-bold tracking-wide"
                :class="row.jenis_kelamin === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/30' : 'bg-pink-50 text-pink-700 border border-pink-200 dark:bg-pink-900/20 dark:text-pink-400 dark:border-pink-800/30'"
              >
                {{ row.jenis_kelamin === 'L' ? 'LAKI-LAKI' : (row.jenis_kelamin === 'P' ? 'PEREMPUAN' : '-') }}
              </span>
              <span class="px-2.5 py-1 rounded-md text-[10px] font-bold tracking-wide bg-gray-100 text-gray-700 border border-gray-200 dark:bg-slate-800 dark:text-gray-300 dark:border-slate-700">
                {{ row.agama || '-' }}
              </span>
            </div>

            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-slate-700/50 text-sm space-y-2">
              <div class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                <BaseIcon :path="mdiFormatListNumbered" :size="16" class="text-gray-400" />
                <span class="font-mono text-xs">{{ row.no_hp || '-' }}</span>
              </div>
              <div class="flex items-start gap-2 text-gray-700 dark:text-gray-300">
                <BaseIcon :path="mdiAlertCircle" :size="16" class="text-gray-400 mt-0.5" />
                <div class="break-all flex flex-col">
                  <span>{{ row.user?.email || '-' }}</span>
                  <span v-if="row.account?.email && row.account.email !== row.user?.email" class="text-[10px] text-amber-600 dark:text-amber-400 mt-1 bg-amber-50 dark:bg-amber-900/20 px-1.5 py-0.5 rounded w-fit border border-amber-200 dark:border-amber-800/30">
                    Akun: {{ row.account.email }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </CardBox>

        <div v-if="siswaLinks.length > 3" class="bg-white dark:bg-slate-900 rounded-xl border border-gray-100 dark:border-slate-800 p-3">
          <BaseLevel>
            <BaseButtons>
              <BaseButton
                v-for="(link, key) in siswaLinks"
                :key="key"
                :active="link.active"
                :label="link.label"
                :color="link.active ? 'info' : 'whiteDark'"
                :disabled="!link.url"
                :href="link.url"
                small
                v-html="link.label"
                class="shadow-sm"
              />
            </BaseButtons>
          </BaseLevel>
        </div>
      </div>

      <CardBox class="mb-6 shadow-xl border-0 overflow-hidden rounded-2xl hidden lg:block">
        <!-- Top Scrollbar -->
        <div
          ref="topScroll"
          class="overflow-x-auto bg-gray-50/50 dark:bg-slate-800/50 border-b border-gray-100 dark:border-slate-700/50"
          @scroll="syncScroll('top')"
        >
          <div :style="{ width: tableWidth + 'px' }" class="h-3"></div>
        </div>

        <div class="overflow-auto min-h-[500px] custom-scrollbar" ref="tableContainer" @scroll="syncScroll('table')">
          <table class="min-w-full whitespace-nowrap text-sm text-left text-gray-500 dark:text-gray-400" ref="tableRef">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50/80 dark:bg-slate-700/80 dark:text-gray-300 sticky top-0 z-10 backdrop-blur-md">
              <tr>
                <th class="w-10 text-center px-4 py-4 rounded-tl-lg border-b border-gray-100 dark:border-slate-700/50">
                  <input
                    type="checkbox"
                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer shadow-sm"
                    :checked="isAllSelected"
                    @change="toggleSelectAll"
                    aria-label="Pilih semua"
                  />
                </th>
                <th class="px-4 py-4 font-semibold tracking-wider border-b border-gray-100 dark:border-slate-700/50">No</th>
                <th class="px-4 py-4 font-semibold tracking-wider border-b border-gray-100 dark:border-slate-700/50">Progres</th>
                <th class="px-4 py-4 font-semibold tracking-wider border-b border-gray-100 dark:border-slate-700/50">NIPD / NISN</th>
                <th class="px-4 py-4 font-semibold tracking-wider border-b border-gray-100 dark:border-slate-700/50 cursor-pointer select-none hover:bg-indigo-50/50 dark:hover:bg-indigo-900/20 transition-colors" @click="toggleSort('nama')">
                  <div class="flex items-center gap-1">
                    <span>Nama Lengkap</span>
                    <BaseIcon
                      v-if="filterForm.sort_by === 'nama'"
                      :path="filterForm.sort_order === 'asc' ? mdiArrowUp : mdiArrowDown"
                      :size="16"
                      class="text-indigo-500"
                    />
                    <BaseIcon
                      v-else
                      :path="mdiArrowUp"
                      :size="16"
                      class="text-gray-300 dark:text-gray-600"
                    />
                  </div>
                </th>
                <th class="px-4 py-4 font-semibold tracking-wider border-b border-gray-100 dark:border-slate-700/50">Kelas / Jurusan</th>
                <th class="px-4 py-4 font-semibold tracking-wider border-b border-gray-100 dark:border-slate-700/50">TTL</th>
                <th class="px-4 py-4 text-center font-semibold tracking-wider border-b border-gray-100 dark:border-slate-700/50">L/P</th>
                <th class="px-4 py-4 font-semibold tracking-wider border-b border-gray-100 dark:border-slate-700/50">Agama</th>
                <th class="px-4 py-4 font-semibold tracking-wider border-b border-gray-100 dark:border-slate-700/50">Alamat</th>
                <th class="px-4 py-4 font-semibold tracking-wider border-b border-gray-100 dark:border-slate-700/50">Kontak</th>
                <th class="px-4 py-4 font-semibold tracking-wider border-b border-gray-100 dark:border-slate-700/50">Email</th>
                <th class="px-4 py-4 text-center font-semibold tracking-wider border-b border-gray-100 dark:border-slate-700/50 sticky right-0 bg-gray-50/90 dark:bg-slate-800/90 backdrop-blur-md rounded-tr-lg shadow-[-4px_0_15px_rgba(0,0,0,0.03)]">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-slate-700/50">
              <tr v-for="(row, index) in siswaData" :key="row.id" class="bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors duration-200 group">
                <td class="text-center px-4 py-4">
                  <input
                    type="checkbox"
                    class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer shadow-sm"
                    v-model="selectedIds"
                    :value="row.id"
                    aria-label="Pilih baris"
                  />
                </td>
                <td class="px-4 py-4 font-medium text-gray-900 dark:text-white">
                  {{ (siswas.current_page - 1) * siswas.per_page + index + 1 }}
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-2">
                    <div
                      class="w-16 bg-gray-100 rounded-full h-2 dark:bg-gray-700 overflow-hidden shadow-inner"
                      :title="getCompletenessMeta(row).missing.length ? `Belum diisi: ${getCompletenessMeta(row).missing.join(', ')}` : 'Lengkap'"
                    >
                      <div class="h-2 rounded-full transition-all duration-500"
                           :class="getCompleteness(row) === 100 ? 'bg-emerald-500' : (getCompleteness(row) >= 50 ? 'bg-amber-400' : 'bg-rose-500')"
                           :style="`width: ${getCompleteness(row)}%`"></div>
                    </div>
                    <span class="text-xs font-bold" :class="getCompleteness(row) === 100 ? 'text-emerald-600 dark:text-emerald-400' : (getCompleteness(row) >= 50 ? 'text-amber-600 dark:text-amber-400' : 'text-rose-600 dark:text-rose-400')">
                      {{ getCompleteness(row) }}%
                    </span>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex flex-col">
                    <span class="font-semibold text-gray-900 dark:text-white">{{ row.nipd || '-' }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 font-mono bg-gray-100 dark:bg-slate-700 px-1.5 py-0.5 rounded mt-1 w-fit">{{ row.nisn }}</span>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-100 to-blue-100 dark:from-indigo-900/50 dark:to-blue-900/50 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold flex-shrink-0 shadow-sm border border-indigo-50 dark:border-indigo-800/30">
                      {{ row.nama.charAt(0).toUpperCase() }}
                    </div>
                    <span class="font-bold text-gray-900 dark:text-white">{{ row.nama }}</span>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex flex-col">
                    <span class="font-semibold text-indigo-900 dark:text-indigo-100">{{ row.kelas?.nama_kelas || '-' }}</span>
                    <span class="text-xs text-indigo-600/80 dark:text-indigo-400/80 mt-0.5">{{ row.kelas?.jurusan?.nama_jurusan || '-' }}</span>
                    <span v-if="row.kelas?.wali_kelas" class="text-[10px] text-gray-500 dark:text-gray-400 mt-1 bg-gray-50 dark:bg-slate-700/50 px-1.5 py-0.5 rounded w-fit border border-gray-200 dark:border-slate-600">
                      Wali: {{ row.kelas.wali_kelas }} <span v-if="row.kelas.no_wa_wali" class="text-indigo-600 dark:text-indigo-400">({{ row.kelas.no_wa_wali }})</span>
                    </span>
                  </div>
                </td>
                <td class="px-4 py-4">
                  <div class="flex flex-col">
                    <span class="text-gray-900 dark:text-gray-100">{{ row.tempat_lahir || '-' }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ formatDate(row.tanggal_lahir) }}</span>
                  </div>
                </td>
                <td class="px-4 py-4 text-center">
                  <span class="px-2.5 py-1 rounded-md text-xs font-bold tracking-wide"
                        :class="row.jenis_kelamin === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800/30' : 'bg-pink-50 text-pink-700 border border-pink-200 dark:bg-pink-900/20 dark:text-pink-400 dark:border-pink-800/30'">
                    {{ row.jenis_kelamin === 'L' ? 'LAKI-LAKI' : (row.jenis_kelamin === 'P' ? 'PEREMPUAN' : '-') }}
                  </span>
                </td>
                <td class="px-4 py-4">
                  {{ row.agama || '-' }}
                </td>
                <td class="px-4 py-4">
                  <div class="flex flex-col max-w-[220px]" :title="`${row.dusun || ''} ${row.kelurahan || ''} ${row.kecamatan || ''}`">
                    <span class="text-gray-900 dark:text-gray-100 truncate font-medium">{{ row.dusun || '-' }}, {{ row.kelurahan || '-' }}</span>
                    <span class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">RT {{ row.rt || '-' }}/RW {{ row.rw || '-' }}, Kec. {{ row.kecamatan || '-' }}</span>
                  </div>
                </td>
                <td class="px-4 py-4 font-mono text-sm text-gray-700 dark:text-gray-300">
                  {{ row.no_hp || '-' }}
                </td>
                <td class="px-4 py-4">
                  <div class="flex flex-col">
                    <span class="text-gray-900 dark:text-gray-100">{{ row.user?.email || '-' }}</span>
                    <div
                      v-if="row.account?.email && row.account.email !== row.user?.email"
                      class="text-[10px] text-amber-600 dark:text-amber-400 mt-1 bg-amber-50 dark:bg-amber-900/20 px-1.5 py-0.5 rounded w-fit border border-amber-200 dark:border-amber-800/30"
                      title="Email alternatif yang digunakan saat registrasi akun siswa"
                    >
                      Akun: {{ row.account.email }}
                    </div>
                  </div>
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-center sticky right-0 bg-white/90 dark:bg-slate-800/90 backdrop-blur-sm group-hover:bg-gray-50/90 dark:group-hover:bg-slate-700/50 transition-colors shadow-[-4px_0_15px_rgba(0,0,0,0.03)]">
                  <BaseButtons type="justify-center" no-wrap>
                    <BaseButton
                      color="info"
                      :icon="mdiPencil"
                      small
                      :to="route('admin.siswa.edit', row.id)"
                      class="shadow-sm"
                    />
                    <BaseButton
                      color="danger"
                      :icon="mdiTrashCan"
                      small
                      @click="deleteSiswa(row.id)"
                      class="shadow-sm"
                    />
                  </BaseButtons>
                </td>
              </tr>
              <tr v-if="siswaData.length === 0">
                <td colspan="13" class="text-center py-16">
                  <div class="flex flex-col items-center justify-center text-gray-500 dark:text-gray-400">
                    <div class="w-20 h-20 bg-gray-50 dark:bg-slate-800 rounded-full flex items-center justify-center mb-4 shadow-inner">
                        <BaseIcon :path="mdiSchool" w="w-10" h="h-10" class="text-gray-300 dark:text-gray-600" />
                    </div>
                    <p class="text-xl font-bold text-gray-700 dark:text-gray-300">Data siswa belum tersedia</p>
                    <p class="text-sm mt-2 max-w-md text-center">Silakan tambah data siswa baru secara manual atau gunakan fitur import Excel untuk memasukkan data secara massal.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="p-5 bg-gray-50/80 dark:bg-slate-800/80 border-t border-gray-100 dark:border-slate-700/50 flex flex-col md:flex-row justify-between items-center gap-4">
          <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
            Menampilkan <span class="text-gray-900 dark:text-white font-bold">{{ siswas.from || 0 }}</span> sampai <span class="text-gray-900 dark:text-white font-bold">{{ siswas.to || 0 }}</span> dari <span class="text-gray-900 dark:text-white font-bold">{{ siswas.total || 0 }}</span> data
          </span>
          <BaseLevel>
            <BaseButtons>
              <BaseButton
                v-for="(link, key) in siswaLinks"
                :key="key"
                :active="link.active"
                :label="link.label"
                :color="link.active ? 'info' : 'whiteDark'"
                :disabled="!link.url"
                :href="link.url"
                small
                v-html="link.label"
                class="shadow-sm transition-all duration-300 hover:shadow-md"
              />
            </BaseButtons>
          </BaseLevel>
        </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
