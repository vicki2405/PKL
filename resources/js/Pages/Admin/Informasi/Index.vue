<script setup>
import { Head, useForm, router } from '@inertiajs/vue3'
import { mdiFileDocumentEdit, mdiPlus, mdiPencil, mdiTrashCan, mdiMagnify, mdiAlertCircle, mdiCheckCircle } from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import { computed, ref, watch } from 'vue'

const props = defineProps({
  informasis: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const deleteForm = useForm({})

const search = ref(props.filters.search || '')

watch(search, (value) => {
  router.get(
    route('admin.informasi.index'),
    { search: value },
    {
      preserveState: true,
      replace: true,
    }
  )
})

const informasiData = computed(() =>
  props.informasis && Array.isArray(props.informasis.data) ? props.informasis.data : [],
)

const informasiLinks = computed(() =>
  props.informasis && Array.isArray(props.informasis.links) ? props.informasis.links : [],
)

const deleteInformasi = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus informasi ini?')) {
    deleteForm.delete(route('admin.informasi.destroy', id))
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Informasi PKL/Berita" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiFileDocumentEdit" title="Informasi PKL/Berita" main>
        <BaseButton
          :icon="mdiPlus"
          label="Tambah Informasi"
          color="contrast"
          rounded-full
          small
          :href="route('admin.informasi.create')"
        />
      </SectionTitleLineWithButton>

      <NotificationBar
        v-if="$page.props.flash && ($page.props.flash.success || $page.props.flash.message)"
        color="success"
        :icon="mdiCheckCircle"
      >
        {{ $page.props.flash.success || $page.props.flash.message }}
      </NotificationBar>

      <NotificationBar
        v-if="$page.props.flash && $page.props.flash.error"
        color="danger"
        :icon="mdiAlertCircle"
      >
        {{ $page.props.flash.error }}
      </NotificationBar>

      <div class="mb-6">
        <FormField>
          <FormControl
            v-model="search"
            :icon="mdiMagnify"
            placeholder="Cari Judul atau Deskripsi..."
          />
        </FormField>
      </div>

      <CardBox class="mb-6" has-table>
        <table>
          <thead>
            <tr>
              <th>Judul</th>
              <th>Tanggal Terbit</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="info in informasiData" :key="info.id">
              <td data-label="Judul">
                {{ info.judul }}
              </td>
              <td data-label="Tanggal Terbit">
                {{ info.tanggal_terbit }}
              </td>
              <td data-label="Status">
                <span
                  class="px-2 py-1 rounded text-xs font-bold"
                  :class="info.status ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                >
                  {{ info.status ? 'Aktif' : 'Tidak Aktif' }}
                </span>
              </td>
              <td class="before:hidden lg:w-1 whitespace-nowrap">
                <BaseButtons type="justify-start lg:justify-end" no-wrap>
                  <BaseButton
                    color="info"
                    :icon="mdiPencil"
                    small
                    :href="route('admin.informasi.edit', info.id)"
                  />
                  <BaseButton
                    color="danger"
                    :icon="mdiTrashCan"
                    small
                    @click="deleteInformasi(info.id)"
                  />
                </BaseButtons>
              </td>
            </tr>
            <tr v-if="informasiData.length === 0">
              <td colspan="4" class="text-center py-8 text-gray-500">
                Belum ada data informasi.
              </td>
            </tr>
          </tbody>
        </table>
        <div class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
          <BaseLevel>
            <BaseButtons>
              <BaseButton
                v-for="link in informasiLinks"
                :key="link.label"
                :active="link.active"
                :label="link.label"
                :color="link.active ? 'lightDark' : 'whiteDark'"
                small
                :href="link.url"
                :disabled="!link.url"
              />
            </BaseButtons>
            <small>Page {{ informasis.current_page }} of {{ informasis.last_page }}</small>
          </BaseLevel>
        </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
