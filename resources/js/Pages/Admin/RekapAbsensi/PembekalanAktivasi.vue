<script setup>
import { Head, useForm, Link, router } from '@inertiajs/vue3'
import {
  mdiCalendarPlus,
  mdiArrowLeft,
  mdiCalendar,
  mdiCheck,
  mdiClose,
  mdiDelete,
  mdiToggleSwitch,
  mdiToggleSwitchOff,
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import { ref } from 'vue'

const props = defineProps({
  aktivasis: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  tanggal: '',
})

const submit = () => {
  form.post(route('admin.pembekalan-aktivasi.store'), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset()
    },
  })
}

const toggleAktivasi = (aktivasi) => {
  router.put(route('admin.pembekalan-aktivasi.toggle', aktivasi.id), {}, {
    preserveScroll: true,
  })
}

const destroyAktivasi = (aktivasi) => {
  if (confirm(`Hapus aktivasi tanggal ${aktivasi.tanggal_formatted}?`)) {
    router.delete(route('admin.pembekalan-aktivasi.destroy', aktivasi.id), {
      preserveScroll: true,
    })
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Aktivasi Pembekalan" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiCalendarPlus"
        title="Aktivasi Absensi Pembekalan"
        main
      >
        <BaseButton
          :to="route('admin.rekap-absensi.index', { tab: 'pembekalan' })"
          :icon="mdiArrowLeft"
          label="Kembali"
          color="whiteDark"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>

      <!-- Form Tambah Aktivasi -->
      <CardBox class="mb-6 shadow-md rounded-lg">
        <form @submit.prevent="submit">
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-4">
              <FormField label="Pilih Tanggal">
                <FormControl
                  v-model="form.tanggal"
                  :icon="mdiCalendar"
                  type="date"
                  :min="new Date().toISOString().substr(0, 10)"
                />
              </FormField>
            </div>
            <div class="md:col-span-4">
              <BaseButton
                type="submit"
                color="success"
                label="Aktifkan"
                :icon="mdiCalendarPlus"
                :loading="form.processing"
                rounded-full
                small
              />
            </div>
          </div>
        </form>
      </CardBox>

      <!-- Daftar Aktivasi -->
      <CardBox class="shadow-md rounded-lg" has-table>
        <div v-if="aktivasis.length === 0" class="py-8 text-center text-gray-500">
          <BaseIcon :path="mdiCalendarPlus" size="48" class="mb-4" />
          <p>Belum ada aktivasi pembekalan.</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="table-auto w-full">
            <thead class="bg-gray-50 dark:bg-slate-800 text-gray-500 dark:text-gray-400 uppercase text-xs font-semibold tracking-wider">
              <tr>
                <th class="px-4 py-3 text-left">No</th>
                <th class="px-4 py-3 text-left">Tanggal</th>
                <th class="px-4 py-3 text-center">Status</th>
                <th class="px-4 py-3 text-left">Dibuat Oleh</th>
                <th class="px-4 py-3 text-left">Dibuat Pada</th>
                <th class="px-4 py-3 text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-slate-700">
              <tr
                v-for="(a, index) in aktivasis"
                :key="a.id"
                class="hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors duration-150"
              >
                <td class="px-4 py-3 text-gray-500 text-sm">{{ index + 1 }}</td>
                <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">{{ a.tanggal_formatted }}</td>
                <td class="px-4 py-3 text-center">
                  <span
                    class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold"
                    :class="a.is_active
                      ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300'
                      : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'"
                  >
                    <BaseIcon :path="a.is_active ? mdiToggleSwitch : mdiToggleSwitchOff" size="14" />
                    {{ a.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ a.created_by }}</td>
                <td class="px-4 py-3 text-sm text-gray-500">{{ a.created_at }}</td>
                <td class="px-4 py-3 text-center">
                  <div class="flex items-center justify-center gap-2">
                    <BaseButton
                      :color="a.is_active ? 'warning' : 'success'"
                      :icon="a.is_active ? mdiClose : mdiCheck"
                      :label="a.is_active ? 'Nonaktifkan' : 'Aktifkan'"
                      small
                      rounded-full
                      @click="toggleAktivasi(a)"
                    />
                    <BaseButton
                      color="danger"
                      :icon="mdiDelete"
                      small
                      rounded-full
                      @click="destroyAktivasi(a)"
                    />
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
