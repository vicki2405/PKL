<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { mdiSchool, mdiPlus, mdiPencil, mdiTrashCan, mdiAlertCircle } from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import { computed, ref } from 'vue'

const props = defineProps({
  jabatans: {
    type: Object,
    default: () => ({}),
  },
})

const form = useForm({
  nama_jabatan: '',
  deskripsi: '',
})

const deleteForm = useForm({})

const isModalActive = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const jabatanData = computed(() =>
  props.jabatans && Array.isArray(props.jabatans.data) ? props.jabatans.data : [],
)

const jabatanLinks = computed(() =>
  props.jabatans && Array.isArray(props.jabatans.links) ? props.jabatans.links : [],
)

const openModal = () => {
  isEditing.value = false
  editingId.value = null
  form.reset()
  form.clearErrors()
  isModalActive.value = true
}

const closeModal = () => {
  isModalActive.value = false
  form.reset()
  form.clearErrors()
}

const submit = () => {
  if (isEditing.value && editingId.value) {
    form.put(route('admin.jabatan_pembimbing.update', editingId.value), {
      onSuccess: () => {
        closeModal()
      },
    })
  } else {
    form.post(route('admin.jabatan_pembimbing.store'), {
      onSuccess: () => {
        closeModal()
      },
    })
  }
}

const startEdit = (row) => {
  isEditing.value = true
  editingId.value = row.id
  form.nama_jabatan = row.nama_jabatan
  form.deskripsi = row.deskripsi
  isModalActive.value = true
}

const deleteJabatan = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus data jabatan ini?')) {
    deleteForm.delete(route('admin.jabatan_pembimbing.destroy', id))
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Data Jabatan Pembimbing" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiSchool" title="Data Jabatan Pembimbing" main>
        <BaseButton
          :icon="mdiPlus"
          label="Tambah Jabatan"
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

      <CardBox class="mb-6 max-w-4xl mx-auto" has-table>
        <div class="overflow-x-auto">
          <table>
            <thead>
              <tr>
                <th class="w-16 text-center">No</th>
                <th>Nama Jabatan</th>
                <th>Deskripsi</th>
                <th class="w-40 text-right">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(row, index) in jabatanData" :key="row.id">
                <td class="text-center" data-label="No">
                  {{ index + 1 + ((props.jabatans.current_page - 1) * props.jabatans.per_page) }}
                </td>
                <td data-label="Nama Jabatan">
                  <span class="font-semibold">{{ row.nama_jabatan }}</span>
                </td>
                <td data-label="Deskripsi">
                  {{ row.deskripsi || '-' }}
                </td>
                <td class="before:hidden lg:w-1 whitespace-nowrap text-right">
                  <BaseButtons type="justify-start lg:justify-end" no-wrap>
                    <BaseButton
                      color="info"
                      :icon="mdiPencil"
                      small
                      @click="startEdit(row)"
                    />
                    <BaseButton
                      color="danger"
                      :icon="mdiTrashCan"
                      small
                      @click="deleteJabatan(row.id)"
                    />
                  </BaseButtons>
                </td>
              </tr>
              <tr v-if="jabatanData.length === 0">
                <td colspan="4" class="text-center py-8 text-gray-500">
                  <div class="flex flex-col items-center justify-center">
                    <span class="text-lg font-medium">Data jabatan pembimbing belum tersedia</span>
                    <p class="text-sm mt-1">Silakan tambahkan jabatan baru.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          v-if="jabatanLinks.length > 3"
          class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800"
        >
          <BaseLevel>
            <BaseButtons>
              <BaseButton
                v-for="(link, key) in jabatanLinks"
                :key="key"
                :active="link.active"
                :label="link.label"
                :color="link.active ? 'lightDark' : 'whiteDark'"
                :disabled="!link.url"
                small
                @click="link.url && router.visit(link.url)"
                v-html="link.label"
              />
            </BaseButtons>
          </BaseLevel>
        </div>
      </CardBox>

      <CardBoxModal
        v-model="isModalActive"
        :title="isEditing ? 'Edit Jabatan' : 'Tambah Jabatan'"
        :button="isEditing ? 'info' : 'contrast'"
        :button-label="isEditing ? 'Simpan Perubahan' : 'Simpan'"
        has-cancel
        @confirm="submit"
        @cancel="closeModal"
      >
        <FormField label="Nama Jabatan" :error="form.errors.nama_jabatan">
          <FormControl
            v-model="form.nama_jabatan"
            placeholder="Contoh: Wali Kelas, Pembimbing Lapangan"
          />
        </FormField>

        <FormField label="Deskripsi" :error="form.errors.deskripsi">
          <FormControl
            v-model="form.deskripsi"
            placeholder="Deskripsi tugas jabatan (opsional)"
            type="textarea"
          />
        </FormField>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template>
