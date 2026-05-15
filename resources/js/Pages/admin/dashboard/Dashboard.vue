<template>
  <div class="page">
    <div class="hero">
      <div class="hero-overlay" />
      <div class="hero-content">
        <div>
          <div class="eyebrow">Admin</div>
          <h2 class="title">Dashboard</h2>
          <div class="subtitle">Bookings overview, revenue, and quick access to management pages.</div>
        </div>

        <div class="hero-actions">
          <RouterLink class="btn" :to="{ name: 'admin.bookings.index' }">Manage Bookings</RouterLink>
          <RouterLink class="btn btn-light" :to="{ name: 'admin.packages.index' }">Manage Packages</RouterLink>
        </div>
      </div>
    </div>

    <div v-if="loading" class="loading">
      <div class="skeleton hero-skel" />
      <div class="skeleton-grid">
        <div v-for="i in 4" :key="i" class="skeleton kpi-skel" />
      </div>
    </div>

    <div v-else>
      <div class="kpis">
        <div class="kpi">
          <div class="kpi-top">
            <div class="k">Total bookings</div>
          </div>
          <div class="v">{{ summary.totalBookings }}</div>
          <div class="hint">All-time</div>
        </div>

        <div class="kpi">
          <div class="kpi-top">
            <div class="k">Pending</div>
          </div>
          <div class="v">{{ summary.pendingBookings }}</div>
          <div class="hint">Awaiting approval</div>
        </div>

        <div class="kpi">
          <div class="kpi-top">
            <div class="k">Approved</div>
          </div>
          <div class="v">{{ summary.approvedBookings }}</div>
          <div class="hint">Confirmed trips</div>
        </div>

        <div class="kpi kpi-accent">
          <div class="kpi-top">
            <div class="k">Revenue</div>
          </div>
          <div class="v">{{ formatCurrency(summary.totalRevenue) }}</div>
          <div class="hint">Based on paid bookings</div>
        </div>
      </div>

      <div class="section-head">
        <div>
          <div class="section-title">Executive summary</div>
          <div class="section-sub">Highlights from the last 7 days</div>
        </div>
        <button class="btn btn-light" type="button" @click="load" :disabled="loading">Refresh</button>
      </div>

      <div class="kpis">
        <div class="kpi">
          <div class="kpi-top">
            <div class="k">Today's bookings</div>
          </div>
          <div class="v">{{ insights.todaysBookings }}</div>
          <div class="hint">Created today</div>
        </div>

        <div class="kpi">
          <div class="kpi-top">
            <div class="k">Pending payments</div>
          </div>
          <div class="v">{{ insights.submittedPayments }}</div>
          <div class="hint">Submitted / Cash pending</div>
        </div>

        <div class="kpi">
          <div class="kpi-top">
            <div class="k">Cancellation rate</div>
          </div>
          <div class="v">{{ insights.cancellationRate }}%</div>
          <div class="hint">{{ insights.cancelledBookings }} cancelled total</div>
        </div>

        <div class="kpi">
          <div class="kpi-top">
            <div class="k">Top package</div>
          </div>
          <div class="v">{{ insights.topPackage?.title || '—' }}</div>
          <div class="hint" v-if="insights.topPackage">{{ insights.topPackage.count }} bookings</div>
          <div class="hint" v-else>Not enough data yet</div>
        </div>
      </div>

      <div class="kpis">
        <RouterLink class="kpi kpi-link" :to="{ name: 'admin.bookings.index' }">
          <div class="kpi-top">
            <div class="k">Quick action</div>
          </div>
          <div class="v">Bookings</div>
          <div class="hint">Approve, reject, mark paid</div>
        </RouterLink>

        <RouterLink class="kpi kpi-link" :to="{ name: 'admin.packages.index' }">
          <div class="kpi-top">
            <div class="k">Quick action</div>
          </div>
          <div class="v">Packages</div>
          <div class="hint">Create and manage listings</div>
        </RouterLink>

        <RouterLink class="kpi kpi-link" :to="{ name: 'admin.reports.summary' }">
          <div class="kpi-top">
            <div class="k">Quick action</div>
          </div>
          <div class="v">Reports</div>
          <div class="hint">Revenue & bookings summary</div>
        </RouterLink>

        <RouterLink class="kpi kpi-link" :to="{ name: 'admin.audit.index' }">
          <div class="kpi-top">
            <div class="k">Quick action</div>
          </div>
          <div class="v">Audit logs</div>
          <div class="hint">Track important actions</div>
        </RouterLink>
      </div>

      <div class="grid">
        <section class="card">
          <div class="card-head">
            <div>
              <div class="card-title">Bookings trend (7 days)</div>
              <div class="card-sub">Daily new bookings</div>
            </div>
            <div class="chip">{{ totalTrend }} total</div>
          </div>

          <div v-if="!charts.bookings7d || charts.bookings7d.length === 0" class="empty">No trend data yet.</div>

          <div v-else class="chart">
            <svg class="spark" viewBox="0 0 300 80" preserveAspectRatio="none">
              <polyline :points="sparkPoints(charts.bookings7d)" fill="none" stroke="#0d6efd" stroke-width="3" />
            </svg>
            <div class="bars">
              <div v-for="p in charts.bookings7d" :key="p.label" class="bar">
                <div class="bar-fill" :style="{ height: barHeight(p.value, maxBookings) }" />
                <div class="bar-label">{{ p.label }}</div>
              </div>
            </div>
          </div>
        </section>

        <section class="card">
          <div class="card-head">
            <div>
              <div class="card-title">Revenue trend (7 days)</div>
              <div class="card-sub">Approved bookings revenue</div>
            </div>
            <div class="chip">{{ formatCurrency(insights.weekRevenue) }}</div>
          </div>

          <div v-if="!charts.revenue7d || charts.revenue7d.length === 0" class="empty">No revenue data yet.</div>

          <div v-else class="chart">
            <svg class="spark" viewBox="0 0 300 80" preserveAspectRatio="none">
              <polyline :points="sparkPoints(charts.revenue7d)" fill="none" stroke="#16a34a" stroke-width="3" />
            </svg>
            <div class="bars">
              <div v-for="p in charts.revenue7d" :key="p.label" class="bar">
                <div class="bar-fill green" :style="{ height: barHeight(p.value, maxRevenue) }" />
                <div class="bar-label">{{ p.label }}</div>
              </div>
            </div>
          </div>
        </section>

        <section class="card">
          <div class="card-head">
            <div>
              <div class="card-title">Payments breakdown</div>
              <div class="card-sub">By method</div>
            </div>
            <RouterLink class="link" :to="{ name: 'admin.bookings.index' }">Open bookings</RouterLink>
          </div>

          <div v-if="!charts.paymentMethods || charts.paymentMethods.length === 0" class="empty">No payment data yet.</div>

          <div v-else class="pie">
            <div v-for="m in charts.paymentMethods" :key="m.method" class="pie-row">
              <div class="pie-left">
                <div class="pie-title">{{ formatMethod(m.method) }}</div>
                <div class="pie-sub">{{ m.count }} bookings</div>
              </div>
              <div class="pie-bar">
                <div class="pie-bar-fill" :style="{ width: pct(m.count, maxMethodCount) }" />
              </div>
            </div>
          </div>
        </section>

        <section class="card">
          <div class="card-head">
            <div>
              <div class="card-title">Recent activity</div>
              <div class="card-sub">Latest admin/user actions</div>
            </div>
            <RouterLink class="link" :to="{ name: 'admin.audit.index' }">View logs</RouterLink>
          </div>

          <div v-if="!recentActivity || recentActivity.length === 0" class="empty">No recent activity.</div>

          <div v-else class="activity">
            <div v-for="a in recentActivity" :key="a.id" class="activity-row">
              <div class="dot" />
              <div class="activity-body">
                <div class="activity-top">
                  <div class="activity-action">{{ a.action }}</div>
                  <div class="activity-time">{{ formatTime(a.created_at) }}</div>
                </div>
                <div class="activity-sub">
                  <span v-if="a.user">{{ a.user.name }}</span>
                  <span v-else>System</span>
                  <span v-if="a.entity_type"> · {{ shortType(a.entity_type) }} #{{ a.entity_id }}</span>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';

