<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { mdiShieldAccount, mdiTrashCan, mdiAlertCircle, mdiPlus, mdiPencil, mdiMagnify, mdiCheckboxMarkedCircleOutline, mdiCheckboxBlankCircleOutline, mdiChevronDown, mdiChevronRight } from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import CardBox from '@admin/components/CardBox.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import { computed, ref } from 'vue'

const props = defineProps({
  roles: {
    type: Array,
    default: () => [],
  },
  permissions: {
    type: Array,
    default: () => [],
  },
})

const groupLabels = {
  data_admin: 'Data Admin',
  dashboard: 'Dashboard',
  jurusan: 'Jurusan',
  kelas: 'Kelas',
  siswa: 'Siswa',
  jabatan_pembimbing: 'Jabatan Pembimbing',
  pembimbing: 'Pembimbing',
  bengkel: 'Bengkel',
  role: 'Role',
  permission: 'Permission',
  input_data_pkl: 'Input Data PKL',
  input_pembimbing: 'Input Pembimbing',
  mutasi_pkl: 'Mutasi PKL',
  monitoring: 'Monitoring',
  rekap_absensi: 'Rekap Absensi',
  rekap_absensi_pembekalan: 'Rekap Absensi Pembekalan',
  pembekalan_aktivasi: 'Pembekalan Aktivasi',
  cetak_p1: 'Cetak P1',
  surat_pengantar: 'Surat Pengantar',
  rekap_data_pkl: 'Rekap Data PKL',
  informasi: 'Informasi',
  rekap_problem: 'Rekap Problem',
  rekap_karir: 'Rekap Karir',
  user_pembimbing: 'User Pembimbing',
  user_siswa: 'User Siswa',
  backup: 'Backup',
}

const actionLabels = {
  view: 'Lihat',
  create: 'Tambah',
  edit: 'Ubah',
  delete: 'Hapus',
  import: 'Import',
  export: 'Export',
  print: 'Cetak',
  process: 'Proses',
  approve: 'Setujui',
  reject: 'Tolak',
  bulk_delete: 'Hapus Massal',
  export_excel: 'Export Excel',
  export_pdf: 'Export PDF',
}

const groupColors = {
  data_admin: 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300',
  dashboard: 'bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-300',
  jurusan: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
  kelas: 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
  siswa: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
  jabatan_pembimbing: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
  pembimbing: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
  bengkel: 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
  role: 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
  permission: 'bg-fuchsia-100 text-fuchsia-700 dark:bg-fuchsia-900/40 dark:text-fuchsia-300',
  input_data_pkl: 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
  input_pembimbing: 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
  mutasi_pkl: 'bg-rose-100 text-rose-700 dark:bg-rose-900/40 dark:text-rose-300',
  monitoring: 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
  rekap_absensi: 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
  rekap_absensi_pembekalan: 'bg-violet-100 text-violet-700 dark:bg-violet-900/40 dark:text-violet-300',
  pembekalan_aktivasi: 'bg-pink-100 text-pink-700 dark:bg-pink-900/40 dark:text-pink-300',
  cetak_p1: 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
  surat_pengantar: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
  rekap_data_pkl: 'bg-lime-100 text-lime-700 dark:bg-lime-900/40 dark:text-lime-300',
  informasi: 'bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300',
  rekap_problem: 'bg-stone-100 text-stone-700 dark:bg-stone-900/40 dark:text-stone-300',
  rekap_karir: 'bg-teal-100 text-teal-700 dark:bg-teal-900/40 dark:text-teal-300',
  user_pembimbing: 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
  user_siswa: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300',
  backup: 'bg-gray-100 text-gray-700 dark:bg-gray-900/40 dark:text-gray-300',
}

const permissionGroupKeys = computed(() => {
  const groups = {}
  props.permissions.forEach((perm) => {
    const dotIdx = perm.name.indexOf('.')
    const group = dotIdx === -1 ? 'general' : perm.name.substring(0, dotIdx)
    if (!groups[group]) groups[group] = []
    groups[group].push(perm.name)
  })
  const order = Object.keys(groupLabels)
  return Object.keys(groups).sort((a, b) => {
    const ai = order.indexOf(a)
    const bi = order.indexOf(b)
    if (ai === -1 && bi === -1) return a.localeCompare(b)
    if (ai === -1) return 1
    if (bi === -1) return -1
    return ai - bi
  })
})

const expandedGroups = ref({})

const isGroupExpanded = (key) => {
  if (expandedGroups.value[key] === undefined) return true
  return expandedGroups.value[key]
}

const toggleGroup = (key) => {
  expandedGroups.value[key] = !isGroupExpanded(key)
}

