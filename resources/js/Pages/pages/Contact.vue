<template>
  <div class="page">
    <section class="hero">
      <div class="hero-overlay" />
      <div class="container hero-grid">
        <div class="hero-copy">
          <div class="kicker">Prime Surigao</div>
          <h1 class="h1">Contact</h1>
          <p class="sub">Questions about packages, dates, or payments? Send us a message — we typically reply within 24 hours.</p>

          <div class="hero-actions">
            <RouterLink class="btn" :to="auth.loggedIn ? { name: 'user.bookings' } : { name: 'login' }">Explore packages</RouterLink>
            <RouterLink class="btn btn-ghost" :to="{ name: 'about' }">About us</RouterLink>
          </div>
        </div>
      </div>
    </section>

    <section class="container section">
      <div class="two">
        <div class="stack">
          <div class="card">
            <div class="card-k">Contact details</div>
            <div class="info">
              <div class="info-item">
                <div class="i-k">Email</div>
                <div class="i-v">hello@example.com</div>
              </div>
              <div class="info-item">
                <div class="i-k">Phone</div>
                <div class="i-v">+63 900 000 0000</div>
              </div>
              <div class="info-item">
                <div class="i-k">Office</div>
                <div class="i-v">Surigao City, Philippines</div>
              </div>
              <div class="info-item">
                <div class="i-k">Hours</div>
                <div class="i-v">Mon–Sat • 8:00 AM – 6:00 PM</div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-k">Common inquiries</div>
            <div class="quick">
              <button class="pill" type="button" @click="mailto('Group booking')">Group booking</button>
              <button class="pill" type="button" @click="mailto('Payment concern')">Payment concern</button>
              <button class="pill" type="button" @click="mailto('Custom itinerary')">Custom itinerary</button>
              <button class="pill" type="button" @click="mailto('Cancellation / reschedule')">Cancellation / reschedule</button>
            </div>
            <div class="note">Tip: clicking opens your email with a ready subject.</div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { auth } from '../../stores/auth';

function mailto(subject) {
  const to = 'hello@example.com';
  const s = encodeURIComponent(subject || 'Inquiry');
  const body = encodeURIComponent('Hi Prime Surigao,\n\n');
  window.location.href = `mailto:${to}?subject=${s}&body=${body}`;
}
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@600;700;800&display=swap');

.page{min-height:calc(100vh - 60px);}
.container{max-width:1100px;margin:0 auto;padding:0 18px;}

.hero{position:relative;min-height:360px;display:flex;align-items:stretch;background-image:url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=60');background-size:cover;background-position:center;border-bottom:1px solid rgba(226,232,240,0.7);}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(90deg,rgba(2,8,23,0.78),rgba(2,8,23,0.36));}
.hero-grid{position:relative;z-index:1;display:grid;grid-template-columns:1fr;align-items:center;padding:44px 0;}

.hero-copy{color:#fff;max-width:760px;}
.kicker{font-family:'Great Vibes',cursive;font-weight:400;font-size:46px;line-height:1;opacity:.98;text-shadow:0 12px 28px rgba(0,0,0,0.35);}
.h1{margin:10px 0 8px 0;font-family:'Playfair Display',ui-serif,Georgia,'Times New Roman',serif;font-size:44px;line-height:1.05;letter-spacing:-.02em;text-shadow:0 14px 34px rgba(0,0,0,0.35);}
.sub{margin:0;max-width:680px;opacity:.92;line-height:1.55;font-weight:700;}

.hero-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;}
.btn{background:#0d6efd;color:#fff;text-decoration:none;border-radius:12px;padding:10px 14px;font-weight:900;font-size:14px;display:inline-flex;align-items:center;justify-content:center;border:0;cursor:pointer;}
.btn:disabled{opacity:.6;cursor:not-allowed;}
.btn-ghost{background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);}
.btn-light{background:#eff6ff;color:#0d6efd;border:1px solid #dbeafe;}

.section{padding:28px 0;}
.two{display:grid;grid-template-columns:1fr;gap:14px;align-items:start;}
.stack{display:grid;gap:14px;}

.card{background:#fff;border:1px solid #e6eef9;border-radius:16px;padding:18px;box-shadow:0 12px 40px rgba(9,30,66,0.04);box-sizing:border-box;}
.card-k{color:#0d6efd;font-weight:900;letter-spacing:.08em;text-transform:uppercase;font-size:12px;margin-bottom:10px;}

.info{display:grid;gap:10px;}
.info-item{background:#f8fbff;border:1px solid #e6eef9;border-radius:14px;padding:12px;}
.i-k{font-size:12px;color:#557188;font-weight:800;margin-bottom:4px;}
.i-v{font-size:13px;color:#0f172a;font-weight:950;}

.quick{display:flex;gap:10px;flex-wrap:wrap;}
.pill{border-radius:999px;border:1px solid #dbeafe;background:#eff6ff;color:#0d6efd;font-weight:900;font-size:12px;padding:8px 10px;cursor:pointer;}
.note{margin-top:10px;color:#64748b;font-weight:800;font-size:12px;}

@media(max-width:980px){
  .two{grid-template-columns:1fr;}
  .h1{font-size:36px;}
  .kicker{font-size:40px;}
}
</style>
