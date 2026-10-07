<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import {
  mdiTransfer,
  mdiPlus,
  mdiMagnify,
  mdiAccountSchool,
  mdiDomain,
  mdiOfficeBuildingCog,
  mdiHumanMaleBoard,
  mdiCheck,
  mdiClose,
  mdiTrashCan,
  mdiMapMarker,
  mdiAlertCircle,
  mdiFileDocumentEdit,
  mdiChevronLeft,
  mdiChevronRight,
  mdiFileExcel,
  mdiEye,
  mdiPrinter
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import { computed, ref, watch } from 'vue'
import debounce from 'lodash/debounce'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  mutasi: {
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
  bengkelOptions: {
    type: Array,
    default: () => [],
  },
  pembimbingOptions: {
    type: Array,
    default: () => [],
  },
})

const search = ref(props.filters.search || '')
const selectedKelas = ref(props.filters.kelas_id || '')
const perPage = ref(props.filters.per_page || 10)
const perPageOptions = [10, 20, 50, 100, 250, 500]

const isProcessModalActive = ref(false)
const isRejectModalActive = ref(false)
const selectedMutasiId = ref(null)
const adminNote = ref('')

const isDetailModalActive = ref(false)
const detailedItem = ref(null)

const showDetail = (item) => {
  detailedItem.value = item
  isDetailModalActive.value = true
}

const tableContainer = ref(null)

const scrollTable = (direction) => {
  if (tableContainer.value) {
    const scrollAmount = 300
    if (direction === 'left') {
      tableContainer.value.scrollBy({ left: -scrollAmount, behavior: 'smooth' })
    } else {
      tableContainer.value.scrollBy({ left: scrollAmount, behavior: 'smooth' })
    }
  }
}

const updateFilters = debounce(() => {
  router.get(
    route('admin.mutasi_pkl.index'),
    {
      search: search.value,
      per_page: perPage.value,
      kelas_id: selectedKelas.value
    },
    { preserveState: true, replace: true, preserveScroll: true }
  )
}, 300)

watch([search, perPage, selectedKelas], () => {
  updateFilters()
})

