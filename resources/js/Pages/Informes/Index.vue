<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    filtros: Object, tiposPapel: Array, operariosDisponibles: Array,
    resumenTipo: Array, resumenAncho: Array, totales: Object,
});

const filtros = reactive({
    fecha_desde: props.filtros.fecha_desde ?? '',
    fecha_hasta: props.filtros.fecha_hasta ?? '',
    operario: props.filtros.operario ?? '',
    tipo_papel_id: props.filtros.tipo_papel_id ?? '',
    largo_master_id: props.filtros.largo_master_id ?? '',
});

const largosDisponibles = reactive({ items: [] });

async function cargarLargos() {
    if (!filtros.tipo_papel_id) { largosDisponibles.items = []; return; }
    const { data } = await axios.get(`/tipos-papel/${filtros.tipo_papel_id}/largos-json`);
    largosDisponibles.items = data;
}

function aplicarFiltros() {
    router.get('/informes', filtros, { preserveState: true, replace: true });
}

watch(() => filtros.tipo_papel_id, () => { filtros.largo_master_id = ''; cargarLargos(); aplicarFiltros(); });
watch(() => [filtros.fecha_desde, filtros.fecha_hasta, filtros.operario, filtros.largo_master_id], aplicarFiltros);

if (filtros.tipo_papel_id) cargarLargos();

function limpiarFiltros() {
    Object.assign(filtros, { fecha_desde: '', fecha_hasta: '', operario: '', tipo_papel_id: '', largo_master_id: '' });
    aplicarFiltros();
}

function exportar() {
    const params = new URLSearchParams(filtros).toString();
    window.location.href = `/informes/exportar?${params}`;
}

function exportarPdf() {
    const params = new URLSearchParams(filtros).toString();
    window.location.href = `/informes/pdf?${params}`;
}

</script>

<template>
    <AppLayout>
        <h1 class="text-lg font-semibold mb-4">Informes de cortes</h1>

        <div class="bg-white rounded-lg shadow p-4 mb-4">
            <h2 class="text-sm font-semibold mb-2">Filtros</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 mb-3">
                <div>
                    <label class="text-xs text-slate-500">Fecha desde</label>
                    <input type="date" v-model="filtros.fecha_desde" class="w-full rounded border-slate-300 text-sm" />
                </div>
                <div>
                    <label class="text-xs text-slate-500">Fecha hasta</label>
                    <input type="date" v-model="filtros.fecha_hasta" class="w-full rounded border-slate-300 text-sm" />
                </div>
                <div>
                    <label class="text-xs text-slate-500">Operario</label>
                    <select v-model="filtros.operario" class="w-full rounded border-slate-300 text-sm">
                        <option value="">-- Todos --</option>
                        <option v-for="op in operariosDisponibles" :key="op.id" :value="op.nombre">{{ op.nombre }}</option>
                    </select>
                </div>
                <div>
                    <label class="text-xs text-slate-500">Tipo de papel</label>
                    <select v-model="filtros.tipo_papel_id" class="w-full rounded border-slate-300 text-sm">
                        <option value="">-- Todos --</option>
                        <option v-for="tp in tiposPapel" :key="tp.id" :value="tp.id">{{ tp.nombre }}</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2">
                <button @click="limpiarFiltros" class="rounded border px-3 py-1.5 text-sm hover:bg-slate-100">Limpiar filtros</button>
                <button @click="exportar" class="rounded bg-green-600 text-white px-3 py-1.5 text-sm">Exportar a Excel</button>
                <button @click="exportarPdf" class="rounded bg-red-600 text-white px-3 py-1.5 text-sm">Exportar a PDF</button>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div class="p-3 rounded bg-slate-100"><p class="text-xs text-slate-500">Cantidad de masters</p><p class="text-lg font-bold">{{ totales.masters }}</p></div>
            <div class="p-3 rounded bg-slate-100"><p class="text-xs text-slate-500">Peso total masters</p><p class="text-lg font-bold">{{ totales.pesoMasterKg }} kg</p></div>
            <div class="p-3 rounded bg-amber-100"><p class="text-xs text-slate-500">Desperdicio total</p><p class="text-lg font-bold">{{ totales.mermaKg }} kg</p></div>
            <div class="p-3 rounded bg-slate-100">
                <p class="text-xs text-slate-500">Peso neto cortado</p>
                <p class="text-lg font-bold">{{ totales.netoKg }} kg</p>
                <p class="text-xs text-slate-400">≈ {{ totales.netoLb }} lb / {{ totales.rollos }} rollos</p>
            </div>
        </div>

        <h2 class="text-sm font-semibold mb-2">Totales por tipo de papel y largo de master</h2>
        <table class="w-full text-xs border mb-6 bg-white">
            <thead class="bg-slate-100"><tr>
                <th class="p-1 border">Tipo de papel</th><th class="p-1 border">Largo (mm)</th>
                <th class="p-1 border">Cantidad</th><th class="p-1 border">Peso total (kg)</th>
                <th class="p-1 border">Peso neto cortado (kg)</th><th class="p-1 border">Merma (kg)</th>
            </tr></thead>
            <tbody>
                <tr v-for="(f, i) in resumenTipo" :key="i">
                    <td class="p-1 border">{{ f.tipo_papel }}</td><td class="p-1 border">{{ f.largo_mm }}</td>
                    <td class="p-1 border">{{ f.cantidad }}</td><td class="p-1 border">{{ f.peso_total_kg }}</td>
                    <td class="p-1 border">{{ f.peso_neto_kg }}</td><td class="p-1 border">{{ f.merma_total_kg }}</td>
                </tr>
                <tr v-if="!resumenTipo.length"><td colspan="6" class="p-2 text-center text-slate-400">Sin datos para los filtros seleccionados.</td></tr>
            </tbody>
        </table>

        <h2 class="text-sm font-semibold mb-2">Totales por tipo de papel y ancho de rollo cortado</h2>
        <table class="w-full text-xs border bg-white">
            <thead class="bg-slate-100"><tr>
                <th class="p-1 border">Tipo de papel</th><th class="p-1 border">Ancho (mm)</th>
                <th class="p-1 border">Cantidad</th><th class="p-1 border">Neto (lb)</th><th class="p-1 border">Neto (kg)</th>
            </tr></thead>
            <tbody>
                <tr v-for="(f, i) in resumenAncho" :key="i">
                    <td class="p-1 border">{{ f.tipo_papel }}</td><td class="p-1 border">{{ f.ancho_mm }}</td>
                    <td class="p-1 border">{{ f.cantidad }}</td>
                    <td class="p-1 border">{{ f.peso_neto_lb }}</td><td class="p-1 border">{{ f.peso_neto_kg }}</td>
                </tr>
                <tr v-if="!resumenAncho.length"><td colspan="5" class="p-2 text-center text-slate-400">Sin datos para los filtros seleccionados.</td></tr>
            </tbody>
        </table>
    </AppLayout>
</template>