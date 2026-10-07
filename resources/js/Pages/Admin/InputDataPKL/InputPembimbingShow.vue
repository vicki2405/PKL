<script setup>
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import {
  mdiHumanMaleBoard,
  mdiArrowLeft,
  mdiPrinter,
  mdiMagnify,
  mdiMapMarker,
  mdiPhone,
  mdiWhatsapp,
  mdiOfficeBuildingCog,
  mdiAccountGroup,
  mdiTrashCan,
  mdiAlertCircle,
  mdiClose,
  mdiCardAccountDetails,
  mdiArrowUp,
  mdiArrowDown,
  mdiSwapHorizontal,
} from '@mdi/js'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'

const props = defineProps({
  pembimbing: {
    type: Object,
    required: true,
  },
  pesertaList: {
    type: Array,
    default: () => [],
  },
  bengkels: {
    type: Array,
    default: () => [],
  },
  stats: {
    type: Object,
    default: () => ({
      bengkel_total: 0,
      siswa_total: 0,
      area_monitoring: '—',
      tanggal_update: '',
    }),
  },
})

// Search & Sort state
const search = ref('')
const sortOrder = ref('asc') // 'asc' atau 'desc'

const toggleSortNama = () => {
  sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
}

// Modal Konfirmasi Lepas Bengkel
const isDetachModalOpen = ref(false)
const selectedBengkelToDetach = ref(null)

const confirmDetach = (bengkel) => {
  selectedBengkelToDetach.value = bengkel
  isDetachModalOpen.value = true
}

const executeDetach = () => {
  if (!selectedBengkelToDetach.value) return

  router.delete(
    route('admin.input_data_pkl.input_pembimbing.detach_bengkel', [
      props.pembimbing.id,
      selectedBengkelToDetach.value.id,
    ]),
    {
      preserveScroll: true,
      onFinish: () => {
        isDetachModalOpen.value = false
        selectedBengkelToDetach.value = null
      },
    }
  )
}

// Format link WhatsApp dari nomor HP
const getWhatsAppUrl = (phone) => {
  if (!phone) return null
  let clean = phone.replace(/[^0-9]/g, '')
  if (clean.startsWith('0')) {
    clean = '62' + clean.slice(1)
  }
  return `https://wa.me/${clean}`
}

// Filter pencarian peserta & pengurutan nama siswa
const filteredPeserta = computed(() => {
  let list = [...props.pesertaList]

  if (search.value) {
    const q = search.value.toLowerCase().trim()
    list = list.filter((p) => {
      return (
        (p.nama_siswa && p.nama_siswa.toLowerCase().includes(q)) ||
        (p.kelas && p.kelas.toLowerCase().includes(q)) ||
        (p.nama_bengkel && p.nama_bengkel.toLowerCase().includes(q)) ||
        (p.nama_pemilik && p.nama_pemilik.toLowerCase().includes(q)) ||
        (p.desa && p.desa.toLowerCase().includes(q)) ||
        (p.kecamatan && p.kecamatan.toLowerCase().includes(q)) ||
        (p.no_hp_siswa && p.no_hp_siswa.includes(q)) ||
        (p.bengkel_lama && p.bengkel_lama.toLowerCase().includes(q))
      )
    })
  }

  return list.sort((a, b) => {
    // Sort primer: nama bengkel (selalu A-Z, tetap saat toggle)
    const bengkelCmp = (a.nama_bengkel || '').localeCompare(
      b.nama_bengkel || '', 'id', { sensitivity: 'base', numeric: true }
    )
    if (bengkelCmp !== 0) return bengkelCmp

    // Sort sekunder: nama siswa (ikut toggle A-Z / Z-A)
    const namaCmp = (a.nama_siswa || '').localeCompare(
      b.nama_siswa || '', 'id', { sensitivity: 'base', numeric: true }
    )
    return sortOrder.value === 'asc' ? namaCmp : -namaCmp
  })
})

const handlePrint = () => {
  window.print()
}
</script>

