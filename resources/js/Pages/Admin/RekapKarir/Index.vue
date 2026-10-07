<script setup>
import { ref, computed } from 'vue'
import { Head, router, Link, useForm } from '@inertiajs/vue3'
import {
    mdiBriefcase,
    mdiSchool,
    mdiStore,
    mdiClockOutline,
    mdiMagnify,
    mdiFileExcel,
    mdiFilePdfBox,
    mdiEye,
    mdiPencil,
    mdiFilter,
    mdiClose,
    mdiDomain,
    mdiBookOpenVariant,
    mdiCertificate,
    mdiCalendarClock,
    mdiOfficeBuilding,
    mdiAccountBadge,
    mdiCheckDecagram,
    mdiCash,
    mdiLightbulbOn,
    mdiMapMarker,
    mdiHandshake,
    mdiAccountSchool,
    mdiContentSave,
    mdiCheck,
    mdiCheckCircle,
    mdiLockOutline,
    mdiLockOpenOutline,
    mdiAlertCircleOutline
} from '@mdi/js'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'
import CardBoxModal from '@admin/components/CardBoxModal.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'

const props = defineProps({
    siswaList: Object,
    stats: Object,
    kelases: Array,
    jurusans: Array,
    filters: Object,
    portalSetting: Object,
})

const search = ref(props.filters.search || '')
const tipeKarir = ref(props.filters.tipe_karir || 'all')
const jurusanId = ref(props.filters.jurusan_id || '')
const kelasId = ref(props.filters.kelas_id || '')
const perPage = ref(props.filters.per_page || 10)

// Dynamic filtered classes based on selected jurusan
const filteredKelases = computed(() => {
    if (!jurusanId.value) return props.kelases || []
    return (props.kelases || []).filter(k => k.jurusan_id == jurusanId.value)
})

const onJurusanChange = () => {
    if (kelasId.value) {
        const exists = filteredKelases.value.some(k => k.id == kelasId.value)
        if (!exists) {
            kelasId.value = ''
        }
    }
    applyFilter()
}

// Options Lists for Form Chips
const statusPTList = ['PTN', 'PTS', 'Kedinasan', 'Luar Negeri']
const jenjangList = ['D4', 'S1']
const jalurList = ['SNBP', 'SNBT', 'Mandiri', 'Beasiswa', 'Lainnya']
const jenisPekerjaanList = ['Kontrak', 'Tetap', 'Magang', 'Freelance']
const kesesuaianList = ['Sangat Sesuai', 'Sesuai', 'Kurang Sesuai', 'Tidak Sesuai']
const targetMulaiList = ['Segera', '1-3 Bulan', '3-6 Bulan', 'Lainnya']
const gajiList = ['< UMK', 'Setara UMK', '> UMK']
const sektorList = ['Jasa', 'Manufaktur', 'Kuliner', 'Kreatif', 'Perdagangan', 'Lainnya']
const kepemilikanList = ['Mandiri', 'Keluarga', 'Kemitraan']
const modalList = ['< 5 Juta', '5 - 20 Juta', '> 20 Juta']

// Filter Change Handler
const applyFilter = () => {
    router.get(
        route('admin.rekap_karir.index'),
        {
            search: search.value,
            tipe_karir: tipeKarir.value,
            jurusan_id: jurusanId.value,
            kelas_id: kelasId.value,
            per_page: perPage.value,
        },
        { preserveState: true, replace: true }
    )
}

const resetFilter = () => {
    search.value = ''
    tipeKarir.value = 'all'
    jurusanId.value = ''
    kelasId.value = ''
    perPage.value = 10
    applyFilter()
}

// Modal Detail State
const isModalActive = ref(false)
const selectedSiswa = ref(null)

const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }) + ' WIB'
}

const formatDateFull = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    }) + ' WIB'
}

const openDetailModal = (siswa) => {
    selectedSiswa.value = siswa
    isModalActive.value = true
}

// Modal Edit State & Form
const isEditModalActive = ref(false)
const selectedEditSiswa = ref(null)

const editForm = useForm({
    tipe_karir: 'kuliah',
    nama_universitas: '',
    status_universitas: 'PTN',
    jenjang_studi: 'S1',
    program_studi: '',
    jalur_masuk: 'SNBP',
    tahun_mulai: new Date().getFullYear(),
    nama_perusahaan: '',
    posisi_kerja: '',
    jenis_pekerjaan: 'Kontrak',
    kesesuaian_jurusan: 'Sesuai',
    target_mulai_kerja: 'Segera',
    ekspektasi_gaji: 'Setara UMK',
    lokasi_kerja: '',
    nama_usaha: '',
    sektor_usaha: 'Jasa',
    status_kepemilikan: 'Mandiri',
    modal_awal: '< 5 Juta',
    lokasi_usaha: '',
})

