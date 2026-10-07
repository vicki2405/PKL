<script setup>
import { Head, Link } from '@inertiajs/vue3'
import {
  mdiOfficeBuildingCog,
  mdiAccountSchool,
  mdiHumanMaleBoard,
  mdiMapMarker,
  mdiArrowLeft,
  mdiDomain,
  mdiSchool,
  mdiGoogleMaps
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import CardBox from '@admin/components/CardBox.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import BaseIcon from '@admin/components/BaseIcon.vue'

const props = defineProps({
  bengkel: {
    type: Object,
    required: true,
  },
  pembimbings: {
    type: Array,
    default: () => [],
  },
  students: {
    type: Array,
    default: () => [],
  },
})
</script>

<template>
  <LayoutAuthenticated>
    <Head :title="`Detail Penempatan - ${bengkel.nama_bengkel}`" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiOfficeBuildingCog"
        title="Detail Penempatan PKL"
        main
      >
        <BaseButtons>
          <BaseButton
            :to="route('admin.input_data_pkl.index')"
            :icon="mdiArrowLeft"
            label="Kembali"
            color="whiteDark"
            rounded-full
            small
          />
        </BaseButtons>
      </SectionTitleLineWithButton>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Bengkel Info -->
        <CardBox class="mb-6 lg:mb-0">
            <h3 class="text-lg font-bold mb-4 border-b pb-2">Data Bengkel</h3>
            <div class="space-y-3">
                <div class="flex items-start">
                    <BaseIcon :path="mdiOfficeBuildingCog" class="mr-2 text-gray-500 mt-1" :size="20" />
                    <div>
                        <p class="font-semibold">Nama Bengkel</p>
                        <p>{{ bengkel.nama_bengkel }}</p>
                        <p class="text-sm text-gray-500">{{ bengkel.kode_bengkel || '-' }}</p>
                    </div>
                </div>
                <div class="flex items-start">
                    <BaseIcon :path="mdiMapMarker" class="mr-2 text-gray-500 mt-1" :size="20" />
                    <div>
                        <p class="font-semibold">Alamat</p>
                        <p>{{ bengkel.dusun ? bengkel.dusun + ', ' : '' }}{{ bengkel.desa }}</p>
                        <p class="text-sm text-gray-500">Kec. {{ bengkel.kecamatan }}</p>
                    </div>
                </div>
                <div class="flex items-start" v-if="bengkel.google_maps_link">
                    <BaseIcon :path="mdiGoogleMaps" class="mr-2 text-gray-500 mt-1" :size="20" />
                    <div>
                        <p class="font-semibold">Koordinat</p>
                        <a
                          :href="bengkel.google_maps_link"
                          target="_blank"
                          class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 hover:underline mt-1"
                        >
                            Lihat di Google Maps
                        </a>
                    </div>
                </div>
            </div>
        </CardBox>

        <!-- Pembimbing Info -->
        <CardBox>
            <h3 class="text-lg font-bold mb-4 border-b pb-2">Data Pembimbing</h3>
            <div v-if="pembimbings.length > 0" class="space-y-4">
                <div v-for="pembimbing in pembimbings" :key="pembimbing.id" class="flex items-center">
                    <BaseIcon :path="mdiHumanMaleBoard" class="mr-4 text-blue-500" :size="48" />
                    <div>
                        <p class="text-xl font-semibold">{{ pembimbing.nama_guru }}</p>
                        <p class="text-gray-500">Jabatan: {{ pembimbing.jabatan_pembimbing?.nama_jabatan || '-' }}</p>
                    </div>
                </div>
            </div>
            <div v-else class="text-gray-500 italic">
                Belum ada pembimbing yang ditugaskan.
            </div>
        </CardBox>
      </div>

      <!-- Students List -->
      <CardBox has-table>
        <h3 class="text-lg font-bold p-6 border-b">Daftar Siswa PKL ({{ students.length }})</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-slate-800 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">No</th>
                        <th scope="col" class="px-6 py-3">Nama Siswa</th>
                        <th scope="col" class="px-6 py-3">Kelas</th>
                        <th scope="col" class="px-6 py-3">Jurusan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(student, index) in students" :key="student.id" class="bg-white border-b dark:bg-slate-900 dark:border-slate-700">
                        <td class="px-6 py-4">{{ index + 1 }}</td>
                        <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                            <div class="flex items-center">
                                <BaseIcon :path="mdiAccountSchool" class="mr-2 text-gray-400" :size="16" />
                                {{ student.nama }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                             <div class="flex items-center">
                                <BaseIcon :path="mdiDomain" class="mr-2 text-gray-400" :size="16" />
                                {{ student.kelas?.nama_kelas }}
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <BaseIcon :path="mdiSchool" class="mr-2 text-gray-400" :size="16" />
                                {{ student.kelas?.jurusan?.nama_jurusan }}
                            </div>
                        </td>
                    </tr>
                    <tr v-if="students.length === 0">
                        <td colspan="4" class="text-center py-4">Tidak ada siswa.</td>
                    </tr>
                </tbody>
            </table>
        </div>
      </CardBox>

    </SectionMain>
  </LayoutAuthenticated>
</template>
