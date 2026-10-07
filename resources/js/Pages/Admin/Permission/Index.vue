<script setup>
import { Head, router, useForm } from '@inertiajs/vue3'
import { mdiShieldCheck, mdiTrashCan, mdiAlertCircle, mdiPlus, mdiPencil, mdiMagnify } from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import CardBox from '@admin/components/CardBox.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import { computed, ref, watch } from 'vue'
import debounce from 'lodash/debounce'

const props = defineProps({
  permissions: {
    type: Object, // Paginated object
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const form = useForm({
  name: '',
})

const deleteForm = useForm({})

const isModalActive = ref(false)
const isEditing = ref(false)
const editingId = ref(null)
const search = ref(props.filters.search || '')

const permissionData = computed(() =>
  props.permissions && Array.isArray(props.permissions.data) ? props.permissions.data : [],
)

const permissionLinks = computed(() =>
  props.permissions && Array.isArray(props.permissions.links) ? props.permissions.links : [],
)

const updateFilters = debounce(() => {
  router.get(
    route('admin.permissions.index'),
    { search: search.value },
    { preserveState: true, preserveScroll: true, replace: true },
  )
}, 300)

watch(search, () => {
  updateFilters()
})

const openModal = () => {
  isEditing.value = false
  editingId.value = null
  form.reset()
  form.clearErrors()
  isModalActive.value = true
}

const editPermission = (permission) => {
  isEditing.value = true
  editingId.value = permission.id
  form.name = permission.name
  form.clearErrors()
  isModalActive.value = true
}

const submit = () => {
  if (isEditing.value && editingId.value) {
    form.put(route('admin.permissions.update', editingId.value), {
      onSuccess: () => {
        isModalActive.value = false
        form.reset()
      },
    })
  } else {
    form.post(route('admin.permissions.store'), {
      onSuccess: () => {
        isModalActive.value = false
        form.reset()
      },
    })
  }
}

const deletePermission = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus permission ini?')) {
    deleteForm.delete(route('admin.permissions.destroy', id))
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Manajemen Permission" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiShieldCheck" title="Manajemen Permission" main>
        <BaseButton
          :icon="mdiPlus"
          label="Tambah Permission"
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

      <CardBox class="mb-6" has-table>
        <div class="p-4 border-b border-gray-100 dark:border-slate-800">
          <div class="w-full md:w-80">
            <FormField class="mb-0">
              <FormControl
                v-model="search"
                :icon="mdiMagnify"
                placeholder="Cari permission..."
                transparent
              />
            </FormField>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table>
            <thead>
              <tr>
                <th>Nama Permission</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="permission in permissionData" :key="permission.id">
                <td data-label="Nama">{{ permission.name }}</td>
                <td class="before:hidden lg:w-1 whitespace-nowrap text-right">
                  <BaseButtons type="justify-start lg:justify-end" no-wrap>
                    <BaseButton
                      color="info"
                      :icon="mdiPencil"
                      small
                      @click="editPermission(permission)"
                    />
                    <BaseButton
                      color="danger"
                      :icon="mdiTrashCan"
                      small
                      @click="deletePermission(permission.id)"
                    />
                  </BaseButtons>
                </td>
              </tr>
              <tr v-if="permissionData.length === 0">
                <td colspan="2" class="text-center py-4 text-gray-500">
                  Data permission tidak ditemukan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div v-if="permissionLinks.length > 3" class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
          <BaseLevel>
            <BaseButtons>
              <BaseButton
                v-for="(link, key) in permissionLinks"
                :key="key"
                :active="link.active"
                :label="link.label"
                :color="link.active ? 'lightDark' : 'whiteDark'"
                :disabled="!link.url"
                small
                @click="link.url && window.location.assign(link.url)"
                v-html="link.label"
              />
            </BaseButtons>
          </BaseLevel>
        </div>
      </CardBox>

      <CardBoxModal
        v-model="isModalActive"
        :title="isEditing ? 'Edit Permission' : 'Tambah Permission'"
        button="info"
        :button-label="isEditing ? 'Simpan' : 'Tambah'"
        has-cancel
        @confirm="submit"
      >
        <FormField label="Nama Permission" :error="form.errors.name">
          <FormControl v-model="form.name" :icon="mdiShieldCheck" placeholder="nama.aksi (contoh: user.create)" />
        </FormField>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template>
