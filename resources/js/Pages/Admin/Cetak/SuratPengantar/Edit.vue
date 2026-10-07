<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import {
  mdiFileDocument,
  mdiArrowLeft,
  mdiContentSave,
  mdiAccountPlus,
  mdiTrashCan
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import { ref, computed, onMounted } from 'vue'

const props = defineProps({
  suratPengantar: {
    type: Object,
    required: true,
  },
  kelasOptions: {
    type: Array,
    default: () => [],
  },
  siswaOptions: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  nomor_surat: props.suratPengantar.nomor_surat,
  lampiran: props.suratPengantar.lampiran,
  tujuan_surat: props.suratPengantar.tujuan_surat,
  alamat_tujuan: props.suratPengantar.alamat_tujuan,
  tanggal_mulai: props.suratPengantar.tanggal_mulai,
  tanggal_selesai: props.suratPengantar.tanggal_selesai,
  tanggal_surat: props.suratPengantar.tanggal_surat,
  kepala_sekolah_nama: props.suratPengantar.kepala_sekolah_nama,
  kepala_sekolah_nbm: props.suratPengantar.kepala_sekolah_nbm,
  ketua_pkl_nama: props.suratPengantar.ketua_pkl_nama,
  ketua_pkl_nbm: props.suratPengantar.ketua_pkl_nbm,
  siswa_ids: props.suratPengantar.siswas.map(s => s.id),
})

const selectedKelas = ref(null)
const selectedSiswaToAdd = ref(null)

const filteredSiswaOptions = computed(() => {
  if (!selectedKelas.value) {
    return []
  }
  return props.siswaOptions.filter(siswa => Number(siswa.kelas_id) === Number(selectedKelas.value))
})

const selectedSiswaList = computed(() => {
  const selectedIds = form.siswa_ids.map((id) => Number(id))
  return props.siswaOptions.filter((siswa) => selectedIds.includes(Number(siswa.id)))
})

const addSiswa = () => {
  const siswaId = Number(selectedSiswaToAdd.value)
  if (siswaId && !form.siswa_ids.map((id) => Number(id)).includes(siswaId)) {
    form.siswa_ids.push(siswaId)
    selectedSiswaToAdd.value = null
  }
}

const removeSiswa = (id) => {
  form.siswa_ids = form.siswa_ids.filter((siswaId) => Number(siswaId) !== Number(id))
}

const submit = () => {
  form.put(route('admin.surat.pengantar.update', props.suratPengantar.id))
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Edit Surat Pengantar" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiFileDocument"
        title="Edit Surat Pengantar"
        main
      >
        <BaseButton
          :icon="mdiArrowLeft"
          label="Kembali"
          color="whiteDark"
          rounded-full
          small
          :to="route('admin.surat.pengantar.index')"
        />
      </SectionTitleLineWithButton>

      <CardBox is-form @submit.prevent="submit">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <FormField label="Nomor Surat" :error="form.errors.nomor_surat">
            <FormControl v-model="form.nomor_surat" placeholder="Contoh: 123/PKL/2026" />
          </FormField>
          <FormField label="Lampiran" :error="form.errors.lampiran">
            <FormControl v-model="form.lampiran" placeholder="Contoh: 1 (satu) bendel" />
          </FormField>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <FormField label="Tujuan Surat (Kepada Yth)" :error="form.errors.tujuan_surat">
            <FormControl v-model="form.tujuan_surat" placeholder="Contoh: Pimpinan PT. Telkom" />
          </FormField>
          <FormField label="Alamat Tujuan" :error="form.errors.alamat_tujuan">
            <FormControl v-model="form.alamat_tujuan" type="textarea" placeholder="Alamat lengkap instansi..." />
          </FormField>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <FormField label="Tanggal Mulai PKL" :error="form.errors.tanggal_mulai">
            <FormControl v-model="form.tanggal_mulai" type="date" />
          </FormField>
          <FormField label="Tanggal Selesai PKL" :error="form.errors.tanggal_selesai">
            <FormControl v-model="form.tanggal_selesai" type="date" />
          </FormField>
          <FormField label="Tanggal Surat" :error="form.errors.tanggal_surat">
            <FormControl v-model="form.tanggal_surat" type="date" />
          </FormField>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <FormField label="Nama Kepala Sekolah" :error="form.errors.kepala_sekolah_nama">
            <FormControl v-model="form.kepala_sekolah_nama" />
          </FormField>
          <FormField label="NBM Kepala Sekolah" :error="form.errors.kepala_sekolah_nbm">
            <FormControl v-model="form.kepala_sekolah_nbm" />
          </FormField>
          <FormField label="Nama Ketua PKL" :error="form.errors.ketua_pkl_nama">
            <FormControl v-model="form.ketua_pkl_nama" />
          </FormField>
          <FormField label="NBM Ketua PKL" :error="form.errors.ketua_pkl_nbm">
            <FormControl v-model="form.ketua_pkl_nbm" />
          </FormField>
        </div>

        <hr class="my-6 border-gray-100 dark:border-slate-700">
        
        <h3 class="text-lg font-bold mb-4">Data Siswa</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4 items-end">
          <FormField label="Pilih Kelas (Filter)">
            <select v-model="selectedKelas" class="form-select w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800 dark:border-slate-700">
              <option :value="null">-- Pilih Kelas --</option>
              <option v-for="kelas in kelasOptions" :key="kelas.id" :value="kelas.id">
                {{ kelas.nama_kelas }}
              </option>
            </select>
          </FormField>
          
          <FormField label="Pilih Siswa">
             <select v-model="selectedSiswaToAdd" class="form-select w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800 dark:border-slate-700" :disabled="!selectedKelas">
              <option :value="null">-- Pilih Siswa --</option>
              <option v-for="siswa in filteredSiswaOptions" :key="siswa.id" :value="siswa.id">
                {{ siswa.nama }}
              </option>
            </select>
          </FormField>

          <div class="mb-6">
             <BaseButton
                :icon="mdiAccountPlus"
                label="Tambah Siswa"
                color="info"
                outline
                @click="addSiswa"
                :disabled="!selectedSiswaToAdd"
              />
          </div>
        </div>

        <div v-if="form.errors.siswa_ids" class="text-red-500 text-sm mb-4">
            {{ form.errors.siswa_ids }}
        </div>

        <div v-if="selectedSiswaList.length > 0" class="overflow-x-auto mb-6">
            <table class="w-full text-sm text-left border rounded">
                <thead class="bg-gray-50 dark:bg-slate-800">
                    <tr>
                        <th class="p-3 border-b">No</th>
                        <th class="p-3 border-b">Nama Siswa</th>
                        <th class="p-3 border-b">NISN</th>
                        <th class="p-3 border-b">Kelas</th>
                        <th class="p-3 border-b">Jurusan</th>
                        <th class="p-3 border-b text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(siswa, index) in selectedSiswaList" :key="siswa.id" class="border-b">
                        <td class="p-3">{{ index + 1 }}</td>
                        <td class="p-3 font-medium">{{ siswa.nama }}</td>
                        <td class="p-3">{{ siswa.nisn }}</td>
                        <td class="p-3">{{ siswa.kelas_nama }}</td>
                        <td class="p-3">{{ siswa.jurusan_nama }}</td>
                        <td class="p-3 text-right">
                            <BaseButton
                                :icon="mdiTrashCan"
                                color="danger"
                                small
                                @click="removeSiswa(siswa.id)"
                            />
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="text-gray-500 text-center py-4 border border-dashed rounded mb-6">
            Belum ada siswa yang dipilih. Silakan pilih kelas dan siswa di atas.
        </div>

        <template #footer>
          <BaseButtons>
            <BaseButton
              type="submit"
              color="info"
              label="Simpan Perubahan"
              :icon="mdiContentSave"
              :loading="form.processing"
            />
          </BaseButtons>
        </template>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
