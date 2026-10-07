<script setup>
import { ref } from 'vue'
import { mdiClose, mdiDotsVertical } from '@mdi/js'
import { containerMaxW } from '@admin/config.js'
import BaseIcon from '@admin/components/BaseIcon.vue'
import NavBarMenuList from '@admin/components/NavBarMenuList.vue'
import NavBarItemPlain from '@admin/components/NavBarItemPlain.vue'

defineProps({
  menu: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['menu-click'])

const menuClick = (event, item) => {
  emit('menu-click', event, item)
}

const isMenuNavBarActive = ref(false)
</script>

<template>
  <nav
    class="top-0 inset-x-0 fixed bg-slate-50 h-14 z-30 transition-position w-screen lg:w-auto dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 shadow-sm"
  >
    <div class="flex lg:items-stretch" :class="containerMaxW">
      <div class="flex flex-1 items-stretch h-14">
        <slot />
      </div>
      <div class="flex-none items-stretch flex h-14 lg:hidden">
        <NavBarItemPlain @click.prevent="isMenuNavBarActive = !isMenuNavBarActive">
          <BaseIcon :path="isMenuNavBarActive ? mdiClose : mdiDotsVertical" size="24" />
        </NavBarItemPlain>
      </div>
      <div
        class="max-h-screen-menu overflow-y-auto lg:overflow-visible absolute w-screen top-14 left-0 bg-slate-50 shadow-lg lg:w-auto lg:flex lg:static lg:shadow-none dark:bg-slate-800"
        :class="[isMenuNavBarActive ? 'block' : 'hidden']"
      >
        <NavBarMenuList :menu="menu" @menu-click="menuClick" />
      </div>
    </div>
  </nav>
</template>
