<script setup>
import { Link } from '@inertiajs/vue3'
import { useDarkModeStore } from '@/stores/darkMode'
import { ref } from 'vue'

const darkModeStore = useDarkModeStore()

const isAsideMobileExpanded = ref(false)
const isAsideLgActive = ref(false)

const menuAside = [
  { label: 'Dashboard', route: 'admin.dashboard' },
  { label: 'Forms', route: 'admin.forms', disabled: true },
  { label: 'UI', route: 'admin.ui', disabled: true },
]
</script>

<template>
  <div
    :class="{
      'overflow-hidden lg:overflow-visible': isAsideMobileExpanded,
    }"
  >
    <div
      :class="[
        'pt-14 min-h-screen w-screen lg:w-auto bg-gray-50 dark:bg-slate-800 dark:text-slate-100 transition-all duration-300 ease-in-out',
        { 'ml-64 lg:ml-0': isAsideMobileExpanded },
      ]"
    >
      <header
        :class="[
          'fixed top-0 inset-x-0 z-30 bg-white dark:bg-slate-900 border-b border-gray-200 dark:border-slate-700 flex items-center transition-all duration-300 ease-in-out min-h-[56px]',
          { 'ml-64 lg:ml-0': isAsideMobileExpanded },
        ]"
      >
        <div class="flex items-center gap-2 sm:gap-3 px-3 sm:px-4 w-full">
          <button
            class="flex lg:hidden items-center justify-center w-10 h-10 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 flex-shrink-0"
            @click.prevent="isAsideMobileExpanded = !isAsideMobileExpanded"
            aria-label="Toggle menu"
          >
            <svg v-if="!isAsideMobileExpanded" class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
              <path d="M3 6h18v2H3V6Zm3 5h12v2H6v-2Zm-3 5h18v2H3v-2Z" />
            </svg>
            <svg v-else class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor">
              <path d="M5 6h14v2H5V6Zm0 5h9v2H5v-2Zm0 5h14v2H5v-2Z" />
            </svg>
          </button>

          <Link :href="route('admin.dashboard')" class="inline-flex items-center gap-2 font-semibold flex-shrink-0">
            <img
              src="/logo2.png"
              alt="PKL STEMDA"
              class="w-7 h-7 sm:w-8 sm:h-8 rounded-full object-cover"
            />
            <span class="text-gray-800 dark:text-slate-100 text-sm sm:text-base">PKL STEMDA</span>
          </Link>

          <div class="hidden lg:flex items-center ml-6 flex-1 max-w-md">
            <div class="relative w-full">
              <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                  <path
                    d="M15.5 14h-.79l-.28-.27A6.471 6.471 0 0 0 16 9.5 6.5 6.5 0 1 0 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79L20 21.49 21.49 20 15.5 14Zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14Z"
                  />
                </svg>
              </span>
              <input
                type="text"
                class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                placeholder="Search (Ctrl + K)"
              />
            </div>
          </div>

          <div class="flex items-center gap-2 ml-auto">
            <button
              class="p-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 dark:text-slate-300 dark:hover:bg-slate-800"
              @click="darkModeStore.set()"
              aria-label="Toggle dark mode"
            >
              <svg v-if="!darkModeStore.isEnabled" class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M12 3a1 1 0 0 1 1 1v1.06a7.002 7.002 0 0 1 5.94 5.94H20a1 1 0 1 1 0 2h-1.06A7.002 7.002 0 0 1 13 18.94V20a1 1 0 1 1-2 0v-1.06A7.002 7.002 0 0 1 5.06 13H4a1 1 0 1 1 0-2h1.06A7.002 7.002 0 0 1 11 5.06V4a1 1 0 0 1 1-1Z"
                />
              </svg>
              <svg v-else class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                <path
                  d="M21 12.79A9 9 0 0 1 12.21 3 7 7 0 1 0 21 12.79ZM12 20a8 8 0 0 0 7.75-6.02A9.004 9.004 0 0 1 9.98 4.25 7 7 0 0 0 12 20Z"
                />
              </svg>
            </button>

            <Link
              :href="route('logout')"
              method="post"
              as="button"
              class="hidden sm:inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium bg-indigo-600 text-white hover:bg-indigo-500 min-h-[32px]"
            >
              Logout
            </Link>
          </div>
        </div>
      </header>

      <aside
        :class="[
          'fixed inset-y-0 left-0 z-40 w-64 sm:w-60 bg-white dark:bg-slate-900 border-r border-gray-200 dark:border-slate-800 transform transition-all duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto lg:h-auto lg:z-auto',
          {
            '-translate-x-full lg:translate-x-0': !isAsideMobileExpanded,
            'translate-x-0': isAsideMobileExpanded,
          },
        ]"
      >
        <div class="min-h-[56px] flex items-center px-4 border-b border-gray-200 dark:border-slate-800 lg:hidden">
          <span class="font-semibold text-gray-700 dark:text-slate-100">Menu</span>
        </div>
        <nav class="h-[calc(100vh-3.5rem)] lg:h-screen-menu overflow-y-auto touch-pan-y py-4 space-y-1">
          <template v-for="item in menuAside" :key="item.label">
            <div
              v-if="item.disabled"
              class="mx-3 px-3 py-2.5 min-h-[40px] rounded-lg text-sm text-gray-400 dark:text-slate-500 cursor-not-allowed"
            >
              {{ item.label }}
            </div>
            <Link
              v-else
              :href="route(item.route)"
              class="flex items-center mx-3 px-3 py-2.5 min-h-[44px] rounded-lg text-sm text-gray-700 hover:bg-gray-100 dark:text-slate-200 dark:hover:bg-slate-800"
              @click="isAsideMobileExpanded = false"
            >
              <span>{{ item.label }}</span>
            </Link>
          </template>
        </nav>
      </aside>

      <main class="lg:pl-64 pt-16 px-3 sm:px-4 pb-8 min-h-screen bg-gray-50 dark:bg-slate-800">
        <slot />
        <footer class="mt-10 text-xs text-gray-500 dark:text-slate-500">
          <span>Admin One style layout — PKL Admin</span>
        </footer>
      </main>
    </div>
  </div>
</template>
