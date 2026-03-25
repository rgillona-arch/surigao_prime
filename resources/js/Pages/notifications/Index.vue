<template>
  <div>
    <h2>Notifications</h2>

    <div class="card">
      <div class="top">
        <div class="muted">Unread: {{ unreadCount }}</div>
        <button class="blue" type="button" @click="load" :disabled="loading">
          {{ loading ? 'Loading...' : 'Refresh' }}
        </button>
      </div>

      <div v-if="loading" class="empty">Loading...</div>

      <div v-else-if="notifications.length === 0" class="empty">No notifications.</div>

      <div v-else class="list">
        <button v-for="n in notifications" :key="n.id" class="item" :class="n.read_at ? '' : 'unread'" type="button" @click="markRead(n)">
          <div class="t">{{ n.title }}</div>
          <div class="m">{{ n.message }}</div>
          <div class="d">{{ formatTime(n.created_at) }}</div>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';

const loading = ref(false);
const notifications = ref([]);
const unreadCount = ref(0);

function formatTime(ts) {
  if (!ts) return '';
  return new Date(ts).toLocaleString();
}

async function load() {
  loading.value = true;
  try {
    const res = await window.axios.get('/api/notifications');
    notifications.value = res.data.notifications || [];
    unreadCount.value = res.data.unreadCount || 0;
  } finally {
    loading.value = false;
  }
}

async function markRead(n) {
  if (!n?.id) return;
  await window.axios.post(`/api/notifications/${n.id}/read`);
  await load();
}

onMounted(load);
</script>

<style scoped>
.card{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:14px;}
.top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;gap:10px;}
.blue{background:#0d6efd;color:#fff;border:0;border-radius:8px;padding:8px 12px;cursor:pointer;font-size:13px;}
.muted{color:#64748b;font-size:13px;font-weight:800;}
.list{display:flex;flex-direction:column;gap:8px;}
.item{border:1px solid #eef2f7;background:#fff;border-radius:12px;padding:12px;text-align:left;cursor:pointer;}
.item.unread{border-color:#bfdbfe;background:#eff6ff;}
.t{font-weight:900;color:#0f172a;margin-bottom:4px;}
.m{color:#334155;font-size:14px;}
.d{color:#64748b;font-size:12px;margin-top:6px;}
.empty{color:#64748b;padding:10px 0;}
</style>
