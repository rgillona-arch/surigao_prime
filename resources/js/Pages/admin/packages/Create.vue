<template>
  <div class="page">
    <div class="head">
      <div>
        <div class="eyebrow">Admin</div>
        <h2 class="title">Create Package</h2>
        <div class="subtitle">Add a new tour offering and publish it for customers.</div>
      </div>

      <div class="head-actions">
        <button class="btn btn-light" type="button" @click="goBack" :disabled="saving">Back</button>
        <button class="btn" type="button" @click="submit" :disabled="saving">{{ saving ? 'Saving...' : 'Save Package' }}</button>
      </div>
    </div>

    <div class="layout">
      <div class="card">
        <form @submit.prevent="submit">
          <div class="grid3">
            <label>
              Title
              <input v-model="form.title" required placeholder="e.g. Surigao Island Hopping" />
            </label>

            <label>
              Price (PHP)
              <input v-model.number="form.price" type="number" min="0" required />
              <div class="help">Shown to customers per person.</div>
            </label>

            <label>
              Slots/Day
              <input v-model.number="form.slots_per_day" type="number" min="0" required />
              <div class="help">Capacity available per date.</div>
            </label>
          </div>

          <label>
            Package Image
            <div class="uploader">
              <input ref="fileInput" class="file" type="file" accept="image/*" @change="onPickFile" />
              <button class="upload-btn" type="button" @click="openFilePicker" :disabled="saving">Upload image</button>
              <div class="upload-meta">
                <div class="upload-name">{{ pickedFileName }}</div>
                <div class="help">JPG/PNG/WEBP up to 5MB.</div>
              </div>
              <button v-if="pickedFile" class="clear-btn" type="button" @click="clearPicked" :disabled="saving">Remove</button>
            </div>
          </label>

          <label>
            Description
            <textarea v-model="form.description" rows="6" placeholder="Describe what's included, itinerary, inclusions..." />
          </label>

          <div class="form-actions">
            <button class="btn btn-light" type="button" @click="reset" :disabled="saving">Reset</button>
            <button class="btn" type="submit" :disabled="saving">{{ saving ? 'Saving...' : 'Save Package' }}</button>
          </div>
        </form>
      </div>

      <div class="card preview">
        <div class="preview-title">Live Preview</div>

        <div class="preview-media">
          <img v-if="previewSrc" :src="previewSrc" alt="Package image preview" />
          <div v-else class="preview-empty">No image preview</div>
        </div>

        <div class="preview-body">
          <div class="preview-name">{{ form.title?.trim() || 'Package title' }}</div>
          <div class="preview-sub">
            <span class="pill">₱{{ formatMoney(form.price) }}</span>
            <span class="pill pill-muted">{{ Number(form.slots_per_day || 0) }} slots/day</span>
          </div>
          <div class="preview-desc">{{ (form.description || '').trim() || 'Package description will appear here.' }}</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const saving = ref(false);

const form = reactive({
  title: '',
  description: '',
  price: 0,
  slots_per_day: 0,
});

const pickedFile = ref(null);
const pickedPreview = ref('');
const fileInput = ref(null);

const pickedFileName = computed(() => {
  if (pickedFile.value?.name) return pickedFile.value.name;
  return 'No file chosen';
});

const previewSrc = computed(() => {
  if (pickedPreview.value) return pickedPreview.value;
  return '';
});

function formatMoney(v) {
  const n = Number(v || 0);
  return new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 }).format(n);
}

function goBack() {
  router.push({ name: 'admin.packages.index' });
}

function reset() {
  form.title = '';
  form.description = '';
  form.price = 0;
  form.slots_per_day = 0;
  clearPicked();
}

function openFilePicker() {
  fileInput.value?.click?.();
}

function clearPicked() {
  pickedFile.value = null;
  if (pickedPreview.value) URL.revokeObjectURL(pickedPreview.value);
  pickedPreview.value = '';
  if (fileInput.value) fileInput.value.value = '';
}

function onPickFile(e) {
  const file = e?.target?.files?.[0] || null;
  pickedFile.value = file;
  if (pickedPreview.value) URL.revokeObjectURL(pickedPreview.value);
  pickedPreview.value = file ? URL.createObjectURL(file) : '';
}

onBeforeUnmount(() => {
  if (pickedPreview.value) URL.revokeObjectURL(pickedPreview.value);
});

