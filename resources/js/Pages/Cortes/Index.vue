<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router, Link } from '@inertiajs/vue3';
import { reactive } from 'vue';
import axios from 'axios';
import {
    coreParteLb, pesoNetoLb, pesoKg, totalAncho, totalPesoNetoKg,
    nuevoNumeroCorte, MAX_ROLLOS_POR_CORTE,
} from '@/Composables/useCorteCalculos.js';

const props = defineProps({
    tiposPapel: Array,
    operariosDisponibles: Array,
    borradores: Array,
    corteEnEdicion: Object,
});

const largosDisponibles = reactive({ items: [] });

const form = useForm(props.corteEnEdicion ? {
    fecha: props.corteEnEdicion.fecha,
    operario: props.corteEnEdicion.operario,
    tipo_papel_id: props.corteEnEdicion.tipo_papel_id,
    largo_master_id: props.corteEnEdicion.largo_master_id,
    rollo_peso: props.corteEnEdicion.rollo_peso,
    merma_kg: props.corteEnEdicion.merma_kg,
    numeros_corte: props.corteEnEdicion.numeros_corte,
    finalizar: false,
} : {
    fecha: new Date().toISOString().slice(0, 10),
    operario: '',
    tipo_papel_id: '',
    largo_master_id: '',
    rollo_peso: '',
    merma_kg: '',
    numeros_corte: [nuevoNumeroCorte(0)],
    finalizar: false,
});

if (props.corteEnEdicion?.tipo_papel_id) {
    cargarLargos(props.corteEnEdicion.tipo_papel_id);
}

async function cargarLargos(tipoPapelId) {
    if (!tipoPapelId) { largosDisponibles.items = []; return; }
    const { data } = await axios.get(`/tipos-papel/${tipoPapelId}/largos-json`);
    largosDisponibles.items = data;
}

function alCambiarTipoPapel() {
    form.largo_master_id = '';
    cargarLargos(form.tipo_papel_id);
}

function agregarNumeroCorte() {
    form.numeros_corte.push(nuevoNumeroCorte(form.numeros_corte.length));
}

function eliminarNumeroCorte(index) {
    form.numeros_corte.splice(index, 1);
    if (form.numeros_corte.length === 0) agregarNumeroCorte();
}

function agregarRollo(numIndex) {
    if (form.numeros_corte[numIndex].rollos.length >= MAX_ROLLOS_POR_CORTE) return;
    form.numeros_corte[numIndex].rollos.push({ ancho: '', peso_lb: '' });
}

function eliminarRollo(numIndex, rolloIndex) {
    form.numeros_corte[numIndex].rollos.splice(rolloIndex, 1);
    if (form.numeros_corte[numIndex].rollos.length === 0) {
        form.numeros_corte[numIndex].rollos.push({ ancho: '', peso_lb: '' });
    }
}

function guardar(finalizar) {
    form.finalizar = finalizar;
    if (props.corteEnEdicion) {
        form.transform((data) => ({ ...data, _method: 'put' })).post(`/cortes/${props.corteEnEdicion.id}`);
    } else {
        form.post('/cortes');
    }
}

function eliminarBorrador(id) {
    if (confirm('¿Eliminar este borrador?')) router.delete(`/cortes/${id}`);
}

const diferenciaAncho = () => {
    const largo = largosDisponibles.items.find((l) => l.id === form.largo_master_id);
    return Math.round(((largo?.largo_mm ?? 0) - totalAncho(form.numeros_corte)) * 100) / 100;
};

const diferenciaPesoKg = () => {
    const ajustado = (parseFloat(form.rollo_peso) || 0) - (parseFloat(form.merma_kg) || 0);
    return Math.round((ajustado - totalPesoNetoKg(form.numeros_corte)) * 1000) / 1000;
};
</script>

