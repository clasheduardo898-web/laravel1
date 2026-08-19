<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({ cortes: Object, puedeEditar: Boolean, puedeEliminar: Boolean, esAdmin: Boolean });

const seleccionados = ref([]);

function seleccionarTodos() {
    seleccionados.value = props.cortes.data.map((c) => c.id);
}
function limpiarSeleccion() {
    seleccionados.value = [];
}
function exportarSeleccionados() {
    if (!seleccionados.value.length) return;
    window.location.href = `/historial/exportar?ids=${seleccionados.value.join(',')}`;
}
function exportarTodo() {
    window.location.href = '/historial/exportar';
}
function verificar(id) {
    router.post(`/historial/${id}/verificar`);
}
function revertir(id) {
    if (confirm('¿Revertir la verificación de este corte?')) router.post(`/historial/${id}/revertir`);
}
function eliminar(id) {
    if (confirm('¿Eliminar este corte completo?')) router.delete(`/historial/${id}`);
}
function sumaRollos(corte, campo) {
    return corte.numeros_corte.flatMap((nc) => nc.rollos_cortados).reduce((a, r) => a + parseFloat(r[campo]), 0);
}
function pesoNetoTotal(corte) {
    return corte.numeros_corte.flatMap((nc) => nc.rollos_cortados).reduce((a, r) => a + parseFloat(r.peso_kg), 0);
}
</script>

<template>
    <AppLayout>
        <div class="flex justify-between items-center mb-4 flex-wrap gap-2">
            <h1 class="text-lg font-semibold">Historial de cortes</h1>
            <div class="flex gap-2 items-center flex-wrap text-sm">
                <span class="text-slate-500">{{ seleccionados.length }} seleccionado(s)</span>
                <button @click="seleccionarTodos" class="rounded border px-2 py-1 hover:bg-slate-100">Seleccionar todos</button>
                <button @click="limpiarSeleccion" class="rounded border px-2 py-1 hover:bg-slate-100">Limpiar</button>
                <button @click="exportarSeleccionados" class="rounded bg-green-600 text-white px-2 py-1">Exportar seleccionados</button>
                <button @click="exportarTodo" class="rounded border border-green-600 text-green-700 px-2 py-1">Exportar todo</button>
            </div>
        </div>

        <div v-for="corte in cortes.data" :key="corte.id" class="bg-white rounded-lg shadow mb-3">
            <div class="p-3 border-b">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
                    <div class="flex items-center gap-2 text-sm">
                        <input type="checkbox" :value="corte.id" v-model="seleccionados" />
                        <span>
                            <strong>{{ corte.fecha }}</strong> — {{ corte.operario }} — {{ corte.tipo_papel }} —
                            Master: {{ corte.rollo_largo_mm }}mm / {{ corte.rollo_peso_kg }}kg — Merma: {{ corte.merma_kg }}kg
                        </span>
                    </div>
                    <div class="flex gap-2 flex-wrap text-sm">
                        <a :href="`/cortes/${corte.id}/imprimir`" target="_blank" class="rounded bg-emerald-600 text-white px-2 py-1">Imprimir PDF</a>
                        <Link v-if="puedeEditar" :href="`/cortes?editar=${corte.id}`" class="rounded bg-amber-500 text-white px-2 py-1">Modificar</Link>
                        <button v-if="puedeEliminar" @click="eliminar(corte.id)" class="rounded bg-red-600 text-white px-2 py-1">Eliminar</button>
                    </div>
                </div>
            </div>
            <div class="p-3">
                <div v-for="nc in corte.numeros_corte" :key="nc.id" class="mb-3">
                    <h3 class="text-sm font-semibold mb-1">
                        <div v-if="nc.verificado_por" class="text-xs bg-sky-50 text-sky-700 rounded px-2 py-1 mb-1 inline-block">
                            ✔ Verificado por {{ nc.verificador?.name }}
                        </div>
                        Número de corte: {{ nc.numero }}
                        (core: {{ nc.core_lb }} lb, unidad: {{ nc.unidad_ancho === 'pulgada' ? 'pulgadas' : 'mm' }})
                    </h3>
                    <table class="w-full text-xs border">
                        <thead class="bg-slate-100">
                            <tr>
                                <th class="p-1 border">Ancho</th><th class="p-1 border">Bruto (lb)</th>
                                <th class="p-1 border">Core (lb)</th><th class="p-1 border">Neto (lb)</th><th class="p-1 border">Kg</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="rc in nc.rollos_cortados" :key="rc.id">
                                <td class="p-1 border">{{ rc.ancho_mm }}</td>
                                <td class="p-1 border">{{ rc.peso_lb }}</td>
                                <td class="p-1 border">{{ rc.core_lb }}</td>
                                <td class="p-1 border">{{ rc.peso_neto_lb }}</td>
                                <td class="p-1 border">{{ rc.peso_kg }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-right text-sm font-semibold">Peso neto total: {{ pesoNetoTotal(corte).toFixed(3) }} kg</div>
            </div>
        </div>

        <div class="flex gap-1 flex-wrap">
            <Link v-for="link in cortes.links" :key="link.label" :href="link.url ?? ''"
                v-html="link.label"
                class="px-2 py-1 text-sm rounded border"
                :class="{ 'bg-indigo-600 text-white': link.active, 'text-slate-300 pointer-events-none': !link.url }" />
        </div>
    </AppLayout>
</template>