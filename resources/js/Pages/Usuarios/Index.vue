<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

const props = defineProps({ usuarios: Array, roles: Array });

const form = useForm({ editId: null, name: '', email: '', password: '', role: 'operario' });

function guardar() {
    if (form.editId) {
        form.put(`/usuarios/${form.editId}`, { onSuccess: () => form.reset('name', 'email', 'password').defaults('editId', null) });
    } else {
        form.post('/usuarios', { onSuccess: () => form.reset() });
    }
}
function editar(u) {
    form.editId = u.id; form.name = u.name; form.email = u.email; form.role = u.role; form.password = '';
}
function cancelar() { form.reset(); form.editId = null; }
function eliminar(id) {
    if (confirm('¿Eliminar este usuario?')) router.delete(`/usuarios/${id}`);
}
</script>

<template>
    <AppLayout>
        <h1 class="text-lg font-semibold mb-4">Gestión de usuarios</h1>

        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h2 class="text-sm font-semibold mb-2">{{ form.editId ? 'Editar usuario' : 'Nuevo usuario' }}</h2>
            <form @submit.prevent="guardar" class="grid grid-cols-2 md:grid-cols-5 gap-2 items-start">
                <div>
                    <input v-model="form.name" placeholder="Nombre" class="w-full rounded border-slate-300 text-sm" />
                    <p v-if="form.errors.name" class="text-xs text-red-600">{{ form.errors.name }}</p>
                </div>
                <div>
                    <input v-model="form.email" type="email" placeholder="Correo" class="w-full rounded border-slate-300 text-sm" />
                    <p v-if="form.errors.email" class="text-xs text-red-600">{{ form.errors.email }}</p>
                </div>
                <div>
                    <input v-model="form.password" type="password" :placeholder="form.editId ? 'Nueva contraseña (opcional)' : 'Contraseña'" class="w-full rounded border-slate-300 text-sm" />
                    <p v-if="form.errors.password" class="text-xs text-red-600">{{ form.errors.password }}</p>
                </div>
                <select v-model="form.role" class="rounded border-slate-300 text-sm">
                    <option v-for="r in roles" :key="r" :value="r">{{ r }}</option>
                </select>
                <div class="flex gap-1">
                    <button class="rounded bg-indigo-600 text-white px-3 py-1.5 text-sm">{{ form.editId ? 'Actualizar' : 'Crear' }}</button>
                    <button v-if="form.editId" type="button" @click="cancelar" class="rounded border px-3 py-1.5 text-sm">Cancelar</button>
                </div>
            </form>
        </div>

        <table class="w-full text-sm border bg-white">
            <thead class="bg-slate-100"><tr><th class="p-2 border text-left">Nombre</th><th class="p-2 border text-left">Correo</th><th class="p-2 border">Rol</th><th class="p-2 border">Acciones</th></tr></thead>
            <tbody>
                <tr v-for="u in usuarios" :key="u.id">
                    <td class="p-2 border">{{ u.name }}</td>
                    <td class="p-2 border">{{ u.email }}</td>
                    <td class="p-2 border text-center">{{ u.role }}</td>
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