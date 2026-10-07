<script setup>
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import { mdiSchool, mdiPlus, mdiPencil, mdiTrashCan, mdiAlertCircle, mdiAccount, mdiMagnify, mdiFilterOutline, mdiTableLarge } from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import { computed, ref, watch } from 'vue'

const props = defineProps({
  pembimbings: {
    type: Object,
    default: () => ({}),
  },
  jabatanOptions: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const page = usePage()

const form = useForm({
  nama_guru: '',
  jabatan_pembimbing_id: '',
})

const deleteForm = useForm({})

const isModalActive = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const selectedJabatan = ref(null)

const search = ref(props.filters.search || '')
const jabatanFilter = ref(props.filters.jabatan_pembimbing_id || '')
const perPage = ref(props.filters.per_page || 10)

const perPageOptions = [
  { value: 10, label: '10' },
  { value: 20, label: '20' },
  { value: 50, label: '50' },
  { value: 100, label: '100' },
]

const pembimbingData = computed(() =>
  props.pembimbings && Array.isArray(props.pembimbings.data) ? props.pembimbings.data : [],
)

const pembimbingLinks = computed(() =>
  props.pembimbings && Array.isArray(props.pembimbings.links) ? props.pembimbings.links : [],
)

const jabatanSelectOptions = computed(() =>
  (props.jabatanOptions || []).map((j) => ({
    id: j.id,
    label: j.nama_jabatan,
  })),
)

const openModal = () => {
  isEditing.value = false
  editingId.value = null
  selectedJabatan.value = null
  form.reset()
  form.clearErrors()
  isModalActive.value = true
}

const closeModal = () => {
  isModalActive.value = false
  selectedJabatan.value = null
  form.reset()
  form.clearErrors()
}

const syncSelectedToForm = () => {
  const val = selectedJabatan.value
  form.jabatan_pembimbing_id = val && typeof val === 'object' ? (val.id ?? val.value ?? '') : (val ?? '')
}

const submit = () => {
  syncSelectedToForm()

  if (isEditing.value && editingId.value) {
    form.put(route('admin.pembimbing.update', editingId.value), {
      onSuccess: () => {
        closeModal()
      },
    })
  } else {
    form.post(route('admin.pembimbing.store'), {
      onSuccess: () => {
        closeModal()
      },
    })
  }
}

const startEdit = (row) => {
  isEditing.value = true
  editingId.value = row.id
  form.nama_guru = row.nama_guru
  selectedJabatan.value = row.jabatan_pembimbing?.id ?? row.jabatan_pembimbing_id ?? null
  form.jabatan_pembimbing_id = selectedJabatan.value || ''
  syncSelectedToForm()
  isModalActive.value = true
}

const deletePembimbing = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus data pembimbing ini?')) {
    deleteForm.delete(route('admin.pembimbing.destroy', id))
  }
}

