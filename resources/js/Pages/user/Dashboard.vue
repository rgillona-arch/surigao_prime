<template>
  <div class="page">
    <div class="hero">
      <div>
        <div class="hero-title">Your Travel Dashboard</div>
        <div class="hero-sub">Overview of your trips and payment status.</div>
      </div>

      <div class="stats">
        <div class="stat">
          <div class="k">Trips</div>
          <div class="v">{{ stats.total }}</div>
        </div>
        <div class="stat">
          <div class="k">Pending</div>
          <div class="v">{{ stats.pending }}</div>
        </div>
        <div class="stat">
          <div class="k">Approved</div>
          <div class="v">{{ stats.approved }}</div>
        </div>
        <div class="stat">
          <div class="k">Paid</div>
          <div class="v">{{ stats.paid }}</div>
        </div>
      </div>
    </div>

    <div v-if="loading">Loading...</div>

    <div v-else class="content">
      <section class="card card-lg overview">
        <div class="overview-title">Quick Actions</div>
        <RouterLink class="big-link" :to="{ name: 'user.bookings' }">Open Bookings</RouterLink>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';

const loading = ref(true);
const bookings = ref([]);

const stats = computed(() => {
  const total = bookings.value.length;
  const pending = bookings.value.filter((b) => b?.status === 'Pending').length;
  const approved = bookings.value.filter((b) => b?.status === 'Approved').length;
  const paid = bookings.value.filter((b) => b?.payment_status === 'Paid').length;
  return { total, pending, approved, paid };
});

async function load() {
  loading.value = true;
  const res = await window.axios.get('/api/user/dashboard');
  bookings.value = res.data.bookings;
  loading.value = false;
}

onMounted(load);
</script>

<style scoped>
.page{max-width:1100px;margin:0 auto;padding:18px 14px 26px;}
.content{display:flex;flex-direction:column;gap:14px;}
.hero{display:flex;align-items:flex-end;justify-content:space-between;gap:14px;margin-bottom:14px;}
.hero-title{font-size:22px;font-weight:1000;color:#0f172a;letter-spacing:-0.02em;}
.hero-sub{margin-top:4px;color:#64748b;font-weight:800;font-size:13px;}
.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;min-width:420px;}
.stat{background:linear-gradient(180deg,#ffffff,#fbfdff);border:1px solid #e6eef9;border-radius:18px;padding:16px 16px;box-shadow:0 10px 28px rgba(2,8,23,0.04);}
.stat .k{color:#64748b;font-weight:900;font-size:12px;}
.stat .v{color:#0f172a;font-weight:1000;font-size:28px;margin-top:6px;letter-spacing:-0.02em;}
.card{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:14px;margin-bottom:16px;}
.card-lg{border-radius:16px;box-shadow:0 10px 28px rgba(2,8,23,0.04);}

.overview{padding:18px;}
.overview-title{font-weight:1000;color:#0f172a;margin-bottom:10px;}
.big-link{display:inline-flex;align-items:center;justify-content:center;text-decoration:none;background:#0d6efd;color:#fff;border-radius:14px;padding:14px 16px;font-weight:1000;min-height:52px;}

@media(max-width:900px){
  .hero{flex-direction:column;align-items:flex-start;}
  .stats{grid-template-columns:repeat(2,minmax(0,1fr));min-width:unset;width:100%;}
}
</style>
