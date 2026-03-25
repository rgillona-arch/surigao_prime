<template>
  <div class="card">
    <h2>Contact</h2>

    <p>If you need help with bookings, contact us using the details below.</p>

    <div class="grid">
      <div>
        <div class="k">Email</div>
        <div class="v">hello@example.com</div>
      </div>
      <div>
        <div class="k">Phone</div>
        <div class="v">+63 900 000 0000</div>
      </div>
      <div>
        <div class="k">Office</div>
        <div class="v">Surigao City, Philippines</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card{background:#fff;border:1px solid #e6eef9;border-radius:14px;padding:18px;box-shadow:0 12px 40px rgba(9,30,66,0.04)}
h2{margin-top:0;color:#07305a}
p{color:#41586b;font-size:14px;line-height:1.6}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:14px}
.k{font-size:12px;color:#557188;margin-bottom:4px}
.v{font-size:14px;font-weight:800;color:#0b4b73}
@media(max-width:900px){.grid{grid-template-columns:1fr}}
</style>
