<template>
  <div class="page">
    <section class="hero">
      <div class="hero-overlay" />
      <div class="container hero-grid">
        <div class="hero-copy">
          <div class="welcome">Welcome{{ welcomeName }} — your next island adventure starts here.</div>
          <div class="kicker">Prime Surigao</div>
          <h1 class="h1">Book Surigao island tours in minutes.</h1>
          <p class="sub">Choose a package, pick a date, and reserve your slots — secure &amp; fast.</p>

          <div class="trust">
            <div class="chip">Instant confirmation</div>
            <div class="chip">Secure payments</div>
            <div class="chip">Local guides</div>
          </div>

          <div class="hero-actions">
            <RouterLink class="btn" :to="{ name: 'about' }">Learn more</RouterLink>
            <RouterLink class="btn btn-ghost" :to="{ name: 'contact' }">Contact</RouterLink>
          </div>
        </div>
      </div>
    </section>

    <section class="container section">
      <div class="section-head">
        <div>
          <div class="section-kicker">Featured</div>
          <h2 class="h2">Most booked tour packages</h2>
          <p class="section-sub">Handpicked trips with the best feedback from travelers.</p>
        </div>
        <RouterLink class="btn btn-light" :to="auth.loggedIn ? { name: 'user.bookings' } : { name: 'login' }">Explore packages</RouterLink>
      </div>

      <div v-if="loading" class="grid-cards">
        <div v-for="i in 6" :key="i" class="p-card sk">
          <div class="p-img" />
          <div class="p-body">
            <div class="skl t" />
            <div class="skl m" />
            <div class="skl s" />
          </div>
        </div>
      </div>

      <div v-else class="grid-cards">
        <article v-for="p in featured" :key="p.id" class="p-card">
          <div class="p-img">
            <img v-if="packageImageUrl(p)" :src="packageImageUrl(p)" :alt="p.title" loading="lazy" />
            <div v-else class="p-empty">No photo</div>
          </div>
          <div class="p-body">
            <div class="p-title">{{ p.title }}</div>
            <div class="p-price">From <b>₱{{ formatMoney(p.price) }}</b> / pax</div>
            <div class="p-meta">
              <span class="pill">Slots/day: {{ p.slots_per_day }}</span>
              <span class="pill pill-muted">Surigao</span>
            </div>
            <button class="btn btn-light" type="button" @click="prefill(p)">Book this package</button>
          </div>
        </article>

        <div v-if="!featured.length" class="empty">No packages available right now.</div>
      </div>
    </section>

    <section class="container section">
      <div class="two">
        <div class="panel">
          <div class="section-kicker">Social proof</div>
          <h2 class="h2">Trusted by travelers</h2>
          <p class="section-sub">A smooth booking experience — with transparent availability.</p>

          <div class="quotes">
            <div class="quote">
              <div class="stars">★★★★★</div>
              <div class="qt">“Fast booking and great support. The island hopping was unforgettable.”</div>
              <div class="qn">— Angel, Customer</div>
            </div>
            <div class="quote">
              <div class="stars">★★★★★</div>
              <div class="qt">“Clean system and easy payment steps. Highly recommended.”</div>
              <div class="qn">— Mark, Customer</div>
            </div>
          </div>
        </div>

        <div class="panel panel-accent">
          <div class="section-kicker">How it works</div>
          <h2 class="h2">3 steps to your trip</h2>
          <div class="steps">
            <div class="step">
              <div class="n">1</div>
              <div>
                <div class="st">Choose a package</div>
                <div class="sd">Browse curated tours with clear pricing.</div>
              </div>
            </div>
            <div class="step">
              <div class="n">2</div>
              <div>
                <div class="st">Pick date &amp; pax</div>
                <div class="sd">See available slots and reserve your seats.</div>
              </div>
            </div>
            <div class="step">
              <div class="n">3</div>
              <div>
                <div class="st">Pay &amp; confirm</div>
                <div class="sd">Secure payments, admin verification, real-time updates.</div>
              </div>
            </div>
          </div>

          <RouterLink class="btn btn-wide" :to="{ name: 'login' }">Get started</RouterLink>
        </div>
      </div>
    </section>

    <footer class="footer">
      <div class="container footer-grid">
        <div>
          <div class="brand">Prime Surigao</div>
          <div class="muted">Travel &amp; Tour Agency booking platform.</div>
        </div>
        <div class="foot-links">
          <RouterLink class="f" :to="{ name: 'home' }">Home</RouterLink>
          <RouterLink class="f" :to="{ name: 'about' }">About</RouterLink>
          <RouterLink class="f" :to="{ name: 'contact' }">Contact</RouterLink>
          <RouterLink class="f" :to="{ name: 'login' }">Login</RouterLink>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { auth } from '../../stores/auth';

