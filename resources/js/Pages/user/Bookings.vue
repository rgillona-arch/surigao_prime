<template>
  <div class="page">
    <div class="hero">
      <div>
        <div class="hero-title">Bookings</div>
        <div class="hero-sub">Create a booking, view trips, submit payments, and download documents.</div>
      </div>

      <div class="hero-actions">
        <RouterLink class="hero-link" :to="{ name: 'user.dashboard' }">Back to Dashboard</RouterLink>
      </div>
    </div>

    <div v-if="loading">Loading...</div>

    <div v-else class="content">
      <div v-if="lightbox.open" class="lb" @click="closeLightbox">
        <div class="lb-inner" @click.stop>
          <button class="lb-x" type="button" @click="closeLightbox">×</button>
          <img class="lb-img" :src="lightbox.src" :alt="lightbox.title || 'Image'" />
          <div v-if="lightbox.title" class="lb-title">{{ lightbox.title }}</div>
        </div>
      </div>

      <div class="layout">
        <section class="card card-lg">
          <div class="card-head">
            <div class="card-head-left">
              <h3>New Booking</h3>
              <div class="muted">Choose a destination package and your travel date.</div>
            </div>
          </div>

          <form @submit.prevent="createBooking">
            <div class="form-grid">
              <label>
                Package
                <select v-model="createForm.package_id" required>
                  <option value="" disabled>Select package</option>
                  <option v-for="p in packages" :key="p.id" :value="p.id">
                    {{ p.title }} ({{ p.price }})
                  </option>
                </select>
              </label>

              <label>
                Travel date
                <input v-model="createForm.date" type="date" required />
              </label>

              <label>
                Travelers (pax)
                <input v-model.number="createForm.pax" type="number" min="1" required />
              </label>

              <label class="span2">
                Note (optional)
                <input v-model="createForm.note" type="text" placeholder="Any requests or reminders" />
              </label>
            </div>

            <div class="actions">
              <button class="btn" type="submit" :disabled="creating">
                {{ creating ? 'Saving...' : 'Create Booking' }}
              </button>
            </div>
          </form>
        </section>

        <section class="card card-lg">
          <div class="card-head">
            <div class="card-head-left">
              <h3>Selected Package</h3>
              <div class="muted">Preview the place you are booking.</div>
            </div>
          </div>

          <div class="pkg-preview pro">
            <button
              class="pkg-img lg"
              :class="selectedPackage?.image_url ? '' : 'empty'"
              type="button"
              @click="selectedPackage?.image_url && openLightbox(selectedPackage.image_url, selectedPackage.title)"
            >
              <img v-if="selectedPackage?.image_url" :src="selectedPackage.image_url" :alt="selectedPackage.title" />
              <div v-else class="pkg-placeholder">No image</div>
            </button>
            <div class="pkg-meta">
              <div class="pkg-title">{{ selectedPackage?.title || 'Select a package' }}</div>
              <div class="pkg-desc">{{ selectedPackage?.description || 'Pick a package to see details here.' }}</div>
            </div>
          </div>
        </section>
      </div>

      <section class="card card-lg">
        <div class="sec-top">
          <h3>Your Trips</h3>
          <div class="muted">{{ bookings.length }} total</div>
        </div>

        <div v-if="bookings.length === 0" class="empty">No bookings yet.</div>

        <div v-else class="trip-grid">
          <div v-for="b in bookings" :key="b.id" class="trip">
            <button
              class="trip-img"
              type="button"
              :class="b.package?.image_url ? '' : 'empty'"
              @click="b.package?.image_url && openLightbox(b.package.image_url, b.package?.title || 'Trip')"
            >
              <img v-if="b.package?.image_url" :src="b.package.image_url" :alt="b.package?.title || 'Trip'" />
              <div v-else class="trip-ph">No image</div>
            </button>

            <div class="trip-body">
              <div class="trip-head">
                <div class="trip-title">{{ b.package?.title || 'Package' }}</div>
                <div class="trip-id">#{{ b.id }}</div>
              </div>

              <div class="trip-meta">
                <div class="meta-item">
                  <div class="k">Date</div>
                  <div class="v">{{ b.date }}</div>
                </div>
                <div class="meta-item">
                  <div class="k">Pax</div>
                  <div class="v">{{ b.pax }}</div>
                </div>
                <div class="meta-item">
                  <div class="k">Status</div>
                  <div class="v"><span class="chip">{{ b.status }}</span></div>
                </div>
                <div class="meta-item">
                  <div class="k">Payment</div>
                  <div class="v"><span class="chip soft">{{ b.payment_status }}</span></div>
                </div>
              </div>

              <div class="trip-docs">
                <div class="k">Documents</div>
                <div v-if="b.documents?.length" class="docs">
                  <a v-for="d in b.documents" :key="d.id" class="doc" :href="docUrl(d.file_path)" target="_blank">
                    {{ d.title }}
                  </a>
                </div>
                <div v-else class="muted">No documents yet.</div>
              </div>

              <div class="trip-actions">
                <button v-if="b.status === 'Pending'" class="btn gray" type="button" @click="cancelBooking(b.id)">Cancel</button>
              </div>

              <div v-if="b.status === 'Approved' && b.payment_status !== 'Paid'" class="pay">
                <div class="pay-title">Payment Module</div>
                <div class="pay-sub">Select a payment method and confirm your payment.</div>

                <div v-if="paymentMessages[b.id]?.text" class="pm" :class="paymentMessages[b.id]?.type">
                  {{ paymentMessages[b.id].text }}
                </div>

                <div class="pay-select">
                  <button
                    class="pay-opt"
                    type="button"
                    :class="paymentForms[b.id].payment_method === 'cash' ? 'active' : ''"
                    @click="selectPaymentMethod(b.id, 'cash')"
                  >
                    Pay via Cash
                  </button>
                  <button
                    class="pay-opt"
                    type="button"
                    :class="paymentForms[b.id].payment_method === 'gcash' ? 'active' : ''"
                    @click="selectPaymentMethod(b.id, 'gcash')"
                  >
                    Pay via GCash
                  </button>
                  <button
                    class="pay-opt"
                    type="button"
                    :class="paymentForms[b.id].payment_method === 'card' ? 'active' : ''"
                    @click="selectPaymentMethod(b.id, 'card')"
                  >
                    Pay via Card
                  </button>
                </div>

                <div v-if="paymentForms[b.id].payment_method === 'cash'" class="pay-panel">
                  <div class="panel-title">Cash Payment</div>
                  <div class="panel-sub">You can pay in person. Please keep your booking reference for verification.</div>

                  <label>
                    Note (optional)
                    <input v-model="paymentForms[b.id].payment_reference" type="text" placeholder="Optional note (e.g., pay on arrival)" />
                  </label>

                  <div class="security-note">All transactions are securely processed.</div>
                </div>

                <div v-else-if="paymentForms[b.id].payment_method === 'gcash'" class="pay-panel">
                  <div class="panel-title">GCash Payment Details</div>
                  <div class="panel-sub">Please send the exact amount and upload proof of payment.</div>

                  <div class="receiver">
                    <div class="r-title">Send payment to:</div>
                    <div class="r-item"><span class="rk">Name</span><span class="rv">{{ gcashReceiverName }}</span></div>
                    <div class="r-item"><span class="rk">Number</span><span class="rv">{{ gcashReceiverNumber }}</span></div>
                  </div>

                  <div class="panel-grid">
                    <label>
                      GCash Name
                      <input v-model="paymentForms[b.id].gcash_name" type="text" placeholder="Your GCash name" />
                    </label>
                    <label>
                      GCash Number
                      <input v-model="paymentForms[b.id].gcash_number" inputmode="numeric" type="text" placeholder="09XX-XXX-XXXX" />
                    </label>
                  </div>

                  <div class="upload">
                    <div class="upload-top">
                      <div class="u-title">Upload proof of payment</div>
                      <div class="u-sub">Screenshot (JPG/PNG) or PDF</div>
                    </div>
                    <label class="upload-btn">
                      <input type="file" class="file" @change="(e) => onProofChange(b.id, e)" accept=".jpg,.jpeg,.png,.pdf" />
                      Choose file
                    </label>
                    <div class="u-name">{{ paymentForms[b.id].payment_proof?.name || 'No file selected' }}</div>
                  </div>
                </div>

                <div v-else-if="paymentForms[b.id].payment_method === 'card'" class="pay-panel">
                  <div class="panel-title">Card Payment</div>
                  <div class="panel-sub">All transactions are securely processed.</div>

                  <div class="panel-grid card-grid">
                    <label>
                      Cardholder Name
                      <input v-model="paymentForms[b.id].cardholder_name" type="text" placeholder="Name on card" />
                    </label>
                    <label>
                      Card Number
                      <input v-model="paymentForms[b.id].card_number" inputmode="numeric" type="text" placeholder="1234 5678 9012 3456" />
                    </label>
                    <label>
                      Expiry Date
                      <input v-model="paymentForms[b.id].expiry" type="text" placeholder="MM/YY" />
                    </label>
                    <label>
                      CVV
                      <input v-model="paymentForms[b.id].cvv" inputmode="numeric" type="password" placeholder="123" />
                    </label>
                  </div>

                  <div class="security-note">All transactions are securely processed.</div>
                </div>

                <div class="pay-actions">
                  <button class="btn" type="button" @click="confirmPayment(b.id)" :disabled="submittingPaymentId === b.id">
                    <span v-if="submittingPaymentId !== b.id">Confirm Payment</span>
                    <span v-else class="loading">
                      <span class="dot"></span>
                      Processing...
                    </span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';

