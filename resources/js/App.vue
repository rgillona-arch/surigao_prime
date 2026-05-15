<template>
  <div class="app">
    <header class="header">
      <div class="brand">
        <div class="logo">SP</div>
        <div class="brand-name">
          {{ auth.loggedIn ? (auth.role === 'admin' ? 'Surigao Prime - Admin' : 'Prime Surigao') : 'Prime Surigao' }}
        </div>
      </div>

      <div class="right">
        <nav class="main-nav">
          <template v-if="!auth.loggedIn">
            <RouterLink :to="{ name: 'home' }" exact-active-class="is-active">Home</RouterLink>
            <RouterLink :to="{ name: 'about' }" exact-active-class="is-active">About</RouterLink>
            <RouterLink :to="{ name: 'contact' }" exact-active-class="is-active">Contact</RouterLink>
            <RouterLink :to="{ name: 'login' }" exact-active-class="is-active">Login</RouterLink>
          </template>

          <template v-else-if="auth.role === 'admin'">
            <RouterLink :to="{ name: 'admin.dashboard' }">Dashboard</RouterLink>
            <RouterLink :to="{ name: 'admin.bookings.index' }">Bookings</RouterLink>
            <RouterLink :to="{ name: 'admin.packages.index' }">Packages</RouterLink>
            <RouterLink :to="{ name: 'admin.reports.summary' }">Reports</RouterLink>
            <RouterLink :to="{ name: 'admin.audit.index' }">Audit Logs</RouterLink>
            <RouterLink :to="{ name: 'notifications.index' }" exact-active-class="is-active">
              Notifications
              <span v-if="unreadCount" class="notif-badge in-nav">{{ unreadCount }}</span>
            </RouterLink>
          </template>

          <template v-else>
            <RouterLink
              :to="{ name: 'user.dashboard', query: { tab: 'home' } }"
              :class="isUserDashTab('home') ? 'is-active' : ''"
              active-class=""
              exact-active-class=""
              >Home</RouterLink
            >
            <RouterLink
              :to="{ name: 'user.dashboard', query: { tab: 'packages' } }"
              :class="isUserDashTab('packages') ? 'is-active' : ''"
              active-class=""
              exact-active-class=""
              >Packages</RouterLink
            >
            <RouterLink :to="{ name: 'user.bookings' }" exact-active-class="is-active">My Bookings</RouterLink>
            <RouterLink :to="{ name: 'notifications.index' }" exact-active-class="is-active">
              Notifications
              <span v-if="unreadCount" class="notif-badge in-nav">{{ unreadCount }}</span>
            </RouterLink>
            <RouterLink
              :to="{ name: 'user.dashboard', query: { tab: 'profile' } }"
              :class="isUserDashTab('profile') ? 'is-active' : ''"
              active-class=""
              exact-active-class=""
              >Profile</RouterLink
            >
          </template>
        </nav>

        <div v-if="auth.loggedIn" class="notif" />

        <button v-if="auth.loggedIn" class="logout-btn" type="button" @click="onLogout">Logout</button>
      </div>
    </header>

    <main class="main">
      <RouterView />
    </main>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router';
import { auth } from './stores/auth';

const router = useRouter();
const route = useRoute();

const unreadCount = ref(0);
const notifLoading = ref(false);

async function loadNotifications() {
  if (!auth.loggedIn) {
    unreadCount.value = 0;
    return;
  }

  notifLoading.value = true;
  try {
    const res = await window.axios.get('/api/notifications');
    unreadCount.value = res.data.unreadCount || 0;
  } finally {
    notifLoading.value = false;
  }
}

async function onLogout() {
  await auth.logout();
  await router.push({ name: 'home' });
}

watch(
  () => auth.loggedIn,
  () => {
    loadNotifications();
  }
);

watch(
  () => route.fullPath,
  () => {
    loadNotifications();
  }
);

onMounted(() => {
  loadNotifications();
  window.addEventListener('notifications-updated', onNotificationsUpdated);
});

onUnmounted(() => {
  window.removeEventListener('notifications-updated', onNotificationsUpdated);
});

function onNotificationsUpdated(e) {
  const next = e?.detail?.unreadCount;
  if (typeof next === 'number') {
    unreadCount.value = next;
    return;
  }
  loadNotifications();
}

function isUserDashTab(tab) {
  return route.name === 'user.dashboard' && String(route.query?.tab || 'home') === tab;
}
</script>

<style scoped>
.app{min-height:100vh;background:linear-gradient(180deg,#f7fbff 0%, #ffffff 55%);}
.header{background:white;box-shadow:0 2px 6px rgba(0,0,0,0.05);display:flex;align-items:center;justify-content:space-between;padding:10px 20px;position:sticky;top:0;z-index:50}
.brand{display:flex;align-items:center;gap:8px;}
.logo{width:36px;height:36px;background:linear-gradient(180deg,#fff,#eef6ff);display:flex;align-items:center;justify-content:center;font-weight:900;color:#0d6efd;border-radius:8px;}
.brand-name{font-weight:800;color:#0b4b73;font-size:16px;}
.right{display:flex;align-items:center;gap:10px;}
.main-nav{display:flex;align-items:center;gap:8px;}
.main-nav a{text-decoration:none;color:#333;font-size:14px;padding:6px 10px;border-radius:8px;}
.main-nav a:hover{background:#eef6ff;color:#0d6efd;}
.main-nav a.router-link-active{background:#eef6ff;color:#0d6efd;}
.main-nav a.is-active{background:#0d6efd;color:white;}
.logout-btn{background:#dc3545;color:white;border:none;padding:8px 14px;border-radius:8px;cursor:pointer;font-size:13px;}
.notif{position:relative;}
.notif-btn{background:transparent;color:#333;border:0;padding:6px 10px;border-radius:8px;cursor:pointer;font-size:14px;display:flex;align-items:center;gap:8px;}
.notif-btn:hover{background:#eef6ff;}
.notif-btn:focus{outline:2px solid rgba(13,110,253,0.25);outline-offset:2px;}
.notif-btn:active{background:#e6f0ff;}
.notif-btn.open{background:#0d6efd;color:#fff;}
.notif-badge{background:#dc2626;color:#fff;border-radius:999px;padding:1px 7px;font-size:12px;font-weight:900;}
.notif-badge.in-nav{margin-left:6px;}
.notif-menu{position:absolute;right:0;top:38px;width:360px;max-width:78vw;background:#fff;border:1px solid #e6eef9;border-radius:12px;box-shadow:0 12px 40px rgba(9,30,66,0.12);overflow:hidden;z-index:60;}
.notif-item{width:100%;text-align:left;background:#fff;border:0;border-bottom:1px solid #eef2f7;padding:10px 12px;cursor:pointer;}
.notif-item.unread{background:#eff6ff;}
.notif-item .t{font-weight:900;color:#0b4b73;font-size:13px;margin-bottom:3px;}
.notif-item .m{color:#41586b;font-size:12px;line-height:1.35}
.notif-empty{padding:12px;color:#64748b;font-size:13px;}
.main{max-width:1100px;margin:0 auto;padding:20px;}
</style>
