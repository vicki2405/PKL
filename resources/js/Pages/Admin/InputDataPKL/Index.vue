<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import {
  mdiFileDocumentEdit,
  mdiPlus,
  mdiMagnify,
  mdiAccountSchool,
  mdiDomain,
  mdiOfficeBuildingCog,
  mdiHumanMaleBoard,
  mdiAlertCircle,
  mdiPencil,
  mdiTrashCan,
  mdiFileExcel,
  mdiCloudUpload,
  mdiDownload,
  mdiEye,
  mdiTable
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import FormFilePicker from '@admin/components/FormFilePicker.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import { computed, ref, watch } from 'vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  bengkels: {
    type: Object,
    default: () => ({}),
  },
  placements: {
    type: Object,
    default: () => ({}),
  },
  siswas: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const view = ref(props.filters.view || 'bengkel')
const search = ref(props.filters.search || '')
const perPage = ref(props.filters.per_page || 10)
const perPageOptions = [10, 20, 50, 100, 250, 500]
const pembimbingFilter = ref(props.filters.pembimbing_filter || 'all')

const isDeleteModalActive = ref(false)
const bengkelToDelete = ref(null)
const isImportModalActive = ref(false)
const importFile = ref(null)

const updateFilters = debounce(() => {
  router.get(
    route('admin.input_data_pkl.index'),
    { view: view.value, search: search.value, per_page: perPage.value, pembimbing_filter: pembimbingFilter.value },
    { preserveState: true, replace: true }
  )
}, 300)

watch([view, search, perPage, pembimbingFilter], () => {
  updateFilters()
})

const dataset = computed(() => {
  const fallback = {
    data: [],
    links: [],
    current_page: 1,
    per_page: perPage.value,
    last_page: 1,
  }

  if (view.value === 'siswa_sudah') return Object.keys(props.placements || {}).length ? props.placements : fallback
  if (view.value === 'siswa_belum') return Object.keys(props.siswas || {}).length ? props.siswas : fallback
  return Object.keys(props.bengkels || {}).length ? props.bengkels : fallback
})

const items = computed(() => dataset.value?.data || [])
const links = computed(() => {
  if (!dataset.value?.links) return []
  return dataset.value.links.map((link) => {
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

const selectedIds = ref([])

const visibleBengkelIds = computed(() => {
  if (view.value !== 'bengkel') return []
  return (items.value || []).map((row) => row.id)
})

const pembimbingList = (item) => {
  const list = []
  if (item && Array.isArray(item.penempatan_pkl)) {
    item.penempatan_pkl.forEach((p) => {
      const nama = p?.pembimbing?.nama_guru
      if (nama && !list.includes(nama)) list.push(nama)
    })
  }
  return list
}

const isAllSelected = computed(() => {
  const ids = visibleBengkelIds.value
  return ids.length > 0 && selectedIds.value.length === ids.length
})

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = visibleBengkelIds.value
  }
}

const bulkDelete = () => {
  if (selectedIds.value.length === 0) return
  if (confirm(`Apakah Anda yakin ingin menghapus data penempatan PKL pada ${selectedIds.value.length} bengkel yang dipilih?`)) {
    router.post(route('admin.input_data_pkl.bulk_destroy'), { ids: selectedIds.value }, {
      preserveScroll: true,
      onSuccess: () => {
        selectedIds.value = []
      },
    })
  }
}

watch(view, () => {
  selectedIds.value = []
})

watch(visibleBengkelIds, (ids) => {
  const allowed = new Set(ids)
  selectedIds.value = selectedIds.value.filter((id) => allowed.has(id))
})

const confirmDelete = (bengkel) => {
  bengkelToDelete.value = bengkel
  isDeleteModalActive.value = true
}

const deletePlacement = () => {
  if (bengkelToDelete.value) {
    router.delete(route('admin.input_data_pkl.destroy', bengkelToDelete.value.id), {
      onSuccess: () => {
        isDeleteModalActive.value = false
        bengkelToDelete.value = null
      },
    })
  }
}

const submitImport = () => {
  if (!importFile.value) return;
  const formData = new FormData();
  formData.append('file', importFile.value);
  router.post(route('admin.input_data_pkl.import'), formData, {
    preserveScroll: true,
    onSuccess: () => {
      isImportModalActive.value = false
      importFile.value = null
    }
  })
}

const exportExcel = () => {
  const url = route('admin.input_data_pkl.export_excel', {
    view: view.value,
    search: search.value,
    pembimbing_filter: pembimbingFilter.value,
  })
  window.open(url, '_blank')
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Data Penempatan PKL" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiFileDocumentEdit"
        title="Data Penempatan PKL"
        main
      >
        <BaseButtons>
          <BaseButton
            :icon="mdiCloudUpload"
            label="Import Excel"
            color="success"
            rounded-full
            small
            @click="isImportModalActive = true"
            class="shadow-lg hover:shadow-xl transition-all duration-300"
          />
          <BaseButton
            :icon="mdiFileExcel"
            label="Export Excel"
            color="success"
            rounded-full
            small
            @click="exportExcel"
            class="shadow-lg hover:shadow-xl transition-all duration-300"
          />
          <BaseButton
            :to="route('admin.input_data_pkl.create')"
            :icon="mdiPlus"
            label="Input Data Baru"
            color="info"
            rounded-full
            small
            class="shadow-lg hover:shadow-xl transition-all duration-300"
          />
        </BaseButtons>
      </SectionTitleLineWithButton>

      <NotificationBar v-if="$page.props.flash.message" color="success" :icon="mdiAlertCircle" class="mb-6 shadow-md">
        {{ $page.props.flash.message }}
      </NotificationBar>
      <NotificationBar v-if="$page.props.flash.error" color="danger" :icon="mdiAlertCircle" class="mb-6 shadow-md">
        {{ $page.props.flash.error }}
      </NotificationBar>

      <div class="mb-6 bg-white dark:bg-slate-800 rounded-2xl shadow-sm p-4 border border-gray-100 dark:border-slate-700">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
          <div class="flex flex-wrap items-center gap-2">
            <BaseButton
              :icon="mdiTable"
              label="Per Bengkel"
              rounded-full
              small
              :color="view === 'bengkel' ? 'info' : 'whiteDark'"
              @click="view = 'bengkel'"
            />
            <BaseButton
              :icon="mdiAccountSchool"
              label="Siswa Sudah Dapat Bengkel"
              rounded-full
              small
              :color="view === 'siswa_sudah' ? 'info' : 'whiteDark'"
              @click="view = 'siswa_sudah'"
            />
            <BaseButton
              :icon="mdiAlertCircle"
              label="Siswa Belum Dapat Bengkel"
              rounded-full
              small
              :color="view === 'siswa_belum' ? 'info' : 'whiteDark'"
              @click="view = 'siswa_belum'"
            />
          </div>
        </div>
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
          <div class="w-full md:w-1/2 relative">
             <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <BaseIcon :path="mdiMagnify" class="text-gray-400" />
             </div>
             <input
                v-model="search"
                type="text"
                placeholder="Cari Siswa atau Bengkel..."
                class="pl-10 w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white transition-all duration-300"
             />
          </div>
          <div v-if="view === 'bengkel'" class="flex items-center gap-2">
            <span class="text-sm font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">Pembimbing:</span>
            <select v-model="pembimbingFilter" class="rounded-xl border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-700 dark:border-slate-600 text-sm py-2 pl-4 pr-10 cursor-pointer transition-all duration-300">
              <option value="all">Semua</option>
              <option value="with_pembimbing">Sudah Ada</option>
              <option value="without_pembimbing">Belum Ada</option>
            </select>
          </div>
          <div class="flex items-center gap-3">
              <span class="text-sm font-medium text-gray-600 dark:text-gray-300">Tampilkan:</span>
              <select v-model="perPage" class="rounded-xl border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-700 dark:border-slate-600 text-sm py-2 pl-4 pr-10 cursor-pointer transition-all duration-300">
                  <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
              </select>
              <BaseButton
                v-if="view === 'bengkel' && selectedIds.length > 0 && $can('bulk_delete', 'input_data_pkl')"
                :icon="mdiTrashCan"
                label="Hapus Terpilih"
                color="danger"
                rounded-full
                small
                @click="bulkDelete"
                class="shadow-lg hover:shadow-xl transition-all duration-300"
              />
          </div>
        </div>
      </div>

      <CardBox class="mb-6 shadow-xl border-0 overflow-hidden rounded-2xl" has-table>
        <div class="overflow-auto min-h-[500px]" ref="tableContainer">
          <table v-if="view === 'bengkel'" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-slate-700 dark:text-gray-400 sticky top-0 z-10">
              <tr>
                <th v-if="$can('bulk_delete', 'input_data_pkl')" class="px-6 py-4 rounded-tl-lg text-center w-10">
                  <input
                    type="checkbox"
                    :checked="isAllSelected"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                </th>
                <th :class="['px-6 py-4', $can('bulk_delete', 'input_data_pkl') ? '' : 'rounded-tl-lg']">No</th>
                <th class="px-6 py-4">Kode Bengkel</th>
                <th class="px-6 py-4">Nama Bengkel</th>
                <th class="px-6 py-4">Alamat</th>
                <th class="px-6 py-4 text-center">Jumlah Siswa</th>
                <th class="px-6 py-4">Pembimbing</th>
                <th class="px-6 py-4 text-center rounded-tr-lg">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in items" :key="item.id" class="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors duration-200">
                <td v-if="$can('bulk_delete', 'input_data_pkl')" class="px-6 py-4 text-center">
                  <input
                    type="checkbox"
                    v-model="selectedIds"
                    :value="item.id"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                </td>
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                    {{ (bengkels.current_page - 1) * bengkels.per_page + index + 1 }}
                </td>
                <td class="px-6 py-4">
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-indigo-900 dark:text-indigo-300">
                        {{ item.kode_bengkel }}
                    </span>
                </td>
                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                  <div class="flex items-center">
                      <div class="p-2 rounded-full bg-blue-50 dark:bg-blue-900/30 mr-3 text-blue-600 dark:text-blue-400">
                        <BaseIcon :path="mdiOfficeBuildingCog" :size="20" />
                      </div>
                      {{ item.nama_bengkel }}
                  </div>
                </td>
                <td class="px-6 py-4">
                   <div class="flex flex-col">
                      <span class="text-gray-900 dark:text-white font-medium">{{ item.dusun }}, {{ item.desa }}</span>
                      <span class="text-xs text-gray-500 mt-0.5">{{ item.kecamatan }}</span>
                   </div>
                </td>
                <td class="px-6 py-4 text-center">
                   <div class="relative inline-flex items-center justify-center w-10 h-10 overflow-hidden bg-emerald-100 rounded-full dark:bg-emerald-900 group transition-all duration-300 hover:scale-110">
                      <span class="font-bold text-emerald-600 dark:text-emerald-300">{{ item.penempatan_pkl_count }}</span>
                   </div>
                </td>
                <td class="px-6 py-4">
                   <div class="flex items-start" v-if="pembimbingList(item).length > 0">
                      <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center mr-2 mt-0.5 text-purple-600 dark:text-purple-400 shrink-0">
                          <BaseIcon :path="mdiHumanMaleBoard" :size="16" />
                      </div>
                      <div class="flex flex-col gap-0.5">
                          <span v-for="nama in pembimbingList(item)" :key="nama" class="text-sm font-medium">{{ nama }}</span>
                      </div>
                  </div>
                  <span v-else class="text-gray-400 text-sm italic flex items-center">
                      <BaseIcon :path="mdiAlertCircle" class="mr-1" :size="14" /> Belum ditentukan
                  </span>
                </td>
                <td class="px-6 py-4 text-center">
                    <div class="flex items-center justify-center space-x-2">
                        <Link
                            :href="route('admin.input_data_pkl.show', item.id)"
                            class="p-2 rounded-full text-blue-600 hover:bg-blue-100 dark:text-blue-400 dark:hover:bg-blue-900/50 transition-colors"
                            title="Lihat Detail"
                        >
                            <BaseIcon :path="mdiEye" :size="20" />
                        </Link>
                        <Link
                            v-if="$can('edit', 'input_data_pkl')"
                            :href="route('admin.input_data_pkl.edit', item.id)"
                            class="p-2 rounded-full text-amber-500 hover:bg-amber-100 dark:text-amber-400 dark:hover:bg-amber-900/50 transition-colors"
                            title="Edit Data"
                        >
                            <BaseIcon :path="mdiPencil" :size="20" />
                        </Link>
                        <button
                            v-if="$can('delete', 'input_data_pkl')"
                            @click="confirmDelete(item)"
                            class="p-2 rounded-full text-red-600 hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-900/50 transition-colors"
                            title="Hapus Data"
                        >
                            <BaseIcon :path="mdiTrashCan" :size="20" />
                        </button>
                    </div>
                </td>
              </tr>
              <tr v-if="items.length === 0">
                  <td :colspan="$can('bulk_delete', 'input_data_pkl') ? 8 : 7" class="text-center py-12">
                      <div class="flex flex-col items-center justify-center text-gray-500">
                          <BaseIcon :path="mdiFileDocumentEdit" size="48" class="mb-4 text-gray-300" />
                          <p class="text-lg font-medium">Belum ada data penempatan PKL.</p>
                          <p class="text-sm mt-1">Silakan tambah data baru atau import dari Excel.</p>
                      </div>
                  </td>
              </tr>
            </tbody>
          </table>

          <table v-else-if="view === 'siswa_sudah'" class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-slate-700 dark:text-gray-400 sticky top-0 z-10">
              <tr>
                <th class="px-6 py-4 rounded-tl-lg">No</th>
                <th class="px-6 py-4">NISN</th>
                <th class="px-6 py-4">Nama Siswa</th>
                <th class="px-6 py-4">Kelas</th>
                <th class="px-6 py-4">Jurusan</th>
                <th class="px-6 py-4">Bengkel</th>
                <th class="px-6 py-4 rounded-tr-lg">Pembimbing</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(row, index) in items"
                :key="row.id"
                class="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors duration-200"
              >
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                  {{ (dataset.current_page - 1) * dataset.per_page + index + 1 }}
                </td>
                <td class="px-6 py-4">
                  <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ row.siswa?.nisn || '-' }}</span>
                </td>
                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                  {{ row.siswa?.nama || '-' }}
                </td>
                <td class="px-6 py-4">{{ row.siswa?.kelas || '-' }}</td>
                <td class="px-6 py-4">{{ row.siswa?.jurusan || '-' }}</td>
                <td class="px-6 py-4">
                  <div v-if="row.bengkel" class="flex items-center gap-2">
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-indigo-900 dark:text-indigo-300">
                      {{ row.bengkel.kode_bengkel }}
                    </span>
                    <Link
                      :href="route('admin.input_data_pkl.show', row.bengkel.id)"
                      class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
                    >
                      {{ row.bengkel.nama_bengkel }}
                    </Link>
                  </div>
                  <span v-else class="text-gray-400 text-sm italic">-</span>
                </td>
                <td class="px-6 py-4">
                  <span class="text-sm font-medium">{{ row.pembimbing?.nama_guru || '-' }}</span>
                </td>
              </tr>
              <tr v-if="items.length === 0">
                <td colspan="7" class="text-center py-12">
                  <div class="flex flex-col items-center justify-center text-gray-500">
                    <BaseIcon :path="mdiAccountSchool" size="48" class="mb-4 text-gray-300" />
                    <p class="text-lg font-medium">Tidak ada siswa yang sudah mendapatkan bengkel.</p>
                    <p class="text-sm mt-1">Silakan ubah pencarian atau filter.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>

          <table v-else class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-slate-700 dark:text-gray-400 sticky top-0 z-10">
              <tr>
                <th class="px-6 py-4 rounded-tl-lg">No</th>
                <th class="px-6 py-4">NISN</th>
                <th class="px-6 py-4">Nama Siswa</th>
                <th class="px-6 py-4">Kelas</th>
                <th class="px-6 py-4 rounded-tr-lg">Jurusan</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(row, index) in items"
                :key="row.id"
                class="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors duration-200"
              >
                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                  {{ (dataset.current_page - 1) * dataset.per_page + index + 1 }}
                </td>
                <td class="px-6 py-4">
                  <span class="text-xs font-semibold text-slate-700 dark:text-slate-200">{{ row.nisn || '-' }}</span>
                </td>
                <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">
                  {{ row.nama || '-' }}
                </td>
                <td class="px-6 py-4">{{ row.kelas || '-' }}</td>
                <td class="px-6 py-4">{{ row.jurusan || '-' }}</td>
              </tr>
              <tr v-if="items.length === 0">
                <td colspan="5" class="text-center py-12">
                  <div class="flex flex-col items-center justify-center text-gray-500">
                    <BaseIcon :path="mdiAlertCircle" size="48" class="mb-4 text-gray-300" />
                    <p class="text-lg font-medium">Tidak ada siswa yang belum mendapatkan bengkel.</p>
                    <p class="text-sm mt-1">Semua siswa sudah ditempatkan.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="p-4 border-t border-gray-100 dark:border-slate-700 bg-gray-50 dark:bg-slate-800/50">
          <BaseLevel>
            <BaseButtons>
              <BaseButton
                v-for="link in links"
                :key="link.label"
                :active="link.active"
                :label="link.label"
                :color="link.active ? 'info' : 'whiteDark'"
                small
                :to="link.url"
                :disabled="!link.url"
                class="transition-transform active:scale-95"
              />
            </BaseButtons>
            <small class="text-gray-500 font-medium">Halaman {{ dataset.current_page }} dari {{ dataset.last_page }}</small>
          </BaseLevel>
        </div>
      </CardBox>
    </SectionMain>

    <CardBoxModal
      v-model="isImportModalActive"
      title="Import Penempatan PKL"
      buttonLabel="Import Sekarang"
      has-cancel
      @confirm="submitImport"
      class="backdrop-blur-sm"
    >
      <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border border-blue-100 dark:border-blue-800">
        <div class="flex items-start">
            <BaseIcon :path="mdiAlertCircle" class="text-blue-500 mr-3 mt-0.5" />
            <div>
                <h4 class="font-semibold text-blue-700 dark:text-blue-300 mb-1">Panduan Import</h4>
                <p class="text-sm text-blue-600 dark:text-blue-400 mb-3">Pastikan format file sesuai dengan template yang disediakan agar data berhasil diimport.</p>
                <BaseButton
                  :icon="mdiDownload"
                  label="Download Template Excel"
                  color="info"
                  outline
                  small
                  :href="route('admin.input_data_pkl.template')"
                  target="_blank"
                />
            </div>
        </div>
      </div>
      <FormField label="Upload File Excel (xlsx/xls/csv)">
        <FormFilePicker v-model="importFile" label="Pilih File dari Komputer" />
      </FormField>
    </CardBoxModal>

    <CardBoxModal
      v-model="isDeleteModalActive"
      title="Konfirmasi Hapus"
      buttonLabel="Ya, Hapus Data"
      buttonColor="danger"
      has-cancel
      @confirm="deletePlacement"
    >
      <div class="text-center p-4">
          <div class="w-16 h-16 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <BaseIcon :path="mdiTrashCan" size="32" />
          </div>
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Hapus Data Penempatan?</h3>
          <p class="text-gray-500 dark:text-gray-400 mb-2">Anda akan menghapus data penempatan PKL di bengkel:</p>
          <p class="font-semibold text-lg text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-slate-700 py-2 rounded-lg">{{ bengkelToDelete?.nama_bengkel }}</p>
          <p class="text-sm text-red-500 mt-4 font-medium">Tindakan ini tidak dapat dibatalkan!</p>
      </div>
    </CardBoxModal>
  </LayoutAuthenticated>
</template>