const openEditModal = (siswa) => {
    selectedEditSiswa.value = siswa
    const k = siswa.siswa_karir || {}
    editForm.tipe_karir = k.tipe_karir || 'kuliah'
    editForm.nama_universitas = k.nama_universitas || ''
    editForm.status_universitas = k.status_universitas || 'PTN'
    editForm.jenjang_studi = k.jenjang_studi || 'S1'
    editForm.program_studi = k.program_studi || ''
    editForm.jalur_masuk = k.jalur_masuk || 'SNBP'
    editForm.tahun_mulai = k.tahun_mulai || new Date().getFullYear()

    editForm.nama_perusahaan = k.nama_perusahaan || ''
    editForm.posisi_kerja = k.posisi_kerja || ''
    editForm.jenis_pekerjaan = k.jenis_pekerjaan || 'Kontrak'
    editForm.kesesuaian_jurusan = k.kesesuaian_jurusan || 'Sesuai'
    editForm.target_mulai_kerja = k.target_mulai_kerja || 'Segera'
    editForm.ekspektasi_gaji = k.ekspektasi_gaji || 'Setara UMK'
    editForm.lokasi_kerja = k.lokasi_kerja || ''

    editForm.nama_usaha = k.nama_usaha || ''
    editForm.sektor_usaha = k.sektor_usaha || 'Jasa'
    editForm.status_kepemilikan = k.status_kepemilikan || 'Mandiri'
    editForm.modal_awal = k.modal_awal || '< 5 Juta'
    editForm.lokasi_usaha = k.lokasi_usaha || ''

    editForm.clearErrors()
    isEditModalActive.value = true
}

const submitEditForm = () => {
    if (!selectedEditSiswa.value) return
    editForm.put(route('admin.rekap_karir.update', selectedEditSiswa.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            isEditModalActive.value = false
        }
    })
}

// Export Trigger Links
const exportExcelUrl = () => {
    const params = new URLSearchParams()
    if (search.value) params.append('search', search.value)
    if (tipeKarir.value && tipeKarir.value !== 'all') params.append('tipe_karir', tipeKarir.value)
    if (jurusanId.value) params.append('jurusan_id', jurusanId.value)
    if (kelasId.value) params.append('kelas_id', kelasId.value)
    const qs = params.toString()
    return route('admin.rekap_karir.export_excel') + (qs ? '?' + qs : '')
}

const exportPdfUrl = () => {
    const params = new URLSearchParams()
    if (search.value) params.append('search', search.value)
    if (tipeKarir.value && tipeKarir.value !== 'all') params.append('tipe_karir', tipeKarir.value)
    if (jurusanId.value) params.append('jurusan_id', jurusanId.value)
    if (kelasId.value) params.append('kelas_id', kelasId.value)
    const qs = params.toString()
    return route('admin.rekap_karir.export_pdf') + (qs ? '?' + qs : '')
}

// Modal Toggle Status Portal Siswa
const isToggleModalActive = ref(false)
const toggleForm = useForm({
    pesan_penutupan: props.portalSetting?.pesan_penutupan || 'Pengisian rencana karier saat ini sedang ditutup oleh pihak sekolah.',
})

const openToggleModal = () => {
    toggleForm.pesan_penutupan = props.portalSetting?.pesan_penutupan || 'Pengisian rencana karier saat ini sedang ditutup oleh pihak sekolah.'
    isToggleModalActive.value = true
}

const confirmTogglePortal = () => {
    toggleForm.post(route('admin.rekap_karir.toggle_status'), {
        preserveScroll: true,
        onSuccess: () => {
            isToggleModalActive.value = false
        }
    })
}
</script>

