<script setup>
import axios from 'axios'
import { Head, useForm, router } from '@inertiajs/vue3'
import {
  mdiAccountSchool,
  mdiTrashCan,
  mdiAlertCircle,
  mdiPlus,
  mdiPencil,
  mdiMagnify,
  mdiRefresh,
  mdiSchool,
  mdiFileExcel,
  mdiDownload,
  mdiEye,
  mdiEyeOff,
  mdiContentCopy,
  mdiCheck,
  mdiKey,
  mdiAccountCheck,
  mdiAccountAlert,
  mdiDomain,
  mdiFilterVariant,
  mdiLockReset,
  mdiLockOutline,
  mdiAccountPlus,
  mdiCheckCircle,
  mdiInformationOutline,
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import UserAvatar from '@admin/components/UserAvatar.vue'
import PillTag from '@admin/components/PillTag.vue'
import CardBox from '@admin/components/CardBox.vue'
import CardBoxWidget from '@admin/components/CardBoxWidget.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import FormControlSearchable from '@admin/components/FormControlSearchable.vue'
import { computed, ref, watch } from 'vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  users: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  jurusans: {
    type: Array,
    default: () => [],
  },
  kelas: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({
      total_users: 0,
      total_linked: 0,
      total_unlinked: 0,
      total_kelas: 0,
    }),
  },
  unlinkedSiswas: {
    type: Array,
    default: () => [],
  },
})

// Filter states
const search = ref(props.filters?.search || '')
const jurusanId = ref(props.filters?.jurusan_id || '')
const kelasId = ref(props.filters?.kelas_id || '')
const perPage = ref(Number(props.filters?.per_page) || 20)
const perPageOptions = [10, 20, 50, 100, 250, 500]

// Jurusan options for dropdown
const jurusanOptions = computed(() => {
  const list = (props.jurusans || []).map((j) => ({
    id: j.id,
    label: j.kode_jurusan ? `${j.nama_jurusan} (${j.kode_jurusan})` : j.nama_jurusan,
  }))
  return [{ id: '', label: 'Semua Jurusan' }, ...list]
})

// Kelas options for filter (filtered by selected jurusan)
const filteredKelasOptions = computed(() => {
  let list = props.kelas || []
  if (jurusanId.value) {
    list = list.filter((k) => Number(k.jurusan_id) === Number(jurusanId.value))
  }
  const mapped = list.map((k) => ({
    id: k.id,
    label: k.nama_kelas,
  }))
  return [{ id: '', label: 'Semua Kelas' }, ...mapped]
})

// Watch jurusan change to reset incompatible kelas
watch(jurusanId, (newVal) => {
  if (newVal && kelasId.value) {
    const exists = (props.kelas || []).some(
      (k) => Number(k.jurusan_id) === Number(newVal) && Number(k.id) === Number(kelasId.value)
    )
    if (!exists) {
      kelasId.value = ''
    }
  }
})

// Debounced filter dispatch
const updateFilters = debounce(() => {
  router.get(
    route('admin.users.siswa.index'),
    {
      search: search.value || undefined,
      jurusan_id: jurusanId.value || undefined,
      kelas_id: kelasId.value || undefined,
      per_page: perPage.value,
    },
    { preserveState: true, replace: true }
  )
}, 300)

watch([search, jurusanId, kelasId, perPage], () => {
  updateFilters()
})

const isFiltered = computed(() => {
  return !!(search.value || jurusanId.value || kelasId.value)
})

const resetFilter = () => {
  search.value = ''
  jurusanId.value = ''
  kelasId.value = ''
  selectedIds.value = []
}

// Bulk Delete State
const selectedIds = ref([])
const userData = computed(() =>
  props.users && Array.isArray(props.users.data) ? props.users.data : []
)
const userLinks = computed(() =>
  props.users && Array.isArray(props.users.links) ? props.users.links : []
)

const allSelected = computed({
  get: () => {
    return userData.value.length > 0 && selectedIds.value.length === userData.value.length
  },
  set: (value) => {
    if (value) {
      selectedIds.value = userData.value.map((user) => user.id)
    } else {
      selectedIds.value = []
    }
  },
})

const deleteForm = useForm({})
const bulkDelete = () => {
  if (confirm(`Apakah Anda yakin ingin menghapus ${selectedIds.value.length} user siswa yang dipilih?`)) {
    router.post(
      route('admin.users.siswa.bulk_destroy'),
      { ids: selectedIds.value },
      {
        onSuccess: () => {
          selectedIds.value = []
        },
      }
    )
  }
}

const bulkResetPassword = () => {
  if (confirm(`Apakah Anda yakin ingin me-reset password untuk ${selectedIds.value.length} user siswa terpilih menjadi 'password'?`)) {
    router.post(
      route('admin.users.siswa.bulk_reset_password'),
      { ids: selectedIds.value, password: 'password' },
      {
        preserveScroll: true,
        onSuccess: () => {
          selectedIds.value = []
        },
      }
    )
  }
}

