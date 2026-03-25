<template>
  <div>
    <h2>Admin Dashboard</h2>

    <div v-if="loading">Loading...</div>

    <div v-else>
      <div class="stats">
        <div class="stat">
          <div class="k">Total Bookings</div>
          <div class="v">{{ summary.totalBookings }}</div>
        </div>
        <div class="stat">
          <div class="k">Pending</div>
          <div class="v">{{ summary.pendingBookings }}</div>
        </div>
        <div class="stat">
          <div class="k">Approved</div>
          <div class="v">{{ summary.approvedBookings }}</div>
        </div>
        <div class="stat">
          <div class="k">Total Revenue</div>
          <div class="v">{{ summary.totalRevenue }}</div>
        </div>
      </div>

      <section class="card">
        <h3>Latest Bookings</h3>
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Customer</th>
              <th>Package</th>
              <th>Status</th>
              <th>Payment</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="b in bookings" :key="b.id">
              <td>{{ b.id }}</td>
              <td>{{ b.customer_name }}</td>
              <td>{{ b.package?.title }}</td>
              <td>{{ b.status }}</td>
              <td>{{ b.payment_status }}</td>
            </tr>
          </tbody>
        </table>
      </section>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';

const loading = ref(true);
const summary = ref({
  totalBookings: 0,
  pendingBookings: 0,
  approvedBookings: 0,
  totalRevenue: 0,
});
const bookings = ref([]);

async function load() {
  loading.value = true;
  const res = await window.axios.get('/api/admin/dashboard');
  summary.value = res.data.summary;
  bookings.value = res.data.bookings;
  loading.value = false;
}

onMounted(load);
</script>

<style scoped>
.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;}
.stat{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:12px;}
.k{font-size:12px;color:#557188;margin-bottom:6px;}
.v{font-size:20px;font-weight:800;color:#0b4b73;}
.card{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:14px;}
.table{width:100%;border-collapse:collapse;}
.table th,.table td{border-bottom:1px solid #eef2f7;padding:8px;text-align:left;font-size:14px;}
@media(max-width:900px){.stats{grid-template-columns:1fr 1fr;}}
</style>
