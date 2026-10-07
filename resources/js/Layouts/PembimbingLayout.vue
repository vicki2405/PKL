<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const page = usePage();
const user = computed(() => page.props.auth?.user || { name: 'Guest', email: '' });

const showLogoutModal = ref(false);
const isLoggingOut = ref(false);

const isActive = (menu) => {
    try {
        if (menu.routeName && route().current(menu.routeName)) {
            return true;
        }
        if (menu.pathPrefix) {
            const currentUrl = page.url || '';
            const path = currentUrl.split('?')[0].split('#')[0];
            return path === menu.pathPrefix || path.startsWith(menu.pathPrefix + '/');
        }
    } catch (e) {
        return false;
    }
    return false;
};

const pembimbingMenus = [
    {
        name: 'Dashboard',
        route: 'pembimbing.dashboard',
        routeName: 'pembimbing.dashboard',
        pathPrefix: '/pembimbing/dashboard',
        icon: 'dashboard'
    },
    {
        name: 'Monitoring',
        route: 'pembimbing.monitoring.index',
        routeName: 'pembimbing.monitoring.*',
        pathPrefix: '/pembimbing/monitoring',
        icon: 'monitoring'
    },
    {
        name: 'Absensi',
        route: 'pembimbing.absensi-pembekalan.index',
        routeName: 'pembimbing.absensi-pembekalan.*',
        pathPrefix: '/pembimbing/absensi-pembekalan',
        icon: 'absensi'
    },
    {
        name: 'Informasi',
        route: 'pembimbing.informasi.index',
        routeName: 'pembimbing.informasi.*',
        pathPrefix: '/pembimbing/informasi',
        icon: 'informasi'
    },
];

const handleLogout = () => {
    isLoggingOut.value = true;
    router.post(route('logout'), {}, {
        onFinish: () => {
            isLoggingOut.value = false;
            showLogoutModal.value = false;
        }
    });
};
</script>

