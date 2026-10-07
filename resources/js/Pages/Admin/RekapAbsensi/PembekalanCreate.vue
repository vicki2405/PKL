<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3'
import {
  mdiBookOpenPageVariant,
  mdiArrowLeft,
  mdiAccountSchool,
  mdiContentSave,
  mdiCalendar,
  mdiCheck,
  mdiClose,
  mdiAlertCircle,
  mdiFilter,
  mdiAccountMultiple,
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import { ref, computed, watch, onMounted } from 'vue'

const props = defineProps({
  siswas: {
    type: Array,
    default: () => [],
  },
  tanggal: {
    type: String,
    default: () => new Date().toISOString().substr(0, 10),
  },
  pembimbingOptions: {
    type: Array,
    default: () => [],
  },
  pembimbingId: {
    type: [String, Number],
    default: '',
  },
  existingAttendances: {
    type: Object,
    default: () => ({}),
  },
})

const selectedTanggal = ref(props.tanggal)
const selectedPembimbing = ref(String(props.pembimbingId || ''))

const hasFilter = computed(() => {
  return Boolean(props.pembimbingId && props.tanggal)
})

const form = useForm({
  tanggal: props.tanggal,
  attendances: {},
})

const syncAttendances = () => {
  form.tanggal = props.tanggal
  selectedTanggal.value = props.tanggal
  selectedPembimbing.value = String(props.pembimbingId || '')

  const initial = {}
  props.siswas.forEach((s) => {
    const existing = props.existingAttendances ? props.existingAttendances[s.id] : null
    if (existing) {
      if (existing.hadir) initial[s.id] = 'hadir'
      else if (existing.izin) initial[s.id] = 'izin'
      else if (existing.sakit) initial[s.id] = 'sakit'
      else if (existing.alpha) initial[s.id] = 'alpha'
      else initial[s.id] = 'hadir'
    } else {
      initial[s.id] = 'hadir'
    }
  })
  form.attendances = initial
}

onMounted(() => {
  syncAttendances()
})

watch(
  () => [props.siswas, props.tanggal, props.existingAttendances, props.pembimbingId],
  () => {
    syncAttendances()
  },
  { deep: true }
)

const searchQuery = ref('')

const filteredSiswas = computed(() => {
  if (!searchQuery.value) return props.siswas
  const q = searchQuery.value.toLowerCase()
  return props.siswas.filter(
    (s) => s.nama.toLowerCase().includes(q) || s.kelas.toLowerCase().includes(q),
  )
})

const statusOptions = [
  { value: 'hadir', label: 'Hadir', color: 'text-green-700 bg-green-100 dark:bg-green-900 dark:text-green-300', border: 'border-green-400' },
  { value: 'izin', label: 'Izin', color: 'text-blue-700 bg-blue-100 dark:bg-blue-900 dark:text-blue-300', border: 'border-blue-400' },
  { value: 'sakit', label: 'Sakit', color: 'text-yellow-700 bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-300', border: 'border-yellow-400' },
  { value: 'alpha', label: 'Alpha', color: 'text-red-700 bg-red-100 dark:bg-red-900 dark:text-red-300', border: 'border-red-400' },
]

const getStatus = (siswaId) => {
  return form.attendances[siswaId] || 'hadir'
}

const setStatus = (siswaId, status) => {
  form.attendances[siswaId] = status
}

const allHadir = () => {
  props.siswas.forEach((s) => {
    form.attendances[s.id] = 'hadir'
  })
}

const applyFilter = () => {
  if (!selectedPembimbing.value) {
    alert('Silakan pilih pembimbing terlebih dahulu.')
    return
  }
  if (!selectedTanggal.value) {
    alert('Silakan pilih tanggal terlebih dahulu.')
    return
  }
  router.get(route('admin.rekap-absensi.pembekalan.create'), {
    tanggal: selectedTanggal.value,
    pembimbing_id: selectedPembimbing.value,
  }, {
    preserveState: false,
    preserveScroll: true,
    replace: true,
  })
}

const submit = () => {
  const attendancesArray = Object.entries(form.attendances).map(([siswaId, status]) => ({
    siswa_id: parseInt(siswaId),
    status,
  }))

  if (attendancesArray.length === 0) {
    alert('Belum ada data absensi yang diisi.')
    return
  }

  form.transform(() => ({
    tanggal: form.tanggal || selectedTanggal.value || props.tanggal,
    attendances: attendancesArray,
  }))

  form.post(route('admin.rekap-absensi.pembekalan.store'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Input Absensi Pembekalan" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiBookOpenPageVariant"
        title="Input Absensi Pembekalan"
        main
      >
        <BaseButton
          :to="route('admin.rekap-absensi.index', { tab: 'pembekalan' })"
          :icon="mdiArrowLeft"
          label="Kembali"
          color="whiteDark"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>

      <!-- Filter Card -->
      <CardBox class="mb-6 shadow-md rounded-lg">
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
          <div class="md:col-span-4">
            <FormField label="Pilih Tanggal">
              <FormControl
                v-model="selectedTanggal"
                :icon="mdiCalendar"
                type="date"
              />
            </FormField>
          </div>
          <div class="md:col-span-4">
            <FormField label="Pilih Pembimbing">
              <select
                v-model="selectedPembimbing"
                class="px-3 py-2 max-w-full focus:ring focus:outline-none border-gray-700 rounded w-full dark:placeholder-gray-400 h-12 border bg-white dark:bg-slate-800"
              >
                <option value="">-- Pilih Pembimbing --</option>
                <option v-for="p in pembimbingOptions" :key="p.id" :value="p.id">
                  {{ p.nama_guru }}
                </option>
              </select>
            </FormField>
          </div>
          <div class="md:col-span-4">
            <BaseButton
              color="info"
              label="Tampilkan"
              :icon="mdiFilter"
              @click="applyFilter"
              rounded-full
              small
            />
          </div>
        </div>
      </CardBox>

      <form @submit.prevent="submit">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
          <!-- Left sidebar -->
          <div class="lg:col-span-1 space-y-4">
            <CardBox class="shadow-md">
              <FormField v-if="hasFilter" label="Cari Siswa">
                <FormControl
                  v-model="searchQuery"
                  placeholder="Cari nama/kelas..."
                />
              </FormField>

              <div v-if="hasFilter" class="flex flex-col gap-2 pt-2">
                <BaseButton
                  color="info"
                  label="Semua Hadir"
                  :icon="mdiCheck"
                  @click="allHadir"
                  small
                  rounded-full
                />
              </div>

              <div v-if="hasFilter" class="mt-4 text-xs text-gray-500">
                <p>Total siswa: {{ props.siswas.length }}</p>
                <p>Terisi: {{ Object.keys(form.attendances).length }} / {{ props.siswas.length }}</p>
              </div>
            </CardBox>

            <!-- Legend -->
            <CardBox class="shadow-md">
              <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-300 mb-3">Keterangan</h4>
              <div class="space-y-2">
                <div v-for="opt in statusOptions" :key="opt.value" class="flex items-center gap-2">
                  <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold" :class="opt.color">
                    {{ opt.label.charAt(0) }}
                  </span>
                  <span class="text-sm">{{ opt.label }}</span>
                </div>
              </div>
            </CardBox>
          </div>

          <!-- Student List -->
          <div class="lg:col-span-3">
            <CardBox class="shadow-md" has-table>
              <div v-if="!hasFilter" class="py-12 text-center text-gray-500">
                <BaseIcon :path="mdiAccountMultiple" size="48" class="mb-4" />
                <p class="text-lg">Silakan pilih tanggal dan pembimbing terlebih dahulu, lalu klik Tampilkan</p>
              </div>
              <div v-else-if="filteredSiswas.length === 0" class="py-12 text-center text-gray-500">
                <BaseIcon :path="mdiAlertCircle" size="48" class="mb-4" />
                <p class="text-lg">Tidak ada siswa bimbingan untuk pembimbing yang dipilih</p>
              </div>
              <div v-else class="overflow-x-auto">
                <table class="table-auto w-full">
                  <thead class="bg-gray-50 dark:bg-slate-800 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold tracking-wider">
                    <tr>
                      <th class="px-4 py-3 text-left">No</th>
                      <th class="px-4 py-3 text-left">Nama Murid</th>
                      <th class="px-4 py-3 text-left">Kelas</th>
                      <th class="px-4 py-3 text-left">Bengkel</th>
                      <th class="px-4 py-3 text-center" colspan="4">Absensi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
                    <tr
                      v-for="(siswa, index) in filteredSiswas"
                      :key="siswa.id"
                      class="hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors duration-150"
                    >
                      <td class="px-4 py-3 text-gray-500 text-sm">{{ index + 1 }}</td>
                      <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ siswa.nama }}</td>
                      <td class="px-4 py-3 text-sm text-gray-500">{{ siswa.kelas }}</td>
                      <td class="px-4 py-3 text-sm text-gray-500">{{ siswa.bengkel }}</td>
                      <td class="px-2 py-2 text-center">
                        <button
                          type="button"
                          v-for="opt in statusOptions"
                          :key="opt.value"
                          @click="setStatus(siswa.id, opt.value)"
                          :class="[
                            'inline-flex items-center justify-center w-9 h-9 rounded-full text-xs font-bold border-2 transition-all duration-150',
                            getStatus(siswa.id) === opt.value
                              ? opt.color + ' ' + opt.border
                              : 'text-gray-400 border-gray-200 dark:border-slate-600 hover:border-gray-400 dark:hover:border-slate-400 bg-transparent'
                          ]"
                        >
                          {{ opt.label.charAt(0) }}
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div v-if="hasFilter && filteredSiswas.length > 0" class="p-4 border-t border-gray-100 dark:border-slate-800 bg-gray-50 dark:bg-slate-900 rounded-b-lg flex justify-end gap-3">
                <BaseButton
                  type="submit"
                  color="success"
                  label="Simpan Absensi"
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
