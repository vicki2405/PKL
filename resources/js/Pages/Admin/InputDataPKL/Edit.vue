<script setup>
import axios from 'axios'
import { Head, useForm } from '@inertiajs/vue3'
import {
  mdiFileDocumentEdit,
  mdiOfficeBuildingCog,
  mdiSchool,
  mdiAccountSchool,
  mdiHumanMaleBoard,
  mdiAlertCircle,
  mdiPlus,
  mdiTrashCan,
  mdiArrowLeft
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import { computed, ref, watch, onMounted } from 'vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  bengkel: {
    type: Object,
    required: true,
  },
  currentStudents: {
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
  currentStudentIds: {
    type: Array,
    default: () => [],
  },
  currentPembimbingId: {
    type: [Number, String],
    default: null,
  },
})

const form = useForm({
  siswa_ids: (props.currentStudentIds || []).map((id) => Number(id)),
  pembimbing_id: props.currentPembimbingId,
})

const pembimbingId = ref(null)
const tempSelectedSiswaId = ref(null)
const isStudentModalActive = ref(false)
const modalSelectedKelas = ref(null)
const siswaOptionsState = ref([])
const siswaSearch = ref('')
const isLoadingSiswaOptions = ref(false)
const selectedSiswaRecords = ref([])

onMounted(() => {
    if (Array.isArray(props.currentStudents) && props.currentStudents.length > 0) {
      selectedSiswaRecords.value = props.currentStudents
    } else {
      selectedSiswaRecords.value = (props.bengkel?.penempatan_pkl || [])
          .map((placement) => placement?.siswa)
          .filter(Boolean)
    }
    if (props.currentPembimbingId) {
        pembimbingId.value = Number(props.currentPembimbingId)
        form.pembimbing_id = Number(props.currentPembimbingId)
    }
})

// Options for selects
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

// Selected Siswa List (Display) - Includes currently placed students in this bengkel + newly added ones
const selectedSiswaList = computed(() => {
  const selectedIdSet = new Set((form.siswa_ids || []).map((id) => Number(id)))
  return selectedSiswaRecords.value.filter((s) => s && selectedIdSet.has(Number(s.id)))
})

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
    const selectedSiswa = filteredSiswaOptions.value.find((s) => Number(s.id) === siswaId)
    if (selectedSiswa) {
      const selectedIdSet = new Set((form.siswa_ids || []).map((id) => Number(id)))
      if (!selectedIdSet.has(siswaId)) {
        form.siswa_ids.push(siswaId)
        const recordIdSet = new Set(selectedSiswaRecords.value.map((s) => Number(s.id)))
        if (!recordIdSet.has(siswaId)) {
          selectedSiswaRecords.value = [...selectedSiswaRecords.value, selectedSiswa]
        }
      }
    }
  }
  isStudentModalActive.value = false
  tempSelectedSiswaId.value = null
}

watch(pembimbingId, (val) => {
  form.pembimbing_id = val ?? null
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
        bengkel_id: props.bengkel.id,
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
  const targetId = Number(id)
  form.siswa_ids = (form.siswa_ids || []).map((sid) => Number(sid)).filter((sid) => sid !== targetId)
  selectedSiswaRecords.value = selectedSiswaRecords.value.filter((siswa) => Number(siswa.id) !== targetId)
}

const submit = () => {
  form.put(route('admin.input_data_pkl.update', props.bengkel.id), {
    onSuccess: () => {
      form.recentlySuccessful = true
      setTimeout(() => { form.recentlySuccessful = false }, 3000)
    },
    onError: () => {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    },
  })
}

const resetForm = () => {
  form.reset()
  // Re-init logic if needed
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Edit Penempatan PKL" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiFileDocumentEdit" title="Edit Penempatan PKL" main>
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
      <NotificationBar
        v-if="$page.props.flash && $page.props.flash.error"
        color="danger"
        :icon="mdiAlertCircle"
      >
        {{ $page.props.flash.error }}
      </NotificationBar>
      <NotificationBar
        v-if="form.recentlySuccessful"
        color="success"
        :icon="mdiAlertCircle"
      >
        Data berhasil disimpan!
      </NotificationBar>

      <div v-if="form.hasErrors" class="mb-4 p-3 bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-800 rounded-lg">
        <p class="text-sm font-semibold text-red-700 dark:text-red-400 mb-1">Terjadi kesalahan validasi:</p>
        <ul class="list-disc list-inside text-xs text-red-600 dark:text-red-300">
          <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
        </ul>
      </div>

      <CardBox is-form @submit.prevent="submit">
        <h4 class="text-lg font-bold mb-4">Edit Penempatan - {{ bengkel.nama_bengkel }}</h4>

        <!-- Bengkel (Read Only) -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-slate-700">
            <div class="flex items-center mb-2">
                <span class="font-semibold w-32">Nama Bengkel:</span>
                <span>{{ bengkel.nama_bengkel }}</span>
            </div>
            <div class="flex items-center">
                <span class="font-semibold w-32">Alamat:</span>
                <span>{{ bengkel.dusun ? bengkel.dusun + ', ' : '' }}{{ bengkel.desa }}, {{ bengkel.kecamatan }}</span>
            </div>
        </div>

        <!-- Pilih Siswa -->
        <div class="mb-6">
            <div class="flex justify-between items-center mb-2">
                <label class="block font-bold">Daftar Siswa</label>
                <BaseButton
                    :icon="mdiPlus"
                    label="Tambah Siswa"
                    color="success"
                    rounded-full
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
            v-model="pembimbingId"
            :options="pembimbingSelectOptions"
            :icon="mdiHumanMaleBoard"
            type="select"
            placeholder="-- Pilih Pembimbing --"
          />
        </FormField>

        <template #footer>
          <BaseButtons>
            <BaseButton type="submit" color="info" :label="form.processing ? 'Menyimpan...' : 'Simpan Perubahan'" :disabled="form.processing" />
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
             Tidak ada siswa yang tersedia di kelas ini (atau semua sudah ditempatkan di tempat lain).
          </div>
          <p v-if="filteredSiswaOptions.length > 0" class="text-xs text-gray-500 mt-1">Pilih siswa yang ingin ditambahkan.</p>
        </FormField>
      </div>
    </CardBoxModal>
  </LayoutAuthenticated>
</template>