const formatPermissionLabel = (name) => {
  const dotIdx = name.indexOf('.')
  if (dotIdx === -1) {
    return name.replace(/_/g, ' ')
  }
  const action = name.substring(dotIdx + 1)
  return actionLabels[action] || action.replace(/_/g, ' ')
}

const formatGroupLabel = (key) => {
  return groupLabels[key] || key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const form = useForm({
  name: '',
  permissions: [],
})

const deleteForm = useForm({})

const isModalActive = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const permissionSearch = ref('')

const filteredPermissionGroupKeys = computed(() => {
  const term = permissionSearch.value.trim().toLowerCase()
  if (!term) return permissionGroupKeys.value

  return permissionGroupKeys.value.filter((key) => {
    const groupPerms = props.permissions.filter(p => {
      const dotIdx = p.name.indexOf('.')
      const g = dotIdx === -1 ? 'general' : p.name.substring(0, dotIdx)
      return g === key
    })
    return groupPerms.some(p => p.name.toLowerCase().includes(term))
  })
})

const openModal = () => {
  isEditing.value = false
  editingId.value = null
  form.reset()
  form.clearErrors()
  permissionSearch.value = ''
  expandedGroups.value = {}
  isModalActive.value = true
}

const editRole = (role) => {
  isEditing.value = true
  editingId.value = role.id
  form.name = role.name
  form.permissions = role.permissions.map(p => p.name)
  form.clearErrors()
  permissionSearch.value = ''
  expandedGroups.value = {}
  isModalActive.value = true
}

const submit = () => {
  if (isEditing.value && editingId.value) {
    form.put(route('admin.roles.update', editingId.value), {
      onSuccess: () => {
        isModalActive.value = false
        form.reset()
      },
    })
  } else {
    form.post(route('admin.roles.store'), {
      onSuccess: () => {
        isModalActive.value = false
        form.reset()
      },
    })
  }
}

const deleteRole = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus role ini?')) {
    deleteForm.delete(route('admin.roles.destroy', id))
  }
}

const isPermissionChecked = (permName) => {
  return form.permissions.includes(permName)
}

const togglePermission = (permName) => {
  const idx = form.permissions.indexOf(permName)
  if (idx === -1) {
    form.permissions.push(permName)
  } else {
    form.permissions.splice(idx, 1)
  }
}

const groupPermissions = (groupKey) => {
  return props.permissions
    .filter(p => {
      const dotIdx = p.name.indexOf('.')
      const g = dotIdx === -1 ? 'general' : p.name.substring(0, dotIdx)
      return g === groupKey
    })
    .sort((a, b) => {
      const aDot = a.name.indexOf('.')
      const bDot = b.name.indexOf('.')
      const aAction = aDot === -1 ? a.name : a.name.substring(aDot + 1)
      const bAction = bDot === -1 ? b.name : b.name.substring(bDot + 1)
      const order = Object.keys(actionLabels)
      const ai = order.indexOf(aAction)
      const bi = order.indexOf(bAction)
      if (ai === -1 && bi === -1) return aAction.localeCompare(bAction)
      if (ai === -1) return 1
      if (bi === -1) return -1
      return ai - bi
    })
}

const filteredGroupPermissions = (groupKey) => {
  const term = permissionSearch.value.trim().toLowerCase()
  const perms = groupPermissions(groupKey)
  if (!term) return perms
  return perms.filter(p => p.name.toLowerCase().includes(term))
}

const allSelectedInGroup = (groupKey) => {
  const perms = groupPermissions(groupKey)
  return perms.length > 0 && perms.every(p => isPermissionChecked(p.name))
}

const someSelectedInGroup = (groupKey) => {
  const perms = groupPermissions(groupKey)
  return perms.some(p => isPermissionChecked(p.name))
}

const toggleGroupPermissions = (groupKey) => {
  const perms = groupPermissions(groupKey)
  if (allSelectedInGroup(groupKey)) {
    perms.forEach(p => {
      const idx = form.permissions.indexOf(p.name)
      if (idx !== -1) form.permissions.splice(idx, 1)
    })
  } else {
    perms.forEach(p => {
      if (!isPermissionChecked(p.name)) {
        form.permissions.push(p.name)
      }
    })
  }
}

