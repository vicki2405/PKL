<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import SiswaLayout from '@/Layouts/SiswaLayout.vue';
import {
  mdiTransfer,
  mdiDomain,
  mdiHumanMaleBoard,
  mdiCheck,
  mdiAccountSchool,
  mdiArrowLeft,
  mdiMapMarker,
  mdiCardAccountDetails,
  mdiOfficeBuildingMarker,
  mdiMagnify,
  mdiFilterVariant,
  mdiRefresh,
  mdiCheckCircle,
  mdiPlus
} from '@mdi/js'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'

const props = defineProps({
  siswa: {
    type: Object,
    default: null,
  },
  bengkelOptions: {
    type: Array,
    default: () => [],
  },
})

const form = useForm({
  bengkel_mode: 'existing',
  bengkel_tujuan_id: '',
  nama_bengkel_baru: '',
  nama_pemilik_bengkel_baru: '',
  dusun: '',
  desa: '',
  kecamatan: '',
  deskripsi: '',
  google_maps_link_baru: '',
  alasan: '',
})

// Search & Location Filter States
const searchQuery = ref('')
const selectedKecamatan = ref('')
const selectedDesaDusun = ref('')

// Extract unique Kecamatan options
const kecamatanOptions = computed(() => {
  const list = props.bengkelOptions
    .map((b) => trimString(b.kecamatan))
    .filter((k) => k !== '')
  return [...new Set(list)].sort()
})

// Extract unique Desa/Dusun options (filtered by selected Kecamatan if present)
const desaDusunOptions = computed(() => {
  let list = props.bengkelOptions
  if (selectedKecamatan.value) {
    list = list.filter((b) => trimString(b.kecamatan) === selectedKecamatan.value)
  }
  const result = []
  list.forEach((b) => {
    const desa = trimString(b.desa)
    const dusun = trimString(b.dusun)
    if (desa && !result.includes(desa)) result.push(desa)
    if (dusun && !result.includes(dusun)) result.push(dusun)
  })
  return result.sort()
})

// Helper to sanitize strings
function trimString(val) {
  return String(val || '').trim()
}

// Filtered Bengkel List based on Search & Location Filters
const filteredBengkels = computed(() => {
  return props.bengkelOptions.filter((b) => {
    // 1. Search Query Match
    if (searchQuery.value) {
      const q = searchQuery.value.toLowerCase()
      const namaMatch = String(b.nama_bengkel || '').toLowerCase().includes(q)
      const kodeMatch = String(b.kode_bengkel || '').toLowerCase().includes(q)
      if (!namaMatch && !kodeMatch) return false
    }

    // 2. Kecamatan Match
    if (selectedKecamatan.value) {
      if (trimString(b.kecamatan) !== selectedKecamatan.value) return false
    }

    // 3. Desa / Dusun Match
    if (selectedDesaDusun.value) {
      const target = selectedDesaDusun.value.toLowerCase()
      const desaMatch = String(b.desa || '').toLowerCase().includes(target)
      const dusunMatch = String(b.dusun || '').toLowerCase().includes(target)
      if (!desaMatch && !dusunMatch) return false
    }

    return true
  })
})

// Reset Filters Function
const resetFilters = () => {
  searchQuery.value = ''
  selectedKecamatan.value = ''
  selectedDesaDusun.value = ''
}

// Reset desa/dusun if kecamatan changes
watch(selectedKecamatan, () => {
  selectedDesaDusun.value = ''
})

// Find selected Bengkel object for summary
const selectedBengkelObj = computed(() => {
  return props.bengkelOptions.find((b) => String(b.id) === String(form.bengkel_tujuan_id)) || null
})

