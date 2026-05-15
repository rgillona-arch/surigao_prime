<template>
  <div class="page">
    <div class="top">
      <section v-if="activeTab === 'home'" class="hero">
        <div class="hero-content">
          <div class="hero-kicker">Prime Surigao Travel</div>
          <h1 class="hero-title">Discover Surigao's Beauty</h1>
          <div class="hero-sub">Book your dream tour — availability and payments synced with Admin in real-time.</div>
          <RouterLink class="hero-cta" :to="{ name: 'user.bookings' }">Book a Package</RouterLink>
        </div>
      </section>

      <section v-else class="hero hero-mini">
        <div class="hero-content">
          <div class="hero-kicker">Prime Surigao Travel</div>
          <h1 class="hero-title">{{ heroTitle }}</h1>
        </div>
      </section>

      <section class="stats">
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
      </section>
    </div>

    <div v-if="loading" class="loading">Loading...</div>

    <div v-else class="content">
      <section v-if="activeTab === 'home'" class="card">
        <div class="card-head">
          <div class="section-title">Featured Destinations</div>
          <RouterLink class="link" :to="{ name: 'user.bookings' }">View all</RouterLink>
        </div>

        <div v-if="featuredPackages.length" class="destinations">
          <article v-for="p in featuredPackages" :key="p.id" class="destination-card">
            <div class="img-wrap">
              <img v-if="packageImageUrl(p)" :src="packageImageUrl(p)" :alt="p.title" loading="lazy" />
              <div v-else class="no-photo">No photo</div>
            </div>
            <div class="info">
              <div>
                <h3>{{ p.title }}</h3>
                <p>₱{{ formatMoney(p.price) }} per person</p>
                <p class="muted">Slots left: {{ p.slots_per_day }}</p>
              </div>
              <div class="card-actions">
                <RouterLink class="btn primary" :to="{ name: 'user.bookings' }">Book Now</RouterLink>
                <div class="muted mini">ID: {{ p.id }}</div>
              </div>
            </div>
          </article>
        </div>

        <div v-else class="muted empty">No packages available right now.</div>
      </section>

      <section v-if="activeTab === 'home'" class="card">
        <div class="card-head">
          <div class="section-title">My Recent Bookings</div>
          <RouterLink class="link" :to="{ name: 'user.bookings' }">Open Bookings</RouterLink>
        </div>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Package</th>
                <th>Date</th>
                <th>Pax</th>
                <th>Status</th>
                <th>Payment</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="recentBookings.length === 0">
                <td colspan="6" class="muted">No bookings yet.</td>
              </tr>
              <tr v-for="b in recentBookings" :key="b.id">
                <td>#{{ b.id }}</td>
                <td>{{ b.package?.title || '-' }}</td>
                <td>{{ formatDate(b.date) }}</td>
                <td>{{ b.pax }}</td>
                <td>
                  <span class="pill" :class="statusClass(b.status)">{{ b.status }}</span>
                </td>
                <td>
                  <span class="pill" :class="paymentClass(b.payment_status)">{{ b.payment_status || '-' }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section v-else-if="activeTab === 'packages'" class="card">
        <div class="card-head">
          <div class="section-title">Your Packages</div>
        </div>

        <div class="tools">
          <div class="tool">
            <div class="tk">Search</div>
            <input v-model.trim="pkgSearch" type="text" placeholder="Search packages…" />
          </div>
          <div class="tool">
            <div class="tk">Sort</div>
            <select v-model="pkgSort">
              <option value="popular">Recommended</option>
              <option value="title">Title (A–Z)</option>
              <option value="price_asc">Price (low to high)</option>
              <option value="price_desc">Price (high to low)</option>
            </select>
          </div>
        </div>

        <div v-if="filteredPackages.length === 0" class="muted empty">No packages match your search.</div>

        <div v-else class="pkg-grid">
          <article v-for="p in filteredPackages" :key="p.id" class="pkg-card">
            <div class="pkg-img" :class="packageImageUrl(p) ? '' : 'empty'">
              <img v-if="packageImageUrl(p)" :src="packageImageUrl(p)" :alt="p.title" loading="lazy" />
              <div v-else class="no-photo">No photo</div>
            </div>

            <div class="pkg-body">
              <div class="pkg-top">
                <div class="pkg-title">{{ p.title }}</div>
                <div class="pkg-price">₱{{ formatMoney(p.price) }}<span class="per">/pax</span></div>
              </div>

              <div class="pkg-meta">
                <div class="pm">
                  <div class="pk">Slots/day</div>
                  <div class="pv">{{ p.slots_per_day }}</div>
                </div>
                <div class="pm">
                  <div class="pk">ID</div>
                  <div class="pv">#{{ p.id }}</div>
                </div>
              </div>

              <div class="pkg-actions">
                <RouterLink class="btn primary" :to="{ name: 'user.bookings', query: { package_id: String(p.id) } }">Book now</RouterLink>
                <RouterLink class="btn ghost" :to="{ name: 'user.bookings', query: { package_id: String(p.id) } }">View details</RouterLink>
              </div>
            </div>
          </article>
        </div>
      </section>

      <section v-else-if="activeTab === 'bookings'" class="card">
        <div class="card-head">
          <div class="section-title">My Bookings</div>
          <RouterLink class="link" :to="{ name: 'user.bookings' }">Manage</RouterLink>
        </div>

        <div class="table-wrap">
          <table class="table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Package</th>
                <th>Date</th>
                <th>Pax</th>
                <th>Status</th>
                <th>Payment</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="bookings.length === 0">
                <td colspan="6" class="muted">No bookings yet.</td>
              </tr>
              <tr v-for="b in bookings" :key="b.id">
                <td>#{{ b.id }}</td>
                <td>{{ b.package?.title || '-' }}</td>
                <td>{{ formatDate(b.date) }}</td>
                <td>{{ b.pax }}</td>
                <td>
                  <span class="pill" :class="statusClass(b.status)">{{ b.status }}</span>
                </td>
                <td>
                  <span class="pill" :class="paymentClass(b.payment_status)">{{ b.payment_status || '-' }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section v-else-if="activeTab === 'notifications'" class="card">
        <div class="card-head">
          <div class="section-title">Notifications</div>
          <button class="btn primary" type="button" @click="loadNotifications" :disabled="loadingNotifications">{{ loadingNotifications ? 'Loading...' : 'Refresh' }}</button>
        </div>

        <div class="muted" style="margin-bottom:10px;">Unread: {{ unreadCount }}</div>

        <div v-if="loadingNotifications" class="muted">Loading...</div>
        <div v-else-if="notifications.length === 0" class="muted">No notifications.</div>
        <div v-else class="notif-list">
          <button v-for="n in notifications" :key="n.id" class="notif" :class="n.read_at ? '' : 'unread'" type="button" @click="markRead(n)">
            <div class="nt">{{ n.title }}</div>
            <div class="nm">{{ n.message }}</div>
            <div class="nd">{{ formatTime(n.created_at) }}</div>
          </button>
        </div>
      </section>

      <section v-else-if="activeTab === 'profile'" class="card">
        <div class="card-head">
          <div class="section-title">Your Profile</div>
          <button class="btn ghost" type="button" @click="openEditProfile">Edit Profile</button>
        </div>

        <div class="profile-layout">
          <div class="profile-card">
            <div class="profile-top">
              <div class="avatar">{{ initials }}</div>
              <div class="p-main">
                <div class="p-name">{{ auth.name || 'Customer' }}</div>
                <div class="p-email">{{ auth.email || '-' }}</div>
                <div class="p-role">
                  <span class="badge">{{ (auth.role || 'customer') }}</span>
                </div>
              </div>
            </div>

            <div class="quick-actions">
              <RouterLink class="qa" :to="{ name: 'user.bookings' }">My Bookings</RouterLink>
              <RouterLink class="qa" :to="{ name: 'notifications.index' }">Notifications</RouterLink>
              <button class="qa qa-primary" type="button" @click="openEditProfile">Edit Profile</button>
            </div>
          </div>

          <div class="details-card">
            <div class="details-title">Account Details</div>
            <div class="details">
              <div class="d-row">
                <div class="dk">Full name</div>
                <div class="dv">{{ auth.name || '-' }}</div>
              </div>
              <div class="d-row">
                <div class="dk">Email</div>
                <div class="dv">{{ auth.email || '-' }}</div>
              </div>
              <div class="d-row">
                <div class="dk">Role</div>
                <div class="dv">{{ auth.role || '-' }}</div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>

    <div v-if="editProfileOpen" class="modal" role="dialog" aria-modal="true">
      <div class="modal-backdrop" @click="closeEditProfile" />
      <div class="modal-card">
        <div class="modal-head">
          <div class="modal-title">Edit Profile</div>
          <button class="icon-btn" type="button" @click="closeEditProfile" aria-label="Close">✕</button>
        </div>

        <div class="modal-body">
          <div v-if="editProfileGeneralError" class="alert">{{ editProfileGeneralError }}</div>

          <div class="form">
            <div class="field">
              <div class="label">Full name</div>
              <input v-model.trim="editProfileForm.name" type="text" placeholder="Your name" />
              <div v-if="editProfileErrors.name" class="err">{{ editProfileErrors.name }}</div>
            </div>

            <div class="field">
              <div class="label">Email</div>
              <input v-model.trim="editProfileForm.email" type="email" placeholder="you@example.com" />
              <div v-if="editProfileErrors.email" class="err">{{ editProfileErrors.email }}</div>
            </div>

            <div class="divider" />

            <div class="modal-title" style="font-size:14px;">Change Password (optional)</div>

            <div class="field">
              <div class="label">Current password</div>
              <input v-model="passwordForm.current_password" type="password" placeholder="Current password" />
              <div v-if="passwordErrors.current_password" class="err">{{ passwordErrors.current_password }}</div>
            </div>

            <div class="field">
              <div class="label">New password</div>
              <input v-model="passwordForm.password" type="password" placeholder="New password (min 8 chars)" />
              <div v-if="passwordErrors.password" class="err">{{ passwordErrors.password }}</div>
            </div>

            <div class="field">
              <div class="label">Confirm new password</div>
              <input v-model="passwordForm.password_confirmation" type="password" placeholder="Confirm new password" />
            </div>
          </div>
        </div>

        <div class="modal-actions">
          <button class="btn" type="button" @click="closeEditProfile" :disabled="savingProfile || savingPassword">Cancel</button>
          <button class="btn primary" type="button" @click="saveProfile" :disabled="savingProfile || savingPassword">{{ savingProfile ? 'Saving...' : 'Save Profile' }}</button>
          <button class="btn ghost" type="button" @click="savePassword" :disabled="savingProfile || savingPassword">{{ savingPassword ? 'Updating...' : 'Update Password' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { RouterLink } from 'vue-router';
import { useRoute } from 'vue-router';
import { auth } from '../../stores/auth';

const loading = ref(true);
const bookings = ref([]);
const packages = ref([]);

const activeTab = ref('home');

const route = useRoute();

const notifications = ref([]);
const unreadCount = ref(0);
const loadingNotifications = ref(false);

const pkgSearch = ref('');
const pkgSort = ref('popular');

const editProfileOpen = ref(false);
const savingProfile = ref(false);
const savingPassword = ref(false);
const editProfileGeneralError = ref('');

const editProfileForm = ref({
  name: '',
  email: '',
});

const editProfileErrors = ref({});

const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const passwordErrors = ref({});


const stats = computed(() => {
  const total = bookings.value.length;
  const pending = bookings.value.filter((b) => b?.status === 'Pending').length;
  const approved = bookings.value.filter((b) => b?.status === 'Approved').length;
  const paid = bookings.value.filter((b) => b?.payment_status === 'Paid').length;
  return { total, pending, approved, paid };
});

const featuredPackages = computed(() => {
  return packages.value.slice(0, 6);
});

const heroTitle = computed(() => {
  const t = String(activeTab.value || 'home');
  if (t === 'packages') return 'Your Packages';
  if (t === 'profile') return 'Your Profile';
  if (t === 'bookings') return 'Your Bookings';
  if (t === 'notifications') return 'Notifications';
  return 'Your Dashboard';
});

const recentBookings = computed(() => {
  return bookings.value.slice(0, 8);
});

const initials = computed(() => {
  const n = String(auth?.name || '').trim();
  if (!n) return 'U';
  const parts = n.split(/\s+/).filter(Boolean).slice(0, 2);
  const s = parts.map((p) => p[0]).join('');
  return (s || 'U').toUpperCase();
});

const filteredPackages = computed(() => {
  const q = String(pkgSearch.value || '').trim().toLowerCase();
  const list = Array.isArray(packages.value) ? packages.value.slice() : [];

  const filtered = q
    ? list.filter((p) => {
        const t = String(p?.title || '').toLowerCase();
        const d = String(p?.description || '').toLowerCase();
        return t.includes(q) || d.includes(q);
      })
    : list;

  const sort = String(pkgSort.value || 'popular');
  if (sort === 'title') {
    filtered.sort((a, b) => String(a?.title || '').localeCompare(String(b?.title || '')));
  } else if (sort === 'price_asc') {
    filtered.sort((a, b) => Number(a?.price || 0) - Number(b?.price || 0));
  } else if (sort === 'price_desc') {
    filtered.sort((a, b) => Number(b?.price || 0) - Number(a?.price || 0));
  }
  return filtered;
});

function openEditProfile() {
  editProfileGeneralError.value = '';
  editProfileErrors.value = {};
  passwordErrors.value = {};
  editProfileForm.value = {
    name: String(auth?.name || ''),
    email: String(auth?.email || ''),
  };
  passwordForm.value = {
    current_password: '',
    password: '',
    password_confirmation: '',
  };
  editProfileOpen.value = true;
}

function closeEditProfile() {
  editProfileOpen.value = false;
}

async function saveProfile() {
  savingProfile.value = true;
  editProfileGeneralError.value = '';
  editProfileErrors.value = {};
  try {
    const res = await window.axios.put('/api/user/profile', {
      name: editProfileForm.value.name,
      email: editProfileForm.value.email,
    });
    auth.loggedIn = true;
    auth.role = res.data.role;
    auth.name = res.data.name;
    auth.email = res.data.email;
    closeEditProfile();
  } catch (e) {
    const status = e?.response?.status;
    const errors = e?.response?.data?.errors;
    if (status === 422 && errors) {
      editProfileErrors.value = {
        name: Array.isArray(errors.name) ? errors.name[0] : errors.name,
        email: Array.isArray(errors.email) ? errors.email[0] : errors.email,
      };
    } else {
      editProfileGeneralError.value = e?.response?.data?.message || 'Failed to update profile.';
    }
  } finally {
    savingProfile.value = false;
  }
}

async function savePassword() {
  savingPassword.value = true;
  editProfileGeneralError.value = '';
  passwordErrors.value = {};
  try {
    await window.axios.put('/api/user/profile/password', {
      current_password: passwordForm.value.current_password,
      password: passwordForm.value.password,
      password_confirmation: passwordForm.value.password_confirmation,
    });
    passwordForm.value = {
      current_password: '',
      password: '',
      password_confirmation: '',
    };
  } catch (e) {
    const status = e?.response?.status;
    const errors = e?.response?.data?.errors;
    if (status === 422 && errors) {
      passwordErrors.value = {
        current_password: Array.isArray(errors.current_password) ? errors.current_password[0] : errors.current_password,
        password: Array.isArray(errors.password) ? errors.password[0] : errors.password,
      };
    } else {
      editProfileGeneralError.value = e?.response?.data?.message || 'Failed to update password.';
    }
  } finally {
    savingPassword.value = false;
  }
}

function formatMoney(value) {
  const n = Number(value || 0);
  try {
    return new Intl.NumberFormat('en-PH').format(n);
  } catch {
    return String(n);
  }
}

function formatDate(value) {
  if (!value) return '-';
  const d = new Date(value);
  if (Number.isNaN(d.getTime())) return String(value);
  return d.toLocaleDateString();
}

function statusClass(status) {
  if (status === 'Approved') return 'ok';
  if (status === 'Cancelled') return 'bad';
  return 'warn';
}

function paymentClass(status) {
  if (status === 'Paid') return 'ok';
  if (status === 'Rejected') return 'bad';
  if (status === 'Submitted' || status === 'Cash Pending') return 'warn';
  return 'neutral';
}

function packageImageUrl(p) {
  const raw = String(p?.image_url || '').trim();
  if (!raw) return null;
  if (/^https?:\/\//i.test(raw)) return raw;
  if (raw.startsWith('/')) return raw;
  return `/storage/${raw}`;
}

function formatTime(ts) {
  if (!ts) return '';
  return new Date(ts).toLocaleString();
}

async function loadNotifications() {
  loadingNotifications.value = true;
  try {
    const res = await window.axios.get('/api/notifications');
    notifications.value = res.data.notifications || [];
    unreadCount.value = res.data.unreadCount || 0;
  } finally {
    loadingNotifications.value = false;
  }
}

async function markRead(n) {
  if (!n?.id) return;
  await window.axios.post(`/api/notifications/${n.id}/read`);
  await loadNotifications();
}

async function openNotifications() {
  activeTab.value = 'notifications';
  if (notifications.value.length === 0) {
    await loadNotifications();
  }
}

function normalizeTab(value) {
  const t = String(value || '').toLowerCase();
  if (t === 'home' || t === 'packages' || t === 'bookings' || t === 'notifications' || t === 'profile') {
    return t;
  }
  return 'home';
}

async function load() {
  loading.value = true;
  const res = await window.axios.get('/api/user/dashboard');
  packages.value = res.data.packages || [];
  bookings.value = res.data.bookings;
  loading.value = false;
}

onMounted(load);

watch(
  () => route.query?.tab,
  async (value) => {
    const t = normalizeTab(value);
    activeTab.value = t;
    if (t === 'notifications') {
      await openNotifications();
    }
  },
  { immediate: true }
);
</script>

<style scoped>
.page{max-width:1100px;margin:0 auto;padding:18px 14px 26px;}
.content{display:flex;flex-direction:column;gap:14px;}
.loading{padding:10px 2px;color:#64748b;font-weight:900;}

.top{display:grid;grid-template-columns:1.45fr 1fr;gap:14px;margin-bottom:14px;}

.hero{position:relative;min-height:240px;border-radius:18px;overflow:hidden;background-image:url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=60');background-size:cover;background-position:center;box-shadow:0 16px 40px rgba(2,8,23,0.15);}
.hero-content{position:relative;z-index:1;padding:28px 24px;max-width:560px;}
.hero-kicker{color:#e0f2fe;font-weight:1000;font-size:12px;letter-spacing:0.08em;text-transform:uppercase;}
.hero-title{margin:6px 0 0;color:#fff;font-weight:1000;font-size:30px;}
.hero-sub{margin-top:8px;color:#eaf2ff;font-weight:800;line-height:1.4;}
.hero-cta{display:inline-flex;margin-top:12px;background:#0d6efd;color:#fff;text-decoration:none;font-weight:1000;border-radius:12px;padding:10px 14px;box-shadow:0 10px 24px rgba(13,110,253,0.35);}
.hero-cta:hover{filter:brightness(0.98);}
.hero-mini{min-height:160px;}

.stats{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;align-content:start;}
.stat{background:#fff;border:1px solid #e6eef9;border-radius:14px;padding:12px;box-shadow:0 4px 12px rgba(2,8,23,0.04);}
.stat .k{color:#64748b;font-weight:1000;font-size:12px;}
.stat .v{margin-top:6px;font-size:22px;font-weight:1000;color:#0f172a;}

.card{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:14px;box-shadow:0 4px 12px rgba(2,8,23,0.04);}
.card-head{display:flex;align-items:baseline;justify-content:space-between;gap:12px;margin-bottom:10px;}
.section-title{font-size:18px;font-weight:1000;color:#0f172a;}
.link{color:#0d6efd;text-decoration:none;font-weight:900;font-size:13px;}
.link:hover{text-decoration:underline;}
.muted{color:#6b7280;font-weight:800;}
.mini{font-size:12px;}
.empty{padding:4px 0;}

.tools{display:flex;gap:10px;flex-wrap:wrap;margin:10px 0 12px 0;}
.tool{flex:1;min-width:220px;display:grid;gap:6px;}
.tk{font-weight:1000;font-size:12px;color:#64748b;}
input,select{width:100%;border-radius:12px;border:1px solid #e6eef9;padding:10px 12px;font-weight:900;outline:none;background:#fff;color:#0f172a;box-sizing:border-box;}
input:focus,select:focus{border-color:#93c5fd;box-shadow:0 0 0 4px rgba(59,130,246,0.18);}

.pkg-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;}
.pkg-card{background:#fff;border:1px solid #eef2f7;border-radius:16px;overflow:hidden;box-shadow:0 8px 24px rgba(2,8,23,0.05);display:flex;flex-direction:column;min-width:0;}
.pkg-img{height:150px;background:linear-gradient(180deg,#eff6ff,#ffffff);display:flex;align-items:center;justify-content:center;}
.pkg-img img{width:100%;height:150px;object-fit:cover;display:block;}
.pkg-body{padding:12px;display:flex;flex-direction:column;gap:10px;}
.pkg-top{display:flex;align-items:flex-start;justify-content:space-between;gap:10px;}
.pkg-title{font-size:15px;font-weight:1000;color:#0f172a;line-height:1.15;min-width:0;}
.pkg-price{font-weight:1000;color:#0d6efd;white-space:nowrap;}
.per{font-size:12px;font-weight:900;color:#64748b;margin-left:2px;}
.pkg-meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;border-top:1px solid #eef2f7;padding-top:10px;}
.pm{background:#fbfdff;border:1px solid #eef2f7;border-radius:14px;padding:10px;}
.pm .pk{font-size:12px;color:#64748b;font-weight:1000;}
.pm .pv{margin-top:4px;color:#0f172a;font-weight:1000;}
.pkg-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:2px;}
.btn{display:inline-flex;align-items:center;justify-content:center;border-radius:12px;padding:9px 12px;border:0;cursor:pointer;font-size:13px;text-decoration:none;font-weight:1000;}
.btn.primary{background:#0d6efd;color:#fff;}
.btn.ghost{background:#eff6ff;color:#0d6efd;border:1px solid #dbeafe;}

.destinations{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;}
.destination-card{background:#fff;border-radius:12px;overflow:hidden;border:1px solid #eef2f7;box-shadow:0 4px 12px rgba(2,8,23,0.04);display:flex;flex-direction:column;}
.img-wrap{width:100%;height:140px;background:#eef6ff;}
.destination-card img{width:100%;height:140px;object-fit:cover;display:block;}
.no-photo{width:100%;height:140px;display:flex;align-items:center;justify-content:center;color:#64748b;font-weight:1000;font-size:13px;background:linear-gradient(180deg,#ffffff,#f8fafc);border-bottom:1px solid #eef2f7;}
.info{padding:10px;flex:1;display:flex;flex-direction:column;justify-content:space-between;gap:10px;}
.info h3{margin:0;font-size:16px;font-weight:1000;color:#0f172a;}
.info p{margin:6px 0 0;font-size:13px;color:#555;font-weight:800;}

.card-actions{display:flex;justify-content:space-between;align-items:center;gap:10px;}
.btn{display:inline-flex;align-items:center;justify-content:center;border-radius:8px;padding:8px 10px;border:0;cursor:pointer;font-size:13px;text-decoration:none;font-weight:1000;}
.btn.primary{background:#0d6efd;color:#fff;}

.table-wrap{overflow:auto;border-radius:12px;border:1px solid #eef2f7;}
.table{width:100%;border-collapse:collapse;background:#fff;}
.table th,.table td{padding:10px;border-bottom:1px solid #eee;font-size:14px;text-align:left;}
.table th{background:#f7faff;color:#444;font-weight:1000;}
.table tr:last-child td{border-bottom:none;}

.pill{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:4px 10px;font-size:12px;font-weight:1000;border:1px solid transparent;}
.pill.ok{background:#ecfdf5;color:#065f46;border-color:#a7f3d0;}
.pill.warn{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;}
.pill.bad{background:#fef2f2;color:#991b1b;border-color:#fecaca;}
.pill.neutral{background:#f8fafc;color:#334155;border-color:#e2e8f0;}

.notif-list{display:flex;flex-direction:column;gap:8px;}
.notif{border:1px solid #eef2f7;background:#fff;border-radius:12px;padding:12px;text-align:left;cursor:pointer;}
.notif.unread{border-color:#bfdbfe;background:#eff6ff;}
.nt{font-weight:1000;color:#0f172a;margin-bottom:4px;}
.nm{color:#334155;font-size:14px;font-weight:800;}
.nd{color:#64748b;font-size:12px;margin-top:6px;font-weight:800;}

.profile-layout{display:grid;grid-template-columns:1.05fr 1fr;gap:14px;align-items:start;}
.profile-card{border:1px solid #e6eef9;border-radius:18px;padding:14px;background:linear-gradient(135deg,#eff6ff,#ffffff);box-shadow:0 10px 28px rgba(2,8,23,0.06);}
.profile-top{display:flex;align-items:center;gap:12px;}
.avatar{width:62px;height:62px;border-radius:18px;background:linear-gradient(180deg,#0d6efd,#60a5fa);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:1000;font-size:20px;flex:0 0 auto;box-shadow:0 12px 22px rgba(13,110,253,0.28);}
.p-main{min-width:0;}
.p-name{font-weight:1000;color:#0f172a;font-size:17px;line-height:1.15;}
.p-email{margin-top:4px;color:#64748b;font-weight:900;font-size:13px;word-break:break-word;}
.p-role{margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;}
.badge{display:inline-flex;align-items:center;justify-content:center;padding:6px 10px;border-radius:999px;background:#0d6efd;color:#fff;font-weight:1000;font-size:12px;}

.quick-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;}
.qa{display:inline-flex;align-items:center;justify-content:center;border-radius:999px;padding:9px 12px;border:1px solid #dbeafe;background:#ffffff;color:#0d6efd;text-decoration:none;font-weight:1000;font-size:13px;cursor:pointer;}
.qa:hover{filter:brightness(0.99);}
.qa-primary{background:#0d6efd;color:#fff;border-color:#0d6efd;}

.details-card{border:1px solid #eef2f7;border-radius:18px;padding:14px;background:#fff;box-shadow:0 10px 28px rgba(2,8,23,0.05);}
.details-title{font-weight:1000;color:#0f172a;font-size:15px;margin-bottom:10px;}
.details{display:grid;gap:10px;}
.d-row{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;border:1px solid #eef2f7;border-radius:14px;padding:12px;background:#fbfdff;}
.dk{color:#64748b;font-weight:1000;font-size:13px;}
.dv{color:#0f172a;font-weight:1000;font-size:14px;min-width:0;word-break:break-word;text-align:right;}

.modal{position:fixed;inset:0;z-index:50;display:flex;align-items:center;justify-content:center;padding:16px;}
.modal-backdrop{position:absolute;inset:0;background:rgba(2,8,23,0.55);}
.modal-card{position:relative;z-index:1;width:100%;max-width:560px;background:#fff;border:1px solid #e6eef9;border-radius:16px;box-shadow:0 18px 60px rgba(2,8,23,0.30);overflow:hidden;}
.modal-head{display:flex;align-items:center;justify-content:space-between;gap:10px;padding:14px 14px 10px 14px;border-bottom:1px solid #eef2f7;background:linear-gradient(90deg,#eff6ff,#ffffff);}
.modal-title{font-weight:1000;color:#0f172a;font-size:16px;}
.icon-btn{border:1px solid #e6eef9;background:#fff;border-radius:10px;padding:6px 10px;cursor:pointer;font-weight:1000;}
.modal-body{padding:14px;}
.modal-actions{display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;padding:12px 14px 14px 14px;border-top:1px solid #eef2f7;background:#fff;}
.form{display:grid;gap:10px;}
.field{display:grid;gap:6px;}
.label{font-weight:1000;font-size:12px;color:#64748b;}
.err{color:#b91c1c;font-weight:900;font-size:12px;}
.divider{height:1px;background:#eef2f7;margin:6px 0;}
.alert{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:12px;padding:10px 12px;font-weight:900;margin-bottom:10px;}

@media(max-width:900px){
  .top{grid-template-columns:1fr;}
  .stats{grid-template-columns:repeat(2,minmax(0,1fr));}
  .pkg-grid{grid-template-columns:1fr;}
  .profile-layout{grid-template-columns:1fr;}
}

@media(max-width:640px){
  .hero-content{padding:24px 16px;}
  .hero-title{font-size:24px;}
  .img-wrap{height:120px;}
  .destination-card img{height:120px;}
}
</style>
