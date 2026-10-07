<script setup>
import { mdiForwardburger, mdiBackburger, mdiBellOutline, mdiShieldCheck, mdiWeatherNight, mdiWeatherSunny, mdiChevronDown, mdiAccountCog, mdiLogout } from '@mdi/js'
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import menuAside from '@admin/menuAside.js'
import { useDarkModeStore } from '@admin/stores/darkMode.js'
import { useMainStore } from '@admin/stores/main.js'
import { useLayoutStore } from '@admin/stores/layout.js'
import BaseIcon from '@admin/components/BaseIcon.vue'
import FormCheckRadio from '@admin/components/FormCheckRadio.vue'
import UserAvatarCurrentUser from '@admin/components/UserAvatarCurrentUser.vue'
import AsideMenu from '@admin/components/AsideMenu.vue'
import FooterBar from '@admin/components/FooterBar.vue'

const layoutAsidePadding = 'xl:pl-64'

const darkModeStore = useDarkModeStore()
const mainStore = useMainStore()
const layoutStore = useLayoutStore()
const page = usePage()

const isUserDropdownOpen = ref(false)
const userDropdownRef = ref(null)

const toggleUserDropdown = () => {
  isUserDropdownOpen.value = !isUserDropdownOpen.value
}

const closeUserDropdown = () => {
  isUserDropdownOpen.value = false
}

const handleOutsideClick = (e) => {
  if (isUserDropdownOpen.value && userDropdownRef.value && !userDropdownRef.value.contains(e.target)) {
    isUserDropdownOpen.value = false
  }
}

onMounted(() => {
  window.addEventListener('click', handleOutsideClick)
})

onUnmounted(() => {
  window.removeEventListener('click', handleOutsideClick)
})

const logout = () => {
  router.post(route('logout'))
}

const props = defineProps({
  isDashboard: Boolean
})

const isDark = computed({
  get: () => darkModeStore.isEnabled,
  set: (value) => darkModeStore.set(value),
})

const userName = computed(() => page.props.auth?.user?.name || mainStore.userName)
const userRoles = computed(() => page.props.auth?.user?.roles || [])
const userPermissions = computed(() => page.props.auth?.user?.permissions || [])
const userHasAnyPermission = computed(() => Boolean(page.props.auth?.user?.has_any_permission))

const mutasiPendingCount = computed(() => Number(page.props?.notifications?.mutasi_pending_count || 0))
const pelanggaranPendingCount = computed(() => Number(page.props?.notifications?.pelanggaran_pending_count || 0))
const totalNotificationCount = computed(() => mutasiPendingCount.value + pelanggaranPendingCount.value)

const navigateNotification = () => {
  if (mutasiPendingCount.value > 0) {
    router.get(route('admin.mutasi_pkl.index'))
  } else if (pelanggaranPendingCount.value > 0) {
    router.get(route('admin.monitoring.index'))
  }
}

const roleSet = computed(() => new Set(userRoles.value))
const permissionSet = computed(() => new Set(userPermissions.value))
const isFullAdmin = computed(() => roleSet.value.has('admin') || roleSet.value.has('super-admin'))

const menuPermissionMap = {
  '/admin': 'view dashboard',
  '/admin/jurusan': 'jurusan.view',
  '/admin/kelas': 'kelas.view',
  '/admin/siswa': 'siswa.view',
  '/admin/jabatan-pembimbing': 'jabatan_pembimbing.view',
  '/admin/pembimbing': 'pembimbing.view',
  '/admin/bengkel': 'bengkel.view',
  '/admin/input-data-pkl': 'input_data_pkl.view',
  '/admin/input-data-pkl/input-pembimbing': 'input_pembimbing.view',
  '/admin/mutasi-pkl': 'mutasi_pkl.view',
  '/admin/monitoring': 'monitoring.view',
  '/admin/monitoring/setting': 'monitoring.view',
  '/admin/rekap-absensi': 'rekap_absensi.view',
  '/admin/rekap-absensi/pembekalan': 'rekap_absensi_pembekalan.view',
  '/admin/pembekalan-aktivasi': 'pembekalan_aktivasi.view',
  '/admin/cetak-p1': 'cetak_p1.view',
  '/admin/surat-pengantar': 'surat_pengantar.view',
  '/admin/rekap-data-pkl': 'rekap_data_pkl.view',
  '/admin/rekap-karir': 'rekap_karir.view',
  '/admin/informasi': 'informasi.view',
  '/admin/rekap-problem': 'rekap_problem.view',
  '/admin/users/pembimbing': 'user_pembimbing.view',
  '/admin/users/siswa': 'user_siswa.view',
  '/admin/data-admin': 'data_admin.view',
  '/admin/roles': 'role.view',
  '/admin/permissions': 'permission.view',
  '/admin/backup': 'backup.view',
}