const items = computed(() => props.mutasi.data)
const links = computed(() => {
  if (!props.mutasi.links) return []
  return props.mutasi.links.map((link) => {
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

const process = (id) => {
    selectedMutasiId.value = id
    adminNote.value = ''
    isProcessModalActive.value = true
}

const submitProcess = () => {
    router.post(route('admin.mutasi_pkl.process', selectedMutasiId.value), {
        admin_note: adminNote.value
    }, {
        onSuccess: () => {
            isProcessModalActive.value = false
            selectedMutasiId.value = null
            adminNote.value = ''
            router.reload({ only: ['mutasi', 'filters'] })
        },
        onError: () => {
            // no-op
        }
    })
}

const approve = (item) => {
    router.visit(route('admin.mutasi_pkl.review', item.id))
}

const printP1 = (id) => {
  window.open(route('admin.cetak.p1.print', id), '_blank')
}

const reject = (id) => {
    selectedMutasiId.value = id
    adminNote.value = ''
    isRejectModalActive.value = true
}

const submitReject = () => {
    if (!adminNote.value) {
        alert('Alasan penolakan wajib diisi.')
        return
    }
    router.put(route('admin.mutasi_pkl.reject', selectedMutasiId.value), {
        admin_note: adminNote.value
    }, {
        onSuccess: () => {
            isRejectModalActive.value = false
            selectedMutasiId.value = null
            adminNote.value = ''
            router.reload({ only: ['mutasi', 'filters'] })
        },
        onError: () => {
            // no-op; errors will be shown via flash/error
        }
    })
}

const destroy = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
    router.delete(route('admin.mutasi_pkl.destroy', id))
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Mutasi PKL" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiTransfer"
        title="Daftar Pengajuan Mutasi PKL"
        main
      >
        <BaseButtons>
          <BaseButton
            :href="route('admin.mutasi_pkl.export', { search: search, kelas_id: selectedKelas })"
            :icon="mdiFileExcel"
            label="Export Excel"
            color="success"
            rounded-full
            target="_blank"
          />
          <BaseButton
            :to="route('admin.mutasi_pkl.create')"
            :icon="mdiPlus"
            label="Buat Pengajuan Baru"
            color="info"
            rounded-full
            small
            class="shadow-lg hover:shadow-xl transition-all duration-300"
          />
        </BaseButtons>
      </SectionTitleLineWithButton>

      <NotificationBar v-if="$page.props.flash.message" color="success" :icon="mdiCheck" class="mb-6 shadow-md">
        {{ $page.props.flash.message }}
      </NotificationBar>
      <NotificationBar v-if="$page.props.flash.error" color="danger" :icon="mdiClose" class="mb-6 shadow-md">
        {{ $page.props.flash.error }}
      </NotificationBar>
      <NotificationBar
        v-if="Number($page.props?.notifications?.mutasi_pending_count || 0) > 0"
        color="warning"
        :icon="mdiAlertCircle"
        class="mb-6 shadow-md"
      >
        Ada {{ Number($page.props?.notifications?.mutasi_pending_count || 0) }} pengajuan mutasi masuk (status MENUNGGU).
      </NotificationBar>

      <div class="mb-6 bg-white dark:bg-slate-800 rounded-2xl shadow-sm p-4 border border-gray-100 dark:border-slate-700">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="w-full md:w-1/3 relative">
                 <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <BaseIcon :path="mdiMagnify" class="text-gray-400" />
                 </div>
                 <input
                    v-model="search"
                    type="text"
                    placeholder="Cari Siswa..."
                    class="pl-10 w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white transition-all duration-300"
                 />
            </div>
            <div class="flex items-center gap-3 w-full md:w-auto">
                <select
                  v-model="selectedKelas"
                  class="rounded-xl border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-700 dark:border-slate-600 text-sm py-2 pl-4 pr-10 cursor-pointer transition-all duration-300 w-full md:w-auto"
                >
                  <option value="">Semua Kelas</option>
                  <option v-for="kelas in kelasOptions" :key="kelas.id" :value="kelas.id">
                    {{ kelas.nama_kelas }}
                  </option>
                </select>

                <div class="flex items-center gap-2 shrink-0">
                  <span class="text-sm font-medium text-gray-600 dark:text-gray-300 hidden sm:inline">Show:</span>
                  <select v-model="perPage" class="rounded-xl border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-700 dark:border-slate-600 text-sm py-2 pl-4 pr-10 cursor-pointer transition-all duration-300">
                      <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
                  </select>
                </div>
            </div>
        </div>
      </div>

      <CardBox class="mb-6 shadow-xl border-0 overflow-hidden rounded-2xl" has-table>
        <div class="px-4 py-2 bg-blue-600 dark:bg-blue-800 text-white flex items-center justify-between text-xs font-medium border-b border-blue-500">
            <button
                @click="scrollTable('left')"
                class="flex items-center px-3 py-1 bg-blue-700 hover:bg-blue-500 rounded-lg transition-colors shadow-sm"
                title="Geser Kiri"
            >
                <BaseIcon :path="mdiChevronLeft" size="18" />
                <span class="ml-1">Geser Kiri</span>
            </button>

            <div class="flex items-center mx-2 text-center opacity-90">
                <BaseIcon :path="mdiTransfer" class="mr-2 transform rotate-90" size="16" />
                <span class="hidden sm:inline">Gunakan tombol atau mouse untuk melihat kolom lain</span>
                <span class="sm:hidden">Geser tabel</span>
            </div>

            <button
                @click="scrollTable('right')"
                class="flex items-center px-3 py-1 bg-blue-700 hover:bg-blue-500 rounded-lg transition-colors shadow-sm"
                title="Geser Kanan"
            >
                <span class="mr-1">Geser Kanan</span>
                <BaseIcon :path="mdiChevronRight" size="18" />
            </button>
        </div>
        <div class="overflow-x-auto min-h-[500px] scroll-smooth" ref="tableContainer">
          <table class="w-full text-sm text-left">
            <thead class="text-sm font-semibold text-white uppercase bg-blue-600 dark:bg-blue-800 sticky top-0 z-10 shadow-md">
              <tr>
                <th class="px-6 py-4 rounded-tl-lg text-center w-12 border-r border-blue-500/30 text-white">No</th>
                <th class="px-6 py-4 border-r border-blue-500/30 text-white">Nama Siswa</th>
                <th class="px-6 py-4 border-r border-blue-500/30 text-white">Bengkel Awal</th>
                <th class="px-6 py-4 border-r border-blue-500/30 text-white">Bengkel Baru</th>
                <th class="px-6 py-4 border-r border-blue-500/30 text-white">Tanggal Pengajuan</th>
                <th class="px-6 py-4 text-center border-r border-blue-500/30 text-white">Status</th>
                <th class="px-6 py-4 text-center rounded-tr-lg text-white">Aksi</th>
              </tr>
            </thead>
            <tbody class="text-gray-500 dark:text-gray-400">
              <tr
                v-for="(item, index) in items"
                :key="item.id"
                class="bg-white border-b dark:bg-slate-800 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors duration-200"
                :class="{
                  'bg-yellow-50/60 dark:bg-yellow-900/10 border-l-4 border-l-yellow-400': item.status === 'pending',
                }"
              >
                <td class="text-center px-6 py-4 font-medium text-gray-900 dark:text-white">
                    {{ (props.mutasi.current_page - 1) * props.mutasi.per_page + index + 1 }}
                </td>
              <td class="px-6 py-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center mr-3 text-indigo-600 dark:text-indigo-400">
                        <BaseIcon :path="mdiAccountSchool" :size="20" />
                    </div>
                    <div>
                        <div class="font-bold text-gray-800 dark:text-gray-100">{{ item.siswa?.nama }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ item.siswa?.nisn }} | Kelas: {{ item.siswa?.kelas?.nama_kelas || '-' }} <span v-if="item.siswa?.kelas?.wali_kelas" class="text-indigo-600 dark:text-indigo-400 font-bold ml-1">(Wali: {{ item.siswa?.kelas?.wali_kelas }})</span></div>
                    </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ item.bengkel_asal?.nama_bengkel || '-' }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center gap-1">
                    <BaseIcon :path="mdiHumanMaleBoard" size="14" />
                    {{ item.pembimbing?.nama_guru || '-' }}
                </div>
              </td>
              <td class="px-6 py-4">
                 <div class="flex items-center">
                      <BaseIcon :path="mdiOfficeBuildingCog" class="mr-2 text-blue-500" :size="18" />
                      <span class="font-bold text-blue-600 dark:text-blue-400">{{ item.nama_bengkel_baru || item.bengkel_tujuan?.nama_bengkel || '-' }}</span>
                 </div>
                 <div v-if="item.nama_pemilik_bengkel_baru" class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Pemilik: <span class="font-medium">{{ item.nama_pemilik_bengkel_baru }}</span>
                 </div>
              </td>
              <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                <div class="flex flex-col">
                    <span class="font-medium">{{ new Date(item.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}</span>
                    <span class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">
                        {{ new Date(item.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                    </span>
                </div>
              </td>
              <td class="text-center px-6 py-4">
                <span
                  class="px-3 py-1 rounded-full text-xs font-bold inline-block border shadow-sm"
                  :class="{
                    'bg-yellow-50 text-yellow-700 border-yellow-200': item.status === 'pending',
                    'bg-blue-50 text-blue-700 border-blue-200': item.status === 'processing',
                    'bg-green-50 text-green-700 border-green-200': item.status === 'approved',
                    'bg-red-50 text-red-700 border-red-200': item.status === 'rejected'
                  }"
                >
                  {{ item.status === 'pending' ? 'MENUNGGU' : (item.status === 'processing' ? 'DIPROSES' : (item.status === 'approved' ? 'DISETUJUI' : 'DITOLAK')) }}
                </span>
              </td>
              <td class="text-center px-6 py-4">
                <div class="flex items-center justify-center gap-2">
                    <button
                        @click="showDetail(item)"
                        class="p-2 rounded-full bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/30 dark:text-blue-400 transition-colors"
                        title="Lihat Detail Informasi"
                    >
                        <BaseIcon :path="mdiEye" size="20" />
                    </button>

                    <button
                        v-if="item.status === 'pending' && $can('process', 'mutasi_pkl')"
                        @click="process(item.id)"
                        class="p-2 rounded-full bg-indigo-50 text-indigo-600 hover:bg-indigo-100 dark:bg-indigo-900/30 dark:text-indigo-400 transition-colors"
                        title="Proses Laporan"
                    >
                        <BaseIcon :path="mdiCheck" size="20" />
                    </button>

                    <button
                        v-if="(item.status === 'pending' || item.status === 'processing') && $can('approve', 'mutasi_pkl')"
                        @click="approve(item)"
                        class="p-2 rounded-full bg-green-100 text-green-600 hover:bg-green-200 dark:bg-green-900/30 dark:text-green-400 transition-colors"
                        title="Setujui & Penyesuaian Mutasi"
                    >
                        <BaseIcon :path="mdiCheck" size="20" />
                    </button>

                    <button
                        v-if="item.status === 'approved'"
                        @click="printP1(item.id)"
                        class="p-2 rounded-full bg-emerald-100 text-emerald-600 hover:bg-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 transition-colors"
                        title="Cetak Surat P-1"
                    >
                        <BaseIcon :path="mdiPrinter" size="20" />
                    </button>

                    <button
                        v-if="(item.status === 'pending' || item.status === 'processing') && $can('reject', 'mutasi_pkl')"
                        @click="reject(item.id)"
                        class="p-2 rounded-full bg-yellow-100 text-yellow-600 hover:bg-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400 transition-colors"
                        title="Tolak"
                    >
                        <BaseIcon :path="mdiClose" size="20" />
                    </button>
                    <button
                        v-if="$can('delete', 'mutasi_pkl')"
                        @click="destroy(item.id)"
                        class="p-2 rounded-full bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-900/30 dark:text-red-400 transition-colors"
                        title="Hapus"
                    >
                        <BaseIcon :path="mdiTrashCan" size="20" />
                    </button>
                </div>
              </td>
            </tr>
            <tr v-if="items.length === 0">
                <td colspan="7" class="text-center py-12">
                    <div class="flex flex-col items-center justify-center text-gray-500">
                        <BaseIcon :path="mdiTransfer" size="48" class="mb-4 text-gray-300" />
                        <p class="text-lg font-medium">Belum ada data mutasi.</p>
                        <p class="text-sm mt-1">Data pengajuan mutasi siswa akan muncul di sini.</p>
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
                :to="link.url"
                :disabled="!link.url"
                :color="link.active ? 'info' : 'whiteDark'"
                small
                class="transition-transform active:scale-95"
              />
            </BaseButtons>
            <small class="text-gray-500 font-medium">Halaman {{ props.mutasi.current_page }} dari {{ props.mutasi.last_page }}</small>
          </BaseLevel>
        </div>
      </CardBox>

      <!-- Modals -->
      <CardBoxModal
        v-model="isProcessModalActive"
        title="Proses Pengajuan Mutasi"
        buttonLabel="Proses & Terima Laporan"
        has-cancel
        @confirm="submitProcess"
        modal-class="w-11/12 max-w-lg"
      >
        <div class="mb-4 bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800 flex items-start">
            <BaseIcon :path="mdiAlertCircle" class="text-blue-500 mr-3 mt-0.5" />
            <div>
                <p class="text-sm text-blue-800 dark:text-blue-200 font-medium">Konfirmasi Proses</p>
                <p class="text-xs text-blue-600 dark:text-blue-300 mt-1">Anda akan memproses laporan ini sebagai "Laporan Diterima". Siswa akan diberitahu.</p>
            </div>
        </div>
        <FormField label="Instruksi Selanjutnya (Opsional)">
            <FormControl
                v-model="adminNote"
                type="textarea"
                placeholder="Berikan instruksi selanjutnya untuk siswa..."
            />
        </FormField>
      </CardBoxModal>

      <!-- Modal Review & Persetujuan Mutasi (Admin Review & Adjustment) -->


      <CardBoxModal
        v-model="isRejectModalActive"
        title="Tolak Mutasi PKL"
        buttonLabel="Tolak Pengajuan"
        buttonColor="danger"
        has-cancel
        @confirm="submitReject"
        modal-class="w-11/12 max-w-lg"
      >
        <div class="mb-4 bg-red-50 dark:bg-red-900/20 p-4 rounded-xl border border-red-100 dark:border-red-800 flex items-start">
            <BaseIcon :path="mdiAlertCircle" class="text-red-500 mr-3 mt-0.5" />
            <div>
                <p class="text-sm text-red-800 dark:text-red-200 font-medium">Konfirmasi Penolakan</p>
                <p class="text-xs text-red-600 dark:text-red-300 mt-1">Pengajuan akan ditolak dan siswa harus mengajukan ulang jika diperlukan.</p>
            </div>
        </div>
        <FormField label="Alasan Penolakan (Wajib)">
            <FormControl
                v-model="adminNote"
                type="textarea"
                placeholder="Berikan alasan penolakan..."
            />
        </FormField>
      </CardBoxModal>

      <!-- Modal Detail Mutasi -->
      <CardBoxModal
        v-model="isDetailModalActive"
        title="Detail Pengajuan Mutasi PKL"
        buttonLabel="Tutup"
        has-cancel
        @confirm="isDetailModalActive = false"
        modal-class="w-11/12 md:w-3/4 lg:w-2/3 max-w-4xl"
      >
        <div v-if="detailedItem" class="space-y-6 text-left p-2">
          <!-- Identitas Siswa -->
          <div class="border-b border-gray-100 dark:border-slate-700 pb-4">
            <h4 class="font-bold text-indigo-600 dark:text-indigo-400 text-sm uppercase tracking-wide mb-3">Identitas Siswa</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-xs text-gray-400 dark:text-gray-500 block">Nama Siswa</span>
                <span class="font-bold text-gray-800 dark:text-gray-100">{{ detailedItem.siswa?.nama }}</span>
              </div>
              <div>
                <span class="text-xs text-gray-400 dark:text-gray-500 block">NISN / Kelas</span>
                <span class="font-medium text-gray-800 dark:text-gray-100">
                  {{ detailedItem.siswa?.nisn || '-' }} / <span class="px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-700 font-bold text-xs">{{ detailedItem.siswa?.kelas?.nama_kelas || '-' }}</span> <span v-if="detailedItem.siswa?.kelas?.wali_kelas" class="text-xs text-indigo-600 dark:text-indigo-400 font-bold ml-2">(Wali: {{ detailedItem.siswa?.kelas?.wali_kelas }})</span>
                </span>
              </div>
            </div>
          </div>

          <!-- Bengkel Asal -->
          <div class="border-b border-gray-100 dark:border-slate-700 pb-4">
            <h4 class="font-bold text-indigo-600 dark:text-indigo-400 text-sm uppercase tracking-wide mb-3">PKL Saat Ini (Asal)</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-xs text-gray-400 dark:text-gray-500 block">Bengkel Asal</span>
                <span class="font-semibold text-gray-800 dark:text-gray-100">{{ detailedItem.bengkel_asal?.nama_bengkel || '-' }}</span>
              </div>
              <div>
                <span class="text-xs text-gray-400 dark:text-gray-500 block">Guru Pembimbing (Asal)</span>
                <span class="font-medium text-gray-800 dark:text-gray-100">{{ detailedItem.pembimbing?.nama_guru || '-' }}</span>
              </div>
            </div>
          </div>

          <!-- Bengkel Tujuan / Pengajuan Baru -->
          <div class="border-b border-gray-100 dark:border-slate-700 pb-4">
            <h4 class="font-bold text-blue-600 dark:text-blue-400 text-sm uppercase tracking-wide mb-3">Bengkel Tujuan Mutasi</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-xs text-gray-400 dark:text-gray-500 block">Nama Bengkel Baru</span>
                <span class="font-bold text-blue-600 dark:text-blue-400">{{ detailedItem.nama_bengkel_baru || detailedItem.bengkel_tujuan?.nama_bengkel || '-' }}</span>
              </div>
              <div v-if="detailedItem.pembimbing_tujuan">
                <span class="text-xs text-gray-400 dark:text-gray-500 block">Guru Pembimbing Baru</span>
                <span class="font-medium text-indigo-600 dark:text-indigo-400">{{ detailedItem.pembimbing_tujuan.nama_guru }}</span>
              </div>
              <div v-if="detailedItem.nama_pemilik_bengkel_baru">
                <span class="text-xs text-gray-400 dark:text-gray-500 block">Pemilik Bengkel Baru</span>
                <span class="font-medium text-gray-800 dark:text-gray-100">{{ detailedItem.nama_pemilik_bengkel_baru }}</span>
              </div>
              <div class="sm:col-span-2">
                <span class="text-xs text-gray-400 dark:text-gray-500 block mb-1">Alamat Bengkel Baru</span>
                <div class="bg-gray-50 dark:bg-slate-700/30 p-3 rounded-xl border border-gray-100 dark:border-slate-700 grid grid-cols-3 gap-2">
                  <div>
                    <span class="text-[10px] text-gray-400 block uppercase">Dusun</span>
                    <span class="font-semibold text-xs text-gray-700 dark:text-gray-200">{{ detailedItem.dusun || '-' }}</span>
                  </div>
                  <div>
                    <span class="text-[10px] text-gray-400 block uppercase">Desa</span>
                    <span class="font-semibold text-xs text-gray-700 dark:text-gray-200">{{ detailedItem.desa || '-' }}</span>
                  </div>
                  <div>
                    <span class="text-[10px] text-gray-400 block uppercase">Kecamatan</span>
                    <span class="font-semibold text-xs text-gray-700 dark:text-gray-200">{{ detailedItem.kecamatan || '-' }}</span>
                  </div>
                  <div v-if="detailedItem.google_maps_link_baru" class="col-span-3 mt-1.5 pt-1.5 border-t border-gray-200 dark:border-slate-600 flex justify-end">
                    <a :href="detailedItem.google_maps_link_baru" target="_blank" class="inline-flex items-center px-2 py-1 text-xs font-bold rounded text-blue-700 bg-blue-100 hover:bg-blue-200 transition-colors">
                      <BaseIcon :path="mdiMapMarker" size="14" class="mr-1" /> Lihat di Google Maps
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Alasan Mutasi -->
          <div class="border-b border-gray-100 dark:border-slate-700 pb-4">
            <h4 class="font-bold text-amber-600 dark:text-amber-400 text-sm uppercase tracking-wide mb-2">Alasan Pengajuan Mutasi</h4>
            <div class="bg-amber-50/50 dark:bg-amber-950/10 p-3 rounded-xl border border-amber-100 dark:border-amber-900/40">
              <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line italic">"{{ detailedItem.alasan }}"</p>
            </div>
          </div>

          <!-- Status & Log Metadata -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm pt-2">
            <div>
              <span class="text-xs text-gray-400 dark:text-gray-500 block">Tanggal Pengajuan</span>
              <span class="font-medium text-gray-800 dark:text-gray-200">
                {{ new Date(detailedItem.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                <span class="text-xs text-gray-400"> pukul {{ new Date(detailedItem.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}</span>
              </span>
            </div>
            <div>
              <span class="text-xs text-gray-400 dark:text-gray-500 block mb-1">Status Pengajuan</span>
              <span
                class="px-3 py-1 rounded-full text-xs font-bold inline-block border shadow-sm"
                :class="{
                  'bg-yellow-50 text-yellow-700 border-yellow-200': detailedItem.status === 'pending',
                  'bg-blue-50 text-blue-700 border-blue-200': detailedItem.status === 'processing',
                  'bg-green-50 text-green-700 border-green-200': detailedItem.status === 'approved',
                  'bg-red-50 text-red-700 border-red-200': detailedItem.status === 'rejected'
                }"
              >
                {{ detailedItem.status === 'pending' ? 'MENUNGGU' : (detailedItem.status === 'processing' ? 'DIPROSES' : (detailedItem.status === 'approved' ? 'DISETUJUI' : 'DITOLAK')) }}
              </span>
            </div>
            <div class="sm:col-span-2" v-if="detailedItem.admin || detailedItem.admin_note">
              <div class="bg-gray-50 dark:bg-slate-700/20 p-3 rounded-xl border border-gray-100 dark:border-slate-700">
                <span class="text-xs text-gray-400 dark:text-gray-500 block" v-if="detailedItem.admin">Diproses Oleh: <span class="font-bold text-gray-700 dark:text-gray-300">{{ detailedItem.admin.name }}</span></span>
                <span class="text-xs text-gray-400 dark:text-gray-500 block mt-1.5" v-if="detailedItem.admin_note">Catatan Keputusan: <span class="font-medium text-gray-700 dark:text-gray-300 italic">"{{ detailedItem.admin_note }}"</span></span>
              </div>
            </div>
          </div>
        </div>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template>
