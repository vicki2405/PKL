<script setup>
import { Head, useForm, Link, usePage } from '@inertiajs/vue3'
import {
  mdiCalendarClock,
  mdiCalendar,
  mdiArrowLeft,
  mdiCheckCircle,
  mdiAlertCircle,
  mdiContentSave,
  mdiInformation,
  mdiPencilOutline,
  mdiCheck,
} from '@mdi/js'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import CardBox from '@admin/components/CardBox.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'
import { computed, ref, watch } from 'vue'

const props = defineProps({
  periodSetting: {
    type: Object,
    default: () => ({}),
  },
})

const page = usePage()
const flashMessage = computed(() => page.props.flash?.message || page.props.flash?.success)
const flashError = computed(() => page.props.flash?.error)

const formatDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return '-'
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(date)
}

const formatShortDate = (dateString) => {
  if (!dateString) return '-'
  const date = new Date(dateString)
  if (isNaN(date.getTime())) return '-'
  return new Intl.DateTimeFormat('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  }).format(date)
}

const monthOptions = [
  { value: 1, label: 'Januari' },
  { value: 2, label: 'Februari' },
  { value: 3, label: 'Maret' },
  { value: 4, label: 'April' },
  { value: 5, label: 'Mei' },
  { value: 6, label: 'Juni' },
  { value: 7, label: 'Juli' },
  { value: 8, label: 'Agustus' },
  { value: 9, label: 'September' },
  { value: 10, label: 'Oktober' },
  { value: 11, label: 'November' },
  { value: 12, label: 'Desember' },
]

const yearOptions = Array.from({ length: 2040 - 2024 + 1 }, (_, i) => 2024 + i)

const parsePeriodMonthYear = (p) => {
  let year = 2026
  let month = 1

  if (p.start_date) {
    const parts = p.start_date.split('-')
    if (parts.length >= 2) {
      year = parseInt(parts[0], 10) || 2026
      month = parseInt(parts[1], 10) || 1
    }
  } else if (p.month_name) {
    const mMatch = monthOptions.find((m) => p.month_name.toLowerCase().includes(m.label.toLowerCase()))
    if (mMatch) month = mMatch.value
    const yMatch = p.month_name.match(/\b(20\d\d)\b/)
    if (yMatch) year = parseInt(yMatch[1], 10)
  }

  const s1Start = p.session_1?.start_date || p.start_date || `${year}-${String(month).padStart(2, '0')}-01`
  const s1End = p.session_1?.end_date || `${year}-${String(month).padStart(2, '0')}-15`
  const s2Start = p.session_2?.start_date || `${year}-${String(month).padStart(2, '0')}-16`
  const s2End = p.session_2?.end_date || p.end_date || `${year}-${String(month).padStart(2, '0')}-31`

  return {
    ...p,
    selected_month: p.selected_month || month,
    selected_year: p.selected_year || year,
    target_monitoring: 2,
    session_1: {
      session: 1,
      label: p.session_1?.label || 'Monitoring 1 (Minggu 1-2)',
      start_date: s1Start,
      end_date: s1End,
      period_text: `${formatShortDate(s1Start)} - ${formatShortDate(s1End)}`,
    },
    session_2: {
      session: 2,
      label: p.session_2?.label || 'Monitoring 2 (Minggu 3-4)',
      start_date: s2Start,
      end_date: s2End,
      period_text: `${formatShortDate(s2Start)} - ${formatShortDate(s2End)}`,
    },
  }
}

const generatePeriodsClient = (monitoringStartDate, mode = 'calendar', endDateStr = null) => {
  if (!monitoringStartDate) return []
  const parts = monitoringStartDate.split('-')
  const startYear = parseInt(parts[0], 10)
  const startMonth = parseInt(parts[1], 10)
  const startDay = parseInt(parts[2] || '1', 10)

  let totalMonths = 6
  let endYear = null
  let endMonth = null
  let endDay = null

  if (endDateStr) {
    const endParts = endDateStr.split('-')
    if (endParts.length === 3) {
      endYear = parseInt(endParts[0], 10)
      endMonth = parseInt(endParts[1], 10)
      endDay = parseInt(endParts[2], 10)

      const diff = (endYear - startYear) * 12 + (endMonth - startMonth) + 1
      if (diff > 0 && diff <= 12) {
        totalMonths = diff
      }
    }
  }

  const periods = []

  for (let i = 0; i < totalMonths; i++) {
    if (mode === 'calendar') {
      const curDate = new Date(startYear, startMonth - 1 + i, 1)
      const year = curDate.getFullYear()
      const month = curDate.getMonth() + 1
      let lastDay = new Date(year, month, 0).getDate()

      // Jika bulan pertama dan startDay > 1, pakai startDay
      const sDay = (i === 0 && startDay > 1) ? startDay : 1
      const sStr = `${year}-${String(month).padStart(2, '0')}-${String(sDay).padStart(2, '0')}`

      // Jika bulan terakhir dan endDay ada, potong sesuai endDay
      if (i === totalMonths - 1 && endYear === year && endMonth === month && endDay) {
        lastDay = Math.min(lastDay, endDay)
      }
      const eStr = `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`

      const monthName = curDate.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })

      const s1Start = sStr
      const s1EndDay = Math.min(15, lastDay)
      const s1End = `${year}-${String(month).padStart(2, '0')}-${String(s1EndDay).padStart(2, '0')}`

      let s2Start = `${year}-${String(month).padStart(2, '0')}-16`
      let s2End = eStr
      if (lastDay <= 15) {
        s2Start = s1End
        s2End = s1End
      }

      periods.push({
        month: i + 1,
        label: `Bulan ${i + 1}`,
        month_name: monthName,
        selected_month: month,
        selected_year: year,
        start_date: sStr,
        end_date: eStr,
        period_text: `${formatDate(sStr)} - ${formatDate(eStr)}`,
        target_monitoring: 2,
        session_1: {
          session: 1,
          label: 'Monitoring 1 (Minggu 1-2)',
          start_date: s1Start,
          end_date: s1End,
          period_text: `${formatShortDate(s1Start)} - ${formatShortDate(s1End)}`,
        },
        session_2: {
          session: 2,
          label: 'Monitoring 2 (Minggu 3-4)',
          start_date: s2Start,
          end_date: s2End,
          period_text: `${formatShortDate(s2Start)} - ${formatShortDate(s2End)}`,
        },
      })
    } else {
      const dStart = new Date(startYear, startMonth - 1 + i, startDay)
      let dEnd = new Date(startYear, startMonth + i, startDay - 1)
      if (i === totalMonths - 1 && endYear && endMonth && endDay) {
        const specEnd = new Date(endYear, endMonth - 1, endDay)
        if (specEnd < dEnd) {
          dEnd = specEnd
        }
      }

      const sY = dStart.getFullYear()
      const sM = dStart.getMonth() + 1
      const sD = String(dStart.getDate()).padStart(2, '0')

      const eY = dEnd.getFullYear()
      const eM = dEnd.getMonth() + 1
      const eD = String(dEnd.getDate()).padStart(2, '0')

      const sStr = `${sY}-${String(sM).padStart(2, '0')}-${sD}`
      const eStr = `${eY}-${String(eM).padStart(2, '0')}-${eD}`

      const midDate = new Date(dStart)
      midDate.setDate(midDate.getDate() + 14)
      if (midDate > dEnd) {
        midDate.setTime(dEnd.getTime())
      }
      const s1End = `${midDate.getFullYear()}-${String(midDate.getMonth() + 1).padStart(2, '0')}-${String(midDate.getDate()).padStart(2, '0')}`

      const nextDay = new Date(midDate)
      nextDay.setDate(nextDay.getDate() + 1)
      const s2Start = nextDay <= dEnd ? `${nextDay.getFullYear()}-${String(nextDay.getMonth() + 1).padStart(2, '0')}-${String(nextDay.getDate()).padStart(2, '0')}` : s1End

      periods.push({
        month: i + 1,
        label: `Bulan ${i + 1}`,
        month_name: `${formatShortDate(sStr)} s.d. ${formatShortDate(eStr)}`,
        selected_month: sM,
        selected_year: sY,
        start_date: sStr,
        end_date: eStr,
        period_text: `${formatDate(sStr)} - ${formatDate(eStr)}`,
        target_monitoring: 2,
        session_1: {
          session: 1,
          label: 'Monitoring 1 (Minggu 1-2)',
          start_date: sStr,
          end_date: s1End,
          period_text: `${formatShortDate(sStr)} - ${formatShortDate(s1End)}`,
        },
        session_2: {
          session: 2,
          label: 'Monitoring 2 (Minggu 3-4)',
          start_date: s2Start,
          end_date: eStr,
          period_text: `${formatShortDate(s2Start)} - ${formatShortDate(eStr)}`,
        },
      })
    }
  }

  return periods
}