const deleteUser = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus user siswa ini?')) {
    deleteForm.delete(route('admin.users.siswa.destroy', id))
  }
}

// Reset Password Modal State & Handlers
const isResetModalActive = ref(false)
const resetUser = ref(null)
const showResetPassword = ref(false)
const showConfirmResetPassword = ref(false)

const resetForm = useForm({
  password: '',
  password_confirmation: '',
})

const openResetModal = (user) => {
  resetUser.value = user
  resetForm.reset()
  resetForm.clearErrors()
  showResetPassword.value = false
  showConfirmResetPassword.value = false
  isResetModalActive.value = true
}

const submitResetPassword = () => {
  if (!resetUser.value) return
  if (!resetForm.password_confirmation && resetForm.password) {
    resetForm.password_confirmation = resetForm.password
  }

  resetForm.put(route('admin.users.siswa.reset_password', resetUser.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      isResetModalActive.value = false
      resetUser.value = null
      resetForm.reset()
    },
  })
}

// ----------------------------------------------------
// Unified Modal Form Logic (Clean, Direct & Robust)
// ----------------------------------------------------
const form = useForm({
  name: '',
  username: '',
  kelas_id: '',
  siswa_id: null,
  password: 'password',
  password_confirmation: 'password',
})

const isModalActive = ref(false)
const isImportModalActive = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

// Kelas options for modal select
const modalKelasOptions = computed(() => {
  const options = (props.kelas || []).map((k) => ({ id: k.id, label: k.nama_kelas }))
  return [{ id: '', label: '-- Pilih Kelas Siswa --' }, ...options]
})

// Candidates list for quick auto-fill when class is selected
const candidateList = ref([])
const isLoadingCandidates = ref(false)
const selectedCandidate = ref(null)

const candidateOptions = computed(() => {
  const placeholder = { id: null, label: '-- Pilih siswa untuk isi otomatis (opsional) --' }
  const mapped = candidateList.value.map((s) => {
    const isLinked = !!s.siswa_user_id
    return {
      id: s.id,
      nama: s.nama,
      nisn: s.nisn,
      label: s.nisn ? `${s.nama} (${s.nisn})${isLinked ? ' - sudah ada akun' : ''}` : s.nama,
      disabled: isLinked,
    }
  })
  return [placeholder, ...mapped]
})

// Auto-fill when candidate chosen
watch(selectedCandidate, (candidate) => {
  if (!candidate || !candidate.id) return
  form.siswa_id = candidate.id
  if (candidate.nama) {
    form.name = candidate.nama
  }
  if (candidate.nisn) {
    form.username = candidate.nisn
    if (!form.password) {
      form.password = 'password'
      form.password_confirmation = 'password'
    }
  }
})

// Fetch class candidates when kelas_id changes in Add mode
watch(
  () => form.kelas_id,
  async (newKelasId) => {
    if (isEditing.value || !newKelasId) {
      candidateList.value = []
      selectedCandidate.value = null
      return
    }

    isLoadingCandidates.value = true
    selectedCandidate.value = null

    try {
      const { data } = await axios.get(route('admin.users.siswa.candidates'), {
        params: { kelas_id: newKelasId },
      })
      candidateList.value = Array.isArray(data?.data) ? data.data : []
    } catch (e) {
      candidateList.value = []
    } finally {
      isLoadingCandidates.value = false
    }
  }
)

const openModal = () => {
  isEditing.value = false
  editingId.value = null
  form.reset()
  form.clearErrors()
  form.password = 'password'
  form.password_confirmation = 'password'
  candidateList.value = []
  selectedCandidate.value = null
  showPassword.value = false
  showConfirmPassword.value = false
  isModalActive.value = true
}

const editUser = (user) => {
  isEditing.value = true
  editingId.value = user.id
  form.clearErrors()
  form.name = user.name || ''
  form.username = user.username || ''
  form.kelas_id = user.siswa?.kelas_id || ''
  form.siswa_id = user.siswa?.id || null
  form.password = ''
  form.password_confirmation = ''
  showPassword.value = false
  showConfirmPassword.value = false
  isModalActive.value = true
}

const setPasswordToDefault = () => {
  form.password = 'password'
  form.password_confirmation = 'password'
}

const submit = () => {
  if (!form.password_confirmation && form.password) {
    form.password_confirmation = form.password
  }

  if (isEditing.value && editingId.value) {
    form.put(route('admin.users.siswa.update', editingId.value), {
      preserveScroll: true,
      onSuccess: () => {
        isModalActive.value = false
        form.reset()
      },
    })
  } else {
    form.post(route('admin.users.siswa.store'), {
      preserveScroll: true,
      onSuccess: () => {
        isModalActive.value = false
        form.reset()
      },
    })
  }
}

