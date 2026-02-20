<template>
  <div>
    <!-- Header -->
    <div class="flex items-start justify-between mb-6">
      <div>
        <router-link to="/produtos" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium mb-2 transition">
          ← Voltar para Produtos
        </router-link>
        <h1 class="text-2xl font-bold text-gray-800">🔗 Vínculos do Produto</h1>
        <div v-if="produto" class="flex items-center gap-3 mt-2">
          <span class="text-gray-600 font-medium">{{ produto.nome }}</span>
          <span class="bg-gray-100 text-gray-500 font-mono text-xs px-2 py-0.5 rounded-md">{{ produto.codigo_interno }}</span>
          <span :class="produto.status === 'ativo' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
            class="px-2 py-0.5 rounded-full text-xs font-semibold">{{ produto.status }}</span>
        </div>
      </div>
    </div>

    <!-- Feedback -->
    <div v-if="feedback.msg" :class="[
      'flex items-center gap-2 px-4 py-3 rounded-xl mb-5 text-sm font-medium border',
      feedback.type === 'success' ? 'bg-green-50 text-green-700 border-green-200' :
      feedback.type === 'info'    ? 'bg-blue-50 text-blue-700 border-blue-200' :
                                    'bg-red-50 text-red-700 border-red-200'
    ]">
      {{ feedback.msg }}
    </div>

    <div class="grid grid-cols-2 gap-5">
      <!-- Vinculados -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <div>
            <h2 class="font-bold text-gray-800">Fornecedores Vinculados</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ vinculados.length }} vínculo(s) ativo(s)</p>
          </div>
          <button v-if="selecionadosVinculados.length"
            @click="desvincularEmMassa" :disabled="processando"
            class="flex items-center gap-1.5 bg-red-500 hover:bg-red-600 disabled:opacity-60 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
            🗑️ Desvincular ({{ selecionadosVinculados.length }})
          </button>
        </div>

        <div v-if="loadingVinculados" class="flex items-center justify-center py-12 text-gray-400 text-sm gap-2">
          <svg class="animate-spin h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
          Carregando...
        </div>
        <div v-else-if="!vinculados.length" class="flex flex-col items-center justify-center py-12 text-gray-400">
          <span class="text-3xl mb-2">🔗</span>
          <p class="text-xs">Nenhum fornecedor vinculado.</p>
        </div>
        <div v-else class="divide-y divide-gray-50 max-h-96 overflow-y-auto">
          <div v-for="f in vinculados" :key="f.id"
            class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition">
            <input type="checkbox" :value="f.id" v-model="selecionadosVinculados"
              class="w-4 h-4 rounded accent-indigo-600 cursor-pointer" />
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-sm text-gray-800 truncate">{{ f.nome }}</div>
              <div class="text-xs text-gray-400 font-mono">{{ f.cnpj }}</div>
            </div>
            <span :class="f.status === 'ativo' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
              class="px-2 py-0.5 rounded-full text-xs font-semibold shrink-0">{{ f.status }}</span>
            <button @click="desvincular(f.id)" title="Desvincular"
              class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition text-xs font-bold">✕</button>
          </div>
        </div>
      </div>

      <!-- Disponíveis para vincular -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
          <div>
            <h2 class="font-bold text-gray-800">Vincular Fornecedores</h2>
            <p class="text-xs text-gray-400 mt-0.5">{{ disponivelsFiltrados.length }} disponível(is)</p>
          </div>
          <button v-if="selecionadosDisponiveis.length"
            @click="vincularEmMassa" :disabled="processando"
            class="flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
            🔗 Vincular ({{ selecionadosDisponiveis.length }})
          </button>
        </div>

        <div class="px-5 py-3 border-b border-gray-50">
          <input v-model="searchDisponivel" placeholder="Filtrar fornecedores..."
            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400" />
        </div>

        <div v-if="loadingDisponiveis" class="flex items-center justify-center py-12 text-gray-400 text-sm gap-2">
          <svg class="animate-spin h-4 w-4 text-indigo-400" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
          </svg>
          Carregando...
        </div>
        <div v-else-if="!disponivelsFiltrados.length" class="flex flex-col items-center justify-center py-12 text-gray-400">
          <span class="text-3xl mb-2">✅</span>
          <p class="text-xs">Todos os fornecedores já vinculados.</p>
        </div>
        <div v-else class="divide-y divide-gray-50 max-h-96 overflow-y-auto">
          <div v-for="f in disponivelsFiltrados" :key="f.id"
            class="flex items-center gap-3 px-5 py-3.5 hover:bg-gray-50 transition">
            <input type="checkbox" :value="f.id" v-model="selecionadosDisponiveis"
              class="w-4 h-4 rounded accent-indigo-600 cursor-pointer" />
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-sm text-gray-800 truncate">{{ f.nome }}</div>
              <div class="text-xs text-gray-400 font-mono">{{ f.cnpj }}</div>
            </div>
            <span :class="f.status === 'ativo' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
              class="px-2 py-0.5 rounded-full text-xs font-semibold shrink-0">{{ f.status }}</span>
            <button @click="vincular(f.id)" title="Vincular"
              class="text-indigo-500 hover:text-indigo-700 hover:bg-indigo-50 p-1.5 rounded-lg transition text-sm font-bold">+</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'

