<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import {
  mdiAccountTie,
  mdiTrashCan,
  mdiAlertCircle,
  mdiPlus,
  mdiPencil,
  mdiMagnify,
  mdiRefresh,
  mdiFileExcel,
  mdiDownload,
  mdiKey,
  mdiEye,
  mdiEyeOff,
  mdiLockReset,
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import CardBox from '@admin/components/CardBox.vue'
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
  pembimbingOptions: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  pembimbing_id: null,
})

const deleteForm = useForm({})
const bulkDeleteForm = useForm({})
const bulkResetForm = useForm({
  ids: [],
  password: 'password',
})
const resetForm = useForm({
  password: '',
  password_confirmation: '',
})
const filterForm = useForm({
  search: props.filters?.search || '',
  per_page: props.filters?.per_page || 10,
})

const isModalActive = ref(false)
const isImportModalActive = ref(false)
const isResetModalActive = ref(false)
const resetUser = ref(null)
const showResetPassword = ref(false)
const showConfirmResetPassword = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const selectedPembimbing = ref(null)

const selectedIds = ref([])

const selectAll = (event) => {
  if (event.target.checked) {
    selectedIds.value = userData.value.map(u => u.id)
  } else {
    selectedIds.value = []
  }
}

const toggleSelect = (id) => {
  const idx = selectedIds.value.indexOf(id)
  if (idx === -1) {
    selectedIds.value.push(id)
  } else {
    selectedIds.value.splice(idx, 1)
  }
}

const allSelected = computed(() =>
  userData.value.length > 0 && selectedIds.value.length === userData.value.length,
)

const importForm = useForm({
  file: null,
})

const openImportModal = () => {
  importForm.reset()
  isImportModalActive.value = true
}

const submitImport = () => {
  importForm.post(route('admin.users.pembimbing.import'), {
    onSuccess: () => {
      isImportModalActive.value = false
      importForm.reset()
    },
  })
}

const downloadTemplate = () => {
  window.location.href = route('admin.users.pembimbing.template')
}

const userData = computed(() =>
  props.users && Array.isArray(props.users.data) ? props.users.data : [],
)

const userLinks = computed(() =>
  props.users && Array.isArray(props.users.links) ? props.users.links : [],
)

const formatPaginationLabel = (label) => {
  if (label.includes('Previous')) {
    return 'Previous'
  }

  if (label.includes('Next')) {
    return 'Next'
  }

  return label
}

const openModal = () => {
  isEditing.value = false
  editingId.value = null
  form.reset()
  selectedPembimbing.value = null
  form.clearErrors()
  isModalActive.value = true
}

const editUser = (user) => {
  isEditing.value = true
  editingId.value = user.id
  form.name = user.name
  form.email = user.email
  form.password = ''
  form.password_confirmation = ''
  form.pembimbing_id = null
  selectedPembimbing.value = null
  form.clearErrors()
  isModalActive.value = true
}

watch(selectedPembimbing, (val) => {
  if (val) {
    form.pembimbing_id = val.id
    // Auto-fill name and email if creating new user or fields are empty
    if (!isEditing.value) {
      const name = val.nama_guru || val.label || ''
      if (!form.name) {
        form.name = name
      }
      if (!form.email && name) {
        const slug = name.toLowerCase().replace(/[^a-z0-9]/g, '').replace(/\s+/g, '.')
        form.email = `${slug}@smktelkom-sidoarjo.sch.id`
      }
      // Set default password for speed
      if (!form.password) {
        form.password = 'password'
        form.password_confirmation = 'password'
      }
    }
  } else {
    form.pembimbing_id = null
    if (!isEditing.value) {
      form.name = ''
      form.email = ''
      form.password = ''
      form.password_confirmation = ''
    }
  }
})

const submit = () => {
  if (isEditing.value && editingId.value) {
    form.put(route('admin.users.pembimbing.update', editingId.value), {
      onSuccess: () => {
        isModalActive.value = false
        form.reset()
      },
    })
  } else {
    form.post(route('admin.users.pembimbing.store'), {
      onSuccess: () => {
        isModalActive.value = false
        form.reset()
      },
    })
  }
}