const hasAccessToPath = (path) => {
  if (isFullAdmin.value) {
    return true
  }

  if (!path || !permissionSet.value) {
    return false
  }

  if (path === '/admin') {
    return permissionSet.value.has('view dashboard') || userHasAnyPermission.value
  }

  const requiredPermission = menuPermissionMap[path]
  if (!requiredPermission) {
    return false
  }

  return permissionSet.value.has(requiredPermission)
}

const cleanupLabelOnlySections = (items) => {
  const result = []
  for (let i = 0; i < items.length; i++) {
    const item = items[i]
    if (!item?.isLabel) {
      result.push(item)
      continue
    }

    const hasFollowingMenu = items.slice(i + 1).some((nextItem) => !nextItem?.isLabel)
    if (hasFollowingMenu) {
      result.push(item)
    }
  }
  return result
}

const filterMenuByPermission = (items) => {
  const filtered = items
    .map((item) => {
      if (item?.isLabel) {
        return item
      }

      if (Array.isArray(item?.menu)) {
        const children = filterMenuByPermission(item.menu)
        if (children.length === 0) {
          return null
        }
        return { ...item, menu: children }
      }

      if (item?.to) {
        return hasAccessToPath(item.to) ? item : null
      }

      return null
    })
    .filter(Boolean)

  return cleanupLabelOnlySections(filtered)
}

const filteredMenuAside = computed(() => filterMenuByPermission(menuAside))

router.on('navigate', () => {
  layoutStore.isAsideMobileExpanded = false
  layoutStore.isAsideLgActive = false
})

const menuClick = (event, item) => {
  if (item.isToggleLightDark) {
    darkModeStore.set()
  }

  if (item.isLogout) {
    router.post('/logout')
  }
}
</script>