const loading = ref(true);
const summary = ref({
  totalBookings: 0,
  pendingBookings: 0,
  approvedBookings: 0,
  totalRevenue: 0,
});

const insights = ref({
  todaysBookings: 0,
  weekBookings: 0,
  weekRevenue: 0,
  submittedPayments: 0,
  paidBookings: 0,
  cancelledBookings: 0,
  cancellationRate: 0,
  topPackage: null,
});

const charts = ref({
  bookings7d: [],
  revenue7d: [],
  paymentMethods: [],
});

const recentActivity = ref([]);

async function load() {
  loading.value = true;
  try {
    const res = await window.axios.get('/api/admin/dashboard');
    summary.value = res.data.summary;
    insights.value = res.data.insights || insights.value;
    charts.value = res.data.charts || charts.value;
    recentActivity.value = res.data.recentActivity || [];
  } finally {
    loading.value = false;
  }
}

function formatCurrency(amount) {
  const n = Number(amount || 0);
  try {
    return new Intl.NumberFormat(undefined, { style: 'currency', currency: 'PHP' }).format(n);
  } catch (e) {
    return `₱${n.toFixed(2)}`;
  }
}

function formatMethod(m) {
  const s = String(m || '').trim().toLowerCase();
  if (!s || s === 'unknown') return 'Unknown';
  if (s === 'gcash') return 'GCash';
  if (s === 'bank_transfer') return 'Bank transfer';
  if (s === 'card') return 'Card';
  if (s === 'cash') return 'Cash';
  return s.replace(/_/g, ' ');
}

