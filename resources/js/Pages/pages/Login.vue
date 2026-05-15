<template>
  <div class="page">
    <section class="hero">
      <div class="hero-overlay" />
      <div class="container hero-grid">
        <div class="hero-copy">
          <div class="kicker">Prime Surigao</div>
          <h1 class="h1">Sign in to continue</h1>
          <p class="sub">Log in as customer or admin — or create a customer account in seconds.</p>

          <div class="hero-actions">
            <a class="btn btn-ghost" href="/">Back to Home</a>
          </div>
        </div>

        <div class="panel" role="region" aria-label="Login">
          <div class="panel-head">
            <div class="panel-title">{{ mode === 'register' ? 'Create a customer account' : (role === 'admin' ? 'Admin login' : 'Customer login') }}</div>
            <div class="panel-sub">
              {{ mode === 'register' ? 'Register to book packages and track trips.' : (role === 'admin' ? 'Manage bookings, packages, and reports.' : 'Book packages and manage your reservations.') }}
            </div>
          </div>

          <div v-if="error" class="error">{{ error }}</div>

          <form v-if="mode !== 'register'" class="form" @submit.prevent="onLogin">
            <div class="field">
              <div class="label">Login as</div>
              <select v-model="role" name="intended_role" aria-label="Login type">
                <option value="customer">Customer</option>
                <option value="admin">Admin</option>
              </select>
            </div>

            <div class="field">
              <div class="label">Email</div>
              <input v-model.trim="loginForm.email" name="email" type="email" placeholder="you@example.com" required autocomplete="username" />
            </div>

            <div class="field">
              <div class="label">Password</div>
              <div class="pw">
                <input v-model="loginForm.password" name="password" :type="showLoginPassword ? 'text' : 'password'" placeholder="Enter password" required autocomplete="current-password" />
                <button class="pw-btn" type="button" @click="showLoginPassword = !showLoginPassword">
                  {{ showLoginPassword ? 'Hide' : 'Show' }}
                </button>
              </div>
            </div>

            <button class="btn" type="submit" :disabled="loading">
              {{ loading ? 'Logging in…' : (role === 'admin' ? 'Login as Admin' : 'Login as Customer') }}
            </button>

            <div class="hint">Secure login • We never share your information.</div>

            <div v-if="role === 'customer'" class="cred">
              Don’t have an account?
              <a href="#" @click.prevent="mode = 'register'">Create one</a>
            </div>
          </form>

          <form v-else class="form" @submit.prevent="onRegister">
            <div class="field">
              <div class="label">Full name</div>
              <input v-model.trim="registerForm.name" name="name" type="text" placeholder="Your name" required autocomplete="name" />
            </div>

            <div class="field">
              <div class="label">Email</div>
              <input v-model.trim="registerForm.email" name="email" type="email" placeholder="you@example.com" required autocomplete="username" />
            </div>

            <div class="field">
              <div class="label">Password</div>
              <div class="pw">
                <input v-model="registerForm.password" name="password" :type="showRegisterPassword ? 'text' : 'password'" placeholder="Min 8 characters" required autocomplete="new-password" />
                <button class="pw-btn" type="button" @click="showRegisterPassword = !showRegisterPassword">
                  {{ showRegisterPassword ? 'Hide' : 'Show' }}
                </button>
              </div>
            </div>

            <button class="btn" type="submit" :disabled="loading">
              {{ loading ? 'Creating account…' : 'Create account' }}
            </button>

            <div class="hint">Customer accounts can book packages and track trips.</div>

            <div class="cred">
              Already have an account?
              <a href="#" @click.prevent="mode = 'login'">Login</a>
            </div>
          </form>
        </div>
      </div>
    </section>

    <section class="container section" aria-hidden="true">
      <div class="grid3">
        <div class="card">
          <div class="c-t">Fast booking</div>
          <div class="c-s">Reserve tours in minutes with clear steps.</div>
        </div>
        <div class="card">
          <div class="c-t">Secure payments</div>
          <div class="c-s">Trusted options and transparent confirmation.</div>
        </div>
        <div class="card">
          <div class="c-t">Local guides</div>
          <div class="c-s">Experience Surigao with reliable local support.</div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { auth } from '../../stores/auth';

const router = useRouter();

const mode = ref('login');
const role = ref('customer');
const showLoginPassword = ref(false);
const showRegisterPassword = ref(false);

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

watch(role, () => {
  error.value = '';
  showLoginPassword.value = false;
});