const selectedCount = computed(() => form.permissions.length)
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Manajemen Role" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiShieldAccount" title="Manajemen Role" main>
        <BaseButton
          :icon="mdiPlus"
          label="Tambah Role"
          color="contrast"
          rounded-full
          small
          @click="openModal"
        />
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

      <div class="grid grid-cols-1 gap-6 mb-6">
        <CardBox v-for="role in roles" :key="role.id" class="mb-6">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-bold">{{ role.name }}</h3>
            <BaseButtons>
              <BaseButton
                color="info"
                :icon="mdiPencil"
                small
                @click="editRole(role)"
              />
              <BaseButton
                v-if="role.name !== 'super-admin' && role.name !== 'admin'"
                color="danger"
                :icon="mdiTrashCan"
                small
                @click="deleteRole(role.id)"
              />
            </BaseButtons>
          </div>

          <div class="border-t border-gray-100 dark:border-slate-800 pt-4">
            <h4 class="text-sm font-semibold text-gray-500 mb-2">Permissions:</h4>
            <div class="flex flex-wrap gap-2">
              <span
                v-for="perm in role.permissions"
                :key="perm.id"
                class="px-2 py-1 bg-gray-100 dark:bg-slate-700 rounded text-xs"
              >
                {{ perm.name }}
              </span>
              <span v-if="role.permissions.length === 0" class="text-gray-400 text-sm italic">
                No permissions assigned
              </span>
            </div>
          </div>
        </CardBox>
      </div>

      <CardBoxModal
        v-model="isModalActive"
        :title="isEditing ? 'Edit Role' : 'Tambah Role'"
        button="info"
        :button-label="isEditing ? 'Simpan' : 'Tambah'"
        has-cancel
        modal-class="w-11/12 md:w-5/6 lg:w-3/4 xl:w-3/5 2xl:w-1/2"
        @confirm="submit"
      >
        <FormField label="Nama Role" :error="form.errors.name">
          <FormControl v-model="form.name" :icon="mdiShieldAccount" placeholder="Nama Role" />
        </FormField>

        <FormField label="Permissions">
          <div class="flex items-center gap-3 mb-3">
            <div class="flex-1 relative">
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <BaseIcon :path="mdiMagnify" class="text-gray-400" :size="16" />
              </div>
              <input
                v-model="permissionSearch"
                type="text"
                placeholder="Cari permission..."
                class="pl-9 w-full rounded-xl border-gray-200 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-700 dark:border-slate-600 dark:text-white text-sm py-2 transition-all duration-300"
              />
            </div>
            <span class="text-xs font-medium text-gray-500 whitespace-nowrap">{{ selectedCount }} dipilih</span>
          </div>

          <div class="max-h-96 overflow-y-auto space-y-2 pr-1">
            <div
              v-for="groupKey in filteredPermissionGroupKeys"
              :key="groupKey"
              class="border border-gray-200 dark:border-slate-600 rounded-xl overflow-hidden transition-all duration-200"
            >
              <div
                class="flex items-center justify-between px-4 py-2.5 cursor-pointer select-none hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors"
                :class="groupColors[groupKey] || 'bg-gray-50 dark:bg-slate-800'"
                @click="toggleGroup(groupKey)"
              >
                <div class="flex items-center gap-2">
                  <BaseIcon
                    :path="isGroupExpanded(groupKey) ? mdiChevronDown : mdiChevronRight"
                    :size="18"
                  />
                  <span class="text-sm font-semibold">{{ formatGroupLabel(groupKey) }}</span>
                  <span class="text-xs opacity-70">({{ groupPermissions(groupKey).length }})</span>
                </div>
                <BaseButton
                  :icon="someSelectedInGroup(groupKey) ? mdiCheckboxMarkedCircleOutline : mdiCheckboxBlankCircleOutline"
                  :color="allSelectedInGroup(groupKey) ? 'success' : 'whiteDark'"
                  :label="allSelectedInGroup(groupKey) ? 'Semua' : 'Pilih Semua'"
                  small
                  outline
                  @click.stop="toggleGroupPermissions(groupKey)"
                />
              </div>
              <div v-show="isGroupExpanded(groupKey)" class="p-3 bg-white dark:bg-slate-800/50">
                <div
                  v-if="filteredGroupPermissions(groupKey).length === 0"
                  class="text-sm text-gray-400 italic text-center py-3"
                >
                  Tidak ada permission yang cocok
                </div>
                <div
                  v-else
                  class="grid grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-1"
                >
                  <label
                    v-for="perm in filteredGroupPermissions(groupKey)"
                    :key="perm.id"
                    class="flex items-center gap-2 py-1.5 px-2 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-slate-700/50 transition-colors group"
                  >
                    <input
                      type="checkbox"
                      :checked="isPermissionChecked(perm.name)"
                      @change="togglePermission(perm.name)"
                      class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-700 dark:border-slate-500"
                    />
                    <span class="text-sm text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white transition-colors">
                      {{ formatPermissionLabel(perm.name) }}
                    </span>
                  </label>
                </div>
              </div>
            </div>
          </div>
        </FormField>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template>
