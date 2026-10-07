<script setup>
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import {
  mdiFileDocument,
  mdiMagnify,
  mdiPrinter,
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

const props = defineProps({
  mutasi: {
    type: Object,
    default: () => ({}),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const search = ref(props.filters.search || '')
const perPage = ref(props.filters.per_page || 10)
const perPageOptions = [10, 20, 50, 100, 250, 500]

const updateFilters = debounce(() => {
  router.get(
    route('admin.cetak.p1'),
    { search: search.value, per_page: perPage.value },
    { preserveState: true, replace: true }
  )
}, 300)

watch([search, perPage], () => {
  updateFilters()
})

const items = computed(() => props.mutasi.data)
const links = computed(() => {
  if (!props.mutasi.links) return []
  return props.mutasi.links.map((link) => {
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
    window.open(route('admin.cetak.p1.print', id), '_blank');
}

const page = usePage()

const canDelete = computed(() => {
  const roles = page.props?.auth?.user?.roles || []
  return roles.includes('admin') || roles.includes('super-admin')
})

const destroy = (id) => {
  if (confirm('Apakah Anda yakin ingin menghapus data ini?')) {
    router.delete(route('admin.cetak.p1.destroy', id))
  }
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Cetak Surat P1" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiFileDocument"
        title="Cetak Surat P1 (Mutasi PKL)"
        main
      />

      <div class="mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="w-full md:w-1/2">
            <FormField>
            <FormControl
                v-model="search"
                :icon="mdiMagnify"
                placeholder="Cari nama siswa..."
            />
            </FormField>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm font-medium dark:text-gray-300">Tampilkan:</span>
            <select v-model="perPage" class="border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm dark:bg-slate-800 dark:border-slate-700 text-sm pl-3 pr-10">
                <option v-for="opt in perPageOptions" :key="opt" :value="opt">{{ opt }}</option>
            </select>
        </div>
      </div>

      <CardBox class="mb-6" has-table>
        <div v-if="items.length === 0" class="text-center py-10 text-gray-500">
            <p>Tidak ada data mutasi yang disetujui.</p>
        </div>
        <div v-else class="overflow-y-auto max-h-[350px] border-b border-gray-100 dark:border-slate-800">
            <table class="w-full relative">
            <thead class="sticky top-0 z-10 bg-gray-50 dark:bg-slate-800 shadow-sm">
                <tr>
                <th>No</th>
                <th>Siswa</th>
                <th>Kelas</th>
                <th>Bengkel Asal</th>
                <th>Bengkel Tujuan</th>
                <th>Pembimbing</th>
                <th>Tanggal Persetujuan</th>
                <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, index) in items" :key="item.id">
                <td data-label="No">{{ (mutasi.current_page - 1) * mutasi.per_page + index + 1 }}</td>
                <td data-label="Siswa">
                    <div class="font-bold">{{ item.siswa?.nama || '-' }}</div>
                    <div v-if="item.siswa?.nisn" class="text-xs text-slate-400 font-mono">{{ item.siswa.nisn }}</div>
                </td>
                <td data-label="Kelas">
                    {{ item.siswa?.kelas?.nama_kelas || '-' }}
                </td>
                <td data-label="Bengkel Asal">
                    {{ item.bengkel_asal?.nama_bengkel || '-' }}
                </td>
                <td data-label="Bengkel Tujuan">
                    {{ item.nama_bengkel_baru || item.bengkel_tujuan?.nama_bengkel || '-' }}
                </td>
                <td data-label="Pembimbing">
                    <div class="text-xs text-slate-500">Asal: <span class="font-medium text-slate-700 dark:text-slate-300">{{ item.pembimbing?.nama_guru || '-' }}</span></div>
                    <div v-if="item.pembimbing_tujuan" class="text-xs text-indigo-600 dark:text-indigo-400">Tujuan: <span class="font-medium">{{ item.pembimbing_tujuan?.nama_guru }}</span></div>
                </td>
                <td data-label="Tanggal Persetujuan">
                    {{ item.tanggal_surat ? new Date(item.tanggal_surat).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}
                </td>
                <td class="before:hidden lg:w-1 whitespace-nowrap">
                    <BaseButtons type="justify-start lg:justify-end" no-wrap>
                    <BaseButton
                        color="info"
                        :icon="mdiPrinter"
                        small
                        label="Cetak"
                        @click="print(item.id)"
                    />
                    <BaseButton
                        v-if="canDelete"
                        color="danger"
                        :icon="mdiTrashCan"
                        small
                        label="Hapus"
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
            <small>Halaman {{ mutasi.current_page }} dari {{ mutasi.last_page }}</small>
            </BaseLevel>
        </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