const route = useRoute()
const produtoId = route.params.id

const produto = ref(null)
const vinculados = ref([])
const todosForncedores = ref([])
const loadingVinculados = ref(false)
const loadingDisponiveis = ref(false)
const processando = ref(false)
const feedback = reactive({ msg: '', type: 'success' })
const selecionadosVinculados = ref([])
const selecionadosDisponiveis = ref([])
const searchDisponivel = ref('')

let feedbackTimer = null
function mostrarFeedback(msg, type = 'success') {
  feedback.msg = msg; feedback.type = type
  clearTimeout(feedbackTimer)
  feedbackTimer = setTimeout(() => { feedback.msg = '' }, 5000)
}

const disponivelsFiltrados = computed(() => {
  const ids = new Set(vinculados.value.map(f => f.id))
  const s = searchDisponivel.value.toLowerCase()
  return todosForncedores.value
    .filter(f => !ids.has(f.id))
    .filter(f => !s || f.nome.toLowerCase().includes(s) || f.cnpj.includes(s))
})

async function carregarProduto() {
  const { data } = await axios.get(`/api/produtos/${produtoId}`)
  produto.value = data
}

async function carregarVinculados() {
  loadingVinculados.value = true
  try {
    const { data } = await axios.get(`/api/produtos/${produtoId}/fornecedores`)
    vinculados.value = data
    selecionadosVinculados.value = []
  } finally { loadingVinculados.value = false }
}

async function carregarTodosFornecedores() {
  loadingDisponiveis.value = true
  try {
    const { data } = await axios.get('/api/fornecedores/all')
    todosForncedores.value = data
  } finally { loadingDisponiveis.value = false }
}

async function vincular(id) {
  try {
    await axios.post(`/api/produtos/${produtoId}/vincular`, { fornecedor_id: id })
    mostrarFeedback('✅ Fornecedor vinculado com sucesso!')
    await carregarVinculados()
  } catch (err) {
    mostrarFeedback(err.response?.data?.message || 'Erro ao vincular.', 'error')
  }
}

async function desvincular(id) {
  if (!confirm('Deseja remover este vínculo?')) return
  try {
    await axios.delete(`/api/produtos/${produtoId}/fornecedores/${id}`)
    mostrarFeedback('✅ Vínculo removido com sucesso!')
    await carregarVinculados()
  } catch (err) {
    mostrarFeedback(err.response?.data?.message || 'Erro ao desvincular.', 'error')
  }
}

async function vincularEmMassa() {
  processando.value = true
  try {
    await axios.post(`/api/produtos/${produtoId}/vincular-massa`, { fornecedor_ids: selecionadosDisponiveis.value })
    mostrarFeedback('⏳ Vínculo em massa enviado para processamento! Os dados serão atualizados em breve.', 'info')
    selecionadosDisponiveis.value = []
    setTimeout(carregarVinculados, 2000)
  } catch (err) {
    mostrarFeedback(err.response?.data?.message || 'Erro ao processar.', 'error')
  } finally { processando.value = false }
}

async function desvincularEmMassa() {
  if (!confirm(`Desvincular ${selecionadosVinculados.value.length} fornecedor(es)?`)) return
  processando.value = true
  try {
    await axios.post(`/api/produtos/${produtoId}/desvincular-massa`, { fornecedor_ids: selecionadosVinculados.value })
    mostrarFeedback('⏳ Desvínculo em massa enviado para processamento! Os dados serão atualizados em breve.', 'info')
    selecionadosVinculados.value = []
    setTimeout(carregarVinculados, 2000)
  } catch (err) {
    mostrarFeedback(err.response?.data?.message || 'Erro ao processar.', 'error')
  } finally { processando.value = false }
}

onMounted(() => Promise.all([carregarProduto(), carregarVinculados(), carregarTodosFornecedores()]))
</script>