<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import {
  mdiFileDocument,
  mdiMagnify,
  mdiPrinter,
  mdiPlus,
  mdiPencil,
  mdiTrashCan
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import BaseLevel from '@admin/components/BaseLevel.vue'
import { computed, ref, watch } from 'vue'
import debounce from 'lodash/debounce'
import NotificationBar from '@admin/components/NotificationBar.vue'

const props = defineProps({
  suratPengantars: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const search = ref(props.filters.search || '')

const updateFilters = debounce(() => {
  router.get(
    route('admin.surat.pengantar.index'),
    { search: search.value },
    { preserveState: true, replace: true }
  )
}, 300)

watch(search, () => {
  updateFilters()
})

const items = computed(() => props.suratPengantars.data)
const links = computed(() => {
  if (!props.suratPengantars.links) return []
  return props.suratPengantars.links.map((link) => {
    let label = link.label
    label = label.replace('&laquo;', '').replace('&raquo;', '').trim()
    if (label === 'Previous') {
      label = 'Sebelumnya'
    } else if (label === 'Next') {
      label = 'Selanjutnya'
    }
    return { ...link, label }
  })
})

const print = (id) => {
    window.open(route('admin.surat.pengantar.print', id), '_blank');
}

const destroy = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus surat ini?')) {
    router.delete(route('admin.surat.pengantar.destroy', id))
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Surat Pengantar" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiFileDocument"
        title="Surat Pengantar"
        main
      >
        <BaseButton
          :icon="mdiPlus"
          label="Tambah Surat"
          color="contrast"
          rounded-full
          small
          :to="route('admin.surat.pengantar.create')"
        />
      </SectionTitleLineWithButton>

      <NotificationBar
        v-if="$page.props.flash && $page.props.flash.message"
        color="success"
        :icon="mdiFileDocument"
      >
        {{ $page.props.flash.message }}
      </NotificationBar>

      <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="w-full md:w-1/2">
            <FormField>
            <FormControl
                v-model="search"
                :icon="mdiMagnify"
                placeholder="Cari nomor surat atau tujuan..."
            />
            </FormField>
        </div>
      </div>

      <CardBox class="mb-6" has-table>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
            <thead>
                <tr>
                <th class="p-4 border-b dark:border-slate-700">Nomor Surat</th>
                <th class="p-4 border-b dark:border-slate-700">Tujuan</th>
                <th class="p-4 border-b dark:border-slate-700">Tanggal Surat</th>
                <th class="p-4 border-b dark:border-slate-700">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="items.length === 0">
                    <td colspan="4" class="p-4 text-center text-gray-500">Belum ada data surat pengantar.</td>
                </tr>
                <tr v-for="item in items" :key="item.id">
                <td class="p-4 border-b dark:border-slate-700">
                    {{ item.nomor_surat }}
                </td>
                <td class="p-4 border-b dark:border-slate-700">
                    {{ item.tujuan_surat }}
                </td>
                <td class="p-4 border-b dark:border-slate-700">
                    {{ item.tanggal_surat ? new Date(item.tanggal_surat).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}
                </td>
                <td class="p-4 border-b dark:border-slate-700 whitespace-nowrap">
                    <BaseButtons type="justify-start lg:justify-end" no-wrap>
                    <BaseButton
                        color="info"
                        :icon="mdiPrinter"
                        small
                        label="Cetak"
                        @click="print(item.id)"
                    />
                    <BaseButton
                        color="warning"
                        :icon="mdiPencil"
                        small
                        :to="route('admin.surat.pengantar.edit', item.id)"
                    />
                    <BaseButton
                        color="danger"
                        :icon="mdiTrashCan"
                        small
                        @click="destroy(item.id)"
                    />
                    </BaseButtons>
                </td>
                </tr>
            </tbody>
            </table>
        </div>

        <div v-if="links.length > 3" class="p-3 lg:px-6 border-t border-gray-100 dark:border-slate-800">
            <BaseLevel>
            <BaseButtons>
                <BaseButton
                v-for="link in links"
                :key="link.label"
                :active="link.active"
                :label="link.label"
                :color="link.active ? 'contrast' : 'whiteDark'"
                small
                @click="link.url ? router.visit(link.url) : null"
                :disabled="!link.url"
                />
            </BaseButtons>
            <small>Halaman {{ suratPengantars.current_page }} dari {{ suratPengantars.last_page }}</small>
            </BaseLevel>
        </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>