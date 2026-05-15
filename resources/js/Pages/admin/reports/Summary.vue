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

        <button class="gray" type="button" @click="exportCsv" :disabled="loading || !summary">
          Export CSV
        </button>

        <button class="light" type="button" @click="printReport" :disabled="!summary">
          Print
        </button>
      </div>

      <div class="print-head" v-if="summary">
        <div class="print-title">Surigao Prime - Reports Summary</div>
        <div class="print-sub">Generated: {{ new Date().toLocaleString() }}</div>
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

function exportCsv() {
  const qs = new URLSearchParams();
  if (from.value) qs.set('from', from.value);
  if (to.value) qs.set('to', to.value);
  const url = `/api/admin/reports/summary.csv${qs.toString() ? `?${qs.toString()}` : ''}`;
  window.open(url, '_blank');
}

function printReport() {
  window.print();
}

onMounted(load);
</script>

<style scoped>
.card{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:14px;}
.filters{display:flex;flex-wrap:wrap;gap:12px;align-items:end;}
.blue{background:#0d6efd;color:#fff;border:0;border-radius:8px;padding:8px 12px;cursor:pointer;font-size:13px;}
.gray{background:#64748b;color:#fff;border:0;border-radius:8px;padding:8px 12px;cursor:pointer;font-size:13px;}
.light{background:#eff6ff;color:#0d6efd;border:1px solid #dbeafe;border-radius:8px;padding:8px 12px;cursor:pointer;font-size:13px;font-weight:900;}
.summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:12px;}
.box{border:1px solid #eef2f7;border-radius:12px;padding:12px;}
.k{font-size:12px;color:#64748b;}
.v{font-size:20px;font-weight:900;color:#0f172a;margin-top:4px;}
.mt{margin-top:14px;}
.title{font-weight:900;margin-bottom:8px;}
.table{width:100%;border-collapse:collapse;}
.table th,.table td{border-bottom:1px solid #eef2f7;padding:8px;text-align:left;font-size:14px;}
.empty{margin-top:10px;color:#64748b;}

.print-head{display:none;margin-top:10px;padding:10px;border:1px solid #eef2f7;border-radius:12px;background:#f8fafc;}
.print-title{font-weight:900;color:#0f172a;}
.print-sub{color:#64748b;font-size:12px;margin-top:2px;}

@media print{
  .filters{display:none;}
  .print-head{display:block;}
  .card{border:0;}
}
</style>
