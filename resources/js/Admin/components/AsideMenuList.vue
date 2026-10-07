<script setup>
import AsideMenuItem from '@admin/components/AsideMenuItem.vue'

defineProps({
  isDropdownList: Boolean,
  menu: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['menu-click'])

const menuClick = (event, item) => {
  emit('menu-click', event, item)
}
</script>

<template>
  <ul :class="[isDropdownList ? 'ml-3 my-1 p-1 space-y-0.5 rounded-xl bg-slate-100/70 dark:bg-slate-950/70 border border-slate-200/60 dark:border-slate-800/80 shadow-inner' : 'space-y-0.5']">
    <AsideMenuItem
      v-for="(item, index) in menu"
      :key="index"
      :item="item"
      :is-dropdown-list="isDropdownList"
      @menu-click="menuClick"
    />
  </ul>
</template>
