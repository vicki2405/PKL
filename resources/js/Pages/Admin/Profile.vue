<script setup>
import { computed, ref } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { useMainStore } from '@admin/stores/main'
import {
  mdiAccount,
  mdiMail,
  mdiAsterisk,
  mdiFormTextboxPassword,
  mdiContentSave,
  mdiAlertCircle,
  mdiCheckCircle,
} from '@mdi/js'
import SectionMain from '@admin/components/SectionMain.vue'
import CardBox from '@admin/components/CardBox.vue'
import BaseDivider from '@admin/components/BaseDivider.vue'
import FormField from '@admin/components/FormField.vue'
import FormControl from '@admin/components/FormControl.vue'
import BaseButton from '@admin/components/BaseButton.vue'
import BaseButtons from '@admin/components/BaseButtons.vue'
import UserCard from '@admin/components/UserCard.vue'
import LayoutAuthenticated from '@admin/layouts/LayoutAuthenticated.vue'
import SectionTitleLineWithButton from '@admin/components/SectionTitleLineWithButton.vue'
import NotificationBar from '@admin/components/NotificationBar.vue'

const mainStore = useMainStore()
const page = usePage()

const currentUser = computed(() => page.props.auth?.user || {})

const profileForm = useForm({
  name: currentUser.value.name || mainStore.userName || '',
  email: currentUser.value.email || mainStore.userEmail || '',
})

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const profileSuccessMessage = ref('')
const passwordSuccessMessage = ref('')

const submitProfile = () => {
  profileSuccessMessage.value = ''
  profileForm.patch(route('profile.update'), {
    preserveScroll: true,
    onSuccess: () => {
      mainStore.setUser({
        name: profileForm.name,
        email: profileForm.email,
      })
      profileSuccessMessage.value = 'Profil berhasil diperbarui.'
      setTimeout(() => {
        profileSuccessMessage.value = ''
      }, 4000)
    },
  })
}

const submitPass = () => {
  passwordSuccessMessage.value = ''
  passwordForm.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => {
      passwordForm.reset()
      passwordSuccessMessage.value = 'Kata sandi berhasil diubah.'
      setTimeout(() => {
        passwordSuccessMessage.value = ''
      }, 4000)
    },
  })
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Profil Akun" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiAccount" title="Profil Akun" main />

      <NotificationBar
        v-if="$page.props.flash?.message || $page.props.flash?.success"
        color="success"
        :icon="mdiCheckCircle"
        class="mb-6"
      >
        {{ $page.props.flash.message || $page.props.flash.success }}
      </NotificationBar>

      <NotificationBar
        v-if="$page.props.flash?.error"
        color="danger"
        :icon="mdiAlertCircle"
        class="mb-6"
      >
        {{ $page.props.flash.error }}
      </NotificationBar>

      <UserCard class="mb-6" />

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Edit Profile Form -->
        <CardBox is-form @submit.prevent="submitProfile">
          <div class="font-bold text-lg mb-4 text-slate-800 dark:text-white">
            Informasi Profil
          </div>

          <NotificationBar
            v-if="profileSuccessMessage"
            color="success"
            :icon="mdiCheckCircle"
            class="mb-4"
          >
            {{ profileSuccessMessage }}
          </NotificationBar>

          <FormField label="Nama Lengkap" :error="profileForm.errors.name">
            <FormControl
              v-model="profileForm.name"
              :icon="mdiAccount"
              name="name"
              required
              autocomplete="name"
              placeholder="Masukkan nama lengkap"
            />
          </FormField>

          <FormField label="Email" :error="profileForm.errors.email">
            <FormControl
              v-model="profileForm.email"
              :icon="mdiMail"
              type="email"
              name="email"
              required
              autocomplete="email"
              placeholder="Masukkan alamat email"
            />
          </FormField>

          <template #footer>
            <BaseButtons>
              <BaseButton
                color="info"
                type="submit"
                label="Simpan Profil"
                :icon="mdiContentSave"
                :loading="profileForm.processing"
              />
            </BaseButtons>
          </template>
        </CardBox>

        <!-- Change Password Form -->
        <CardBox is-form @submit.prevent="submitPass">
          <div class="font-bold text-lg mb-4 text-slate-800 dark:text-white">
            Perbarui Kata Sandi
          </div>

          <NotificationBar
            v-if="passwordSuccessMessage"
            color="success"
            :icon="mdiCheckCircle"
            class="mb-4"
          >
            {{ passwordSuccessMessage }}
          </NotificationBar>

          <FormField
            label="Kata Sandi Saat Ini"
            :error="passwordForm.errors.current_password"
          >
            <FormControl
              v-model="passwordForm.current_password"
              :icon="mdiAsterisk"
              name="current_password"
              type="password"
              required
              autocomplete="current-password"
              placeholder="Masukkan kata sandi saat ini"
            />
          </FormField>

          <BaseDivider />

          <FormField
            label="Kata Sandi Baru"
            :error="passwordForm.errors.password"
          >
            <FormControl
              v-model="passwordForm.password"
              :icon="mdiFormTextboxPassword"
              name="password"
              type="password"
              required
              autocomplete="new-password"
              placeholder="Minimal 8 karakter"
            />
          </FormField>

          <FormField
            label="Konfirmasi Kata Sandi Baru"
            :error="passwordForm.errors.password_confirmation"
          >
            <FormControl
              v-model="passwordForm.password_confirmation"
              :icon="mdiFormTextboxPassword"
              name="password_confirmation"
              type="password"
              required
              autocomplete="new-password"
              placeholder="Ulangi kata sandi baru"
            />
          </FormField>

          <template #footer>
            <BaseButtons>
              <BaseButton
                type="submit"
                color="info"
                label="Perbarui Sandi"
                :icon="mdiContentSave"
                :loading="passwordForm.processing"
              />
            </BaseButtons>
          </template>
        </CardBox>
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>
