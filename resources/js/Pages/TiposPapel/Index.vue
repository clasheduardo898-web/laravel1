<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps({ tiposPapel: Array });

const formTipo = useForm({ nombre: '' });
function crearTipo() {
    formTipo.post('/tipos-papel', { onSuccess: () => formTipo.reset() });
}

const nuevoLargo = reactive({});

   watch(
       () => props.tiposPapel,
       (tipos) => {
           tipos.forEach((tp) => {
               if (!nuevoLargo[tp.id]) {
                   nuevoLargo[tp.id] = { valor: '', unidad_medida: 'mm' };
               }
           });
       },
       { immediate: true, deep: true }
   );

function agregarLargo(tipoPapelId) {
    router.post(`/tipos-papel/${tipoPapelId}/largos`, nuevoLargo[tipoPapelId], {
        onSuccess: () => { nuevoLargo[tipoPapelId] = { valor: '', unidad_medida: 'mm' }; },
    });
}

const editando = reactive({ id: null, valor: '', unidad_medida: 'mm' });
function editarLargo(lm) {
    editando.id = lm.id; editando.valor = lm.valor_original; editando.unidad_medida = lm.unidad_medida;
}
function actualizarLargo() {
    router.put(`/largos-master/${editando.id}`, { valor: editando.valor, unidad_medida: editando.unidad_medida }, {
        onSuccess: () => { editando.id = null; },
    });
}
function cancelarLargo() { editando.id = null; }
function alternarLargo(id) { router.post(`/largos-master/${id}/alternar`); }
function eliminarLargo(id) {
    if (confirm('¿Eliminar este largo?')) router.delete(`/largos-master/${id}`);
}

const editandoTipo = reactive({ id: null, nombre: '' });
function editarTipo(tp) {
    editandoTipo.id = tp.id;
    editandoTipo.nombre = tp.nombre;
}
function actualizarTipo() {
    router.put(`/tipos-papel/${editandoTipo.id}`, { nombre: editandoTipo.nombre }, {
        onSuccess: () => { editandoTipo.id = null; },
    });
}
function cancelarTipo() {
    editandoTipo.id = null;
}
function alternarTipo(id) {
    router.post(`/tipos-papel/${id}/alternar`);
}
function eliminarTipo(id) {
    if (confirm('¿Eliminar este tipo de papel? También se eliminarán sus largos asociados.')) {
        router.delete(`/tipos-papel/${id}`);
    }
}

</script>

<template>
    <AppLayout>
        <h1 class="text-lg font-semibold mb-4">Tipos de papel y largos de master</h1>

        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h2 class="text-sm font-semibold mb-2">Nuevo tipo de papel</h2>
            <form @submit.prevent="crearTipo" class="flex gap-2">
                <input v-model="formTipo.nombre" placeholder="Nombre del tipo de papel" class="rounded border-slate-300 text-sm" />
                <button class="rounded bg-indigo-600 text-white px-3 py-1.5 text-sm">Agregar</button>
            </form>
        </div>

        <div v-for="tp in tiposPapel" :key="tp.id" class="bg-white rounded-lg shadow mb-4">
            <div class="p-3 border-b flex justify-between items-center flex-wrap gap-2">
                <template v-if="editandoTipo.id === tp.id">
                    <div class="flex gap-2 items-center flex-wrap">
                        <input v-model="editandoTipo.nombre" class="rounded border border-slate-300 text-sm px-2 py-1" />
                        <button @click="actualizarTipo" class="rounded bg-indigo-600 text-white px-2 py-1 text-xs">Guardar</button>
                        <button @click="cancelarTipo" class="rounded border px-2 py-1 text-xs">Cancelar</button>
                    </div>
                </template>
                <template v-else>
                    <span class="font-semibold text-sm">
                        {{ tp.nombre }}
                        <span v-if="!tp.activo" class="text-xs text-slate-400 font-normal">(inactivo)</span>
                    </span>
                    <div class="flex gap-1 flex-wrap">
                        <button @click="alternarTipo(tp.id)" class="text-xs rounded border px-2 py-1">{{ tp.activo ? 'Desactivar' : 'Activar' }}</button>
                        <button @click="editarTipo(tp)" class="text-xs rounded bg-amber-500 text-white px-2 py-1">Editar</button>
                        <button @click="eliminarTipo(tp.id)" class="text-xs rounded bg-red-600 text-white px-2 py-1">Eliminar</button>
                    </div>
                </template>
            </div>
            <div class="p-3">
                <table class="w-full text-xs border mb-2">
                    <thead class="bg-slate-100"><tr><th class="p-1 border">Valor ingresado</th><th class="p-1 border">Equivalente (mm)</th><th class="p-1 border">Estado</th><th class="p-1 border">Acción</th></tr></thead>
                    <tbody>
                        <tr v-for="lm in tp.largos" :key="lm.id">
                            <template v-if="editando.id === lm.id">
                                <td colspan="4" class="p-1 border">
                                    <div class="flex gap-2 items-center flex-wrap">
                                        <input type="number" step="0.01" v-model="editando.valor" class="rounded border-slate-300 text-xs w-28" />
                                        <select v-model="editando.unidad_medida" class="rounded border-slate-300 text-xs">
                                            <option value="mm">mm</option><option value="pulgada">pulgadas</option>
                                        </select>
                                        <button @click="actualizarLargo" class="rounded bg-indigo-600 text-white px-2 py-1 text-xs">Guardar</button>
                                        <button @click="cancelarLargo" class="rounded border px-2 py-1 text-xs">Cancelar</button>
                                    </div>
                                </td>
                            </template>
                            <template v-else>
                                <td class="p-1 border">{{ lm.valor_original }} {{ lm.unidad_medida === 'pulgada' ? 'pulg' : 'mm' }}</td>
                                <td class="p-1 border">{{ lm.largo_mm }} mm</td>
                                <td class="p-1 border">{{ lm.activo ? 'Activo' : 'Inactivo' }}</td>
                                <td class="p-1 border">
                                    <div class="flex gap-1 flex-wrap">
                                        <button @click="alternarLargo(lm.id)" class="text-xs rounded border px-2 py-1">{{ lm.activo ? 'Desactivar' : 'Activar' }}</button>
                                        <button @click="editarLargo(lm)" class="text-xs rounded bg-amber-500 text-white px-2 py-1">Editar</button>
                                        <button @click="eliminarLargo(lm.id)" class="text-xs rounded bg-red-600 text-white px-2 py-1">Eliminar</button>
                                    </div>
                                </td>
                            </template>
                        </tr>
                    </tbody>
                </table>
                <form @submit.prevent="agregarLargo(tp.id)" class="flex gap-2 flex-wrap">
                    <input type="number" step="0.01" v-model="nuevoLargo[tp.id].valor" placeholder="Ej. 1219 o 48" class="rounded border-slate-300 text-xs w-32" />
                    <select v-model="nuevoLargo[tp.id].unidad_medida" class="rounded border-slate-300 text-xs">
                        <option value="mm">mm</option><option value="pulgada">pulgadas</option>
                    </select>
                    <button class="rounded border border-indigo-400 text-indigo-600 px-2 py-1 text-xs">+ Agregar largo</button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>