const submit = () => {
  form.post(route('siswa.mutasi_pkl.store'), {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <Head title="Ajukan Mutasi PKL" />

  <SiswaLayout>
    <div class="max-w-7xl mx-auto space-y-6 pb-12">
      <!-- Page Header Banner -->
      <div class="relative bg-gradient-to-r from-slate-900 via-slate-800 to-orange-950 rounded-3xl p-5 sm:p-8 overflow-hidden shadow-xl text-white border border-slate-800">
          <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-orange-500 opacity-15 rounded-full blur-3xl pointer-events-none"></div>
          <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-amber-500 opacity-15 rounded-full blur-3xl pointer-events-none"></div>

          <div class="relative z-10 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
              <div class="flex items-center gap-2 text-orange-400 mb-1.5 text-xs font-bold uppercase tracking-wider">
                <BaseIcon :path="mdiTransfer" size="16" />
                <span>Pengajuan Mutasi</span>
              </div>
              <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Form Perubahan Lokasi PKL</h1>
              <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">Isi data bengkel/dudi tujuan secara lengkap dan alasan kepindahan kamu.</p>
            </div>

            <Link
              :href="route('siswa.dashboard')"
              class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 border border-white/20 rounded-xl backdrop-blur-sm transition-all text-white font-bold text-xs active:scale-95"
            >
              <BaseIcon :path="mdiArrowLeft" size="16" />
              <span>Kembali</span>
            </Link>
          </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

        <!-- Left Column: Student Info Card -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden lg:sticky lg:top-8">
                <div class="bg-slate-50 px-5 py-4 border-b border-slate-100">
                    <h3 class="text-slate-800 font-bold text-base flex items-center gap-2">
                        <BaseIcon :path="mdiCardAccountDetails" size="20" class="text-orange-500"/>
                        Informasi Siswa saat Ini
                    </h3>
                </div>

                <div class="p-5 sm:p-6 space-y-5">
                    <div class="flex items-start gap-3.5">
                        <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl flex-shrink-0">
                            <BaseIcon :path="mdiAccountSchool" size="20" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Nama Lengkap</p>
                            <p class="font-bold text-slate-800 text-sm sm:text-base">{{ props.siswa?.nama || '-' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl flex-shrink-0">
                             <BaseIcon :path="mdiDomain" size="20" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Kelas & Jurusan</p>
                            <p class="font-bold text-slate-800 text-sm">{{ props.siswa?.kelas?.nama_kelas || '-' }}</p>
                            <p class="text-xs text-slate-500">{{ props.siswa?.kelas?.jurusan?.nama_jurusan || '-' }}</p>
                        </div>
                    </div>

                    <div class="h-px bg-slate-100"></div>

                    <div class="flex items-start gap-3.5">
                        <div class="p-2.5 bg-orange-50 text-orange-600 rounded-xl flex-shrink-0">
                             <BaseIcon :path="mdiOfficeBuildingMarker" size="20" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Bengkel Saat Ini</p>
                            <p class="font-bold text-slate-800 text-sm">{{ props.siswa?.current_bengkel?.nama_bengkel || 'Belum ada penempatan' }}</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl flex-shrink-0">
                             <BaseIcon :path="mdiHumanMaleBoard" size="20" />
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pembimbing</p>
                            <p class="font-bold text-slate-800 text-sm">{{ props.siswa?.current_pembimbing?.nama_guru || 'Belum ditentukan' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Form Inputs -->
        <div class="lg:col-span-2">
            <form @submit.prevent="submit" class="space-y-6">

                <!-- Card 1: Alasan Kepindahan -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-5 sm:p-8">
                        <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-4 flex items-center gap-3">
                            <span class="flex items-center justify-center w-7 h-7 rounded-xl bg-orange-100 text-orange-600 text-xs font-extrabold">1</span>
                            Alasan Pengajuan Mutasi
                        </h3>

                        <FormField label="Mengapa Anda ingin mengajukan pindah lokasi PKL?" help="Tuliskan alasan secara jelas dan lengkap.">
                          <FormControl
                            v-model="form.alasan"
                            type="textarea"
                            placeholder="Contoh: Lokasi bengkel saat ini terlalu jauh dari domisili, atau kendala sarana..."
                            class="min-h-[110px] text-sm"
                            required
                          />
                          <div v-if="form.errors.alasan" class="text-rose-600 text-xs mt-1 font-semibold">
                            {{ form.errors.alasan }}
                          </div>
                        </FormField>
                    </div>
                </div>

                <!-- Card 2: Data Bengkel Baru -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-5 sm:p-8">
                        <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-4 flex items-center gap-3">
                             <span class="flex items-center justify-center w-7 h-7 rounded-xl bg-orange-100 text-orange-600 text-xs font-extrabold">2</span>
                            Data Bengkel / DUDI Tujuan
                        </h3>

                        <!-- Pilihan Tipe Bengkel Tujuan (Kartu Deskriptif & Jelas) -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 mb-6">
                          <!-- Opsi 1: Bengkel Terdaftar Sekolah -->
                          <div
                            @click="form.bengkel_mode = 'existing'"
                            class="p-4 rounded-2xl border-2 transition-all cursor-pointer flex items-start gap-3.5 relative"
                            :class="form.bengkel_mode === 'existing'
                              ? 'border-indigo-600 bg-indigo-50/70 dark:bg-indigo-950/40 text-indigo-950 dark:text-indigo-200 shadow-sm ring-2 ring-indigo-500/10'
                              : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-slate-300'"
                          >
                            <div
                              class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                              :class="form.bengkel_mode === 'existing' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'"
                            >
                              <BaseIcon :path="mdiDomain" size="20" />
                            </div>
                            <div class="flex-1 min-w-0">
                              <div class="flex items-center justify-between">
                                <h4 class="font-extrabold text-sm text-slate-800 dark:text-slate-100">
                                  Pilih Bengkel Mitra Sekolah
                                </h4>
                                <span
                                  v-if="form.bengkel_mode === 'existing'"
                                  class="w-2.5 h-2.5 rounded-full bg-indigo-600"
                                ></span>
                              </div>
                              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                                Pilih dari daftar bengkel yang sudah bekerja sama resmi dengan sekolah.
                              </p>
                            </div>
                          </div>

                          <!-- Opsi 2: Usulkan Bengkel Baru -->
                          <div
                            @click="form.bengkel_mode = 'new'"
                            class="p-4 rounded-2xl border-2 transition-all cursor-pointer flex items-start gap-3.5 relative"
                            :class="form.bengkel_mode === 'new'
                              ? 'border-orange-500 bg-orange-50/70 dark:bg-orange-950/40 text-orange-950 dark:text-orange-200 shadow-sm ring-2 ring-orange-500/10'
                              : 'border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 hover:border-slate-300'"
                          >
                            <div
                              class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0"
                              :class="form.bengkel_mode === 'new' ? 'bg-orange-500 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-500'"
                            >
                              <BaseIcon :path="mdiPlus" size="20" />
                            </div>
                            <div class="flex-1 min-w-0">
                              <div class="flex items-center justify-between">
                                <h4 class="font-extrabold text-sm text-slate-800 dark:text-slate-100">
                                  Usulkan Tempat / Bengkel Baru
                                </h4>
                                <span
                                  v-if="form.bengkel_mode === 'new'"
                                  class="w-2.5 h-2.5 rounded-full bg-orange-500"
                                ></span>
                              </div>
                              <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 leading-relaxed">
                                Ajukan tempat baru jika lokasi yang kamu tuju belum ada di daftar sekolah.
                              </p>
                            </div>
                          </div>
                        </div>

                        <!-- Mode Existing -->
                        <div v-if="form.bengkel_mode === 'existing'" class="space-y-4 mb-4">
                          
                          <!-- Search & Filter Controls Card -->
                          <div class="p-4 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-800 space-y-3">
                            <div class="flex items-center justify-between gap-2">
                              <div class="flex items-center gap-2 text-slate-800 dark:text-slate-200 font-bold text-xs uppercase tracking-wider">
                                <BaseIcon :path="mdiFilterVariant" size="16" class="text-indigo-500" />
                                <span>Cari & Filter Lokasi Bengkel</span>
                              </div>

                              <button
                                v-if="searchQuery || selectedKecamatan || selectedDesaDusun"
                                type="button"
                                @click="resetFilters"
                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition-colors cursor-pointer"
                              >
                                <BaseIcon :path="mdiRefresh" size="14" />
                                <span>Reset Filter</span>
                              </button>
                            </div>

                            <!-- Search Input -->
                            <div class="relative">
                              <FormControl
                                v-model="searchQuery"
                                :icon="mdiMagnify"
                                placeholder="Cari nama bengkel atau kode..."
                                class="text-sm"
                              />
                            </div>

                            <!-- Filter Dropdowns Grid -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                              <!-- Filter Kecamatan -->
                              <div>
                                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                                  Filter Kecamatan
                                </label>
                                <select
                                  v-model="selectedKecamatan"
                                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500/30"
                                >
                                  <option value="">Semua Kecamatan ({{ kecamatanOptions.length }})</option>
                                  <option v-for="kec in kecamatanOptions" :key="kec" :value="kec">
                                    Kec. {{ kec }}
                                  </option>
                                </select>
                              </div>

                              <!-- Filter Desa / Dusun -->
                              <div>
                                <label class="block text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">
                                  Filter Desa / Dusun
                                </label>
                                <select
                                  v-model="selectedDesaDusun"
                                  class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-xs font-semibold focus:ring-2 focus:ring-indigo-500/30"
                                >
                                  <option value="">Semua Desa / Dusun ({{ desaDusunOptions.length }})</option>
                                  <option v-for="dd in desaDusunOptions" :key="dd" :value="dd">
                                    {{ dd }}
                                  </option>
                                </select>
                              </div>
                            </div>
                          </div>

                          <!-- Results Status Bar -->
                          <div class="flex items-center justify-between text-xs px-1">
                            <span class="text-slate-500 font-medium">
                              Menampilkan <strong class="text-slate-800 dark:text-slate-200 font-extrabold">{{ filteredBengkels.length }}</strong> dari total {{ props.bengkelOptions.length }} bengkel
                            </span>
                            <span v-if="selectedBengkelObj" class="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-1">
                              <BaseIcon :path="mdiCheckCircle" size="14" />
                              <span>Bengkel Terpilih</span>
                            </span>
                          </div>

                          <!-- Selected Bengkel Summary Banner (If Selected) -->
                          <div v-if="selectedBengkelObj" class="p-3.5 bg-indigo-50/80 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 rounded-2xl flex items-center justify-between gap-3">
                            <div class="space-y-0.5">
                              <p class="text-[10px] font-bold text-indigo-500 uppercase tracking-wider">Bengkel Tujuan Terpilih:</p>
                              <p class="font-extrabold text-indigo-900 dark:text-indigo-200 text-sm sm:text-base">
                                {{ selectedBengkelObj.nama_bengkel }}
                                <span class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold ml-1">({{ selectedBengkelObj.kode_bengkel || 'BKG' }})</span>
                              </p>
                              <p class="text-xs text-slate-600 dark:text-slate-300 flex items-center gap-1">
                                <BaseIcon :path="mdiMapMarker" size="14" class="text-rose-500" />
                                <span>Kec. {{ selectedBengkelObj.kecamatan || '-' }}, Desa {{ selectedBengkelObj.desa || selectedBengkelObj.dusun || '-' }}</span>
                              </p>
                            </div>
                          </div>

                          <!-- Interactive Cards List -->
                          <div v-if="filteredBengkels.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[380px] overflow-y-auto pr-1">
                            <div
                              v-for="b in filteredBengkels"
                              :key="b.id"
                              @click="form.bengkel_tujuan_id = b.id"
                              class="relative p-3.5 rounded-2xl border transition-all duration-200 cursor-pointer flex flex-col justify-between gap-2.5 active:scale-[0.99]"
                              :class="String(form.bengkel_tujuan_id) === String(b.id)
                                ? 'bg-indigo-50/90 dark:bg-indigo-950/50 border-indigo-500 ring-2 ring-indigo-500/20 shadow-sm'
                                : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-indigo-300 dark:hover:border-indigo-700'"
                            >
                              <div class="flex items-start justify-between gap-2">
                                <div>
                                  <h4 class="font-bold text-slate-900 dark:text-slate-100 text-sm leading-snug">
                                    {{ b.nama_bengkel }}
                                  </h4>
                                  <span class="inline-block px-2 py-0.5 mt-1 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold text-[10px] rounded-md">
                                    {{ b.kode_bengkel || 'BKG' }}
                                  </span>
                                </div>

                                <div
                                  class="w-5 h-5 rounded-full border flex items-center justify-center flex-shrink-0 transition-all"
                                  :class="String(form.bengkel_tujuan_id) === String(b.id)
                                    ? 'bg-indigo-600 border-indigo-600 text-white'
                                    : 'border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800'"
                                >
                                  <BaseIcon v-if="String(form.bengkel_tujuan_id) === String(b.id)" :path="mdiCheck" size="12" />
                                </div>
                              </div>

                              <div class="text-xs text-slate-500 dark:text-slate-400 flex items-center gap-1.5 pt-1 border-t border-slate-100 dark:border-slate-800/60">
                                <BaseIcon :path="mdiMapMarker" size="14" class="text-slate-400 flex-shrink-0" />
                                <span class="truncate">
                                  Kec. {{ b.kecamatan || '-' }} {{ b.desa || b.dusun ? `• ${b.desa || b.dusun}` : '' }}
                                </span>
                              </div>
                            </div>
                          </div>

                          <!-- Empty Search State -->
                          <div v-else class="p-8 text-center bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-dashed border-slate-200 dark:border-slate-700 space-y-3">
                            <BaseIcon :path="mdiOfficeBuildingMarker" size="36" class="text-slate-300 mx-auto" />
                            <div>
                              <p class="font-bold text-slate-700 dark:text-slate-300 text-sm">Tidak ada bengkel ditemukan</p>
                              <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau reset filter kecamatan/desa.</p>
                            </div>
                            <button
                              type="button"
                              @click="resetFilters"
                              class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 text-white text-xs font-bold rounded-xl hover:bg-indigo-700 transition-colors cursor-pointer"
                            >
                              <BaseIcon :path="mdiRefresh" size="16" />
                              <span>Reset Semua Filter</span>
                            </button>
                          </div>

                          <div v-if="form.errors.bengkel_tujuan_id" class="text-rose-600 text-xs mt-1 font-semibold">
                            {{ form.errors.bengkel_tujuan_id }}
                          </div>
                        </div>

                        <!-- Mode New -->
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6">
                            <FormField label="Nama Bengkel Baru" help="Nama bengkel atau instansi yang dituju">
                                <FormControl
                                  v-model="form.nama_bengkel_baru"
                                  :icon="mdiDomain"
                                  placeholder="Masukkan nama bengkel baru..."
                                  required
                                />
                                <div v-if="form.errors.nama_bengkel_baru" class="text-rose-600 text-xs mt-1 font-semibold">
                                  {{ form.errors.nama_bengkel_baru }}
                                </div>
                            </FormField>

                            <FormField label="Nama Pemilik Bengkel" help="Nama penanggung jawab bengkel (Opsional)">
                                <FormControl
                                  v-model="form.nama_pemilik_bengkel_baru"
                                  placeholder="Contoh: Bpk. Ahmad"
                                />
                                <div v-if="form.errors.nama_pemilik_bengkel_baru" class="text-rose-600 text-xs mt-1 font-semibold">
                                  {{ form.errors.nama_pemilik_bengkel_baru }}
                                </div>
                            </FormField>

                            <FormField label="Dusun" help="Dusun lokasi bengkel">
                                <FormControl
                                  v-model="form.dusun"
                                  placeholder="Contoh: Dusun Krajan"
                                  required
                                />
                                <div v-if="form.errors.dusun" class="text-rose-600 text-xs mt-1 font-semibold">
                                  {{ form.errors.dusun }}
                                </div>
                            </FormField>

                            <FormField label="Desa / Kelurahan" help="Desa lokasi bengkel">
                                <FormControl
                                  v-model="form.desa"
                                  placeholder="Contoh: Desa Sukamaju"
                                  required
                                />
                                <div v-if="form.errors.desa" class="text-rose-600 text-xs mt-1 font-semibold">
                                  {{ form.errors.desa }}
                                </div>
                            </FormField>

                            <FormField label="Kecamatan" help="Kecamatan lokasi bengkel">
                                <FormControl
                                  v-model="form.kecamatan"
                                  placeholder="Contoh: Kecamatan Genteng"
                                  required
                                />
                                <div v-if="form.errors.kecamatan" class="text-rose-600 text-xs mt-1 font-semibold">
                                  {{ form.errors.kecamatan }}
                                </div>
                            </FormField>

                            <FormField label="Deskripsi / Patokan Alamat" help="Detail patokan alamat tambahan (Opsional)">
                                <FormControl
                                  v-model="form.deskripsi"
                                  type="textarea"
                                  placeholder="Contoh: Depan Balai Desa, cat toko biru..."
                                />
                                <div v-if="form.errors.deskripsi" class="text-rose-600 text-xs mt-1 font-semibold">
                                  {{ form.errors.deskripsi }}
                                </div>
                            </FormField>

                            <FormField label="Link Google Maps" help="Share link lokasi dari Google Maps (Opsional)" class="md:col-span-2">
                                <FormControl
                                  v-model="form.google_maps_link_baru"
                                  :icon="mdiMapMarker"
                                  placeholder="https://maps.google.com/..."
                                />
                                <div v-if="form.errors.google_maps_link_baru" class="text-rose-600 text-xs mt-1 font-semibold">
                                  {{ form.errors.google_maps_link_baru }}
                                </div>
                            </FormField>
                        </div>
                    </div>

                    <!-- Sticky / Fullwidth Action Button -->
                    <div class="bg-slate-50/80 px-5 py-4 sm:px-8 flex items-center justify-end gap-3 border-t border-slate-100">
                         <Link :href="route('siswa.dashboard')" class="px-4 py-2.5 rounded-xl text-slate-600 font-bold text-xs hover:bg-slate-200 transition-colors">
                          Batal
                        </Link>

                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="flex-1 sm:flex-none flex items-center justify-center gap-2.5 px-7 py-3.5 bg-gradient-to-r from-orange-500 via-amber-500 to-orange-600 text-white font-extrabold text-sm rounded-2xl hover:shadow-orange-500/35 hover:-translate-y-0.5 active:scale-[0.98] transition-all duration-200 shadow-lg shadow-orange-500/25 cursor-pointer"
                            :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                        >
                            <BaseIcon :path="mdiCheck" size="20" />
                            <span>Kirim Pengajuan Mutasi</span>
                        </button>
                    </div>
                </div>

            </form>
        </div>

      </div>
    </div>
  </SiswaLayout>
</template>
