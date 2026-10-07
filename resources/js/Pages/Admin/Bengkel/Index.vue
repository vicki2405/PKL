<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import {
  mdiOfficeBuildingCog,
  mdiPlus,
  mdiPencil,
  mdiTrashCan,
  mdiAlertCircle,
  mdiMapMarker,
  mdiMagnify,
  mdiHomeCity,
  mdiCity,
  mdiMapSearch,
  mdiGoogleMaps,
  mdiFileExcel,
  mdiDownload,
  mdiUpload,
  mdiChevronLeft,
  mdiChevronRight,
  mdiCarCog,
  mdiMotorbike,
  mdiChip,
  mdiPalette,
  mdiSilverwareForkKnife,
  mdiHelpCircle,
  mdiEye
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import FormFilePicker from '@admin/components/FormFilePicker.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import CardBoxWidget from '@admin/components/CardBoxWidget.vue'
import { computed, ref, watch } from 'vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  bengkels: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  stats: {
    type: Object,
    default: () => ({}),
  },
})

const form = useForm({
  kode_bengkel: '',
  nama_bengkel: '',
  nama_pemilik: '',
  dusun: '',
  desa: '',
  kecamatan: '',
  deskripsi: '',
  google_maps_link: '',
  latitude: '',
  longitude: '',
  no_hp: '',
})

const deleteForm = useForm({})
const importForm = useForm({
  file: null,
})

const isModalActive = ref(false)
const isImportModalActive = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const selectedBengkel = ref(null)
const isDetailModalActive = ref(false)

const showDetail = (row) => {
  selectedBengkel.value = row
  isDetailModalActive.value = true
}

const getBadgeClass = (kode) => {
  const map = {
    TKR: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-400 dark:border-emerald-900/50',
    TSM: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/40 dark:text-blue-400 dark:border-blue-900/50',
    TEI: 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-950/40 dark:text-indigo-400 dark:border-indigo-900/50',
    ANS: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-950/40 dark:text-purple-400 dark:border-purple-900/50',
    KLR: 'bg-orange-50 text-orange-700 border-orange-200 dark:bg-orange-950/40 dark:text-orange-400 dark:border-orange-900/50',
  }
  return map[kode] || 'bg-slate-50 text-slate-700 border-slate-200 dark:bg-slate-900 dark:text-slate-400 dark:border-slate-800'
}

// Filtering
const search = ref(props.filters.search || '')
const filterDusun = ref(props.filters.dusun || '')
const filterDesa = ref(props.filters.desa || '')
const filterKecamatan = ref(props.filters.kecamatan || '')
const filterKode = ref(props.filters.kode_bengkel || '')
const perPage = ref(props.filters.per_page || 10)
const perPageOptions = [10, 20, 50, 100, 200, 300, 500]

const updateFilters = debounce(() => {
  router.get(
    route('admin.bengkel.index'),
    {
      search: search.value,
      dusun: filterDusun.value,
      desa: filterDesa.value,
      kecamatan: filterKecamatan.value,
      kode_bengkel: filterKode.value,
      per_page: perPage.value,
    },
    { preserveState: true, replace: true, preserveScroll: true }
  )
}, 300)

watch([search, filterDusun, filterDesa, filterKecamatan, filterKode, perPage], () => {
  updateFilters()
})

const bengkelData = computed(() =>
  props.bengkels && Array.isArray(props.bengkels.data) ? props.bengkels.data : [],
)