<template>
  <LayoutAuthenticated>
    <Head :title="`Daftar Peserta & Pembimbing - ${props.pembimbing.nama_guru}`" />

    <SectionMain class="space-y-6">

      <!-- ================= 1. ACTION BAR / HEADER UTAMA (NON-PRINT) ================= -->
      <div class="print:hidden">
        <SectionTitleLineWithButton
          :icon="mdiHumanMaleBoard"
          title="Detail Penugasan Pembimbing"
          main
        >
          <!-- HANYA 2 TOMBOL DENGAN FUNGSI BERBEDA: 1 KEMBALI, 1 CETAK -->
          <div class="flex items-center gap-2">
            <BaseButton
              :to="route('admin.input_data_pkl.input_pembimbing')"
              :icon="mdiArrowLeft"
              label="Kembali"
              color="whiteDark"
              rounded-full
              small
            />
            <BaseButton
              :icon="mdiPrinter"
              label="Cetak / PDF"
              color="info"
              rounded-full
              small
              @click="handlePrint"
            />
          </div>
        </SectionTitleLineWithButton>
      </div>

      <!-- Notifikasi Flash Message -->
      <div class="print:hidden">
        <NotificationBar
          v-if="$page.props.flash?.message"
          color="success"
          :icon="mdiAlertCircle"
          class="shadow-md rounded-2xl"
        >
          {{ $page.props.flash.message }}
        </NotificationBar>
        <NotificationBar
          v-if="$page.props.flash?.error"
          color="danger"
          :icon="mdiAlertCircle"
          class="shadow-md rounded-2xl"
        >
          {{ $page.props.flash.error }}
        </NotificationBar>
      </div>

      <!-- ================= 2. KARTU HEADER CETAK FORMAL (KHUSUS MODE PRINT) ================= -->
      <div class="hidden print:block mb-3">
        <div class="text-center pb-2 border-b-2 border-black">
          <h2 class="text-sm font-black uppercase tracking-wider text-black">
            DAFTAR PESERTA PKL DAN PEMBIMBING PKL {{ new Date().getFullYear() }}
          </h2>
          <h3 class="text-xs font-black uppercase tracking-widest text-black mt-0.5">
            SMKS MUHAMMADIYAH 2 GENTENG
          </h3>
          <p class="text-[10px] font-bold text-black tracking-wider mt-0.5">
            TAHUN PELAJARAN 2026/2027
          </p>
        </div>

        <!-- Tabel Metadata Resmi & Kompak (Hemat Ruang Vertikal) -->
        <div class="mt-2 text-[10px] text-black border border-black p-2 bg-slate-50">
          <div class="grid grid-cols-2 gap-x-6 gap-y-1">
            <div class="flex items-start">
              <span class="w-32 font-bold uppercase text-[9px] shrink-0">Guru Pembimbing</span>
              <span class="font-bold">: {{ pembimbing.nama_guru }} <span v-if="pembimbing.jabatan" class="font-normal">({{ pembimbing.jabatan }})</span></span>
            </div>
            <div class="flex items-start">
              <span class="w-28 font-bold uppercase text-[9px] shrink-0">Area Monitoring</span>
              <span class="font-black">: {{ stats.area_monitoring }}</span>
            </div>
            <div class="flex items-start">
              <span class="w-32 font-bold uppercase text-[9px] shrink-0">Jumlah Penugasan</span>
              <span class="font-bold">: <strong>{{ stats.bengkel_total }}</strong> Bengkel &bull; <strong>{{ stats.siswa_total }}</strong> Siswa</span>
            </div>
            <div class="flex items-start">
              <span class="w-28 font-bold uppercase text-[9px] shrink-0">Kontak / Dicetak</span>
              <span>: {{ pembimbing.no_hp && pembimbing.no_hp !== '—' ? pembimbing.no_hp : '—' }} (Per {{ stats.tanggal_update }})</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= 3. HEADER INFORMASI RESMI PEMBIMBING (WEB VIEW ONLY) ================= -->
      <div class="print:hidden bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-300 dark:border-slate-700 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
          <!-- Guru Pembimbing -->
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-xs">
              <BaseIcon :path="mdiHumanMaleBoard" size="22" />
            </div>
            <div>
              <span class="text-[10px] uppercase font-bold tracking-wider text-slate-500 dark:text-slate-400 block">Guru Pembimbing</span>
              <h1 class="text-base font-black text-slate-950 dark:text-white leading-tight">
                {{ pembimbing.nama_guru }}
              </h1>
              <div class="flex flex-wrap items-center gap-2 text-xs text-slate-600 dark:text-slate-300 pt-0.5 font-bold">
                <span v-if="pembimbing.jabatan" class="text-indigo-600 dark:text-indigo-400 font-semibold">
                  {{ pembimbing.jabatan }}
                </span>
                <span v-if="pembimbing.nip && pembimbing.nip !== '—'" class="text-slate-500">
                  &bull; NIP: {{ pembimbing.nip }}
                </span>
              </div>
            </div>
          </div>

          <!-- Quick Stats Pills (Kompak & Modern) -->
          <div class="flex flex-wrap items-center gap-2.5">
            <div class="px-3.5 py-1.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 border border-indigo-200 dark:border-indigo-800 text-xs flex items-center gap-2">
              <BaseIcon :path="mdiOfficeBuildingCog" size="16" class="text-indigo-600 dark:text-indigo-400 shrink-0" />
              <div>
                <span class="text-[9px] uppercase font-bold text-indigo-700 dark:text-indigo-300 block leading-none">Bengkel</span>
                <span class="font-black text-indigo-900 dark:text-white text-sm leading-tight">{{ stats.bengkel_total }}</span>
              </div>
            </div>
            <div class="px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-800 text-xs flex items-center gap-2">
              <BaseIcon :path="mdiAccountGroup" size="16" class="text-emerald-600 dark:text-emerald-400 shrink-0" />
              <div>
                <span class="text-[9px] uppercase font-bold text-emerald-700 dark:text-emerald-300 block leading-none">Siswa PKL</span>
                <span class="font-black text-emerald-900 dark:text-white text-sm leading-tight">{{ stats.siswa_total }}</span>
              </div>
            </div>
            <div class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs">
              <span class="text-[9px] uppercase font-bold text-slate-500 dark:text-slate-400 block leading-none">Area Monitoring</span>
              <span class="font-black text-slate-900 dark:text-white text-xs leading-tight block mt-0.5">{{ stats.area_monitoring }}</span>
            </div>
            <div class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 border border-slate-300 dark:border-slate-700 text-xs">
              <span class="text-[9px] uppercase font-bold text-slate-500 dark:text-slate-400 block leading-none">Kontak</span>
              <a
                v-if="pembimbing.no_hp && pembimbing.no_hp !== '—'"
                :href="getWhatsAppUrl(pembimbing.no_hp)"
                target="_blank"
                class="font-black text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-1 text-xs mt-0.5 leading-tight"
                title="Hubungi WhatsApp"
              >
                <BaseIcon :path="mdiWhatsapp" size="13" />
                {{ pembimbing.no_hp }}
              </a>
              <span v-else class="font-bold text-slate-500 text-xs block mt-0.5">—</span>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= 4. TABEL UTAMA DAFTAR PESERTA PKL (SESUAI FORMAT EXCEL) ================= -->
      <CardBox class="rounded-3xl border border-slate-300 dark:border-slate-700 shadow-sm overflow-hidden print:border-none print:shadow-none" has-table>
        
        <!-- Filter Pencarian Instan (Hanya 1 input search, sembunyikan saat print) -->
        <div class="p-4 sm:p-5 border-b border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50 dark:bg-slate-900/60 print:hidden">
          <div class="flex items-center gap-2">
            <span class="text-xs font-black uppercase tracking-wider text-slate-900 dark:text-white">
              Daftar Siswa Bimbingan
            </span>
            <span class="px-2.5 py-0.5 text-xs font-black rounded-full bg-indigo-100 text-indigo-900 dark:bg-indigo-900 dark:text-white">
              {{ filteredPeserta.length }} Siswa
            </span>
          </div>

          <div class="relative w-full sm:w-72">
            <input
              v-model="search"
              type="text"
              placeholder="Cari siswa, kelas, bengkel..."
              class="w-full pl-9 pr-4 py-2 text-xs rounded-xl bg-white dark:bg-slate-800 border border-slate-300 dark:border-slate-600 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 text-slate-950 dark:text-white font-bold placeholder-slate-400"
            />
            <BaseIcon
              :path="mdiMagnify"
              size="16"
              class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-500 dark:text-slate-400"
            />
          </div>
        </div>

        <!-- Tabel Lengkap -->
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-xs">
            <thead>
              <tr class="bg-slate-200 dark:bg-slate-800 border-b-2 border-slate-400 dark:border-slate-600 text-slate-950 dark:text-white font-black uppercase text-[10px] tracking-wider print:bg-slate-200 print:text-black print:border-b-2 print:border-black">
                <th class="py-3 px-3 w-10 text-center font-black print:border print:border-black print:p-1 print:w-7">No</th>
                <th
                  class="py-3 px-4 min-w-[200px] cursor-pointer select-none hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors font-black print:border print:border-black print:p-1 print:min-w-0"
                  title="Klik untuk mengubah urutan A-Z / Z-A"
                  @click="toggleSortNama"
                >
                  <div class="flex items-center gap-1.5">
                    <span>Nama Siswa</span>
                    <BaseIcon
                      :path="sortOrder === 'asc' ? mdiArrowUp : mdiArrowDown"
                      size="14"
                      class="text-indigo-700 dark:text-indigo-400 print:hidden shrink-0"
                    />
                  </div>
                </th>
                <th class="py-3 px-3 min-w-[90px] text-center font-black print:border print:border-black print:p-1 print:min-w-0 print:w-16">Kelas</th>
                <th class="py-3 px-3 min-w-[130px] font-black print:border print:border-black print:p-1 print:min-w-0">No HP Siswa</th>
                <th class="py-3 px-4 min-w-[180px] font-black print:border print:border-black print:p-1 print:min-w-0">Nama Bengkel</th>
                <th class="py-3 px-3 min-w-[130px] font-black print:border print:border-black print:p-1 print:min-w-0">Nama Pemilik</th>
                <th class="py-3 px-3 min-w-[130px] font-black print:border print:border-black print:p-1 print:min-w-0">No Kontak</th>
                <th class="py-3 px-4 min-w-[220px] font-black print:border print:border-black print:p-1 print:min-w-0">Alamat</th>
                <th class="py-3 px-3 w-16 text-center print:hidden font-black">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-700 text-slate-950 dark:text-white print:divide-slate-300">
              
              <tr
                v-for="(item, idx) in filteredPeserta"
                :key="item.id"
                class="hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors print:hover:bg-transparent"
                :class="item.pernah_pindah ? 'bg-amber-50/50 dark:bg-amber-950/20' : ''"
              >
                <!-- 1. No -->
                <td class="py-3.5 px-3 text-center font-black text-slate-950 dark:text-white print:text-black print:p-1">
                  {{ idx + 1 }}
                </td>

                <!-- 2. Nama Siswa + Tanda Pernah Pindah -->
                <td class="py-3.5 px-4 font-black text-slate-950 dark:text-white print:text-black print:p-1">
                  <div class="space-y-0.5">
                    <div class="flex flex-wrap items-center gap-1">
                      <span class="text-xs sm:text-sm font-black print:text-[9.5px] print:font-bold">
                        {{ item.nama_siswa }}
                      </span>
                      <!-- TANDA SISWA PERNAH PINDAH -->
                      <span
                        v-if="item.pernah_pindah"
                        class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-black bg-amber-400 text-amber-950 border border-amber-500 print:bg-transparent print:border-none print:text-[8px] print:p-0 print:font-bold"
                        :title="`Pernah pindah dari: ${item.bengkel_lama || 'Bengkel Sebelumnya'}`"
                      >
                        <BaseIcon :path="mdiSwapHorizontal" size="13" class="shrink-0 print:hidden" />
                        (Pindah)
                      </span>
                    </div>

                    <!-- Keterangan bengkel asal jika pernah pindah -->
                    <div
                      v-if="item.pernah_pindah && item.bengkel_lama"
                      class="text-[11px] font-bold text-amber-800 dark:text-amber-300 print:text-black print:text-[8px] flex items-center gap-1"
                    >
                      <span>(Asal: <strong>{{ item.bengkel_lama }}</strong>)</span>
                    </div>
                  </div>
                </td>

                <!-- 3. Kelas -->
                <td class="py-3.5 px-3 text-center print:p-1">
                  <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-black bg-indigo-100 dark:bg-indigo-900/80 text-indigo-950 dark:text-white border border-indigo-200 dark:border-indigo-800 print:border-none print:p-0 print:bg-transparent print:text-black print:text-[9px] print:font-bold">
                    {{ item.kelas }}
                  </span>
                </td>

                <!-- 4. No HP Siswa -->
                <td class="py-3.5 px-3 font-mono text-xs font-bold text-slate-950 dark:text-white print:text-black print:p-1 print:text-[9px]">
                  <a
                    v-if="item.no_hp_siswa"
                    :href="getWhatsAppUrl(item.no_hp_siswa)"
                    target="_blank"
                    class="text-emerald-700 dark:text-emerald-400 hover:underline inline-flex items-center gap-1 font-bold print:text-black"
                    title="Kirim Pesan WhatsApp"
                  >
                    <BaseIcon :path="mdiWhatsapp" size="14" class="print:hidden text-emerald-600 shrink-0" />
                    {{ item.no_hp_siswa }}
                  </a>
                  <span v-else class="text-slate-800 dark:text-slate-200 font-bold">—</span>
                </td>

                <!-- 5. Nama Bengkel (Murni Nama Bengkel Saja Tanpa Kode) -->
                <td class="py-3.5 px-4 font-black text-slate-950 dark:text-white print:text-black print:p-1 print:text-[9px] print:font-bold">
                  {{ item.nama_bengkel }}
                </td>

                <!-- 6. Nama Pemilik -->
                <td class="py-3.5 px-3 font-bold text-slate-950 dark:text-white print:text-black print:p-1 print:text-[9px]">
                  {{ item.nama_pemilik || '—' }}
                </td>

                <!-- 7. No Kontak Bengkel -->
                <td class="py-3.5 px-3 font-mono text-xs font-bold text-slate-950 dark:text-white print:text-black print:p-1 print:text-[9px]">
                  <a
                    v-if="item.no_hp_bengkel"
                    :href="getWhatsAppUrl(item.no_hp_bengkel)"
                    target="_blank"
                    class="text-indigo-700 dark:text-indigo-400 hover:underline inline-flex items-center gap-1 font-bold print:text-black"
                    title="Hubungi Kontak Bengkel"
                  >
                    <BaseIcon :path="mdiPhone" size="14" class="print:hidden text-indigo-600 shrink-0" />
                    {{ item.no_hp_bengkel }}
                  </a>
                  <span v-else class="text-slate-800 dark:text-slate-200 font-bold">—</span>
                </td>

                <!-- 8. Alamat Bengkel -->
                <td class="py-3.5 px-4 text-slate-950 dark:text-white print:text-black font-bold print:p-1 print:text-[8.5px] print:leading-tight">
                  <div class="flex items-start gap-1.5">
                    <a
                      v-if="item.google_maps_link"
                      :href="item.google_maps_link"
                      target="_blank"
                      class="text-rose-600 hover:text-rose-700 mt-0.5 shrink-0 print:hidden"
                      title="Buka Google Maps"
                    >
                      <BaseIcon :path="mdiMapMarker" size="15" />
                    </a>
                    <span>{{ item.alamat_lengkap }}</span>
                  </div>
                </td>

                <!-- 9. Aksi Lepas Penugasan Bengkel (Hanya di Web, bukan Print) -->
                <td class="py-3.5 px-3 text-center print:hidden">
                  <button
                    type="button"
                    class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-rose-600 hover:text-white hover:bg-rose-600 bg-rose-50 dark:bg-rose-950/40 dark:hover:bg-rose-600 transition-colors shadow-xs"
                    title="Lepas Penugasan Bengkel ini"
                    @click="confirmDetach({ id: item.bengkel_id, nama_bengkel: item.nama_bengkel })"
                  >
                    <BaseIcon :path="mdiTrashCan" size="16" />
                  </button>
                </td>

              </tr>

              <!-- State Kosong -->
              <tr v-if="filteredPeserta.length === 0">
                <td colspan="9" class="py-8 text-center text-slate-800 dark:text-slate-200 font-bold text-sm">
                  Tidak ada peserta PKL yang cocok dengan kata kunci pencarian.
                </td>
              </tr>

            </tbody>
          </table>
        </div>

        <!-- ================= 5. KETERANGAN FOOTER ================= -->
        <div class="p-4 border-t border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/80 print:bg-white print:border-t-2 print:border-black print:p-2">
          <div class="flex flex-wrap items-center justify-between gap-2 text-xs font-bold text-slate-700 dark:text-slate-300 print:text-black print:text-[9px]">
            <div class="flex items-center gap-4">
              <span>Total: <strong>{{ stats.bengkel_total }} Bengkel</strong> &bull; <strong>{{ stats.siswa_total }} Siswa PKL</strong></span>
              <span>Area: <strong>{{ stats.area_monitoring }}</strong></span>
            </div>
            <div class="text-slate-500 dark:text-slate-400 print:text-black">
              Dicetak pada: {{ stats.tanggal_update }}
            </div>
          </div>
        </div>

      </CardBox>

      <!-- ================= 5. MODAL KONFIRMASI LEPAS BENGKEL ================= -->
      <div
        v-if="isDetachModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
        @click.self="isDetachModalOpen = false"
      >
        <div class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl p-6 shadow-2xl border border-slate-200 dark:border-slate-700 space-y-4">
          <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/50 dark:text-rose-400 flex items-center justify-center">
                <BaseIcon :path="mdiAlertCircle" size="20" />
              </div>
              <h3 class="font-extrabold text-sm text-slate-900 dark:text-white">
                Lepas Penugasan Bengkel
              </h3>
            </div>
            <button
              type="button"
              class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
              @click="isDetachModalOpen = false"
            >
              <BaseIcon :path="mdiClose" size="20" />
            </button>
          </div>

          <p class="text-xs text-slate-800 dark:text-slate-200 font-bold leading-relaxed">
            Apakah Anda yakin ingin melepas bengkel <strong class="text-slate-950 dark:text-white">{{ selectedBengkelToDetach?.nama_bengkel }}</strong> dari guru pembimbing <strong class="text-indigo-600 dark:text-indigo-400">{{ pembimbing.nama_guru }}</strong>?
          </p>
          <div class="p-3 rounded-2xl bg-amber-100 dark:bg-amber-950/40 border border-amber-300 dark:border-amber-700 text-xs font-bold text-amber-950 dark:text-amber-200">
            *Semua siswa yang ditempatkan di bengkel ini status pembimbingnya akan menjadi kosong/belum memiliki pembimbing.
          </div>

          <div class="flex items-center justify-end gap-2 pt-2">
            <button
              type="button"
              class="px-4 py-2 text-xs font-bold rounded-xl text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors"
              @click="isDetachModalOpen = false"
            >
              Batal
            </button>
            <button
              type="button"
              class="px-4 py-2 text-xs font-bold rounded-xl bg-rose-600 text-white hover:bg-rose-700 shadow-md shadow-rose-600/20 transition-colors"
              @click="executeDetach"
            >
              Ya, Lepas Bengkel
            </button>
          </div>
        </div>
      </div>

    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>
