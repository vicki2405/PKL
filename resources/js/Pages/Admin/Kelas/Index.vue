<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { mdiSchool, mdiTrashCan, mdiAlertCircle, mdiPlus, mdiPencil, mdiDomain, mdiFormatListNumbered, mdiClose, mdiMagnify, mdiRefresh, mdiAccount, mdiCellphone } from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import CardBox from '@admin/components/CardBox.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import { computed, ref, watch } from 'vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  kelas: {
    type: Object,
    default: () => ({}),
  },
  jurusanOptions: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const form = useForm({
  jurusan_id: '',
  nama_kelas: '',
  tingkat: '',
  keterangan: '',
  wali_kelas: '',
  no_wa_wali: '',
})

const filterForm = useForm({
  nama_kelas: props.filters?.nama_kelas || '',
  jurusan_id: props.filters?.jurusan_id || '',
  per_page: Number(props.filters?.per_page || 5),
})

const perPageOptions = [5, 10, 20, 50, 100]

const selectedJurusanFilter = ref(
  props.filters?.jurusan_id
    ? (props.jurusanOptions || []).find((j) => j.id == props.filters.jurusan_id)
      ? {
          id: props.filters.jurusan_id,
          label: (props.jurusanOptions || []).find((j) => j.id == props.filters.jurusan_id).nama_jurusan,
        }
      : null
    : null,
)

watch(
  selectedJurusanFilter,
  (val) => {
    filterForm.jurusan_id = val?.id || ''
  }
)

watch(
  filterForm,
  debounce(() => {
    router.get(route('admin.kelas.index'), filterForm.data(), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    })
  }, 300),
  { deep: true },
)

const isModalActive = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const deleteForm = useForm({})

const selectedJurusan = ref(null)
const selectedTingkat = ref(null)

const kelasData = computed(() =>
  props.kelas && Array.isArray(props.kelas.data) ? props.kelas.data : [],
)

const kelasLinks = computed(() =>
  props.kelas && Array.isArray(props.kelas.links) ? props.kelas.links : [],
)

const jurusanSelectOptions = computed(() =>
  (props.jurusanOptions || []).map((j) => ({
    id: j.id,
    label: j.kode_jurusan ? `${j.kode_jurusan} - ${j.nama_jurusan}` : j.nama_jurusan,
  })),
)

const tingkatOptions = [
  { id: 'X', label: 'X' },
  { id: 'XI', label: 'XI' },
  { id: 'XII', label: 'XII' },
  { id: 'XIII', label: 'XIII' },
]

const tingkatValueMap = {
  '10': 'X',
  '11': 'XI',
  '12': 'XII',
  '13': 'XIII',
  X: 'X',
  XI: 'XI',
  XII: 'XII',
  XIII: 'XIII',
}

const formatTingkat = (value) => tingkatValueMap[value] || value || '-'

watch(
  selectedJurusan,
  (val) => {
    form.jurusan_id = val || ''
  },
  { immediate: true },
)

watch(
  selectedTingkat,
  (val) => {
    form.tingkat = val || ''
  },
  { immediate: true },
)

const openModal = () => {
  isEditing.value = false
  editingId.value = null
  form.reset()
  form.clearErrors()
  selectedJurusan.value = null
  selectedTingkat.value = null
  isModalActive.value = true
}

const closeModal = () => {
  isModalActive.value = false
  form.reset()
  form.clearErrors()
  selectedJurusan.value = null
  selectedTingkat.value = null
}

const submit = () => {
  if (isEditing.value && editingId.value) {
    form.put(route('admin.kelas.update', editingId.value), {
      onSuccess: () => {
        closeModal()
      },
    })
  } else {
    form.post(route('admin.kelas.store'), {
      onSuccess: () => {
        closeModal()
      },
    })
  }
}

const startEdit = (row) => {
  isEditing.value = true
  editingId.value = row.id
  selectedJurusan.value = row.jurusan_id || null
  selectedTingkat.value = tingkatValueMap[row.tingkat] || row.tingkat || null

  form.nama_kelas = row.nama_kelas
  form.keterangan = row.keterangan
  form.wali_kelas = row.wali_kelas || ''
  form.no_wa_wali = row.no_wa_wali || ''
  isModalActive.value = true
}

