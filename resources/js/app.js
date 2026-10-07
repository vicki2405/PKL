import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { createPinia } from 'pinia';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const permissionRouteMap = {
    '/admin/jurusan': 'jurusan',
    '/admin/kelas': 'kelas',
    '/admin/siswa': 'siswa',
    '/admin/jabatan-pembimbing': 'jabatan_pembimbing',
    '/admin/pembimbing': 'pembimbing',
    '/admin/bengkel': 'bengkel',
    '/admin/input-data-pkl': 'input_data_pkl',
    '/admin/mutasi-pkl': 'mutasi_pkl',
    '/admin/monitoring': 'monitoring',
    '/admin/rekap-absensi': 'rekap_absensi',
    '/admin/cetak-p1': 'cetak_p1',
    '/admin/surat-pengantar': 'surat_pengantar',
    '/admin/rekap-data-pkl': 'rekap_data_pkl',
    '/admin/rekap-karir': 'rekap_karir',
    '/admin/informasi': 'informasi',
    '/admin/rekap-problem': 'rekap_problem',
    '/admin/users/pembimbing': 'user_pembimbing',
    '/admin/users/siswa': 'user_siswa',
    '/admin/data-admin': 'data_admin',
    '/admin/roles': 'role',
    '/admin/permissions': 'permission',
    '/admin/backup': 'backup',
};

const resolveResourceByPath = (urlPath) => {
    const path = String(urlPath || '').split('?')[0];
    const entries = Object.entries(permissionRouteMap).sort((a, b) => b[0].length - a[0].length);

    for (const [routePrefix, resource] of entries) {
        if (path === routePrefix || path.startsWith(`${routePrefix}/`)) {
            return resource;
        }
    }

    return null;
};

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        const pinia = createPinia();

        app.config.globalProperties.$hasPermission = function (permission) {
            const user = this.$page?.props?.auth?.user;
            if (!user) return false;
            const roles = Array.isArray(user.roles) ? user.roles : [];
            if (roles.includes('admin') || roles.includes('super-admin')) return true;
            const permissions = Array.isArray(user.permissions) ? user.permissions : [];
            return permissions.includes(permission);
        };

        app.config.globalProperties.$can = function (action, resource = null) {
            const user = this.$page?.props?.auth?.user;
            if (!user) return false;
            const roles = Array.isArray(user.roles) ? user.roles : [];
            if (roles.includes('admin') || roles.includes('super-admin')) return true;
            const resolvedResource = resource || resolveResourceByPath(this.$page?.url);
            if (!resolvedResource || !action) return true;
            const permissions = Array.isArray(user.permissions) ? user.permissions : [];
            return permissions.includes(`${resolvedResource}.${action}`);
        };

        return app
            .use(plugin)
            .use(ZiggyVue)
            .use(pinia)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
