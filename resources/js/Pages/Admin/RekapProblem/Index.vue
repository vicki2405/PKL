<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import {
  mdiAlertCircle,
  mdiPlus,
  mdiMagnify,
  mdiTrashCan,
  mdiPencil,
  mdiCalendar,
  mdiAccountSchool,
  mdiDomain,
  mdiHumanMaleBoard,
  mdiCalendarToday,
  mdiCalendarMonth,
  mdiGavel,
  mdiFileExcel,
  mdiFilePdfBox,
  mdiFilter,
  mdiMapMarker,
  mdiAccountTie,
  mdiAlert,
  mdiCheckCircle,
  mdiEye
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
import CardBoxWidget from '@admin/components/CardBoxWidget.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import { computed, ref, watch } from 'vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  problems: {
    type: Object,
    default: () => ({}),
  },
  stats: {
    type: Object,
    default: () => ({ total: 0, today: 0, month: 0 }),
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
})

const search = ref(props.filters.search || '')
const perPage = ref(props.filters.per_page || 10)
const tanggal = ref(props.filters.tanggal || '')
const kelasId = ref(props.filters.kelas_id || '')
const pembimbingId = ref(props.filters.pembimbing_id || '')

const perPageOptions = [10, 20, 50, 100, 250, 500]

const isDetailModalActive = ref(false)
const detailedItem = ref(null)
const isDeleteModalActive = ref(false)
const itemToDelete = ref(null)

const showDetail = (item) => {
  detailedItem.value = item
  isDetailModalActive.value = true
}

const confirmDelete = (item) => {
  itemToDelete.value = item
  isDeleteModalActive.value = true
}

const deleteItem = () => {
  if (itemToDelete.value) {
    router.delete(route('admin.rekap_problem.destroy', itemToDelete.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        isDeleteModalActive.value = false
        itemToDelete.value = null
      },
    })
  }
}

const computedKelasOptions = computed(() => [
    { id: '', label: 'Semua Kelas' },
    ...props.kelasOptions
])

const computedPembimbingOptions = computed(() => [
    { id: '', label: 'Semua Pembimbing' },
    ...props.pembimbingOptions
])

const updateFilters = debounce(() => {
  router.get(
    route('admin.rekap_problem.index'),
    {
      search: search.value,
      per_page: perPage.value,
      tanggal: tanggal.value,
      kelas_id: kelasId.value,
      pembimbing_id: pembimbingId.value,
    },
    { preserveState: true, replace: true }
  )
}, 300)

watch([search, perPage, tanggal, kelasId, pembimbingId], () => {
  updateFilters()
})

