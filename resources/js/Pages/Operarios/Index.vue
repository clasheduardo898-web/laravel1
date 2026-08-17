<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';

defineProps({ operarios: Array });

const form = useForm({ nombre: '', editId: null });

function guardar() {
    if (form.editId) {
        form.put(`/operarios/${form.editId}`, { onSuccess: () => form.reset() });
    } else {
        form.post('/operarios', { onSuccess: () => form.reset() });
    }
}
function editar(op) {
    form.editId = op.id;
    form.nombre = op.nombre;
}
function cancelar() {
    form.reset();
}
function alternarActivo(op) {
    router.put(`/operarios/${op.id}`, { nombre: op.nombre, activo: !op.activo });
}
function eliminar(id) {
    if (confirm('¿Eliminar este operario?')) router.delete(`/operarios/${id}`);
}
</script>

<template>
    <AppLayout>
        <h1 class="text-lg font-semibold mb-4">Gestión de operarios</h1>

        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h2 class="text-sm font-semibold mb-2">{{ form.editId ? 'Editar operario' : 'Nuevo operario' }}</h2>
            <form @submit.prevent="guardar" class="flex gap-2 flex-wrap">
                <input v-model="form.nombre" placeholder="Nombre" class="rounded border-slate-300 text-sm" />
                <button class="rounded bg-indigo-600 text-white px-3 py-1.5 text-sm">{{ form.editId ? 'Actualizar' : 'Crear' }}</button>
                <button v-if="form.editId" type="button" @click="cancelar" class="rounded border px-3 py-1.5 text-sm">Cancelar</button>
            </form>
            <p v-if="form.errors.nombre" class="text-xs text-red-600 mt-1">{{ form.errors.nombre }}</p>
        </div>

        <table class="w-full text-sm border bg-white">
            <thead class="bg-slate-100"><tr><th class="p-2 border text-left">Nombre</th><th class="p-2 border">Activo</th><th class="p-2 border">Acciones</th></tr></thead>
            <tbody>
                <tr v-for="op in operarios" :key="op.id">
                    <td class="p-2 border">{{ op.nombre }}</td>
                    <td class="p-2 border text-center">{{ op.activo ? 'Sí' : 'No' }}</td>
                    <td class="p-2 border">
                        <div class="flex gap-2 flex-wrap justify-center">
                            <button @click="alternarActivo(op)" class="text-xs rounded border px-2 py-1">{{ op.activo ? 'Desactivar' : 'Activar' }}</button>
                            <button @click="editar(op)" class="text-xs rounded bg-amber-500 text-white px-2 py-1">Editar</button>
                            <button @click="eliminar(op.id)" class="text-xs rounded bg-red-600 text-white px-2 py-1">Eliminar</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </AppLayout>
</template>