<template>
    <AppLayout>
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <h1 class="text-lg font-semibold mb-4">
                {{ corteEnEdicion ? 'Editar corte' : 'Registrar corte de rollo master' }}
            </h1>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                <div>
                    <label class="text-sm text-slate-600">Fecha</label>
                    <input type="date" v-model="form.fecha" class="w-full rounded border-slate-300 text-sm" />
                    <p v-if="form.errors.fecha" class="text-xs text-red-600">{{ form.errors.fecha }}</p>
                </div>
                <div>
                    <label class="text-sm text-slate-600">Operario</label>
                    <select v-model="form.operario" class="w-full rounded border-slate-300 text-sm">
                        <option value="">-- Selecciona --</option>
                        <option v-for="op in operariosDisponibles" :key="op.id" :value="op.nombre">{{ op.nombre }}</option>
                    </select>
                    <p v-if="form.errors.operario" class="text-xs text-red-600">{{ form.errors.operario }}</p>
                </div>
                <div>
                    <label class="text-sm text-slate-600">Tipo de papel</label>
                    <select v-model="form.tipo_papel_id" @change="alCambiarTipoPapel" class="w-full rounded border-slate-300 text-sm">
                        <option value="">-- Selecciona --</option>
                        <option v-for="tp in tiposPapel" :key="tp.id" :value="tp.id">{{ tp.nombre }}</option>
                    </select>
                    <p v-if="form.errors.tipo_papel_id" class="text-xs text-red-600">{{ form.errors.tipo_papel_id }}</p>
                </div>
                <div>
                    <label class="text-sm text-slate-600">Largo del master</label>
                    <select v-model="form.largo_master_id" class="w-full rounded border-slate-300 text-sm">
                        <option value="">-- Selecciona --</option>
                        <option v-for="lm in largosDisponibles.items" :key="lm.id" :value="lm.id">
                            {{ lm.valor_original }} {{ lm.unidad_medida === 'pulgada' ? 'pulg' : 'mm' }}
                        </option>
                    </select>
                    <p v-if="form.errors.largo_master_id" class="text-xs text-red-600">{{ form.errors.largo_master_id }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3 mb-4">
                <div>
                    <label class="text-sm text-slate-600">Peso del rollo master (kg)</label>
                    <input type="number" step="0.01" v-model="form.rollo_peso" class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div>
                    <label class="text-sm text-slate-600">Merma / desperdicio (kg)</label>
                    <input type="number" step="0.01" v-model="form.merma_kg" class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
            </div>

            <div v-for="(nc, numIndex) in form.numeros_corte" :key="numIndex" class="border rounded-lg p-3 mb-3 bg-slate-50">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Número de corte</label>
                        <input v-model="nc.numero" class="w-full rounded border-slate-300 text-sm" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Core (lb)</label>
                        <input type="number" step="0.001" v-model="nc.core_lb" class="w-full rounded border-slate-300 text-sm" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Unidad de ancho</label>
                        <select v-model="nc.unidad_ancho" class="w-full rounded border-slate-300 text-sm">
                            <option value="mm">mm</option>
                            <option value="pulgada">pulgadas</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="button" @click="eliminarNumeroCorte(numIndex)"
                            class="w-full rounded border border-red-300 text-red-600 text-sm py-1.5 hover:bg-red-50">Eliminar</button>
                    </div>
                </div>

                <div class="flex justify-between items-center mb-2 flex-wrap gap-2">
                    <span class="text-xs text-slate-500">Rollos en este corte: {{ nc.rollos.length }} / {{ MAX_ROLLOS_POR_CORTE }}</span>
                    <button type="button" @click="agregarRollo(numIndex)"
                        class="text-xs rounded border border-indigo-300 text-indigo-600 px-2 py-1 hover:bg-indigo-50">+ Agregar rollo</button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-xs border">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="p-1 border">Ancho (mm)</th><th class="p-1 border">Peso bruto (lb)</th>
                                <th class="p-1 border">Core (lb)</th><th class="p-1 border">Peso neto (lb)</th>
                                <th class="p-1 border">Kg</th><th class="p-1 border">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(rollo, rolloIndex) in nc.rollos" :key="rolloIndex">
                                <td class="p-1 border">
                                    <input type="number" step="0.01" v-model="rollo.ancho" class="w-full rounded border-slate-300 text-xs" />
                                </td>
                                <td class="p-1 border">
                                    <input type="number" step="0.001" v-model="rollo.peso_lb" class="w-full rounded border-slate-300 text-xs" />
                                </td>
                                <td class="p-1 border text-slate-500">{{ coreParteLb(nc, rollo.ancho).toFixed(3) }}</td>
                                <td class="p-1 border text-slate-500">{{ pesoNetoLb(nc, rollo.peso_lb, rollo.ancho).toFixed(3) }}</td>
                                <td class="p-1 border text-slate-500">{{ pesoKg(pesoNetoLb(nc, rollo.peso_lb, rollo.ancho)).toFixed(3) }}</td>
                                <td class="p-1 border text-center">
                                    <button type="button" @click="eliminarRollo(numIndex, rolloIndex)" class="text-red-600">✕</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <button type="button" @click="agregarNumeroCorte"
                class="text-sm rounded border border-slate-300 px-3 py-1.5 mb-4 hover:bg-slate-100">+ Agregar número de corte</button>

            <div class="grid grid-cols-2 gap-3 mb-4 text-sm">
                <div class="p-2 rounded bg-slate-100">Diferencia de largo: <strong>{{ diferenciaAncho() }} mm</strong></div>
                <div class="p-2 rounded bg-slate-100">Diferencia de peso: <strong>{{ diferenciaPesoKg() }} kg</strong></div>
            </div>

            <div class="flex gap-2">
                <button type="button" @click="guardar(false)" :disabled="form.processing"
                    class="rounded bg-slate-600 text-white px-4 py-2 text-sm hover:bg-slate-700 disabled:opacity-50">
                    Guardar borrador
                </button>
                <button type="button" @click="guardar(true)" :disabled="form.processing"
                    class="rounded bg-indigo-600 text-white px-4 py-2 text-sm hover:bg-indigo-700 disabled:opacity-50">
                    Finalizar corte
                </button>
            </div>
        </div>

        <div v-if="borradores.length" class="bg-white rounded-lg shadow p-4">
            <h2 class="text-sm font-semibold mb-2">Borradores</h2>
            <div v-for="b in borradores" :key="b.id" class="flex justify-between items-center text-sm border-b py-2">
                <span>{{ b.fecha }} — {{ b.operario }} — {{ b.tipo_papel }}</span>
                <div class="flex gap-2">
                    <Link :href="`/cortes?editar=${b.id}`" class="text-indigo-600 hover:underline">Continuar</Link>
                    <button @click="eliminarBorrador(b.id)" class="text-red-600 hover:underline">Eliminar</button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>