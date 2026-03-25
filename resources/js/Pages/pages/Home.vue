<template>
  <div class="wrap">
    <section class="hero">
      <h1>Prime Surigao Travel &amp; Tour Agency</h1>
      <p>Welcome to the Online Booking &amp; Reservation System.</p>

      <div class="cta">
        <RouterLink class="btn" :to="{ name: 'login' }">Login</RouterLink>
        <RouterLink class="btn alt" :to="{ name: 'about' }">Learn more</RouterLink>
      </div>

      <div class="cards">
        <div class="card">
          <h3>Fast Booking</h3>
          <p>Choose a package, select a date, and book in minutes.</p>
        </div>
        <div class="card">
          <h3>Admin Tools</h3>
          <p>Manage packages and approve bookings.</p>
        </div>
        <div class="card">
          <h3>Secure Sessions</h3>
          <p>Protected dashboards for users and admins.</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>

<style scoped>
.wrap{max-width:1100px;margin:0 auto;}
.hero{background:linear-gradient(180deg,rgba(255,255,255,0.95),#fff);border:1px solid #e6eef9;border-radius:14px;padding:24px;box-shadow:0 12px 40px rgba(9,30,66,0.06)}
.hero h1{margin:0 0 8px 0;color:#07305a;font-size:30px}
.hero p{margin:0 0 14px 0;color:#41586b}
.cta{display:flex;gap:10px;margin:12px 0 18px 0;flex-wrap:wrap}
.btn{background:#0d6efd;color:#fff;text-decoration:none;border-radius:10px;padding:10px 14px;font-weight:800;font-size:14px;display:inline-block}
.alt{background:#f1f5f9;color:#0b4b73;border:1px solid #e6eef9}
.cards{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.card{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:14px}
.card h3{margin:0 0 6px 0;color:#0b4b73}
.card p{margin:0;color:#557188;font-size:14px}
@media(max-width:900px){.cards{grid-template-columns:1fr}}
</style>
