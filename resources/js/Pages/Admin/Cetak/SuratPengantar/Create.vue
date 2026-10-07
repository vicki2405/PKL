<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import {
  mdiFileDocument,
  mdiArrowLeft,
  mdiContentSave,
  mdiCog,
  mdiChevronDown,
  mdiChevronUp,
  mdiAccountPlus,
  mdiTrashCan,
  mdiOfficeBuildingMarker,
  mdiSchool,
} from '@mdi/js'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import FormControlSearchable from '@admin/components/FormControlSearchable.vue'
import { ref, computed, watch } from 'vue'

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
  templateSettings: {
    type: Object,
    default: () => ({}),
  },
  bengkelStudents: {
    type: Object,
    default: () => ({}),
  },
})

const form = useForm({
  nomor_surat: props.templateSettings.default_nomor_surat || '',
  lampiran: props.templateSettings.default_lampiran || '',
  tujuan_surat: '', // Kepada Yth
  alamat_tujuan: '',
  tanggal_mulai: props.templateSettings.default_tanggal_mulai || '',
  tanggal_selesai: props.templateSettings.default_tanggal_selesai || '',
  tanggal_surat: new Date().toISOString().split('T')[0],
  kepala_sekolah_nama: props.templateSettings.kepala_sekolah_nama || 'Eko Wahyudi, S.Kom',
  kepala_sekolah_nbm: props.templateSettings.kepala_sekolah_nbm || '1076615',
  ketua_pkl_nama: props.templateSettings.ketua_pkl_nama || 'Arief Syarifudin, S.Pd',
  ketua_pkl_nbm: props.templateSettings.ketua_pkl_nbm || '',
  siswa_ids: [],
})

const settingForm = useForm({
  default_nomor_surat: props.templateSettings.default_nomor_surat || '',
  default_lampiran: props.templateSettings.default_lampiran || '',
  default_perihal: props.templateSettings.default_perihal || '',
  default_tanggal_mulai: props.templateSettings.default_tanggal_mulai || '',
  default_tanggal_selesai: props.templateSettings.default_tanggal_selesai || '',
  kepala_sekolah_nama: props.templateSettings.kepala_sekolah_nama || '',
  kepala_sekolah_nbm: props.templateSettings.kepala_sekolah_nbm || '',
  ketua_pkl_nama: props.templateSettings.ketua_pkl_nama || '',
  ketua_pkl_nbm: props.templateSettings.ketua_pkl_nbm || '',
  kop_surat: null,
  kepala_sekolah_ttd: null,
  kepala_sekolah_stempel: null,
  ketua_pkl_ttd: null,
  ketua_pkl_stempel: null,
})

const selectedKelas = ref(null)
const selectedSiswaToAdd = ref(null)
const selectedBengkel = ref(null)
const isTemplateSettingsOpen = ref(true)

const bengkelSearchableOptions = computed(() =>
  props.bengkelOptions.map((b) => ({
    id: b.id,
    label: b.nama_bengkel,
  }))
)

watch(selectedBengkel, (val) => {
  if (val) {
    const bengkelId = val.id
    const b = props.bengkelOptions.find((x) => Number(x.id) === Number(bengkelId))
    if (b) {
      form.tujuan_surat = `Pimpinan ${b.nama_bengkel}`
      const parts = []
      if (b.dusun) parts.push(b.dusun)
      if (b.desa) parts.push(b.desa)
      if (b.kecamatan) parts.push(`Kec. ${b.kecamatan}`)
      let addr = parts.join(', ')
      if (b.deskripsi) addr = addr ? `${addr} (${b.deskripsi})` : `(${b.deskripsi})`
      form.alamat_tujuan = addr
    }
    const siswaIds = (props.bengkelStudents[Number(bengkelId)] || []).map(s => Number(s.id))
    form.siswa_ids = siswaIds
  }
})

const filteredSiswaOptions = computed(() => {
  if (!selectedKelas.value) {
    return []
  }
  return props.siswaOptions.filter((siswa) => Number(siswa.kelas_id) === Number(selectedKelas.value))
})

