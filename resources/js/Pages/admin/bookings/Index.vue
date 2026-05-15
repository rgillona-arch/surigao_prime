<template>
  <div>
    <h2>Bookings</h2>

    <div class="card">
      <div class="row">
        <label>
          Status
          <select v-model="status" @change="load">
            <option value="">All</option>
            <option value="Pending">Pending</option>
            <option value="Approved">Approved</option>
            <option value="Cancelled">Cancelled</option>
          </select>
        </label>
      </div>

      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Package</th>
            <th>Status</th>
            <th>Payment</th>
            <th>Method</th>
            <th>Reference</th>
            <th>Proof</th>
            <th>Documents</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <template v-for="b in bookings" :key="b.id">
            <tr>
              <td>{{ b.id }}</td>
              <td>{{ b.customer_name }}</td>
              <td>{{ b.package?.title }}</td>
              <td>{{ b.status }}</td>
              <td>{{ b.payment_status }}</td>
              <td>{{ b.payment_method || '-' }}</td>
              <td>{{ b.payment_reference || '-' }}</td>
              <td>
                <a v-if="b.payment_proof_path" class="link" :href="paymentProofUrl(b.id)" target="_blank">View</a>
                <span v-else>-</span>
              </td>
              <td>
                <div v-if="b.documents?.length" class="docs">
                  <a v-for="d in b.documents" :key="d.id" class="doc" :href="proofUrl(d.file_path)" target="_blank">
                    {{ d.title }}
                  </a>
                </div>
                <span v-else>-</span>

                <button class="small" type="button" @click="toggleDocPanel(b.id)">
                  {{ openDocId === b.id ? 'Close' : 'Upload' }}
                </button>
              </td>
              <td class="actions">
                <button class="blue" type="button" @click="approve(b.id)" :disabled="!canApprove(b)">Approve</button>
                <button class="gray" type="button" @click="cancel(b.id)" :disabled="!canCancel(b)">Cancel</button>
                <button class="green" type="button" @click="verifyPayment(b.id)" :disabled="!canVerifyPayment(b)">Verify Payment</button>
                <button class="amber" type="button" @click="rejectPayment(b.id)" :disabled="!canVerifyPayment(b)">Reject</button>
                <button class="red" type="button" @click="destroy(b.id)">Delete</button>
              </td>
            </tr>

            <tr v-if="openDocId === b.id" :key="'doc-' + b.id">
              <td colspan="10">
                <div class="doc-panel">
                  <div class="doc-title">Upload Document for Booking #{{ b.id }}</div>

                  <div class="row">
                    <label>
                      Type
                      <select v-model="docForms[b.id].type">
                        <option value="voucher">Voucher</option>
                        <option value="eticket">E-ticket</option>
                        <option value="summary">Booking Summary</option>
                        <option value="requirement">Requirement</option>
                      </select>
                    </label>

                    <label>
                      Title
                      <input v-model="docForms[b.id].title" type="text" placeholder="e.g. E-ticket" />
                    </label>

                    <label>
                      File (PDF/JPG/PNG)
                      <input type="file" @change="(e) => onDocFileChange(b.id, e)" accept=".pdf,.jpg,.jpeg,.png" />
                    </label>

                    <button class="blue" type="button" @click="uploadDoc(b.id)" :disabled="uploadingDocId === b.id">
                      {{ uploadingDocId === b.id ? 'Uploading...' : 'Upload' }}
                    </button>
                  </div>
                </div>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';

const bookings = ref([]);
const status = ref('');
const openDocId = ref(null);
const docForms = reactive({});
const uploadingDocId = ref(null);

async function load() {
  const res = await window.axios.get('/api/admin/bookings', { params: { status: status.value || undefined } });
  bookings.value = res.data.bookings;

  for (const b of bookings.value) {
    if (!docForms[b.id]) {
      docForms[b.id] = {
        type: 'voucher',
        title: '',
        file: null,
      };
    }
  }
}

