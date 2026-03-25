<template>
  <div>
    <h2>Audit Logs</h2>

    <div class="card">
      <div class="filters">
        <label>
          Action
          <input v-model="action" type="text" placeholder="e.g. auth.login" />
        </label>

        <label>
          User ID
          <input v-model="userId" type="number" placeholder="" />
        </label>

        <label>
          From
          <input v-model="from" type="date" />
        </label>

        <label>
          To
          <input v-model="to" type="date" />
        </label>

        <button class="blue" type="button" @click="load" :disabled="loading">
          {{ loading ? 'Loading...' : 'Load' }}
        </button>
      </div>

      <table class="table" v-if="logs.length">
        <thead>
          <tr>
            <th>Time</th>
            <th>User</th>
            <th>Action</th>
            <th>Entity</th>
            <th>IP</th>
            <th>Metadata</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="l in logs" :key="l.id">
            <td>{{ formatTime(l.created_at) }}</td>
            <td>{{ l.user ? `${l.user.name} (#${l.user.id})` : '-' }}</td>
            <td>{{ l.action }}</td>
            <td>
              <span v-if="l.entity_type">{{ shortType(l.entity_type) }} #{{ l.entity_id }}</span>
              <span v-else>-</span>
            </td>
            <td>{{ l.ip || '-' }}</td>
            <td>
              <pre class="meta">{{ pretty(l.metadata) }}</pre>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-else class="empty">
        No audit logs found.
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';

const action = ref('');
const userId = ref('');
const from = ref('');
const to = ref('');

const loading = ref(false);
const logs = ref([]);

function pretty(v) {
  try {
    return JSON.stringify(v || {}, null, 2);
  } catch {
    return '';
  }
}

function shortType(t) {
  if (!t) return '';
  const parts = String(t).split('\\');
  return parts[parts.length - 1] || t;
}

function formatTime(ts) {
  if (!ts) return '-';
  return new Date(ts).toLocaleString();
}

async function load() {
  loading.value = true;
  try {
    const res = await window.axios.get('/api/admin/audit-logs', {
      params: {
        action: action.value || undefined,
        user_id: userId.value || undefined,
        from: from.value || undefined,
        to: to.value || undefined,
      },
    });

    logs.value = res.data.logs || [];
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>

<style scoped>
.card{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:14px;}
.filters{display:flex;flex-wrap:wrap;gap:12px;align-items:end;margin-bottom:10px;}
.blue{background:#0d6efd;color:#fff;border:0;border-radius:8px;padding:8px 12px;cursor:pointer;font-size:13px;}
.table{width:100%;border-collapse:collapse;}
.table th,.table td{border-bottom:1px solid #eef2f7;padding:8px;text-align:left;font-size:13px;vertical-align:top;}
.meta{max-width:520px;white-space:pre-wrap;word-break:break-word;background:#f8fafc;border:1px solid #eef2f7;border-radius:10px;padding:8px;margin:0;}
.empty{margin-top:10px;color:#64748b;}
</style>
