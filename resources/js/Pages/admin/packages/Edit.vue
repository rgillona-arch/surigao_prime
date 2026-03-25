<template>
  <div>
    <h2>Edit Package</h2>

    <div v-if="loading">Loading...</div>

    <div v-else class="card">
      <form @submit.prevent="submit">
        <div class="grid"> 
          <label>
            Title
            <input v-model="form.title" required />
          </label>
          <label>
            Price
            <input v-model.number="form.price" type="number" min="0" required />
          </label>
          <label>
            Slots/Day
            <input v-model.number="form.slots_per_day" type="number" min="0" required />
          </label>
        </div>

        <label>
          Image URL
          <input v-model="form.image_url" />
        </label>

        <label>
          Description
          <textarea v-model="form.description" rows="4" />
        </label>

        <button type="submit" :disabled="saving">{{ saving ? 'Saving...' : 'Update' }}</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();

const loading = ref(true);
const saving = ref(false);

const form = reactive({
  title: '',
  description: '',
  price: 0,
  slots_per_day: 0,
  image_url: '',
});

async function load() {
  loading.value = true;
  const res = await window.axios.get(`/api/admin/packages/${route.params.id}`);
  Object.assign(form, res.data.package);
  loading.value = false;
}

async function submit() {
  saving.value = true;
  await window.axios.put(`/api/admin/packages/${route.params.id}`, form);
  saving.value = false;
  await router.push({ name: 'admin.packages.index' });
}

onMounted(load);
</script>

<style scoped>
.card{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:14px;}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
label{display:block;font-size:13px;color:#41586b;margin-bottom:10px;}
input,textarea{width:100%;padding:8px;border-radius:8px;border:1px solid #e6eef9;}
button{background:#0d6efd;color:#fff;border:0;border-radius:10px;padding:9px 12px;font-weight:700;cursor:pointer;}
@media(max-width:900px){.grid{grid-template-columns:1fr;}}
</style>
