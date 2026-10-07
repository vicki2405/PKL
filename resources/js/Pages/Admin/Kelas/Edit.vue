<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import { mdiSchool, mdiArrowLeft, mdiAlertCircle } from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import { computed, ref, watch } from 'vue'

const props = defineProps({
  kelas: {
    type: Object,
    required: true,
  },
  jurusanOptions: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  jurusan_id: props.kelas.jurusan_id,
  nama_kelas: props.kelas.nama_kelas,
  tingkat: props.kelas.tingkat,
  keterangan: props.kelas.keterangan,
  wali_kelas: props.kelas.wali_kelas || '',
  no_wa_wali: props.kelas.no_wa_wali || '',
})

const jurusanSelectOptions = computed(() =>
  (props.jurusanOptions || []).map((j) => ({
    id: j.id,
    label: j.kode_jurusan ? `${j.kode_jurusan} - ${j.nama_jurusan}` : j.nama_jurusan,
  })),
)

const submit = () => {
  form.put(route('admin.kelas.update', props.kelas.id))
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Edit Kelas" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiSchool" title="Edit Kelas" main>
        <BaseButton
          :icon="mdiArrowLeft"
          label="Kembali"
          color="whiteDark"
          rounded-full
          small
          :to="route('admin.kelas.index')"
        />
      </SectionTitleLineWithButton>

      <NotificationBar
        v-if="$page.props.errors && Object.keys($page.props.errors).length"
        color="danger"
        :icon="mdiAlertCircle"
      >
        Periksa kembali input Anda.
      </NotificationBar>

      <CardBox class="mb-6">
        <form class="grid gap-4 md:grid-cols-2" @submit.prevent="submit">
          <FormField class="md:col-span-2" label="Jurusan" :error="form.errors.jurusan_id">
            <FormControl
              v-model="form.jurusan_id"
              :options="jurusanSelectOptions"
              type="select"
              placeholder="Pilih Jurusan"
              required
            />
          </FormField>

          <FormField label="Nama Kelas" :error="form.errors.nama_kelas">
            <FormControl v-model="form.nama_kelas" placeholder="Contoh: X TKJ 1" required />
          </FormField>

          <FormField label="Tingkat" :error="form.errors.tingkat">
            <FormControl v-model="form.tingkat" placeholder="X / XI / XII" />
          </FormField>

          <FormField label="Wali Kelas" :error="form.errors.wali_kelas">
            <FormControl v-model="form.wali_kelas" placeholder="Nama Wali Kelas" />
          </FormField>

          <FormField label="No WA Wali Kelas" :error="form.errors.no_wa_wali">
            <FormControl v-model="form.no_wa_wali" placeholder="Contoh: 08123456789" />
          </FormField>

          <FormField class="md:col-span-2" label="Keterangan" :error="form.errors.keterangan">
            <FormControl v-model="form.keterangan" placeholder="Opsional" />
          </FormField>

          <div class="md:col-span-2 flex justify-end">
            <BaseButton type="submit" label="Update" color="info" />
          </div>
        </form>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>

