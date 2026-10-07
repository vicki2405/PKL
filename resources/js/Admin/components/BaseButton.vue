<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import {
  mdiCloudDownload,
  mdiCloudUpload,
  mdiContentSave,
  mdiFileExcel,
  mdiFilePdfBox,
  mdiPencil,
  mdiPlus,
  mdiPrinter,
  mdiTrashCan,
  mdiUpload,
} from '@mdi/js'
import { getButtonColor } from '@admin/colors.js'
import BaseIcon from '@admin/components/BaseIcon.vue'

const props = defineProps({
  label: {
    type: [String, Number],
    default: null,
  },
  icon: {
    type: String,
    default: null,
  },
  iconSize: {
    type: [String, Number],
    default: null,
  },
  href: {
    type: String,
    default: null,
  },
  target: {
    type: String,
    default: null,
  },
  to: {
    type: [String, Object],
    default: null,
  },
  type: {
    type: String,
    default: null,
  },
  color: {
    type: String,
    default: 'white',
  },
  as: {
    type: String,
    default: null,
  },
  small: Boolean,
  outline: Boolean,
  active: Boolean,
  disabled: Boolean,
  roundedFull: Boolean,
  permission: {
    type: String,
    default: null,
  },
  autoPermission: {
    type: Boolean,
    default: true,
  },
})

const page = usePage()
const routePermissionMap = {
  '/admin/jurusan': 'jurusan',
  '/admin/kelas': 'kelas',
  '/admin/siswa': 'siswa',
  '/admin/jabatan-pembimbing': 'jabatan_pembimbing',
  '/admin/pembimbing': 'pembimbing',
  '/admin/bengkel': 'bengkel',
  '/admin/input-data-pkl': 'input_data_pkl',
  '/admin/mutasi-pkl': 'mutasi_pkl',
  '/admin/monitoring': 'monitoring',
  '/admin/rekap-absensi': 'rekap_absensi',
  '/admin/rekap-absensi/pembekalan': 'rekap_absensi_pembekalan',
  '/admin/cetak-p1': 'cetak_p1',
  '/admin/surat-pengantar': 'surat_pengantar',
  '/admin/rekap-data-pkl': 'rekap_data_pkl',
  '/admin/informasi': 'informasi',
  '/admin/rekap-problem': 'rekap_problem',
  '/admin/users/pembimbing': 'user_pembimbing',
  '/admin/users/siswa': 'user_siswa',
  '/admin/data-admin': 'data_admin',
  '/admin/roles': 'role',
  '/admin/permissions': 'permission',
  '/admin/backup': 'backup',
}

const iconActionMap = {
  [mdiPlus]: 'create',
  [mdiPencil]: 'edit',
  [mdiTrashCan]: 'delete',
  [mdiFileExcel]: 'export',
  [mdiFilePdfBox]: 'export',
  [mdiCloudDownload]: 'export',
  [mdiUpload]: 'import',
  [mdiCloudUpload]: 'import',
  [mdiPrinter]: 'print',
  [mdiContentSave]: 'edit',
}

const resolveResource = () => {
  const currentPath = String(page.url || '').split('?')[0]
  const entries = Object.entries(routePermissionMap).sort((a, b) => b[0].length - a[0].length)

  for (const [routePrefix, resource] of entries) {
    if (currentPath === routePrefix || currentPath.startsWith(`${routePrefix}/`)) {
      return resource
    }
  }

  return null
}

const resolveActionByIcon = () => {
  if (!props.icon || !props.autoPermission) return null
  const mappedAction = iconActionMap[props.icon]
  if (mappedAction === 'edit' && props.icon === mdiContentSave) {
    return String(page.url || '').includes('/create') ? 'create' : 'edit'
  }

  return mappedAction || null
}

const canRender = computed(() => {
  const user = page.props.auth?.user
  if (!user) return true

  const rawRoles = user.roles || []
  const roleNames = Array.isArray(rawRoles)
    ? rawRoles.map((r) => (typeof r === 'string' ? r : r?.name || ''))
    : []

  if (roleNames.includes('admin') || roleNames.includes('super-admin')) return true

  const rawPermissions = user.permissions || []
  const permissionNames = Array.isArray(rawPermissions)
    ? rawPermissions.map((p) => (typeof p === 'string' ? p : p?.name || ''))
    : []

  if (props.permission) {
    return permissionNames.includes(props.permission)
  }

  const resource = resolveResource()
  const action = resolveActionByIcon()
  if (!resource || !action) {
    return true
  }

  return permissionNames.includes(`${resource}.${action}`)
})

const is = computed(() => {
  if (props.as) {
    return props.as
  }

  if (props.to) {
    return Link
  }

  if (props.href) {
    return 'a'
  }

  return 'button'
})

const computedType = computed(() => {
  if (is.value === 'button') {
    return props.type ?? 'button'
  }

  return null
})

const labelClass = computed(() => (props.small && props.icon ? 'px-1' : 'px-2'))

const componentClass = computed(() => {
  const base = [
    'inline-flex',
    'justify-center',
    'items-center',
    'whitespace-nowrap',
    'focus:outline-hidden',
    'transition-all',
    'duration-200',
    'active:scale-[0.98]',
    'font-bold',
    'border',
    props.disabled ? 'cursor-not-allowed' : 'cursor-pointer',
    'select-none',
    props.roundedFull ? 'rounded-full' : 'rounded-xl sm:rounded-2xl',
    getButtonColor(props.color, props.outline, !props.disabled, props.active),
  ]

  if (!props.label && props.icon) {
    base.push('p-1.5 sm:p-1')
  } else if (props.small) {
    base.push('text-sm min-h-[32px] sm:min-h-[28px]', props.roundedFull ? 'px-3 py-1.5 sm:py-1' : 'p-1.5 sm:p-1')
  } else {
    base.push('py-2.5 sm:py-2 min-h-[44px] sm:min-h-[40px]', props.roundedFull ? 'px-5 sm:px-6' : 'px-4 sm:px-3')
  }

  if (props.disabled) {
    base.push(props.outline ? 'opacity-50' : 'opacity-70')
  }

  return base
})
</script>

<template>
  <component
    v-if="canRender"
    :is="is"
    :class="componentClass"
    :href="to ?? href"
    :type="computedType"
    :target="target"
    :disabled="disabled"
    :preserve-scroll="!!to"
  >
    <BaseIcon v-if="icon" :path="icon" :size="iconSize" />
    <span v-if="label" :class="labelClass">{{ label }}</span>
  </component>
</template>
