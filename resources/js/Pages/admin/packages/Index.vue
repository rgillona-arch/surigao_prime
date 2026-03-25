<template>
  <div>
    <div class="top">
      <h2>Packages</h2>
      <RouterLink class="btn" :to="{ name: 'admin.packages.create' }">Create</RouterLink>
    </div>

    <div class="card">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Price</th>
            <th>Slots/Day</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="p in packages" :key="p.id">
            <td>{{ p.id }}</td>
            <td>{{ p.title }}</td>
            <td>{{ p.price }}</td>
            <td>{{ p.slots_per_day }}</td>
            <td>
              <span class="badge" :class="p.is_active ? 'green' : 'gray'">
                {{ p.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="actions">
              <RouterLink class="link" :to="{ name: 'admin.packages.edit', params: { id: p.id } }">Edit</RouterLink>
              <button class="blue" @click="toggleActive(p)" :disabled="togglingId === p.id">
                {{ togglingId === p.id ? 'Saving...' : (p.is_active ? 'Deactivate' : 'Activate') }}
              </button>
              <button class="red" @click="destroy(p.id)">Delete</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';

const packages = ref([]);
const togglingId = ref(null);

async function load() {
  const res = await window.axios.get('/api/admin/packages');
  packages.value = res.data.packages;
}

async function destroy(id) {
  await window.axios.delete(`/api/admin/packages/${id}`);
  await load();
}

async function toggleActive(p) {
  togglingId.value = p.id;
  const action = p.is_active ? 'deactivate' : 'activate';
  await window.axios.post(`/api/admin/packages/${p.id}/${action}`);
  togglingId.value = null;
  await load();
}

onMounted(load);
</script>

<style scoped>
.top{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
.btn{background:#0d6efd;color:#fff;text-decoration:none;border-radius:10px;padding:9px 12px;font-weight:700;font-size:13px;}
.card{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:14px;}
.table{width:100%;border-collapse:collapse;}
.table th,.table td{border-bottom:1px solid #eef2f7;padding:8px;text-align:left;font-size:14px;}
.actions{display:flex;gap:8px;align-items:center;}
.link{color:#0d6efd;text-decoration:none;font-weight:700;}
button{background:#dc2626;color:#fff;border:0;border-radius:8px;padding:7px 10px;cursor:pointer;font-size:12px;}
.blue{background:#0d6efd;}
.badge{display:inline-block;padding:3px 8px;border-radius:999px;font-size:12px;font-weight:800;border:1px solid transparent;}
.green{background:#dcfce7;color:#166534;border-color:#bbf7d0;}
.gray{background:#f3f4f6;color:#374151;border-color:#e5e7eb;}
</style>
