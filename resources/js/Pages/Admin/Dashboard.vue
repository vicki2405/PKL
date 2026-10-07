<script setup>
import { computed, ref } from 'vue'
import { useMainStore } from '@admin/stores/main'
import { useDarkModeStore } from '@admin/stores/darkMode.js'
import { useLayoutStore } from '@admin/stores/layout.js'
import { usePage, Link } from '@inertiajs/vue3'
import BaseIcon from '@admin/components/BaseIcon.vue'
import NumberDynamic from '@admin/components/NumberDynamic.vue'
import {
  mdiDomain,
  mdiGoogleClassroom,
  mdiAccountSchool,
  mdiHumanMaleBoard,
  mdiOfficeBuildingCog,
  mdiAccountGroup,
  mdiChartBar,
  mdiChartArc,
  mdiBellAlertOutline,
  mdiArrowRight,
  mdiCheckCircle,
  mdiClockOutline,
  mdiCloseCircleOutline,
  mdiProgressClock,
  mdiShieldCheck,
  mdiTrendingUp,
  mdiAccountCheck,
  mdiMapMarkerCheck,
  mdiBriefcaseCheck,
  mdiAccountSwitch
} from '@mdi/js'
import PieChart from '@admin/components/Charts/PieChart.vue'
import BarChart from '@admin/components/Charts/BarChart.vue'
import SectionMain from '@admin/components/SectionMain.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'

const props = defineProps({
  totalJurusan: {
    type: Number,
    default: 0,
  },
  totalKelas: {
    type: Number,
    default: 0,
  },
  totalSiswa: {
    type: Number,
    default: 0,
  },
  totalPembimbing: {
    type: Number,
    default: 0,
  },
  totalBengkel: {
    type: Number,
    default: 0,
  },
  totalMutasi: {
    type: Number,
    default: 0,
  },
  mutasiPending: {
    type: Number,
    default: 0,
  },
  mutasiProcessing: {
    type: Number,
    default: 0,
  },
  mutasiApproved: {
    type: Number,
    default: 0,
  },
  mutasiRejected: {
    type: Number,
    default: 0,
  },
  siswaSudahPKL: {
    type: Number,
    default: 0,
  },
  siswaBelumPKL: {
    type: Number,
    default: 0,
  },
  chartLabels: {
    type: Array,
    default: () => [],
  },
  chartDataset: {
    type: Array,
    default: () => [],
  },
  mutasiChartLabels: {
    type: Array,
    default: () => [],
  },
  mutasiChartDataset: {
    type: Array,
    default: () => [],
  },
  bengkelChartLabels: {
    type: Array,
    default: () => [],
  },
  bengkelChartDataset: {
    type: Array,
    default: () => [],
  },
})

// Hitung persentase penempatan PKL
const persentasePenempatan = computed(() => {
  if (!props.totalSiswa || props.totalSiswa <= 0) return 0
  return Math.round((props.siswaSudahPKL / props.totalSiswa) * 100)
})

// Rasio rata-rata siswa per pembimbing
const rasioSiswaPembimbing = computed(() => {
  if (!props.totalPembimbing || props.totalPembimbing <= 0) return 0
  return (props.totalSiswa / props.totalPembimbing).toFixed(1)
})

// Rasio rata-rata siswa per bengkel
const rasioSiswaBengkel = computed(() => {
  if (!props.totalBengkel || props.totalBengkel <= 0) return 0
  return (props.totalSiswa / props.totalBengkel).toFixed(1)
})

// Data Chart Penempatan Siswa (Donut)
const chartData = computed(() => {
  return {
    labels: props.chartLabels,
    datasets: [
      {
        data: props.chartDataset,
        hoverOffset: 6,
      },
    ],
  }
})

// Data Chart Mutasi Siswa (Donut)
const mutasiChartData = computed(() => {
  return {
    labels: props.mutasiChartLabels,
    datasets: [
      {
        data: props.mutasiChartDataset,
        hoverOffset: 6,
      },
    ],
  }
})

