import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useLayoutStore = defineStore('layout', () => {
  const isAsideMobileExpanded = ref(false)
  const isAsideLgActive = ref(false)
  const isSidebarVisible = ref(true)

  function toggleSidebar() {
    isSidebarVisible.value = !isSidebarVisible.value
  }

  function toggleMobileSidebar() {
    isAsideMobileExpanded.value = !isAsideMobileExpanded.value
  }

  function closeMobileSidebar() {
    isAsideMobileExpanded.value = false
  }

  return {
    isAsideMobileExpanded,
    isAsideLgActive,
    isSidebarVisible,
    toggleSidebar,
    toggleMobileSidebar,
    closeMobileSidebar
  }
})
