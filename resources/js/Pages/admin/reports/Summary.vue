<template>
  <div>
    <h2>Reports</h2>

    <div class="card">
      <div class="filters">
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

      <div v-if="summary" class="summary">
        <div class="box">
          <div class="k">Approved Bookings</div>
          <div class="v">{{ summary.totalApprovedBookings }}</div>
        </div>
        <div class="box">
          <div class="k">Total Pax</div>
          <div class="v">{{ summary.totalPax }}</div>
        </div>
        <div class="box">
          <div class="k">Revenue</div>
          <div class="v">₱{{ summary.totalRevenue }}</div>
        </div>
      </div>

      <div v-if="byPackage && byPackage.length" class="mt">
        <div class="title">By Package</div>
        <table class="table">
          <thead>
            <tr>
              <th>Package</th>
              <th>Approved Bookings</th>
              <th>Total Pax</th>
              <th>Revenue</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in byPackage" :key="row.package_id">
              <td>{{ row.package_title }}</td>
              <td>{{ row.approved_bookings }}</td>
              <td>{{ row.total_pax }}</td>
              <td>₱{{ row.revenue }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else-if="summary && (!byPackage || byPackage.length === 0)" class="empty">No data for selected range.</div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue';

const from = ref('');
const to = ref('');

const loading = ref(false);
const summary = ref(null);
const byPackage = ref([]);

async function load() {
  loading.value = true;
  try {
    const res = await window.axios.get('/api/admin/reports/summary', {
      params: {
        from: from.value || undefined,
        to: to.value || undefined,
      },
    });

    summary.value = res.data.summary;
    byPackage.value = res.data.byPackage || [];
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>

<style scoped>
.card{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:14px;}
.filters{display:flex;flex-wrap:wrap;gap:12px;align-items:end;}
.blue{background:#0d6efd;color:#fff;border:0;border-radius:8px;padding:8px 12px;cursor:pointer;font-size:13px;}
.summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:12px;}
.box{border:1px solid #eef2f7;border-radius:12px;padding:12px;}
.k{font-size:12px;color:#64748b;}
.v{font-size:20px;font-weight:900;color:#0f172a;margin-top:4px;}
.mt{margin-top:14px;}
.title{font-weight:900;margin-bottom:8px;}
.table{width:100%;border-collapse:collapse;}
.table th,.table td{border-bottom:1px solid #eef2f7;padding:8px;text-align:left;font-size:14px;}
.empty{margin-top:10px;color:#64748b;}
</style>
