<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth?.user);
const permissions = computed(() => user.value?.permissions ?? []);
const puede = (permiso) => permissions.value.includes(permiso);

const menuAbierto = ref(false);

const cerrarSesion = () => router.post('/logout');
</script>

<template>
    <div class="min-h-screen bg-slate-50">
        <nav v-if="user" class="bg-white border-b">
            <div class="max-w-7xl mx-auto px-3 sm:px-6">
                <div class="flex justify-between items-center h-14">
                    <span class="font-semibold text-slate-800">Sistema de Cortes</span>

                    <button class="sm:hidden p-2" @click="menuAbierto = !menuAbierto">☰</button>

                    <div class="hidden sm:flex items-center gap-4">
                        <Link v-if="puede('cortes.crear') || puede('cortes.ver-propios') || puede('cortes.ver-todos')"
                            href="/cortes" class="text-sm text-slate-600 hover:text-indigo-600">Cortes</Link>
                        <Link v-if="puede('historial.ver')" href="/historial"
                            class="text-sm text-slate-600 hover:text-indigo-600">Historial</Link>
                        <Link v-if="puede('informes.ver')" href="/informes"
                            class="text-sm text-slate-600 hover:text-indigo-600">Informes</Link>
                        <Link v-if="puede('catalogos.gestionar')" href="/operarios"
                            class="text-sm text-slate-600 hover:text-indigo-600">Operarios</Link>
                        <Link v-if="puede('catalogos.gestionar')" href="/tipos-papel"
                            class="text-sm text-slate-600 hover:text-indigo-600">Tipos de papel</Link>
                        <Link v-if="puede('usuarios.gestionar')" href="/usuarios"
                            class="text-sm text-slate-600 hover:text-indigo-600">Usuarios</Link>

                        <span class="text-xs text-slate-400">{{ user.name }} ({{ user.roles?.join(', ') }})</span>
                        <button @click="cerrarSesion" class="text-sm text-red-600 hover:underline">Salir</button>
                    </div>
                </div>

                <div v-if="menuAbierto" class="sm:hidden pb-3 flex flex-col gap-2">
                    <Link href="/cortes" class="text-sm text-slate-600">Cortes</Link>
                    <Link href="/historial" class="text-sm text-slate-600">Historial</Link>
                    <Link href="/informes" class="text-sm text-slate-600">Informes</Link>
                    <Link href="/operarios" class="text-sm text-slate-600">Operarios</Link>
                    <Link href="/tipos-papel" class="text-sm text-slate-600">Tipos de papel</Link>
                    <Link href="/usuarios" class="text-sm text-slate-600">Usuarios</Link>
                    <button @click="cerrarSesion" class="text-sm text-red-600 text-left">Salir</button>
                </div>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto px-3 sm:px-6 py-4">
            <div v-if="$page.props.flash.message" class="mb-3 rounded bg-green-50 text-green-700 text-sm px-3 py-2">
                {{ $page.props.flash.message }}
            </div>
            <div v-if="$page.props.flash.error" class="mb-3 rounded bg-amber-50 text-amber-700 text-sm px-3 py-2">
                {{ $page.props.flash.error }}
            </div>
            <slot />
        </main>
    </div>
</template>