<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <div class="min-h-screen flex items-center justify-center bg-slate-100 px-4">
        <div class="w-full max-w-sm bg-white rounded-xl shadow p-6">
            <h1 class="text-xl font-semibold text-slate-800 mb-4 text-center">Iniciar sesión</h1>

            <div v-if="form.errors.email" class="mb-3 rounded bg-red-50 text-red-700 text-sm px-3 py-2">
                {{ form.errors.email }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Correo</label>
                    <input v-model="form.email" type="email" required autofocus
                        class="w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña</label>
                    <input v-model="form.password" type="password" required
                        class="w-full rounded-md border-slate-300 focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input v-model="form.remember" type="checkbox" class="rounded border-slate-300" />
                    Recordarme
                </label>
                <button type="submit" :disabled="form.processing"
                    class="w-full rounded-md bg-indigo-600 text-white py-2 font-medium hover:bg-indigo-700 disabled:opacity-50">
                    Entrar
                </button>
            </form>
        </div>
    </div>
</template>