const loading = ref(true);
const creating = ref(false);

const packages = ref([]);
const bookings = ref([]);

const paymentForms = reactive({});
const submittingPaymentId = ref(null);
const paymentMessages = reactive({});

const gcashReceiverName = String(import.meta?.env?.VITE_GCASH_RECEIVER_NAME || 'GCash Receiver Name');
const gcashReceiverNumber = String(import.meta?.env?.VITE_GCASH_RECEIVER_NUMBER || '09XX-XXX-XXXX');

const createForm = reactive({
  package_id: '',
  date: '',
  pax: 1,
  note: '',
});

const selectedPackage = computed(() => {
  const id = Number(createForm.package_id);
  if (!id) return null;
  return packages.value.find((p) => Number(p.id) === id) || null;
});

const lightbox = reactive({
  open: false,
  src: '',
  title: '',
});

function openLightbox(src, title) {
  lightbox.open = true;
  lightbox.src = src;
  lightbox.title = title || '';
}

function closeLightbox() {
  lightbox.open = false;
  lightbox.src = '';
  lightbox.title = '';
}

async function load() {
  loading.value = true;
  const res = await window.axios.get('/api/user/dashboard');
  packages.value = res.data.packages;
  bookings.value = res.data.bookings;

  for (const b of bookings.value) {
    if (!paymentForms[b.id]) {
      paymentForms[b.id] = {
        payment_method: '',
        payment_reference: '',
        payment_proof: null,
        gcash_name: '',
        gcash_number: '',
        cardholder_name: '',
        card_number: '',
        expiry: '',
        cvv: '',
      };
    }
  }

  loading.value = false;
}

