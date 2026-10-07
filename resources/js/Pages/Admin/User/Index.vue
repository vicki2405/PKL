<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { mdiAccountGroup, mdiTrashCan, mdiAlertCircle, mdiPlus, mdiPencil, mdiMagnify, mdiRefresh, mdiAccountKey, mdiShieldKey, mdiAccountTie, mdiEye, mdiEyeOff } from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import CardBox from '@admin/components/CardBox.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import FormCheckRadioGroup from '@admin/components/FormCheckRadioGroup.vue'
import { computed, ref, watch } from 'vue'

const props = defineProps({
  users: {
    type: Object,
    default: () => ({}),
  },
  roles: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  roles: [],
})

const deleteForm = useForm({})
const filterForm = useForm({
  search: props.filters?.search || '',
  per_page: props.filters?.per_page || 10,
})

const isModalActive = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const showPassword = ref(false)
const showConfirmPassword = ref(false)

const perPageOptions = [10, 20, 50, 100]

const userData = computed(() =>
  props.users && Array.isArray(props.users.data) ? props.users.data : [],
)

const userLinks = computed(() =>
  props.users && Array.isArray(props.users.links) ? props.users.links : [],
)

const roleOptions = computed(() => {
  return props.roles.reduce((acc, role) => {
    acc[role.name] = role.name
    return acc
  }, {})
})

const openModal = () => {
  isEditing.value = false
  editingId.value = null
  form.reset()
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
  form.roles = user.roles.map(r => r.name)
  form.clearErrors()
  isModalActive.value = true
}

const submit = () => {
  if (isEditing.value && editingId.value) {
    form.put(route('admin.users.update', editingId.value), {
      onSuccess: () => {
        isModalActive.value = false
        form.reset()
      },
      onError: () => {
        isModalActive.value = true
      },
    })
  } else {
    form.post(route('admin.users.store'), {
      onSuccess: () => {
        isModalActive.value = false
        form.reset()
      },
      onError: () => {
        isModalActive.value = true
      },
    })
  }
}

const deleteUser = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
    deleteForm.delete(route('admin.users.destroy', id))
  }
}

const applyFilters = () => {
  filterForm.get(route('admin.users.index'), {
    preserveState: true,
    preserveScroll: true,
  })
}

const resetFilters = () => {
  filterForm.search = ''
  applyFilters()
}

let timeout
watch(
  () => filterForm.search,
  () => {
    clearTimeout(timeout)
    timeout = setTimeout(() => applyFilters(), 500)
  }
)

