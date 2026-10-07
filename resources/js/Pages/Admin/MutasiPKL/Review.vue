<script setup>
import { computed, ref, watch, onMounted, onBeforeUnmount } from 'vue'
import { Head, useForm, Link } from '@inertiajs/vue3'
import {
  mdiTransfer,
  mdiArrowLeft,
  mdiAccountSchool,
  mdiDomain,
  mdiHumanMaleBoard,
  mdiAlertCircle,
  mdiCheckBold,
  mdiCheckCircle,
  mdiMapMarker,
  mdiCalendarClock,
  mdiNoteTextOutline,
  mdiOfficeBuildingCog,
  mdiShieldCheck,
  mdiPhone,
  mdiMagnify,
  mdiClose,
  mdiArrowRightBold,
  mdiRefresh,
  mdiPlus
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
import NotificationBar from '@admin/components/NotificationBar.vue'

const props = defineProps({
  mutasi: {
    type: Object,
    required: true,
  },
  bengkelOptions: {
    type: Array,
    default: () => [],
  },
  pembimbingOptions: {
    type: Array,
    default: () => [],
  },
})

// Deteksi awal apakah ada bengkel terdaftar yang cocok dengan nama usulan siswa
const matchExistingBengkel = props.bengkelOptions.find((b) => {
  const existingName = (b.nama_bengkel || '').trim().toLowerCase()
  const newName = (props.mutasi.nama_bengkel_baru || '').trim().toLowerCase()
  return newName && (existingName === newName || existingName.includes(newName) || newName.includes(existingName))
})

const initialMode = props.mutasi.bengkel_tujuan_id ? 'existing' : (matchExistingBengkel ? 'existing' : 'new')
const initialBengkelTujuanId = props.mutasi.bengkel_tujuan_id || (matchExistingBengkel ? matchExistingBengkel.id : '')

// Cek apakah bengkel tujuan awal sudah ada pembimbingnya
const initialTargetBengkel = props.bengkelOptions.find((b) => Number(b.id) === Number(initialBengkelTujuanId))
const initialPembimbingTujuanId = initialTargetBengkel?.assigned_pembimbing_id
  ? initialTargetBengkel.assigned_pembimbing_id
  : '' // JANGAN fallback ke pembimbingOptions[0]. Biarkan KOSONG jika belum ada pembimbing!

const form = useForm({
  bengkel_mode: initialMode,
  bengkel_tujuan_id: initialBengkelTujuanId,
  pembimbing_tujuan_id: initialPembimbingTujuanId,
  pembimbing_id: props.mutasi.pembimbing_id || '',
  nama_bengkel_baru: props.mutasi.nama_bengkel_baru || '',
  nama_pemilik_bengkel_baru: props.mutasi.nama_pemilik_bengkel_baru || '',
  dusun: props.mutasi.dusun || '',
  desa: props.mutasi.desa || '',
  kecamatan: props.mutasi.kecamatan || '',
  deskripsi: props.mutasi.deskripsi || '',
  google_maps_link_baru: props.mutasi.google_maps_link_baru || '',
  admin_note: props.mutasi.admin_note || '',
})

// Searchable bengkel state
const searchContainerRef = ref(null)
const bengkelSearch = ref('')
const isSearchDropdownOpen = ref(false)

const handleClickOutside = (event) => {
  if (searchContainerRef.value && !searchContainerRef.value.contains(event.target)) {
    isSearchDropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

const filteredBengkelList = computed(() => {
  const q = bengkelSearch.value.trim().toLowerCase()
  if (!q) return props.bengkelOptions.slice(0, 40)
  return props.bengkelOptions.filter((b) => {
    const name = (b.nama_bengkel || '').toLowerCase()
    const code = (b.kode_bengkel || '').toLowerCase()
    const kec = (b.kecamatan || '').toLowerCase()
    const desa = (b.desa || '').toLowerCase()
    return name.includes(q) || code.includes(q) || kec.includes(q) || desa.includes(q)
  })
})

const selectedBengkelData = computed(() => {
  if (!form.bengkel_tujuan_id) return null
  return props.bengkelOptions.find((b) => Number(b.id) === Number(form.bengkel_tujuan_id)) || null
})

const selectBengkel = (b) => {
  form.bengkel_tujuan_id = b.id
  if (b.assigned_pembimbing_id) {
    form.pembimbing_tujuan_id = b.assigned_pembimbing_id
  } else {
    form.pembimbing_tujuan_id = ''
  }
  isSearchDropdownOpen.value = false
  bengkelSearch.value = ''
}

const clearSelectedBengkel = () => {
  form.bengkel_tujuan_id = ''
  form.pembimbing_tujuan_id = ''
  bengkelSearch.value = ''
  isSearchDropdownOpen.value = true
}

// Smart suggestion bengkel terdaftar
const suggestedExistingBengkel = computed(() => {
  if (form.bengkel_mode === 'existing' && form.bengkel_tujuan_id) return null
  return matchExistingBengkel || null
})

// Pembimbing resmi bengkel tujuan yang terdaftar
const selectedTargetBengkelAssimbingPembimbing = computed(() => {
  if (form.bengkel_mode !== 'existing' || !form.bengkel_tujuan_id) return null
  const target = props.bengkelOptions.find((b) => Number(b.id) === Number(form.bengkel_tujuan_id))
  if (!target || !target.assigned_pembimbing_id) return null
  return props.pembimbingOptions.find((p) => Number(p.id) === Number(target.assigned_pembimbing_id)) || null
})

const selectedTargetBengkelAssignedPembimbing = selectedTargetBengkelAssimbingPembimbing

const pembimbingHelpText = computed(() => {
  if (form.bengkel_mode === 'existing') {
    if (!form.bengkel_tujuan_id) {
      return 'Silakan pilih bengkel tujuan terlebih dahulu pada pencarian di atas untuk memeriksa dan memuat data guru pembimbing resminya.'
    }
    if (selectedTargetBengkelAssignedPembimbing.value) {
      return `Bengkel tujuan ini sudah memiliki guru pembimbing resmi (${selectedTargetBengkelAssignedPembimbing.value.nama_guru}). Sistem menguncinya otomatis agar 1 bengkel tidak memiliki 2 pembimbing.`
    }
    return 'Bengkel tujuan yang Anda pilih belum memiliki guru pembimbing. Form dikosongkan agar Anda tahu bengkel ini belum ada pembimbingnya (bisa ditentukan nanti di menu Input Pembimbing).'
  }
  return 'Bengkel usulan baru belum memiliki guru pembimbing resmi. Anda dapat memilih pembimbing sekarang atau mengosongkannya agar ditentukan nanti di menu Input Pembimbing.'
})

// Watcher pergantian bengkel tujuan: Otomatis isi jika sudah ada pembimbing, KOSONGKAN jika belum ada
watch(
  () => form.bengkel_tujuan_id,
  (newBengkelId) => {
    if (form.bengkel_mode === 'existing' && newBengkelId) {
      const target = props.bengkelOptions.find((b) => Number(b.id) === Number(newBengkelId))
      if (target?.assigned_pembimbing_id) {
        form.pembimbing_tujuan_id = target.assigned_pembimbing_id
      } else {
        form.pembimbing_tujuan_id = ''
      }
    } else {
      form.pembimbing_tujuan_id = ''
    }
  }
)

// Watcher mode switch
watch(
  () => form.bengkel_mode,
  (newMode) => {
    if (newMode === 'new') {
      form.bengkel_tujuan_id = ''
      form.pembimbing_tujuan_id = ''
      isSearchDropdownOpen.value = false
    } else if (newMode === 'existing') {
      if (!form.bengkel_tujuan_id && matchExistingBengkel) {
        form.bengkel_tujuan_id = matchExistingBengkel.id
      }
    }
  }
)

const applySuggestion = (bengkel) => {
  form.bengkel_mode = 'existing'
  form.bengkel_tujuan_id = bengkel.id
  if (bengkel.assigned_pembimbing_id) {
    form.pembimbing_tujuan_id = bengkel.assigned_pembimbing_id
  } else {
    form.pembimbing_tujuan_id = ''
  }
  isSearchDropdownOpen.value = false
}

const submitApprove = () => {
  form.put(route('admin.mutasi_pkl.approve', props.mutasi.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Review & Persetujuan Mutasi PKL" />

    <SectionMain>
      <!-- Top Title & Navigation Bar -->
      <SectionTitleLineWithButton
        :icon="mdiTransfer"
        title="Review & Persetujuan Mutasi PKL"
        main
      >
        <BaseButton
          :to="route('admin.mutasi_pkl.index')"
          :icon="mdiArrowLeft"
          label="Kembali ke Daftar Mutasi"
          color="whiteDark"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>

      <!-- Flash Notification Alerts -->
      <NotificationBar
        v-if="$page.props.flash?.message"
        color="info"
        :icon="mdiShieldCheck"
        class="mb-6"
      >
        {{ $page.props.flash.message }}
      </NotificationBar>

      <NotificationBar
        v-if="$page.props.flash?.error"
        color="danger"
        :icon="mdiAlertCircle"
        class="mb-6"
      >
        {{ $page.props.flash.error }}
      </NotificationBar>

      <!-- ================= 1. HEADER PROFIL SISWA & METADATA (MODERN STRIP) ================= -->
      <div class="mb-6 bg-white dark:bg-slate-800 rounded-3xl p-5 sm:p-6 border border-slate-200/80 dark:border-slate-700 shadow-sm relative overflow-hidden">
        <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-indigo-500/5 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-5 relative z-10">
          <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-900/60 flex items-center justify-center text-indigo-600 dark:text-indigo-400 shrink-0 shadow-inner">
              <BaseIcon :path="mdiAccountSchool" size="30" />
            </div>
            <div>
              <div class="flex flex-wrap items-center gap-2 mb-1">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-950/60 px-2.5 py-0.5 rounded-lg border border-indigo-100 dark:border-indigo-900/40">
                  Data Siswa Pemohon
                </span>
                <span
                  class="px-2.5 py-0.5 rounded-lg text-xs font-bold uppercase tracking-wider border shadow-xs"
                  :class="{
                    'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800': props.mutasi.status === 'pending',
                    'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/40 dark:text-blue-300 dark:border-blue-800': props.mutasi.status === 'processing',
                    'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 dark:border-emerald-800': props.mutasi.status === 'approved'
                  }"
                >
                  Status: {{ props.mutasi.status }}
                </span>
              </div>
              <h2 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-slate-100">
                {{ props.mutasi.siswa?.nama }}
              </h2>
              <p class="text-xs text-slate-500 dark:text-slate-400 font-mono mt-0.5">
                NISN: {{ props.mutasi.siswa?.nisn || '-' }} &bull; Kelas: <strong class="text-slate-700 dark:text-slate-200 font-sans">{{ props.mutasi.siswa?.kelas?.nama_kelas || '-' }}</strong> ({{ props.mutasi.siswa?.kelas?.jurusan?.nama_jurusan || '-' }})
              </p>
            </div>
          </div>

          <!-- Quick Info Cards -->
          <div class="flex flex-wrap items-center gap-3 text-xs pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100 dark:border-slate-700/60">
            <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200/60 dark:border-slate-700">
              <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Wali Kelas</span>
              <span class="font-bold text-slate-800 dark:text-slate-200">{{ props.mutasi.siswa?.kelas?.wali_kelas || '-' }}</span>
              <a
                v-if="props.mutasi.siswa?.kelas?.no_wa_wali"
                :href="'https://wa.me/' + props.mutasi.siswa.kelas.no_wa_wali.replace(/\D/g, '')"
                target="_blank"
                class="inline-flex items-center gap-1 ml-2 text-emerald-600 dark:text-emerald-400 font-bold hover:underline"
              >
                <BaseIcon :path="mdiPhone" size="13" /> WA
              </a>
            </div>

            <div class="p-3 bg-slate-50 dark:bg-slate-900/60 rounded-2xl border border-slate-200/60 dark:border-slate-700">
              <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Waktu Pengajuan</span>
              <span class="font-bold text-slate-800 dark:text-slate-200 flex items-center gap-1">
                <BaseIcon :path="mdiCalendarClock" size="14" class="text-slate-400" />
                {{ new Date(props.mutasi.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= 2. KARTU PERBANDINGAN VISUAL (LOKASI ASAL VS USULAN TUJUAN) ================= -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch mb-6">
        
        <!-- Sisi Kiri: Bengkel Asal (Lama) -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-slate-700/60">
              <div class="flex items-center gap-2">
                <BaseIcon :path="mdiDomain" size="20" class="text-slate-500" />
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                  Bengkel PKL Saat Ini (Asal)
                </h4>
              </div>
              <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                Lokasi Lama
              </span>
            </div>

            <div class="space-y-3 text-xs">
              <div>
                <span class="text-[11px] text-slate-400 block">Nama Bengkel:</span>
                <span class="font-extrabold text-base text-slate-800 dark:text-slate-100 block">
                  {{ props.mutasi.bengkel_asal?.nama_bengkel || '-' }}
                </span>
                <span class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 block flex items-center gap-1">
                  <BaseIcon :path="mdiMapMarker" size="14" class="text-slate-400 shrink-0" />
                  {{ [props.mutasi.bengkel_asal?.dusun, props.mutasi.bengkel_asal?.desa, props.mutasi.bengkel_asal?.kecamatan].filter(Boolean).join(', ') || 'Lokasi tidak tersedia' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Pembimbing Asal Info Box -->
          <div class="mt-4 p-3 bg-blue-50/70 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/50 rounded-2xl">
            <div class="flex items-center gap-2.5">
              <BaseIcon :path="mdiHumanMaleBoard" size="20" class="text-blue-600 dark:text-blue-400" />
              <div>
                <span class="text-[10px] uppercase font-bold tracking-wider text-blue-500 block">Guru Pembimbing Asal</span>
                <span class="font-extrabold text-xs text-blue-950 dark:text-blue-100">
                  {{ props.mutasi.pembimbing?.nama_guru || '-' }}
                </span>
              </div>
            </div>
            <p class="text-[10px] text-blue-600 dark:text-blue-300 mt-2 italic leading-relaxed">
              *Tercatat resmi sebagai Pembimbing Asal untuk tanda tangan Surat P-1.
            </p>
          </div>
        </div>

        <!-- Tengah: Panah Transisi Mutasi (Desktop Pill) -->
        <div class="hidden lg:flex lg:col-span-2 flex-col items-center justify-center p-2 text-center">
          <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-600/20 mb-2">
            <BaseIcon :path="mdiArrowRightBold" size="24" />
          </div>
          <span class="text-[10px] font-black uppercase tracking-widest text-indigo-600 dark:text-indigo-400">
            Mutasi PKL
          </span>
        </div>

        <!-- Sisi Kanan: Usulan Bengkel Baru dari Siswa -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-800 rounded-3xl p-5 border border-slate-200/80 dark:border-slate-700 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-slate-700/60">
              <div class="flex items-center gap-2">
                <BaseIcon :path="mdiOfficeBuildingCog" size="20" class="text-amber-500" />
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                  Usulan Tempat Baru oleh Siswa
                </h4>
              </div>
              <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-amber-50 text-amber-700 border border-amber-200 dark:bg-amber-950/40 dark:text-amber-300 dark:border-amber-800">
                Pengajuan Siswa
              </span>
            </div>

            <div class="space-y-3 text-xs">
              <div>
                <span class="text-[11px] text-slate-400 block">Nama Usulan Tempat:</span>
                <span class="font-extrabold text-base text-indigo-600 dark:text-indigo-400 block">
                  {{ props.mutasi.nama_bengkel_baru || props.mutasi.bengkel_tujuan?.nama_bengkel || '-' }}
                </span>
                <span v-if="props.mutasi.nama_pemilik_bengkel_baru" class="text-xs text-slate-600 dark:text-slate-300 mt-0.5 block">
                  Pemilik / PJ: <strong class="text-slate-800 dark:text-slate-100">{{ props.mutasi.nama_pemilik_bengkel_baru }}</strong>
                </span>
              </div>

              <!-- Alamat Usulan -->
              <div class="grid grid-cols-3 gap-2 p-2.5 bg-slate-50 dark:bg-slate-900/60 rounded-xl border border-slate-200/60 dark:border-slate-700 text-center">
                <div>
                  <span class="text-[9px] uppercase tracking-wider text-slate-400 block">Dusun</span>
                  <span class="font-bold text-[11px] text-slate-700 dark:text-slate-300 truncate block">
                    {{ props.mutasi.dusun || '-' }}
                  </span>
                </div>
                <div>
                  <span class="text-[9px] uppercase tracking-wider text-slate-400 block">Desa</span>
                  <span class="font-bold text-[11px] text-slate-700 dark:text-slate-300 truncate block">
                    {{ props.mutasi.desa || '-' }}
                  </span>
                </div>
                <div>
                  <span class="text-[9px] uppercase tracking-wider text-slate-400 block">Kecamatan</span>
                  <span class="font-bold text-[11px] text-slate-700 dark:text-slate-300 truncate block">
                    {{ props.mutasi.kecamatan || '-' }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <div class="mt-4 flex flex-col gap-2">
            <a
              v-if="props.mutasi.google_maps_link_baru"
              :href="props.mutasi.google_maps_link_baru"
              target="_blank"
              class="inline-flex items-center justify-center gap-1.5 px-3 py-1.5 text-xs font-bold rounded-xl text-blue-700 bg-blue-50 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300 transition-colors border border-blue-200 dark:border-blue-800"
            >
              <BaseIcon :path="mdiMapMarker" size="15" />
              Buka Titik Lokasi di Google Maps
            </a>

            <!-- Alasan Siswa Box -->
            <div class="p-3 bg-amber-50/60 dark:bg-amber-950/20 border border-amber-200/60 dark:border-amber-900/40 rounded-2xl">
              <span class="text-[10px] font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider block mb-1">Alasan Siswa:</span>
              <p class="text-xs text-slate-700 dark:text-slate-300 italic leading-relaxed line-clamp-2">
                "{{ props.mutasi.alasan }}"
              </p>
            </div>
          </div>
        </div>

      </div>

      <!-- ================= 3. FORM PENETAPAN & PERSETUJUAN OLEH ADMIN (UTAMA) ================= -->
      <CardBox is-form @submit.prevent="submitApprove" class="shadow-sm border border-slate-200/80 dark:border-slate-700">
        
        <div class="pb-4 mb-5 border-b border-slate-100 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div>
            <h3 class="text-base sm:text-lg font-black text-slate-800 dark:text-slate-100 flex items-center gap-2">
              <BaseIcon :path="mdiShieldCheck" size="24" class="text-emerald-500" />
              Penetapan Bengkel Tujuan & Pembimbing Baru
            </h3>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
              Pilih apakah mutasi ini menggunakan bengkel yang sudah ada di database atau menambahkan bengkel baru.
            </p>
          </div>
        </div>

        <!-- Smart Suggestion Alert (Jika Terdeteksi Bengkel Mirip di Database) -->
        <div
          v-if="suggestedExistingBengkel"
          class="p-4 mb-6 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs"
        >
          <div class="flex items-start gap-3 text-xs text-amber-900 dark:text-amber-200">
            <BaseIcon :path="mdiAlertCircle" size="22" class="text-amber-500 shrink-0 mt-0.5" />
            <div>
              <span class="font-extrabold text-sm block">Terdeteksi Bengkel yang Mirip di Database!</span>
              <span class="text-xs text-amber-800 dark:text-amber-300 block mt-0.5">
                "<strong>{{ suggestedExistingBengkel.nama_bengkel }}</strong>" (Kode: {{ suggestedExistingBengkel.kode_bengkel || 'BKG' }} - Kec. {{ suggestedExistingBengkel.kecamatan || '-' }})
              </span>
              <span class="text-[11px] text-amber-700/80 dark:text-amber-400/80 block mt-0.5">
                Klik tombol di samping untuk langsung memilih bengkel ini tanpa perlu mengetik ulang.
              </span>
            </div>
          </div>
          <button
            type="button"
            @click="applySuggestion(suggestedExistingBengkel)"
            class="w-full sm:w-auto px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shrink-0 transition-colors shadow-sm cursor-pointer"
          >
            Gunakan Bengkel Ini
          </button>
        </div>

        <!-- Segmented Mode Switcher (Tab Elegan) -->
        <div class="mb-6">
          <span class="text-xs font-bold text-slate-600 dark:text-slate-300 block mb-2">Penentuan Bengkel Tujuan:</span>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-1.5 bg-slate-100 dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700">
            <button
              type="button"
              @click="form.bengkel_mode = 'existing'"
              class="py-3 px-4 text-xs font-extrabold rounded-xl transition-all cursor-pointer flex items-center justify-center gap-2"
              :class="form.bengkel_mode === 'existing' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm border border-slate-200/60 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-800'"
            >
              <BaseIcon :path="mdiDomain" size="18" />
              Gunakan Bengkel yang Sudah Ada
            </button>
            <button
              type="button"
              @click="form.bengkel_mode = 'new'"
              class="py-3 px-4 text-xs font-extrabold rounded-xl transition-all cursor-pointer flex items-center justify-center gap-2"
              :class="form.bengkel_mode === 'new' ? 'bg-white dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 shadow-sm border border-slate-200/60 dark:border-slate-700' : 'text-slate-600 dark:text-slate-400 hover:text-slate-800'"
            >
              <BaseIcon :path="mdiOfficeBuildingCog" size="18" />
              Tambah sebagai Bengkel Baru
            </button>
          </div>
        </div>

        <!-- ================= TAB 1: GUNAKAN BENGKEL YANG SUDAH ADA (SEARCHABLE COMBOBOX) ================= -->
        <div v-if="form.bengkel_mode === 'existing'" class="space-y-4 mb-6">
          
          <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
            Pilih dari Daftar Bengkel yang Sudah Ada di Database:
          </label>

          <!-- Tampilan Bengkel Terpilih (Jika Sudah Memilih) -->
          <div
            v-if="selectedBengkelData"
            class="p-4 bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 rounded-2xl flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-xs"
          >
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="px-2 py-0.5 bg-indigo-600 text-white rounded text-[10px] font-extrabold uppercase tracking-wider">
                  {{ selectedBengkelData.kode_bengkel || 'BKG' }}
                </span>
                <h4 class="font-extrabold text-base text-indigo-950 dark:text-indigo-100">
                  {{ selectedBengkelData.nama_bengkel }}
                </h4>
              </div>

              <p class="text-xs text-slate-600 dark:text-slate-300 flex items-center gap-1">
                <BaseIcon :path="mdiMapMarker" size="14" class="text-rose-500 shrink-0" />
                <span>Kec. {{ selectedBengkelData.kecamatan || '-' }}, Desa {{ selectedBengkelData.desa || selectedBengkelData.dusun || '-' }}</span>
                <span v-if="selectedBengkelData.nama_pemilik" class="text-slate-400 ml-1">&bull; Pemilik: {{ selectedBengkelData.nama_pemilik }}</span>
              </p>

              <!-- Status Pembimbing Tag -->
              <div class="pt-1 flex items-center gap-2">
                <span
                  v-if="selectedBengkelData.assigned_pembimbing_id"
                  class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-100/70 dark:bg-emerald-950/50 px-2.5 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800"
                >
                  <BaseIcon :path="mdiCheckCircle" size="13" />
                  Sudah Ada Guru Pembimbing Resmi
                </span>
                <span
                  v-else
                  class="inline-flex items-center gap-1 text-[11px] font-bold text-amber-700 dark:text-amber-300 bg-amber-100/70 dark:bg-amber-950/50 px-2.5 py-0.5 rounded-full border border-amber-200 dark:border-amber-800"
                >
                  <BaseIcon :path="mdiAlertCircle" size="13" />
                  Bengkel Ini Belum Ada Pembimbing
                </span>
              </div>
            </div>

            <!-- Tombol Ganti Bengkel -->
            <button
              type="button"
              @click="clearSelectedBengkel"
              class="w-full sm:w-auto px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 border border-slate-300 dark:border-slate-600 text-slate-700 dark:text-slate-200 rounded-xl text-xs font-bold transition-all shadow-xs flex items-center justify-center gap-1.5 cursor-pointer shrink-0"
            >
              <BaseIcon :path="mdiRefresh" size="15" />
              Ganti Bengkel
            </button>
          </div>

          <!-- Search Input Box & Dropdown (Jika Belum Memilih atau Ingin Ganti) -->
          <div v-else ref="searchContainerRef" class="relative">
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <BaseIcon :path="mdiMagnify" size="18" />
              </div>
              <input
                v-model="bengkelSearch"
                type="text"
                @focus="isSearchDropdownOpen = true"
                @keydown.esc="isSearchDropdownOpen = false"
                class="w-full pl-10 pr-10 py-3 text-sm rounded-2xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500 shadow-xs"
                placeholder="Ketik nama bengkel, kode, atau kecamatan untuk mencari..."
              />
              <button
                v-if="bengkelSearch"
                type="button"
                @click="bengkelSearch = ''"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
                title="Bersihkan teks"
              >
                <BaseIcon :path="mdiClose" size="18" />
              </button>
            </div>

            <!-- Dropdown Results List -->
            <div
              v-if="isSearchDropdownOpen"
              class="mt-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl max-h-72 overflow-y-auto z-50 divide-y divide-slate-100 dark:divide-slate-800"
            >
              <!-- Dropdown Header Bar with Close Button -->
              <div class="px-3.5 py-2 bg-slate-50 dark:bg-slate-800/80 border-b border-slate-200/80 dark:border-slate-700/80 flex items-center justify-between text-xs sticky top-0 z-10 backdrop-blur-xs">
                <span class="text-slate-500 dark:text-slate-400 font-medium text-[11px]">
                  Pilih bengkel dari daftar:
                </span>
                <button
                  type="button"
                  @click="isSearchDropdownOpen = false"
                  class="text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 font-bold text-[11px] flex items-center gap-1 cursor-pointer px-2 py-0.5 rounded hover:bg-slate-200/60 dark:hover:bg-slate-700 transition-colors"
                >
                  <BaseIcon :path="mdiClose" size="14" />
                  Tutup (Esc)
                </button>
              </div>

              <div
                v-for="b in filteredBengkelList"
                :key="b.id"
                @click="selectBengkel(b)"
                class="p-3.5 hover:bg-indigo-50/70 dark:hover:bg-slate-800/80 cursor-pointer transition-colors flex items-center justify-between gap-3 text-xs"
              >
                <div class="space-y-0.5">
                  <div class="flex items-center gap-2">
                    <span class="px-1.5 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 rounded text-[10px] font-mono font-bold">
                      {{ b.kode_bengkel || 'BKG' }}
                    </span>
                    <span class="font-bold text-slate-800 dark:text-slate-100 text-sm">
                      {{ b.nama_bengkel }}
                    </span>
                  </div>
                  <p class="text-slate-500 dark:text-slate-400 text-[11px]">
                    Kec. {{ b.kecamatan || '-' }} {{ b.desa ? `&bull; Desa ${b.desa}` : '' }} {{ b.nama_pemilik ? `&bull; Pemilik: ${b.nama_pemilik}` : '' }}
                  </p>
                </div>

                <div class="shrink-0 text-right">
                  <span
                    v-if="b.assigned_pembimbing_id"
                    class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
                  >
                    Ada Pembimbing
                  </span>
                  <span
                    v-else
                    class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-800"
                  >
                    Belum Ada Pembimbing
                  </span>
                </div>
              </div>

              <div v-if="filteredBengkelList.length === 0" class="p-6 text-center text-slate-400 text-xs space-y-2">
                <BaseIcon :path="mdiOfficeBuildingCog" size="32" class="mx-auto text-slate-300" />
                <p class="font-medium text-slate-600 dark:text-slate-400">Bengkel tidak ditemukan dengan kata kunci "{{ bengkelSearch }}"</p>
                <p class="text-[11px]">Jika tempat ini memang baru, silakan klik tab <strong>Tambah sebagai Bengkel Baru</strong> di atas.</p>
              </div>
            </div>
          </div>
        </div>

        <!-- ================= TAB 2: TAMBAH SEBAGAI BENGKEL BARU (TERTATA RAPI) ================= -->
        <div v-else class="space-y-4 mb-6">
          <div class="bg-slate-50/70 dark:bg-slate-800/40 p-5 rounded-3xl border border-slate-200/80 dark:border-slate-700/80 space-y-4">
            
            <div class="border-b border-slate-200/60 dark:border-slate-700/60 pb-3">
              <span class="text-sm font-extrabold text-slate-800 dark:text-slate-100 block">
                Data Usulan Bengkel Baru dari Siswa
              </span>
              <p class="text-xs text-slate-500 mt-0.5">
                Admin dapat memeriksa atau merapikan data bengkel baru yang diajukan siswa sebelum disimpan permanen ke database master bengkel.
              </p>
            </div>

            <!-- Group 1: Identitas Bengkel -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <FormField label="Nama Bengkel Baru" class="col-span-1 sm:col-span-2">
                <FormControl v-model="form.nama_bengkel_baru" placeholder="Contoh: Bengkel Maju Jaya Motor" />
              </FormField>
              
              <FormField label="Nama Pemilik / Penanggung Jawab" class="col-span-1 sm:col-span-2">
                <FormControl v-model="form.nama_pemilik_bengkel_baru" placeholder="Contoh: Bpk. H. Suparman" />
              </FormField>
            </div>

            <!-- Group 2: Alamat Bengkel -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2 border-t border-slate-200/40 dark:border-slate-700/40">
              <FormField label="Kecamatan">
                <FormControl v-model="form.kecamatan" placeholder="Kecamatan..." />
              </FormField>

              <FormField label="Desa / Kelurahan">
                <FormControl v-model="form.desa" placeholder="Desa..." />
              </FormField>

              <FormField label="Dusun / Kampung">
                <FormControl v-model="form.dusun" placeholder="Dusun / Jl..." />
              </FormField>

              <FormField label="Link Google Maps (URL Lengkap)" class="col-span-1 sm:col-span-3">
                <FormControl v-model="form.google_maps_link_baru" placeholder="https://maps.google.com/..." />
              </FormField>
            </div>
          </div>
        </div>

        <!-- ================= 4. FORM PEMBIMBING BARU & CATATAN ADMIN ================= -->
        <div class="pt-5 border-t border-slate-200 dark:border-slate-700 space-y-4">
          
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 items-start">
            
            <!-- Input Pembimbing Baru -->
            <div>
              <FormField
                label="Pembimbing Baru (Bengkel Tujuan)"
                :help="pembimbingHelpText"
              >
                <select
                  v-model="form.pembimbing_tujuan_id"
                  :disabled="!!selectedTargetBengkelAssignedPembimbing"
                  class="w-full px-3.5 py-2.5 text-sm rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 font-medium focus:ring-2 focus:ring-indigo-500 shadow-sm transition-opacity"
                  :class="{ 'opacity-70 cursor-not-allowed bg-slate-100 dark:bg-slate-800 text-slate-500': !!selectedTargetBengkelAssignedPembimbing }"
                >
                  <option value="">-- Belum Ada Pembimbing (Dikosongkan) --</option>
                  <option v-for="p in props.pembimbingOptions" :key="p.id" :value="p.id">
                    {{ p.nama_guru }}
                  </option>
                </select>
              </FormField>

              <!-- Status Banner Pembimbing -->
              <div
                v-if="form.bengkel_mode === 'existing' && !form.bengkel_tujuan_id"
                class="mt-2 p-2.5 bg-slate-50 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700 rounded-xl text-xs text-slate-600 dark:text-slate-300 flex items-center gap-2"
              >
                <BaseIcon :path="mdiAlertCircle" size="16" class="text-slate-400 shrink-0" />
                <span>
                  Silakan cari dan pilih bengkel tujuan di atas terlebih dahulu untuk memuat guru pembimbing resminya.
                </span>
              </div>
              <div
                v-else-if="selectedTargetBengkelAssignedPembimbing"
                class="mt-2 p-2.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-xs text-emerald-800 dark:text-emerald-200 flex items-center gap-2"
              >
                <BaseIcon :path="mdiShieldCheck" size="16" class="text-emerald-500 shrink-0" />
                <span>
                  Otomatis terisi pembimbing resmi bengkel: <strong>{{ selectedTargetBengkelAssignedPembimbing.nama_guru }}</strong>.
                </span>
              </div>
              <div
                v-else
                class="mt-2 p-2.5 bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/50 rounded-xl text-xs text-amber-800 dark:text-amber-300 flex items-start gap-2"
              >
                <BaseIcon :path="mdiAlertCircle" size="16" class="text-amber-500 shrink-0 mt-0.5" />
                <div>
                  <span class="font-bold block">Bengkel Tujuan Belum Memiliki Pembimbing</span>
                  <span class="text-[11px] block text-amber-700 dark:text-amber-400">
                    Kolom pembimbing sengaja dikosongkan. Siswa akan ditempatkan di bengkel ini dan otomatis masuk ke daftar tab <strong>"Bengkel Belum Ada Pembimbing"</strong> di menu <em>Input Pembimbing</em>.
                  </span>
                </div>
              </div>
            </div>

            <!-- Input Catatan Admin -->
            <div>
              <FormField label="Catatan Persetujuan Admin (Opsional)" help="Catatan ini dapat dibaca oleh siswa atau digunakan sebagai log riwayat mutasi">
                <FormControl
                  v-model="form.admin_note"
                  type="textarea"
                  class="min-h-[105px]"
                  placeholder="Berikan catatan tambahan mengenai persetujuan mutasi ini jika diperlukan..."
                />
              </FormField>
            </div>

          </div>
        </div>

        <!-- ================= 5. SUBMIT ACTION BAR ================= -->
        <div class="pt-6 mt-6 border-t border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row items-center justify-between gap-3">
          <BaseButton
            :to="route('admin.mutasi_pkl.index')"
            :icon="mdiArrowLeft"
            label="Kembali ke Daftar Mutasi"
            color="whiteDark"
            class="w-full sm:w-auto"
          />

          <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
            <BaseButton
              type="submit"
              color="success"
              :icon="mdiCheckBold"
              label="Setujui & Simpan Mutasi"
              :loading="form.processing"
              :disabled="form.processing"
              class="w-full sm:w-auto px-7 py-3 text-sm font-black shadow-md cursor-pointer"
            />
          </div>
        </div>

      </CardBox>

    </SectionMain>
  </LayoutAuthenticated>
</template>