<template>
  <div
    :class="{
      'overflow-hidden lg:overflow-visible': layoutStore.isAsideMobileExpanded,
    }"
  >
      <div
        :class="[
          { 'lg:pl-[268px]': layoutStore.isSidebarVisible },
        ]"
      class="min-h-screen w-screen transition-all duration-300 ease-in-out lg:w-auto bg-zinc-100/90 text-zinc-800 dark:bg-slate-950 dark:text-slate-100"
    >
      <!-- Modern Top Navigation Header Bar -->
      <header
        class="sticky top-0 z-30 bg-white dark:bg-slate-900 border-b border-zinc-200/90 dark:border-slate-800/80 px-6 py-3.5 flex items-center justify-between shadow-xs transition-colors"
      >
        <div class="flex items-center gap-3">
          <button
            @click.prevent="layoutStore.toggleMobileSidebar"
            class="lg:hidden p-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-all flex-shrink-0"
            aria-label="Toggle mobile sidebar"
          >
            <BaseIcon :path="layoutStore.isAsideMobileExpanded ? mdiBackburger : mdiForwardburger" size="22" />
          </button>

          <button
            @click.prevent="layoutStore.toggleSidebar"
            class="hidden lg:inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-all flex-shrink-0"
            :class="{'rotate-180': !layoutStore.isSidebarVisible}"
            aria-label="Toggle desktop sidebar"
          >
            <BaseIcon :path="mdiBackburger" size="22" />
          </button>

          <!-- System Brand Title / Breadcrumb Header -->
          <div class="flex items-center gap-2">
            <div class="hidden sm:flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400">
              <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50">SIM PKL</span>
              <span>/</span>
              <span class="text-slate-800 dark:text-slate-200 font-bold">Panel Admin</span>
            </div>
          </div>
        </div>

        <!-- Top Right Actions & User Profile Pill -->
        <div class="flex items-center gap-3 sm:gap-4">
          <!-- Notification Quick Badge -->
          <div v-if="totalNotificationCount > 0" class="relative group">
            <button
              @click="navigateNotification"
              class="p-2 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800/60 hover:bg-amber-100 dark:hover:bg-amber-900/60 transition-all flex items-center gap-1.5 text-xs font-bold"
              :title="mutasiPendingCount > 0 ? (mutasiPendingCount + ' Pengajuan Mutasi PKL Masuk') : (pelanggaranPendingCount + ' Laporan Pelanggaran PKL Menunggu') + ' (Klik untuk membuka)'"
            >
              <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
              </span>
              <BaseIcon :path="mdiBellOutline" size="18" />
              <span class="hidden sm:inline">{{ totalNotificationCount }} Notifikasi</span>
            </button>
          </div>

          <!-- Dark/Light Theme Switcher -->
          <button
            @click="darkModeStore.set()"
            class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-amber-400 transition-all"
            :title="isDark ? 'Switch ke Mode Terang' : 'Switch ke Mode Gelap'"
          >
            <BaseIcon :path="isDark ? mdiWeatherSunny : mdiWeatherNight" size="20" />
          </button>

          <!-- User Profile Dropdown Pill -->
          <div ref="userDropdownRef" class="relative pl-2 sm:pl-3 border-l border-slate-200 dark:border-slate-800">
            <button
              @click.stop="toggleUserDropdown"
              class="flex items-center gap-2.5 p-1.5 rounded-2xl hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-all cursor-pointer select-none group"
              :class="{ 'bg-slate-100 dark:bg-slate-800/80 ring-2 ring-indigo-500/20': isUserDropdownOpen }"
            >
              <UserAvatarCurrentUser class="w-8 h-8 rounded-full ring-2 ring-indigo-500/40 group-hover:ring-indigo-500 transition-all" />
              <div class="hidden sm:flex flex-col text-left">
                <span class="text-xs font-bold text-slate-800 dark:text-slate-100 leading-tight flex items-center gap-1">
                  {{ userName }}
                  <BaseIcon :path="mdiChevronDown" size="14" class="text-slate-400 group-hover:text-indigo-500 transition-transform duration-200" :class="{ 'rotate-180': isUserDropdownOpen }" />
                </span>
                <span class="text-[10px] uppercase font-mono font-semibold text-indigo-600 dark:text-indigo-400">
                  Administrator
                </span>
              </div>
            </button>

            <!-- Dropdown Popover Container -->
            <transition
              enter-active-class="transition duration-150 ease-out"
              enter-from-class="transform scale-95 opacity-0"
              enter-to-class="transform scale-100 opacity-100"
              leave-active-class="transition duration-100 ease-in"
              leave-from-class="transform scale-100 opacity-100"
              leave-to-class="transform scale-95 opacity-0"
            >
              <div
                v-if="isUserDropdownOpen"
                class="absolute right-0 mt-2 w-56 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-2xl py-2 z-50 overflow-hidden"
              >
                <!-- Dropdown Header Info -->
                <div class="px-4 py-2.5 border-b border-slate-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-900/50">
                  <p class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate">{{ userName }}</p>
                  <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ page.props.auth?.user?.email || 'admin@stemda.sch.id' }}</p>
                </div>

                <!-- Dropdown Action Items -->
                <div class="p-1.5 space-y-1">
                  <button
                    @click="closeUserDropdown(); router.get(route('profile.edit'))"
                    class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-semibold text-slate-700 dark:text-slate-200 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-xl transition-colors text-left"
                  >
                    <BaseIcon :path="mdiAccountCog" size="18" class="text-slate-400" />
                    <span>Profil Saya</span>
                  </button>

                  <button
                    @click="closeUserDropdown(); logout()"
                    class="w-full flex items-center gap-2.5 px-3 py-2 text-xs font-bold text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-xl transition-colors text-left"
                  >
                    <BaseIcon :path="mdiLogout" size="18" class="text-rose-500" />
                    <span>Logout (Keluar)</span>
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </div>
      </header>

      <AsideMenu
        :is-aside-mobile-expanded="layoutStore.isAsideMobileExpanded"
        :is-aside-lg-active="layoutStore.isAsideLgActive"
        :is-sidebar-visible="layoutStore.isSidebarVisible"
        :menu="filteredMenuAside"
        @menu-click="menuClick"
        @aside-lg-close-click="layoutStore.closeMobileSidebar(); layoutStore.isAsideLgActive = false"
      />
      <slot />
      <FooterBar>
      </FooterBar>
    </div>
  </div>
</template>
