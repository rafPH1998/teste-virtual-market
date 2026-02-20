<template>
  <div class="flex min-h-screen bg-gray-50">
    <!-- Sidebar -->
    <aside class="fixed top-0 left-0 h-full w-60 bg-gradient-to-b from-indigo-950 to-indigo-900 text-white flex flex-col z-50 shadow-xl">
      <div class="flex items-center gap-3 px-5 py-6 border-b border-white/10">
        <div class="bg-indigo-500 rounded-xl p-2">
          <Building2 class="w-5 h-5 text-white" />
        </div>
        <div>
          <div class="font-bold text-sm leading-tight">Gestão Comercial</div>
          <div class="text-indigo-300 text-xs">Sistema Interno</div>
        </div>
      </div>

      <nav class="flex flex-col gap-1 p-3 flex-1">
        <router-link
          to="/fornecedores"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-indigo-200 hover:bg-white/10 hover:text-white transition"
          active-class="bg-white/15 text-white">
          <Factory class="w-4 h-4" /> Fornecedores
        </router-link>
        <router-link
          to="/produtos"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-indigo-200 hover:bg-white/10 hover:text-white transition"
          active-class="bg-white/15 text-white">
          <Package class="w-4 h-4" /> Produtos
        </router-link>
        <router-link
          to="/pedidos"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium text-indigo-200 hover:bg-white/10 hover:text-white transition"
          active-class="bg-white/15 text-white">
          <ClipboardList class="w-4 h-4" /> Pedidos
        </router-link>
      </nav>

      <div class="px-5 py-4 border-t border-white/10 text-xs text-indigo-400">
        v1.0.0 · Time Comercial
      </div>
    </aside>

    <!-- Main -->
    <main class="ml-60 flex-1 p-8 min-h-screen">
      <router-view />
    </main>
  </div>
</template>

<script setup>
import { Building2, Factory, Package, ClipboardList } from 'lucide-vue-next'
</script>