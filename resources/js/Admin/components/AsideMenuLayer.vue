<script setup>
import { mdiLogout, mdiClose, mdiShieldCheck } from '@mdi/js'
import { computed, onMounted, onBeforeUnmount, ref } from 'vue'
import AsideMenuList from '@admin/components/AsideMenuList.vue'
import AsideMenuItem from '@admin/components/AsideMenuItem.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'

defineProps({
  menu: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['menu-click', 'aside-lg-close-click'])

const logoutItem = computed(() => ({
  label: 'Logout Keluar',
  icon: mdiLogout,
  isLogout: true,
}))

const menuClick = (event, item) => {
  emit('menu-click', event, item)
}

const asideLgCloseClick = (event) => {
  emit('aside-lg-close-click', event)
}

const menuScrollEl = ref(null)
let rafId = null

const persistScroll = () => {
  if (!menuScrollEl.value) return
  try {
    localStorage.setItem('admin_aside_scrollTop', String(menuScrollEl.value.scrollTop || 0))
  } catch (e) {
    // no-op
  }
}

const onScroll = () => {
  if (rafId) cancelAnimationFrame(rafId)
  rafId = requestAnimationFrame(persistScroll)
}

onMounted(() => {
  if (!menuScrollEl.value) return
  try {
    const stored = localStorage.getItem('admin_aside_scrollTop')
    if (stored !== null) {
      const top = Number(stored)
      if (Number.isFinite(top)) {
        menuScrollEl.value.scrollTop = top
      }
    }
  } catch (e) {
    // no-op
  }
  menuScrollEl.value.addEventListener('scroll', onScroll, { passive: true })
})

onBeforeUnmount(() => {
  if (rafId) cancelAnimationFrame(rafId)
  rafId = null
  if (!menuScrollEl.value) return
  try {
    menuScrollEl.value.removeEventListener('scroll', onScroll)
  } catch (e) {
    // no-op
  }
})
</script>

<template>
  <aside
    id="aside"
    class="lg:py-3 lg:pl-3 w-72 sm:w-64 fixed flex z-40 top-0 left-0 h-screen transition-all duration-300 ease-in-out select-none"
  >
    <div class="aside lg:rounded-2xl flex-1 flex flex-col overflow-hidden bg-white text-zinc-800 dark:bg-slate-900 dark:text-slate-100 shadow-2xl border border-zinc-200/90 dark:border-slate-800/90 backdrop-blur-xl">
      <!-- Sidebar Brand Header -->
      <div class="aside-brand flex flex-row min-h-[64px] items-center justify-between bg-gradient-to-r from-zinc-900 via-indigo-950 to-zinc-900 text-white px-4 border-b border-zinc-800 shadow-sm">
        <div class="flex items-center gap-3 flex-1">
          <div class="w-9 h-9 rounded-xl bg-white/10 p-1 flex justify-center items-center flex-shrink-0 backdrop-blur-md ring-1 ring-white/20 shadow-md">
            <img
              src="/logo2.png"
              alt="PKL STEMDA"
              class="w-full h-full rounded-lg object-cover"
            />
          </div>
          <div class="flex flex-col">
            <span class="font-extrabold text-white text-sm tracking-wide leading-tight">SIM PKL</span>
            <span class="text-[10px] text-indigo-200 uppercase font-mono tracking-widest font-bold">STEMDA PANEL</span>
          </div>
        </div>
        <button class="inline-block xl:hidden p-2 rounded-lg hover:bg-white/10 text-white transition-colors" @click.prevent="asideLgCloseClick" title="Tutup Menu">
          <BaseIcon :path="mdiClose" size="20" />
        </button>
      </div>

      <!-- Navigation Links Container -->
      <div
        class="flex-1 py-3 overflow-y-auto overflow-x-hidden aside-scrollbars aside-scrollbars-[slate] dark:aside-scrollbars-[slate] touch-pan-y"
        ref="menuScrollEl"
      >
        <AsideMenuList :menu="menu" @menu-click="menuClick" />
      </div>
    </div>
  </aside>
</template>
