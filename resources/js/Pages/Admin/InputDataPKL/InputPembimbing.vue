<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import {
  mdiHumanMaleBoard,
  mdiArrowLeft,
  mdiOfficeBuildingCog,
  mdiAlertCircle,
  mdiPlus,
  mdiTrashCan,
  mdiMapMarker,
  mdiShieldStarOutline,
  mdiDomain,
  mdiCheckCircle,
  mdiAlertCircleOutline,
  mdiAccountCheck,
  mdiCheckCircleOutline,
  mdiContentSave,
  mdiPackageVariantClosed,
  mdiSchoolOutline,
} from '@mdi/js'
import { computed, ref } from 'vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import FormField from '@admin/components/FormField.vue'
import FormControlSearchable from '@admin/components/FormControlSearchable.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'

const props = defineProps({
  pembimbingOptions: {
    type: Array,
    default: () => [],
  },
  bengkelOptions: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({ total: 0, assigned: 0, unassigned: 0 }),
  },
})

const form = useForm({
  pembimbing_id: null,
  bengkel_ids: [],
})

const selectedPembimbing = ref(null)
const selectedBengkel = ref(null)

const pembimbingSelectOptions = computed(() => props.pembimbingOptions || [])
const bengkelSelectOptions = computed(() => props.bengkelOptions || [])

const selectedBengkels = computed(() => {
  const set = new Set(form.bengkel_ids.map((id) => Number(id)))
  return bengkelSelectOptions.value.filter((b) => set.has(Number(b.id)))
})

const availableBengkelOptions = computed(() => {
  const chosen = new Set(form.bengkel_ids.map((id) => Number(id)))
  return bengkelSelectOptions.value.filter((b) => !chosen.has(Number(b.id)))
})

const addBengkel = () => {
  const id = selectedBengkel.value?.id
  if (!id) return
  const next = [...form.bengkel_ids.map((x) => Number(x)), Number(id)]
  form.bengkel_ids = [...new Set(next)]
  selectedBengkel.value = null
}

const removeBengkel = (id) => {
  const removeId = Number(id)
  form.bengkel_ids = form.bengkel_ids.filter((x) => Number(x) !== removeId)
}

