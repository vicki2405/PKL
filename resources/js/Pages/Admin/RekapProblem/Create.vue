<script setup>
import axios from 'axios'
import { Head, useForm, Link } from '@inertiajs/vue3'
import {
  mdiAlertCircle,
  mdiArrowLeft,
  mdiAccountSchool,
  mdiDomain,
  mdiHumanMaleBoard,
  mdiCheck,
  mdiCalendar,
  mdiFormatTitle,
  mdiFileDocumentEdit,
  mdiAccountGroup,
  mdiInformation,
  mdiOfficeBuilding,
  mdiAccountTie,
  mdiReload,
  mdiGavel,
  mdiContentSave
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import { computed, ref, watch } from 'vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  siswaOptions: {
    type: Array,
    default: () => [],
  },
  kelasOptions: {
    type: Array,
    default: () => [],
  },
})

const today = new Date()
const localDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`

const form = useForm({
  tanggal: localDate,
  siswa_id: '',
  deskripsi_masalah: '',
  deskripsi_tindakan: '',
  saksi: '',
})

const selectedSiswaId = ref(null)
const selectedSiswa = ref(null)
const selectedKelasId = ref(null)
const siswaOptionsState = ref([])
const siswaSearch = ref('')
const isLoadingSiswaOptions = ref(false)

const kelasSelectOptions = computed(() => {
  const options = (props.kelasOptions || []).map((kelas) => ({
    ...kelas,
    value: kelas.id,
    label: kelas.label || kelas.nama_kelas,
  }))

  return [{ id: null, value: null, label: 'Pilih Kelas...' }, ...options]
})

const filteredSiswaOptions = computed(() => {
  const all = siswaOptionsState.value.map((s) => ({
    ...s,
    value: s.id,
    label: `${s.nama} - ${s.kelas_nama}`,
  }))

  if (!selectedKelasId.value) {
    return [{ id: null, value: null, label: 'Pilih Kelas terlebih dahulu...' }]
  }

  return [{ id: null, value: null, label: 'Pilih Siswa...' }, ...all.filter((s) => Number(s.kelas_id) === Number(selectedKelasId.value))]
})

const bengkelAlamat = computed(() => {
  const bengkel = selectedSiswa.value?.bengkel
  if (!bengkel) return '-'
  const parts = [bengkel.dusun, bengkel.desa, bengkel.kecamatan].filter(Boolean)
  return parts.length ? parts.join(', ') : '-'
})

// Watch selection to update form and display fields
watch(selectedSiswaId, (val) => {
  form.siswa_id = val || ''
  selectedSiswa.value = val ? (siswaOptionsState.value.find((s) => Number(s.id) === Number(val)) || null) : null
})

watch(selectedKelasId, () => {
  selectedSiswaId.value = null
  selectedSiswa.value = null
  form.siswa_id = ''
  siswaSearch.value = ''
  fetchSiswaOptions()
})

const fetchSiswaOptions = debounce(async () => {
  if (!selectedKelasId.value) {
    siswaOptionsState.value = []
    return
  }

  isLoadingSiswaOptions.value = true

  try {
    const { data } = await axios.get(route('admin.rekap_problem.siswa_options'), {
      params: {
        kelas_id: selectedKelasId.value,
        search: siswaSearch.value,
      },
    })

    const rows = Array.isArray(data?.data) ? data.data : []
    siswaOptionsState.value = rows

    if (selectedSiswaId.value) {
      selectedSiswa.value = rows.find((s) => Number(s.id) === Number(selectedSiswaId.value)) || null
    }
  } catch (error) {
    siswaOptionsState.value = []
  } finally {
    isLoadingSiswaOptions.value = false
  }
}, 300)

watch(siswaSearch, () => {
  fetchSiswaOptions()
})

const resetForm = () => {
  form.reset()
  selectedKelasId.value = null
  selectedSiswaId.value = null
  selectedSiswa.value = null
  siswaSearch.value = ''
  siswaOptionsState.value = []
}

const submit = () => {
  form.post(route('admin.rekap_problem.store'), {
    onSuccess: () => resetForm(),
  })
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Input Rekap Problem" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiAlertCircle"
        title="Input Rekap Problem"
        main
      >
        <BaseButton
          :to="route('admin.rekap_problem.index')"
          :icon="mdiArrowLeft"
          label="Kembali"
          color="whiteDark"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>

      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column: Student Context -->
            <div class="lg:col-span-1 space-y-6">
                <CardBox title="Informasi Siswa" :icon="mdiAccountSchool" class="shadow-md">
                    <FormField label="Tanggal Masalah" :error="form.errors.tanggal">
                        <FormControl
                            v-model="form.tanggal"
                            :icon="mdiCalendar"
                            type="date"
                            placeholder="Pilih Tanggal"
                        />
                    </FormField>

                    <FormField label="Kelas" :error="form.errors.kelas_id">
                        <FormControl
                            v-model="selectedKelasId"
                            :options="kelasSelectOptions"
                            placeholder="Pilih Kelas"
                            :icon="mdiDomain"
                        />
                    </FormField>

                    <FormField label="Siswa" :error="form.errors.siswa_id">
                        <div v-if="selectedKelasId" class="mb-2">
                            <FormControl
                                v-model="siswaSearch"
                                :icon="mdiAccountSchool"
                                placeholder="Cari nama siswa..."
                            />
                        </div>
                        <FormControl
                            v-model="selectedSiswaId"
                            :options="filteredSiswaOptions"
                            placeholder="Pilih Siswa"
                            :icon="mdiAccountSchool"
                        />
                        <div v-if="isLoadingSiswaOptions" class="mt-2 text-xs text-gray-500">
                            Memuat daftar siswa...
                        </div>
                    </FormField>

                    <!-- Enhanced Info Box for Selected Student -->
                    <transition
                        enter-active-class="transition duration-300 ease-out"
                        enter-from-class="transform scale-95 opacity-0"
                        enter-to-class="transform scale-100 opacity-100"
                        leave-active-class="transition duration-200 ease-in"
                        leave-from-class="transform scale-100 opacity-100"
                        leave-to-class="transform scale-95 opacity-0"
                    >
                        <div v-if="selectedSiswa && selectedSiswa.id" class="mt-6 bg-blue-50 dark:bg-slate-800 rounded-lg p-4 border border-blue-100 dark:border-slate-700">
                            <h4 class="text-sm font-semibold text-blue-600 dark:text-blue-400 mb-3 flex items-center">
                                <BaseIcon :path="mdiInformation" size="18" class="mr-1"/>
                                Detail Penempatan
                            </h4>

                            <div class="space-y-3">
                                <div class="flex items-start">
                                    <div class="bg-white dark:bg-slate-700 p-2 rounded-full mr-3 shadow-sm">
                                        <BaseIcon :path="mdiOfficeBuilding" size="20" class="text-gray-600 dark:text-gray-300"/>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Bengkel</p>
                                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ selectedSiswa?.bengkel?.nama_bengkel || '-' }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="bg-white dark:bg-slate-700 p-2 rounded-full mr-3 shadow-sm">
                                        <BaseIcon :path="mdiDomain" size="20" class="text-gray-600 dark:text-gray-300"/>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Alamat Bengkel</p>
                                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ bengkelAlamat }}</p>
                                    </div>
                                </div>

                                <div class="flex items-start">
                                    <div class="bg-white dark:bg-slate-700 p-2 rounded-full mr-3 shadow-sm">
                                        <BaseIcon :path="mdiAccountTie" size="20" class="text-gray-600 dark:text-gray-300"/>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Pembimbing</p>
                                        <p class="font-medium text-gray-800 dark:text-gray-200">{{ selectedSiswa?.pembimbing?.nama_guru || '-' }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </transition>
                </CardBox>
            </div>

            <!-- Right Column: Problem Details -->
            <div class="lg:col-span-2">
                <CardBox title="Detail Permasalahan" :icon="mdiAlertCircle" class="shadow-md h-full">
                    <div class="grid grid-cols-1 gap-6">
                        <FormField label="Deskripsi Masalah" :error="form.errors.deskripsi_masalah" help="Jelaskan secara detail masalah yang terjadi">
                            <FormControl
                                v-model="form.deskripsi_masalah"
                                type="textarea"
                                :icon="mdiFileDocumentEdit"
                                placeholder="Contoh: Siswa terlambat datang ke tempat PKL selama 3 hari berturut-turut..."
                                class="h-32"
                            />
                        </FormField>

                        <FormField label="Deskripsi Tindakan" :error="form.errors.deskripsi_tindakan" help="Langkah-langkah yang sudah diambil">
                            <FormControl
                                v-model="form.deskripsi_tindakan"
                                type="textarea"
                                :icon="mdiCheck"
                                placeholder="Contoh: Diberikan teguran lisan dan pembinaan oleh pembimbing lapangan..."
                                class="h-32"
                            />
                        </FormField>

                        <FormField label="Sanksi (Opsional)" :error="form.errors.saksi">
                            <FormControl
                                v-model="form.saksi"
                                :icon="mdiGavel"
                                placeholder="Contoh: Surat Peringatan 1"
                            />
                        </FormField>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800 flex justify-end gap-3">
                         <BaseButton
                            type="button"
                            color="info"
                            outline
                            label="Reset"
                            :icon="mdiReload"
                            @click="resetForm"
                        />
                        <BaseButton
                            type="submit"
                            color="success"
                            label="Simpan Data"
                            :icon="mdiContentSave"
                            :loading="form.processing"
                        />
                    </div>
                </CardBox>
            </div>
        </div>
      </form>
    </SectionMain>
  </LayoutAuthenticated>
</template>
