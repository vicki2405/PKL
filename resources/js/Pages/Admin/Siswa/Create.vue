<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import {
  mdiSchool,
  mdiArrowLeft,
  mdiAlertCircle,
  mdiAccount,
  mdiCardAccountDetails,
  mdiPhone,
  mdiMapMarker,
  mdiGenderMaleFemale,
  mdiDomain,
  mdiGoogleClassroom,
  mdiCalendar,
  mdiIdentifier
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import { computed, ref, watch } from 'vue'

const props = defineProps({
  jurusanOptions: {
    type: Array,
    default: () => [],
  },
  kelasOptions: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  kelas_id: '',
  nisn: '',
  nipd: '',
  nama: '',
  tempat_lahir: '',
  tanggal_lahir: '',
  jenis_kelamin: null,
  agama: null,
  no_hp: '',
  alamat: '',
  rt: '',
  rw: '',
  dusun: '',
  kelurahan: '',
  kecamatan: '',
})

const selectedJurusan = ref(null)
const jkOptions = [
  { id: 'L', label: 'Laki-laki' },
  { id: 'P', label: 'Perempuan' },
]

const agamaOptions = [
  { id: 'islam', label: 'Islam', value: 'Islam' },
  { id: 'kristen', label: 'Kristen', value: 'Kristen' },
  { id: 'katolik', label: 'Katolik', value: 'Katolik' },
  { id: 'hindu', label: 'Hindu', value: 'Hindu' },
  { id: 'buddha', label: 'Buddha', value: 'Buddha' },
  { id: 'konghucu', label: 'Konghucu', value: 'Konghucu' },
  { id: 'lainnya', label: 'Lainnya', value: 'Lainnya' },
]

const jurusanSelectOptions = computed(() =>
  (props.jurusanOptions || []).map((j) => ({
    id: j.id,
    label: j.kode_jurusan ? `${j.kode_jurusan} - ${j.nama_jurusan}` : j.nama_jurusan,
  })),
)

const filteredKelasOptions = computed(() => {
  const raw = selectedJurusan.value
  const jurusanId = raw && typeof raw === 'object' ? raw.id : raw
  const list = (props.kelasOptions || []).filter((k) => !jurusanId || k.jurusan_id == jurusanId)
  return list.map((k) => ({ id: k.id, label: k.nama_kelas }))
})

const fieldLabels = {
  kelas_id: 'Kelas',
  nisn: 'NISN',
  nipd: 'NIPD',
  nama: 'Nama',
  tempat_lahir: 'Tempat Lahir',
  tanggal_lahir: 'Tanggal Lahir',
  jenis_kelamin: 'Jenis Kelamin',
  agama: 'Agama',
  no_hp: 'No HP',
  alamat: 'Alamat',
  rt: 'RT',
  rw: 'RW',
  dusun: 'Dusun',
  kelurahan: 'Kelurahan',
  kecamatan: 'Kecamatan',
}

watch(selectedJurusan, () => {
  form.kelas_id = ''
})

const submit = () => {
  form.post(route('admin.siswa.store'))
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Tambah Siswa" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiSchool" title="Tambah Siswa" main>
        <BaseButton
          :icon="mdiArrowLeft"
          label="Kembali"
          color="whiteDark"
          rounded-full
          small
          :to="route('admin.siswa.index')"
        />
      </SectionTitleLineWithButton>

      <NotificationBar
        v-if="$page.props.errors && Object.keys($page.props.errors).length"
        color="danger"
        :icon="mdiAlertCircle"
      >
        <div class="font-semibold mb-1">
          Form belum lengkap atau ada yang salah. Periksa kembali input Anda.
        </div>
        <ul class="list-disc list-inside text-sm space-y-0.5">
          <li v-for="(message, field) in $page.props.errors" :key="field">
            <span v-if="fieldLabels[field]">
              <span class="font-semibold">{{ fieldLabels[field] }}:</span>
              {{ ' ' + message }}
            </span>
            <span v-else>
              {{ message }}
            </span>
          </li>
        </ul>
      </NotificationBar>

      <div class="max-w-4xl mx-auto">
        <CardBox class="mb-6">
          <form class="grid gap-6 md:grid-cols-2" @submit.prevent="submit">
            <FormField label="Jurusan">
              <FormControl
                v-model="selectedJurusan"
                :options="jurusanSelectOptions"
                type="select"
                :icon="mdiDomain"
                placeholder="Pilih Jurusan"
              />
            </FormField>

            <FormField label="Kelas" :error="form.errors.kelas_id">
              <FormControl
                v-model="form.kelas_id"
                :options="filteredKelasOptions"
                type="select"
                :icon="mdiGoogleClassroom"
                placeholder="Pilih Kelas"
                required
              />
            </FormField>

            <FormField label="NISN" :error="form.errors.nisn">
              <FormControl
                v-model="form.nisn"
                :icon="mdiCardAccountDetails"
                placeholder="Nomor Induk Siswa Nasional"
                required
              />
            </FormField>

            <FormField label="NIPD" :error="form.errors.nipd">
              <FormControl
                v-model="form.nipd"
                :icon="mdiIdentifier"
                placeholder="Nomor Induk Peserta Didik"
              />
            </FormField>

            <FormField label="Nama" :error="form.errors.nama">
              <FormControl
                v-model="form.nama"
                :icon="mdiAccount"
                placeholder="Nama Lengkap"
                required
              />
            </FormField>

            <FormField label="Tempat Lahir" :error="form.errors.tempat_lahir">
              <FormControl
                v-model="form.tempat_lahir"
                :icon="mdiMapMarker"
                placeholder="Tempat Lahir"
              />
            </FormField>

            <FormField label="Tanggal Lahir" :error="form.errors.tanggal_lahir">
              <FormControl
                v-model="form.tanggal_lahir"
                type="date"
                :icon="mdiCalendar"
                placeholder="Tanggal Lahir"
              />
            </FormField>

            <FormField label="Jenis Kelamin" :error="form.errors.jenis_kelamin">
              <FormControl
                v-model="form.jenis_kelamin"
                :options="jkOptions"
                type="select"
                :icon="mdiGenderMaleFemale"
                placeholder="Pilih Jenis Kelamin"
                required
              />
            </FormField>

            <FormField label="Agama" :error="form.errors.agama">
              <FormControl
                v-model="form.agama"
                :options="agamaOptions"
                type="select"
                placeholder="Pilih Agama"
              />
            </FormField>

            <FormField label="No HP" :error="form.errors.no_hp">
              <FormControl
                v-model="form.no_hp"
                :icon="mdiPhone"
                placeholder="0812xxxxxxx"
              />
            </FormField>

            <FormField class="md:col-span-2" label="Alamat" :error="form.errors.alamat">
              <FormControl
                v-model="form.alamat"
                type="textarea"
                placeholder="Alamat Lengkap"
              />
            </FormField>

            <FormField label="RT" :error="form.errors.rt">
              <FormControl v-model="form.rt" placeholder="001" />
            </FormField>

            <FormField label="RW" :error="form.errors.rw">
              <FormControl v-model="form.rw" placeholder="002" />
            </FormField>

            <FormField label="Dusun" :error="form.errors.dusun">
              <FormControl v-model="form.dusun" placeholder="Nama Dusun" />
            </FormField>

            <FormField label="Kelurahan" :error="form.errors.kelurahan">
              <FormControl v-model="form.kelurahan" placeholder="Nama Kelurahan" />
            </FormField>

            <FormField label="Kecamatan" :error="form.errors.kecamatan">
              <FormControl v-model="form.kecamatan" placeholder="Nama Kecamatan" />
            </FormField>

            <div class="md:col-span-2 flex justify-end">
              <BaseButton type="submit" label="Simpan" color="info" />
            </div>
          </form>
        </CardBox>
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>