const calculateMonitoringStartDate = (dateStr) => {
  if (!dateStr) return '2026-08-01'
  const parts = dateStr.split('-')
  if (parts.length < 3) return dateStr
  const year = parseInt(parts[0], 10)
  const month = parseInt(parts[1], 10)
  const day = parseInt(parts[2], 10)

  // Khusus Juni jika mulai tgl 16 ke atas (karena Juli libur semester/kenaikan kelas) -> langsung 1 Agustus
  if (month === 6 && day >= 16) {
    return `${year}-08-01`
  }

  // Aturan umum: jika mulai tanggal 16 ke atas (misal 20 Juli, 20 Agustus, dll),
  // sisa hari di bulan tersebut tidak cukup untuk 2x kunjungan (interval 2 minggu sekali).
  // Maka Monitoring Bulan Ke-1 otomatis dimulai tgl 1 di bulan berikutnya!
  if (day >= 16) {
    const nextDate = new Date(year, month, 1)
    const sYear = nextDate.getFullYear()
    const sMonth = String(nextDate.getMonth() + 1).padStart(2, '0')
    return `${sYear}-${sMonth}-01`
  }

  return `${year}-${String(month).padStart(2, '0')}-01`
}

const defaultPkl = props.periodSetting?.tanggal_mulai_pkl || '2026-07-20'
const defaultMon = props.periodSetting?.tanggal_mulai_monitoring || calculateMonitoringStartDate(defaultPkl)
const defaultSelesai = props.periodSetting?.tanggal_selesai_pkl || '2026-12-20'