// Import form
const importForm = useForm({
  file: null,
})

const openImportModal = () => {
  importForm.reset()
  isImportModalActive.value = true
}

const submitImport = () => {
  importForm.post(route('admin.users.siswa.import'), {
    onSuccess: () => {
      isImportModalActive.value = false
      importForm.reset()
    },
  })
}

const downloadTemplate = () => {
  window.location.href = route('admin.users.siswa.template')
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Manajemen User Siswa" />

    <SectionMain>
      <!-- Page Title & Header Actions -->
      <SectionTitleLineWithButton :icon="mdiAccountSchool" title="Manajemen User Siswa" main>
        <div class="flex items-center gap-2.5">
          <BaseButton
            :icon="mdiFileExcel"
            label="Import User"
            color="success"
            rounded-full
            small
            :auto-permission="false"
            class="shadow-sm hover:shadow transition-all"
            @click="openImportModal"
          />
          <BaseButton
            :icon="mdiPlus"
            label="Tambah User Siswa"
            color="contrast"
            rounded-full
            small
            :auto-permission="false"
            class="shadow-sm hover:shadow transition-all"
            @click="openModal"
          />
        </div>
      </SectionTitleLineWithButton>

      <!-- Metric Stats Overview Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <CardBoxWidget
          :number="stats.total_users || 0"
          :icon="mdiAccountSchool"
          color="text-indigo-500 dark:text-indigo-400"
          label="Total User Siswa"
          class="border border-indigo-100/60 dark:border-indigo-900/30 bg-gradient-to-br from-white to-indigo-50/30 dark:from-slate-900 dark:to-indigo-950/20 shadow-sm hover:shadow-md transition-shadow"
        />
        <CardBoxWidget
          :number="stats.total_linked || 0"
          :icon="mdiAccountCheck"
          color="text-emerald-500 dark:text-emerald-400"
          label="Akun Terhubung Siswa"
          class="border border-emerald-100/60 dark:border-emerald-900/30 bg-gradient-to-br from-white to-emerald-50/30 dark:from-slate-900 dark:to-emerald-950/20 shadow-sm hover:shadow-md transition-shadow"
        />
        <CardBoxWidget
          :number="stats.total_unlinked || 0"
          :icon="mdiAccountAlert"
          color="text-amber-500 dark:text-amber-400"
          label="Siswa Belum Punya Akun"
          class="border border-amber-100/60 dark:border-amber-900/30 bg-gradient-to-br from-white to-amber-50/30 dark:from-slate-900 dark:to-amber-950/20 shadow-sm hover:shadow-md transition-shadow"
        />
        <CardBoxWidget
          :number="stats.total_kelas || 0"
          :icon="mdiSchool"
          color="text-sky-500 dark:text-sky-400"
          label="Total Kelas Terdaftar"
          class="border border-sky-100/60 dark:border-sky-900/30 bg-gradient-to-br from-white to-sky-50/30 dark:from-slate-900 dark:to-sky-950/20 shadow-sm hover:shadow-md transition-shadow"
        />
      </div>

      <!-- Flash Messages -->
      <NotificationBar
        v-if="$page.props.flash && $page.props.flash.message"
        color="success"
        :icon="mdiAlertCircle"
        class="mb-6 shadow-sm"
      >
        {{ $page.props.flash.message }}
      </NotificationBar>

      <NotificationBar
        v-if="$page.props.flash && $page.props.flash.error"
        color="danger"
        :icon="mdiAlertCircle"
        class="mb-6 shadow-sm"
      >
        {{ $page.props.flash.error }}
      </NotificationBar>

      <!-- Advanced Filters Card -->
      <CardBox class="shadow-lg border border-slate-100/80 dark:border-slate-800/80 rounded-2xl overflow-hidden mb-6 bg-white/90 dark:bg-slate-900/90 backdrop-blur">
        <div class="p-5">
          <div class="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                <BaseIcon :path="mdiFilterVariant" size="18" />
              </div>
              <span class="font-bold text-sm text-slate-800 dark:text-slate-100">Filter & Pencarian</span>
            </div>

            <div class="flex items-center gap-2">
              <BaseButton
                v-if="isFiltered"
                :icon="mdiRefresh"
                label="Reset Filter"
                color="whiteDark"
                small
                rounded-full
                class="text-xs"
                @click="resetFilter"
              />
              <BaseButton
                v-if="selectedIds.length > 0"
                :icon="mdiLockReset"
                :label="'Reset Sandi (' + selectedIds.length + ')'"
                color="warning"
                small
                rounded-full
                class="shadow-sm text-xs font-semibold"
                @click="bulkResetPassword"
              />
              <BaseButton
                v-if="selectedIds.length > 0"
                :icon="mdiTrashCan"
                :label="'Hapus (' + selectedIds.length + ')'"
                color="danger"
                small
                rounded-full
                class="shadow-sm text-xs font-semibold animate-pulse"
                @click="bulkDelete"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
            <!-- Search Input -->
            <div class="sm:col-span-2 lg:col-span-4">
              <FormField label="Pencarian">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <BaseIcon :path="mdiMagnify" :size="17" class="text-slate-400" />
                  </div>
                  <input
                    v-model="search"
                    type="text"
                    placeholder="Nama, NISN, atau kelas..."
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/40 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all"
                  />
                </div>
              </FormField>
            </div>

            <!-- Filter Jurusan -->
            <div class="lg:col-span-3">
              <FormField label="Jurusan">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <BaseIcon :path="mdiDomain" :size="17" class="text-slate-400" />
                  </div>
                  <select
                    v-model="jurusanId"
                    class="w-full pl-10 pr-8 py-2 text-sm rounded-xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/40 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all appearance-none cursor-pointer"
                  >
                    <option v-for="j in jurusanOptions" :key="j.id" :value="j.id">
                      {{ j.label }}
                    </option>
                  </select>
                </div>
              </FormField>
            </div>

            <!-- Filter Kelas -->
            <div class="lg:col-span-3">
              <FormField label="Kelas">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <BaseIcon :path="mdiSchool" :size="17" class="text-slate-400" />
                  </div>
                  <select
                    v-model="kelasId"
                    class="w-full pl-10 pr-8 py-2 text-sm rounded-xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/40 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all appearance-none cursor-pointer"
                  >
                    <option v-for="k in filteredKelasOptions" :key="k.id" :value="k.id">
                      {{ k.label }}
                    </option>
                  </select>
                </div>
              </FormField>
            </div>

            <!-- Tampilkan Per Page -->
            <div class="lg:col-span-2">
              <FormField label="Tampilkan">
                <select
                  v-model="perPage"
                  class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200/80 dark:border-slate-700/80 bg-slate-50/50 dark:bg-slate-800/40 text-slate-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-all appearance-none cursor-pointer"
                >
                  <option v-for="option in perPageOptions" :key="option" :value="option">
                    {{ option }} baris
                  </option>
                </select>
              </FormField>
            </div>
          </div>
        </div>
      </CardBox>

      <!-- Main Data Table Box -->
      <CardBox class="shadow-xl border border-slate-100/90 dark:border-slate-800/90 rounded-2xl overflow-hidden mb-6" has-table>
        <!-- Mobile View (Card Grid) -->
        <div class="grid grid-cols-1 md:hidden gap-4 p-4 bg-slate-50/70 dark:bg-slate-900/50">
          <div
            v-for="user in userData"
            :key="user.id"
            class="bg-white dark:bg-slate-800 rounded-2xl p-4 shadow-sm border border-slate-100 dark:border-slate-700/80 relative transition-all"
          >
            <div class="absolute top-4 right-4">
              <input
                type="checkbox"
                :value="user.id"
                v-model="selectedIds"
                class="rounded-lg border-slate-300 text-indigo-600 shadow-sm focus:ring focus:ring-indigo-200 cursor-pointer"
              />
            </div>

            <div class="flex items-center space-x-3.5 mb-3.5 pr-8">
              <div class="w-11 h-11 flex-shrink-0">
                <UserAvatar :username="user.name" api="initials" class="w-full h-full rounded-2xl bg-indigo-50 dark:bg-slate-700" />
              </div>
              <div class="overflow-hidden">
                <h3 class="font-bold text-slate-800 dark:text-white truncate">{{ user.name }}</h3>
                <span class="inline-flex items-center text-xs font-mono text-slate-500 dark:text-slate-400">
                  NISN: {{ user.username }}
                </span>
              </div>
            </div>

            <!-- Jurusan & Kelas Tag -->
            <div class="flex flex-wrap gap-2 items-center mb-3 p-2.5 bg-slate-50 dark:bg-slate-900/60 rounded-xl">
              <div class="text-xs text-slate-500 font-semibold uppercase tracking-wider">Kelas:</div>
              <PillTag
                v-if="user.siswa?.kelas"
                :label="user.siswa.kelas.nama_kelas"
                color="info"
                small
              />
              <span v-else class="text-xs text-slate-400">-</span>

              <PillTag
                v-if="user.siswa?.kelas?.jurusan"
                :label="user.siswa.kelas.jurusan.kode_jurusan || user.siswa.kelas.jurusan.nama_jurusan"
                color="contrast"
                small
                outline
              />
            </div>

            <!-- Actions -->
            <div class="grid grid-cols-3 gap-2">
              <BaseButton
                color="warning"
                :icon="mdiLockReset"
                label="Reset"
                small
                outline
                class="w-full justify-center rounded-xl"
                title="Reset Password Siswa"
                @click="openResetModal(user)"
              />
              <BaseButton
                color="info"
                :icon="mdiPencil"
                label="Edit"
                small
                outline
                class="w-full justify-center rounded-xl"
                title="Edit Data Siswa"
                @click="editUser(user)"
              />
              <BaseButton
                color="danger"
                :icon="mdiTrashCan"
                label="Hapus"
                small
                outline
                class="w-full justify-center rounded-xl"
                title="Hapus Akun Siswa"
                @click="deleteUser(user.id)"
              />
            </div>
          </div>

          <div v-if="userData.length === 0" class="text-center py-10 text-slate-500 col-span-1">
            <BaseIcon :path="mdiAccountSchool" size="48" class="mx-auto text-slate-300 mb-2" />
            <p class="font-medium text-sm">Data user siswa tidak ditemukan.</p>
          </div>
        </div>

        <!-- Desktop View (Modern Table) -->
        <div class="hidden md:block overflow-x-auto">
          <table class="w-full text-left text-sm border-collapse">
            <thead>
              <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-900/40 text-xs uppercase font-semibold text-slate-500 dark:text-slate-400 tracking-wider">
                <th class="py-3.5 px-4 w-12 text-center">
                  <input
                    type="checkbox"
                    v-model="allSelected"
                    class="rounded-lg border-slate-300 text-indigo-600 shadow-sm focus:ring focus:ring-indigo-200 cursor-pointer"
                  />
                </th>
                <th class="py-3.5 px-3 w-12 text-center">No</th>
                <th class="py-3.5 px-4">Siswa</th>
                <th class="py-3.5 px-4">Jurusan</th>
                <th class="py-3.5 px-4">Kelas</th>
                <th class="py-3.5 px-4 text-center">Status</th>
                <th class="py-3.5 px-4 text-right pr-6">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
              <tr
                v-for="(user, index) in userData"
                :key="user.id"
                class="hover:bg-slate-50/70 dark:hover:bg-slate-800/40 transition-colors group"
              >
                <!-- Checkbox -->
                <td class="py-3 px-4 text-center">
                  <input
                    type="checkbox"
                    :value="user.id"
                    v-model="selectedIds"
                    class="rounded-lg border-slate-300 text-indigo-600 shadow-sm focus:ring focus:ring-indigo-200 cursor-pointer"
                  />
                </td>

                <!-- Row Number -->
                <td class="py-3 px-3 text-center text-xs font-mono text-slate-400">
                  {{ (users.current_page - 1) * users.per_page + index + 1 }}
                </td>

                <!-- Siswa Info (Avatar, Name, NISN) -->
                <td class="py-3 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 flex-shrink-0">
                      <UserAvatar
                        :username="user.name"
                        api="initials"
                        class="w-full h-full rounded-xl bg-indigo-50 dark:bg-slate-700 shadow-xs"
                      />
                    </div>
                    <div>
                      <div class="font-bold text-slate-800 dark:text-slate-100 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                        {{ user.name }}
                      </div>
                      <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400 font-mono">
                        <span>NISN:</span>
                        <span class="font-semibold text-slate-700 dark:text-slate-300">{{ user.username }}</span>
                      </div>
                    </div>
                  </div>
                </td>

                <!-- Jurusan -->
                <td class="py-3 px-4">
                  <PillTag
                    v-if="user.siswa?.kelas?.jurusan"
                    :label="user.siswa.kelas.jurusan.kode_jurusan || user.siswa.kelas.jurusan.nama_jurusan"
                    color="contrast"
                    small
                    outline
                  />
                  <span v-else class="text-slate-400 text-xs">-</span>
                </td>

                <!-- Kelas -->
                <td class="py-3 px-4">
                  <PillTag
                    v-if="user.siswa?.kelas"
                    :label="user.siswa.kelas.nama_kelas"
                    color="info"
                    small
                  />
                  <span v-else class="text-slate-400 text-xs italic">Belum diatur</span>
                </td>

                <!-- Status Akun -->
                <td class="py-3 px-4 text-center">
                  <PillTag
                    v-if="user.siswa?.id"
                    label="Terhubung"
                    color="success"
                    small
                  />
                  <PillTag
                    v-else
                    label="Tanpa Data"
                    color="warning"
                    small
                    outline
                  />
                </td>

                <!-- Actions -->
                <td class="py-3 px-4 text-right pr-6 whitespace-nowrap">
                  <BaseButtons type="justify-end" no-wrap>
                    <BaseButton
                      color="warning"
                      :icon="mdiLockReset"
                      small
                      rounded-full
                      class="hover:shadow-sm"
                      title="Reset Password Siswa"
                      @click="openResetModal(user)"
                    />
                    <BaseButton
                      color="info"
                      :icon="mdiPencil"
                      small
                      rounded-full
                      class="hover:shadow-sm"
                      title="Edit User Siswa"
                      @click="editUser(user)"
                    />
                    <BaseButton
                      color="danger"
                      :icon="mdiTrashCan"
                      small
                      rounded-full
                      class="hover:shadow-sm"
                      title="Hapus User Siswa"
                      @click="deleteUser(user.id)"
                    />
                  </BaseButtons>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="userData.length === 0">
                <td colspan="7" class="py-12 text-center text-slate-500">
                  <div class="flex flex-col items-center justify-center">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400 mb-3">
                      <BaseIcon :path="mdiAccountSchool" size="32" />
                    </div>
                    <p class="font-bold text-slate-700 dark:text-slate-300 mb-1">Data User Siswa Tidak Ditemukan</p>
                    <p class="text-xs text-slate-400">Coba ubah kata kunci pencarian atau sesuaikan filter jurusan dan kelas.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination Controls -->
        <div v-if="userLinks.length > 3" class="p-4 lg:px-6 border-t border-slate-100 dark:border-slate-800 bg-slate-50/40 dark:bg-slate-900/20">
          <BaseLevel>
            <span class="text-xs text-slate-500">
              Menampilkan <span class="font-bold text-slate-700 dark:text-slate-200">{{ users.from || 0 }}</span> - <span class="font-bold text-slate-700 dark:text-slate-200">{{ users.to || 0 }}</span> dari <span class="font-bold text-slate-700 dark:text-slate-200">{{ users.total || 0 }}</span> siswa
            </span>
            <BaseButtons>
              <BaseButton
                v-for="(link, key) in userLinks"
                :key="key"
                :active="link.active"
                :label="link.label.replace(/&laquo; /g, '« ').replace(/ &raquo;/g, ' »').replace(/&laquo;/g, '«').replace(/&raquo;/g, '»')"
                :color="link.active ? 'lightDark' : 'whiteDark'"
                :disabled="!link.url"
                small
                rounded-full
                class="text-xs"
                @click="link.url && router.visit(link.url)"
              />
            </BaseButtons>
          </BaseLevel>
        </div>
      </CardBox>

      <!-- ========================================================= -->
      <!-- MODAL POPUP: Tambah / Edit User Siswa (Sleek & Unified UI) -->
      <!-- ========================================================= -->
      <CardBoxModal
        v-model="isModalActive"
        :title="isEditing ? 'Edit User Siswa' : 'Tambah User Siswa'"
        has-cancel
      >
        <!-- Modal Subtitle & Header Badge -->
        <div class="flex items-center gap-3 p-3 mb-4 rounded-2xl border" :class="isEditing ? 'bg-sky-50/50 dark:bg-sky-950/30 border-sky-100 dark:border-sky-900/40' : 'bg-indigo-50/50 dark:bg-indigo-950/30 border-indigo-100 dark:border-indigo-900/40'">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" :class="isEditing ? 'bg-sky-500 text-white shadow-md shadow-sky-500/20' : 'bg-indigo-600 text-white shadow-md shadow-indigo-600/20'">
            <BaseIcon :path="isEditing ? mdiPencil : mdiAccountPlus" size="20" />
          </div>
          <div>
            <h4 class="text-sm font-bold text-slate-800 dark:text-slate-100">
              {{ isEditing ? 'Perbarui Kredensial Siswa' : 'Form Pembuatan Akun Siswa' }}
            </h4>
            <p class="text-xs text-slate-500 dark:text-slate-400">
              {{ isEditing ? 'Ubah data kelas, nama, atau reset password akun siswa.' : 'Lengkapi data kelas, nama, dan NISN untuk membuat akun login siswa.' }}
            </p>
          </div>
        </div>

        <!-- Validation Errors Banner -->
        <div
          v-if="Object.keys(form.errors).length > 0"
          class="p-3.5 mb-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-xs text-rose-600 dark:text-rose-400 space-y-1.5"
        >
          <div class="font-bold flex items-center gap-1.5">
            <BaseIcon :path="mdiAlertCircle" size="16" />
            <span>Terdapat beberapa kesalahan pengisian:</span>
          </div>
          <ul class="list-disc list-inside pl-1 space-y-0.5">
            <li v-for="(err, key) in form.errors" :key="key">{{ err }}</li>
          </ul>
        </div>

        <!-- UNIFIED SINGLE FORM -->
        <div class="space-y-4">
          <!-- 1. Kelas Siswa -->
          <FormField label="Pilih Kelas" :error="form.errors.kelas_id" help="Pilih kelas siswa yang terdaftar">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <BaseIcon :path="mdiSchool" size="18" />
              </div>
              <select
                v-model="form.kelas_id"
                class="w-full pl-10 pr-8 py-2.5 text-sm rounded-2xl border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all appearance-none cursor-pointer"
              >
                <option v-for="opt in modalKelasOptions" :key="opt.id" :value="opt.id">
                  {{ opt.label }}
                </option>
              </select>
            </div>
          </FormField>

          <!-- 2. Auto-Fill Siswa Quick Picker (Only in Create mode when class is selected) -->
          <div v-if="!isEditing && form.kelas_id" class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-200/80 dark:border-slate-700/80 space-y-2">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-600 dark:text-slate-300">
              <span class="flex items-center gap-1.5">
                <BaseIcon :path="mdiInformationOutline" size="15" class="text-indigo-500" />
                Pilih dari Siswa Terdaftar (Isi Otomatis):
              </span>
              <span v-if="isLoadingCandidates" class="text-indigo-500 text-[11px] font-normal">Memuat data...</span>
            </div>

            <FormControlSearchable
              v-model="selectedCandidate"
              :options="candidateOptions"
              :icon="mdiAccountSchool"
              placeholder="Cari & pilih nama siswa di kelas ini..."
              :transparent="false"
              :borderless="false"
            />
            <p class="text-[11px] text-slate-400">
              Memilih siswa akan otomatis mengisi Nama, NISN, dan Password default ('password'). Atau Anda bisa mengetik langsung di bawah.
            </p>
          </div>

          <!-- 3. Nama Lengkap Siswa -->
          <FormField label="Nama Lengkap Siswa" :error="form.errors.name">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <BaseIcon :path="mdiAccountSchool" size="18" />
              </div>
              <input
                v-model="form.name"
                type="text"
                placeholder="Masukkan nama lengkap siswa..."
                class="w-full pl-10 pr-4 py-2.5 text-sm rounded-2xl border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
              />
            </div>
          </FormField>

          <!-- 4. Username / NISN -->
          <FormField label="Username / NISN" :error="form.errors.username" help="Gunakan NISN sebagai username login siswa">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <BaseIcon :path="mdiKey" size="18" />
              </div>
              <input
                v-model="form.username"
                type="text"
                placeholder="Masukkan NISN siswa..."
                class="w-full pl-10 pr-4 py-2.5 text-sm rounded-2xl border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 font-mono focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
              />
            </div>
          </FormField>

          <!-- 5. Credentials Password Box -->
          <div class="pt-3 border-t border-slate-100 dark:border-slate-800 space-y-3.5">
            <div class="flex items-center justify-between">
              <label class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">
                Kata Sandi (Password)
              </label>
              <button
                type="button"
                class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-semibold flex items-center gap-1 cursor-pointer hover:underline"
                @click="setPasswordToDefault"
              >
                <BaseIcon :path="mdiLockReset" size="14" />
                <span>Gunakan Default ('password')</span>
              </button>
            </div>

            <!-- Password Field -->
            <FormField
              :error="form.errors.password"
              :help="isEditing ? 'Kosongkan jika tidak ingin mengganti password.' : 'Default: password (minimal 3 karakter)'"
            >
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                  <BaseIcon :path="mdiKey" size="18" />
                </div>
                <input
                  v-model="form.password"
                  :type="showPassword ? 'text' : 'password'"
                  :placeholder="isEditing ? 'Masukkan password baru...' : 'Default: password'"
                  class="w-full pl-10 pr-10 py-2.5 text-sm rounded-2xl border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 font-mono focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                />
                <button
                  type="button"
                  class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                  @click="showPassword = !showPassword"
                >
                  <BaseIcon :path="showPassword ? mdiEyeOff : mdiEye" size="18" />
                </button>
              </div>
            </FormField>

            <!-- Konfirmasi Password -->
            <FormField
              v-if="form.password || !isEditing"
              label="Konfirmasi Password"
              :error="form.errors.password_confirmation"
            >
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                  <BaseIcon :path="mdiKey" size="18" />
                </div>
                <input
                  v-model="form.password_confirmation"
                  :type="showConfirmPassword ? 'text' : 'password'"
                  placeholder="Ketik ulang password..."
                  class="w-full pl-10 pr-10 py-2.5 text-sm rounded-2xl border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 font-mono focus:ring-4 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all"
                />
                <button
                  type="button"
                  class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                  @click="showConfirmPassword = !showConfirmPassword"
                >
                  <BaseIcon :path="showConfirmPassword ? mdiEyeOff : mdiEye" size="18" />
                </button>
              </div>
            </FormField>
          </div>
        </div>

        <!-- Custom Footer Slot with Responsive Buttons & Loading State -->
        <template #footer>
          <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800">
            <BaseButton
              label="Batal"
              color="whiteDark"
              rounded-full
              :disabled="form.processing"
              @click="isModalActive = false"
            />
            <BaseButton
              :label="isEditing ? 'Simpan Perubahan' : 'Buat Akun Siswa'"
              :color="isEditing ? 'info' : 'contrast'"
              rounded-full
              :icon="mdiCheckCircle"
              :loading="form.processing"
              :disabled="form.processing"
              class="shadow-sm"
              @click="submit"
            />
          </div>
        </template>
      </CardBoxModal>

      <!-- Modal Import User Siswa -->
      <CardBoxModal
        v-model="isImportModalActive"
        title="Import User Siswa"
        has-cancel
      >
        <div class="space-y-4">
          <div class="flex items-center justify-between p-3.5 bg-indigo-50/50 dark:bg-indigo-950/30 rounded-2xl border border-indigo-100 dark:border-indigo-900/40">
            <div>
              <p class="font-bold text-sm text-slate-800 dark:text-slate-100">Unduh Format Template</p>
              <p class="text-xs text-slate-500 dark:text-slate-400">File Excel template sudah pre-filled dengan siswa yang ada.</p>
            </div>
            <BaseButton
              :icon="mdiDownload"
              label="Template Excel"
              color="info"
              small
              rounded-full
              @click="downloadTemplate"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">
              Pilih File Spreadsheet (.xlsx, .xls, .csv)
            </label>
            <input
              type="file"
              accept=".xlsx, .xls, .csv"
              class="block w-full text-sm text-slate-700 dark:text-slate-200 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-950 dark:file:text-indigo-300 border border-slate-300 dark:border-slate-700 rounded-xl cursor-pointer bg-slate-50/50 dark:bg-slate-800/50 focus:outline-none"
              @input="importForm.file = $event.target.files[0]"
            />
            <div v-if="importForm.errors.file" class="text-rose-500 text-xs mt-1.5 font-medium">
              {{ importForm.errors.file }}
            </div>
          </div>
        </div>

        <template #footer>
          <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800">
            <BaseButton label="Batal" color="whiteDark" rounded-full @click="isImportModalActive = false" />
            <BaseButton
              label="Mulai Import"
              color="success"
              rounded-full
              :loading="importForm.processing"
              @click="submitImport"
            />
          </div>
        </template>
      </CardBoxModal>

      <!-- Modal Reset Password Siswa -->
      <CardBoxModal
        v-model="isResetModalActive"
        title="Reset Password Siswa"
        has-cancel
      >
        <div v-if="resetUser" class="space-y-4">
          <!-- Info Siswa Banner -->
          <div class="p-3.5 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/40 flex items-center gap-3">
            <UserAvatar :username="resetUser.name" api="initials" class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50" />
            <div class="overflow-hidden">
              <h4 class="font-bold text-sm text-slate-800 dark:text-slate-100 truncate">{{ resetUser.name }}</h4>
              <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">NISN: {{ resetUser.username }} &bull; {{ resetUser.siswa?.kelas?.nama_kelas || 'Kelas belum diatur' }}</p>
            </div>
          </div>

          <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            Masukkan kata sandi baru untuk akun siswa ini. Setelah disimpan, siswa dapat langsung login menggunakan kata sandi yang baru.
          </p>

          <!-- Password Baru -->
          <FormField label="Password Baru" :error="resetForm.errors.password" help="Minimal 3 karakter">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <BaseIcon :path="mdiKey" size="18" />
              </div>
              <input
                v-model="resetForm.password"
                :type="showResetPassword ? 'text' : 'password'"
                placeholder="Masukkan password baru siswa..."
                class="w-full pl-10 pr-10 py-2.5 text-sm rounded-2xl border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 font-mono focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                @click="showResetPassword = !showResetPassword"
              >
                <BaseIcon :path="showResetPassword ? mdiEyeOff : mdiEye" size="18" />
              </button>
            </div>
          </FormField>

          <!-- Konfirmasi Password Baru -->
          <FormField label="Konfirmasi Password Baru" :error="resetForm.errors.password_confirmation">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <BaseIcon :path="mdiKey" size="18" />
              </div>
              <input
                v-model="resetForm.password_confirmation"
                :type="showConfirmResetPassword ? 'text' : 'password'"
                placeholder="Ulangi password baru siswa..."
                class="w-full pl-10 pr-10 py-2.5 text-sm rounded-2xl border border-slate-200/90 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 placeholder-slate-400 font-mono focus:ring-4 focus:ring-amber-500/20 focus:border-amber-500 transition-all"
              />
              <button
                type="button"
                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                @click="showConfirmResetPassword = !showConfirmResetPassword"
              >
                <BaseIcon :path="showConfirmResetPassword ? mdiEyeOff : mdiEye" size="18" />
              </button>
            </div>
          </FormField>
        </div>

        <template #footer>
          <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-slate-100 dark:border-slate-800">
            <BaseButton label="Batal" color="whiteDark" rounded-full @click="isResetModalActive = false" />
            <BaseButton
              label="Simpan Password Baru"
              color="warning"
              rounded-full
              :loading="resetForm.processing"
              @click="submitResetPassword"
            />
          </div>
        </template>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template>