@page {
  size: 330mm 215mm; /* Ukuran Kertas F4 / Folio Landscape (330 x 215 mm) */
  margin: 4mm 6mm 4mm 6mm;
}

@media print {
  /* Hilangkan background dan pastikan teks hitam pekat */
  :global(body) {
    background: #ffffff !important;
    color: #000000 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
    margin: 0 !important;
    padding: 0 !important;
  }

  /* Sembunyikan elemen navigasi, sidebar, header, footer admin */
  :global(header),
  :global(aside),
  :global(footer),
  :global(nav),
  :global(.aside-menu) {
    display: none !important;
  }

  /* Reset padding layout induk agar full-width tanpa margin samping */
  :global(.min-h-screen),
  :global([class*="lg:pl-"]) {
    padding-left: 0 !important;
    padding-top: 0 !important;
    margin: 0 !important;
    width: 100% !important;
    min-height: auto !important;
    background: transparent !important;
  }

  :global(section),
  :global(main) {
    padding: 0 !important;
    margin: 0 !important;
    max-width: 100% !important;
  }

  .print\:hidden {
    display: none !important;
  }

  /* Format Tabel Cetak Super Efisien agar Pas 1 Lembar */
  table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 8.5px !important;
    line-height: 1.15 !important;
  }

  thead {
    display: table-header-group !important;
  }

  thead tr {
    background-color: #f1f5f9 !important;
    border-top: 1.5px solid #000000 !important;
    border-bottom: 1.5px solid #000000 !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  th, td {
    border: 1px solid #000000 !important;
    padding: 2px 3.5px !important;
    color: #000000 !important;
    vertical-align: middle !important;
  }

  th {
    font-weight: 800 !important;
    font-size: 8.5px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.02em !important;
  }

  tbody tr {
    page-break-inside: avoid !important;
    break-inside: avoid !important;
  }

  tbody tr:nth-child(even) {
    background-color: #f8fafc !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>