const initialPeriods = (props.periodSetting?.monthly_periods && Array.isArray(props.periodSetting.monthly_periods) && props.periodSetting.monthly_periods.length > 0)
  ? props.periodSetting.monthly_periods.map(parsePeriodMonthYear)
  : generatePeriodsClient(defaultMon, props.periodSetting?.periode_mode || 'calendar', defaultSelesai)

const form = useForm({
  tanggal_mulai_pkl: defaultPkl,
  tanggal_selesai_pkl: defaultSelesai,
  tanggal_mulai_monitoring: defaultMon,
  periode_mode: props.periodSetting?.periode_mode || 'calendar',
  monthly_periods: initialPeriods,
})

const isCustomMode = ref(false)

const isRolledOver = computed(() => {
  if (!form.tanggal_mulai_pkl || !form.tanggal_mulai_monitoring) return false
  const pklMonth = form.tanggal_mulai_pkl.substring(0, 7)
  const monMonth = form.tanggal_mulai_monitoring.substring(0, 7)
  return pklMonth !== monMonth
})

// Auto-sync monitoring date when PKL start date changes
watch(
  () => form.tanggal_mulai_pkl,
  (newVal) => {
    if (!newVal) return
    form.tanggal_mulai_monitoring = calculateMonitoringStartDate(newVal)
    form.monthly_periods = generatePeriodsClient(form.tanggal_mulai_monitoring, form.periode_mode, form.tanggal_selesai_pkl)
  }
)

// Auto-recalculate periods when monitoring date or mode changes
watch(
  [() => form.tanggal_mulai_monitoring, () => form.periode_mode],
  ([newMon, newMode]) => {
    if (newMon) {
      form.monthly_periods = generatePeriodsClient(newMon, newMode, form.tanggal_selesai_pkl)
    }
  }
)

// Auto-recalculate periods dynamically when end date changes
watch(
  () => form.tanggal_selesai_pkl,
  (newEnd) => {
    if (newEnd) {
      form.monthly_periods = generatePeriodsClient(form.tanggal_mulai_monitoring, form.periode_mode, newEnd)
    }
  }
)