async function submit() {
  if (saving.value) return;
  saving.value = true;
  try {
    if (pickedFile.value) {
      const fd = new FormData();
      fd.append('title', form.title);
      fd.append('description', form.description || '');
      fd.append('price', String(form.price ?? 0));
      fd.append('slots_per_day', String(form.slots_per_day ?? 0));
      fd.append('image', pickedFile.value);
      await window.axios.post('/api/admin/packages', fd, {
        headers: { 'Content-Type': 'multipart/form-data' },
      });
    } else {
      await window.axios.post('/api/admin/packages', form);
    }
    await router.push({ name: 'admin.packages.index' });
  } finally {
    saving.value = false;
  }
}
</script>

<style scoped>
.page{max-width:1100px;margin:0 auto;padding:18px;}

.head{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-bottom:14px;}
.eyebrow{color:#0d6efd;font-weight:900;font-size:12px;letter-spacing:.08em;text-transform:uppercase;}
.title{margin:2px 0 4px;font-size:26px;line-height:1.15;color:#0f172a;}
.subtitle{color:#475569;font-size:14px;max-width:720px;}
.head-actions{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end;}

.layout{display:grid;grid-template-columns:1.6fr 1fr;gap:18px;align-items:start;}
.card{background:#fff;border:1px solid #e6eef9;border-radius:16px;padding:18px;box-shadow:0 10px 28px rgba(2,8,23,0.04);}
.card form{display:flex;flex-direction:column;gap:22px;}

label{display:block;font-size:13px;color:#41586b;margin-bottom:0;font-weight:800;}
.help{margin-top:6px;color:#64748b;font-size:12px;font-weight:700;line-height:1.35;}

.grid3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:22px;row-gap:10px;padding-bottom:6px;margin-bottom:0;}
.grid3 label{margin-bottom:0;}

input,textarea{width:100%;padding:10px 10px;border-radius:10px;border:1px solid #e6eef9;background:#fff;outline:none;font-weight:700;color:#0f172a;display:block;margin-top:6px;}
textarea{min-height:160px;resize:vertical;}
input:focus,textarea:focus{border-color:#93c5fd;box-shadow:0 0 0 4px rgba(59,130,246,0.15);}

.card *,
.card *::before,
.card *::after{box-sizing:border-box;}

input,textarea{max-width:100%;}

.uploader{display:flex;align-items:center;gap:12px;border:1px dashed #cbd5e1;background:linear-gradient(180deg,#ffffff,#fbfdff);border-radius:16px;padding:14px;max-width:100%;overflow:hidden;}
.file{position:absolute;opacity:0;pointer-events:none;}
.upload-btn{background:#0d6efd;color:#fff;border:0;border-radius:12px;padding:10px 12px;font-weight:900;cursor:pointer;font-size:13px;white-space:nowrap;}
.upload-btn:disabled{opacity:.55;cursor:not-allowed;}
.upload-meta{min-width:0;flex:1;}
.upload-name{font-weight:950;color:#0f172a;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.clear-btn{background:#fee2e2;color:#991b1b;border:1px solid #fecaca;border-radius:12px;padding:10px 12px;font-weight:900;cursor:pointer;font-size:13px;white-space:nowrap;}
.clear-btn:disabled{opacity:.55;cursor:not-allowed;}

.form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:4px;padding-top:14px;border-top:1px solid #eef2f7;}

.btn{background:#0d6efd;color:#fff;border:0;border-radius:10px;padding:10px 12px;font-weight:900;cursor:pointer;font-size:13px;}
.btn:disabled{opacity:.55;cursor:not-allowed;}
.btn.btn-light{background:#eff6ff;color:#0d6efd;border:1px solid #dbeafe;}

.preview{padding:18px;}
.preview-title{font-weight:950;color:#0f172a;margin-bottom:10px;}
.preview-media{border:1px solid #eef2f7;border-radius:16px;overflow:hidden;background:#f8fafc;height:220px;display:flex;align-items:center;justify-content:center;}
.preview-media img{width:100%;height:100%;object-fit:cover;display:block;}
.preview-empty{color:#64748b;font-weight:800;font-size:13px;}

.preview-body{margin-top:12px;}
.preview-name{font-weight:1000;color:#0f172a;font-size:16px;}
.preview-sub{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px;}
.pill{border-radius:999px;padding:6px 10px;background:#06b6d4;color:#fff;font-weight:900;font-size:12px;}
.pill-muted{background:#f1f5f9;color:#0f172a;border:1px solid #e2e8f0;}
.preview-desc{margin-top:10px;color:#475569;font-size:13px;line-height:1.45;white-space:pre-wrap;}

@media(max-width:1000px){.layout{grid-template-columns:1fr;gap:14px;}.grid3{grid-template-columns:1fr;}.head{flex-direction:column;align-items:flex-start;}.head-actions{justify-content:flex-start;}}
</style>