const deleteUser = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
    deleteForm.delete(route('admin.users.pembimbing.destroy', id))
  }
}

const bulkDelete = () => {
  if (selectedIds.value.length === 0) return
  if (confirm(`Hapus ${selectedIds.value.length} user pembimbing yang dipilih?`)) {
    bulkDeleteForm.post(route('admin.users.pembimbing.bulk_destroy'), {
      ids: selectedIds.value,
      onSuccess: () => {
        selectedIds.value = []
      },
    })
  }
}

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

  resetForm.put(route('admin.users.pembimbing.reset_password', resetUser.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      isResetModalActive.value = false
      resetUser.value = null
      resetForm.reset()
    },
  })
}

const bulkResetPassword = () => {
  if (selectedIds.value.length === 0) return
  if (confirm(`Apakah Anda yakin ingin me-reset password untuk ${selectedIds.value.length} user pembimbing terpilih menjadi 'password'?`)) {
    bulkResetForm.post(route('admin.users.pembimbing.bulk_reset_password'), {
      ids: selectedIds.value,
      password: 'password',
      preserveScroll: true,
      onSuccess: () => {
        selectedIds.value = []
      },
    })
  }
}

const search = debounce(() => {
  router.get(route('admin.users.pembimbing.index'), { search: filterForm.search }, { preserveState: true, replace: true })
}, 300)

watch(() => filterForm.search, search)

const changePerPage = debounce(() => {
  router.get(route('admin.users.pembimbing.index'), { search: filterForm.search, per_page: filterForm.per_page }, { preserveState: true, replace: true })
}, 300)

watch(() => filterForm.per_page, changePerPage)

