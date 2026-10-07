<script setup>
</script>

<template>
  <div class="relative pt-24 pb-28 lg:pb-12 overflow-hidden bg-gray-900 text-white border-t-4 border-orange-500">
    <!-- Background Decoration: Animated Grid -->
    <div class="absolute inset-0 opacity-10 pointer-events-none"
         style="background-image: linear-gradient(#333 1px, transparent 1px), linear-gradient(90deg, #333 1px, transparent 1px); background-size: 30px 30px;">
    </div>

    <!-- Background Decoration: Floating Orbs -->
    <div class="absolute top-0 right-0 w-96 h-96 bg-orange-500 rounded-full mix-blend-screen filter blur-[100px] opacity-20 animate-pulse"></div>
    <div class="absolute bottom-0 left-10 w-72 h-72 bg-blue-600 rounded-full mix-blend-screen filter blur-[80px] opacity-20 animate-pulse delay-1000"></div>

    <!-- Content Container -->
    <div class="max-w-screen-xl w-full mx-auto px-6 sm:px-8 lg:px-16 flex flex-col items-start justify-center relative z-10">

      <!-- Title Section with Neon Glow -->
      <div class="relative mb-6 group">
        <div class="absolute -left-6 top-0 bottom-0 w-1 bg-gradient-to-b from-orange-500 via-yellow-500 to-transparent rounded-full opacity-75 group-hover:h-full transition-all duration-500"></div>
        <h3 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-white via-gray-200 to-gray-400 drop-shadow-lg">
          HUMAS SMKS MUHAMMADIYAH 2 GENTENG
        </h3>
        <div class="h-1 w-24 bg-gradient-to-r from-orange-500 to-transparent mt-2 rounded-full"></div>
      </div>

      <!-- Description with Modern Typography -->
      <p class="text-gray-400 text-base sm:text-lg max-w-2xl leading-relaxed mb-10 font-light tracking-wide">
        Sistem Informasi Manajemen Praktik Kerja Lapangan (PKL) yang terintegrasi. <br class="hidden sm:block" />
        <span class="text-gray-500">Mewujudkan sinergi pendidikan vokasi yang unggul dan berkemajuan.</span>
      </p>

      <!-- Footer Bottom Section -->
      <div class="w-full border-t border-gray-800 pt-8 flex flex-col sm:flex-row justify-between items-center gap-6">

        <!-- Copyright -->
        <div class="flex items-center gap-2">
           <div class="w-2 h-2 bg-green-500 rounded-full animate-ping"></div>
           <p class="text-gray-500 text-sm font-mono tracking-wider">
             &copy; {{ new Date().getFullYear() }} <span class="text-orange-400 font-semibold">HUMAS STEMDA</span>. All rights reserved.
           </p>
        </div>

        <!-- Social / Contact Links (Optional Decoration) -->
        <div class="flex gap-4">
           <a href="#" class="w-8 h-8 rounded-lg bg-gray-800 flex items-center justify-center hover:bg-orange-500 hover:text-white transition-all duration-300 group">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
             </svg>
           </a>
           <a href="#" class="w-8 h-8 rounded-lg bg-gray-800 flex items-center justify-center hover:bg-orange-500 hover:text-white transition-all duration-300 group">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
             </svg>
           </a>
           <a href="#" class="w-8 h-8 rounded-lg bg-gray-800 flex items-center justify-center hover:bg-orange-500 hover:text-white transition-all duration-300 group">
             <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 group-hover:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
             </svg>
           </a>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.delay-1000 {
  animation-delay: 1s;
}
</style>
