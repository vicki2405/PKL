<script setup>
import { mdiCloudUpload, mdiCloudDownload, mdiDatabase, mdiAlertCircle } from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import CardBox from '@admin/components/CardBox.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import { useForm } from '@inertiajs/vue3'
import NotificationBar from '@admin/components/NotificationBar.vue'
import { ref } from 'vue'

const props = defineProps({
  //
})

const importForm = useForm({
  file: null,
})

const confirmText = ref('')

const submitImport = () => {
  if (confirmText.value !== 'RESTORE') {
    alert('Ketik RESTORE pada kolom konfirmasi untuk melanjutkan.')
    return
  }
  if (!confirm('Apakah Anda yakin ingin merestore data? Data saat ini akan dihapus dan digantikan dengan data dari file backup.')) {
    return;
  }

  importForm.post(route('admin.backup.import'), {
    onSuccess: () => {
        importForm.reset()
        confirmText.value = ''
        // alert('Data berhasil direstore!')
    },
  })
}

const exportData = () => {
  window.location.href = route('admin.backup.export')
}
</script>

<template>
  <LayoutAuthenticated>
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiDatabase" title="Backup & Restore Data" main />

      <NotificationBar v-if="$page.props.flash.message" color="success" :icon="mdiCloudDownload">
        {{ $page.props.flash.message }}
      </NotificationBar>
      <NotificationBar v-if="$page.props.flash.error" color="danger" :icon="mdiAlertCircle">
        {{ $page.props.flash.error }}
      </NotificationBar>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Export / Backup Section -->
        <CardBox class="h-full">
          <div class="flex flex-col items-center justify-center p-6 text-center h-full">
            <div class="mb-4 p-4 bg-blue-50 rounded-full text-blue-600 dark:bg-slate-800 dark:text-blue-400">
                <svg viewBox="0 0 24 24" width="48" height="48" class="inline-block fill-current">
                    <path :d="mdiCloudDownload" />
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Export Data (Backup)</h3>
            <p class="text-gray-500 mb-6">
              Ambil data keseluruhan (Master Data, PKL, User, Admin) dalam format JSON untuk cadangan.
            </p>
            <BaseButton
              color="info"
              label="Download Backup"
              :icon="mdiCloudDownload"
              @click="exportData"
              class="w-full md:w-auto"
            />
          </div>
        </CardBox>

        <!-- Import / Restore Section -->
        <CardBox class="h-full">
          <div class="flex flex-col items-center justify-center p-6 text-center h-full">
            <div class="mb-4 p-4 bg-emerald-50 rounded-full text-emerald-600 dark:bg-slate-800 dark:text-emerald-400">
                <svg viewBox="0 0 24 24" width="48" height="48" class="inline-block fill-current">
                    <path :d="mdiCloudUpload" />
                </svg>
            </div>
            <h3 class="text-xl font-bold mb-2">Import Data (Restore)</h3>
            <p class="text-gray-500 mb-6">
              Masukkan data yang berasal dari file backup (JSON) untuk mengembalikan kondisi sistem.
              <br><span class="text-red-500 font-semibold text-sm">PERINGATAN: Data saat ini akan ditimpa!</span>
            </p>

            <div class="w-full max-w-xs">
                <input
                  type="file"
                  @input="importForm.file = $event.target.files[0]"
                  accept=".json"
                  class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:bg-slate-800 dark:border-slate-700 focus:outline-none mb-4"
                />
                <div v-if="importForm.errors.file" class="text-red-500 text-xs mb-2">
                  {{ importForm.errors.file }}
                </div>
                <input
                  type="text"
                  v-model="confirmText"
                  placeholder="Ketik RESTORE untuk mengaktifkan"
                  class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg bg-gray-50 dark:bg-slate-800 dark:border-slate-700 focus:outline-none mb-4 px-3 py-2"
                />

                <BaseButton
                  color="success"
                  label="Restore Data"
                  :icon="mdiCloudUpload"
                  :loading="importForm.processing"
                  :disabled="!importForm.file || confirmText !== 'RESTORE'"
                  @click="submitImport"
                  class="w-full"
                />
            </div>
          </div>
        </CardBox>
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>
