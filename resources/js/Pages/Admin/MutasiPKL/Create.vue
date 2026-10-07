<script setup>
import axios from 'axios'
import { Head, useForm, Link } from '@inertiajs/vue3'
import {
  mdiTransfer,
  mdiArrowLeft,
  mdiAccountSchool,
  mdiDomain,
  mdiHumanMaleBoard,
  mdiAlertCircle,
  mdiCheck
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
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
  kelasOptions: {
    type: Array,
    default: () => [],
  },
  siswaOptions: {
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

const form = useForm({
  kelas_id: '',
  siswa_id: '',
  bengkel_asal_id: '',
  bengkel_tujuan_id: '',
  pembimbing_id: '',
  alasan: '',
})

const selectedKelasObj = ref(null)
const selectedSiswaObj = ref(null)
const selectedBengkelAsalObj = ref(null)
const selectedBengkelObj = ref(null)
const selectedPembimbingObj = ref(null)
const siswaOptionsState = ref([])
const siswaSearch = ref('')
const isLoadingSiswaOptions = ref(false)

// Format options for select
const formattedKelasOptions = computed(() =>
  props.kelasOptions.map((k) => ({
    id: k.id,
    label: k.nama_kelas,
  }))
)

const formattedSiswaOptions = computed(() =>
  siswaOptionsState.value.map((s) => ({
    id: s.id,
    kelas_id: s.kelas_id,
    label: `${s.nama} - ${s.kelas?.nama_kelas || ''} ${s.kelas?.jurusan?.nama_jurusan || ''}`,
    current_bengkel: s.current_bengkel
  }))
)

const filteredSiswaOptions = computed(() => {
  const kelasId = selectedKelasObj.value?.id
  if (!kelasId) return []
  return formattedSiswaOptions.value.filter((s) => String(s.kelas_id) === String(kelasId))
})

const formattedBengkelOptions = computed(() =>
  props.bengkelOptions.map((b) => ({
    id: b.id,
    label: b.nama_bengkel,
    nama_bengkel: b.nama_bengkel,
  }))
)

const formattedPembimbingOptions = computed(() =>
  props.pembimbingOptions.map((p) => ({
    id: p.id,
    label: p.nama_guru,
  }))
)

const extractId = (val) => (val && typeof val === 'object' ? (val.id ?? val.value ?? '') : (val ?? ''))

// Update form values when selections change
watch(selectedKelasObj, (val) => {
  const kId = extractId(val)
  form.kelas_id = kId
  selectedSiswaObj.value = null
  selectedBengkelAsalObj.value = null
  form.siswa_id = ''
  form.bengkel_asal_id = ''
  siswaSearch.value = ''
  fetchSiswaOptions()
})

watch(selectedSiswaObj, (val) => {
    form.siswa_id = extractId(val)

    const siswaItem = typeof val === 'object' && val !== null
      ? val
      : siswaOptionsState.value.find((s) => String(s.id) === String(val))

    const currentBengkelId = siswaItem?.current_bengkel?.id
    if (currentBengkelId) {
      const match = formattedBengkelOptions.value.find((b) => String(b.id) === String(currentBengkelId)) || null
      selectedBengkelAsalObj.value = match?.id ?? currentBengkelId
      form.bengkel_asal_id = currentBengkelId
      return
    }

    selectedBengkelAsalObj.value = null
    form.bengkel_asal_id = ''
})

watch(selectedBengkelAsalObj, (val) => {
  form.bengkel_asal_id = extractId(val)
})

watch(selectedBengkelObj, (val) => {
    form.bengkel_tujuan_id = extractId(val)
})

watch(selectedPembimbingObj, (val) => {
    form.pembimbing_id = extractId(val)
})

const fetchSiswaOptions = debounce(async () => {
  const kId = extractId(selectedKelasObj.value)
  if (!kId) {
    siswaOptionsState.value = []
    return
  }

  isLoadingSiswaOptions.value = true

  try {
    const { data } = await axios.get(route('admin.mutasi_pkl.siswa_options'), {
      params: {
        kelas_id: kId,
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

const submit = () => {
  form.post(route('admin.mutasi_pkl.store'), {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Buat Pengajuan Mutasi PKL" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiTransfer"
        title="Buat Pengajuan Mutasi PKL"
        main
      >
        <BaseButton
          :to="route('admin.mutasi_pkl.index')"
          :icon="mdiArrowLeft"
          label="Kembali"
          color="whiteDark"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>

      <CardBox>
        <form @submit.prevent="submit">
          <NotificationBar v-if="form.hasErrors" color="danger" :icon="mdiAlertCircle">
            Mohon periksa kesalahan di bawah ini.
          </NotificationBar>

          <FormField label="Pilih Kelas" help="Pilih kelas terlebih dahulu">
            <FormControl
              v-model="selectedKelasObj"
              :options="formattedKelasOptions"
              :icon="mdiAccountSchool"
              placeholder="Pilih kelas"
              required
            />
          </FormField>

          <FormField
            label="Pilih Siswa"
            :help="selectedSiswaObj
              ? `Bengkel Saat Ini: ${selectedSiswaObj.current_bengkel?.nama_bengkel || 'Belum ada penempatan'}`
              : (selectedKelasObj
                ? (filteredSiswaOptions.length ? 'Pilih siswa yang akan dipindahkan' : 'Tidak ada siswa di kelas ini')
                : 'Pilih kelas terlebih dahulu')"
          >
            <div v-if="selectedKelasObj" class="mb-2">
              <FormControl
                v-model="siswaSearch"
                :icon="mdiAccountSchool"
                placeholder="Cari nama siswa..."
              />
            </div>
            <FormControl
              v-model="selectedSiswaObj"
              :options="filteredSiswaOptions"
              :icon="mdiAccountSchool"
              :disabled="!selectedKelasObj"
              placeholder="Pilih siswa"
              required
            />
            <div v-if="isLoadingSiswaOptions" class="mt-2 text-xs text-gray-500">
              Memuat daftar siswa...
            </div>
          </FormField>

          <FormField
            label="Bengkel Asal"
            :help="selectedSiswaObj
              ? (selectedSiswaObj.current_bengkel?.id ? 'Diambil otomatis dari penempatan siswa' : 'Pilih bengkel asal terlebih dahulu')
              : 'Pilih siswa terlebih dahulu'"
          >
            <FormControl
              v-model="selectedBengkelAsalObj"
              :options="formattedBengkelOptions"
              :icon="mdiDomain"
              :disabled="!selectedSiswaObj || !!selectedSiswaObj.current_bengkel?.id"
              placeholder="Pilih bengkel asal"
              required
            />
          </FormField>

          <FormField label="Bengkel Tujuan" help="Ketik nama bengkel untuk mencari">
            <FormControlSearchable
              v-model="selectedBengkelObj"
              :options="formattedBengkelOptions"
              :icon="mdiDomain"
              placeholder="Cari nama bengkel..."
              required
            />
          </FormField>

          <FormField label="Pembimbing (Pengaju)" help="Pilih pembimbing yang mengajukan mutasi">
            <FormControl
              v-model="selectedPembimbingObj"
              :options="formattedPembimbingOptions"
              :icon="mdiHumanMaleBoard"
              placeholder="Pilih pembimbing"
              required
            />
          </FormField>

          <FormField label="Alasan Mutasi" help="Jelaskan alasan pemindahan siswa">
            <FormControl
              v-model="form.alasan"
              type="textarea"
              placeholder="Contoh: Jarak lokasi terlalu jauh, ketidakcocokan skill, dll."
              required
            />
          </FormField>

          <BaseButtons>
            <BaseButton
              type="submit"
              color="info"
              label="Simpan Pengajuan"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
              :icon="mdiCheck"
            />
          </BaseButtons>
        </form>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
