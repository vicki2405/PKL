<script setup>
import { 
    mdiFileDocumentEditOutline, 
    mdiStoreEditOutline, 
    mdiFileCheckOutline, 
    mdiAccountClockOutline,
    mdiArrowRight,
    mdiAlertCircleOutline,
    mdiRefresh
} from '@mdi/js';
</script>

<template>
  <div class="bg-gradient-to-br from-orange-50 to-white w-full py-20 relative overflow-hidden scroll-mt-24" id="alur">
    
    <!-- Decorative Background Elements -->
    <div class="absolute top-0 left-0 w-64 h-64 bg-orange-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 -translate-x-1/2 -translate-y-1/2 animate-blob"></div>
    <div class="absolute top-0 right-0 w-64 h-64 bg-purple-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 translate-x-1/2 -translate-y-1/2 animate-blob animation-delay-2000"></div>
    <div class="absolute bottom-0 left-20 w-64 h-64 bg-pink-200 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>

    <div class="max-w-screen-xl px-6 sm:px-8 lg:px-16 mx-auto flex flex-col w-full text-center justify-center relative z-10">
      
      <!-- Title Section -->
      <div class="flex flex-col w-full mb-16 relative">
        <h3 class="text-3xl sm:text-4xl lg:text-5xl font-black text-gray-800 leading-tight tracking-tight">
          Alur Pelaksanaan <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-red-600">PKL</span>
        </h3>
        <p class="mt-4 text-lg text-gray-600 font-medium max-w-2xl mx-auto">
          Ikuti 4 langkah mudah menuju kesuksesan Praktik Kerja Lapanganmu! 🚀
        </p>
      </div>

      <!-- Flow Content -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12 relative">
        
        <!-- Connecting Line (Desktop) -->
        <div class="hidden lg:block absolute top-16 left-0 w-full h-3 bg-gray-100 rounded-full -z-10 overflow-hidden">
             <div class="h-full w-full bg-gradient-to-r from-orange-200 via-purple-200 to-blue-200 opacity-50"></div>
        </div>

        <!-- Step 1 -->
        <div class="flex flex-col items-center group perspective">
            <div class="relative mb-8 transform transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3">
                <div class="w-28 h-28 rounded-3xl bg-gradient-to-br from-orange-400 to-red-500 shadow-xl flex items-center justify-center transform rotate-6 group-hover:rotate-0 transition-all duration-300">
                     <svg class="w-14 h-14 text-white drop-shadow-md" viewBox="0 0 24 24">
                        <path fill="currentColor" :d="mdiFileDocumentEditOutline" />
                    </svg>
                </div>
                <div class="absolute -top-4 -right-4 w-10 h-10 rounded-full bg-white text-orange-600 border-4 border-orange-100 flex items-center justify-center font-black text-lg shadow-lg">1</div>
            </div>
            
            <div class="bg-white p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 w-full h-full border-b-4 border-orange-100 hover:border-orange-500 relative group-hover:-translate-y-2">
                <h4 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-orange-600 transition-colors">Ambil Formulir F-1</h4>
                <div class="h-1 w-12 bg-orange-200 rounded-full mx-auto mb-4"></div>
                <p class="text-sm text-gray-600 mb-3 font-medium">
                    Ambil di <strong>R. HUMAS</strong>
                </p>
                <p class="text-xs text-gray-500 leading-relaxed bg-orange-50 p-3 rounded-xl border border-orange-100">
                    Isi formulir (perorangan/kelompok) sesuai daya tampung bengkel.
                </p>
            </div>
        </div>

        <!-- Step 2 -->
        <div class="flex flex-col items-center group perspective">
            <div class="relative mb-8 transform transition-transform duration-500 group-hover:scale-110 group-hover:-rotate-3">
                <div class="w-28 h-28 rounded-3xl bg-gradient-to-br from-blue-400 to-indigo-500 shadow-xl flex items-center justify-center transform -rotate-3 group-hover:rotate-0 transition-all duration-300">
                     <svg class="w-14 h-14 text-white drop-shadow-md" viewBox="0 0 24 24">
                        <path fill="currentColor" :d="mdiStoreEditOutline" />
                    </svg>
                </div>
                <div class="absolute -top-4 -right-4 w-10 h-10 rounded-full bg-white text-blue-600 border-4 border-blue-100 flex items-center justify-center font-black text-lg shadow-lg">2</div>
            </div>
            
            <div class="bg-white p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 w-full h-full border-b-4 border-blue-100 hover:border-blue-500 relative group-hover:-translate-y-2">
                <h4 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-blue-600 transition-colors">Lengkapi Data</h4>
                <div class="h-1 w-12 bg-blue-200 rounded-full mx-auto mb-4"></div>
                <p class="text-sm text-gray-600 mb-3 font-medium">
                    Ke <strong>Bengkel Tujuan</strong>
                </p>
                <p class="text-xs text-gray-500 leading-relaxed bg-blue-50 p-3 rounded-xl border border-blue-100">
                    Mintakan tanda tangan & stempel basah pemilik bengkel di Form F-1.
                </p>
            </div>
        </div>

        <!-- Step 3 -->
        <div class="flex flex-col items-center group perspective">
            <div class="relative mb-8 transform transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3">
                <div class="w-28 h-28 rounded-3xl bg-gradient-to-br from-green-400 to-emerald-500 shadow-xl flex items-center justify-center transform rotate-3 group-hover:rotate-0 transition-all duration-300">
                     <svg class="w-14 h-14 text-white drop-shadow-md" viewBox="0 0 24 24">
                        <path fill="currentColor" :d="mdiFileCheckOutline" />
                    </svg>
                </div>
                <div class="absolute -top-4 -right-4 w-10 h-10 rounded-full bg-white text-green-600 border-4 border-green-100 flex items-center justify-center font-black text-lg shadow-lg">3</div>
            </div>
            
            <div class="bg-white p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 w-full h-full border-b-4 border-green-100 hover:border-green-500 relative group-hover:-translate-y-2">
                <h4 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-green-600 transition-colors">Serahkan Form</h4>
                <div class="h-1 w-12 bg-green-200 rounded-full mx-auto mb-4"></div>
                <p class="text-sm text-gray-600 mb-3 font-medium">
                    Ke <strong>Panitia PKL</strong>
                </p>
                <p class="text-xs text-gray-500 leading-relaxed bg-green-50 p-3 rounded-xl border border-green-100">
                    Serahkan Form F-1 yang sudah lengkap dan ditandatangani ke panitia.
                </p>
            </div>
        </div>

        <!-- Step 4 -->
        <div class="flex flex-col items-center group perspective">
            <div class="relative mb-8 transform transition-transform duration-500 group-hover:scale-110 group-hover:-rotate-3">
                <div class="w-28 h-28 rounded-3xl bg-gradient-to-br from-purple-400 to-pink-500 shadow-xl flex items-center justify-center transform -rotate-6 group-hover:rotate-0 transition-all duration-300">
                     <svg class="w-14 h-14 text-white drop-shadow-md" viewBox="0 0 24 24">
                        <path fill="currentColor" :d="mdiAccountClockOutline" />
                    </svg>
                </div>
                <div class="absolute -top-4 -right-4 w-10 h-10 rounded-full bg-white text-purple-600 border-4 border-purple-100 flex items-center justify-center font-black text-lg shadow-lg">4</div>
            </div>
            
            <div class="bg-white p-6 rounded-3xl shadow-xl hover:shadow-2xl transition-all duration-300 w-full h-full border-b-4 border-purple-100 hover:border-purple-500 relative group-hover:-translate-y-2">
                <h4 class="text-xl font-bold text-gray-800 mb-2 group-hover:text-purple-600 transition-colors">Tunggu ACC</h4>
                <div class="h-1 w-12 bg-purple-200 rounded-full mx-auto mb-4"></div>
                <p class="text-sm text-gray-600 mb-3 font-medium">
                    Persetujuan <strong>Resmi</strong>
                </p>
                <p class="text-xs text-gray-500 leading-relaxed bg-purple-50 p-3 rounded-xl border border-purple-100">
                    Tunggu pengumuman resmi persetujuan tempat PKL dari panitia.
                </p>
            </div>
        </div>

      </div>

      <!-- Additional Info / Decoration -->
      <div class="mt-20 mx-auto max-w-4xl">
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden relative transform hover:scale-[1.01] transition-transform duration-300">
            <div class="absolute top-0 w-full h-2 bg-gradient-to-r from-orange-400 via-red-500 to-purple-500"></div>
            
            <div class="flex flex-col md:flex-row items-center p-8 lg:p-10 gap-8">
                <!-- Cartoon Illustration -->
                <div class="w-full md:w-1/3 flex justify-center relative">
                    <div class="absolute inset-0 bg-gradient-to-tr from-orange-100 to-transparent rounded-full opacity-50 blur-xl"></div>
                    <img src="/assets/Illustration2.png" alt="Happy Student" class="w-56 h-auto object-contain drop-shadow-2xl relative z-10 animate-bounce-slow" />
                </div>

                <div class="w-full md:w-2/3 text-left">
                    <h5 class="text-2xl font-black text-gray-800 mb-6 flex items-center gap-3">
                        <span class="flex items-center justify-center w-10 h-10 rounded-full bg-orange-100 text-orange-600">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </span>
                        Catatan Penting!
                    </h5>
                    
                    <div class="space-y-4">
                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-red-50 border border-red-100">
                            <svg class="w-6 h-6 text-red-500 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                <path :d="mdiAlertCircleOutline" />
                            </svg>
                            <p class="text-sm text-gray-700 font-medium">
                                Panitia berhak <span class="text-red-600 font-bold">membatalkan</span> pengajuan tempat PKL jika tidak memenuhi syarat standar kompetensi atau kuota penuh.
                            </p>
                        </div>

                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-orange-50 border border-orange-100">
                             <svg class="w-6 h-6 text-orange-500 mt-0.5 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                <path :d="mdiRefresh" />
                            </svg>
                            <p class="text-sm text-gray-700 font-medium">
                                Jika ditolak, siswa wajib mengajukan ulang mulai dari <span class="text-orange-600 font-bold">Langkah 1</span>.
                            </p>
                        </div>
                    </div>
                    
                    <div class="mt-8 flex flex-wrap gap-4">
                        <div class="px-5 py-2.5 bg-gray-100 rounded-xl font-bold text-gray-700 text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            Mulai: 19 Jan 2026
                        </div>
                        <div class="px-5 py-2.5 bg-gray-100 rounded-xl font-bold text-gray-700 text-sm flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-red-500"></span>
                            Batas: 28 Feb 2026
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>

    </div>
  </div>
</template>

<style scoped>
.perspective {
    perspective: 1000px;
}
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
.animate-bounce-slow {
    animation: bounce-slow 3s infinite;
}
@keyframes bounce-slow {
    0%, 100% { transform: translateY(-5%); }
    50% { transform: translateY(5%); }
}
</style>
