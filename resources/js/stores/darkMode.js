import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useDarkModeStore = defineStore('darkMode', () => {
  const isEnabled = ref(false)

  function set(payload = null) {
    isEnabled.value = payload !== null ? payload : !isEnabled.value

    if (typeof document !== 'undefined') {
      document.documentElement.classList[isEnabled.value ? 'add' : 'remove']('dark')
    }
  }

  return {
    isEnabled,
    set,
  }
})