function pct(v, max) {
  const n = Number(v || 0);
  const d = Number(max || 0);
  if (d <= 0) return '0%';
  return `${Math.max(0, Math.min(100, Math.round((n / d) * 100)))}%`;
}

function barHeight(v, max) {
  const n = Number(v || 0);
  const d = Number(max || 0);
  if (d <= 0) return '0%';
  return `${Math.max(6, Math.round((n / d) * 100))}%`;
}

function sparkPoints(series) {
  const arr = Array.isArray(series) ? series : [];
  const values = arr.map((p) => Number(p?.value || 0));
  const max = Math.max(1, ...values);
  const w = 300;
  const h = 80;
  if (values.length <= 1) {
    const y = h - (Number(values[0] || 0) / max) * (h - 10) - 5;
    return `0,${y} ${w},${y}`;
  }
  return values
    .map((v, i) => {
      const x = (i / (values.length - 1)) * w;
      const y = h - (v / max) * (h - 10) - 5;
      return `${x.toFixed(1)},${y.toFixed(1)}`;
    })
    .join(' ');
}

function shortType(t) {
  if (!t) return '';
  const parts = String(t).split('\\');
  return parts[parts.length - 1] || t;
}

function formatTime(ts) {
  if (!ts) return '';
  return new Date(ts).toLocaleString();
}

const totalTrend = computed(() => (charts.value.bookings7d || []).reduce((a, p) => a + Number(p?.value || 0), 0));
const maxBookings = computed(() => Math.max(1, ...(charts.value.bookings7d || []).map((p) => Number(p?.value || 0))));
const maxRevenue = computed(() => Math.max(1, ...(charts.value.revenue7d || []).map((p) => Number(p?.value || 0))));
const maxMethodCount = computed(() => Math.max(1, ...(charts.value.paymentMethods || []).map((p) => Number(p?.count || 0))));

onMounted(load);
</script>

