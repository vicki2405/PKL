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
import { computed, ref, watch, onMounted } from 'vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  problem: {
    type: Object,
    default: () => ({}),
  },
  siswaOptions: {
    type: Array,
    default: () => [],
  },
  kelasOptions: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  tanggal: props.problem.tanggal || '',
  siswa_id: props.problem.siswa_id || '',
  deskripsi_masalah: props.problem.deskripsi_masalah || '',
  deskripsi_tindakan: props.problem.deskripsi_tindakan || '',
  saksi: props.problem.saksi || '',
})

const initialSiswa = props.problem?.siswa ? {
  id: props.problem.siswa.id,
  kelas_id: props.problem.siswa.kelas_id,
  nama: props.problem.siswa.nama,
  kelas_nama: props.problem.siswa.kelas?.nama_kelas || '-',
  bengkel: props.problem.bengkel,
  pembimbing: props.problem.pembimbing,
  label: `${props.problem.siswa.nama} - ${props.problem.siswa.kelas?.nama_kelas || '-'}`,
} : null

const selectedSiswaId = ref(props.problem?.siswa_id || null)
const selectedSiswa = ref(initialSiswa)
const selectedKelasId = ref(props.problem?.siswa?.kelas_id || null)
const siswaOptionsState = ref(initialSiswa ? [initialSiswa] : [])
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

watch(selectedSiswaId, (val) => {
  form.siswa_id = val || ''
  if (val) {
    selectedSiswa.value = siswaOptionsState.value.find((s) => Number(s.id) === Number(val)) || selectedSiswa.value
  } else {
    selectedSiswa.value = null
  }
})

watch(selectedKelasId, (newVal, oldVal) => {
  if (oldVal !== undefined && oldVal !== null && newVal !== oldVal) {
    selectedSiswaId.value = null
    selectedSiswa.value = null
    form.siswa_id = ''
    siswaSearch.value = ''
  }
  fetchSiswaOptions()
})

onMounted(() => {
  if (selectedKelasId.value) {
    fetchSiswaOptions()
  }
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
    if (selectedSiswa.value && !rows.some((row) => Number(row.id) === Number(selectedSiswa.value.id))) {
      rows.unshift(selectedSiswa.value)
    }
    siswaOptionsState.value = rows

    if (selectedSiswaId.value) {
      selectedSiswa.value = rows.find((s) => Number(s.id) === Number(selectedSiswaId.value)) || selectedSiswa.value
    }
  } catch (error) {
    siswaOptionsState.value = selectedSiswa.value ? [selectedSiswa.value] : []
  } finally {
    isLoadingSiswaOptions.value = false
  }
}, 300)

watch(siswaSearch, () => {
  fetchSiswaOptions()
})

const submit = () => {
  form.put(route('admin.rekap_problem.update', props.problem.id), {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Edit Rekap Problem" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiAlertCircle"
        title="Edit Rekap Problem"
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
                            type="reset"
                            color="info"
                            outline
                            label="Reset"
                            :icon="mdiReload"
                            @click="form.reset()"
                        />
                        <BaseButton
                            type="submit"
                            color="success"
                            label="Update Data"
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