const items = computed(() => props.problems?.data || [])
const links = computed(() => {
    if (!props.problems?.links) return []
    return props.problems.links.map((link) => {
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

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const [year, month, day] = String(dateString).slice(0, 10).split('-')
  if (year && month && day) {
    const d = new Date(Number(year), Number(month) - 1, Number(day))
    return d.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' })
  }
  return dateString
}

const formatWeekday = (dateString) => {
  if (!dateString) return ''
  const [year, month, day] = String(dateString).slice(0, 10).split('-')
  if (year && month && day) {
    const d = new Date(Number(year), Number(month) - 1, Number(day))
    return d.toLocaleDateString('id-ID', { weekday: 'long' })
  }
  return ''
}

const exportData = (type) => {
    const params = new URLSearchParams({
        type: type,
        search: search.value,
        tanggal: tanggal.value,
        kelas_id: kelasId.value,
        pembimbing_id: pembimbingId.value
    }).toString()

    window.location.href = `${route('admin.rekap_problem.export')}?${params}`
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Rekap Problem Siswa" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiAlertCircle"
        title="Rekap Problem Siswa"
        main
      >
        <div class="flex items-center gap-2">
            <BaseButton
                @click="exportData('excel')"
                :icon="mdiFileExcel"
                label="Excel"
                color="success"
                permission="rekap_problem.export_excel"
                rounded-full
                small
            />
            <BaseButton
                @click="exportData('pdf')"
                :icon="mdiFilePdfBox"
                label="PDF"
                color="danger"
                permission="rekap_problem.export_pdf"
                rounded-full
                small
            />
            <BaseButton
                :to="route('admin.rekap_problem.create')"
                :icon="mdiPlus"
                label="Input Data"
                color="contrast"
                rounded-full
                small
            />
        </div>
      </SectionTitleLineWithButton>

      <NotificationBar v-if="$page.props.flash.message" color="success" :icon="mdiAlertCircle">
        {{ $page.props.flash.message }}
      </NotificationBar>

      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 mb-6">
        <CardBoxWidget
          :number="props.stats.total || 0"
          :icon="mdiAlertCircle"
          label="Total Problem"
          color="text-indigo-500"
        />
        <CardBoxWidget
          :number="props.stats.today || 0"
          :icon="mdiCalendarToday"
          label="Hari Ini"
          color="text-emerald-500"
        />
        <CardBoxWidget
          :number="props.stats.month || 0"
          :icon="mdiCalendarMonth"
          label="Bulan Ini"
          color="text-amber-500"
        />
      </div>

      <CardBox class="mb-6 rounded-2xl shadow-sm border border-slate-200 dark:border-slate-800" has-table>
        <div class="p-4 space-y-4 bg-gray-50 dark:bg-slate-800/50">
            <!-- Filter Section -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <FormField class="mb-0">
                    <FormControl
                        v-model="tanggal"
                        type="date"
                        :icon="mdiCalendar"
                        placeholder="Filter Tanggal"
                        transparent
                    />
                </FormField>
                <FormField class="mb-0">
                    <FormControl
                        v-model="kelasId"
                        :options="computedKelasOptions"
                        :icon="mdiDomain"
                        transparent
                    />
                </FormField>
                <FormField class="mb-0">
                    <FormControl
                        v-model="pembimbingId"
                        :options="computedPembimbingOptions"
                        :icon="mdiHumanMaleBoard"
                        transparent
                    />
                </FormField>
                <FormField class="mb-0">
                    <FormControl
                        v-model="search"
                        :icon="mdiMagnify"
                        placeholder="Cari siswa / bengkel..."
                        transparent
                    />
                </FormField>
            </div>

            <div class="flex justify-end border-t border-gray-200 dark:border-gray-700 pt-3">
                <div class="w-full md:w-32">
                    <FormControl
                        v-model="perPage"
                        type="select"
                        :options="perPageOptions"
                        small
                    />
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-700 dark:text-gray-400 border-b dark:border-gray-600">
                    <tr>
                        <th scope="col" class="px-6 py-4 w-12 text-center">No</th>
                        <th scope="col" class="px-6 py-4">Tanggal</th>
                        <th scope="col" class="px-6 py-4">Siswa</th>
                        <th scope="col" class="px-6 py-4">Bengkel</th>
                        <th scope="col" class="px-6 py-4">Pembimbing</th>
                        <th scope="col" class="px-6 py-4 text-center w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <tr v-if="items.length === 0">
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <BaseIcon :path="mdiAlertCircle" size="48" class="text-gray-300 mb-2"/>
                                <p>Belum ada data problem.</p>
                            </div>
                        </td>
                    </tr>
                    <tr v-for="(item, index) in items" :key="item.id" class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white text-center align-top">
                            {{ (problems.current_page - 1) * problems.per_page + index + 1 }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap align-top">
                            <span class="text-gray-700 dark:text-gray-300 font-medium block mb-1">
                                {{ formatDate(item.tanggal) }}
                            </span>
                            <span class="text-xs text-gray-500">
                                {{ formatWeekday(item.tanggal) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 align-top">
                            <div class="font-bold text-gray-800 dark:text-gray-100">{{ item.siswa?.nama }}</div>
                            <div class="text-xs text-gray-500 font-mono mt-0.5">{{ item.siswa?.nisn || '-' }} | Kelas: {{ item.siswa?.kelas?.nama_kelas || '-' }} <span v-if="item.siswa?.kelas?.wali_kelas" class="text-indigo-600 dark:text-indigo-400 font-bold ml-1">(Wali: {{ item.siswa?.kelas?.wali_kelas }})</span></div>
                        </td>
                        <td class="px-6 py-4 align-top">
                          <div class="flex items-center">
                            <BaseIcon :path="mdiMapMarker" size="16" class="text-amber-500 mr-1.5 flex-shrink-0" />
                            <span class="text-gray-700 dark:text-gray-300">{{ item.bengkel?.nama_bengkel }}</span>
                          </div>
                        </td>
                        <td class="px-6 py-4 align-top">
                          <div class="flex items-center">
                            <BaseIcon :path="mdiAccountTie" size="16" class="text-emerald-500 mr-1.5 flex-shrink-0" />
                            <span class="text-gray-700 dark:text-gray-300">{{ item.pembimbing?.nama_guru }}</span>
                          </div>
                        </td>
                        <td class="px-6 py-4 text-center align-top">
                            <BaseButtons type="justify-center" no-wrap>
                                <BaseButton
                                    color="success"
                                    :icon="mdiEye"
                                    small
                                    @click="showDetail(item)"
                                />
                                <BaseButton
                                    :to="route('admin.rekap_problem.edit', item.id)"
                                    color="info"
                                    :icon="mdiPencil"
                                    small
                                />
                                <BaseButton
                                    color="danger"
                                    :icon="mdiTrashCan"
                                    small
                                    @click="confirmDelete(item)"
                                />
                            </BaseButtons>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-if="items.length > 0" class="p-3 lg:px-6 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
            <BaseLevel>
                <BaseButtons>
                    <BaseButton
                        v-for="link in links"
                        :key="link.label"
                        :active="link.active"
                        :label="link.label"
                        :color="link.active ? 'lightDark' : 'whiteDark'"
                        small
                        @click="link.url ? router.visit(link.url) : null"
                        :disabled="!link.url"
                    />
                </BaseButtons>
                <small class="text-gray-500">Halaman {{ problems.current_page }} dari {{ problems.last_page }}</small>
            </BaseLevel>
        </div>
      </CardBox>

      <!-- Modal Detail Problem -->
      <CardBoxModal
        v-model="isDetailModalActive"
        title="Detail Masalah & Tindakan Siswa"
        buttonLabel="Tutup"
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

          <!-- Lokasi PKL & Pembimbing -->
          <div class="border-b border-gray-100 dark:border-slate-700 pb-4">
            <h4 class="font-bold text-indigo-600 dark:text-indigo-400 text-sm uppercase tracking-wide mb-3">Lokasi PKL & Pembimbing</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
              <div>
                <span class="text-xs text-gray-400 dark:text-gray-500 block">Tempat Magang (Bengkel)</span>
                <span class="font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-1">
                  <BaseIcon :path="mdiDomain" size="14" class="text-amber-500" />
                  {{ detailedItem.bengkel?.nama_bengkel || '-' }}
                </span>
              </div>
              <div>
                <span class="text-xs text-gray-400 dark:text-gray-500 block">Guru Pembimbing</span>
                <span class="font-medium text-gray-800 dark:text-gray-100 flex items-center gap-1">
                  <BaseIcon :path="mdiAccountTie" size="14" class="text-emerald-500" />
                  {{ detailedItem.pembimbing?.nama_guru || '-' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Kasus Masalah -->
          <div class="border-b border-gray-100 dark:border-slate-700 pb-4">
            <h4 class="font-bold text-red-600 dark:text-red-400 text-sm uppercase tracking-wide mb-2 flex items-center gap-1">
              <BaseIcon :path="mdiAlert" size="16" />
              <span>Deskripsi Masalah</span>
            </h4>
            <div class="bg-red-50/50 dark:bg-red-950/10 p-3 rounded-xl border border-red-100 dark:border-red-900/40">
              <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ detailedItem.deskripsi_masalah }}</p>
            </div>
          </div>

          <!-- Tindakan / Solusi -->
          <div class="border-b border-gray-100 dark:border-slate-700 pb-4">
            <h4 class="font-bold text-blue-600 dark:text-blue-400 text-sm uppercase tracking-wide mb-2 flex items-center gap-1">
              <BaseIcon :path="mdiCheckCircle" size="16" />
              <span>Tindakan / Solusi Masalah</span>
            </h4>
            <div class="bg-blue-50/50 dark:bg-blue-950/10 p-3 rounded-xl border border-blue-100 dark:border-blue-900/40">
              <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed" v-if="detailedItem.deskripsi_tindakan">{{ detailedItem.deskripsi_tindakan }}</p>
              <span class="text-xs text-gray-400 italic" v-else>- Belum ada tindakan -</span>
            </div>
          </div>

          <!-- Saksi / Sanksi -->
          <div class="border-b border-gray-100 dark:border-slate-700 pb-4" v-if="detailedItem.saksi">
            <h4 class="font-bold text-slate-600 dark:text-slate-400 text-sm uppercase tracking-wide mb-2 flex items-center gap-1">
              <BaseIcon :path="mdiGavel" size="16" />
              <span>Sanksi / Saksi</span>
            </h4>
            <div class="bg-slate-50 dark:bg-slate-700/30 p-3 rounded-xl border border-slate-100 dark:border-slate-700">
              <p class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ detailedItem.saksi }}</p>
            </div>
          </div>

          <!-- Metadata Kejadian -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs pt-2">
            <div>
              <span class="text-xs text-gray-400 dark:text-gray-500 block">Tanggal Kejadian</span>
              <span class="font-medium text-gray-800 dark:text-gray-200">
                {{ formatDate(detailedItem.tanggal) }}
                <span class="text-xs text-gray-400" v-if="formatWeekday(detailedItem.tanggal)"> ({{ formatWeekday(detailedItem.tanggal) }})</span>
              </span>
            </div>
            <div>
              <span class="text-xs text-gray-400 dark:text-gray-500 block" v-if="detailedItem.admin">Dibuat Oleh</span>
              <span class="font-bold text-gray-800 dark:text-gray-200" v-if="detailedItem.admin">{{ detailedItem.admin.name }}</span>
            </div>
          </div>
        </div>
      </CardBoxModal>
      <!-- Modal Konfirmasi Hapus Problem -->
      <CardBoxModal
        v-model="isDeleteModalActive"
        title="Konfirmasi Hapus Data"
        button="danger"
        buttonLabel="Hapus Data"
        has-cancel
        @confirm="deleteItem"
      >
        <p>Apakah Anda yakin ingin menghapus data problem siswa <strong>{{ itemToDelete?.siswa?.nama || 'ini' }}</strong>?</p>
        <p class="text-xs text-gray-500 mt-2">Tindakan ini tidak dapat dibatalkan.</p>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template>