async function createBooking() {
  creating.value = true;
  try {
    await window.axios.post('/api/bookings', createForm);
    await load();
  } catch (e) {
    const status = e?.response?.status;
    if (status === 422) {
      const errors = e?.response?.data?.errors;
      const first = errors ? Object.values(errors).flat()?.[0] : null;
      alert(first || 'Booking failed. Please check your details and try again.');
      return;
    }
    if (status === 401) {
      alert('Your session expired. Please login again.');
      return;
    }
    alert('Booking failed. Please try again.');
  } finally {
    creating.value = false;
  }
}

async function cancelBooking(id) {
  await window.axios.post(`/api/bookings/${id}/cancel`);
  await load();
}

function onProofChange(bookingId, e) {
  paymentForms[bookingId].payment_proof = e?.target?.files?.[0] || null;
}

function docUrl(path) {
  return `/storage/${path}`;
}

async function submitPayment(bookingId) {
  submittingPaymentId.value = bookingId;
  try {
    const f = paymentForms[bookingId];

    const fd = new FormData();
    fd.append('payment_method', f.payment_method);
    if (f.payment_reference) {
      fd.append('payment_reference', f.payment_reference);
    }
    if (f.payment_method !== 'cash') {
      if (!f.payment_proof) {
        alert('Please attach a proof file for online payments.');
        return;
      }
      fd.append('payment_proof', f.payment_proof);
    }

    await window.axios.post(`/api/bookings/${bookingId}/payment`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    paymentForms[bookingId].payment_reference = '';
    paymentForms[bookingId].payment_proof = null;
    await load();
  } finally {
    submittingPaymentId.value = null;
  }
}

function setPaymentMessage(bookingId, type, text) {
  paymentMessages[bookingId] = { type, text };
}

function selectPaymentMethod(bookingId, method) {
  paymentForms[bookingId].payment_method = method;
  setPaymentMessage(bookingId, null, '');
}

function normalizeCardNumber(num) {
  return String(num || '').replace(/\s+/g, '');
}

function isValidExpiry(v) {
  const s = String(v || '').trim();
  if (!/^\d{2}\/\d{2}$/.test(s)) return false;
  const [mmStr, yyStr] = s.split('/');
  const mm = Number(mmStr);
  const yy = Number(yyStr);
  if (!mm || mm < 1 || mm > 12) return false;
  if (Number.isNaN(yy)) return false;
  return true;
}

function validatePayment(bookingId) {
  const f = paymentForms[bookingId];
  if (!f.payment_method) {
    setPaymentMessage(bookingId, 'error', 'Payment failed. Please check your details and try again.');
    return false;
  }

  if (f.payment_method === 'cash') {
    return true;
  }

  if (f.payment_method === 'gcash') {
    const name = String(f.gcash_name || '').trim();
    const num = String(f.gcash_number || '').replace(/\s+/g, '');
    if (!name) {
      setPaymentMessage(bookingId, 'error', 'Payment failed. Please check your details and try again.');
      return false;
    }
    if (!/^\d{10,13}$/.test(num)) {
      setPaymentMessage(bookingId, 'error', 'Payment failed. Please check your details and try again.');
      return false;
    }
    if (!f.payment_proof) {
      setPaymentMessage(bookingId, 'error', 'Payment failed. Please check your details and try again.');
      return false;
    }
    return true;
  }

  if (f.payment_method === 'card') {
    const cardholder = String(f.cardholder_name || '').trim();
    const cardNum = normalizeCardNumber(f.card_number);
    const expiry = String(f.expiry || '').trim();
    const cvv = String(f.cvv || '').trim();

    if (!cardholder || !cardNum || !expiry || !cvv) {
      setPaymentMessage(bookingId, 'error', 'Payment failed. Please check your details and try again.');
      return false;
    }

    if (!/^\d{13,19}$/.test(cardNum)) {
      setPaymentMessage(bookingId, 'error', 'Payment failed. Please check your details and try again.');
      return false;
    }

    if (!isValidExpiry(expiry)) {
      setPaymentMessage(bookingId, 'error', 'Payment failed. Please check your details and try again.');
      return false;
    }

    if (!/^\d{3,4}$/.test(cvv)) {
      setPaymentMessage(bookingId, 'error', 'Payment failed. Please check your details and try again.');
      return false;
    }

    return true;
  }

  setPaymentMessage(bookingId, 'error', 'Payment failed. Please check your details and try again.');
  return false;
}

async function confirmPayment(bookingId) {
  if (!validatePayment(bookingId)) return;

  setPaymentMessage(bookingId, 'pending', 'Your payment is being processed.');

  const f = paymentForms[bookingId];

  if (f.payment_method === 'gcash') {
    const name = String(f.gcash_name || '').trim();
    const num = String(f.gcash_number || '').replace(/\s+/g, '');
    f.payment_reference = `GCASH:${name}:${num}`;
  }

  if (f.payment_method === 'card') {
    const cardNum = normalizeCardNumber(f.card_number);
    const last4 = cardNum.slice(-4);
    f.payment_reference = `CARD-****${last4}`;
  }

  try {
    await submitPayment(bookingId);
    setPaymentMessage(bookingId, 'success', 'Your payment has been successfully submitted. Please wait for verification.');
  } catch (e) {
    setPaymentMessage(bookingId, 'error', 'Payment failed. Please check your details and try again.');
    throw e;
  }
}

onMounted(load);
</script>

<style scoped>
.page, .page *{box-sizing:border-box;}
.page{max-width:1100px;margin:0 auto;padding:18px 14px 26px;}
.content{display:flex;flex-direction:column;gap:14px;}
.hero{display:flex;align-items:flex-end;justify-content:space-between;gap:14px;margin-bottom:14px;}
.hero-title{font-size:22px;font-weight:1000;color:#0f172a;letter-spacing:-0.02em;}
.hero-sub{margin-top:4px;color:#64748b;font-weight:800;font-size:13px;}
.hero-actions{display:flex;align-items:center;gap:8px;}
.hero-link{text-decoration:none;background:#eef6ff;color:#0d6efd;border:1px solid #dbeafe;padding:8px 12px;border-radius:999px;font-weight:900;font-size:13px;}

.layout{display:grid;grid-template-columns:1.2fr 0.8fr;gap:12px;}
.card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:10px;}
.card-head-left{display:flex;flex-direction:column;gap:2px;min-width:0;}
.card-head h3{margin:0;}
.card-head .muted{line-height:1.35;}
.actions{display:flex;justify-content:flex-end;margin-top:10px;}
.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;}
.span2{grid-column:1 / -1;}

.card{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:14px;margin-bottom:16px;max-width:100%;min-width:0;}
.card-lg{border-radius:16px;box-shadow:0 10px 28px rgba(2,8,23,0.04);}
.sec-top{display:flex;align-items:center;justify-content:space-between;gap:10px;}
.muted{color:#64748b;font-size:13px;font-weight:800;}
.empty{color:#64748b;padding:10px 0;}

.sec-top h3{margin:0;}
button{background:#0d6efd;color:#fff;border:0;border-radius:8px;padding:8px 10px;cursor:pointer;}
.btn{background:#0d6efd;color:#fff;border:0;border-radius:10px;padding:9px 12px;cursor:pointer;font-weight:900;font-size:13px;}
.btn.gray{background:#64748b;}
input,select,textarea{width:100%;max-width:100%;padding:8px;border-radius:8px;border:1px solid #e6eef9;box-sizing:border-box;}
label{display:block;font-size:13px;color:#41586b;}
.docs{display:flex;flex-direction:column;gap:4px;}
.doc{color:#0d6efd;text-decoration:none;font-weight:800;font-size:13px;}
.pkg-preview{display:flex;gap:12px;align-items:flex-start;border:1px solid #eef2f7;border-radius:14px;padding:12px;background:#fbfdff;min-width:0;}
.pkg-preview.pro{min-height:148px;flex-direction:column;}
.pkg-img{width:160px;height:100px;border-radius:14px;overflow:hidden;border:1px solid #e6eef9;background:#f8fafc;display:flex;align-items:center;justify-content:center;padding:0;cursor:pointer;flex:0 0 auto;}
.pkg-img.lg{width:100%;max-width:100%;height:160px;}
.pkg-img img{width:100%;height:100%;object-fit:cover;display:block;}
.pkg-placeholder{color:#64748b;font-weight:800;font-size:13px;}
.pkg-meta{flex:1;min-width:0;width:100%;}
.pkg-title{font-weight:900;color:#0f172a;margin-bottom:4px;}
.pkg-desc{color:#475569;font-size:13px;white-space:pre-wrap;}

.trip-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:10px;}
.trip{border:1px solid #e6eef9;border-radius:14px;overflow:hidden;background:#fff;display:flex;flex-direction:column;}
.trip-img{width:100%;height:170px;border:0;padding:0;border-radius:0;background:#f8fafc;cursor:pointer;display:flex;align-items:center;justify-content:center;}
.trip-img img{width:100%;height:100%;object-fit:cover;display:block;}
.trip-ph{color:#64748b;font-weight:900;}
.trip-body{padding:12px;display:flex;flex-direction:column;gap:10px;}
.trip-head{display:flex;align-items:center;justify-content:space-between;gap:10px;}
.trip-title{font-weight:1000;color:#0f172a;}
.trip-id{color:#64748b;font-weight:900;font-size:12px;}
.trip-meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;border-top:1px solid #eef2f7;padding-top:10px;}
.meta-item .k{color:#64748b;font-size:12px;font-weight:900;}
.meta-item .v{color:#0f172a;font-weight:900;font-size:13px;margin-top:3px;}
.chip{display:inline-block;padding:3px 8px;border-radius:999px;background:#eef2ff;color:#3730a3;border:1px solid #e0e7ff;font-weight:1000;font-size:12px;}
.chip.soft{background:#f1f5f9;color:#0f172a;border-color:#e2e8f0;}
.trip-docs .k{color:#64748b;font-size:12px;font-weight:900;margin-bottom:6px;}
.trip-actions{display:flex;justify-content:flex-end;}

.pay{margin-top:12px;padding:12px;border:1px solid #e6eef9;border-radius:14px;background:linear-gradient(180deg,#f8fbff,#ffffff);}
.pay-title{font-weight:1000;color:#0b4b73;margin-bottom:2px;}
.pay-sub{color:#64748b;font-weight:800;font-size:12px;margin-bottom:10px;}

.pm{border-radius:12px;padding:10px 12px;font-weight:900;font-size:13px;margin-bottom:10px;border:1px solid transparent;}
.pm.success{background:#ecfdf5;color:#065f46;border-color:#a7f3d0;}
.pm.pending{background:#eff6ff;color:#1d4ed8;border-color:#bfdbfe;}
.pm.error{background:#fef2f2;color:#991b1b;border-color:#fecaca;}

.pay-select{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:10px;}
.pay-opt{background:#fff;color:#0f172a;border:1px solid #e6eef9;border-radius:14px;padding:12px 12px;cursor:pointer;text-align:center;font-weight:1000;}
.pay-opt:hover{border-color:#c7ddff;box-shadow:0 8px 22px rgba(2,8,23,0.05);}
.pay-opt.active{border-color:#0d6efd;box-shadow:0 12px 28px rgba(13,110,253,0.18);}

.pay-panel{border:1px solid #eef2f7;border-radius:14px;padding:12px;background:#fff;margin-top:10px;}
.panel-title{font-weight:1000;color:#0f172a;margin-bottom:3px;}
.panel-sub{color:#64748b;font-weight:800;font-size:12px;margin-bottom:10px;}
.panel-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;align-items:end;}
.card-grid input{letter-spacing:0.02em;}
.security-note{margin-top:10px;color:#64748b;font-weight:900;font-size:12px;}

.receiver{border:1px solid #e6eef9;border-radius:14px;padding:10px 12px;background:#fbfdff;margin-bottom:10px;}
.r-title{font-weight:1000;color:#0f172a;margin-bottom:6px;font-size:13px;}
.r-item{display:flex;align-items:center;justify-content:space-between;gap:10px;}
.rk{color:#64748b;font-weight:900;font-size:12px;}
.rv{color:#0f172a;font-weight:1000;font-size:12px;}

.loading{display:inline-flex;align-items:center;gap:8px;}
.dot{width:8px;height:8px;border-radius:999px;background:#fff;opacity:0.9;animation:pulse 1s infinite ease-in-out;}
@keyframes pulse{0%{transform:scale(1);opacity:0.5}50%{transform:scale(1.35);opacity:1}100%{transform:scale(1);opacity:0.5}}

.upload{border:1px dashed #c7ddff;border-radius:14px;padding:10px;background:#eff6ff;}
.upload-top{display:flex;align-items:baseline;justify-content:space-between;gap:10px;margin-bottom:8px;}
.u-title{font-weight:1000;color:#0f172a;font-size:13px;}
.u-sub{color:#64748b;font-weight:800;font-size:12px;}
.upload-btn{display:inline-flex;align-items:center;justify-content:center;background:#0d6efd;color:#fff;border-radius:12px;padding:8px 10px;font-weight:1000;cursor:pointer;width:max-content;}
.file{display:none;}
.u-name{margin-top:8px;color:#0f172a;font-weight:900;font-size:12px;word-break:break-word;}
.pay-actions{display:flex;justify-content:flex-end;margin-top:10px;}

.lb{position:fixed;inset:0;background:rgba(15,23,42,0.72);display:flex;align-items:center;justify-content:center;z-index:9999;padding:18px;}
.lb-inner{position:relative;max-width:980px;width:100%;max-height:90vh;background:#0b1220;border-radius:14px;overflow:hidden;border:1px solid rgba(255,255,255,0.08);}
.lb-img{width:100%;height:auto;display:block;max-height:78vh;object-fit:contain;background:#0b1220;}
.lb-title{padding:10px 12px;color:#e2e8f0;font-weight:900;font-size:13px;border-top:1px solid rgba(255,255,255,0.08);}
.lb-x{position:absolute;top:10px;right:10px;width:36px;height:36px;border-radius:999px;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.14);color:#fff;font-size:22px;line-height:1;cursor:pointer;}

@media(max-width:900px){
  .layout{grid-template-columns:1fr;}
  .form-grid{grid-template-columns:1fr;}
  .trip-grid{grid-template-columns:1fr;}
  .trip-img{height:190px;}
  .pay-select{grid-template-columns:1fr;}
  .panel-grid{grid-template-columns:1fr;}
  .hero{flex-direction:column;align-items:flex-start;}
}
</style>