watch(
  () => filterForm.per_page,
  () => applyFilters()
)
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Data Admin" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiAccountGroup" title="Data Admin" main>
        <BaseButton
          :icon="mdiPlus"
          label="Tambah Admin"
          color="contrast"
          rounded-full
          small
          @click="openModal"
        />
        <BaseButtons class="ml-2">
          <BaseButton
            :to="route('admin.roles.index')"
            :icon="mdiAccountTie"
            label="Role"
            color="whiteDark"
            rounded-full
            small
          />
          <BaseButton
            :to="route('admin.permissions.index')"
            :icon="mdiShieldKey"
            label="Permission"
            color="whiteDark"
            rounded-full
            small
          />
        </BaseButtons>
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

      <CardBox class="mb-6 shadow-md rounded-lg" has-table>
        <div class="p-4 border-b border-gray-100 dark:border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4 bg-gray-50/50 dark:bg-slate-800/50">
          <div class="w-full md:w-1/3 relative">
            <input
              v-model="filterForm.search"
              type="text"
              placeholder="Cari Nama / Email..."
              class="w-full pl-10 pr-3 py-2 bg-white dark:bg-slate-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder-gray-400 shadow-sm text-sm"
            />
            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
              <svg class="w-5 h-5 text-gray-400" viewBox="0 0 24 24">
                <path fill="currentColor" :d="mdiMagnify" />
              </svg>
            </div>
          </div>
          <div class="w-full md:w-auto flex items-center gap-3">
            <div class="relative w-full md:w-32">
              <select
                v-model.number="filterForm.per_page"
                class="w-full pl-3 pr-10 py-2 bg-white dark:bg-slate-900 border border-gray-300 dark:border-gray-700 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all shadow-sm text-sm appearance-none cursor-pointer"
              >
                <option v-for="perPage in perPageOptions" :key="perPage" :value="perPage">
                  {{ perPage }} Baris
                </option>
              </select>
              <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-500">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </div>
            </div>
            <BaseButton
              @click="resetFilters"
              :icon="mdiRefresh"
              color="whiteDark"
              label="Reset"
              small
              class="shadow-sm"
            />
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-slate-800 dark:text-gray-400 border-b dark:border-gray-700">
              <tr>
                <th scope="col" class="px-6 py-4">Admin</th>
                <th scope="col" class="px-6 py-4">Roles</th>
                <th scope="col" class="px-6 py-4 text-right">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
              <tr v-if="userData.length === 0">
                <td colspan="3" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                  <div class="flex flex-col items-center justify-center">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <span class="text-base font-medium">Data admin tidak ditemukan</span>
                    <span class="text-sm mt-1">Coba sesuaikan filter pencarian Anda</span>
                  </div>
                </td>
              </tr>
              <tr
                v-for="user in userData"
                :key="user.id"
                class="bg-white dark:bg-slate-900 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors"
              >
                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/50 flex items-center justify-center text-blue-600 dark:text-blue-400 font-bold text-lg mr-4">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <div class="font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                      <div class="text-gray-500 dark:text-gray-400">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4">
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-for="role in user.roles"
                      :key="role.id"
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="role.name === 'super-admin' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400' : (role.name === 'admin' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-gray-100 text-gray-800 dark:bg-slate-700 dark:text-gray-300')"
                    >
                      {{ role.name }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      @click="editUser(user)"
                      class="p-2 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg transition-colors"
                      title="Edit Admin"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                    </button>
                    <button
                      @click="deleteUser(user.id)"
                      class="p-2 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                      title="Hapus Admin"
                    >
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="userLinks.length > 3" class="p-4 border-t border-gray-100 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-800/50 rounded-b-lg">
          <BaseLevel>
            <BaseButtons>
              <BaseButton
                v-for="(link, key) in userLinks"
                :key="key"
                :active="link.active"
                :label="link.label"
                :color="link.active ? 'lightDark' : 'whiteDark'"
                :disabled="!link.url"
                small
                @click="link.url && router.get(link.url, {}, { preserveState: true, preserveScroll: true })"
              />
            </BaseButtons>
          </BaseLevel>
        </div>
      </CardBox>

      <CardBoxModal
        v-model="isModalActive"
        :title="isEditing ? 'Edit Admin' : 'Tambah Admin'"
        button="info"
        :button-label="isEditing ? 'Simpan' : 'Tambah'"
        has-cancel
        :close-on-confirm="false"
        @confirm="submit"
      >
        <FormField label="Nama" :error="form.errors.name">
          <FormControl v-model="form.name" :icon="mdiAccountGroup" placeholder="Nama" />
        </FormField>

        <FormField label="Email" :error="form.errors.email">
          <FormControl v-model="form.email" :icon="mdiAccountKey" placeholder="Email" />
        </FormField>

        <FormField label="Password" :error="form.errors.password">
          <div class="relative password-wrapper">
            <FormControl
              v-model="form.password"
              :icon="mdiShieldKey"
              :type="showPassword ? 'text' : 'password'"
              :placeholder="isEditing ? 'Kosongkan jika tidak diubah' : 'Password'"
            />
            <button
              type="button"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
              @click="showPassword = !showPassword"
            >
              <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path v-if="showPassword" fill="currentColor" :d="mdiEyeOff" />
                <path v-else fill="currentColor" :d="mdiEye" />
              </svg>
            </button>
          </div>
        </FormField>

        <FormField label="Konfirmasi Password" :error="form.errors.password_confirmation">
          <div class="relative password-wrapper">
            <FormControl
              v-model="form.password_confirmation"
              :icon="mdiShieldKey"
              :type="showConfirmPassword ? 'text' : 'password'"
              :placeholder="isEditing ? 'Kosongkan jika tidak diubah' : 'Konfirmasi Password'"
            />
            <button
              type="button"
              class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
              @click="showConfirmPassword = !showConfirmPassword"
            >
              <svg class="w-5 h-5" viewBox="0 0 24 24">
                <path v-if="showConfirmPassword" fill="currentColor" :d="mdiEyeOff" />
                <path v-else fill="currentColor" :d="mdiEye" />
              </svg>
            </button>
          </div>
        </FormField>

        <FormField label="Roles" :error="form.errors.roles">
          <FormCheckRadioGroup
            v-model="form.roles"
            name="roles"
            :options="roleOptions"
            is-column
          />
        </FormField>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>
.password-wrapper :deep(input) {
  padding-right: 2.5rem;
}
</style>