const applyFilters = () => {
  router.get(route('admin.pembimbing.index'), {
    search: search.value,
    jabatan_pembimbing_id: jabatanFilter.value,
    per_page: perPage.value,
  }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  search.value = ''
  jabatanFilter.value = ''
  perPage.value = 10
  applyFilters()
}

const handleSearch = () => {
  applyFilters()
}

const handlePerPageChange = () => {
  applyFilters()
}

watch(() => props.filters, (newFilters) => {
  search.value = newFilters.search || ''
  jabatanFilter.value = newFilters.jabatan_pembimbing_id || ''
  perPage.value = newFilters.per_page || 10
}, { immediate: true, deep: true })
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Data Pembimbing" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiSchool" title="Data Pembimbing" main>
        <BaseButton
          :icon="mdiPlus"
          label="Tambah Pembimbing"
          color="contrast"
          rounded-full
          small
          @click="openModal"
        />
      </SectionTitleLineWithButton>

      <NotificationBar
        v-if="$page.props.flash && $page.props.flash.message"
        color="success"
        :icon="mdiAlertCircle"
      >
        {{ $page.props.flash.message }}
      </NotificationBar>

      <CardBox class="mb-6 max-w-5xl mx-auto">
        <div class="p-4 border-b border-gray-100 dark:border-slate-800">
          <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">
              <div class="relative min-w-[200px] sm:min-w-[260px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                  </svg>
                </div>
                <input
                  v-model="search"
                  type="text"
                  placeholder="Cari nama, NIP, email..."
                  class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition"
                  @keyup.enter="handleSearch"
                />
              </div>
              <div class="relative min-w-[180px]">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                  </svg>
                </div>
                <select
                  v-model="jabatanFilter"
                  class="w-full pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition appearance-none cursor-pointer"
                  @change="applyFilters"
                >
                  <option value="">Semua Jabatan</option>
                  <option v-for="j in jabatanOptions" :key="j.id" :value="j.id">{{ j.nama_jabatan }}</option>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                  <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </div>
              <BaseButton
                v-if="search || jabatanFilter || perPage != 10"
                label="Reset"
                color="info"
                outline
                small
                @click="resetFilters"
              />
            </div>
            <div class="flex items-center gap-3 w-full lg:w-auto">
              <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 whitespace-nowrap">Tampilkan</span>
                <select
                  v-model="perPage"
                  class="w-20 px-2 py-2 rounded-lg border border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition appearance-none cursor-pointer"
                  @change="handlePerPageChange"
                >
                  <option v-for="opt in perPageOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                </select>
                <span class="text-xs text-gray-500">data</span>
              </div>
            </div>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table>
            <thead>
              <tr>
                <th class="w-16 text-center">No</th>
                <th>Nama Guru</th>
                <th>Jabatan</th>
                <th class="w-40 text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in pembimbingData" :key="row.id">
                <td class="text-center" data-label="No">
                  {{ index + 1 + ((props.pembimbings.current_page - 1) * props.pembimbings.per_page) }}
                </td>
                <td data-label="Nama Guru">
                  <div class="flex items-center gap-4">
                    <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 text-sm font-semibold shadow-sm ring-2 ring-slate-200 dark:ring-slate-700 shrink-0">
                      {{ row.nama_guru?.charAt(0)?.toUpperCase() || '?' }}
                    </div>
                    <div class="flex flex-col">
                      <span class="font-medium text-slate-700 dark:text-slate-200">{{ row.nama_guru }}</span>
                      <span class="text-xs text-slate-500">{{ row.account?.email || row.user?.email }}</span>
                    </div>
                  </div>
                </td>
                <td data-label="Jabatan">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                    {{ row.jabatan_pembimbing?.nama_jabatan || '-' }}
                  </span>
                </td>
                <td class="before:hidden lg:w-1 whitespace-nowrap text-right">
                  <BaseButtons type="justify-start lg:justify-end" no-wrap>
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
                      @click="deletePembimbing(row.id)"
                    />
                  </BaseButtons>
                </td>
              </tr>
              <tr v-if="pembimbingData.length === 0">
                <td colspan="4" class="text-center py-8 text-gray-500">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="text-lg font-medium text-gray-400">Data pembimbing belum tersedia</span>
                    <p class="text-sm mt-1">Silakan tambahkan pembimbing baru.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="pembimbingLinks.length > 3"
          class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800"
        >
          <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <span class="text-xs text-gray-500">
              Menampilkan {{ props.pembimbings.from || 0 }} - {{ props.pembimbings.to || 0 }} dari {{ props.pembimbings.total || 0 }} data
            </span>
            <BaseButtons>
              <BaseButton
                v-for="(link, key) in pembimbingLinks"
                :key="key"
                :active="link.active"
                :label="link.label"
                :color="link.active ? 'lightDark' : 'whiteDark'"
                :disabled="!link.url"
                small
                @click="link.url && router.visit(link.url)"
                v-html="link.label"
              />
            </BaseButtons>
          </div>
        </div>
        <div v-else class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
          <span class="text-xs text-gray-500">
            Menampilkan {{ props.pembimbings.from || 0 }} - {{ props.pembimbings.to || 0 }} dari {{ props.pembimbings.total || 0 }} data
          </span>
        </div>
      </CardBox>

      <CardBoxModal
        v-model="isModalActive"
        :title="isEditing ? 'Edit Pembimbing' : 'Tambah Pembimbing'"
        :button="isEditing ? 'info' : 'contrast'"
        :button-label="isEditing ? 'Simpan Perubahan' : 'Simpan'"
        has-cancel
        @confirm="submit"
        @cancel="closeModal"
      >
        <FormField label="Nama Guru" :error="form.errors.nama_guru">
          <FormControl
            v-model="form.nama_guru"
            placeholder="Contoh: Budi Santoso"
          />
        </FormField>

        <FormField label="Jabatan" :error="form.errors.jabatan_pembimbing_id">
          <FormControl
            v-model="selectedJabatan"
            :options="jabatanSelectOptions"
            type="select"
            placeholder="Pilih Jabatan"
          />
        </FormField>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template>
