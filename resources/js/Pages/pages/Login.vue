<template>
  <div class="wrap">
    <div class="hero">
      <div class="brand">
        <div class="logo">PS</div>
        <div>
          <div class="brand-title">Prime Surigao Travel &amp; Tour Agency</div>
          <div class="small">Online Booking &amp; Reservation System</div>
        </div>
      </div>

      <h1>Discover Surigao — Book trips, explore islands</h1>
      <p>Login as user or admin to continue.</p>

      <div class="hero-cards" aria-hidden="true">
        <div class="card">
          <h4>Real-time Availability</h4>
          <p>See slots per package &amp; book dates without overbooking.</p>
        </div>
        <div class="card">
          <h4>Admin Controls</h4>
          <p>Approve or cancel bookings, manage packages.</p>
        </div>
      </div>
    </div>

    <div class="login" role="region" aria-label="Login">
      <h2>Sign in to continue</h2>
      <p>Enter your account credentials to continue.</p>

      <div class="back">
        <a href="/">Back to Home</a>
      </div>

      <div v-if="error" class="error">{{ error }}</div>

      <form v-if="mode === 'login'" @submit.prevent="onLogin">
        <div class="field">
          <select v-model="loginForm.intended_role" name="intended_role" aria-label="Login type">
            <option value="customer">Login as Customer</option>
            <option value="admin">Login as Admin</option>
          </select>
        </div>

        <div class="field">
          <input v-model="loginForm.email" name="email" type="email" placeholder="Email" required autocomplete="username" />
        </div>

        <div class="field">
          <input v-model="loginForm.password" name="password" type="password" placeholder="Password" required autocomplete="current-password" />
        </div>

        <button class="btn" type="submit" :disabled="loading">
          {{ loading ? 'Logging in...' : 'Login' }}
        </button>

        <div v-if="loginForm.intended_role === 'customer'" class="cred">
          Don’t have an account?
          <a href="#" @click.prevent="mode = 'register'">Create one</a>
        </div>
      </form>

      <form v-else @submit.prevent="onRegister">
        <div class="field">
          <input v-model="registerForm.name" name="name" type="text" placeholder="Full name" required autocomplete="name" />
        </div>

        <div class="field">
          <input v-model="registerForm.email" name="email" type="email" placeholder="Email" required autocomplete="username" />
        </div>

        <div class="field">
          <input v-model="registerForm.password" name="password" type="password" placeholder="Password (min 8 chars)" required autocomplete="new-password" />
        </div>

        <button class="btn" type="submit" :disabled="loading">
          {{ loading ? 'Creating account...' : 'Register' }}
        </button>

        <div class="cred">
          Already have an account?
          <a href="#" @click.prevent="mode = 'login'">Login</a>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { auth } from '../../stores/auth';

const router = useRouter();

const mode = ref('login');

const loginForm = reactive({
  intended_role: 'customer',
  email: '',
  password: '',
});

const registerForm = reactive({
  name: '',
  email: '',
  password: '',
});

const loading = ref(false);
const error = ref('');

async function onLogin() {
  loading.value = true;
  error.value = '';

  try {
    await auth.login(loginForm);
    await router.push(auth.role === 'admin' ? { name: 'admin.dashboard' } : { name: 'user.dashboard' });
  } catch (e) {
    error.value = e?.response?.data?.message || 'Login failed.';
  } finally {
    loading.value = false;
  }
}

async function onRegister() {
  loading.value = true;
  error.value = '';

  try {
    await auth.register(registerForm);
    await router.push(auth.role === 'admin' ? { name: 'admin.dashboard' } : { name: 'user.dashboard' });
  } catch (e) {
    const data = e?.response?.data;
    error.value = data?.message || 'Registration failed.';
  } finally {
    loading.value = false;
  }
}
</script>

<style scoped>
:root{font-family:Inter,"Segoe UI",Roboto,Arial,sans-serif;color:#12263a;}
*{box-sizing:border-box}
.wrap{width:940px;max-width:96%;display:grid;grid-template-columns:1fr 440px;gap:24px;align-items:center;margin:24px auto;}
.hero{padding:28px;border-radius:14px;background:linear-gradient(180deg,rgba(255,255,255,0.85),rgba(255,255,255,0.9));box-shadow:0 12px 40px rgba(9,30,66,0.08);}
.brand{display:flex;align-items:center;gap:12px;}
.logo{width:56px;height:56px;border-radius:12px;background:linear-gradient(180deg,#fff,#f1fbff);display:flex;align-items:center;justify-content:center;font-weight:900;color:#0d6efd;font-size:18px;box-shadow:0 6px 18px rgba(6,182,212,0.08);}
.brand-title{font-size:14px;color:#0b4b73;font-weight:800}
.hero h1{margin:14px 0 6px 0;font-size:28px;color:#07305a;}
.hero p{margin:0;color:#41586b;font-size:15px;line-height:1.45;}
.hero-cards{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-top:18px;}
.card{background:#fff;border-radius:10px;padding:10px;box-shadow:0 6px 18px rgba(9,30,66,0.04);}
.card h4{margin:0 0 6px 0;font-size:14px;color:#0b4b73}
.card p{margin:0;font-size:13px;color:#557188}
.login{padding:22px;border-radius:14px;background:linear-gradient(180deg,rgba(255,255,255,0.97),#fff);box-shadow:0 12px 40px rgba(9,30,66,0.06);}
.login h2{margin:0 0 8px 0;font-size:20px;color:#0d6efd}
.login p{margin:0 0 16px 0;color:#41586b;font-size:13px}
.field{margin-bottom:10px}
input,select{width:100%;padding:10px 12px;border-radius:10px;border:1px solid #e6eef9;font-size:14px;outline:none;}
.btn{width:100%;padding:10px 12px;border-radius:10px;border:0;cursor:pointer;font-weight:700;background:#0d6efd;color:white;font-size:14px;}
.cred{background:#fffbe6;color:#7a4d00;padding:8px 10px;border-radius:8px;font-size:13px;margin-top:14px;border:1px solid #fff2cc}
.cred a{color:#0d6efd;text-decoration:none;font-weight:800;margin-left:6px;}
.small{font-size:13px;color:#6b8190;margin-top:8px}
.error{background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca;padding:8px 10px;border-radius:8px;font-size:13px;margin-bottom:10px;}
.back{margin:0 0 10px 0;font-size:13px;}
.back a{color:#0d6efd;text-decoration:none;font-weight:700;}
@media(max-width:880px){.wrap{grid-template-columns:1fr;}}
</style>