const bengkelLinks = computed(() => {
  if (!props.bengkels || !Array.isArray(props.bengkels.links)) return []
  return props.bengkels.links.map((link) => {
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

const isAllSelected = computed(() => {
  return (
    bengkelData.value.length > 0 &&
    selectedIds.value.length === bengkelData.value.length
  )
})

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedIds.value = []
  } else {
    selectedIds.value = bengkelData.value.map((row) => row.id)
  }
}

const bulkDelete = () => {
  if (confirm(`Apakah Anda yakin ingin menghapus ${selectedIds.value.length} data bengkel yang dipilih?`)) {
    router.post(route('admin.bengkel.bulk_destroy'), {
      ids: selectedIds.value
    }, {
      onSuccess: () => {
        selectedIds.value = []
      }
    })
  }
}

const openModal = () => {
  isEditing.value = false
  editingId.value = null
  form.reset()
  form.clearErrors()
  isModalActive.value = true
}

const closeModal = () => {
  isModalActive.value = false
  form.reset()
  form.clearErrors()
}

const submit = () => {
  if (isEditing.value && editingId.value) {
    form.put(route('admin.bengkel.update', editingId.value), {
      onSuccess: () => {
        closeModal()
      },
      onError: () => {
        // Optional: Scroll to top of modal or show toast
        alert('Gagal menyimpan data. Mohon periksa kembali inputan Anda.')
      }
    })
  } else {
    form.post(route('admin.bengkel.store'), {
      onSuccess: () => {
        closeModal()
      },
      onError: () => {
        // Optional: Scroll to top of modal or show toast
        alert('Gagal menyimpan data. Mohon periksa kembali inputan Anda.')
      }
    })
  }
}

const startEdit = (row) => {
  isEditing.value = true
  editingId.value = row.id
  form.kode_bengkel = row.kode_bengkel
  form.nama_bengkel = row.nama_bengkel
  form.nama_pemilik = row.nama_pemilik
  form.dusun = row.dusun
  form.desa = row.desa
  form.kecamatan = row.kecamatan
  form.deskripsi = row.deskripsi
  form.google_maps_link = row.google_maps_link
  form.latitude = row.latitude
  form.longitude = row.longitude
  form.no_hp = row.no_hp
  isModalActive.value = true
}

const deleteBengkel = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus data bengkel ini?')) {
    deleteForm.delete(route('admin.bengkel.destroy', id))
  }
}

const submitImport = () => {
  importForm.post(route('admin.bengkel.import'), {
    preserveScroll: true,
    forceFormData: true,
    onSuccess: () => {
      isImportModalActive.value = false
      importForm.reset()
    },
  })
}

const exportExcel = () => {
  window.open(route('admin.bengkel.export'), '_blank')
}

