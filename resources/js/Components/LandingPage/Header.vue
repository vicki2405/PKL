<script setup>
import { ref, onMounted, onUnmounted } from "vue";
import { Link } from "@inertiajs/vue3";
import ButtonOutline from "./ButtonOutline.vue";

const scrollActive = ref(false);

const checkScroll = () => {
  scrollActive.value = window.scrollY > 20;
};

onMounted(() => {
  window.addEventListener("scroll", checkScroll);
});

onUnmounted(() => {
  window.removeEventListener("scroll", checkScroll);
});
</script>

<template>
  <header
    class="fixed top-0 w-full z-30 bg-white-500 transition-all"
    :class="{ 'shadow-md pt-0': scrollActive, 'pt-4': !scrollActive }"
  >
    <nav
      class="max-w-screen-xl px-6 sm:px-8 lg:px-16 mx-auto flex items-center justify-between py-3 sm:py-4 lg:grid lg:grid-cols-12 lg:grid-flow-col"
    >
      <div class="flex items-center gap-3 lg:col-start-1 lg:col-end-4">
        <img src="/logo2.png" class="h-8 w-auto shrink-0" alt="Logo" />
        <span class="font-bold text-sm sm:text-xl text-black-600 truncate max-w-[10rem] sm:max-w-none">
          HUMAS STEMDA
        </span>
      </div>
      <ul class="hidden lg:flex lg:col-start-4 lg:col-end-10 text-black-500 items-center">
        <!-- Menu Khusus Siswa -->
        <template v-if="$page.props.auth.user && Array.isArray($page.props.auth.user.roles) && $page.props.auth.user.roles.includes('siswa')">
            <li class="px-4 py-2 mx-2 cursor-pointer animation-hover inline-block relative" :class="route().current('siswa.dashboard') ? 'text-orange-500' : 'text-black-500 hover:text-orange-500'">
                <Link :href="route('siswa.dashboard')">Dashboard</Link>
            </li>
            <li class="px-4 py-2 mx-2 cursor-pointer animation-hover inline-block relative" :class="route().current('siswa.profile') ? 'text-orange-500' : 'text-black-500 hover:text-orange-500'">
                <Link :href="route('siswa.profile')">Profil</Link>
            </li>
            <li class="px-4 py-2 mx-2 cursor-pointer animation-hover inline-block relative" :class="route().current('siswa.mutasi_pkl.create') ? 'text-orange-500' : 'text-black-500 hover:text-orange-500'">
                <Link :href="route('siswa.mutasi_pkl.create')">Mutasi</Link>
            </li>
            <li class="px-4 py-2 mx-2 cursor-pointer animation-hover inline-block relative" :class="route().current('siswa.karir.*') ? 'text-orange-500' : 'text-black-500 hover:text-orange-500'">
                <Link :href="route('siswa.karir.edit')">Karier</Link>
            </li>
        </template>

        <!-- Menu Non-Siswa Terautentikasi (Admin, Pembimbing, dll) -->
        <template v-else-if="$page.props.auth.user">
            <li class="px-4 py-2 mx-2 cursor-pointer animation-hover inline-block relative text-black-500 hover:text-orange-500">
              <a href="/#about" class="scroll-smooth">Beranda</a>
            </li>
            <li class="px-4 py-2 mx-2 cursor-pointer animation-hover inline-block relative text-black-500 hover:text-orange-500">
              <a href="/#alur" class="scroll-smooth">Alur</a>
            </li>
            <li class="px-4 py-2 mx-2 cursor-pointer animation-hover inline-block relative text-black-500 hover:text-orange-500">
              <a href="/#map-section" class="scroll-smooth">Peta</a>
            </li>
            <li class="px-4 py-2 mx-2 cursor-pointer animation-hover inline-block relative text-black-500 hover:text-orange-500">
              <a href="/#bengkel-table" class="scroll-smooth">Data</a>
            </li>
            <li class="px-4 py-2 mx-2 cursor-pointer animation-hover inline-block relative text-orange-500 hover:text-orange-600 font-semibold">
              <Link :href="route('dashboard')">Dashboard</Link>
            </li>
        </template>

        <!-- Menu Tamu (Guest) -->
        <template v-else>
            <li class="px-4 py-2 mx-2 cursor-pointer animation-hover inline-block relative text-black-500 hover:text-orange-500">
              <a href="/#about" class="scroll-smooth">Beranda</a>
            </li>
            <li class="px-4 py-2 mx-2 cursor-pointer animation-hover inline-block relative text-black-500 hover:text-orange-500">
              <a href="/#alur" class="scroll-smooth">Alur</a>
            </li>
            <li class="px-4 py-2 mx-2 cursor-pointer animation-hover inline-block relative text-black-500 hover:text-orange-500">
              <a href="/#map-section" class="scroll-smooth">Peta</a>
            </li>
            <li class="px-4 py-2 mx-2 cursor-pointer animation-hover inline-block relative text-black-500 hover:text-orange-500">
              <a href="/#bengkel-table" class="scroll-smooth">Data</a>
            </li>
        </template>
      </ul>
      <div class="font-medium flex justify-end items-center lg:col-start-10 lg:col-end-13">
        <template v-if="$page.props.auth.user">
          <Link
            :href="route('dashboard')"
            class="font-medium tracking-wide py-2 px-5 bg-orange-500 text-white rounded-full capitalize hover:bg-orange-600 hover:shadow-lg transition-all transform hover:scale-105 hidden lg:inline-block mr-2 text-sm"
          >
            Dashboard
          </Link>
          <Link
            :href="route('logout')"
            method="post"
            as="button"
            class="text-black-600 mx-2 sm:mx-4 capitalize tracking-wide hover:text-orange-500 transition-all bg-transparent border-none cursor-pointer hidden lg:block"
          >
            Logout
          </Link>
        </template>
        <template v-else>
          <Link
            :href="route('login')"
            class="font-medium tracking-wide py-2 px-5 sm:px-8 bg-orange-500 text-white outline-none rounded-full capitalize hover:bg-orange-600 hover:shadow-lg transition-all transform hover:scale-105 hidden lg:block"
          >
            Sign In
          </Link>
        </template>
      </div>
    </nav>
  </header>

  <!-- Mobile Bottom Navigation -->
  <nav class="fixed lg:hidden bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-100 shadow-[0_-2px_10px_rgba(0,0,0,0.05)] pb-safe">
    <div class="max-w-screen-xl mx-auto px-4">
      <ul class="grid grid-cols-5 items-center h-16">

        <!-- Case 1: Siswa Role -->
        <template v-if="$page.props.auth.user && Array.isArray($page.props.auth.user.roles) && $page.props.auth.user.roles.includes('siswa')">
            <!-- Dashboard -->
            <li class="h-full">
                <Link :href="route('siswa.dashboard')" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group" :class="route().current('siswa.dashboard') ? 'text-orange-500' : 'text-gray-400 hover:text-orange-500'">
                    <div class="relative p-1 rounded-xl transition-all duration-200" :class="route().current('siswa.dashboard') ? 'bg-orange-50' : 'group-hover:bg-gray-50'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Dashboard</span>
                </Link>
            </li>
            <!-- Profil -->
            <li class="h-full">
                <Link :href="route('siswa.profile')" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group" :class="route().current('siswa.profile') ? 'text-orange-500' : 'text-gray-400 hover:text-orange-500'">
                    <div class="relative p-1 rounded-xl transition-all duration-200" :class="route().current('siswa.profile') ? 'bg-orange-50' : 'group-hover:bg-gray-50'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Profil</span>
                </Link>
            </li>
            <!-- Mutasi -->
            <li class="h-full">
                <Link :href="route('siswa.mutasi_pkl.create')" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group" :class="route().current('siswa.mutasi_pkl.create') ? 'text-orange-500' : 'text-gray-400 hover:text-orange-500'">
                    <div class="relative p-1 rounded-xl transition-all duration-200" :class="route().current('siswa.mutasi_pkl.create') ? 'bg-orange-50' : 'group-hover:bg-gray-50'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Mutasi</span>
                </Link>
            </li>
            <!-- Karier -->
            <li class="h-full">
                <Link :href="route('siswa.karir.edit')" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group" :class="route().current('siswa.karir.*') ? 'text-orange-500' : 'text-gray-400 hover:text-orange-500'">
                    <div class="relative p-1 rounded-xl transition-all duration-200" :class="route().current('siswa.karir.*') ? 'bg-orange-50' : 'group-hover:bg-gray-50'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Karier</span>
                </Link>
            </li>
            <!-- Logout -->
            <li class="h-full">
                <Link :href="route('logout')" method="post" as="button" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group text-red-400 hover:text-red-500">
                    <div class="relative p-1 rounded-xl transition-all duration-200 group-hover:bg-red-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Logout</span>
                </Link>
            </li>
        </template>

        <!-- Case 2: Other Authenticated Roles (Admin, Pembimbing, etc) -->
        <template v-else-if="$page.props.auth.user">
            <!-- Beranda -->
            <li class="h-full">
                <a href="/#about" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group text-gray-400 hover:text-orange-500">
                    <div class="relative p-1 rounded-xl transition-all duration-200 group-hover:bg-orange-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Beranda</span>
                </a>
            </li>
            <!-- Peta -->
            <li class="h-full">
                <a href="/#map-section" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group text-gray-400 hover:text-orange-500">
                    <div class="relative p-1 rounded-xl transition-all duration-200 group-hover:bg-orange-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Peta</span>
                </a>
            </li>
            <!-- Data -->
            <li class="h-full">
                <a href="/#bengkel-table" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group text-gray-400 hover:text-orange-500">
                    <div class="relative p-1 rounded-xl transition-all duration-200 group-hover:bg-orange-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Data</span>
                </a>
            </li>
            <!-- Dashboard -->
            <li class="h-full">
                <Link :href="route('dashboard')" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group text-orange-500 hover:text-orange-600 font-medium">
                    <div class="relative p-1 rounded-xl transition-all duration-200 bg-orange-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Dashboard</span>
                </Link>
            </li>
            <!-- Logout -->
            <li class="h-full">
                <Link :href="route('logout')" method="post" as="button" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group text-red-400 hover:text-red-500">
                    <div class="relative p-1 rounded-xl transition-all duration-200 group-hover:bg-red-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Logout</span>
                </Link>
            </li>
        </template>

        <!-- Case 3: Guest -->
        <template v-else>
            <!-- Beranda -->
            <li class="h-full">
                <a href="/#about" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group text-gray-400 hover:text-orange-500">
                    <div class="relative p-1 rounded-xl transition-all duration-200 group-hover:bg-orange-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Beranda</span>
                </a>
            </li>
            <!-- Alur -->
            <li class="h-full">
                <a href="/#alur" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group text-gray-400 hover:text-orange-500">
                    <div class="relative p-1 rounded-xl transition-all duration-200 group-hover:bg-orange-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Alur</span>
                </a>
            </li>
            <!-- Peta -->
            <li class="h-full">
                <a href="/#map-section" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group text-gray-400 hover:text-orange-500">
                    <div class="relative p-1 rounded-xl transition-all duration-200 group-hover:bg-orange-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Peta</span>
                </a>
            </li>
            <!-- Data -->
            <li class="h-full">
                <a href="/#bengkel-table" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group text-gray-400 hover:text-orange-500">
                    <div class="relative p-1 rounded-xl transition-all duration-200 group-hover:bg-orange-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Data</span>
                </a>
            </li>

            <!-- Login -->
            <li class="h-full">
                 <Link :href="route('login')" class="flex flex-col items-center justify-center h-full w-full transition-colors duration-200 group text-gray-400 hover:text-orange-500">
                    <div class="relative p-1 rounded-xl transition-all duration-200 group-hover:bg-orange-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                    </div>
                    <span class="text-[10px] font-medium mt-1">Masuk</span>
                </Link>
            </li>
        </template>

      </ul>
    </div>
  </nav>
</template>

<style scoped>
/* Mobile nav style moved to BottomNav.vue */
</style>
