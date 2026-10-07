<script setup>
import axios from 'axios'
import { Head, useForm } from '@inertiajs/vue3'
import {
  mdiFileDocumentEdit,
  mdiOfficeBuildingCog,
  mdiSchool,
  mdiAccountSchool,
  mdiHumanMaleBoard,
  mdiCalendarRange,
  mdiAlertCircle,
  mdiPlus,
  mdiTrashCan,
  mdiAccountMultiplePlus,
  mdiArrowLeft
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import FormControlSearchable from '@admin/components/FormControlSearchable.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import { computed, ref, watch } from 'vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  bengkelOptions: {
    type: Array,
    default: () => [],
  },
  kelasOptions: {
    type: Array,
    default: () => [],
  },
  siswaOptions: {
    type: Array,
    default: () => [],
  },
  pembimbingOptions: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  bengkel_id: '',
  siswa_ids: [],
  pembimbing_id: '',
})

const selectedBengkel = ref('')
const selectedKelas = ref('')
const selectedPembimbing = ref('')
const tempSelectedSiswaId = ref(null)
const isStudentModalActive = ref(false)
const modalSelectedKelas = ref(null)
const siswaOptionsState = ref([])
const siswaSearch = ref('')
const isLoadingSiswaOptions = ref(false)
const selectedSiswaRecords = ref([])

// Options for selects
const bengkelSelectOptions = computed(() =>
  props.bengkelOptions.map((b) => ({
    id: b.id,
    label: b.kode_bengkel ? `${b.kode_bengkel} - ${b.nama_bengkel}` : b.nama_bengkel,
  }))
)

const kelasSelectOptions = computed(() =>
  props.kelasOptions.map((k) => ({
    id: k.id,
    label: k.nama_kelas,
  }))
)

const pembimbingSelectOptions = computed(() =>
  props.pembimbingOptions.map((p) => ({
    id: p.id,
    label: p.nama_guru,
  }))
)

// Filtered Siswa based on selected Kelas
const modalSelectedKelasId = computed(() => {
  const raw = modalSelectedKelas.value
  const id = raw?.id ?? raw?.value ?? raw ?? null
  return id === '' || id === null || id === undefined ? null : Number(id)
})

const filteredSiswaOptions = computed(() => {
  if (modalSelectedKelasId.value === null) return []
  return siswaOptionsState.value
    .map((s) => ({
      ...s,
      label: s.nama,
    }))
})

// Selected Siswa List (Display)
const selectedSiswaList = computed(() => {
  return selectedSiswaRecords.value.filter(s => form.siswa_ids.includes(s.id))
})

// Add Siswa to list
const addSiswa = () => {
  if (tempSelectedSiswaId.value) {
    const siswaId = Number(tempSelectedSiswaId.value)
    if (!form.siswa_ids.includes(siswaId)) {
      const selectedSiswa = filteredSiswaOptions.value.find((s) => Number(s.id) === siswaId)
      if (selectedSiswa) {
        form.siswa_ids.push(siswaId)
        selectedSiswaRecords.value = [...selectedSiswaRecords.value, selectedSiswa]
      }
    }
    tempSelectedSiswaId.value = null
  }
}

const openStudentModal = () => {
  isStudentModalActive.value = true
  modalSelectedKelas.value = null
  tempSelectedSiswaId.value = null
  siswaSearch.value = ''
  siswaOptionsState.value = []
}

const addStudentsFromModal = () => {
  if (tempSelectedSiswaId.value) {
    const siswaId = Number(tempSelectedSiswaId.value)
    if (!form.siswa_ids.includes(siswaId)) {
      const selectedSiswa = filteredSiswaOptions.value.find((s) => Number(s.id) === siswaId)
      if (selectedSiswa) {
        form.siswa_ids.push(siswaId)
        selectedSiswaRecords.value = [...selectedSiswaRecords.value, selectedSiswa]
      }
    }
  }
  isStudentModalActive.value = false
  tempSelectedSiswaId.value = null
}

// Watchers
watch(selectedBengkel, (val) => {
  form.bengkel_id = val?.id ?? val ?? ''
})

watch(selectedPembimbing, (val) => {
  form.pembimbing_id = val?.id ?? val ?? ''
})

watch(
  modalSelectedKelas,
  () => {
    tempSelectedSiswaId.value = null
    siswaSearch.value = ''
    fetchSiswaOptions()
  },
)

const fetchSiswaOptions = debounce(async () => {
  if (modalSelectedKelasId.value === null) {
    siswaOptionsState.value = []
    return
  }

  isLoadingSiswaOptions.value = true

  try {
    const { data } = await axios.get(route('admin.input_data_pkl.siswa_options'), {
      params: {
        kelas_id: modalSelectedKelasId.value,
        search: siswaSearch.value,
      },
    })

    siswaOptionsState.value = Array.isArray(data?.data) ? data.data : []
  } catch (error) {
    siswaOptionsState.value = []
  } finally {
    isLoadingSiswaOptions.value = false
  }
}, 300)

watch(siswaSearch, () => {
  fetchSiswaOptions()
})

const removeSiswa = (id) => {
  form.siswa_ids = form.siswa_ids.filter((sid) => sid !== id)
  selectedSiswaRecords.value = selectedSiswaRecords.value.filter((siswa) => siswa.id !== id)
}

const submit = () => {
  form.post(route('admin.input_data_pkl.store'), {
    onSuccess: () => {
      form.reset()
      selectedBengkel.value = ''
      selectedKelas.value = ''
      selectedPembimbing.value = ''
      tempSelectedSiswaId.value = null
    },
  })
}

