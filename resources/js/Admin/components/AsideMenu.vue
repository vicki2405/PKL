<script setup>
import AsideMenuLayer from '@admin/components/AsideMenuLayer.vue'
import OverlayLayer from '@admin/components/OverlayLayer.vue'

defineProps({
  menu: {
    type: Array,
    required: true,
  },
  isAsideMobileExpanded: Boolean,
  isAsideLgActive: Boolean,
  isSidebarVisible: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['menu-click', 'aside-lg-close-click'])

const menuClick = (event, item) => {
  emit('menu-click', event, item)
}

const asideLgCloseClick = (event) => {
  emit('aside-lg-close-click', event)
}
</script>

<template>
  <AsideMenuLayer
    :menu="menu"
    :class="[
      isAsideMobileExpanded ? 'left-0' : '-left-72 sm:-left-64 lg:left-0',
      { 'hidden lg:flex': isSidebarVisible && !isAsideMobileExpanded, 'hidden': !isSidebarVisible && !isAsideMobileExpanded }
    ]"
    @menu-click="menuClick"
    @aside-lg-close-click="asideLgCloseClick"
  />
  <OverlayLayer v-show="isAsideLgActive || isAsideMobileExpanded" z-index="z-30" @overlay-click="asideLgCloseClick" />
</template>