const resetFilter = () => {
  filterForm.search = ''
  search()
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Manajemen User Pembimbing" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiAccountTie" title="Manajemen User Pembimbing" main>
        <div class="flex gap-2">
          <BaseButton
            :icon="mdiFileExcel"
            label="Import User"
            color="success"
            rounded-full
            small
            @click="openImportModal"
          />
          <BaseButton
            :icon="mdiPlus"
            label="Tambah User Pembimbing"
            color="contrast"
            rounded-full
            small
            @click="openModal"
          />
        </div>
      </SectionTitleLineWithButton>

      <CardBoxModal
        v-model="isImportModalActive"
        title="Import User Pembimbing"
        has-cancel
      >
        <div class="mb-4">
          <div class="flex justify-between items-center mb-4">
             <p class="text-sm text-gray-500">
              Upload file Excel (.xlsx, .xls) atau CSV.
            </p>
            <BaseButton
              :icon="mdiDownload"
              label="Template"
              color="info"
              small
              @click="downloadTemplate"
            />
          </div>

          <input
            type="file"
            @input="importForm.file = $event.target.files[0]"
            accept=".xlsx, .xls, .csv"
            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
          />
          <div v-if="importForm.errors.file" class="text-red-500 text-xs mt-1">
            {{ importForm.errors.file }}
          </div>
        </div>

        <template #footer>
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <BaseButton label="Cancel" color="whiteDark" @click="isImportModalActive = false" />
            <BaseButton label="Import" color="success" :loading="importForm.processing" @click="submitImport" />
          </div>
        </template>
      </CardBoxModal>

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

      <CardBox class="shadow-xl border-0 rounded-2xl overflow-hidden mb-6">
        <div class="p-5">
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
            <div class="md:col-span-7">
              <FormField label="Cari">
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <BaseIcon :path="mdiMagnify" :size="16" class="text-slate-400" />
                  </div>
                  <input
                    v-model="filterForm.search"
                    type="text"
                    placeholder="Nama, email, atau jabatan..."
                    class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 text-slate-700 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500/60 transition-all"
                  />
                </div>
              </FormField>
            </div>
            <div class="md:col-span-2">
              <FormField label="Tampilkan">
                <select
                  v-model="filterForm.per_page"
                  class="w-full px-4 py-2 text-sm rounded-xl border border-slate-200/70 dark:border-slate-700/70 bg-white/60 dark:bg-slate-900/40 text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500/60 transition-all appearance-none"
                >
                  <option value="10">10</option>
                  <option value="20">20</option>
                  <option value="50">50</option>
                  <option value="100">100</option>
                </select>
              </FormField>
            </div>
            <div class="md:col-span-3 flex items-end gap-2 justify-end">
              <BaseButton
                :icon="mdiRefresh"
                color="whiteDark"
                small
                @click="resetFilter"
              />
              <BaseButton
                v-if="selectedIds.length > 0"
                :icon="mdiLockReset"
                :label="'Reset (' + selectedIds.length + ')'"
                color="warning"
                small
                :loading="bulkResetForm.processing"
                @click="bulkResetPassword"
              />
              <BaseButton
                v-if="selectedIds.length > 0"
                :icon="mdiTrashCan"
                :label="'Hapus (' + selectedIds.length + ')'"
                color="danger"
                small
                :loading="bulkDeleteForm.processing"
                @click="bulkDelete"
              />
            </div>
          </div>
        </div>
      </CardBox>

      <CardBox class="mb-6" has-table>
        <div class="overflow-x-auto">
          <table>
            <thead>
              <tr>
                <th class="w-10">
                  <input type="checkbox" :checked="allSelected" @input="selectAll" class="rounded" />
                </th>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Jabatan</th>
                <th>Status Link</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(user, index) in userData" :key="user.id">
                <td data-label="Pilih" class="w-10">
                  <input type="checkbox" :checked="selectedIds.includes(user.id)" @change="toggleSelect(user.id)" class="rounded" />
                </td>
                <td data-label="No">
                  {{ (users.current_page - 1) * users.per_page + index + 1 }}
                </td>
                <td data-label="Nama">{{ user.name }}</td>
                <td data-label="Email">{{ user.email }}</td>
                <td data-label="Jabatan">
                  {{ user.pembimbing?.jabatan_pembimbing?.nama_jabatan || '-' }}
                </td>
                <td data-label="Status Link">
                   <span v-if="user.pembimbing" class="text-green-600 font-bold">Terhubung</span>
                   <span v-else class="text-red-600 font-bold">Belum Terhubung</span>
                </td>
                <td class="before:hidden lg:w-1 whitespace-nowrap text-right">
                  <BaseButtons type="justify-start lg:justify-end" no-wrap>
                    <BaseButton
                      color="warning"
                      :icon="mdiKey"
                      small
                      title="Reset Password"
                      @click="openResetModal(user)"
                    />
                    <BaseButton
                      color="info"
                      :icon="mdiPencil"
                      small
                      @click="editUser(user)"
                    />
                    <BaseButton
                      color="danger"
                      :icon="mdiTrashCan"
                      small
                      @click="deleteUser(user.id)"
                    />
                  </BaseButtons>
                </td>
              </tr>
              <tr v-if="userData.length === 0">
                <td colspan="7" class="text-center py-4 text-gray-500">
                  Data user pembimbing tidak ditemukan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="userLinks.length > 3" class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
          <BaseLevel>
            <BaseButtons>
              <BaseButton
                v-for="(link, key) in userLinks"
                :key="key"
                :active="link.active"
                :label="formatPaginationLabel(link.label)"
                :color="link.active ? 'lightDark' : 'whiteDark'"
                :disabled="!link.url"
                :to="link.url || null"
                small
              />
            </BaseButtons>
          </BaseLevel>
        </div>
      </CardBox>

      <CardBoxModal
        v-model="isModalActive"
        :title="isEditing ? 'Edit User Pembimbing' : 'Tambah User Pembimbing'"
        button="info"
        :button-label="isEditing ? 'Simpan' : 'Tambah'"
        :close-on-confirm="false"
        has-cancel
        @confirm="submit"
        modal-class="w-11/12 md:w-3/5 lg:w-1/2"
      >
        <div class="space-y-4">
          <!-- Link Pembimbing Section -->
          <div class="bg-gray-50 dark:bg-slate-800/50 p-4 rounded-lg border border-gray-200 dark:border-slate-700">
            <div class="mb-3">
              <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Pilih Data Pembimbing</h3>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                Cari dan pilih pembimbing untuk mengisi nama otomatis dan menautkan akun.
              </p>
            </div>

            <FormField label="Nama Pembimbing" class="mb-0" :error="form.errors.pembimbing_id">
              <FormControlSearchable
                v-model="selectedPembimbing"
                :options="pembimbingOptions"
                :icon="mdiMagnify"
                placeholder="Klik disini untuk mencari nama pembimbing..."
              />
            </FormField>

            <div v-if="selectedPembimbing && selectedPembimbing.is_linked" class="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-700 dark:text-yellow-400 text-xs rounded-lg border border-yellow-200 dark:border-yellow-800 flex items-start gap-2">
              <BaseIcon :path="mdiAlertCircle" size="16" class="flex-shrink-0 mt-0.5" />
              <span>
                Pembimbing ini sudah terhubung dengan user <strong>{{ selectedPembimbing.linked_user_name }}</strong>.
                Menautkan ke user baru akan memutus hubungan dengan user lama.
              </span>
            </div>
          </div>

          <!-- Manual Entry Section -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
              <FormField label="Nama Lengkap" :error="form.errors.name">
                <FormControl
                  v-model="form.name"
                  :icon="mdiAccountTie"
                  placeholder="Nama Lengkap User"
                  :disabled="!!selectedPembimbing"
                />
              </FormField>
            </div>

            <div class="md:col-span-2">
              <FormField label="Email" :error="form.errors.email">
                <FormControl
                  v-model="form.email"
                  type="email"
                  :icon="mdiAccountTie"
                  placeholder="user@example.com"
                />
              </FormField>
            </div>

            <FormField
              label="Password"
              :error="form.errors.password"
              :help="isEditing ? 'Kosongkan jika tidak ingin mengubah' : 'Default: password (terisi otomatis)'"
            >
              <FormControl
                v-model="form.password"
                type="password"
                placeholder="Password"
              />
            </FormField>

            <FormField
              label="Konfirmasi Password"
              :error="form.errors.password_confirmation"
            >
              <FormControl
                v-model="form.password_confirmation"
                type="password"
                placeholder="Ulangi Password"
              />
            </FormField>
          </div>
        </div>
      </CardBoxModal>

      <!-- Modal Reset Password Pembimbing -->
      <CardBoxModal
        v-model="isResetModalActive"
        title="Reset Password Pembimbing"
        has-cancel
      >
        <div v-if="resetUser" class="space-y-4">
          <!-- Info Pembimbing Banner -->
          <div class="p-3.5 rounded-2xl bg-amber-50/70 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/40 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center text-amber-700 dark:text-amber-300 font-bold text-base flex-shrink-0">
              <BaseIcon :path="mdiAccountTie" size="20" />
            </div>
            <div class="overflow-hidden">
              <h4 class="font-bold text-sm text-slate-800 dark:text-slate-100 truncate">{{ resetUser.name }}</h4>
              <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ resetUser.email }} &bull; {{ resetUser.pembimbing?.jabatan_pembimbing?.nama_jabatan || 'Guru Pembimbing' }}</p>
            </div>
          </div>

          <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
            Masukkan kata sandi baru untuk akun guru pembimbing ini. Setelah disimpan, guru pembimbing dapat langsung login menggunakan kata sandi yang baru.
          </p>

          <!-- Password Baru -->
          <FormField label="Password Baru" :error="resetForm.errors.password" help="Minimal 6 karakter">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <BaseIcon :path="mdiKey" size="18" />
              </div>
              <input
                v-model="resetForm.password"
                :type="showResetPassword ? 'text' : 'password'"
                placeholder="Masukkan password baru pembimbing..."
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
                placeholder="Ulangi password baru pembimbing..."
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
