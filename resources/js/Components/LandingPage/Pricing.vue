<script setup>
import ButtonOutline from "./ButtonOutline.vue";
</script>

<template>
  <div
    class="bg-gradient-to-b from-white-300 to-white-500 w-full py-14"
    id="pricing"
  >
    <div
      class="max-w-screen-xl px-6 sm:px-8 lg:px-16 mx-auto flex flex-col w-full text-center justify-center"
    >
      <div class="flex flex-col w-full">
        <h3
          class="text-2xl sm:text-3xl lg:text-4xl font-medium text-black-600 leading-relaxed"
        >
          Alur Pelaksanaan PKL
        </h3>
        <p
          class="leading-normal w-10/12 sm:w-7/12 lg:w-6/12 mx-auto my-2 text-center"
        >
          Tahapan kegiatan Praktik Kerja Lapangan yang harus dilalui oleh setiap siswa.
        </p>
        <div
          class="grid grid-flow-row sm:grid-flow-col grid-cols-1 sm:grid-cols-3 gap-8 lg:gap-12 py-8 lg:py-12 px-6 sm:px-0 lg:px-6"
        >
          <!-- Tahap 1 -->
          <div class="flex justify-center">
            <div
              class="flex flex-col justify-center items-center border-2 border-gray-ls-500 rounded-xl py-4 px-6 lg:px-12 xl:px-20 hover:border-orange-500 transition-colors"
            >
              <div class="p-4 lg:p-0 mt-6 lg:mt-16">
                <img
                  src="/assets/Free.png"
                  width="145"
                  height="165"
                  alt="Persiapan"
                />
              </div>
              <p
                class="text-lg text-black-600 font-medium capitalize my-2 sm:my-7"
              >
                Persiapan
              </p>
              <ul
                class="flex flex-col list-inside pl-6 xl:pl-0 items-start justify-start text-left text-black-500 flex-grow"
              >
                <li class="relative check custom-list my-2">
                  Administrasi & Berkas
                </li>
                <li class="relative check custom-list my-2">
                  Pembekalan Materi
                </li>
                <li class="relative check custom-list my-2">
                  Pemilihan Lokasi
                </li>
                <li class="relative check custom-list my-2">
                  Surat Pengantar
                </li>
              </ul>
              <div
                class="flex flex-col w-full justify-center mb-8 flex-none mt-12"
              >
                <p class="text-2xl text-black-600 text-center mb-4">Tahap 1</p>
                <ButtonOutline>Lihat Detail</ButtonOutline>
              </div>
            </div>
          </div>

          <!-- Tahap 2 -->
          <div class="flex justify-center">
            <div
              class="flex flex-col justify-center items-center border-2 border-gray-500 rounded-xl py-4 px-6 lg:px-12 xl:px-20 hover:border-orange-500 transition-colors"
            >
              <div class="p-4 lg:p-0 mt-6 lg:mt-16">
                <img
                  src="/assets/Standard.png"
                  width="145"
                  height="165"
                  alt="Pelaksanaan"
                />
              </div>
              <p
                class="text-lg text-black-600 font-medium capitalize my-2 sm:my-7"
              >
                Pelaksanaan
              </p>
              <ul
                class="flex flex-col list-inside pl-6 xl:pl-0 items-start justify-start text-left text-black-500 flex-grow"
              >
                <li class="relative check custom-list my-2">
                  Jurnal Kegiatan Harian
                </li>
                <li class="relative check custom-list my-2">
                  Absensi Kehadiran
                </li>
                <li class="relative check custom-list my-2">
                  Monitoring Guru
                </li>
                <li class="relative check custom-list my-2">
                  Bimbingan DUDI
                </li>
                <li class="relative check custom-list my-2">
                  Laporan Kemajuan
                </li>
              </ul>
              <div
                class="flex flex-col w-full justify-center mb-8 flex-none mt-12"
              >
                <p class="text-2xl text-black-600 text-center mb-4">
                  Tahap 2
                </p>
                <ButtonOutline>Lihat Detail</ButtonOutline>
              </div>
            </div>
          </div>

          <!-- Tahap 3 -->
          <div class="flex justify-center">
            <div
              class="flex flex-col justify-center items-center border-2 border-gray-500 rounded-xl py-4 px-6 lg:px-12 xl:px-20 hover:border-orange-500 transition-colors"
            >
              <div class="p-4 lg:p-0 mt-6 lg:mt-16">
                <img
                  src="/assets/Premium.png"
                  width="145"
                  height="165"
                  alt="Pelaporan"
                />
              </div>
              <p
                class="text-lg text-black-600 font-medium capitalize my-2 sm:my-7"
              >
                Pelaporan
              </p>
              <ul
                class="flex flex-col list-inside pl-6 xl:pl-0 items-start justify-start text-left text-black-500 flex-grow"
              >
                <li class="relative check custom-list my-2">
                  Penyusunan Laporan
                </li>
                <li class="relative check custom-list my-2">
                  Ujian / Sidang PKL
                </li>
                <li class="relative check custom-list my-2">
                  Penilaian DUDI
                </li>
                <li class="relative check custom-list my-2">
                  Penilaian Sekolah
                </li>
                <li class="relative check custom-list my-2">
                  Sertifikat PKL
                </li>
                <li class="relative check custom-list my-2">
                  Selesai
                </li>
              </ul>
              <div
                class="flex flex-col w-full justify-center mb-8 flex-none mt-12"
              >
                <p class="text-2xl text-black-600 text-center mb-4">
                  Tahap 3
                </p>
                <ButtonOutline>Lihat Detail</ButtonOutline>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
