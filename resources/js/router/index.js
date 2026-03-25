import { createRouter, createWebHistory } from 'vue-router';

import Home from '../Pages/pages/Home.vue';
import About from '../Pages/pages/About.vue';
import Contact from '../Pages/pages/Contact.vue';
import Login from '../Pages/pages/Login.vue';
import UserDashboard from '../Pages/user/Dashboard.vue';
import UserBookings from '../Pages/user/Bookings.vue';
import AdminDashboard from '../Pages/admin/dashboard/Dashboard.vue';
import AdminBookings from '../Pages/admin/bookings/Index.vue';
import AdminPackagesIndex from '../Pages/admin/packages/Index.vue';
import AdminPackagesCreate from '../Pages/admin/packages/Create.vue';
import AdminPackagesEdit from '../Pages/admin/packages/Edit.vue';
import AdminReportsSummary from '../Pages/admin/reports/Summary.vue';
import AdminAuditLogs from '../Pages/admin/audit/Index.vue';
import NotificationsIndex from '../Pages/notifications/Index.vue';

import { auth } from '../stores/auth';

const routes = [
    { path: '/', name: 'home', component: Home },
    { path: '/about', name: 'about', component: About },
    { path: '/contact', name: 'contact', component: Contact },
    { path: '/login', name: 'login', component: Login, meta: { guestOnly: true } },
    { path: '/user-dashboard', name: 'user.dashboard', component: UserDashboard, meta: { role: 'customer' } },
    { path: '/user-bookings', name: 'user.bookings', component: UserBookings, meta: { role: 'customer' } },
    { path: '/admin/dashboard', name: 'admin.dashboard', component: AdminDashboard, meta: { role: 'admin' } },

    { path: '/admin/bookings', name: 'admin.bookings.index', component: AdminBookings, meta: { role: 'admin' } },

    { path: '/admin/reports', name: 'admin.reports.summary', component: AdminReportsSummary, meta: { role: 'admin' } },
    { path: '/admin/audit-logs', name: 'admin.audit.index', component: AdminAuditLogs, meta: { role: 'admin' } },

    { path: '/notifications', name: 'notifications.index', component: NotificationsIndex, meta: { requiresAuth: true } },

    { path: '/admin/packages', name: 'admin.packages.index', component: AdminPackagesIndex, meta: { role: 'admin' } },
    { path: '/admin/packages/create', name: 'admin.packages.create', component: AdminPackagesCreate, meta: { role: 'admin' } },
    { path: '/admin/packages/:id/edit', name: 'admin.packages.edit', component: AdminPackagesEdit, meta: { role: 'admin' } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    if (!auth.ready) {
        await auth.refresh();
    }

    if (to.meta?.guestOnly && auth.loggedIn) {
        return auth.role === 'admin' ? { name: 'admin.dashboard' } : { name: 'user.dashboard' };
    }

    if (to.meta?.role && !auth.loggedIn) {
        return { name: 'login' };
    }

    if (to.meta?.requiresAuth && !auth.loggedIn) {
        return { name: 'login' };
    }

    if (to.meta?.role && auth.role !== to.meta.role) {
        return auth.loggedIn ? (auth.role === 'admin' ? { name: 'admin.dashboard' } : { name: 'user.dashboard' }) : { name: 'login' };
    }

    return true;
});

export default router;