const resetForm = () => {
  form.reset()
  selectedBengkel.value = ''
  selectedKelas.value = ''
  selectedPembimbing.value = ''
  tempSelectedSiswaId.value = null
  siswaSearch.value = ''
  siswaOptionsState.value = []
  selectedSiswaRecords.value = []
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Input Data PKL" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiFileDocumentEdit" title="Input Data PKL" main>
        <BaseButtons>
          <BaseButton
            :to="route('admin.input_data_pkl.index')"
            :icon="mdiArrowLeft"
            label="Kembali"
            color="whiteDark"
            rounded-full
            small
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

      <CardBox is-form @submit.prevent="submit">
        <h4 class="text-lg font-bold mb-4">Form Penempatan PKL</h4>

        <!-- Pilih Bengkel -->
        <FormField label="Pilih Bengkel" :error="form.errors.bengkel_id">
          <FormControlSearchable
            v-model="selectedBengkel"
            :options="bengkelSelectOptions"
            :icon="mdiOfficeBuildingCog"
            placeholder="-- Cari Bengkel --"
          />
        </FormField>

        <!-- Daftar Siswa Terpilih & Tombol Tambah -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <label class="block font-bold">Daftar Siswa</label>
                <BaseButton
                    :icon="mdiAccountMultiplePlus"
                    label="Tambah Siswa"
                    color="success"
                    small
                    @click="openStudentModal"
                />
            </div>

            <div v-if="selectedSiswaList.length === 0" class="text-gray-500 text-sm italic mb-4">
                Belum ada siswa yang dipilih. Klik tombol "Tambah Siswa" untuk memilih.
            </div>

            <div v-if="selectedSiswaList.length > 0" class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-slate-700 rounded-lg">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-slate-800 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-3">No</th>
                            <th scope="col" class="px-6 py-3">Nama Siswa</th>
                            <th scope="col" class="px-6 py-3">Kelas</th>
                            <th scope="col" class="px-6 py-3">Jurusan</th>
                            <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(siswa, index) in selectedSiswaList" :key="siswa.id" class="bg-white border-b dark:bg-slate-900 dark:border-slate-700 hover:bg-gray-50 dark:hover:bg-slate-800">
                            <td class="px-6 py-4">{{ index + 1 }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ siswa.nama }}
                            </td>
                            <td class="px-6 py-4">
                                {{ siswa.kelas?.nama_kelas }}
                            </td>
                            <td class="px-6 py-4">
                                {{ siswa.kelas?.jurusan?.nama_jurusan }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <BaseButton
                                    :icon="mdiTrashCan"
                                    color="danger"
                                    small
                                    rounded-full
                                    @click="removeSiswa(siswa.id)"
                                    title="Hapus Siswa"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
             <p v-if="form.errors.siswa_ids" class="text-red-500 text-xs mt-1">{{ form.errors.siswa_ids }}</p>
        </div>

        <!-- Pilih Pembimbing -->
        <FormField label="Pilih Pembimbing (Opsional)" :error="form.errors.pembimbing_id">
          <FormControl
            v-model="selectedPembimbing"
            :options="pembimbingSelectOptions"
            :icon="mdiHumanMaleBoard"
            type="select"
            placeholder="-- Pilih Pembimbing --"
          />
        </FormField>

        <template #footer>
          <BaseButtons>
            <BaseButton type="submit" color="info" :label="form.processing ? 'Menyimpan...' : 'Simpan Data'" :disabled="form.processing" />
            <BaseButton type="button" color="info" outline label="Reset" @click="resetForm" :disabled="form.processing" />
          </BaseButtons>
        </template>
      </CardBox>
    </SectionMain>

    <CardBoxModal
      v-model="isStudentModalActive"
      title="Pilih Siswa"
      buttonLabel="Tambahkan"
      has-cancel
      @confirm="addStudentsFromModal"
    >
      <div class="mb-4">
        <FormField label="Filter Kelas">
            <FormControl
              v-model="modalSelectedKelas"
              :options="kelasSelectOptions"
              :icon="mdiSchool"
              type="select"
              placeholder="-- Pilih Kelas --"
            />
        </FormField>
      </div>

      <div v-if="modalSelectedKelasId !== null" class="mb-4">
        <FormField label="Pilih Siswa">
          <div class="mb-2">
            <FormControl
              v-model="siswaSearch"
              :icon="mdiAccountSchool"
              placeholder="Cari nama siswa..."
            />
          </div>
          <FormControl
            v-if="filteredSiswaOptions.length > 0"
            v-model="tempSelectedSiswaId"
            :options="filteredSiswaOptions"
            :icon="mdiAccountSchool"
            type="select"
            placeholder="-- Pilih Siswa --"
          />
          <div v-if="isLoadingSiswaOptions" class="text-gray-500 text-sm py-2">
            Memuat daftar siswa...
          </div>
          <div v-else class="text-red-500 text-sm py-2">
             Tidak ada siswa yang tersedia di kelas ini (atau semua sudah ditempatkan).
          </div>
          <p v-if="filteredSiswaOptions.length > 0" class="text-xs text-gray-500 mt-1">Pilih siswa yang ingin ditambahkan.</p>
        </FormField>
      </div>
      <div v-else class="text-center text-gray-500 py-4">
        Silakan pilih kelas terlebih dahulu.
      </div>

      <template #footer>
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
           <BaseButton label="Batal" color="whiteDark" @click="isStudentModalActive = false" />
           <BaseButton label="Tambahkan" color="info" :disabled="!tempSelectedSiswaId" @click="addStudentsFromModal" />
        </div>
      </template>
    </CardBoxModal>
  </LayoutAuthenticated>
</template>
