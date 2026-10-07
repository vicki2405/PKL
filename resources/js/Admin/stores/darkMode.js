import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useDarkModeStore = defineStore('darkMode', () => {
  const stored = typeof localStorage !== 'undefined' ? localStorage.getItem('darkMode') : null
  const isEnabled = ref(stored !== null ? stored === '1' : false)

  function set(payload = null) {
    isEnabled.value = payload !== null ? payload : !isEnabled.value

    if (typeof document !== 'undefined') {
      document.body.classList[isEnabled.value ? 'add' : 'remove']('dark-scrollbars')

      document.documentElement.classList[isEnabled.value ? 'add' : 'remove'](
        'dark',
        'dark-scrollbars-compat',
      )

      if (typeof localStorage !== 'undefined') {
        localStorage.setItem('darkMode', isEnabled.value ? '1' : '0')
      }
    }
  }

  // Initialize class on load
  if (typeof document !== 'undefined') {
    if (isEnabled.value) {
      document.documentElement.classList.add('dark', 'dark-scrollbars-compat')
      document.body.classList.add('dark-scrollbars')
    } else {
      document.documentElement.classList.remove('dark', 'dark-scrollbars-compat')
      document.body.classList.remove('dark-scrollbars')
    }
  }

  return {
    isEnabled,
    set,
  }
})