const onPeriodMonthYearChange = (period) => {
  const m = monthOptions.find((item) => item.value === Number(period.selected_month))
  const monthLabel = m ? m.label : 'Bulan'
  period.month_name = `${monthLabel} ${period.selected_year}`

  if (form.periode_mode === 'calendar') {
    const year = Number(period.selected_year)
    const month = Number(period.selected_month)
    const lastDay = new Date(year, month, 0).getDate()

    period.start_date = `${year}-${String(month).padStart(2, '0')}-01`
    period.end_date = `${year}-${String(month).padStart(2, '0')}-${String(lastDay).padStart(2, '0')}`
    period.period_text = `${formatDate(period.start_date)} - ${formatDate(period.end_date)}`

    if (period.session_1) {
      period.session_1.start_date = period.start_date
      period.session_1.end_date = `${year}-${String(month).padStart(2, '0')}-15`
      period.session_1.period_text = `${formatShortDate(period.session_1.start_date)} - ${formatShortDate(period.session_1.end_date)}`
    }
    if (period.session_2) {
      period.session_2.start_date = `${year}-${String(month).padStart(2, '0')}-16`
      period.session_2.end_date = period.end_date
      period.session_2.period_text = `${formatShortDate(period.session_2.start_date)} - ${formatShortDate(period.session_2.end_date)}`
    }
  }
}

