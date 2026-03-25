import { reactive } from 'vue';

export const auth = reactive({
    ready: false,
    loggedIn: false,
    role: null,
    name: null,
    email: null,

    async refresh() {
        try {
            const res = await window.axios.get('/api/me');
            this.loggedIn = true;
            this.role = res.data.role;
            this.name = res.data.name;
            this.email = res.data.email;
        } catch {
            this.loggedIn = false;
            this.role = null;
            this.name = null;
            this.email = null;
        } finally {
            this.ready = true;
        }
    },

    async login(payload) {
        const res = await window.axios.post('/api/login', payload);
        this.loggedIn = true;
        this.role = res.data.role;
        this.name = res.data.name;
        this.email = res.data.email;
        this.ready = true;
    },

    async register(payload) {
        const res = await window.axios.post('/api/register', payload);
        this.loggedIn = true;
        this.role = res.data.role;
        this.name = res.data.name;
        this.email = res.data.email;
        this.ready = true;
    },

    async logout() {
        await window.axios.post('/api/logout');
        this.loggedIn = false;
        this.role = null;
        this.name = null;
        this.email = null;
        this.ready = true;
    },
});