const submit = () => {
  form.pembimbing_id = selectedPembimbing.value?.id ? Number(selectedPembimbing.value.id) : null
  form.post(route('admin.input_data_pkl.bulk_set_pembimbing'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Input Pembimbing Baru" />
    <SectionMain>
      <div class="relative overflow-hidden rounded-3xl mb-6 border border-slate-200/70 dark:border-slate-700/70">
        <div class="absolute inset-0 bg-[radial-gradient(1000px_circle_at_20%_20%,rgba(56,189,248,0.25),transparent_55%),radial-gradient(900px_circle_at_80%_30%,rgba(167,139,250,0.22),transparent_60%),radial-gradient(900px_circle_at_50%_120%,rgba(34,197,94,0.16),transparent_55%)]" />
        <div class="absolute inset-0 bg-[linear-gradient(to_bottom,rgba(255,255,255,0.66),rgba(255,255,255,0.94))] dark:bg-[linear-gradient(to_bottom,rgba(2,6,23,0.55),rgba(2,6,23,0.92))]" />
        <div class="relative p-6 md:p-8">
          <SectionTitleLineWithButton :icon="mdiHumanMaleBoard" title="Input Pembimbing Baru" main>
            <BaseButtons>
              <BaseButton
                :to="route('admin.input_data_pkl.input_pembimbing')"
                :icon="mdiArrowLeft"
                label="Kembali"
                color="whiteDark"
                rounded-full
                small
              />
            </BaseButtons>
          </SectionTitleLineWithButton>

          <p class="mt-2 text-sm md:text-base text-slate-600 dark:text-slate-300 max-w-3xl">
            Pilih pembimbing, tambah bengkel terisi (bisa lebih dari 5), cek tabel bengkel terpilih, lalu simpan.
          </p>
        </div>
      </div>

      <NotificationBar v-if="$page.props.flash?.message" color="success" :icon="mdiAlertCircle" class="mb-6 shadow-md">
        {{ $page.props.flash.message }}
      </NotificationBar>
      <NotificationBar v-if="$page.props.flash?.error" color="danger" :icon="mdiAlertCircle" class="mb-6 shadow-md">
        {{ $page.props.flash.error }}
      </NotificationBar>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white dark:bg-slate-800/50 p-5 flex items-center gap-4 shadow-sm">
          <div class="w-12 h-12 rounded-2xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center shrink-0">
            <BaseIcon :path="mdiDomain" :size="24" />
          </div>
          <div class="min-w-0">
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Total Bengkel</div>
            <div class="text-2xl font-extrabold text-slate-900 dark:text-white mt-0.5">{{ stats.total }}</div>
          </div>
        </div>
        <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white dark:bg-slate-800/50 p-5 flex items-center gap-4 shadow-sm">
          <div class="w-12 h-12 rounded-2xl bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
            <BaseIcon :path="mdiCheckCircle" :size="24" />
          </div>
          <div class="min-w-0">
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Sudah Ada Pembimbing</div>
            <div class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-0.5">{{ stats.assigned }}</div>
          </div>
        </div>
        <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-white dark:bg-slate-800/50 p-5 flex items-center gap-4 shadow-sm">
          <div class="w-12 h-12 rounded-2xl bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center shrink-0">
            <BaseIcon :path="mdiAlertCircleOutline" :size="24" />
          </div>
          <div class="min-w-0">
            <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide">Belum Ada Pembimbing</div>
            <div class="text-2xl font-extrabold text-amber-600 dark:text-amber-400 mt-0.5">{{ stats.unassigned }}</div>
          </div>
        </div>
      </div>

      <form class="grid grid-cols-1 lg:grid-cols-2 gap-6" @submit.prevent="submit">
        <div class="flex flex-col gap-6 h-full">
          <CardBox class="shadow-xl border-0 rounded-2xl overflow-visible relative z-20 flex-1 flex flex-col">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-white/60 dark:bg-slate-900/40 rounded-t-2xl">
              <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                  <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Pilih Pembimbing</div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">Pilih pembimbing yang akan ditugaskan ke bengkel.</div>
                </div>
                <div class="w-10 h-10 shrink-0 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 flex items-center justify-center">
                  <BaseIcon :path="mdiHumanMaleBoard" :size="20" />
                </div>
              </div>
            </div>

            <div class="p-5 flex-1 flex flex-col">
              <div v-if="pembimbingSelectOptions.length === 0" class="rounded-xl border border-amber-200 bg-amber-50 text-amber-900 p-4">
                <div class="flex items-center gap-2 font-semibold">
                  <BaseIcon :path="mdiPackageVariantClosed" :size="20" />
                  Data pembimbing masih kosong
                </div>
                <div class="mt-1 text-sm">Tambahkan data pembimbing terlebih dahulu.</div>
                <div class="mt-3">
                  <Link href="/admin/pembimbing" class="inline-flex items-center gap-2 text-sm font-semibold text-amber-800 hover:underline">
                    Buka Data Pembimbing
                  </Link>
                </div>
              </div>

              <div v-else class="flex-1 flex flex-col gap-4">
                <FormField label="Nama Pembimbing" :error="form.errors.pembimbing_id" class="flex-1">
                  <FormControlSearchable
                    v-model="selectedPembimbing"
                    :options="pembimbingSelectOptions"
                    :icon="mdiHumanMaleBoard"
                    placeholder="Cari & pilih pembimbing..."
                  />
                </FormField>
                <div class="rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-slate-50/70 dark:bg-slate-900/30 p-4">
                  <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400">
                    <BaseIcon :path="mdiAccountCheck" :size="14" />
                    Terpilih
                  </div>
                  <div class="mt-1 text-base font-extrabold text-slate-900 dark:text-white break-all">
                    {{ selectedPembimbing?.label || '—' }}
                  </div>
                </div>
              </div>
            </div>
          </CardBox>
        </div>

        <div class="flex flex-col gap-6 h-full">
          <CardBox class="shadow-xl border-0 rounded-2xl overflow-visible relative z-10 flex-1 flex flex-col">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-white/60 dark:bg-slate-900/40 rounded-t-2xl">
              <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                  <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Tambah Bengkel</div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">Dropdown hanya dari bengkel yang belum memiliki pembimbing.</div>
                </div>
                <div class="w-10 h-10 shrink-0 rounded-2xl bg-slate-900 text-white dark:bg-white dark:text-slate-900 flex items-center justify-center">
                  <BaseIcon :path="mdiOfficeBuildingCog" :size="20" />
                </div>
              </div>
            </div>

            <div class="p-5 flex-1 flex flex-col">
              <FormField label="Cari & Tambah Bengkel" :error="form.errors.bengkel_ids" class="flex-1 flex flex-col">
                <div class="flex flex-col sm:flex-row gap-3">
                  <div class="flex-1">
                    <FormControlSearchable
                      v-model="selectedBengkel"
                      :options="availableBengkelOptions"
                      :icon="mdiOfficeBuildingCog"
                      placeholder="Cari & pilih bengkel..."
                    />
                  </div>
                  <BaseButton
                    type="button"
                    :icon="mdiPlus"
                    label="Tambah"
                    color="success"
                    rounded-full
                    class="sm:self-start shrink-0"
                    @click="addBengkel"
                    :disabled="!selectedBengkel"
                  />
                </div>
                <p v-if="form.errors.bengkel_ids" class="text-red-500 text-xs mt-2">{{ form.errors.bengkel_ids }}</p>
              </FormField>
              <div class="mt-auto rounded-2xl border border-slate-200/70 dark:border-slate-700/70 bg-slate-50/70 dark:bg-slate-900/30 p-4">
                <div class="flex items-center gap-1.5 text-xs font-semibold text-slate-500 dark:text-slate-400">
                  <BaseIcon :path="mdiSchoolOutline" :size="14" />
                  Sudah Ditambahkan
                </div>
                <div class="mt-1 text-base font-extrabold text-slate-900 dark:text-white">{{ selectedBengkels.length }} bengkel</div>
              </div>
            </div>
          </CardBox>
        </div>

        <div class="lg:col-span-2">
          <CardBox class="shadow-xl border-0 rounded-2xl overflow-hidden">
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 bg-white/60 dark:bg-slate-900/40">
              <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                  <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Tabel Bengkel Terinput</div>
                  <div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ selectedBengkels.length }} bengkel dipilih.
                  </div>
                </div>
                <div class="w-10 h-10 shrink-0 rounded-2xl bg-indigo-600 text-white flex items-center justify-center">
                  <BaseIcon :path="mdiOfficeBuildingCog" :size="20" />
                </div>
              </div>
            </div>

            <div class="p-5">
              <div v-if="selectedBengkels.length === 0" class="py-10 text-center text-slate-500 dark:text-slate-400">
                <div class="flex justify-center mb-3">
                  <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-700/50 flex items-center justify-center">
                    <BaseIcon :path="mdiOfficeBuildingCog" :size="28" />
                  </div>
                </div>
                <p class="text-sm font-medium">Belum ada bengkel yang dipilih.</p>
                <p class="text-xs mt-1">Gunakan form di samping untuk menambah bengkel.</p>
              </div>

              <div v-else class="overflow-auto">
                <table class="w-full text-sm text-left text-slate-600 dark:text-slate-300">
                  <thead class="text-xs uppercase bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200">
                    <tr>
                      <th class="px-4 py-3">Bengkel</th>
                      <th class="px-4 py-3">Lokasi</th>
                      <th class="px-4 py-3 text-center">Siswa</th>
                      <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="b in selectedBengkels" :key="b.id" class="border-b border-slate-100 dark:border-slate-800">
                      <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                          <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-1 rounded-full bg-slate-900 text-white dark:bg-white dark:text-slate-900 whitespace-nowrap">
                            <BaseIcon :path="mdiShieldStarOutline" :size="14" />
                            {{ b.kode_bengkel || '—' }}
                          </span>
                          <span class="font-semibold text-slate-900 dark:text-white">{{ b.nama_bengkel }}</span>
                        </div>
                      </td>
                      <td class="px-4 py-3">
                        <div class="flex items-start gap-2">
                          <BaseIcon :path="mdiMapMarker" :size="18" class="mt-0.5 text-slate-400 shrink-0" />
                          <div class="leading-tight min-w-0">
                            <div class="font-medium text-slate-800 dark:text-slate-200 break-words">
                              {{ b.dusun ? b.dusun + ', ' : '' }}{{ b.desa }}
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">Kec. {{ b.kecamatan || '-' }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-4 py-3 text-center font-bold text-slate-900 dark:text-white">
                        {{ b.penempatan_pkl_count }}
                      </td>
                      <td class="px-4 py-3 text-center">
                        <button
                          type="button"
                          class="inline-flex items-center justify-center w-9 h-9 rounded-full bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-950/30 dark:text-red-300 dark:hover:bg-red-900/40 transition-colors"
                          @click="removeBengkel(b.id)"
                          title="Hapus"
                        >
                          <BaseIcon :path="mdiTrashCan" :size="18" />
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </CardBox>
        </div>

        <div class="lg:col-span-2 rounded-2xl p-4 bg-white/70 dark:bg-slate-900/40 border border-slate-200/70 dark:border-slate-700/70">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="text-sm text-slate-600 dark:text-slate-300 min-w-0">
              <span class="font-semibold text-slate-900 dark:text-white truncate">{{ selectedPembimbing?.label || 'Pilih pembimbing' }}</span>
              <span class="mx-2 text-slate-400">•</span>
              <span class="whitespace-nowrap">{{ selectedBengkels.length }} bengkel</span>
            </div>
            <BaseButton
              type="submit"
              color="info"
              rounded-full
              :icon="mdiContentSave"
              :label="form.processing ? 'Menyimpan...' : 'Simpan'"
              :disabled="form.processing || form.bengkel_ids.length === 0 || !selectedPembimbing"
              class="w-full md:w-auto shadow-lg hover:shadow-xl transition-all shrink-0"
            />
          </div>
        </div>
      </form>
    </SectionMain>
  </LayoutAuthenticated>
</template>