const selectedSiswaList = computed(() => {
  const selectedIds = new Set(form.siswa_ids.map((id) => Number(id)))
  const list = props.siswaOptions.filter((siswa) => selectedIds.has(Number(siswa.id)))
  
  if (selectedBengkel.value) {
    const bengkelId = selectedBengkel.value.id
    const studentsInBengkel = props.bengkelStudents[Number(bengkelId)] || []
    studentsInBengkel.forEach((siswa) => {
      if (selectedIds.has(Number(siswa.id)) && !list.some((s) => Number(s.id) === Number(siswa.id))) {
        list.push(siswa)
      }
    })
  }
  
  return list
})

const bengkelStudentCount = computed(() => {
  const rawId = selectedBengkel.value?.id ?? selectedBengkel.value
  if (!rawId) return 0
  return (props.bengkelStudents[Number(rawId)] || []).length
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

const hasErrorsTujuan = computed(() => {
  return form.errors.tujuan_surat || form.errors.alamat_tujuan
})

const hasErrorsSiswa = computed(() => {
  return form.errors.siswa_ids
})

const submit = () => {
  form.post(route('admin.surat.pengantar.store'))
}

const onFileChange = (event, field) => {
  const [file] = event.target.files || []
  settingForm[field] = file || null
}

const saveTemplateSettings = () => {
  settingForm.post(route('admin.surat.pengantar.template-settings.save'), {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      form.nomor_surat = settingForm.default_nomor_surat || form.nomor_surat
      form.lampiran = settingForm.default_lampiran || form.lampiran
      form.tanggal_mulai = settingForm.default_tanggal_mulai || form.tanggal_mulai
      form.tanggal_selesai = settingForm.default_tanggal_selesai || form.tanggal_selesai
      form.kepala_sekolah_nama = settingForm.kepala_sekolah_nama || form.kepala_sekolah_nama
      form.kepala_sekolah_nbm = settingForm.kepala_sekolah_nbm || form.kepala_sekolah_nbm
      form.ketua_pkl_nama = settingForm.ketua_pkl_nama || form.ketua_pkl_nama
      form.ketua_pkl_nbm = settingForm.ketua_pkl_nbm || form.ketua_pkl_nbm
      settingForm.kop_surat = null
      settingForm.kepala_sekolah_ttd = null
      settingForm.kepala_sekolah_stempel = null
      settingForm.ketua_pkl_ttd = null
      settingForm.ketua_pkl_stempel = null
    },
  })
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Buat Surat Pengantar" />

    <div class="p-6 min-h-screen">

      <!-- Custom Header Banner -->
      <div class="relative bg-gradient-to-r from-blue-600 to-indigo-700 rounded-3xl p-8 mb-8 overflow-hidden shadow-xl text-white">
          <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-white opacity-10 rounded-full blur-3xl"></div>
          <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-cyan-400 opacity-20 rounded-full blur-3xl"></div>

          <div class="relative z-10 flex flex-col md:flex-row justify-between items-center gap-4">
              <div class="flex items-center gap-4">
                  <div class="p-3 bg-white/20 rounded-xl backdrop-blur-sm">
                      <BaseIcon :path="mdiFileDocument" size="32" class="text-white" />
                  </div>
                  <div>
                      <h1 class="text-2xl md:text-3xl font-bold">Buat Surat Pengantar</h1>
                      <p class="text-blue-100 mt-1">Isi formulir di bawah untuk membuat surat pengantar PKL baru.</p>
                  </div>
              </div>
              <BaseButton
                :icon="mdiArrowLeft"
                label="Kembali ke Daftar"
                color="bg-transparent text-white border-white hover:bg-white hover:text-blue-600"
                rounded-full
                :to="route('admin.surat.pengantar.index')"
              />
          </div>
      </div>

      <form @submit.prevent="submit">
        <div class="mb-8 bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-gray-100 dark:border-slate-700 overflow-hidden">
          <div class="bg-gray-50 dark:bg-slate-700/30 px-8 py-5 border-b border-gray-100 dark:border-slate-700 flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
              <div class="w-10 h-10 rounded-full bg-teal-100 dark:bg-teal-900/30 flex items-center justify-center text-teal-600 dark:text-teal-400 shrink-0">
                <BaseIcon :path="mdiCog" size="20" />
              </div>
              <h2 class="text-lg font-bold text-gray-800 dark:text-white truncate">Pengaturan Template Surat</h2>
            </div>
            <BaseButton
              type="button"
              :icon="isTemplateSettingsOpen ? mdiChevronUp : mdiChevronDown"
              :label="isTemplateSettingsOpen ? 'Lipat' : 'Tampilkan'"
              color="white"
              outline
              rounded-full
              small
              :auto-permission="false"
              @click="isTemplateSettingsOpen = !isTemplateSettingsOpen"
            />
          </div>

          <div v-show="isTemplateSettingsOpen" class="p-8 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <FormField label="Kop Surat (Upload Gambar)" :error="settingForm.errors.kop_surat">
              <input type="file" accept="image/*" class="form-input w-full" @change="(e) => onFileChange(e, 'kop_surat')">
              <a v-if="templateSettings.kop_surat_url" :href="templateSettings.kop_surat_url" target="_blank" class="text-xs text-blue-600 mt-1 inline-block">Lihat kop surat saat ini</a>
            </FormField>
            <FormField label="Default Nomor Surat" :error="settingForm.errors.default_nomor_surat">
              <FormControl v-model="settingForm.default_nomor_surat" placeholder="Contoh: 421.5/___/429.101.03/2026" />
            </FormField>

            <FormField label="Default Lampiran" :error="settingForm.errors.default_lampiran">
              <FormControl v-model="settingForm.default_lampiran" placeholder="Contoh: 1 (satu) bendel" />
            </FormField>
            <FormField label="Default Perihal" :error="settingForm.errors.default_perihal">
              <FormControl v-model="settingForm.default_perihal" placeholder="Contoh: Permohonan Tempat Praktik Kerja Lapangan Tahun 2026/2027" />
            </FormField>

            <FormField label="Default Tanggal Mulai PKL" :error="settingForm.errors.default_tanggal_mulai">
              <FormControl v-model="settingForm.default_tanggal_mulai" type="date" />
            </FormField>
            <FormField label="Default Tanggal Selesai PKL" :error="settingForm.errors.default_tanggal_selesai">
              <FormControl v-model="settingForm.default_tanggal_selesai" type="date" />
            </FormField>

            <FormField label="Nama Kepala Sekolah" :error="settingForm.errors.kepala_sekolah_nama">
              <FormControl v-model="settingForm.kepala_sekolah_nama" />
            </FormField>
            <FormField label="NBM Kepala Sekolah" :error="settingForm.errors.kepala_sekolah_nbm">
              <FormControl v-model="settingForm.kepala_sekolah_nbm" />
            </FormField>

            <FormField label="TTD Kepala Sekolah (Upload Gambar)" :error="settingForm.errors.kepala_sekolah_ttd">
              <input type="file" accept="image/*" class="form-input w-full" @change="(e) => onFileChange(e, 'kepala_sekolah_ttd')">
              <a v-if="templateSettings.kepala_sekolah_ttd_url" :href="templateSettings.kepala_sekolah_ttd_url" target="_blank" class="text-xs text-blue-600 mt-1 inline-block">Lihat TTD kepala sekolah</a>
            </FormField>
            <FormField label="Stempel Kepala Sekolah (Upload Gambar)" :error="settingForm.errors.kepala_sekolah_stempel">
              <input type="file" accept="image/*" class="form-input w-full" @change="(e) => onFileChange(e, 'kepala_sekolah_stempel')">
              <a v-if="templateSettings.kepala_sekolah_stempel_url" :href="templateSettings.kepala_sekolah_stempel_url" target="_blank" class="text-xs text-blue-600 mt-1 inline-block">Lihat stempel kepala sekolah</a>
            </FormField>

            <FormField label="Nama Ketua PKL" :error="settingForm.errors.ketua_pkl_nama">
              <FormControl v-model="settingForm.ketua_pkl_nama" />
            </FormField>
            <FormField label="NBM Ketua PKL" :error="settingForm.errors.ketua_pkl_nbm">
              <FormControl v-model="settingForm.ketua_pkl_nbm" />
            </FormField>

            <FormField label="TTD Ketua PKL (Upload Gambar)" :error="settingForm.errors.ketua_pkl_ttd">
              <input type="file" accept="image/*" class="form-input w-full" @change="(e) => onFileChange(e, 'ketua_pkl_ttd')">
              <a v-if="templateSettings.ketua_pkl_ttd_url" :href="templateSettings.ketua_pkl_ttd_url" target="_blank" class="text-xs text-blue-600 mt-1 inline-block">Lihat TTD ketua PKL</a>
            </FormField>
            <FormField label="Stempel Ketua PKL (Upload Gambar)" :error="settingForm.errors.ketua_pkl_stempel">
              <input type="file" accept="image/*" class="form-input w-full" @change="(e) => onFileChange(e, 'ketua_pkl_stempel')">
              <a v-if="templateSettings.ketua_pkl_stempel_url" :href="templateSettings.ketua_pkl_stempel_url" target="_blank" class="text-xs text-blue-600 mt-1 inline-block">Lihat stempel ketua PKL</a>
            </FormField>
          </div>

          <div v-show="isTemplateSettingsOpen" class="px-8 pb-8">
            <BaseButton
              type="button"
              color="success"
              :icon="mdiContentSave"
              label="Save Setting Template Surat"
              :loading="settingForm.processing"
              @click="saveTemplateSettings"
            />
          </div>
        </div>

        <div class="flex flex-col gap-8">
            <!-- SECTION 1: Tujuan Surat -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-gray-100 dark:border-slate-700 overflow-hidden transition-all duration-300 hover:shadow-xl" :class="{'ring-2 ring-red-500/50': hasErrorsTujuan}">
                <div class="bg-gray-50 dark:bg-slate-700/30 px-8 py-5 border-b border-gray-100 dark:border-slate-700 flex items-center gap-3 relative">
                    <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <BaseIcon :path="mdiOfficeBuildingMarker" size="20" />
                    </div>
                    <div v-if="hasErrorsTujuan" class="absolute top-3 left-9 w-3 h-3 bg-red-500 rounded-full border-2 border-white dark:border-slate-800 animate-pulse"></div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white">Tujuan Surat</h2>
                </div>

                <div class="p-8">
                     <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-800/30 rounded-xl">
                        <FormField label="Isi Otomatis dari Data Bengkel" class="mb-0">
                            <FormControlSearchable
                              v-model="selectedBengkel"
                              :options="bengkelSearchableOptions"
                              placeholder="-- Cari & Pilih Bengkel untuk Auto-Fill --"
                            />
                        </FormField>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <FormField label="Kepada Yth." :error="form.errors.tujuan_surat">
                            <FormControl v-model="form.tujuan_surat" placeholder="Contoh: Pimpinan PT. Telkom" />
                        </FormField>
                        <FormField label="Alamat Lengkap" :error="form.errors.alamat_tujuan">
                            <FormControl v-model="form.alamat_tujuan" type="textarea" placeholder="Alamat lengkap instansi..." class="h-full" />
                        </FormField>
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Data Siswa -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-gray-100 dark:border-slate-700 overflow-hidden transition-all duration-300 hover:shadow-xl" :class="{'ring-2 ring-red-500/50': hasErrorsSiswa}">
                 <div class="bg-gray-50 dark:bg-slate-700/30 px-8 py-5 border-b border-gray-100 dark:border-slate-700 flex items-center gap-3 relative">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 dark:bg-indigo-900/30 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <BaseIcon :path="mdiSchool" size="20" />
                    </div>
                    <div v-if="hasErrorsSiswa" class="absolute top-3 left-9 w-3 h-3 bg-red-500 rounded-full border-2 border-white dark:border-slate-800 animate-pulse"></div>
                    <h2 class="text-lg font-bold text-gray-800 dark:text-white">Data Siswa Peserta</h2>
                </div>

                <div class="p-8">
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6 items-end">
                        <div class="md:col-span-5">
                            <FormField label="Filter Kelas">
                            <select v-model="selectedKelas" class="form-select w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800 dark:border-slate-700">
                                <option :value="null">-- Pilih Kelas --</option>
                                <option v-for="kelas in kelasOptions" :key="kelas.id" :value="kelas.id">
                                {{ kelas.nama_kelas }}
                                </option>
                            </select>
                            </FormField>
                        </div>
                        <div class="md:col-span-5">
                            <FormField label="Pilih Siswa">
                                <select v-model="selectedSiswaToAdd" class="form-select w-full rounded-xl border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800 dark:border-slate-700" :disabled="!selectedKelas">
                                <option :value="null">-- Pilih Siswa --</option>
                                <option v-for="siswa in filteredSiswaOptions" :key="siswa.id" :value="siswa.id">
                                {{ siswa.nama }}
                                </option>
                            </select>
                            </FormField>
                        </div>
                        <div class="md:col-span-2 pb-6"> <!-- Padding bottom to align with inputs -->
                            <BaseButton
                                :icon="mdiAccountPlus"
                                label="Tambah"
                                color="indigo"
                                class="w-full rounded-xl h-[42px]"
                                @click="addSiswa"
                                :disabled="!selectedSiswaToAdd"
                            />
                        </div>
                    </div>

                    <div v-if="form.errors.siswa_ids" class="p-4 mb-4 text-red-700 bg-red-100 rounded-xl border border-red-200">
                        {{ form.errors.siswa_ids }}
                    </div>

                    <div v-if="selectedBengkel && bengkelStudentCount > 0" class="p-3 mb-4 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800/30 rounded-xl text-sm text-indigo-700 dark:text-indigo-300 flex items-center gap-2">
                        <BaseIcon :path="mdiSchool" size="18" />
                        <span>Menampilkan <strong>{{ bengkelStudentCount }}</strong> siswa dari bengkel yang dipilih.</span>
                    </div>

                    <div v-if="selectedSiswaList.length > 0" class="overflow-hidden rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 dark:bg-slate-800 uppercase text-xs font-bold text-gray-500 dark:text-gray-400 tracking-wider">
                                <tr>
                                    <th class="p-4 border-b dark:border-slate-700 w-16 text-center">No</th>
                                    <th class="p-4 border-b dark:border-slate-700">Nama Siswa</th>
                                    <th class="p-4 border-b dark:border-slate-700">NISN</th>
                                    <th class="p-4 border-b dark:border-slate-700">Kelas</th>
                                    <th class="p-4 border-b dark:border-slate-700 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <TransitionGroup tag="tbody" name="list" class="divide-y divide-gray-100 dark:divide-slate-700 bg-white dark:bg-slate-900 relative">
                                <tr v-for="(siswa, index) in selectedSiswaList" :key="siswa.id" class="hover:bg-indigo-50/50 dark:hover:bg-slate-800 transition-colors group">
                                    <td class="p-4 text-center font-medium text-gray-500">{{ index + 1 }}</td>
                                    <td class="p-4 font-semibold text-gray-800 dark:text-white">{{ siswa.nama }}</td>
                                    <td class="p-4 text-gray-600 dark:text-gray-300">{{ siswa.nisn }}</td>
                                    <td class="p-4">
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300">
                                            {{ siswa.kelas_nama }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <button @click="removeSiswa(siswa.id)" class="text-gray-400 hover:text-red-500 transition-colors p-2 rounded-full hover:bg-red-50 dark:hover:bg-red-900/20">
                                            <BaseIcon :path="mdiTrashCan" size="18" />
                                        </button>
                                    </td>
                                </tr>
                            </TransitionGroup>
                        </table>
                    </div>
                    <div v-else class="flex flex-col items-center justify-center py-12 border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-xl bg-gray-50/50 dark:bg-slate-800/30">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-slate-700 rounded-full flex items-center justify-center mb-4 text-gray-400">
                            <BaseIcon :path="mdiSchool" size="32" />
                        </div>
                        <p class="text-gray-500 font-medium">Belum ada siswa yang ditambahkan</p>
                        <p class="text-sm text-gray-400 mt-1">Silakan pilih kelas dan siswa dari formulir di atas</p>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="sticky bottom-6 z-20 flex justify-end">
                <button
                    v-if="$can('create', 'surat_pengantar')"
                    type="submit"
                    :disabled="form.processing"
                    class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold py-3 px-8 rounded-full shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-3 min-w-[250px]"
                >
                    <BaseIcon :path="mdiContentSave" size="24" />
                    <span class="text-lg">Simpan Surat Pengantar</span>
                    <div v-if="form.processing" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full ml-2"></div>
                </button>
            </div>
        </div>
      </form>
    </div>
  </LayoutAuthenticated>
</template>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.4s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(30px);
}
</style>
