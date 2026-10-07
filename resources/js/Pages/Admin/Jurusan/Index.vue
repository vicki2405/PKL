<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { mdiSchool, mdiTrashCan, mdiAlertCircle, mdiPencil, mdiClose, mdiPlus, mdiDomain } from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import CardBox from '@admin/components/CardBox.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import { computed, ref } from 'vue'

const props = defineProps({
  jurusans: {
    type: Object,
    default: () => ({}),
  },
})

const form = useForm({
  kode_jurusan: '',
  nama_jurusan: '',
  keterangan: '',
})

const isModalActive = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const deleteForm = useForm({})

const jurusanData = computed(() =>
  props.jurusans && Array.isArray(props.jurusans.data) ? props.jurusans.data : [],
)

const jurusanLinks = computed(() =>
  props.jurusans && Array.isArray(props.jurusans.links) ? props.jurusans.links : [],
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
    form.put(route('admin.jurusan.update', editingId.value), {
      onSuccess: () => {
        closeModal()
      },
    })
  } else {
    form.post(route('admin.jurusan.store'), {
      onSuccess: () => {
        closeModal()
      },
    })
  }
}

const startEdit = (jurusan) => {
  isEditing.value = true
  editingId.value = jurusan.id
  form.kode_jurusan = jurusan.kode_jurusan
  form.nama_jurusan = jurusan.nama_jurusan
  form.keterangan = jurusan.keterangan
  isModalActive.value = true
}

const deleteJurusan = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus data jurusan ini?')) {
    deleteForm.delete(route('admin.jurusan.destroy', id))
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Data Jurusan" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiSchool" title="Data Jurusan" main>
        <BaseButton
          :icon="mdiPlus"
          label="Tambah Jurusan"
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

      <CardBox class="mb-6" has-table>
        <table>
          <thead>
            <tr>
              <th>Kode Jurusan</th>
              <th>Nama Jurusan</th>
              <th>Keterangan</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="jurusan in jurusanData" :key="jurusan.id">
              <td data-label="Kode Jurusan">
                <span class="font-bold">{{ jurusan.kode_jurusan }}</span>
              </td>
              <td data-label="Nama Jurusan">
                {{ jurusan.nama_jurusan }}
              </td>
              <td data-label="Keterangan">
                {{ jurusan.keterangan || '-' }}
              </td>
              <td class="before:hidden lg:w-1 whitespace-nowrap text-right">
                <BaseButtons type="justify-start lg:justify-end" no-wrap>
                  <BaseButton
                    color="info"
                    :icon="mdiPencil"
                    small
                    @click="startEdit(jurusan)"
                  />
                  <BaseButton
                    color="danger"
                    :icon="mdiTrashCan"
                    small
                    @click="deleteJurusan(jurusan.id)"
                  />
                </BaseButtons>
              </td>
            </tr>
            <tr v-if="jurusanData.length === 0">
              <td colspan="4" class="text-center py-8 text-gray-500">
                <div class="flex flex-col items-center justify-center">
                  <span class="text-lg font-medium">Data jurusan belum tersedia</span>
                  <p class="text-sm mt-1">Silakan tambahkan jurusan baru.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination Links -->
        <div v-if="jurusanLinks.length > 3" class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
          <BaseLevel>
            <BaseButtons>
              <BaseButton
                v-for="(link, key) in jurusanLinks"
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
        :title="isEditing ? 'Edit Jurusan' : 'Tambah Jurusan'"
        :button="isEditing ? 'info' : 'contrast'"
        :button-label="isEditing ? 'Simpan Perubahan' : 'Simpan'"
        has-cancel
        @confirm="submit"
        @cancel="closeModal"
      >
        <FormField label="Kode Jurusan" :error="form.errors.kode_jurusan">
          <FormControl
            v-model="form.kode_jurusan"
            placeholder="Contoh: TKJ"
            :icon="mdiDomain"
          />
        </FormField>

        <FormField label="Nama Jurusan" :error="form.errors.nama_jurusan">
          <FormControl
            v-model="form.nama_jurusan"
            placeholder="Contoh: Teknik Komputer dan Jaringan"
            :icon="mdiSchool"
          />
        </FormField>

        <FormField label="Keterangan" :error="form.errors.keterangan">
          <FormControl
            v-model="form.keterangan"
            placeholder="Keterangan tambahan (opsional)"
            type="textarea"
          />
        </FormField>
      </CardBoxModal>

    </SectionMain>
  </LayoutAuthenticated>
</template>
