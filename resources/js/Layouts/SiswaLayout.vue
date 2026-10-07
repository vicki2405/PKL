<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'Siswa', email: '' });
const avatarErrorDesktop = ref(false);
const avatarErrorMobile = ref(false);
const showLogoutModal = ref(false);

const isActive = (menu) => {
    const url = page.url || '';
    if (menu === 'dashboard') {
        return route().current('siswa.dashboard') || url === '/siswa/dashboard' || url === '/siswa';
    }
    if (menu === 'profile') {
        return route().current('siswa.profile*') || url.startsWith('/siswa/profile');
    }
    if (menu === 'karir') {
        return route().current('siswa.karir*') || url.startsWith('/siswa/karir');
    }
    if (menu === 'mutasi') {
        return route().current('siswa.mutasi_pkl*') || url.startsWith('/siswa/mutasi');
    }
    return false;
};
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex flex-col font-sans selection:bg-orange-500 selection:text-white">
        <!-- Desktop Sidebar -->
        <aside class="hidden lg:flex flex-col w-64 bg-slate-900 text-white min-h-screen fixed left-0 top-0 bottom-0 z-50 shadow-2xl transition-all duration-300 overflow-hidden">
            <!-- Background Ambient Glow -->
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
                <div class="absolute -top-[20%] -left-[20%] w-[70%] h-[40%] rounded-full bg-orange-500/15 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-[70%] h-[40%] rounded-full bg-amber-500/10 blur-3xl"></div>
            </div>

            <!-- Brand Header -->
            <div class="relative z-10 p-6 flex items-center justify-center border-b border-slate-800/50 backdrop-blur-sm">
                <Link :href="route('siswa.dashboard')" class="flex items-center gap-3 group">
                    <div class="relative">
                        <div class="absolute inset-0 bg-orange-500 blur-md opacity-20 group-hover:opacity-40 transition-opacity"></div>
                        <img src="/logo2.png" alt="Logo" class="h-10 w-auto relative z-10 transition-transform group-hover:scale-105" />
                    </div>
                    <span class="font-bold text-xl tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white via-slate-200 to-orange-400 group-hover:to-white transition-all">PORTAL SISWA</span>
                </Link>
            </div>

            <!-- Nav Links (Desktop) -->
            <nav class="relative z-10 flex-1 py-6 px-4 space-y-2 overflow-y-auto">
                <div class="px-2 mb-3 text-xs font-bold text-slate-500 uppercase tracking-widest">
                    Menu Utama
                </div>

                <!-- Dashboard -->
                <Link :href="route('siswa.dashboard')"
                      :class="isActive('dashboard') ? 'bg-gradient-to-r from-orange-600 to-amber-600 text-white shadow-lg shadow-orange-500/25 ring-1 ring-orange-400/30' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white'"
                      class="flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:scale-110" :class="isActive('dashboard') ? 'text-white' : 'text-slate-500 group-hover:text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="font-medium tracking-wide">Dashboard</span>
                </Link>

                <!-- Profil -->
                <Link :href="route('siswa.profile')"
                      :class="isActive('profile') ? 'bg-gradient-to-r from-orange-600 to-amber-600 text-white shadow-lg shadow-orange-500/25 ring-1 ring-orange-400/30' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white'"
                      class="flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:scale-110" :class="isActive('profile') ? 'text-white' : 'text-slate-500 group-hover:text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="font-medium tracking-wide">Profil Saya</span>
                </Link>

                <!-- Karier -->
                <Link :href="route('siswa.karir.edit')"
                      :class="isActive('karir') ? 'bg-gradient-to-r from-orange-600 to-amber-600 text-white shadow-lg shadow-orange-500/25 ring-1 ring-orange-400/30' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white'"
                      class="flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:scale-110" :class="isActive('karir') ? 'text-white' : 'text-slate-500 group-hover:text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="font-medium tracking-wide">Karier</span>
                </Link>

                <!-- Mutasi -->
                <Link :href="route('siswa.mutasi_pkl.create')"
                      :class="isActive('mutasi') ? 'bg-gradient-to-r from-orange-600 to-amber-600 text-white shadow-lg shadow-orange-500/25 ring-1 ring-orange-400/30' : 'text-slate-400 hover:bg-slate-800/60 hover:text-white'"
                      class="flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:scale-110" :class="isActive('mutasi') ? 'text-white' : 'text-slate-500 group-hover:text-white'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    <span class="font-medium tracking-wide">Pengajuan Mutasi</span>
                </Link>
            </nav>

            <!-- User Info (Desktop) -->
            <div class="relative z-10 p-4 border-t border-slate-800/50 backdrop-blur-sm">
                <div class="bg-slate-800/50 rounded-2xl p-4 flex items-center gap-3 border border-slate-700/50">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-orange-500 to-amber-600 flex items-center justify-center text-white font-bold text-lg shadow-inner ring-2 ring-slate-700 overflow-hidden flex-shrink-0">
                        <img v-if="user.profile_photo_url && !avatarErrorDesktop" :src="user.profile_photo_url" alt="Profile" class="w-full h-full object-cover" @error="avatarErrorDesktop = true" />
                        <span v-else>{{ user.name?.charAt(0) || 'S' }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-white truncate">{{ user.name || 'Siswa' }}</p>
                        <p class="text-xs text-orange-400 truncate font-medium">Siswa PKL</p>
                    </div>
                </div>
                <Link :href="route('logout')" method="post" as="button" class="mt-4 w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-500 hover:to-rose-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-900/20 transition-all hover:shadow-red-900/40 active:scale-95 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Keluar
                </Link>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 lg:ml-64 transition-all duration-300">

            <!-- Mobile Header Topbar -->
            <header class="bg-white/95 backdrop-blur-md shadow-sm lg:hidden sticky top-0 z-40 border-b border-slate-100 px-4 py-3">
                <div class="flex items-center justify-between">
                    <Link :href="route('siswa.dashboard')" class="flex items-center gap-2.5">
                        <img src="/logo2.png" alt="Logo" class="h-8 w-auto" />
                        <div>
                            <span class="font-bold text-base text-slate-800 tracking-wide block leading-tight">PORTAL SISWA</span>
                            <span class="text-[10px] text-orange-600 font-semibold tracking-wider uppercase block">SMK SIM PKL</span>
                        </div>
                    </Link>
                    <Link :href="route('siswa.profile')" class="flex items-center gap-2">
                        <div class="h-9 w-9 rounded-full bg-gradient-to-tr from-orange-500 to-amber-500 text-white flex items-center justify-center font-bold text-sm shadow-md ring-2 ring-orange-200 overflow-hidden">
                            <img v-if="user.profile_photo_url && !avatarErrorMobile" :src="user.profile_photo_url" alt="Profile" class="w-full h-full object-cover" @error="avatarErrorMobile = true" />
                            <span v-else>{{ user.name?.charAt(0) || 'S' }}</span>
                        </div>
                    </Link>
                </div>
            </header>

            <!-- Mobile Bottom Navigation Bar (Premium, Thumb-Friendly, Never Clipped) -->
            <nav class="fixed lg:hidden bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-xl border-t border-slate-200/90 shadow-[0_-8px_25px_rgba(0,0,0,0.08)] pb-safe">
                <div class="max-w-md mx-auto px-1 sm:px-2">
                    <ul class="grid grid-cols-5 items-stretch h-[68px]">
                        <!-- 1. Beranda -->
                        <li class="h-full">
                            <Link :href="route('siswa.dashboard')"
                                  class="relative flex flex-col items-center justify-center h-full w-full py-1 transition-all duration-200 group select-none">
                                <!-- Top Active Glowing Accent Bar -->
                                <span v-if="isActive('dashboard')" class="absolute top-0 w-8 h-1 bg-gradient-to-r from-orange-500 to-amber-500 rounded-full shadow-[0_2px_8px_rgba(249,115,22,0.6)] animate-in fade-in zoom-in-50 duration-200"></span>

                                <!-- Icon Container -->
                                <div class="relative p-1.5 rounded-2xl transition-all duration-200 flex items-center justify-center"
                                     :class="isActive('dashboard') ? 'bg-gradient-to-tr from-orange-500/15 to-amber-500/15 text-orange-600 shadow-xs border border-orange-500/20 scale-105' : 'text-slate-400 group-hover:text-slate-600 group-active:scale-95'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                </div>
                                <span class="text-[10px] mt-0.5 tracking-tight"
                                      :class="isActive('dashboard') ? 'font-extrabold text-orange-600' : 'font-medium text-slate-400'">
                                    Beranda
                                </span>
                            </Link>
                        </li>

                        <!-- 2. Profil -->
                        <li class="h-full">
                            <Link :href="route('siswa.profile')"
                                  class="relative flex flex-col items-center justify-center h-full w-full py-1 transition-all duration-200 group select-none">
                                <span v-if="isActive('profile')" class="absolute top-0 w-8 h-1 bg-gradient-to-r from-orange-500 to-amber-500 rounded-full shadow-[0_2px_8px_rgba(249,115,22,0.6)] animate-in fade-in zoom-in-50 duration-200"></span>

                                <div class="relative p-1.5 rounded-2xl transition-all duration-200 flex items-center justify-center"
                                     :class="isActive('profile') ? 'bg-gradient-to-tr from-orange-500/15 to-amber-500/15 text-orange-600 shadow-xs border border-orange-500/20 scale-105' : 'text-slate-400 group-hover:text-slate-600 group-active:scale-95'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <span class="text-[10px] mt-0.5 tracking-tight"
                                      :class="isActive('profile') ? 'font-extrabold text-orange-600' : 'font-medium text-slate-400'">
                                    Profil
                                </span>
                            </Link>
                        </li>

                        <!-- 3. Karier -->
                        <li class="h-full">
                            <Link :href="route('siswa.karir.edit')"
                                  class="relative flex flex-col items-center justify-center h-full w-full py-1 transition-all duration-200 group select-none">
                                <span v-if="isActive('karir')" class="absolute top-0 w-8 h-1 bg-gradient-to-r from-orange-500 to-amber-500 rounded-full shadow-[0_2px_8px_rgba(249,115,22,0.6)] animate-in fade-in zoom-in-50 duration-200"></span>

                                <div class="relative p-1.5 rounded-2xl transition-all duration-200 flex items-center justify-center"
                                     :class="isActive('karir') ? 'bg-gradient-to-tr from-orange-500/15 to-amber-500/15 text-orange-600 shadow-xs border border-orange-500/20 scale-105' : 'text-slate-400 group-hover:text-slate-600 group-active:scale-95'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-[10px] mt-0.5 tracking-tight"
                                      :class="isActive('karir') ? 'font-extrabold text-orange-600' : 'font-medium text-slate-400'">
                                    Karier
                                </span>
                            </Link>
                        </li>

                        <!-- 4. Mutasi -->
                        <li class="h-full">
                            <Link :href="route('siswa.mutasi_pkl.create')"
                                  class="relative flex flex-col items-center justify-center h-full w-full py-1 transition-all duration-200 group select-none">
                                <span v-if="isActive('mutasi')" class="absolute top-0 w-8 h-1 bg-gradient-to-r from-orange-500 to-amber-500 rounded-full shadow-[0_2px_8px_rgba(249,115,22,0.6)] animate-in fade-in zoom-in-50 duration-200"></span>

                                <div class="relative p-1.5 rounded-2xl transition-all duration-200 flex items-center justify-center"
                                     :class="isActive('mutasi') ? 'bg-gradient-to-tr from-orange-500/15 to-amber-500/15 text-orange-600 shadow-xs border border-orange-500/20 scale-105' : 'text-slate-400 group-hover:text-slate-600 group-active:scale-95'">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </div>
                                <span class="text-[10px] mt-0.5 tracking-tight"
                                      :class="isActive('mutasi') ? 'font-extrabold text-orange-600' : 'font-medium text-slate-400'">
                                    Mutasi
                                </span>
                            </Link>
                        </li>

                        <!-- 5. Keluar (Safe Modal Trigger) -->
                        <li class="h-full">
                            <button type="button"
                                    @click="showLogoutModal = true"
                                    class="relative flex flex-col items-center justify-center h-full w-full py-1 transition-all duration-200 group select-none cursor-pointer">
                                <div class="relative p-1.5 rounded-2xl transition-all duration-200 flex items-center justify-center text-slate-400 group-hover:text-red-500 group-hover:bg-red-50/80 group-active:scale-95">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </div>
                                <span class="text-[10px] mt-0.5 tracking-tight font-medium text-slate-400 group-hover:text-red-500">
                                    Keluar
                                </span>
                            </button>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Page Content (Generous Bottom Padding to Never Clip with Fixed Bottom Nav) -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 bg-slate-50/50 pb-32 sm:pb-36 lg:pb-8">
                <slot />
            </main>
        </div>

        <!-- Mobile Safe Logout Confirmation Modal -->
        <Teleport to="body">
            <div v-if="showLogoutModal" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4" role="dialog" aria-modal="true">
                <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" @click="showLogoutModal = false"></div>
                <div class="relative w-full max-w-sm rounded-3xl bg-white p-6 shadow-2xl border border-slate-100 text-center space-y-4 animate-in fade-in zoom-in-95 duration-150">
                    <div class="w-14 h-14 rounded-2xl bg-red-50 text-red-500 flex items-center justify-center mx-auto shadow-inner border border-red-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-extrabold text-slate-800">Konfirmasi Keluar</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                            Apakah Anda yakin ingin keluar dari akun Portal Siswa?
                        </p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <button type="button"
                                @click="showLogoutModal = false"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold text-xs hover:bg-slate-50 transition-colors">
                            Batal
                        </button>
                        <Link :href="route('logout')"
                              method="post"
                              as="button"
                              class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-red-600 to-rose-600 text-white font-bold text-xs shadow-md shadow-red-600/25 hover:from-red-700 hover:to-rose-700 transition-all active:scale-95 cursor-pointer">
                            Ya, Keluar
                        </Link>
                    </div>
                </div>
            </div>
        </Teleport>
    </div>
</template>

