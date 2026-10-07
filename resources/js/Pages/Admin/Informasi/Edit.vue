<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import {
  mdiFileDocumentEdit,
  mdiArrowLeft,
  mdiContentSave,
  mdiCalendar,
  mdiFormatTitle,
  mdiText,
  mdiCheckCircle
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import FormCheckRadio from '@admin/components/FormCheckRadio.vue'

const props = defineProps({
  informasi: {
    type: Object,
    required: true,
  },
})

const form = useForm({
  judul: props.informasi.judul,
  deskripsi: props.informasi.deskripsi,
  tanggal_terbit: props.informasi.tanggal_terbit,
  status: Boolean(props.informasi.status),
})

const submit = () => {
  form.put(route('admin.informasi.update', props.informasi.id))
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Edit Informasi" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiFileDocumentEdit" title="Edit Informasi" main>
        <BaseButton
          :icon="mdiArrowLeft"
          label="Kembali"
          color="whiteDark"
          rounded-full
          small
          :href="route('admin.informasi.index')"
        />
      </SectionTitleLineWithButton>

      <CardBox is-form @submit.prevent="submit">
        <FormField label="Judul Informasi" :error="form.errors.judul">
          <FormControl
            v-model="form.judul"
            :icon="mdiFormatTitle"
            placeholder="Masukkan judul informasi"
          />
        </FormField>

        <FormField label="Tanggal Terbit" :error="form.errors.tanggal_terbit">
          <FormControl
            v-model="form.tanggal_terbit"
            type="date"
            :icon="mdiCalendar"
          />
        </FormField>

        <FormField label="Deskripsi" :error="form.errors.deskripsi">
          <FormControl
            v-model="form.deskripsi"
            type="textarea"
            placeholder="Masukkan isi informasi"
          />
        </FormField>

        <FormField label="Status Publikasi">
          <FormCheckRadio
            v-model="form.status"
            type="switch"
            label="Aktifkan Informasi"
            :value="true"
            name="status"
          />
        </FormField>

        <template #footer>
          <div class="flex items-center justify-end gap-3">
            <BaseButton
              type="submit"
              color="info"
              label="Simpan Perubahan"
              :icon="mdiContentSave"
              :loading="form.processing"
            />
          </div>
        </template>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