<style scoped>
.page{max-width:1100px;margin:0 auto;padding:18px;}
.hero{position:relative;overflow:hidden;border-radius:16px;min-height:140px;background:#0b4b73;margin-bottom:16px;border:1px solid rgba(255,255,255,0.14);}
.hero::before{content:'';position:absolute;inset:0;background-image:url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1200&q=60');background-position:center;background-size:cover;transform:scale(1.02);}
.hero-overlay{position:absolute;inset:0;background:rgba(0,0,0,0.38);}
.hero-content{position:relative;z-index:2;padding:18px;display:flex;align-items:center;justify-content:space-between;gap:14px;}
.eyebrow{color:rgba(255,255,255,0.92);font-weight:900;font-size:12px;letter-spacing:.08em;text-transform:uppercase;}
.title{margin:2px 0 4px;font-size:26px;line-height:1.15;color:#ffffff;}
.subtitle{color:rgba(255,255,255,0.9);font-size:14px;max-width:620px;}
.hero-actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;}
.hero .btn{background:#06b6d4;color:#fff;border:0;}
.hero .btn.btn-light{background:rgba(255,255,255,0.12);color:#fff;border:1px solid rgba(255,255,255,0.22);}
.btn{background:#0d6efd;color:#fff;border:0;border-radius:10px;padding:10px 12px;text-decoration:none;font-weight:900;font-size:13px;}
.btn.btn-light{background:#eff6ff;color:#0d6efd;border:1px solid #dbeafe;}

.loading{padding:14px;color:#64748b;}
.skeleton{background:linear-gradient(90deg,#f1f5f9,#e2e8f0,#f1f5f9);background-size:200% 100%;animation:shimmer 1.1s infinite;border-radius:14px;}
.hero-skel{height:92px;margin-bottom:12px;}
.skeleton-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;}
.kpi-skel{height:92px;}
@keyframes shimmer{0%{background-position:0 0;}100%{background-position:-200% 0;}}

.kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:16px;}
.kpi{background:#fff;border:1px solid #e6eef9;border-radius:14px;padding:14px;}
.kpi-accent{background:linear-gradient(180deg,#ffffff,#f8fbff);}
.kpi-link{text-decoration:none;display:block;}
.kpi-link:hover{border-color:#dbeafe;}
.kpi-top{display:flex;align-items:center;justify-content:space-between;gap:10px;}
.k{font-size:12px;color:#64748b;font-weight:900;letter-spacing:.02em;}
.v{font-size:22px;font-weight:950;color:#0f172a;margin-top:8px;}
.hint{color:#64748b;font-size:12px;margin-top:6px;}

.section-head{display:flex;align-items:flex-end;justify-content:space-between;gap:10px;margin:8px 0 10px;}
.section-title{font-weight:950;color:#0f172a;}
.section-sub{color:#64748b;font-size:12px;margin-top:2px;}

.grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:6px;}
.card{background:#fff;border:1px solid #e6eef9;border-radius:16px;padding:14px;}
.card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;margin-bottom:10px;}
.card-title{font-weight:950;color:#0f172a;}
.card-sub{color:#64748b;font-size:12px;margin-top:2px;}
.link{color:#0d6efd;text-decoration:none;font-weight:900;font-size:13px;}
.chip{background:#f8fafc;border:1px solid #e2e8f0;color:#0f172a;font-weight:900;border-radius:999px;padding:6px 10px;font-size:12px;white-space:nowrap;}

.chart{display:flex;flex-direction:column;gap:10px;}
.spark{width:100%;height:80px;background:linear-gradient(180deg,#ffffff,#f8fbff);border:1px solid #eef2f7;border-radius:12px;padding:6px;}
.bars{display:grid;grid-template-columns:repeat(7,1fr);gap:8px;align-items:end;}
.bar{display:flex;flex-direction:column;gap:6px;align-items:stretch;}
.bar-fill{height:40%;background:#dbeafe;border:1px solid #bfdbfe;border-radius:10px;min-height:6px;}
.bar-fill.green{background:#dcfce7;border-color:#bbf7d0;}
.bar-label{font-size:11px;color:#64748b;text-align:center;}

.pie{display:flex;flex-direction:column;gap:10px;}
.pie-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;align-items:center;}
.pie-title{font-weight:950;color:#0f172a;}
.pie-sub{color:#64748b;font-size:12px;margin-top:2px;}
.pie-bar{height:12px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:999px;overflow:hidden;}
.pie-bar-fill{height:100%;background:#0d6efd;border-radius:999px;}

.activity{display:flex;flex-direction:column;gap:10px;}
.activity-row{display:flex;gap:10px;}
.dot{width:10px;height:10px;border-radius:999px;background:#93c5fd;margin-top:6px;flex:0 0 auto;}
.activity-body{flex:1;min-width:0;}
.activity-top{display:flex;justify-content:space-between;gap:10px;align-items:baseline;}
.activity-action{font-weight:950;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.activity-time{color:#64748b;font-size:12px;white-space:nowrap;}
.activity-sub{color:#64748b;font-size:12px;margin-top:2px;}

.empty{color:#64748b;padding:6px 0;}

@media(max-width:1000px){.kpis{grid-template-columns:1fr 1fr;}.hero-content{flex-direction:column;align-items:flex-start;}.hero-actions{justify-content:flex-start;}.grid{grid-template-columns:1fr;}.skeleton-grid{grid-template-columns:1fr 1fr;}}
</style>