<template>
    <LayoutAuthenticated>
        <Head title="Rekap Aspirasi Karier Siswa" />

        <SectionMain>
            <SectionTitleLineWithButton :icon="mdiBriefcase" title="Rekap Aspirasi Karier Siswa" main>
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Status Badge Portal Siswa (Warna Terang & Menyala) -->
                    <div
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-black border shadow-xs"
                        :class="portalSetting?.is_open
                            ? 'bg-emerald-100 text-emerald-950 border-emerald-400 dark:bg-emerald-950/80 dark:text-emerald-200'
                            : 'bg-rose-100 text-rose-950 border-rose-400 dark:bg-rose-950/80 dark:text-rose-200'"
                    >
                        <span
                            class="w-2.5 h-2.5 rounded-full"
                            :class="portalSetting?.is_open ? 'bg-emerald-600 animate-pulse' : 'bg-rose-600'"
                        ></span>
                        <span>Portal Siswa: <strong class="uppercase font-black">{{ portalSetting?.is_open ? 'DIBUKA' : 'DITUTUP' }}</strong></span>
                    </div>

                    <!-- Tombol Aksi Buka / Tutup (Hanya 1 Tombol Fungsional, Tanpa Duplikat) -->
                    <button
                        type="button"
                        @click="openToggleModal"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2.5 font-black text-xs rounded-xl shadow-md transition-all active:scale-95 cursor-pointer"
                        :class="portalSetting?.is_open
                            ? 'bg-rose-600 hover:bg-rose-700 text-white'
                            : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
                    >
                        <BaseIcon :path="portalSetting?.is_open ? mdiLockOutline : mdiLockOpenOutline" size="18" />
                        <span>{{ portalSetting?.is_open ? 'Tutup Pengisian Siswa' : 'Buka Pengisian Siswa' }}</span>
                    </button>

                    <a
                        :href="exportExcelUrl()"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition-all active:scale-95"
                    >
                        <BaseIcon :path="mdiFileExcel" size="18" />
                        <span>Export Excel</span>
                    </a>
                    <a
                        :href="exportPdfUrl()"
                        target="_blank"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-md transition-all active:scale-95"
                    >
                        <BaseIcon :path="mdiFilePdfBox" size="18" />
                        <span>Export PDF</span>
                    </a>
                </div>
            </SectionTitleLineWithButton>

            <NotificationBar
                v-if="$page.props.flash && $page.props.flash.message"
                color="success"
                :icon="mdiCheckCircle"
                class="mb-6 shadow-sm"
            >
                {{ $page.props.flash.message }}
            </NotificationBar>

            <!-- 1. Stats Counter Widgets Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <!-- Kuliah -->
                <div class="bg-gradient-to-br from-orange-500 to-amber-600 rounded-3xl p-5 text-white shadow-lg relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 opacity-20 pointer-events-none">
                        <BaseIcon :path="mdiSchool" size="96" />
                    </div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-orange-100 mb-1">Kuliah</div>
                    <div class="text-3xl font-extrabold">{{ stats.total_kuliah }}</div>
                    <div class="text-[11px] text-orange-100 mt-2">Melanjutkan Pendidikan Tinggi</div>
                </div>

                <!-- Kerja -->
                <div class="bg-gradient-to-br from-indigo-600 to-blue-600 rounded-3xl p-5 text-white shadow-lg relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 opacity-20 pointer-events-none">
                        <BaseIcon :path="mdiBriefcase" size="96" />
                    </div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-indigo-100 mb-1">Kerja</div>
                    <div class="text-3xl font-extrabold">{{ stats.total_kerja }}</div>
                    <div class="text-[11px] text-indigo-100 mt-2">Langsung Bekerja di Industri</div>
                </div>

                <!-- Wirausaha -->
                <div class="bg-gradient-to-br from-emerald-600 to-teal-600 rounded-3xl p-5 text-white shadow-lg relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 opacity-20 pointer-events-none">
                        <BaseIcon :path="mdiStore" size="96" />
                    </div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-emerald-100 mb-1">Wirausaha</div>
                    <div class="text-3xl font-extrabold">{{ stats.total_wirausaha }}</div>
                    <div class="text-[11px] text-emerald-100 mt-2">Memulai Usaha Mandiri</div>
                </div>

                <!-- Belum Mengisi -->
                <div class="bg-gradient-to-br from-slate-700 to-slate-800 rounded-3xl p-5 text-white shadow-lg relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 opacity-20 pointer-events-none">
                        <BaseIcon :path="mdiClockOutline" size="96" />
                    </div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-300 mb-1">Belum Mengisi</div>
                    <div class="text-3xl font-extrabold">{{ stats.total_belum }}</div>
                    <div class="text-[11px] text-slate-300 mt-2">Belum Mengisi Aspirasi</div>
                </div>
            </div>

            <!-- 2. Filter Bar Container -->
            <CardBox class="mb-6 border border-slate-200/80 shadow-xs">
                <div class="space-y-4">
                    <!-- Tipe Karier Pill Filter -->
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-xs font-bold text-slate-500 uppercase mr-2">Tipe:</span>

                        <button
                            type="button"
                            @click="tipeKarir = 'all'; applyFilter()"
                            :class="tipeKarir === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium'"
                            class="px-3.5 py-1.5 rounded-full text-xs transition-all active:scale-95"
                        >
                            Semua ({{ stats.total_siswa }})
                        </button>

                        <button
                            type="button"
                            @click="tipeKarir = 'kuliah'; applyFilter()"
                            :class="tipeKarir === 'kuliah' ? 'bg-orange-500 text-white font-bold shadow-sm' : 'bg-orange-50 hover:bg-orange-100 text-orange-700 font-medium'"
                            class="px-3.5 py-1.5 rounded-full text-xs transition-all active:scale-95"
                        >
                            🎓 Kuliah ({{ stats.total_kuliah }})
                        </button>

                        <button
                            type="button"
                            @click="tipeKarir = 'kerja'; applyFilter()"
                            :class="tipeKarir === 'kerja' ? 'bg-indigo-600 text-white font-bold shadow-sm' : 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-medium'"
                            class="px-3.5 py-1.5 rounded-full text-xs transition-all active:scale-95"
                        >
                            💼 Kerja ({{ stats.total_kerja }})
                        </button>

                        <button
                            type="button"
                            @click="tipeKarir = 'wirausaha'; applyFilter()"
                            :class="tipeKarir === 'wirausaha' ? 'bg-emerald-600 text-white font-bold shadow-sm' : 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-medium'"
                            class="px-3.5 py-1.5 rounded-full text-xs transition-all active:scale-95"
                        >
                            🏪 Wirausaha ({{ stats.total_wirausaha }})
                        </button>

                        <button
                            type="button"
                            @click="tipeKarir = 'belum'; applyFilter()"
                            :class="tipeKarir === 'belum' ? 'bg-slate-600 text-white font-bold shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium'"
                            class="px-3.5 py-1.5 rounded-full text-xs transition-all active:scale-95"
                        >
                            ⏳ Belum Mengisi ({{ stats.total_belum }})
                        </button>
                    </div>

                    <!-- Search Input & Dropdowns Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 pt-2 border-t border-slate-100">
                        <div class="sm:col-span-4 relative">
                            <input
                                v-model="search"
                                type="text"
                                placeholder="Cari Nama, NISN, Kelas, Universitas, Usaha..."
                                class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all"
                                @keyup.enter="applyFilter"
                            />
                            <div class="absolute left-3 top-2.5 text-slate-400">
                                <BaseIcon :path="mdiMagnify" size="16" />
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <select
                                v-model="jurusanId"
                                class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all"
                                @change="onJurusanChange"
                            >
                                <option value="">-- Semua Jurusan --</option>
                                <option v-for="j in jurusans" :key="j.id" :value="j.id">{{ j.nama_jurusan }}</option>
                            </select>
                        </div>

                        <div class="sm:col-span-3">
                            <select
                                v-model="kelasId"
                                class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all"
                                @change="applyFilter"
                            >
                                <option value="">-- Semua Kelas --</option>
                                <option v-for="k in filteredKelases" :key="k.id" :value="k.id">{{ k.nama_kelas }}</option>
                            </select>
                        </div>

                        <div class="sm:col-span-2 flex items-center gap-2">
                            <select
                                v-model="perPage"
                                class="w-full py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all"
                                @change="applyFilter"
                            >
                                <option :value="10">10 / hal</option>
                                <option :value="20">20 / hal</option>
                                <option :value="50">50 / hal</option>
                                <option :value="100">100 / hal</option>
                                <option :value="200">200 / hal</option>
                            </select>

                            <button
                                type="button"
                                @click="resetFilter"
                                title="Reset Filter"
                                class="p-2 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl transition-all flex-shrink-0"
                            >
                                <BaseIcon :path="mdiClose" size="16" />
                            </button>
                        </div>
                    </div>
                </div>
            </CardBox>

            <!-- 3. DataTable Component -->
            <CardBox class="shadow-sm border border-slate-200/80 overflow-hidden" has-table>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-700">
                        <thead class="bg-slate-100/80 text-slate-600 uppercase font-bold text-[11px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4 w-12 text-center">No</th>
                                <th class="py-3.5 px-4">Nama Siswa</th>
                                <th class="py-3.5 px-4">Kelas & Jurusan</th>
                                <th class="py-3.5 px-4 text-center">Pilihan Karier</th>
                                <th class="py-3.5 px-4">Ringkasan Aspirasi & Target</th>
                                <th class="py-3.5 px-4 text-center w-36">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr
                                v-for="(siswa, index) in siswaList.data"
                                :key="siswa.id"
                                class="hover:bg-slate-50/80 transition-colors"
                            >
                                <td class="py-3.5 px-4 text-center font-bold text-slate-500">
                                    {{ siswaList.from + index }}
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-extrabold text-slate-900 text-xs sm:text-sm">{{ siswa.nama }}</div>
                                    <div class="text-[11px] text-slate-400 mt-0.5">NISN: {{ siswa.nisn || '-' }}</div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-800">{{ siswa.kelas?.nama_kelas || '-' }}</div>
                                    <div class="text-[10px] text-slate-500">{{ siswa.kelas?.jurusan?.nama_jurusan || siswa.jurusan?.nama_jurusan || '-' }}</div>
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <span
                                        v-if="siswa.siswa_karir?.tipe_karir === 'kuliah'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-orange-100 text-orange-700"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-500"></span>
                                        Kuliah
                                    </span>
                                    <span
                                        v-else-if="siswa.siswa_karir?.tipe_karir === 'kerja'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-indigo-100 text-indigo-700"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                        Kerja
                                    </span>
                                    <span
                                        v-else-if="siswa.siswa_karir?.tipe_karir === 'wirausaha'"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-extrabold bg-emerald-100 text-emerald-700"
                                    >
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Wirausaha
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-500"
                                    >
                                        Belum Mengisi
                                    </span>

                                    <div v-if="siswa.siswa_karir?.updated_at" class="text-[10px] font-medium text-slate-400 mt-1 flex items-center justify-center gap-1">
                                        <BaseIcon :path="mdiCalendarClock" size="12" />
                                        <span>{{ formatDate(siswa.siswa_karir.updated_at) }}</span>
                                    </div>
                                </td>

                                <td class="py-3.5 px-4">
                                    <!-- KULIAH -->
                                    <div v-if="siswa.siswa_karir?.tipe_karir === 'kuliah'" class="space-y-0.5">
                                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                            <BaseIcon :path="mdiSchool" size="14" class="text-orange-500 flex-shrink-0" />
                                            <span>{{ siswa.siswa_karir.nama_universitas }}</span>
                                            <span class="text-[10px] text-orange-600 font-semibold px-1.5 py-0.2 bg-orange-50 rounded">
                                                {{ siswa.siswa_karir.status_universitas }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-600">
                                            Prodi: <span class="font-semibold">{{ siswa.siswa_karir.program_studi }}</span> ({{ siswa.siswa_karir.jenjang_studi }})
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            Jalur: {{ siswa.siswa_karir.jalur_masuk }} | Tahun: {{ siswa.siswa_karir.tahun_mulai }}
                                        </div>
                                    </div>

                                    <!-- KERJA -->
                                    <div v-else-if="siswa.siswa_karir?.tipe_karir === 'kerja'" class="space-y-0.5">
                                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                            <BaseIcon :path="mdiOfficeBuilding" size="14" class="text-indigo-500 flex-shrink-0" />
                                            <span>{{ siswa.siswa_karir.nama_perusahaan }}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-600">
                                            Posisi: <span class="font-semibold">{{ siswa.siswa_karir.posisi_kerja }}</span> ({{ siswa.siswa_karir.jenis_pekerjaan }})
                                        </div>
                                        <div class="text-[10px] text-indigo-600 font-medium flex items-center gap-1">
                                            <BaseIcon :path="mdiMapMarker" size="12" />
                                            <span>Lokasi Target: {{ siswa.siswa_karir.lokasi_kerja || '-' }}</span>
                                        </div>
                                    </div>

                                    <!-- WIRAUSAHA -->
                                    <div v-else-if="siswa.siswa_karir?.tipe_karir === 'wirausaha'" class="space-y-0.5">
                                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                                            <BaseIcon :path="mdiLightbulbOn" size="14" class="text-emerald-500 flex-shrink-0" />
                                            <span>{{ siswa.siswa_karir.nama_usaha }}</span>
                                        </div>
                                        <div class="text-[11px] text-slate-600">
                                            Sektor: <span class="font-semibold">{{ siswa.siswa_karir.sektor_usaha }}</span> | Status: {{ siswa.siswa_karir.status_kepemilikan }}
                                        </div>
                                        <div class="text-[10px] text-emerald-600 font-medium">
                                            Modal: {{ siswa.siswa_karir.modal_awal }} | Lokasi: {{ siswa.siswa_karir.lokasi_usaha }}
                                        </div>
                                    </div>

                                    <!-- BELUM -->
                                    <div v-else class="text-slate-400 italic text-[11px]">
                                        Belum memilih atau mengisi formulir karier.
                                    </div>
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button
                                            v-if="siswa.siswa_karir"
                                            type="button"
                                            @click="openDetailModal(siswa)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-orange-50 hover:bg-orange-100 text-orange-700 rounded-xl text-xs font-bold transition-all active:scale-95"
                                            title="Lihat Detail"
                                        >
                                            <BaseIcon :path="mdiEye" size="14" />
                                            <span>Detail</span>
                                        </button>
                                        <button
                                            type="button"
                                            @click="openEditModal(siswa)"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-bold transition-all active:scale-95"
                                            title="Edit Aspirasi Karier"
                                        >
                                            <BaseIcon :path="mdiPencil" size="14" />
                                            <span>Edit</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="siswaList.data.length === 0">
                                <td colspan="6" class="py-12 text-center text-slate-400 italic">
                                    Tidak ada data siswa yang cocok dengan filter.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Footer -->
                <div v-if="siswaList.links && siswaList.links.length > 3" class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div class="text-slate-500">
                        Menampilkan <span class="font-bold text-slate-800">{{ siswaList.from }}</span> - <span class="font-bold text-slate-800">{{ siswaList.to }}</span> dari <span class="font-bold text-slate-800">{{ siswaList.total }}</span> siswa
                    </div>
                    <div class="flex flex-wrap gap-1">
                        <Link
                            v-for="(link, i) in siswaList.links"
                            :key="i"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                link.active ? 'bg-orange-500 text-white font-bold' : 'bg-slate-100 hover:bg-slate-200 text-slate-700',
                                !link.url ? 'opacity-50 cursor-not-allowed' : 'active:scale-95'
                            ]"
                            class="px-3 py-1.5 rounded-lg text-xs transition-all"
                        />
                    </div>
                </div>
            </CardBox>
        </SectionMain>

        <!-- Detail Modal Popover -->
        <CardBoxModal
            v-model="isModalActive"
            title="Detail Aspirasi Karier Siswa"
            button="blue"
            has-cancel
            button-label="Tutup"
        >
            <div v-if="selectedSiswa && selectedSiswa.siswa_karir" class="space-y-4 text-xs text-slate-700">
                <!-- Header Student Profile Card -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-orange-500 to-amber-600 text-white flex items-center justify-center font-bold text-base flex-shrink-0">
                        {{ selectedSiswa.nama?.charAt(0) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-extrabold text-sm text-slate-900">{{ selectedSiswa.nama }}</div>
                        <div class="text-slate-500 text-[11px]">
                            NISN: {{ selectedSiswa.nisn || '-' }} | Kelas: {{ selectedSiswa.kelas?.nama_kelas || '-' }} ({{ selectedSiswa.kelas?.jurusan?.nama_jurusan || selectedSiswa.jurusan?.nama_jurusan || '-' }})
                        </div>
                    </div>
                </div>

                <!-- Timestamp Box -->
                <div class="p-3 bg-slate-100 rounded-xl border border-slate-200/80 text-slate-600 flex items-center justify-between text-[11px] font-medium">
                    <div class="flex items-center gap-2">
                        <BaseIcon :path="mdiCalendarClock" size="16" class="text-orange-500" />
                        <span>Waktu Terakhir Dikirim ke DB:</span>
                    </div>
                    <span class="font-bold text-slate-900">{{ formatDateFull(selectedSiswa.siswa_karir.updated_at) }}</span>
                </div>

                <!-- 1. KULIAH DETAIL -->
                <div v-if="selectedSiswa.siswa_karir.tipe_karir === 'kuliah'" class="space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-orange-100 text-orange-800 rounded-full font-bold text-xs">
                        <BaseIcon :path="mdiSchool" size="16" />
                        <span>Kategori: Kuliah (Pendidikan Tinggi)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 rounded-2xl bg-orange-50/50 border border-orange-100">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Perguruan Tinggi Target</span>
                            <span class="font-bold text-slate-900 text-sm">{{ selectedSiswa.siswa_karir.nama_universitas }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Status Kampus</span>
                            <span class="font-bold text-slate-800">{{ selectedSiswa.siswa_karir.status_universitas }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Program Studi Target</span>
                            <span class="font-bold text-slate-800">{{ selectedSiswa.siswa_karir.program_studi }} ({{ selectedSiswa.siswa_karir.jenjang_studi }})</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Jalur Masuk & Tahun</span>
                            <span class="font-bold text-slate-800">{{ selectedSiswa.siswa_karir.jalur_masuk }} (Mulai {{ selectedSiswa.siswa_karir.tahun_mulai }})</span>
                        </div>
                    </div>
                </div>

                <!-- 2. KERJA DETAIL -->
                <div v-else-if="selectedSiswa.siswa_karir.tipe_karir === 'kerja'" class="space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-100 text-indigo-800 rounded-full font-bold text-xs">
                        <BaseIcon :path="mdiBriefcase" size="16" />
                        <span>Kategori: Kerja (Dunia Kerja / DUDI)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 rounded-2xl bg-indigo-50/50 border border-indigo-100">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Target Perusahaan / DUDI</span>
                            <span class="font-bold text-slate-900 text-sm">{{ selectedSiswa.siswa_karir.nama_perusahaan }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Posisi / Jabatan Incaran</span>
                            <span class="font-bold text-slate-800">{{ selectedSiswa.siswa_karir.posisi_kerja }} ({{ selectedSiswa.siswa_karir.jenis_pekerjaan }})</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Kesesuaian Jurusan</span>
                            <span class="font-bold text-slate-800">{{ selectedSiswa.siswa_karir.kesesuaian_jurusan }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Ekspektasi Gaji Awal</span>
                            <span class="font-bold text-slate-800">{{ selectedSiswa.siswa_karir.ekspektasi_gaji }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Target Mulai Bekerja</span>
                            <span class="font-bold text-slate-800">{{ selectedSiswa.siswa_karir.target_mulai_kerja }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Rencana Lokasi Kerja</span>
                            <span class="font-extrabold text-indigo-700 flex items-center gap-1">
                                <BaseIcon :path="mdiMapMarker" size="14" />
                                <span>{{ selectedSiswa.siswa_karir.lokasi_kerja || '-' }}</span>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 3. WIRAUSAHA DETAIL -->
                <div v-else-if="selectedSiswa.siswa_karir.tipe_karir === 'wirausaha'" class="space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full font-bold text-xs">
                        <BaseIcon :path="mdiStore" size="16" />
                        <span>Kategori: Wirausaha (Bisnis Mandiri)</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 rounded-2xl bg-emerald-50/50 border border-emerald-100">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Nama / Merk Usaha</span>
                            <span class="font-bold text-slate-900 text-sm">{{ selectedSiswa.siswa_karir.nama_usaha }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Sektor Usaha</span>
                            <span class="font-bold text-slate-800">{{ selectedSiswa.siswa_karir.sektor_usaha }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Status Kepemilikan</span>
                            <span class="font-bold text-slate-800">{{ selectedSiswa.siswa_karir.status_kepemilikan }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Estimasi Modal Awal</span>
                            <span class="font-bold text-slate-800">{{ selectedSiswa.siswa_karir.modal_awal }}</span>
                        </div>
                        <div class="sm:col-span-2">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Rencana Lokasi Usaha</span>
                            <span class="font-bold text-emerald-800 flex items-center gap-1">
                                <BaseIcon :path="mdiMapMarker" size="14" />
                                <span>{{ selectedSiswa.siswa_karir.lokasi_usaha }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </CardBoxModal>

        <!-- Admin Edit Modal Popover -->
        <CardBoxModal
            v-model="isEditModalActive"
            title="Edit Aspirasi Karier Siswa (Admin)"
            button="blue"
            has-cancel
            button-label="Simpan Perubahan"
            :close-on-confirm="false"
            @confirm="submitEditForm"
        >
            <div v-if="selectedEditSiswa" class="space-y-4 text-xs text-slate-700">
                <!-- Siswa Header Info -->
                <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-100 flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-blue-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                        {{ selectedEditSiswa.nama?.charAt(0) }}
                    </div>
                    <div>
                        <div class="font-extrabold text-sm text-slate-900">{{ selectedEditSiswa.nama }}</div>
                        <div class="text-slate-500 text-[11px]">
                            NISN: {{ selectedEditSiswa.nisn || '-' }} | Kelas: {{ selectedEditSiswa.kelas?.nama_kelas || '-' }}
                        </div>
                    </div>
                </div>

                <!-- Tipe Karier Choice Pills -->
                <div>
                    <label class="block font-bold text-slate-700 text-xs mb-1.5 uppercase">Pilih Rencana Karier</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button
                            type="button"
                            @click="editForm.tipe_karir = 'kuliah'; editForm.clearErrors()"
                            :class="editForm.tipe_karir === 'kuliah' ? 'bg-orange-500 text-white font-extrabold shadow-sm' : 'bg-slate-100 text-slate-600 font-medium'"
                            class="py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all"
                        >
                            <BaseIcon :path="mdiSchool" size="16" />
                            <span>Kuliah</span>
                        </button>
                        <button
                            type="button"
                            @click="editForm.tipe_karir = 'kerja'; editForm.clearErrors()"
                            :class="editForm.tipe_karir === 'kerja' ? 'bg-indigo-600 text-white font-extrabold shadow-sm' : 'bg-slate-100 text-slate-600 font-medium'"
                            class="py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all"
                        >
                            <BaseIcon :path="mdiBriefcase" size="16" />
                            <span>Kerja</span>
                        </button>
                        <button
                            type="button"
                            @click="editForm.tipe_karir = 'wirausaha'; editForm.clearErrors()"
                            :class="editForm.tipe_karir === 'wirausaha' ? 'bg-emerald-600 text-white font-extrabold shadow-sm' : 'bg-slate-100 text-slate-600 font-medium'"
                            class="py-2.5 px-3 rounded-xl text-xs flex items-center justify-center gap-1.5 transition-all"
                        >
                            <BaseIcon :path="mdiStore" size="16" />
                            <span>Wirausaha</span>
                        </button>
                    </div>
                </div>

                <!-- FORM KULIAH -->
                <div v-if="editForm.tipe_karir === 'kuliah'" class="space-y-3 pt-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Perguruan Tinggi / Universitas</label>
                        <input
                            v-model="editForm.nama_universitas"
                            type="text"
                            placeholder="Contoh: Universitas Airlangga / ITS"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                        />
                        <span v-if="editForm.errors.nama_universitas" class="text-rose-500 text-[10px] mt-0.5 block">{{ editForm.errors.nama_universitas }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Kampus</label>
                            <div class="flex flex-wrap gap-1">
                                <button
                                    v-for="opt in statusPTList"
                                    :key="opt"
                                    type="button"
                                    @click="editForm.status_universitas = opt"
                                    :class="editForm.status_universitas === opt ? 'bg-orange-500 text-white font-bold' : 'bg-slate-100 text-slate-600'"
                                    class="px-2.5 py-1 rounded-lg text-[11px]"
                                >
                                    {{ opt }}
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Jenjang Studi Target</label>
                            <div class="flex gap-1.5">
                                <button
                                    v-for="opt in jenjangList"
                                    :key="opt"
                                    type="button"
                                    @click="editForm.jenjang_studi = opt"
                                    :class="editForm.jenjang_studi === opt ? 'bg-orange-500 text-white font-bold' : 'bg-slate-100 text-slate-600'"
                                    class="px-3 py-1 rounded-lg text-[11px] flex-1 text-center"
                                >
                                    {{ opt }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Program Studi Target</label>
                        <input
                            v-model="editForm.program_studi"
                            type="text"
                            placeholder="Contoh: Teknik Informatika / Manajemen"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"
                        />
                        <span v-if="editForm.errors.program_studi" class="text-rose-500 text-[10px] mt-0.5 block">{{ editForm.errors.program_studi }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Jalur Masuk</label>
                            <select v-model="editForm.jalur_masuk" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                                <option v-for="j in jalurList" :key="j" :value="j">{{ j }}</option>
                            </select>
                            <span v-if="editForm.errors.jalur_masuk" class="text-rose-500 text-[10px] mt-0.5 block">{{ editForm.errors.jalur_masuk }}</span>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Tahun Mulai</label>
                            <input
                                v-model.number="editForm.tahun_mulai"
                                type="number"
                                class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs"
                            />
                            <span v-if="editForm.errors.tahun_mulai" class="text-rose-500 text-[10px] mt-0.5 block">{{ editForm.errors.tahun_mulai }}</span>
                        </div>
                    </div>
                </div>

                <!-- FORM KERJA -->
                <div v-else-if="editForm.tipe_karir === 'kerja'" class="space-y-3 pt-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama Perusahaan Target</label>
                        <input
                            v-model="editForm.nama_perusahaan"
                            type="text"
                            placeholder="Contoh: PT Astra International / Bank Mandiri"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        />
                        <span v-if="editForm.errors.nama_perusahaan" class="text-rose-500 text-[10px] mt-0.5 block">{{ editForm.errors.nama_perusahaan }}</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Posisi / Jabatan Incaran</label>
                        <input
                            v-model="editForm.posisi_kerja"
                            type="text"
                            placeholder="Contoh: Operator Produksi / Staff IT"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        />
                        <span v-if="editForm.errors.posisi_kerja" class="text-rose-500 text-[10px] mt-0.5 block">{{ editForm.errors.posisi_kerja }}</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Rencana Lokasi Kerja yang Diinginkan</label>
                        <input
                            v-model="editForm.lokasi_kerja"
                            type="text"
                            placeholder="Contoh: Surabaya / Jabodetabek / Luar Negeri"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500"
                        />
                        <span v-if="editForm.errors.lokasi_kerja" class="text-rose-500 text-[10px] mt-0.5 block">{{ editForm.errors.lokasi_kerja }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Jenis Pekerjaan</label>
                            <select v-model="editForm.jenis_pekerjaan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                                <option v-for="j in jenisPekerjaanList" :key="j" :value="j">{{ j }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Ekspektasi Gaji</label>
                            <select v-model="editForm.ekspektasi_gaji" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                                <option v-for="g in gajiList" :key="g" :value="g">{{ g }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Kesesuaian Jurusan</label>
                            <select v-model="editForm.kesesuaian_jurusan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                                <option v-for="k in kesesuaianList" :key="k" :value="k">{{ k }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Target Mulai Kerja</label>
                            <select v-model="editForm.target_mulai_kerja" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                                <option v-for="t in targetMulaiList" :key="t" :value="t">{{ t }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- FORM WIRAUSAHA -->
                <div v-else-if="editForm.tipe_karir === 'wirausaha'" class="space-y-3 pt-2">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Nama / Merk Usaha</label>
                        <input
                            v-model="editForm.nama_usaha"
                            type="text"
                            placeholder="Contoh: Kopi Janji Jiwa / Bengkel Mandiri"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                        />
                        <span v-if="editForm.errors.nama_usaha" class="text-rose-500 text-[10px] mt-0.5 block">{{ editForm.errors.nama_usaha }}</span>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Rencana Lokasi Usaha</label>
                        <input
                            v-model="editForm.lokasi_usaha"
                            type="text"
                            placeholder="Contoh: Sidoarjo / Online Shop"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500"
                        />
                        <span v-if="editForm.errors.lokasi_usaha" class="text-rose-500 text-[10px] mt-0.5 block">{{ editForm.errors.lokasi_usaha }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Sektor Usaha</label>
                            <select v-model="editForm.sektor_usaha" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                                <option v-for="s in sektorList" :key="s" :value="s">{{ s }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Status Kepemilikan</label>
                            <select v-model="editForm.status_kepemilikan" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                                <option v-for="k in kepemilikanList" :key="k" :value="k">{{ k }}</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Modal Awal</label>
                        <select v-model="editForm.modal_awal" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                            <option v-for="m in modalList" :key="m" :value="m">{{ m }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </CardBoxModal>

        <!-- Modal Konfirmasi Buka / Tutup Pengisian Siswa -->
        <CardBoxModal
            v-model="isToggleModalActive"
            :title="portalSetting?.is_open ? 'Tutup Pengisian Siswa' : 'Buka Pengisian Siswa'"
            :button="portalSetting?.is_open ? 'danger' : 'success'"
            :button-label="portalSetting?.is_open ? 'Ya, Tutup Pengisian' : 'Ya, Buka Pengisian'"
            has-cancel
            @confirm="confirmTogglePortal"
        >
            <div class="space-y-4 text-xs font-bold text-slate-900 dark:text-white">
                <div
                    class="p-4 rounded-2xl flex items-start gap-3 border"
                    :class="portalSetting?.is_open ? 'bg-rose-50 border-rose-200 text-rose-950 dark:bg-rose-950/40 dark:text-rose-200' : 'bg-emerald-50 border-emerald-200 text-emerald-950 dark:bg-emerald-950/40 dark:text-emerald-200'"
                >
                    <BaseIcon :path="portalSetting?.is_open ? mdiLockOutline : mdiLockOpenOutline" size="24" class="shrink-0" />
                    <div>
                        <p class="font-black text-sm">
                            {{ portalSetting?.is_open ? 'Apakah Anda yakin ingin MENUTUP akses pengisian siswa?' : 'Apakah Anda yakin ingin MEMBUKA akses pengisian siswa?' }}
                        </p>
                        <p class="mt-1 text-xs font-medium">
                            {{ portalSetting?.is_open
                                ? 'Saat ditutup, seluruh siswa tidak dapat mengisi ataupun mengubah formulir rencana karier di menu siswa.'
                                : 'Saat dibuka, siswa yang belum mengisi atau masih memiliki jatah revisi dapat mengakses dan mengisi kembali formulir karier.' }}
                        </p>
                    </div>
                </div>

                <div v-if="portalSetting?.is_open" class="space-y-1.5">
                    <label class="block font-black text-xs text-slate-900 dark:text-white">
                        Pesan Notifikasi untuk Siswa saat Ditutup:
                    </label>
                    <textarea
                        v-model="toggleForm.pesan_penutupan"
                        rows="2"
                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-950 dark:text-white font-bold focus:ring-2 focus:ring-rose-500"
                        placeholder="Contoh: Pengisian rencana karier saat ini sedang ditutup oleh pihak sekolah."
                    ></textarea>
                </div>
            </div>
        </CardBoxModal>
    </LayoutAuthenticated>
</template>