const deleteKelas = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus data kelas ini?')) {
    deleteForm.delete(route('admin.kelas.destroy', id))
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Data Kelas" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiSchool" title="Data Kelas" main>
        <BaseButton
          :icon="mdiPlus"
          label="Tambah Kelas"
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

      <CardBox class="mb-6" has-table>
        <div class="p-3 sm:p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
          <div class="w-full sm:w-1/3 relative">
            <input
              v-model="filterForm.nama_kelas"
              type="text"
              placeholder="Cari Nama Kelas..."
              class="w-full pl-10 pr-3 py-2.5 sm:py-2 bg-transparent border-b border-gray-300 dark:border-gray-600 focus:border-blue-500 focus:ring-0 focus:outline-none transition-colors placeholder-gray-400 text-sm sm:text-base"
            />
            <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
              <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24">
                <path fill="currentColor" :d="mdiMagnify" />
              </svg>
            </div>
          </div>
          <div class="w-full sm:w-2/3 flex flex-col sm:flex-row gap-3 justify-end">
            <div class="w-full sm:w-1/2">
              <FormControl
                v-model="selectedJurusanFilter"
                :options="jurusanSelectOptions"
                :icon="mdiDomain"
                type="select"
                placeholder="Semua Jurusan"
              />
            </div>
            <div class="w-full sm:w-1/4">
              <FormControl
                v-model="filterForm.per_page"
                :options="perPageOptions"
                :icon="mdiFormatListNumbered"
                type="select"
              />
            </div>
          </div>
        </div>
        <div class="overflow-x-auto touch-pan-x">
        <table class="w-full min-w-[500px] sm:min-w-0">
          <thead>
            <tr>
              <th>Nama Kelas</th>
              <th>Tingkat</th>
              <th>Jurusan</th>
              <th>Wali Kelas</th>
              <th>Keterangan</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in kelasData" :key="row.id">
              <td data-label="Nama Kelas">
                <span class="font-bold">{{ row.nama_kelas }}</span>
              </td>
              <td data-label="Tingkat">
                {{ formatTingkat(row.tingkat) }}
              </td>
              <td data-label="Jurusan">
                {{ row.jurusan?.nama_jurusan || '-' }}
              </td>
              <td data-label="Wali Kelas">
                <div v-if="row.wali_kelas" class="font-semibold text-slate-800 dark:text-slate-200">
                  {{ row.wali_kelas }}
                </div>
                <div v-if="row.no_wa_wali" class="text-xs text-slate-500 dark:text-slate-400">
                  WA: {{ row.no_wa_wali }}
                </div>
                <span v-if="!row.wali_kelas && !row.no_wa_wali">-</span>
              </td>
              <td data-label="Keterangan">
                {{ row.keterangan || '-' }}
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
                    @click="deleteKelas(row.id)"
                  />
                </BaseButtons>
              </td>
            </tr>
            <tr v-if="kelasData.length === 0">
              <td colspan="6" class="text-center py-8 text-gray-500">
                <div class="flex flex-col items-center justify-center">
                  <span class="text-lg font-medium">Data kelas belum tersedia</span>
                  <p class="text-sm mt-1">Silakan tambahkan kelas baru.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        </div>
        <div class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
          <BaseLevel>
            <div class="text-gray-500 dark:text-gray-400 text-sm">
              Menampilkan <span class="font-bold">{{ kelas.from || 0 }}</span> hingga <span class="font-bold">{{ kelas.to || 0 }}</span> dari <span class="font-bold">{{ kelas.total || 0 }}</span> data
            </div>
            <BaseButtons v-if="kelasLinks.length > 3">
              <BaseButton
                v-for="(link, key) in kelasLinks"
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
          </BaseLevel>
        </div>
      </CardBox>

      <CardBoxModal
        v-model="isModalActive"
        :title="isEditing ? 'Edit Kelas' : 'Tambah Kelas'"
        :button="isEditing ? 'info' : 'contrast'"
        :button-label="isEditing ? 'Simpan Perubahan' : 'Simpan'"
        has-cancel
        @confirm="submit"
        @cancel="closeModal"
      >
        <FormField label="Jurusan" :error="form.errors.jurusan_id">
          <FormControl
            v-model="selectedJurusan"
            :options="jurusanSelectOptions"
            type="select"
            :icon="mdiDomain"
            placeholder="Pilih Jurusan"
          />
        </FormField>

        <FormField label="Tingkat" :error="form.errors.tingkat">
          <FormControl
            v-model="selectedTingkat"
            :options="tingkatOptions"
            type="select"
            :icon="mdiFormatListNumbered"
            placeholder="Pilih Tingkat"
          />
        </FormField>

        <FormField label="Nama Kelas" :error="form.errors.nama_kelas">
          <FormControl
            v-model="form.nama_kelas"
            placeholder="Contoh: X TKJ 1"
            :icon="mdiSchool"
          />
        </FormField>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormField label="Wali Kelas" :error="form.errors.wali_kelas">
            <FormControl
              v-model="form.wali_kelas"
              placeholder="Nama Wali Kelas"
              :icon="mdiAccount"
            />
          </FormField>

          <FormField label="No WA Wali Kelas" :error="form.errors.no_wa_wali">
            <FormControl
              v-model="form.no_wa_wali"
              placeholder="Contoh: 08123456789"
              :icon="mdiCellphone"
            />
          </FormField>
        </div>

        <FormField label="Keterangan" :error="form.errors.keterangan">
          <FormControl
            v-model="form.keterangan"
            placeholder="Keterangan tambahan (opsional)"
            type="textarea"
          />
        </FormField>
      </CardBoxModal>

    </SectionMain>
  </LayoutAuthenticated>
</template>