async function onLogin() {
  loading.value = true;
  error.value = '';

  try {
    loginForm.intended_role = role.value === 'admin' ? 'admin' : 'customer';
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
@import url('https://fonts.googleapis.com/css2?family=Great+Vibes&family=Playfair+Display:wght@600;700;800&display=swap');

*{box-sizing:border-box}
.page{min-height:calc(100vh - 60px);}
.container{max-width:1100px;margin:0 auto;padding:0 18px;}

.hero{position:relative;min-height:520px;display:flex;align-items:stretch;background-image:url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=60');background-size:cover;background-position:center;border-bottom:1px solid rgba(226,232,240,0.7);}
.hero-overlay{position:absolute;inset:0;background:linear-gradient(90deg,rgba(2,8,23,0.82),rgba(2,8,23,0.44));}
.hero-grid{position:relative;z-index:1;display:grid;grid-template-columns:1fr 440px;gap:18px;align-items:center;padding:52px 0;}

.hero-copy{color:#fff;}
.kicker{font-family:'Great Vibes',cursive;font-weight:400;font-size:52px;line-height:1;opacity:.98;text-shadow:0 12px 28px rgba(0,0,0,0.35);}
.h1{margin:12px 0 10px 0;font-family:'Playfair Display',ui-serif,Georgia,'Times New Roman',serif;font-size:42px;line-height:1.05;letter-spacing:-.02em;text-shadow:0 14px 34px rgba(0,0,0,0.35);}
.sub{margin:0;max-width:620px;opacity:.92;line-height:1.55;font-weight:700;}

.hero-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px;}

.panel{background:linear-gradient(180deg,rgba(255,255,255,0.96),#fff);border:1px solid rgba(226,232,240,0.9);border-radius:18px;padding:16px;box-shadow:0 18px 55px rgba(2,8,23,0.25);color:#0f172a;}
.tabs{display:grid;grid-template-columns:1fr 1fr 1fr;background:#f1f5f9;border:1px solid #e6eef9;border-radius:14px;padding:4px;gap:4px;}
.tab{border:0;background:transparent;border-radius:11px;padding:10px 10px;font-weight:950;color:#334155;cursor:pointer;font-size:13px;}
.tab.active{background:#fff;box-shadow:0 10px 28px rgba(9,30,66,0.08);color:#0d6efd;}

.panel-head{margin-top:12px;margin-bottom:10px;}
.panel-title{font-weight:1000;color:#0f172a;font-size:16px;}
.panel-sub{margin-top:3px;color:#475569;font-weight:800;font-size:12px;line-height:1.4;}

.form{display:grid;gap:12px;margin-top:10px;}
.field{display:grid;gap:6px;}
.label{font-weight:900;font-size:12px;color:#334155;}

input{width:100%;border-radius:12px;border:1px solid #e6eef9;padding:11px 12px;font-weight:800;outline:none;background:#fff;color:#0f172a;box-sizing:border-box;}
input:focus{border-color:#93c5fd;box-shadow:0 0 0 4px rgba(59,130,246,0.18);}

select{width:100%;border-radius:12px;border:1px solid #e6eef9;padding:11px 12px;font-weight:800;outline:none;background:#fff;color:#0f172a;box-sizing:border-box;}
select:focus{border-color:#93c5fd;box-shadow:0 0 0 4px rgba(59,130,246,0.18);}

.pw{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:center;}
.pw-btn{border-radius:12px;border:1px solid #dbeafe;background:#eff6ff;color:#0d6efd;font-weight:950;font-size:12px;padding:10px 12px;cursor:pointer;white-space:nowrap;}

.btn{background:#0d6efd;color:#fff;text-decoration:none;border-radius:12px;padding:11px 14px;font-weight:950;font-size:14px;display:inline-flex;align-items:center;justify-content:center;border:0;cursor:pointer;width:100%;}
.btn:disabled{opacity:.6;cursor:not-allowed;}
.btn-ghost{background:rgba(255,255,255,0.14);border:1px solid rgba(255,255,255,0.22);width:auto;}

.hint{color:#64748b;font-weight:850;font-size:12px;}
.cred{background:#fffbe6;color:#7a4d00;padding:10px 12px;border-radius:12px;font-size:13px;border:1px solid #fff2cc;font-weight:800;}
.cred a{color:#0d6efd;text-decoration:none;font-weight:950;margin-left:6px;}
.error{background:#fee2e2;color:#7f1d1d;border:1px solid #fecaca;padding:10px 12px;border-radius:12px;font-size:13px;font-weight:800;}

.section{padding:28px 0;}
.grid3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;}
.card{background:rgba(255,255,255,0.92);border:1px solid rgba(226,232,240,0.9);border-radius:16px;padding:16px;box-shadow:0 12px 40px rgba(9,30,66,0.04);}
.c-t{font-weight:1000;color:#0f172a;font-size:14px;}
.c-s{margin-top:6px;color:#475569;font-weight:800;font-size:12px;line-height:1.45;}

@media(max-width:980px){
  .hero-grid{grid-template-columns:1fr;}
  .grid3{grid-template-columns:1fr;}
  .kicker{font-size:46px;}
  .h1{font-size:34px;}
}
</style>
