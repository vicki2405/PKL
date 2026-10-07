import {
  mdiMonitor,
  mdiGithub,
  mdiTable,
  mdiViewList,
  mdiReact,
  mdiHumanMaleBoard,
  mdiSitemap,
  mdiAccountTie,
  mdiOfficeBuildingCog,
  mdiWrench,
  mdiDomain,
  mdiGoogleClassroom,
  mdiAccountSchool,
  mdiFileDocumentEdit,
  mdiTransfer,
  mdiPrinter,
  mdiFileDocument,
  mdiAccountGroup,
  mdiShieldAccount,
  mdiShieldCheck,
  mdiClipboardList,
  mdiAlertCircle,
  mdiDatabase,
  mdiCalendarPlus,
  mdiBriefcase,
  mdiCalendarClock,
  mdiClipboardText,
  mdiAccountSwitch,
} from '@mdi/js'

export default [
  {
    label: 'General',
    isLabel: true,
  },
  {
    to: '/admin',
    icon: mdiMonitor,
    label: 'Dashboard',
  },
  {
    label: 'Master Data',
    isLabel: true,
  },
  {
    label: 'Master Siswa',
    icon: mdiViewList,
    menu: [
      {
        to: '/admin/jurusan',
        label: 'Data Jurusan',
        icon: mdiDomain,
      },
      {
        to: '/admin/kelas',
        label: 'Data Kelas',
        icon: mdiGoogleClassroom,
      },
      {
        to: '/admin/siswa',
        label: 'Data Siswa',
        icon: mdiAccountSchool,
      },
    ],
  },
  {
    label: 'Master Pembimbing',
    icon: mdiHumanMaleBoard,
    menu: [
      {
        to: '/admin/jabatan-pembimbing',
        label: 'Data Jabatan',
        icon: mdiSitemap,
      },
      {
        to: '/admin/pembimbing',
        label: 'Data Pembimbing',
        icon: mdiAccountTie,
      },
    ],
  },
  {
    label: 'Master Bengkel',
    icon: mdiOfficeBuildingCog,
    menu: [
      {
        to: '/admin/bengkel',
        label: 'Data Bengkel',
        icon: mdiWrench,
      },
    ],
  },
  {
    label: 'Manajemen PKL',
    isLabel: true,
  },
  {
    label: 'Input Data PKL',
    icon: mdiFileDocumentEdit,
    menu: [
      {
        to: '/admin/input-data-pkl',
        label: 'Data Penempatan',
        icon: mdiFileDocumentEdit,
      },
      {
        to: '/admin/input-data-pkl/input-pembimbing',
        label: 'Input Pembimbing',
        icon: mdiHumanMaleBoard,
      },
      {
        to: '/admin/monitoring-penempatan',
        label: 'Riwayat Pindah Siswa',
        icon: mdiAccountSwitch,
      },
    ],
  },
  {
    to: '/admin/mutasi-pkl',
    label: 'Mutasi PKL',
    icon: mdiTransfer,
  },
  {
    label: 'Monitoring PKL',
    icon: mdiMonitor,
    menu: [
      {
        to: '/admin/monitoring',
        label: 'Rekap Monitoring',
        icon: mdiClipboardText,
      },
      {
        to: '/admin/monitoring/setting',
        label: 'Pengaturan Periode PKL',
        icon: mdiCalendarClock,
      },
    ],
  },
  {
    label: 'Rekap Absensi',
    icon: mdiClipboardList,
    menu: [
      {
        to: '/admin/rekap-absensi',
        label: 'Rekap Absensi',
        icon: mdiClipboardList,
      },
      {
        to: '/admin/pembekalan-aktivasi',
        label: 'Aktivasi Pembekalan',
        icon: mdiCalendarPlus,
      },
    ],
  },
  {
    label: 'Cetak Berkas PKL',
    icon: mdiPrinter,
    menu: [
      {
        to: '/admin/cetak-p1',
        label: 'Cetak P1',
        icon: mdiFileDocument,
      },
      {
        to: '/admin/surat-pengantar',
        label: 'Surat Pengantar',
        icon: mdiFileDocument,
      },
    ],
  },
  {
    to: '/admin/rekap-data-pkl',
    label: 'Data Pembimbing PKL',
    icon: mdiTable,
  },
  {
    to: '/admin/rekap-karir',
    label: 'Aspirasi Karier',
    icon: mdiBriefcase,
  },
  {
    to: '/admin/informasi',
    label: 'Informasi PKL/Berita',
    icon: mdiFileDocumentEdit,
  },
  {
    to: '/admin/rekap-problem',
    label: 'Rekap Problem',
    icon: mdiAlertCircle,
  },
  {
    label: 'Manajemen User',
    isLabel: true,
  },
  {
    to: '/admin/users/pembimbing',
    label: 'Pembimbing',
    icon: mdiAccountTie,
  },
  {
    to: '/admin/users/siswa',
    label: 'Siswa',
    icon: mdiAccountSchool,
  },
  {
    label: 'Manajemen Admin',
    isLabel: true,
  },
  {
    to: '/admin/data-admin',
    label: 'Data Admin',
    icon: mdiAccountGroup,
  },
  {
    to: '/admin/roles',
    label: 'Role',
    icon: mdiShieldAccount,
  },
  {
    to: '/admin/permissions',
    label: 'Permission',
    icon: mdiShieldCheck,
  },
  {
    to: '/admin/backup',
    label: 'Backup Data',
    icon: mdiDatabase,
  },
]