<template>
    <div class="min-h-screen bg-gray-50 flex">
        <!-- Sidebar (Desktop) -->
        <aside class="hidden lg:flex flex-col w-64 bg-slate-900 text-white min-h-screen fixed left-0 top-0 bottom-0 z-50 shadow-2xl transition-all duration-300 overflow-hidden">
            <!-- Decorative Background Elements -->
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
                <div class="absolute -top-[20%] -left-[20%] w-[70%] h-[40%] rounded-full bg-indigo-500/10 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 w-[70%] h-[40%] rounded-full bg-purple-500/10 blur-3xl"></div>
            </div>

            <div class="relative z-10 p-6 flex items-center justify-center border-b border-slate-800/50 backdrop-blur-sm">
                <Link :href="route('pembimbing.dashboard')" class="flex items-center gap-3 group">
                    <div class="relative">
                        <div class="absolute inset-0 bg-indigo-500 blur-md opacity-20 group-hover:opacity-40 transition-opacity"></div>
                        <img src="/logo2.png" alt="Logo" class="h-10 w-auto relative z-10 transition-transform group-hover:scale-105" />
                    </div>
                    <span class="font-bold text-xl tracking-wider bg-clip-text text-transparent bg-gradient-to-r from-white to-slate-400 group-hover:to-white transition-all">PEMBIMBING</span>
                </Link>
            </div>

            <nav class="relative z-10 flex-1 py-6 px-4 space-y-2 overflow-y-auto">
                <div class="px-2 mb-3 text-xs font-bold text-slate-500 uppercase tracking-widest">
                    Menu Utama
                </div>

                <Link :href="route('pembimbing.dashboard')"
                      :class="{'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-500/20': route().current('pembimbing.dashboard'), 'text-slate-400 hover:bg-slate-800/50 hover:text-white': !route().current('pembimbing.dashboard')}"
                      class="flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:scale-110" :class="{'text-white': route().current('pembimbing.dashboard'), 'text-slate-500 group-hover:text-white': !route().current('pembimbing.dashboard')}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span class="font-medium tracking-wide">Dashboard</span>
                </Link>

                <Link :href="route('pembimbing.monitoring.index')"
                      :class="{'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-500/20': route().current('pembimbing.monitoring.*'), 'text-slate-400 hover:bg-slate-800/50 hover:text-white': !route().current('pembimbing.monitoring.*')}"
                      class="flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:scale-110" :class="{'text-white': route().current('pembimbing.monitoring.*'), 'text-slate-500 group-hover:text-white': !route().current('pembimbing.monitoring.*')}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                    <span class="font-medium tracking-wide">Monitoring</span>
                </Link>

                <Link :href="route('pembimbing.absensi-pembekalan.index')"
                      :class="{'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-500/20': route().current('pembimbing.absensi-pembekalan.*'), 'text-slate-400 hover:bg-slate-800/50 hover:text-white': !route().current('pembimbing.absensi-pembekalan.*')}"
                      class="flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:scale-110" :class="{'text-white': route().current('pembimbing.absensi-pembekalan.*'), 'text-slate-500 group-hover:text-white': !route().current('pembimbing.absensi-pembekalan.*')}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="font-medium tracking-wide">Absensi Pembekalan</span>
                </Link>

                <Link :href="route('pembimbing.informasi.index')"
                      :class="{'bg-gradient-to-r from-indigo-600 to-blue-600 text-white shadow-lg shadow-indigo-500/20': route().current('pembimbing.informasi.*'), 'text-slate-400 hover:bg-slate-800/50 hover:text-white': !route().current('pembimbing.informasi.*')}"
                      class="flex items-center gap-3 px-4 py-3.5 rounded-xl transition-all duration-300 group">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform group-hover:scale-110" :class="{'text-white': route().current('pembimbing.informasi.*'), 'text-slate-500 group-hover:text-white': !route().current('pembimbing.informasi.*')}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium tracking-wide">Informasi</span>
                </Link>

            </nav>

            <div class="relative z-10 p-4 border-t border-slate-800/50 backdrop-blur-sm">
                <Link :href="route('profile.edit')" class="bg-slate-800/50 hover:bg-slate-800 rounded-2xl p-4 flex items-center gap-3 border border-slate-700/50 transition-colors group" title="Ubah Profil / Kata Sandi">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-lg shadow-inner ring-2 ring-slate-700 group-hover:ring-indigo-400 transition-all">
                        {{ user.name?.charAt(0) || 'U' }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-white truncate group-hover:text-indigo-200 transition-colors">{{ user.name || 'User' }}</p>
                        <p class="text-xs text-indigo-400 truncate font-medium">Ubah Profil / Sandi</p>
                    </div>
                </Link>
                <button type="button" @click="showLogoutModal = true" class="mt-4 w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-500 hover:to-pink-500 text-white rounded-xl text-sm font-bold shadow-lg shadow-red-900/20 transition-all hover:shadow-red-900/40 active:scale-95">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Log Out
                </button>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 lg:ml-64 transition-all duration-300">

            <!-- Desktop Top Header Bar -->
            <header v-if="$slots.header" class="hidden lg:flex items-center justify-between px-8 py-4 bg-white border-b border-slate-200/80 sticky top-0 z-30 shadow-xs backdrop-blur-md">
                <div class="font-bold text-slate-800 text-lg flex items-center gap-3">
                    <slot name="header" />
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="route('profile.edit')" class="flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-indigo-600 bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-200 px-3 py-1.5 rounded-xl transition-all shadow-xs">
                        <div class="h-6 w-6 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-[10px]">
                            {{ user.name?.charAt(0) || 'U' }}
                        </div>
                        <span class="max-w-[140px] truncate">{{ user.name || 'Profil' }}</span>
                    </Link>
                </div>
            </header>

            <!-- Mobile Header (Simplified) -->
            <header class="bg-white/80 backdrop-blur-md shadow-sm lg:hidden sticky top-0 z-40 border-b border-slate-100">
                <div class="flex items-center justify-between px-4 py-3">
                    <div class="flex items-center gap-3">
                        <img src="/logo2.png" alt="Logo" class="h-8 w-auto" />
                        <span class="font-bold text-lg text-slate-800 tracking-wide">PEMBIMBING</span>
                    </div>
                    <Link :href="route('profile.edit')" class="h-8 w-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-sm shadow-sm border border-indigo-200">
                        {{ user.name?.charAt(0) || 'U' }}
                    </Link>
                </div>
            </header>

            <!-- Mobile Bottom Navigation (Always Active, Glowing Bar & Pill Accent) -->
            <nav class="fixed lg:hidden bottom-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-t border-slate-200/80 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] pb-safe">
                <div class="max-w-screen-xl mx-auto px-2">
                    <ul class="grid grid-cols-5 items-center h-[68px]">
                        <!-- Main Menu Items -->
                        <li v-for="menu in pembimbingMenus" :key="menu.name" class="h-full">
                            <Link :href="route(menu.route)"
                                  class="relative flex flex-col items-center justify-center h-full w-full transition-all duration-200 group">
                                <!-- Glowing Top Active Bar -->
                                <div v-if="isActive(menu)"
                                     class="absolute top-0 inset-x-0 mx-auto w-8 h-0.5 rounded-full bg-indigo-600 shadow-[0_2px_8px_rgba(79,70,229,0.6)] animate-pulse"></div>

                                <!-- Icon Container with Pill Highlight -->
                                <div class="relative p-1.5 rounded-xl transition-all duration-200"
                                     :class="isActive(menu) ? 'bg-indigo-50/90 text-indigo-600 ring-1 ring-indigo-200/60 shadow-xs scale-105' : 'text-slate-400 group-hover:text-indigo-600 group-hover:bg-slate-50'">
                                    <!-- Dashboard Icon -->
                                    <svg v-if="menu.icon === 'dashboard'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                    <!-- Monitoring Icon -->
                                    <svg v-else-if="menu.icon === 'monitoring'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <!-- Absensi Icon -->
                                    <svg v-else-if="menu.icon === 'absensi'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <!-- Informasi Icon -->
                                    <svg v-else-if="menu.icon === 'informasi'" xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>

                                <!-- Menu Label -->
                                <span class="text-[10px] tracking-tight mt-0.5"
                                      :class="isActive(menu) ? 'font-bold text-indigo-600' : 'font-medium text-slate-400 group-hover:text-indigo-600'">
                                    {{ menu.name }}
                                </span>
                            </Link>
                        </li>

                        <!-- Logout Button with Safe Modal Trigger -->
                        <li class="h-full">
                            <button type="button"
                                    @click="showLogoutModal = true"
                                    class="relative flex flex-col items-center justify-center h-full w-full transition-all duration-200 group text-slate-400 hover:text-red-600">
                                <div class="relative p-1.5 rounded-xl transition-all duration-200 group-hover:bg-red-50 group-hover:text-red-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                </div>
                                <span class="text-[10px] tracking-tight mt-0.5 font-medium group-hover:font-semibold">
                                    Keluar
                                </span>
                            </button>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="flex-1 p-4 lg:p-8 overflow-y-auto bg-slate-50/50 pb-32 sm:pb-36 lg:pb-8">
                <slot />
            </main>
        </div>

        <!-- Confirmation Modal for Logout -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showLogoutModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
                    <div class="relative w-full max-w-sm bg-white rounded-3xl shadow-2xl p-6 text-center border border-slate-100 transform transition-all">
                        <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 mb-2">Konfirmasi Keluar</h3>
                        <p class="text-sm text-slate-500 mb-6">Apakah Anda yakin ingin keluar dari akun Pembimbing?</p>
                        <div class="flex items-center gap-3">
                            <button
                                type="button"
                                @click="showLogoutModal = false"
                                :disabled="isLoggingOut"
                                class="flex-1 py-2.5 px-4 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-50 transition-colors"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                @click="handleLogout"
                                :disabled="isLoggingOut"
                                class="flex-1 py-2.5 px-4 rounded-xl bg-gradient-to-r from-red-600 to-pink-600 text-white text-sm font-semibold hover:from-red-500 hover:to-pink-500 shadow-md shadow-red-500/20 transition-all flex items-center justify-center gap-2"
                            >
                                <svg v-if="isLoggingOut" class="animate-spin h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>{{ isLoggingOut ? 'Keluar...' : 'Ya, Keluar' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.pb-safe {
    padding-bottom: max(0.5rem, env(safe-area-inset-bottom, 0px));
}
</style>