// Data Chart Bengkel per Jurusan (Bar Chart)
const bengkelBarData = computed(() => {
  return {
    labels: props.bengkelChartLabels,
    datasets: [
      {
        label: 'Jumlah Bengkel Mitra',
        data: props.bengkelChartDataset,
      },
    ],
  }
})

const mainStore = useMainStore()
const darkModeStore = useDarkModeStore()
const page = usePage()

const userName = computed(() => page.props.auth?.user?.name || mainStore.userName)
</script>

<template>
  <LayoutAuthenticated :is-dashboard="true">
    <SectionMain class="space-y-6">

      <!-- ================= 1. MODERN HERO BANNER ================= -->
      <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 sm:p-8 text-white shadow-xl border border-indigo-500/20">
        <!-- Glow Orbs Background -->
        <div class="absolute -top-24 -right-24 w-80 h-80 rounded-full bg-indigo-500/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-24 -left-24 w-80 h-80 rounded-full bg-purple-500/20 blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
          <div class="flex items-center gap-5">
            <div class="p-3 bg-white/10 dark:bg-white/5 rounded-2xl border border-white/20 backdrop-blur-md shadow-lg shrink-0">
              <img src="/logo pkl.png" alt="Logo PKL" class="w-16 h-16 sm:w-20 sm:h-20 object-contain drop-shadow-md" />
            </div>
            <div class="space-y-1">
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/25 border border-indigo-400/30 text-indigo-200 text-xs font-bold tracking-wide">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Selamat Datang Kembali, {{ userName }}</span>
              </div>
              <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                Dashboard Control Panel
              </h1>
              <p class="text-slate-300 text-xs sm:text-sm max-w-xl leading-relaxed">
                Pusat monitoring dan tata kelola program Praktik Kerja Lapangan (PKL) sekolah secara terintegrasi dan real-time.
              </p>
              <div class="pt-2">
                <Link
                  :href="route('admin.monitoring_penempatan.index')"
                  class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white/15 hover:bg-white/25 text-white border border-white/20 text-xs font-bold transition-all shadow-xs cursor-pointer"
                >
                  <BaseIcon :path="mdiAccountSwitch" size="16" />
                  <span>Monitoring & Riwayat Pindah Siswa</span>
                  <BaseIcon :path="mdiArrowRight" size="14" />
                </Link>
              </div>
            </div>
          </div>

          <!-- Quick Metrics Badges inside Hero -->
          <div class="flex flex-wrap sm:flex-nowrap items-center gap-3">
            <div class="flex-1 sm:flex-initial px-4 py-3 rounded-2xl bg-white/10 dark:bg-slate-800/60 border border-white/15 backdrop-blur-md">
              <span class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">Kesiapan PKL</span>
              <div class="flex items-baseline gap-1 mt-0.5">
                <span class="text-2xl font-black text-emerald-400">{{ persentasePenempatan }}%</span>
                <span class="text-xs text-slate-400">terploting</span>
              </div>
            </div>

            <div class="flex-1 sm:flex-initial px-4 py-3 rounded-2xl bg-white/10 dark:bg-slate-800/60 border border-white/15 backdrop-blur-md">
              <span class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">Siswa Aktif</span>
              <div class="flex items-baseline gap-1 mt-0.5">
                <span class="text-2xl font-black text-indigo-300">{{ totalSiswa }}</span>
                <span class="text-xs text-slate-400">anak</span>
              </div>
            </div>

            <div class="flex-1 sm:flex-initial px-4 py-3 rounded-2xl bg-white/10 dark:bg-slate-800/60 border border-white/15 backdrop-blur-md">
              <span class="block text-[11px] font-bold text-slate-300 uppercase tracking-wider">Bengkel Mitra</span>
              <div class="flex items-baseline gap-1 mt-0.5">
                <span class="text-2xl font-black text-purple-300">{{ totalBengkel }}</span>
                <span class="text-xs text-slate-400">DUDI</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= 2. MUTASI ACTION ALERT BANNER (JIKA ADA PENDING) ================= -->
      <div
        v-if="mutasiPending > 0"
        class="p-4 sm:p-5 rounded-2xl bg-gradient-to-r from-amber-500/10 via-amber-500/5 to-transparent dark:from-amber-950/40 dark:via-amber-900/10 dark:to-transparent border border-amber-300 dark:border-amber-700/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-sm"
      >
        <div class="flex items-start gap-3.5">
          <div class="p-2.5 bg-amber-500 text-white rounded-xl shadow-md shrink-0 mt-0.5">
            <BaseIcon :path="mdiBellAlertOutline" size="22" />
          </div>
          <div class="space-y-0.5">
            <div class="flex items-center gap-2">
              <span class="px-2 py-0.5 bg-amber-500 text-white font-extrabold text-[10px] rounded uppercase tracking-wider">
                Tindakan Diperlukan
              </span>
              <h4 class="font-extrabold text-sm sm:text-base text-amber-950 dark:text-amber-200">
                Ada {{ mutasiPending }} Pengajuan Mutasi Siswa Menunggu Verifikasi
              </h4>
            </div>
            <p class="text-xs text-amber-800 dark:text-amber-300/90 leading-relaxed">
              Terdapat permohonan perpindahan tempat bengkel PKL yang baru diajukan siswa dan belum direview oleh admin.
            </p>
          </div>
        </div>

        <Link
          :href="route('admin.mutasi_pkl.index')"
          class="w-full sm:w-auto px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-bold transition-all shadow-md hover:shadow-lg flex items-center justify-center gap-2 shrink-0 cursor-pointer"
        >
          <span>Tinjau Daftar Mutasi</span>
          <BaseIcon :path="mdiArrowRight" size="16" />
        </Link>
      </div>

      <!-- ================= 3. KPI METRIC CARDS (6 CARDS WITH ELEVATED DESIGN) ================= -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        
        <!-- 1. Data Jurusan -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              Jurusan
            </span>
            <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/60 dark:border-emerald-800/60 text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
              <BaseIcon :path="mdiDomain" size="20" />
            </div>
          </div>
          <div class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
            <NumberDynamic :value="totalJurusan" />
          </div>
          <span class="text-[11px] text-slate-400 font-medium block mt-0.5">Kompetensi Keahlian</span>
        </div>

        <!-- 2. Data Kelas -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              Kelas
            </span>
            <div class="p-2.5 rounded-xl bg-sky-50 dark:bg-sky-950/60 border border-sky-200/60 dark:border-sky-800/60 text-sky-600 dark:text-sky-400 group-hover:scale-110 transition-transform">
              <BaseIcon :path="mdiGoogleClassroom" size="20" />
            </div>
          </div>
          <div class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
            <NumberDynamic :value="totalKelas" />
          </div>
          <span class="text-[11px] text-slate-400 font-medium block mt-0.5">Rombongan Belajar</span>
        </div>

        <!-- 3. Total Data Siswa -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              Total Siswa
            </span>
            <div class="p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200/60 dark:border-rose-800/60 text-rose-600 dark:text-rose-400 group-hover:scale-110 transition-transform">
              <BaseIcon :path="mdiAccountSchool" size="20" />
            </div>
          </div>
          <div class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
            <NumberDynamic :value="totalSiswa" />
          </div>
          <span class="text-[11px] text-slate-400 font-medium block mt-0.5">Peserta PKL Aktif</span>
        </div>

        <!-- 4. Data Pembimbing -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              Pembimbing
            </span>
            <div class="p-2.5 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200/60 dark:border-amber-800/60 text-amber-600 dark:text-amber-400 group-hover:scale-110 transition-transform">
              <BaseIcon :path="mdiHumanMaleBoard" size="20" />
            </div>
          </div>
          <div class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
            <NumberDynamic :value="totalPembimbing" />
          </div>
          <span class="text-[11px] text-slate-400 font-medium block mt-0.5">Guru Pembimbing</span>
        </div>

        <!-- 5. Data Bengkel -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              Bengkel Mitra
            </span>
            <div class="p-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/60 border border-purple-200/60 dark:border-purple-800/60 text-purple-600 dark:text-purple-400 group-hover:scale-110 transition-transform">
              <BaseIcon :path="mdiOfficeBuildingCog" size="20" />
            </div>
          </div>
          <div class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
            <NumberDynamic :value="totalBengkel" />
          </div>
          <span class="text-[11px] text-slate-400 font-medium block mt-0.5">DUDI Terdaftar</span>
        </div>

        <!-- 6. Data Mutasi -->
        <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-800 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 group">
          <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
              Total Mutasi
            </span>
            <div class="p-2.5 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200/60 dark:border-indigo-800/60 text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform">
              <BaseIcon :path="mdiAccountGroup" size="20" />
            </div>
          </div>
          <div class="text-2xl font-black text-slate-800 dark:text-slate-100 tracking-tight">
            <NumberDynamic :value="totalMutasi" />
          </div>
          <span class="text-[11px] text-slate-400 font-medium block mt-0.5">Riwayat Pengajuan</span>
        </div>

      </div>

      <!-- ================= 4. SECTION GRAFIK UTAMA: BENGKEL BAR CHART & PENEMPATAN DONUT ================= -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        
        <!-- KIRI: Modern Bar Chart Bengkel per Jurusan (7 Kolom) -->
        <div class="lg:col-span-7 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm flex flex-col justify-between">
          <div>
            <!-- Header Chart -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800">
              <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                  <BaseIcon :path="mdiChartBar" size="22" />
                </div>
                <div>
                  <h3 class="text-base font-extrabold text-slate-800 dark:text-slate-100">
                    Statistik Bengkel Mitra per Kompetensi
                  </h3>
                  <p class="text-xs text-slate-400">
                    Sebaran tempat praktik kerja industri menurut program keahlian
                  </p>
                </div>
              </div>
              <div class="px-3 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs">
                Total: {{ totalBengkel }} Bengkel
              </div>
            </div>

            <!-- Canvas Bar Chart -->
            <div class="h-72 sm:h-80 pt-4">
              <BarChart :data="bengkelBarData" />
            </div>
          </div>

          <!-- Bottom Legend Pills for Bengkel -->
          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
            <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50">
              <span class="w-3 h-3 rounded-full bg-emerald-500 shrink-0"></span>
              <span class="font-medium text-slate-600 dark:text-slate-300 truncate">TKR</span>
            </div>
            <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50">
              <span class="w-3 h-3 rounded-full bg-sky-500 shrink-0"></span>
              <span class="font-medium text-slate-600 dark:text-slate-300 truncate">TSM</span>
            </div>
            <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50">
              <span class="w-3 h-3 rounded-full bg-indigo-500 shrink-0"></span>
              <span class="font-medium text-slate-600 dark:text-slate-300 truncate">TEI</span>
            </div>
            <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50">
              <span class="w-3 h-3 rounded-full bg-purple-500 shrink-0"></span>
              <span class="font-medium text-slate-600 dark:text-slate-300 truncate">Animasi</span>
            </div>
            <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50">
              <span class="w-3 h-3 rounded-full bg-orange-500 shrink-0"></span>
              <span class="font-medium text-slate-600 dark:text-slate-300 truncate">Kuliner</span>
            </div>
            <div class="flex items-center gap-2 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/50">
              <span class="w-3 h-3 rounded-full bg-slate-500 shrink-0"></span>
              <span class="font-medium text-slate-600 dark:text-slate-300 truncate">Lainnya</span>
            </div>
          </div>
        </div>

        <!-- KANAN: Modern Donut Penempatan Siswa PKL (5 Kolom) -->
        <div class="lg:col-span-5 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm flex flex-col justify-between">
          <div>
            <!-- Header Chart -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
              <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400">
                  <BaseIcon :path="mdiChartArc" size="22" />
                </div>
                <div>
                  <h3 class="text-base font-extrabold text-slate-800 dark:text-slate-100">
                    Kesiapan Penempatan Siswa
                  </h3>
                  <p class="text-xs text-slate-400">
                    Persentase siswa yang telah mendapatkan lokasi bengkel
                  </p>
                </div>
              </div>
            </div>

            <!-- Canvas Donut Chart -->
            <div class="h-64 sm:h-72 pt-3 flex items-center justify-center">
              <PieChart :data="chartData" variant="placement" subtext="Siswa PKL" />
            </div>
          </div>

          <!-- Progress Bar & Breakdown Detail -->
          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 space-y-3">
            <div>
              <div class="flex justify-between text-xs font-bold mb-1.5">
                <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                  <BaseIcon :path="mdiCheckCircle" size="14" />
                  Sudah Terploting ({{ persentasePenempatan }}%)
                </span>
                <span class="text-rose-500 dark:text-rose-400">
                  Belum ({{ 100 - persentasePenempatan }}%)
                </span>
              </div>
              <div class="w-full h-3 bg-rose-100 dark:bg-rose-950/40 rounded-full overflow-hidden flex">
                <div
                  class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full transition-all duration-500"
                  :style="{ width: `${persentasePenempatan}%` }"
                ></div>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-xs">
              <div class="p-2.5 rounded-2xl bg-emerald-50/70 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-800/40">
                <span class="block text-[11px] font-bold text-emerald-700 dark:text-emerald-300">Sudah Ditempatkan</span>
                <span class="text-lg font-black text-emerald-900 dark:text-emerald-100">{{ siswaSudahPKL }} Siswa</span>
              </div>
              <div class="p-2.5 rounded-2xl bg-rose-50/70 dark:bg-rose-950/30 border border-rose-200/50 dark:border-rose-800/40">
                <span class="block text-[11px] font-bold text-rose-700 dark:text-rose-300">Belum Ada Bengkel</span>
                <span class="text-lg font-black text-rose-900 dark:text-rose-100">{{ siswaBelumPKL }} Siswa</span>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- ================= 5. SECTION GRAFIK MUTASI & QUICK INSIGHTS ================= -->
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        
        <!-- KIRI: Status Mutasi PKL Donut Chart (6 Kolom) -->
        <div class="lg:col-span-6 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm flex flex-col justify-between">
          <div>
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
              <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-purple-50 dark:bg-purple-950/60 text-purple-600 dark:text-purple-400">
                  <BaseIcon :path="mdiProgressClock" size="22" />
                </div>
                <div>
                  <h3 class="text-base font-extrabold text-slate-800 dark:text-slate-100">
                    Status Pengajuan Mutasi PKL
                  </h3>
                  <p class="text-xs text-slate-400">
                    Monitoring dinamika perpindahan lokasi bengkel siswa
                  </p>
                </div>
              </div>
              <Link
                :href="route('admin.mutasi_pkl.index')"
                class="text-xs font-bold text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 flex items-center gap-1"
              >
                <span>Kelola</span>
                <BaseIcon :path="mdiArrowRight" size="14" />
              </Link>
            </div>

            <!-- Canvas Mutasi Donut -->
            <div class="h-64 sm:h-72 pt-3 flex items-center justify-center">
              <PieChart :data="mutasiChartData" variant="mutasi" subtext="Mutasi PKL" />
            </div>
          </div>

          <!-- Status Pill Breakdown -->
          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
            <div class="p-2.5 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/40 text-center">
              <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Menunggu</span>
              <span class="text-lg font-black text-amber-800 dark:text-amber-200">{{ mutasiPending }}</span>
            </div>
            <div class="p-2.5 rounded-2xl bg-sky-50 dark:bg-sky-950/30 border border-sky-200 dark:border-sky-800/40 text-center">
              <span class="block text-[10px] font-bold uppercase tracking-wider text-sky-600 dark:text-sky-400">Diproses</span>
              <span class="text-lg font-black text-sky-800 dark:text-sky-200">{{ mutasiProcessing }}</span>
            </div>
            <div class="p-2.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/40 text-center">
              <span class="block text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Disetujui</span>
              <span class="text-lg font-black text-emerald-800 dark:text-emerald-200">{{ mutasiApproved }}</span>
            </div>
            <div class="p-2.5 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800/40 text-center">
              <span class="block text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Ditolak</span>
              <span class="text-lg font-black text-rose-800 dark:text-rose-200">{{ mutasiRejected }}</span>
            </div>
          </div>
        </div>

        <!-- KANAN: Operational Insights & Ratio Cards (6 Kolom) -->
        <div class="lg:col-span-6 bg-white dark:bg-slate-900 rounded-3xl border border-slate-200/80 dark:border-slate-800 p-6 shadow-sm flex flex-col justify-between space-y-4">
          <div>
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-slate-800">
              <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-2xl bg-teal-50 dark:bg-teal-950/60 text-teal-600 dark:text-teal-400">
                  <BaseIcon :path="mdiTrendingUp" size="22" />
                </div>
                <div>
                  <h3 class="text-base font-extrabold text-slate-800 dark:text-slate-100">
                    Rasio & Efisiensi Distribusi PKL
                  </h3>
                  <p class="text-xs text-slate-400">
                    Indikator beban bimbingan guru dan kapasitas bengkel mitra
                  </p>
                </div>
              </div>
            </div>

            <!-- Insight List -->
            <div class="space-y-3.5 pt-4">
              <!-- Item 1: Rasio Siswa per Pembimbing -->
              <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                  <div class="p-2 rounded-xl bg-amber-100 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">
                    <BaseIcon :path="mdiAccountCheck" size="18" />
                  </div>
                  <div>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block">
                      Rasio Siswa per Guru Pembimbing
                    </span>
                    <span class="text-[11px] text-slate-400">
                      Rata-rata beban bimbingan guru pendamping
                    </span>
                  </div>
                </div>
                <div class="text-right shrink-0">
                  <span class="text-lg font-black text-slate-800 dark:text-slate-100">
                    1 : {{ rasioSiswaPembimbing }}
                  </span>
                  <span class="text-[10px] text-slate-400 block font-medium">siswa / guru</span>
                </div>
              </div>

              <!-- Item 2: Rasio Siswa per Bengkel -->
              <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                  <div class="p-2 rounded-xl bg-purple-100 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400">
                    <BaseIcon :path="mdiMapMarkerCheck" size="18" />
                  </div>
                  <div>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block">
                      Rasio Siswa per Bengkel Mitra
                    </span>
                    <span class="text-[11px] text-slate-400">
                      Rata-rata kapasitas tampung per DUDI
                    </span>
                  </div>
                </div>
                <div class="text-right shrink-0">
                  <span class="text-lg font-black text-slate-800 dark:text-slate-100">
                    {{ rasioSiswaBengkel }}
                  </span>
                  <span class="text-[10px] text-slate-400 block font-medium">siswa / bengkel</span>
                </div>
              </div>

              <!-- Item 3: Total DUDI Mitra -->
              <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200/60 dark:border-slate-700/60 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                  <div class="p-2 rounded-xl bg-indigo-100 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400">
                    <BaseIcon :path="mdiBriefcaseCheck" size="18" />
                  </div>
                  <div>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-100 block">
                      Kemitraan DUDI Terverifikasi
                    </span>
                    <span class="text-[11px] text-slate-400">
                      Total jaringan industri resmi sekolah
                    </span>
                  </div>
                </div>
                <div class="text-right shrink-0">
                  <span class="text-lg font-black text-indigo-600 dark:text-indigo-400">
                    {{ totalBengkel }}
                  </span>
                  <span class="text-[10px] text-slate-400 block font-medium">mitra aktif</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Navigation Link Bar -->
          <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs">
            <span class="text-slate-400 font-medium">Pintasan Cepat:</span>
            <div class="flex items-center gap-2">
              <Link
                :href="route('admin.mutasi_pkl.index')"
                class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold transition-colors"
              >
                Mutasi Siswa
              </Link>
              <Link
                :href="route('admin.input_data_pkl.index')"
                class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold transition-colors"
              >
                Penempatan PKL
              </Link>
              <Link
                :href="route('admin.input_data_pkl.input_pembimbing')"
                class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 hover:text-indigo-600 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold transition-colors"
              >
                Input Pembimbing
              </Link>
              <Link
                :href="route('admin.monitoring_penempatan.index')"
                class="px-3 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 font-bold transition-colors flex items-center gap-1"
              >
                <BaseIcon :path="mdiAccountSwitch" size="14" />
                <span>Riwayat Pindah Siswa</span>
              </Link>
            </div>
          </div>
        </div>

      </div>

    </SectionMain>
  </LayoutAuthenticated>
</template>