async function approve(id) {
  await window.axios.post(`/api/admin/bookings/${id}/approve`);
  await load();
}

async function cancel(id) {
  await window.axios.post(`/api/admin/bookings/${id}/cancel`);
  await load();
}

async function markPaid(id) {
  await window.axios.post(`/api/admin/bookings/${id}/mark-paid`);
  await load();
}

async function markSubmitted(id) {
  await window.axios.post(`/api/admin/bookings/${id}/mark-submitted`);
  await load();
}

async function verifyPayment(id) {
  await window.axios.post(`/api/admin/bookings/${id}/verify-payment`);
  await load();
}

async function rejectPayment(id) {
  const reason = window.prompt('Reason (optional):') || '';
  await window.axios.post(`/api/admin/bookings/${id}/reject-payment`, { reason: reason || undefined });
  await load();
}

function canApprove(b) {
  const s = String(b?.status || '').toLowerCase();
  if (s.includes('approved')) return false;
  if (s.includes('cancel')) return false;
  return true;
}

function canCancel(b) {
  const s = String(b?.status || '').toLowerCase();
  if (s.includes('approved')) return false;
  if (s.includes('cancel')) return false;
  return true;
}

function canVerifyPayment(b) {
  return b?.payment_status === 'Submitted' || b?.payment_status === 'Cash Pending';
}

function proofUrl(path) {
  return `/storage/${path}`;
}

function paymentProofUrl(bookingId) {
  return `/api/admin/bookings/${bookingId}/payment-proof`;
}

function toggleDocPanel(id) {
  openDocId.value = openDocId.value === id ? null : id;
}

function onDocFileChange(bookingId, e) {
  docForms[bookingId].file = e?.target?.files?.[0] || null;
}

async function uploadDoc(bookingId) {
  uploadingDocId.value = bookingId;
  try {
    const f = docForms[bookingId];
    if (!f.title) {
      alert('Please enter a title.');
      return;
    }
    if (!f.file) {
      alert('Please choose a file.');
      return;
    }

    const fd = new FormData();
    fd.append('type', f.type);
    fd.append('title', f.title);
    fd.append('file', f.file);

    await window.axios.post(`/api/admin/bookings/${bookingId}/documents`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    f.title = '';
    f.file = null;
    await load();
  } finally {
    uploadingDocId.value = null;
  }
}

async function destroy(id) {
  await window.axios.delete(`/api/admin/bookings/${id}`);
  await load();
}

onMounted(load);
</script>

<style scoped>
.card{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:14px;}
.row{display:flex;gap:12px;margin-bottom:10px;}
.table{width:100%;border-collapse:collapse;}
.table th,.table td{border-bottom:1px solid #eef2f7;padding:8px;text-align:left;font-size:14px;}
.actions{display:flex;flex-wrap:wrap;gap:6px;}
button{background:#0d6efd;color:#fff;border:0;border-radius:8px;padding:7px 9px;cursor:pointer;font-size:12px;}
button:disabled{opacity:.45;cursor:not-allowed;}
.small{background:#eef6ff;color:#0d6efd;font-weight:900;border:1px solid #dbeafe;margin-top:6px;}
.blue{background:#0d6efd;}
.gray{background:#64748b;}
.green{background:#16a34a;}
.amber{background:#d97706;}
.red{background:#dc2626;}
.link{color:#0d6efd;text-decoration:none;font-weight:800;}
.docs{display:flex;flex-direction:column;gap:4px;margin-bottom:4px;}
.doc{color:#0d6efd;text-decoration:none;font-weight:800;font-size:13px;}
.doc-panel{margin:6px 0;padding:12px;border:1px solid #e6eef9;border-radius:12px;background:#f8fbff;}
.doc-title{font-weight:900;color:#0b4b73;margin-bottom:8px;}
input{padding:8px;border-radius:8px;border:1px solid #e6eef9;}
select{padding:8px;border-radius:8px;border:1px solid #e6eef9;}
label{font-size:13px;color:#41586b;}
</style>