const submit = () => {
  form.post(route('admin.monitoring.period-setting.save'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <LayoutAuthenticated>
    <Head :title="`Pengaturan Jadwal PKL (${form.monthly_periods?.length || 0} Bulan)`" />

    <SectionMain>
      <!-- Clean Header with Single Navigation Link -->
      <SectionTitleLineWithButton :icon="mdiCalendarClock" :title="`Jadwal & Periode PKL (${form.monthly_periods?.length || 0} Bulan)`" main>
        <Link
          :href="route('admin.monitoring.index')"
          class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold transition-all bg-white hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 shadow-sm"
        >
          <BaseIcon :path="mdiArrowLeft" w="w-4" h="h-4" />
          <span>Kembali ke Monitoring</span>
        </Link>
      </SectionTitleLineWithButton>

      <NotificationBar v-if="flashMessage" color="success" :icon="mdiCheckCircle" class="mb-5 shadow-sm">
        <span>{{ flashMessage }}</span>
      </NotificationBar>

      <NotificationBar v-if="flashError" color="danger" :icon="mdiAlertCircle" class="mb-5 shadow-sm">
        <span>{{ flashError }}</span>
      </NotificationBar>

      <form @submit.prevent="submit" class="space-y-5">
        <!-- Card 1: Pengaturan Utama (Rentang Waktu PKL & Monitoring) -->
        <CardBox class="shadow-sm">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5 p-1">
            <!-- Kolom 1: Tanggal Mulai PKL (Awal) -->
            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-slate-700 dark:text-slate-300 mb-1.5">
                Tanggal Mulai PKL (Awal) <span class="text-red-500">*</span>
              </label>
              <input
                type="date"
                v-model="form.tanggal_mulai_pkl"
                min="2024-01-01"
                max="2040-12-31"
                class="w-full rounded-xl border-slate-300 text-sm font-bold focus:border-indigo-500 focus:ring-indigo-500 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100 py-2.5 px-3"
                required
              />
              <p class="text-[11px] text-slate-500 mt-1">
                Hari pertama siswa mulai masuk PKL di DUDI.
              </p>
            </div>

            <!-- Kolom 2: Tanggal Selesai PKL (Akhir) -->
            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-emerald-700 dark:text-emerald-400 mb-1.5">
                Tanggal Selesai PKL (Akhir) <span class="text-red-500">*</span>
              </label>
              <input
                type="date"
                v-model="form.tanggal_selesai_pkl"
                :min="form.tanggal_mulai_pkl || '2024-01-01'"
                max="2040-12-31"
                class="w-full rounded-xl border-emerald-300 dark:border-emerald-700 text-sm font-bold focus:border-emerald-500 focus:ring-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/30 text-emerald-950 dark:text-emerald-100 py-2.5 px-3"
                required
              />
              <p class="text-[11px] text-emerald-600 dark:text-emerald-400 mt-1 font-medium">
                Hari terakhir pelaksanaan PKL siswa di DUDI.
              </p>
            </div>

            <!-- Kolom 3: Mulai Monitoring Pembimbing -->
            <div>
              <label class="block text-xs font-extrabold uppercase tracking-wider text-indigo-700 dark:text-indigo-400 mb-1.5">
                Mulai Monitoring Pembimbing <span class="text-red-500">*</span>
              </label>
              <input
                type="date"
                v-model="form.tanggal_mulai_monitoring"
                min="2024-01-01"
                max="2040-12-31"
                class="w-full rounded-xl border-indigo-300 dark:border-indigo-700 text-sm font-bold focus:border-indigo-500 focus:ring-indigo-500 bg-indigo-50/40 dark:bg-indigo-950/30 text-indigo-950 dark:text-indigo-100 py-2.5 px-3"
                required
              />
              <p class="text-[11px] text-indigo-600 dark:text-indigo-400 mt-1 font-medium">
                Otomatis dihitung (mulai tgl 1 bulan berikutnya jika PKL dimulai tgl 16 ke atas).
              </p>
            </div>
          </div>
        </CardBox>

        <!-- Penjelasan Otomatis Jika Tanggal Bergeser ke Bulan Depan -->
        <div
          v-if="isRolledOver"
          class="p-4 bg-amber-50/90 dark:bg-amber-950/40 border border-amber-200/90 dark:border-amber-800/80 rounded-2xl flex items-start gap-3 text-xs text-amber-950 dark:text-amber-200 shadow-sm"
        >
          <BaseIcon :path="mdiInformation" w="w-5" h="h-5" class="text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" />
          <div class="leading-relaxed">
            <p class="font-extrabold text-amber-900 dark:text-amber-100 text-sm">
              Penyesuaian Jadwal Otomatis (Masa Adaptasi Siswa)
            </p>
            <p class="mt-1 text-amber-800 dark:text-amber-300">
              PKL dimulai pada <strong>{{ formatDate(form.tanggal_mulai_pkl) }}</strong>. Karena sisa hari di bulan tersebut tidak mencukupi untuk 2x kunjungan monitoring (interval 2 minggu sekali), maka sisa hari tersebut difungsikan sebagai masa adaptasi siswa di tempat PKL, dan <strong>Jadwal Monitoring Bulan Ke-1 otomatis dijadwalkan mulai {{ formatDate(form.tanggal_mulai_monitoring) }}</strong>.
            </p>
          </div>
        </div>

        <!-- Card 2: Tabel Jadwal Bersih & Tenang (Tanpa Input Berantakan) -->
        <CardBox class="shadow-sm overflow-hidden" has-table>
          <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50 dark:bg-slate-800/30">
            <div>
              <h3 class="font-extrabold text-sm text-slate-800 dark:text-slate-100 flex items-center gap-2">
                <BaseIcon :path="mdiCalendar" w="w-4" h="h-4" class="text-indigo-600" />
                <span>Pratinjau Jadwal Monitoring {{ form.monthly_periods?.length || 0 }} Bulan (Otomatis 2x / Bulan)</span>
              </h3>
              <p class="text-xs text-slate-500 mt-0.5">
                Total {{ (form.monthly_periods?.length || 0) * 2 }} sesi kunjungan terhitung otomatis dari awal sampai akhir PKL.
              </p>
            </div>
            <!-- Toggle Sesuaikan Manual (Hanya jika perlu) -->
            <button
              type="button"
              @click="isCustomMode = !isCustomMode"
              class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all cursor-pointer"
              :class="isCustomMode
                ? 'bg-indigo-600 text-white border-indigo-600'
                : 'bg-white dark:bg-slate-800 hover:bg-slate-50 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700'"
            >
              <BaseIcon :path="mdiPencilOutline" w="w-3.5" h="h-3.5" />
              <span>{{ isCustomMode ? 'Tutup Penyesuaian' : 'Sesuaikan Tanggal Khusus' }}</span>
            </button>
          </div>

          <!-- Panel Opsi Lanjutan jika Mode Kustom Aktif -->
          <div v-if="isCustomMode" class="p-3.5 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <div class="flex items-center gap-2.5">
              <span class="font-bold text-slate-700 dark:text-slate-300">Metode Perhitungan:</span>
              <select
                v-model="form.periode_mode"
                class="rounded-lg border-slate-300 dark:border-slate-600 text-xs font-semibold py-1.5 px-2.5 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100"
              >
                <option value="calendar">Bulan Kalender Penuh (Standar)</option>
                <option value="cycle">Siklus 30 Hari</option>
              </select>
            </div>
            <span class="text-slate-500">Anda dapat menyunting tanggal atau bulan setiap sesi pada tabel di bawah.</span>
          </div>

          <!-- Clean Overview Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-xs">
              <thead class="bg-slate-50 dark:bg-slate-800/80 font-bold text-slate-600 dark:text-slate-300 border-b border-slate-100 dark:border-slate-800">
                <tr>
                  <th class="px-4 py-3 text-center w-24">Periode</th>
                  <th class="px-4 py-3 text-left">Bulan Kalender</th>
                  <th class="px-4 py-3 text-left">Monitoring 1 (Minggu 1–2)</th>
                  <th class="px-4 py-3 text-left">Monitoring 2 (Minggu 3–4)</th>
                  <th class="px-4 py-3 text-center w-32">Target Kunjungan</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                <tr v-for="(period, idx) in form.monthly_periods" :key="idx" class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                  <!-- Periode -->
                  <td class="px-4 py-3.5 text-center font-bold">
                    <span class="inline-block px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300 font-extrabold text-xs">
                      Bulan {{ period.month }}
                    </span>
                  </td>

                  <!-- Bulan Kalender -->
                  <td class="px-4 py-3.5">
                    <div v-if="!isCustomMode" class="font-bold text-slate-800 dark:text-slate-200 text-sm">
                      {{ period.month_name }}
                    </div>
                    <!-- Custom mode inputs -->
                    <div v-else class="flex items-center gap-1.5">
                      <select
                        v-model.number="period.selected_month"
                        @change="onPeriodMonthYearChange(period)"
                        class="text-xs rounded-lg border-slate-200 dark:border-slate-700 py-1 px-2 font-bold bg-slate-50 dark:bg-slate-800"
                      >
                        <option v-for="m in monthOptions" :key="m.value" :value="m.value">{{ m.label }}</option>
                      </select>
                      <select
                        v-model.number="period.selected_year"
                        @change="onPeriodMonthYearChange(period)"
                        class="text-xs rounded-lg border-slate-200 dark:border-slate-700 py-1 px-2 font-bold bg-slate-50 dark:bg-slate-800"
                      >
                        <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
                      </select>
                    </div>
                  </td>

                  <!-- Monitoring 1 -->
                  <td class="px-4 py-3.5">
                    <div v-if="!isCustomMode" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 font-semibold text-slate-700 dark:text-slate-200">
                      <span class="text-indigo-600 dark:text-indigo-400 font-bold">Monitoring 1:</span>
                      <span>{{ period.session_1?.period_text || formatShortDate(period.start_date) }}</span>
                    </div>
                    <!-- Custom inputs -->
                    <div v-else class="flex items-center gap-1">
                      <input type="date" v-model="period.session_1.start_date" class="text-[11px] rounded border-slate-200 dark:border-slate-700 py-1 px-1" />
                      <span class="text-slate-400">-</span>
                      <input type="date" v-model="period.session_1.end_date" class="text-[11px] rounded border-slate-200 dark:border-slate-700 py-1 px-1" />
                    </div>
                  </td>

                  <!-- Monitoring 2 -->
                  <td class="px-4 py-3.5">
                    <div v-if="!isCustomMode" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 font-semibold text-slate-700 dark:text-slate-200">
                      <span class="text-purple-600 dark:text-purple-400 font-bold">Monitoring 2:</span>
                      <span>{{ period.session_2?.period_text || formatShortDate(period.end_date) }}</span>
                    </div>
                    <!-- Custom inputs -->
                    <div v-else class="flex items-center gap-1">
                      <input type="date" v-model="period.session_2.start_date" class="text-[11px] rounded border-slate-200 dark:border-slate-700 py-1 px-1" />
                      <span class="text-slate-400">-</span>
                      <input type="date" v-model="period.session_2.end_date" class="text-[11px] rounded border-slate-200 dark:border-slate-700 py-1 px-1" />
                    </div>
                  </td>

                  <!-- Target -->
                  <td class="px-4 py-3.5 text-center">
                    <span class="px-2.5 py-1 rounded-full text-[11px] font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/50">
                      2x Kunjungan
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardBox>

        <!-- Card 3: Single Primary Action Button -->
        <div class="flex items-center justify-between p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
          <div class="flex items-center gap-2 text-xs text-slate-500">
            <BaseIcon :path="mdiInformation" w="w-4" h="h-4" class="text-indigo-500 shrink-0" />
            <span>Jadwal otomatis tersimpan dan berlaku serentak ke seluruh guru pembimbing.</span>
          </div>

          <!-- Single Clear Save Button -->
          <BaseButton
            type="submit"
            label="Simpan Jadwal PKL"
            :icon="mdiContentSave"
            color="info"
            :loading="form.processing"
            :auto-permission="false"
            class="px-5 py-2.5 font-bold shadow-md shadow-indigo-500/20"
          />
        </div>
      </form>
    </SectionMain>
  </LayoutAuthenticated>
</template>
