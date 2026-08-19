<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({ usuarios: Array, roles: Array });

const form = useForm({ editId: null, name: '', email: '', password: '', roles: [] });

function guardar() {
    if (form.editId) {
        form.put(`/usuarios/${form.editId}`, { onSuccess: () => cancelar() });
    } else {
        form.post('/usuarios', { onSuccess: () => cancelar() });
    }
}
function editar(u) {
    form.editId = u.id; form.name = u.name; form.email = u.email; form.roles = [...u.roles]; form.password = '';
}
function cancelar() {
    form.reset(); form.editId = null; form.roles = [];
}
function eliminar(id) {
    if (confirm('¿Eliminar este usuario?')) router.delete(`/usuarios/${id}`);
}
</script>

<template>
    <AppLayout>
        <h1 class="text-lg font-semibold mb-4">Gestión de usuarios</h1>

        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h2 class="text-sm font-semibold mb-2">{{ form.editId ? 'Editar usuario' : 'Nuevo usuario' }}</h2>
            <form @submit.prevent="guardar" class="grid grid-cols-2 md:grid-cols-4 gap-3 items-start">
                <div>
                    <input v-model="form.name" placeholder="Nombre" class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm" />
                    <p v-if="form.errors.name" class="text-xs text-red-600">{{ form.errors.name }}</p>
                </div>
                <div>
                    <input v-model="form.email" type="email" placeholder="Correo" class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm" />
                    <p v-if="form.errors.email" class="text-xs text-red-600">{{ form.errors.email }}</p>
                </div>
                <div>
                    <input v-model="form.password" type="password" :placeholder="form.editId ? 'Nueva contraseña (opcional)' : 'Contraseña'" class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm" />
                    <p v-if="form.errors.password" class="text-xs text-red-600">{{ form.errors.password }}</p>
                </div>
                <div>
                    <label class="text-xs text-slate-500 block mb-1">Roles (puede seleccionar varios)</label>
                    <div class="flex flex-col gap-1">
                        <label v-for="r in roles" :key="r" class="flex items-center gap-1.5 text-sm">
                            <input type="checkbox" :value="r" v-model="form.roles" class="rounded border-slate-300" />
                            {{ r }}
                        </label>
                    </div>
                    <p v-if="form.errors.roles" class="text-xs text-red-600">{{ form.errors.roles }}</p>
                </div>
                <div class="flex gap-1 col-span-2 md:col-span-4">
                    <button class="rounded bg-indigo-600 text-white px-3 py-1.5 text-sm">{{ form.editId ? 'Actualizar' : 'Crear' }}</button>
                    <button v-if="form.editId" type="button" @click="cancelar" class="rounded border px-3 py-1.5 text-sm">Cancelar</button>
                </div>
            </form>
        </div>

        <table class="w-full text-sm border bg-white">
            <thead class="bg-slate-100"><tr><th class="p-2 border text-left">Nombre</th><th class="p-2 border text-left">Correo</th><th class="p-2 border">Roles</th><th class="p-2 border">Acciones</th></tr></thead>
            <tbody>
                <tr v-for="u in usuarios" :key="u.id">
                    <td class="p-2 border">{{ u.name }}</td>
                    <td class="p-2 border">{{ u.email }}</td>
                    <td class="p-2 border text-center">{{ u.roles.join(', ') }}</td>
                    <td class="p-2 border">
                        <div class="flex gap-2 justify-center">
                            <button @click="editar(u)" class="text-xs rounded bg-amber-500 text-white px-2 py-1">Editar</button>
                            <button @click="eliminar(u.id)" class="text-xs rounded bg-red-600 text-white px-2 py-1">Eliminar</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </AppLayout>
</template>