const scrollContainer = ref(null)
const scrollLeft = () => {
  if (scrollContainer.value) {
    scrollContainer.value.scrollBy({ left: -400, behavior: 'smooth' })
  }
}
const scrollRight = () => {
  if (scrollContainer.value) {
    scrollContainer.value.scrollBy({ left: 400, behavior: 'smooth' })
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Data Bengkel" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiOfficeBuildingCog" title="Data Bengkel" main>
        <BaseButtons>
          <BaseButton
            :icon="mdiFileExcel"
            label="Import Excel"
            color="success"
            rounded-full
            small
            @click="isImportModalActive = true"
          />
          <BaseButton
            :icon="mdiFileExcel"
            label="Export Excel"
            color="info"
            rounded-full
            small
            @click="exportExcel"
          />
          <BaseButton
            :icon="mdiPlus"
            label="Tambah Bengkel"
            color="contrast"
            rounded-full
            small
            @click="openModal"
          />
        </BaseButtons>
      </SectionTitleLineWithButton>

      <NotificationBar
        v-if="$page.props.flash && $page.props.flash.message"
        color="success"
        :icon="mdiAlertCircle"
      >
        {{ $page.props.flash.message }}
      </NotificationBar>
      <NotificationBar
        v-if="$page.props.flash && $page.props.flash.error"
        color="danger"
        :icon="mdiAlertCircle"
      >
        {{ $page.props.flash.error }}
      </NotificationBar>

      <!-- Card Stats Grid -->
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6 mb-6">
        <CardBoxWidget
          :number="props.stats?.TKR || 0"
          :icon="mdiCarCog"
          color="text-emerald-500"
          label="Jurusan TKR"
        />
        <CardBoxWidget
          :number="props.stats?.TSM || 0"
          :icon="mdiMotorbike"
          color="text-blue-500"
          label="Jurusan TSM"
        />
        <CardBoxWidget
          :number="props.stats?.TEI || 0"
          :icon="mdiChip"
          color="text-indigo-500"
          label="Jurusan TEI"
        />
        <CardBoxWidget
          :number="props.stats?.ANS || 0"
          :icon="mdiPalette"
          color="text-purple-500"
          label="Jurusan Animasi"
        />
        <CardBoxWidget
          :number="props.stats?.KLR || 0"
          :icon="mdiSilverwareForkKnife"
          color="text-orange-500"
          label="Jurusan Kuliner"
        />
        <CardBoxWidget
          :number="props.stats?.LAIN || 0"
          :icon="mdiHelpCircle"
          color="text-zinc-500"
          label="Lainnya"
        />
      </div>

      <CardBoxModal
        v-model="isImportModalActive"
        title="Import Bengkel"
        button="info"
        button-label="Import"
        has-cancel
        modal-class="w-11/12 md:w-3/5 lg:w-1/2 xl:w-1/2"
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
            :href="route('admin.bengkel.template')"
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

      <CardBox class="mb-6" has-table>
        <div class="p-4 flex flex-wrap gap-4">
          <!-- Search -->
          <div class="w-full md:w-1/5">
            <FormControl v-model="search" :icon="mdiMagnify" placeholder="Cari Nama Bengkel..." />
          </div>

          <!-- Filters -->
          <div class="w-full md:w-1/5">
            <FormControl v-model="filterDusun" :icon="mdiHomeCity" placeholder="Filter Dusun..." />
          </div>
          <div class="w-full md:w-1/5">
            <FormControl v-model="filterDesa" :icon="mdiCity" placeholder="Filter Desa..." />
          </div>
          <div class="w-full md:w-1/5">
            <FormControl v-model="filterKecamatan" :icon="mdiMapSearch" placeholder="Filter Kecamatan..." />
          </div>
          
          <!-- Filter Jurusan -->
          <div class="w-full md:w-1/5">
            <select
              v-model="filterKode"
              class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-slate-800 dark:border-slate-700"
            >
              <option value="">Semua Jurusan</option>
              <option value="TKR">Teknik Kendaraan Ringan (TKR)</option>
              <option value="TSM">Teknik Sepeda Motor (TSM)</option>
              <option value="TEI">Teknik Elektronika Industri (TEI)</option>
              <option value="ANS">Animasi (ANS)</option>
              <option value="KLR">Kuliner (KLR)</option>
              <option value="LAIN">Lainnya / Belum Ditentukan</option>
            </select>
          </div>

          <!-- Per Page -->
          <div class="w-full md:w-auto">
            <select
              v-model="perPage"
              class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-slate-800 dark:border-slate-700"
            >
              <option v-for="option in perPageOptions" :key="option" :value="option">
                {{ option }}
              </option>
            </select>
          </div>

          <div v-if="selectedIds.length > 0" class="w-full md:w-auto">
             <BaseButton
              :icon="mdiTrashCan"
              label="Hapus Terpilih"
              color="danger"
              rounded-full
              small
              @click="bulkDelete"
            />
          </div>
        </div>

        <div class="flex items-center justify-end gap-2 px-4 pb-3 pt-1">
          <BaseButton :icon="mdiChevronLeft" color="info" small @click="scrollLeft" />
          <BaseButton :icon="mdiChevronRight" color="info" small @click="scrollRight" />
        </div>

        <div class="overflow-x-auto" ref="scrollContainer">
          <table class="w-full text-sm">
            <thead class="bg-blue-600 text-white [&>tr>th]:text-white">
              <tr>
                <th class="w-10 text-center">
                  <input
                    type="checkbox"
                    :checked="isAllSelected"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                </th>
                <th class="w-16 text-center">No</th>
                <th class="min-w-[220px]">Nama Bengkel</th>
                <th class="min-w-[150px]">Nama Pemilik</th>
                <th class="min-w-[200px]">Alamat</th>
                <th class="min-w-[120px]">No HP</th>
                <th class="w-48 text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in bengkelData" :key="row.id">
                <td class="text-center">
                  <input
                    type="checkbox"
                    v-model="selectedIds"
                    :value="row.id"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                  />
                </td>
                <td class="text-center" data-label="No">
                  {{ index + 1 + ((props.bengkels.current_page - 1) * props.bengkels.per_page) }}
                </td>
                <td data-label="Nama Bengkel" class="font-semibold whitespace-normal">
                  <div class="flex items-center gap-2">
                    <span
                      v-if="row.kode_bengkel"
                      class="px-1.5 py-0.5 text-[10px] font-extrabold uppercase rounded-md border"
                      :class="getBadgeClass(row.kode_bengkel)"
                    >
                      {{ row.kode_bengkel }}
                    </span>
                    <span>{{ row.nama_bengkel }}</span>
                  </div>
                </td>
                <td data-label="Nama Pemilik" class="whitespace-normal text-xs">
                  {{ row.nama_pemilik || '-' }}
                </td>
                <td data-label="Alamat" class="whitespace-normal text-xs text-slate-600 dark:text-slate-400">
                  <div>
                    {{ [row.dusun, row.desa, row.kecamatan].filter(Boolean).join(', ') || '-' }}
                  </div>
                </td>
                <td data-label="No HP" class="text-xs">
                  {{ row.no_hp || '-' }}
                </td>
                <td class="before:hidden lg:w-1 whitespace-nowrap text-right">
                  <BaseButtons type="justify-start lg:justify-end" no-wrap>
                    <BaseButton
                      color="success"
                      :icon="mdiEye"
                      small
                      @click="showDetail(row)"
                    />
                    <BaseButton
                      color="info"
                      :icon="mdiPencil"
                      small
                      @click="startEdit(row)"
                    />
                    <BaseButton
                      color="danger"
                      :icon="mdiTrashCan"
                      small
                      @click="deleteBengkel(row.id)"
                    />
                  </BaseButtons>
                </td>
              </tr>
              <tr v-if="bengkelData.length === 0">
                <td colspan="7" class="text-center py-8 text-gray-500">
                  <div class="flex flex-col items-center justify-center">
                    <span class="text-lg font-medium">Data bengkel belum tersedia</span>
                    <p class="text-sm mt-1">Silakan tambahkan bengkel baru.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="bengkelLinks.length > 3"
          class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800"
        >
          <BaseLevel>
            <BaseButtons>
              <BaseButton
                v-for="(link, index) in bengkelLinks"
                :key="index"
                :active="link.active"
                :label="link.label.replace(/&laquo; /g, '« ').replace(/ &raquo;/g, ' »').replace(/&laquo;/g, '«').replace(/&raquo;/g, '»')"
                :color="link.active ? 'lightDark' : 'whiteDark'"
                small
                @click="link.url && router.visit(link.url)"
                :disabled="!link.url"
              />
            </BaseButtons>
            <small>Halaman {{ props.bengkels.current_page }} dari {{ props.bengkels.last_page }}</small>
          </BaseLevel>
        </div>
      </CardBox>

      <CardBoxModal
        v-model="isModalActive"
        :title="isEditing ? 'Edit Bengkel' : 'Tambah Bengkel'"
        button-label="Simpan"
        has-cancel
        @confirm="submit"
      >
        <FormField label="Kode Bengkel / Jurusan Rumpun" :error="form.errors.kode_bengkel">
          <select
            v-model="form.kode_bengkel"
            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-slate-800 dark:border-slate-700"
          >
            <option value="">-- Pilih Kode Rumpun Jurusan --</option>
            <option value="TKR">TKR (Teknik Kendaraan Ringan)</option>
            <option value="TSM">TSM (Teknik Sepeda Motor)</option>
            <option value="TEI">TEI (Teknik Elektronika Industri)</option>
            <option value="ANS">ANS (Animasi)</option>
            <option value="KLR">KLR (Kuliner)</option>
          </select>
        </FormField>

        <FormField label="Nama Bengkel" :error="form.errors.nama_bengkel">
          <FormControl v-model="form.nama_bengkel" placeholder="Masukkan nama bengkel" />
        </FormField>

        <FormField label="Nama Pemilik" :error="form.errors.nama_pemilik">
          <FormControl v-model="form.nama_pemilik" placeholder="Masukkan nama pemilik" />
        </FormField>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormField label="Dusun" :error="form.errors.dusun">
            <FormControl v-model="form.dusun" placeholder="Nama Dusun" />
          </FormField>
          <FormField label="Desa" :error="form.errors.desa">
            <FormControl v-model="form.desa" placeholder="Nama Desa" />
          </FormField>
        </div>

        <FormField label="Kecamatan" :error="form.errors.kecamatan">
          <FormControl v-model="form.kecamatan" placeholder="Nama Kecamatan" />
        </FormField>

        <FormField label="Deskripsi Alamat" :error="form.errors.deskripsi">
          <FormControl v-model="form.deskripsi" type="textarea" placeholder="Detail lokasi (misal: Depan Masjid)" />
        </FormField>

        <FormField label="Link Google Maps" :error="form.errors.google_maps_link">
          <FormControl v-model="form.google_maps_link" placeholder="https://maps.google.com/..." />
        </FormField>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormField label="Latitude" :error="form.errors.latitude">
            <FormControl v-model="form.latitude" placeholder="Contoh: -7.123456" />
          </FormField>
          <FormField label="Longitude" :error="form.errors.longitude">
            <FormControl v-model="form.longitude" placeholder="Contoh: 112.123456" />
          </FormField>
        </div>

        <div class="text-xs text-gray-500 mb-4 bg-blue-50 p-3 rounded-lg border border-blue-100">
            <p class="font-semibold text-blue-700 mb-1">Tips:</p>
            <p>Jika Anda memasukkan <strong>Link Google Maps</strong> yang mengandung koordinat (contoh: <code>/@-7.123,112.123</code>), sistem akan otomatis mengisi Latitude dan Longitude saat disimpan.</p>
        </div>

        <FormField label="No HP" :error="form.errors.no_hp">
          <FormControl v-model="form.no_hp" placeholder="Contoh: 081234567890" />
        </FormField>
      </CardBoxModal>

      <!-- Modal Detail Bengkel -->
      <CardBoxModal
        v-model="isDetailModalActive"
        title="Detail Informasi Bengkel"
        buttonLabel="Tutup"
        has-cancel
        :has-confirm="false"
        @confirm="isDetailModalActive = false"
        modal-class="w-11/12 md:w-3/4 lg:w-2/3 max-w-4xl"
      >
        <div v-if="selectedBengkel" class="space-y-6 text-slate-800 dark:text-slate-100">
          <!-- Header Banner -->
          <div class="p-4 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-100 dark:border-indigo-900/50 flex items-start gap-4">
            <div class="p-3 bg-indigo-500 text-white rounded-xl">
              <svg viewBox="0 0 24 24" class="w-6 h-6 fill-current">
                <path :d="mdiOfficeBuildingCog" />
              </svg>
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <h3 class="text-xl font-bold">{{ selectedBengkel.nama_bengkel }}</h3>
                <span 
                  v-if="selectedBengkel.kode_bengkel"
                  class="px-2.5 py-1 rounded-full text-xs font-extrabold uppercase border"
                  :class="getBadgeClass(selectedBengkel.kode_bengkel)"
                >
                  {{ selectedBengkel.kode_bengkel }}
                </span>
              </div>
              <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">
                Kode Bengkel: {{ selectedBengkel.kode_bengkel || '-' }}
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Left Info Block -->
            <div class="space-y-4 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
              <h4 class="text-sm font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Kontak & Pemilik</h4>
              
              <div>
                <label class="block text-xs font-semibold text-slate-400">Nama Pemilik</label>
                <p class="text-sm font-medium mt-0.5">{{ selectedBengkel.nama_pemilik || '-' }}</p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-400">No HP / WhatsApp</label>
                <p class="text-sm font-medium mt-0.5">{{ selectedBengkel.no_hp || '-' }}</p>
              </div>
            </div>

            <!-- Right Info Block -->
            <div class="space-y-4 p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
              <h4 class="text-sm font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Lokasi & Alamat</h4>

              <div>
                <label class="block text-xs font-semibold text-slate-400">Alamat Administratif</label>
                <p class="text-sm font-medium mt-0.5">
                  Dusun {{ selectedBengkel.dusun || '-' }}, 
                  Desa {{ selectedBengkel.desa || '-' }}, 
                  Kecamatan {{ selectedBengkel.kecamatan || '-' }}
                </p>
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-400">Petunjuk Lokasi (Deskripsi)</label>
                <p class="text-sm font-medium mt-0.5 whitespace-pre-line">{{ selectedBengkel.deskripsi || '-' }}</p>
              </div>
            </div>
          </div>

          <!-- Maps Block -->
          <div v-if="selectedBengkel.google_maps_link" class="p-4 rounded-2xl border border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50">
            <h4 class="text-sm font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 mb-3">Peta Lokasi</h4>
            <div class="flex items-center justify-between gap-4 flex-wrap bg-white dark:bg-slate-950 p-4 rounded-xl border border-slate-200/50 dark:border-slate-800/80">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-rose-50 dark:bg-rose-950/40 text-rose-500 rounded-lg">
                  <svg viewBox="0 0 24 24" class="w-5 h-5 fill-current">
                    <path :d="mdiGoogleMaps" />
                  </svg>
                </div>
                <div>
                  <div class="text-sm font-bold text-slate-800 dark:text-slate-100">Koordinat GPS</div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    Lat: {{ selectedBengkel.latitude || '-' }} | Long: {{ selectedBengkel.longitude || '-' }}
                  </div>
                </div>
              </div>
              <a
                :href="selectedBengkel.google_maps_link"
                target="_blank"
                class="px-4 py-2 bg-rose-500 hover:bg-rose-600 text-white rounded-xl text-xs font-bold transition-colors inline-flex items-center gap-1.5 shadow-md shadow-rose-500/20"
              >
                <svg viewBox="0 0 24 24" class="w-4 h-4 fill-current">
                  <path :d="mdiGoogleMaps" />
                </svg>
                <span>Buka Google Maps</span>
              </a>
            </div>
          </div>
        </div>
      </CardBoxModal>

      <!-- Map Modal -->
      <!-- <CardBoxModal
        v-model="isMapModalActive"
        title="Lokasi Bengkel"
        has-cancel
        :has-confirm="false"
        cancel-label="Tutup"
      >
        <div class="h-96 w-full rounded-lg overflow-hidden border border-gray-200">
          <l-map
            ref="map"
            v-model:zoom="mapZoom"
            :center="mapCenter"
            :use-global-leaflet="false"
          >
            <l-tile-layer
              url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
              layer-type="base"
              name="OpenStreetMap"
            ></l-tile-layer>
            <l-marker
                v-if="selectedBengkel && selectedBengkel.latitude && selectedBengkel.longitude"
                :lat-lng="[parseFloat(selectedBengkel.latitude), parseFloat(selectedBengkel.longitude)]"
              >
                <l-popup>
                  <div class="text-center">
                    <h3 class="font-bold">{{ selectedBengkel.nama_bengkel }}</h3>
                    <p class="text-sm">
                      {{ selectedBengkel.dusun ? selectedBengkel.dusun + ', ' : '' }}
                      {{ selectedBengkel.desa ? selectedBengkel.desa + ', ' : '' }}
                      {{ selectedBengkel.kecamatan }}
                    </p>
                    <a
                      :href="`https://www.google.com/maps?q=${selectedBengkel.latitude},${selectedBengkel.longitude}`"
                      target="_blank"
                      class="text-blue-600 text-xs hover:underline block mt-1"
                    >
                      Buka di Google Maps
                    </a>
                  </div>
                </l-popup>
              </l-marker>
          </l-map>
        </div>
      </CardBoxModal> -->
    </SectionMain>
  </LayoutAuthenticated>
</template>
