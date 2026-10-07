import { createRouter, createWebHashHistory } from 'vue-router'
import Style from '@admin/views/StyleView.vue'
import Home from '@admin/views/HomeView.vue'

const routes = [
  {
    meta: {
      title: 'Select style',
    },
    path: '/',
    name: 'style',
    component: Style,
  },
  {
    // Document title tag
    // We combine it with defaultDocumentTitle set in `src/main.js` on router.afterEach hook
    meta: {
      title: 'Dashboard',
    },
    path: '/dashboard',
    name: 'dashboard',
    component: Home,
  },
  {
    meta: {
      title: 'Tables',
    },
    path: '/tables',
    name: 'tables',
    component: () => import('@admin/views/TablesView.vue'),
  },
  {
    meta: {
      title: 'Login',
    },
    path: '/login',
    name: 'login',
    component: () => import('@admin/views/LoginView.vue'),
  },
  {
    meta: {
      title: 'Error',
    },
    path: '/error',
    name: 'error',
    component: () => import('@admin/views/ErrorView.vue'),
  },
]

const router = createRouter({
  history: createWebHashHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { top: 0 }
  },
})

export default router