const loading = ref(true);
const packages = ref([]);

const featured = computed(() => packages.value.slice(0, 6));

const welcomeName = computed(() => {
  const n = String(auth?.name || '').trim();
  if (!auth.loggedIn || !n) return '';
  const first = n.split(' ')[0];
  return first ? `, ${first}` : '';
});

function formatMoney(value) {
  const n = Number(value || 0);
  try {
    return new Intl.NumberFormat('en-PH').format(n);
  } catch {
    return String(n);
  }
}

function packageImageUrl(p) {
  const u = String(p?.image_url || '').trim();
  if (!u) return '';
  if (/^https?:\/\//i.test(u) || u.startsWith('/')) return u;
  return '';
}

async function load() {
  loading.value = true;
  try {
    const res = await window.axios.get('/api/packages');
    packages.value = res.data.packages || [];
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@600;700;800&display=swap');

.page{min-height:calc(100vh - 60px);}
.container{max-width:1100px;margin:0 auto;padding:0 18px;}

.hero{position:relative;min-height:420px;display:flex;align-items:stretch;background-image:url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=60');background-size:cover;background-position:center;border-bottom:1px solid rgba(226,232,240,0.7);}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(90deg,rgba(2,8,23,0.78),rgba(2,8,23,0.38));}
.hero-grid{position:relative;z-index:1;display:grid;grid-template-columns:1fr;gap:22px;align-items:center;padding:42px 0;}

.hero-copy{color:#fff;}
.welcome{font-family:'Playfair Display',ui-serif,Georgia,'Times New Roman',serif;font-weight:800;font-size:22px;line-height:1.15;opacity:.96;margin-bottom:10px;text-shadow:0 12px 28px rgba(0,0,0,0.35);}
.kicker{font-family:'Great Vibes',cursive;font-weight:400;font-size:44px;line-height:1;opacity:.98;margin-top:2px;text-shadow:0 12px 28px rgba(0,0,0,0.35);}
.h1{margin:10px 0 10px 0;font-size:42px;line-height:1.05;letter-spacing:-.02em;text-shadow:0 14px 34px rgba(0,0,0,0.35);}
.sub{margin:0 0 16px 0;max-width:520px;opacity:.92;line-height:1.5;}

.trust{display:flex;gap:8px;flex-wrap:wrap;margin:10px 0 18px 0;}
.chip{background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);backdrop-filter:blur(6px);padding:7px 10px;border-radius:999px;font-weight:800;font-size:12px;}

.hero-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:8px;}

.btn{background:#0d6efd;color:#fff;text-decoration:none;border-radius:12px;padding:10px 14px;font-weight:900;font-size:14px;display:inline-flex;align-items:center;justify-content:center;border:0;cursor:pointer;}
.btn:disabled{opacity:.6;cursor:not-allowed;}
.btn-ghost{background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);}
.btn-light{background:#eff6ff;color:#0d6efd;border:1px solid #dbeafe;}
.link{color:#0d6efd;text-decoration:none;font-weight:900;}
.muted{color:#64748b;}

.section{padding:34px 0;}
.section-head{display:flex;justify-content:space-between;align-items:flex-end;gap:14px;margin-bottom:14px;flex-wrap:wrap;}
.section-kicker{color:#0d6efd;font-weight:900;letter-spacing:.08em;text-transform:uppercase;font-size:12px;}
.h2{margin:6px 0 6px 0;font-size:24px;line-height:1.2;color:#0f172a;}
.section-sub{margin:0;color:#475569;font-weight:700;font-size:13px;max-width:700px;}

.grid-cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;}
.p-card{background:#fff;border:1px solid #e6eef9;border-radius:16px;overflow:hidden;box-shadow:0 10px 28px rgba(2,8,23,0.04);}
.p-img{height:150px;background:#f1f5f9;display:flex;align-items:center;justify-content:center;}
.p-img img{width:100%;height:100%;object-fit:cover;display:block;}
.p-empty{color:#64748b;font-weight:900;font-size:13px;}
.p-body{padding:14px;display:flex;flex-direction:column;gap:10px;}
.p-title{font-weight:1000;color:#0f172a;}
.p-price{color:#334155;font-weight:800;font-size:13px;}
.p-meta{display:flex;gap:8px;flex-wrap:wrap;}
.pill{border-radius:999px;padding:6px 10px;background:#06b6d4;color:#fff;font-weight:900;font-size:12px;}
.pill-muted{background:#f1f5f9;color:#0f172a;border:1px solid #e2e8f0;}
.empty{color:#64748b;font-weight:900;padding:10px 0;}

.two{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.panel{background:#fff;border:1px solid #e6eef9;border-radius:16px;padding:18px;box-shadow:0 10px 28px rgba(2,8,23,0.04);}
.panel-accent{background:linear-gradient(180deg,#ffffff,#f8fbff);}
.quotes{display:grid;gap:12px;margin-top:12px;}
.quote{border:1px solid #eef2f7;border-radius:14px;padding:14px;background:#fff;}
.stars{color:#f59e0b;font-weight:1000;letter-spacing:.12em;margin-bottom:8px;}
.qt{color:#0f172a;font-weight:800;line-height:1.45;}
.qn{color:#64748b;font-weight:900;margin-top:10px;font-size:12px;}

.steps{display:grid;gap:12px;margin-top:12px;margin-bottom:14px;}
.step{display:flex;gap:12px;align-items:flex-start;border:1px solid #eef2f7;border-radius:14px;padding:12px;background:#fff;}
.step .n{width:32px;height:32px;border-radius:12px;background:#0d6efd;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:1000;}
.st{font-weight:1000;color:#0f172a;}
.sd{color:#475569;font-weight:800;font-size:13px;margin-top:4px;}

.footer{padding:22px 0;border-top:1px solid #e6eef9;background:#fff;}
.footer-grid{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;flex-wrap:wrap;}
.brand{font-weight:1000;color:#0f172a;}
.foot-links{display:flex;gap:12px;flex-wrap:wrap;}
.f{color:#0d6efd;text-decoration:none;font-weight:900;}

.sk .p-img{background:linear-gradient(90deg,#f1f5f9,#e2e8f0,#f1f5f9);background-size:200% 100%;animation:sh 1.2s linear infinite;}
.sk .skl{border-radius:10px;background:linear-gradient(90deg,#f1f5f9,#e2e8f0,#f1f5f9);background-size:200% 100%;animation:sh 1.2s linear infinite;}
.sk .t{height:14px;width:70%;}
.sk .m{height:12px;width:85%;}
.sk .s{height:12px;width:55%;}
@keyframes sh{0%{background-position:0% 0}100%{background-position:200% 0}}

@media(max-width:980px){
  .grid-cards{grid-template-columns:1fr;}
  .two{grid-template-columns:1fr;}
  .welcome{font-size:18px;}
  .kicker{font-size:38px;}
  .h1{font-size:34px;}
}
</style>
