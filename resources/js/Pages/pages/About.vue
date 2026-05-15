<template>
  <div class="page">
    <section class="hero">
      <div class="hero-overlay" />
      <div class="container hero-grid">
        <div class="hero-copy">
          <div class="kicker">Prime Surigao</div>
          <h1 class="h1">About us</h1>
          <p class="sub">We help travelers discover Surigao with curated tours, local guides, and a smooth booking experience.</p>

          <div class="hero-actions">
            <RouterLink class="btn" :to="{ name: 'contact' }">Contact us</RouterLink>
            <RouterLink class="btn btn-ghost" :to="auth.loggedIn ? { name: 'user.bookings' } : { name: 'login' }">Explore packages</RouterLink>
          </div>
        </div>
      </div>
    </section>

    <section class="container section">
      <div class="two">
        <div class="card">
          <div class="card-k">Our story</div>
          <h2 class="h2">Travel made simple — with real local care.</h2>
          <p class="p">
            Prime Surigao is built for travelers who want a beautiful, safe, and well-organized island experience.
            We combine trusted local partners with a simple online booking flow — so you can focus on the adventure.
          </p>
          <div class="mini">
            <div class="mini-item">
              <div class="m-k">Service area</div>
              <div class="m-v">Surigao City &amp; nearby islands</div>
            </div>
            <div class="mini-item">
              <div class="m-k">Booking</div>
              <div class="m-v">Fast, guided, and transparent</div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-k">Why choose us</div>
          <div class="grid3">
            <div class="feature">
              <div class="f-t">Local guides</div>
              <div class="f-s">Get insider tips and real island stories.</div>
            </div>
            <div class="feature">
              <div class="f-t">Secure payments</div>
              <div class="f-s">Clear options and safe confirmation flow.</div>
            </div>
            <div class="feature">
              <div class="f-t">Curated packages</div>
              <div class="f-s">Handpicked trips for every budget.</div>
            </div>
            <div class="feature">
              <div class="f-t">Customer support</div>
              <div class="f-s">We’re here before and after booking.</div>
            </div>
            <div class="feature">
              <div class="f-t">Transparent pricing</div>
              <div class="f-s">No confusing add-ons or hidden fees.</div>
            </div>
            <div class="feature">
              <div class="f-t">Easy booking</div>
              <div class="f-s">Book your trip in a few clicks.</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="container section">
      <div class="strip">
        <div class="stat">
          <div class="s-v">100+</div>
          <div class="s-k">Happy travelers</div>
        </div>
        <div class="stat">
          <div class="s-v">20+</div>
          <div class="s-k">Curated packages</div>
        </div>
        <div class="stat">
          <div class="s-v">4.8</div>
          <div class="s-k">Average rating</div>
        </div>
      </div>
    </section>

    <section class="container section">
      <div class="section-head">
        <div>
          <div class="section-kicker">Social proof</div>
          <h2 class="h2">What travelers say</h2>
        </div>
      </div>
      <div class="grid-cards">
        <div class="t-card">
          <div class="t-quote">“Smooth booking and very friendly guide. The trip was exactly as promised.”</div>
          <div class="t-meta">Customer</div>
        </div>
        <div class="t-card">
          <div class="t-quote">“The package was well-organized — we felt safe and taken care of.”</div>
          <div class="t-meta">Customer</div>
        </div>
        <div class="t-card">
          <div class="t-quote">“Fast updates and clear instructions. Highly recommended for first-time visitors.”</div>
          <div class="t-meta">Customer</div>
        </div>
      </div>
    </section>

    <section class="container section">
      <div class="cta">
        <div>
          <div class="cta-k">Ready to plan your trip?</div>
          <div class="cta-t">Explore packages and book your Surigao adventure.</div>
        </div>
        <div class="cta-actions">
          <RouterLink class="btn" :to="auth.loggedIn ? { name: 'user.bookings' } : { name: 'login' }">Explore packages</RouterLink>
          <RouterLink class="btn btn-light" :to="{ name: 'contact' }">Ask a question</RouterLink>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { auth } from '../../stores/auth';
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
.sub{margin:0;max-width:640px;opacity:.92;line-height:1.55;font-weight:700;}

.hero-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:16px;}
.btn{background:#0d6efd;color:#fff;text-decoration:none;border-radius:12px;padding:10px 14px;font-weight:900;font-size:14px;display:inline-flex;align-items:center;justify-content:center;border:0;cursor:pointer;}
.btn-ghost{background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);}
.btn-light{background:#eff6ff;color:#0d6efd;border:1px solid #dbeafe;}

.section{padding:28px 0;}
.two{display:grid;grid-template-columns:1.1fr .9fr;gap:14px;}
.card{background:#fff;border:1px solid #e6eef9;border-radius:16px;padding:18px;box-shadow:0 12px 40px rgba(9,30,66,0.04);box-sizing:border-box;}
.card-k{color:#0d6efd;font-weight:900;letter-spacing:.08em;text-transform:uppercase;font-size:12px;}
.h2{margin:8px 0 8px 0;font-size:22px;line-height:1.2;color:#0f172a;}
.p{margin:0;color:#475569;font-weight:700;line-height:1.65;font-size:13px;}

.mini{margin-top:14px;display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.mini-item{background:#f8fbff;border:1px solid #e6eef9;border-radius:14px;padding:12px;}
.m-k{font-size:12px;color:#557188;font-weight:800;margin-bottom:4px;}
.m-v{font-size:13px;color:#0f172a;font-weight:950;}

.grid3{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:12px;}
.feature{background:#f8fbff;border:1px solid #e6eef9;border-radius:14px;padding:12px;}
.f-t{font-weight:950;color:#0f172a;font-size:13px;}
.f-s{margin-top:4px;color:#475569;font-weight:750;font-size:12px;line-height:1.45;}

.strip{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;}
.stat{background:linear-gradient(180deg,#ffffff,#f8fbff);border:1px solid #e6eef9;border-radius:16px;padding:16px;text-align:center;}
.s-v{font-size:26px;font-weight:1000;color:#0f172a;}
.s-k{margin-top:4px;color:#475569;font-weight:850;font-size:12px;}

.section-head{display:flex;justify-content:space-between;align-items:flex-end;gap:14px;margin-bottom:14px;flex-wrap:wrap;}
.section-kicker{color:#0d6efd;font-weight:900;letter-spacing:.08em;text-transform:uppercase;font-size:12px;}
.grid-cards{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;}
.t-card{background:#fff;border:1px solid #e6eef9;border-radius:16px;padding:16px;box-shadow:0 12px 40px rgba(9,30,66,0.04);}
.t-quote{color:#0f172a;font-weight:800;line-height:1.6;font-size:13px;}
.t-meta{margin-top:12px;color:#64748b;font-weight:900;font-size:12px;}

.cta{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;background:linear-gradient(90deg,#0b4b73,#0d6efd);border-radius:18px;padding:18px;color:#fff;box-shadow:0 18px 55px rgba(2,8,23,0.18);}
.cta-k{font-weight:900;letter-spacing:.08em;text-transform:uppercase;font-size:12px;opacity:.95;}
.cta-t{margin-top:6px;font-weight:1000;font-size:18px;}
.cta-actions{display:flex;gap:10px;flex-wrap:wrap;}

@media(max-width:980px){
  .two{grid-template-columns:1fr;}
  .grid-cards{grid-template-columns:1fr;}
  .strip{grid-template-columns:1fr;}
  .h1{font-size:36px;}
  .kicker{font-size:40px;}
}
</style>
