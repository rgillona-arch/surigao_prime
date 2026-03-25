<template>
  <div>
    <h2>Create Package</h2>

    <div class="card">
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

        <button type="submit" :disabled="saving">{{ saving ? 'Saving...' : 'Save' }}</button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const saving = ref(false);

const form = reactive({
  title: '',
  description: '',
  price: 0,
  slots_per_day: 0,
  image_url: '',
});

async function submit() {
  saving.value = true;
  await window.axios.post('/api/admin/packages', form);
  saving.value = false;
  await router.push({ name: 'admin.packages.index' });
}
</script>

<style scoped>
.card{background:#fff;border:1px solid #e6eef9;border-radius:12px;padding:14px;}
.grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;}
label{display:block;font-size:13px;color:#41586b;margin-bottom:10px;}
input,textarea{width:100%;padding:8px;border-radius:8px;border:1px solid #e6eef9;}
button{background:#0d6efd;color:#fff;border:0;border-radius:10px;padding:9px 12px;font-weight:700;cursor:pointer;}
@media(max-width:900px){.grid{grid-template-columns:1fr;}}
</style>
