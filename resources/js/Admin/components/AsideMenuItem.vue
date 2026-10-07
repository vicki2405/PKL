<script setup>
import { ref, computed, watch } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { mdiMinus, mdiPlus } from '@mdi/js'
import { getButtonColor } from '@admin/colors.js'
import BaseIcon from '@admin/components/BaseIcon.vue'
import AsideMenuList from '@admin/components/AsideMenuList.vue'
import menuAside from '@admin/menuAside.js'

const props = defineProps({
  item: {
    type: Object,
    required: true,
  },
  isDropdownList: Boolean,
})

const emit = defineEmits(['menu-click'])

const page = usePage()
const hasColor = computed(() => props.item && props.item.color)

const isDropdownActive = ref(false)

const getAllMenuTos = (menuList) => {
  const result = []
  if (!Array.isArray(menuList)) return result
  for (const m of menuList) {
    if (m?.to) result.push(m.to)
    if (Array.isArray(m?.menu)) {
      result.push(...getAllMenuTos(m.menu))
    }
  }
  return result
}

const allMenuTos = getAllMenuTos(menuAside)

const currentPath = computed(() => {
  try {
    const rawUrl = page?.url || ''
    return rawUrl.split('?')[0].split('#')[0]
  } catch (e) {
    return ''
  }
})

const isActive = computed(() => {
  if (props.item.to) {
    try {
      const path = currentPath.value
      if (path === props.item.to) return true
      if (props.item.to !== '/admin' && path.length > 0 && path.startsWith(props.item.to + '/')) {
        // Jangan aktifkan prefix menu jika ada menu lain di sidebar yang exact match
        const hasExactMatchOtherItem = allMenuTos.some(to => to === path)
        if (hasExactMatchOtherItem) {
          return false
        }
        // Jangan aktifkan jika ada menu lain yang prefix-nya lebih spesifik
        const hasMoreSpecificMatch = allMenuTos.some(to => to !== props.item.to && to.startsWith(props.item.to + '/') && path.startsWith(to))
        if (hasMoreSpecificMatch) {
          return false
        }
        return true
      }
    } catch (e) {
      return false
    }
  }
  return false
})

const hasActiveInMenu = (menu, path) => {
  if (!Array.isArray(menu)) return false

  for (const child of menu) {
    if (child?.to) {
      if (path === child.to) return true
      if (child.to !== '/admin' && path.length > 0 && path.startsWith(child.to + '/')) return true
    }
    if (child?.menu && hasActiveInMenu(child.menu, path)) return true
  }

  return false
}

const componentClass = computed(() => {
  const base = []

  if (props.isDropdownList) {
    base.push('py-2 pl-6 pr-3 text-xs font-semibold my-0.5 rounded-lg mx-1 transition-all duration-200')
  } else {
    base.push('py-2.5 px-3 min-h-[42px] my-0.5 rounded-xl mx-2 text-sm font-semibold transition-all duration-200')
  }

  if (hasColor.value) {
    base.push(getButtonColor(props.item.color, false, true))
  } else if (isActive.value) {
    if (props.isDropdownList) {
      base.push('bg-indigo-600 dark:bg-indigo-600 text-white dark:text-white font-bold shadow-md shadow-indigo-600/30')
    } else {
      base.push('bg-indigo-600 dark:bg-indigo-600 text-white dark:text-white font-bold shadow-md shadow-indigo-600/25')
    }
  } else if (isDropdownRouteActive.value && hasDropdown.value) {
    base.push('text-indigo-600 dark:text-indigo-400 bg-indigo-50/70 dark:bg-indigo-950/40')
  } else {
    if (props.isDropdownList) {
      base.push('text-slate-600 dark:text-slate-300 hover:bg-slate-200/70 dark:hover:bg-slate-800/80 hover:text-indigo-600 dark:hover:text-indigo-300')
    } else {
      base.push('text-slate-600 dark:text-slate-300 hover:bg-slate-100/90 dark:hover:bg-slate-800/70 hover:text-indigo-600 dark:hover:text-indigo-300')
    }
  }

  return base
})

const hasDropdown = computed(() => !!props.item.menu)

const mutasiPendingCount = computed(() => Number(page.props?.notifications?.mutasi_pending_count || 0))
const showMutasiBadge = computed(() => props.item?.to === '/admin/mutasi-pkl' && mutasiPendingCount.value > 0)
const pelanggaranPendingCount = computed(() => Number(page.props?.notifications?.pelanggaran_pending_count || 0))
const showPelanggaranBadge = computed(() => {
  if (pelanggaranPendingCount.value <= 0) return false
  if (props.item?.to === '/admin/monitoring') return true
  if (!isDropdownActive.value && Array.isArray(props.item?.menu)) {
    return props.item.menu.some(child => child?.to === '/admin/monitoring')
  }
  return false
})

const isDropdownRouteActive = computed(() => {
  try {
    return hasActiveInMenu(props.item.menu, currentPath.value)
  } catch (e) {
    return false
  }
})

watch(
  () => isDropdownRouteActive.value,
  (active) => {
    if (active) {
      isDropdownActive.value = true
    }
  },
  { immediate: true },
)

const menuClick = (event) => {
  if (!props.item.to && !props.item.href) {
    event.preventDefault()
  }
  emit('menu-click', event, props.item)

  if (hasDropdown.value) {
    isDropdownActive.value = !isDropdownActive.value
  }
}
</script>

<template>
  <li>
    <div
      v-if="item.isLabel"
      class="px-4 pt-4 pb-1 text-[11px] uppercase tracking-widest text-slate-400 dark:text-slate-500 font-bold"
    >
      {{ item.label }}
    </div>
    <component
      v-else
      :is="item.to ? Link : 'a'"
      :href="item.to ?? item.href ?? null"
      :target="item.target ?? null"
      class="flex cursor-pointer w-full items-center group"
      :class="componentClass"
      :preserve-scroll="!!item.to"
      @click="menuClick"
    >
      <BaseIcon
        v-if="item.icon"
        :path="item.icon"
        class="flex-none mr-2.5 transition-transform duration-200 group-hover:scale-110"
        :class="{ 'text-white': isActive }"
        :size="18"
      />
      <span
        class="flex-1 truncate"
        >{{ item.label }}</span
      >
      <span
        v-if="showMutasiBadge"
        class="ml-2 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-500 text-white shadow-xs animate-pulse"
      >
        {{ mutasiPendingCount }}
      </span>
      <span
        v-if="showPelanggaranBadge"
        class="ml-2 px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-red-600 text-white shadow-xs animate-pulse"
      >
        {{ pelanggaranPendingCount }}
      </span>
      <BaseIcon
        v-if="hasDropdown"
        :path="isDropdownActive ? mdiMinus : mdiPlus"
        class="flex-none ml-1 opacity-70"
        :size="16"
      />
    </component>
    <AsideMenuList
      v-if="hasDropdown"
      :menu="item.menu"
      :class="['aside-menu-dropdown transition-all duration-200', isDropdownActive ? 'block' : 'hidden']"
      is-dropdown-list
    />
  </li>
</template>
