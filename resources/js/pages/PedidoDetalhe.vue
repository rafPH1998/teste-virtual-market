<template>
  <div>
    <!-- Header -->
    <div class="flex items-start justify-between mb-6">
      <div>
        <router-link to="/pedidos"
          class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium mb-2 transition">
          <ArrowLeft class="w-4 h-4" /> Voltar para Pedidos
        </router-link>
        <div class="flex items-center gap-3">
          <h1 class="text-2xl font-bold text-gray-800">
            Pedido <span class="text-indigo-600">#{{ pedido?.id }}</span>
          </h1>
          <span v-if="pedido" :class="badgeClass(pedido.status)" class="px-3 py-1 rounded-full text-sm font-semibold">
            {{ labelStatus(pedido.status) }}
          </span>
        </div>
        <p v-if="pedido" class="text-sm text-gray-400 mt-1 flex items-center gap-1.5">
          <Clock class="w-3.5 h-3.5" /> Criado em {{ formatarDatetime(pedido.created_at) }}
        </p>
      </div>
      <button v-if="pedido && !pedidoBloqueado" @click="abrirModalItem()"
        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow-sm">
        <Plus class="w-4 h-4" /> Adicionar Item
      </button>
    </div>

    <!-- Alerta bloqueado -->
    <div v-if="pedido && pedidoBloqueado"
      class="flex items-center gap-2 px-4 py-3 rounded-xl mb-5 text-sm font-medium border bg-amber-50 text-amber-700 border-amber-200">
      <Lock class="w-4 h-4 shrink-0" />
      Este pedido está <strong class="mx-1">{{ labelStatus(pedido.status) }}</strong> e não pode ser editado.
    </div>

    <div v-if="feedback.msg" :class="[
      'flex items-center gap-2 px-4 py-3 rounded-xl mb-5 text-sm font-medium border',
      feedback.type === 'success' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'
    ]">
      <CheckCircle v-if="feedback.type === 'success'" class="w-4 h-4 shrink-0" />
      <XCircle v-else class="w-4 h-4 shrink-0" />
      {{ feedback.msg }}
    </div>

    <div v-if="loading" class="flex items-center justify-center py-24 text-gray-400 text-sm gap-2">
      <Loader2 class="w-6 h-6 text-indigo-500 animate-spin" /> Carregando pedido...
    </div>

    <div v-if="pedido" class="grid grid-cols-3 gap-5">
      <!-- Coluna principal -->
      <div class="col-span-2 space-y-5">

        <!-- Informações -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
          <h2 class="flex items-center gap-2 font-bold text-gray-600 text-xs uppercase tracking-wide mb-4">
            <Info class="w-4 h-4" /> Informações do Pedido
          </h2>
          <div class="grid grid-cols-2 gap-5">
            <div>
              <div class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1 flex items-center gap-1">
                <Factory class="w-3 h-3" /> Fornecedor
              </div>
              <div class="font-bold text-gray-800">{{ pedido.fornecedor?.nome }}</div>
              <div class="text-xs text-gray-400 font-mono mt-0.5">{{ pedido.fornecedor?.cnpj }}</div>
            </div>
            <div>
              <div class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1 flex items-center gap-1">
                <Calendar class="w-3 h-3" /> Data do Pedido
              </div>
              <div class="font-semibold text-gray-800">{{ formatarData(pedido.created_at) }}</div>
            </div>
            <div v-if="pedido.observacoes" class="col-span-2">
              <div class="text-xs text-gray-400 font-semibold uppercase tracking-wide mb-1 flex items-center gap-1">
                <FileText class="w-3 h-3" /> Observações
              </div>
              <div class="text-gray-600 text-sm bg-gray-50 rounded-xl p-3">{{ pedido.observacoes }}</div>
            </div>
          </div>
        </div>

        <!-- Itens -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="flex items-center gap-2 font-bold text-gray-600 text-xs uppercase tracking-wide">
              <ShoppingCart class="w-4 h-4" /> Itens do Pedido
            </h2>
            <span class="text-xs text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">{{ pedido.itens?.length || 0 }} item(s)</span>
          </div>

          <div v-if="!pedido.itens?.length" class="flex flex-col items-center justify-center py-12 text-gray-400">
            <Package class="w-10 h-10 mb-2 text-gray-200" />
            <p class="text-sm mb-3">Nenhum item adicionado ainda.</p>
            <button v-if="!pedidoBloqueado" @click="abrirModalItem()"
              class="flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition">
              <Plus class="w-4 h-4" /> Adicionar primeiro item
            </button>
          </div>

          <table v-else class="w-full text-sm">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-100">
                <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Produto</th>
                <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Qtd</th>
                <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Valor Unit.</th>
                <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
                <th v-if="!pedidoBloqueado" class="px-4 py-3 w-10"></th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="item in pedido.itens" :key="item.id" class="hover:bg-gray-50 transition">
                <td class="px-5 py-4">
                  <div class="font-semibold text-gray-800">{{ item.produto?.nome }}</div>
                  <div class="text-xs text-gray-400 font-mono">{{ item.produto?.codigo_interno }}</div>
                </td>
                <td class="px-4 py-4 text-center font-medium text-gray-700">{{ item.quantidade }}</td>
                <td class="px-4 py-4 text-right text-gray-600">{{ formatarMoeda(item.valor_unitario) }}</td>
                <td class="px-5 py-4 text-right font-bold text-gray-800">{{ formatarMoeda(item.valor_total) }}</td>
                <td v-if="!pedidoBloqueado" class="px-4 py-4">
                  <button @click="removerItem(item)"
                    class="text-red-400 hover:text-red-600 hover:bg-red-50 p-1.5 rounded-lg transition">
                    <Trash2 class="w-3.5 h-3.5" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Total -->
          <div v-if="pedido.itens?.length" class="flex items-center justify-between px-5 py-4 bg-indigo-50 border-t border-indigo-100">
            <span class="text-sm font-semibold text-indigo-700 flex items-center gap-2">
              <DollarSign class="w-4 h-4" /> Total do Pedido
            </span>
            <span class="text-xl font-bold text-indigo-700">{{ formatarMoeda(pedido.valor_total) }}</span>
          </div>
        </div>
      </div>

      <!-- Histórico -->
      <div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="flex items-center gap-2 font-bold text-gray-600 text-xs uppercase tracking-wide">
              <History class="w-4 h-4" /> Histórico de Status
            </h2>
          </div>
          <div v-if="!pedido.historico_status?.length" class="flex flex-col items-center justify-center py-10 text-gray-400">
            <History class="w-8 h-8 mb-2 text-gray-200" />
            <p class="text-xs">Sem registros.</p>
          </div>
          <div v-else class="divide-y divide-gray-50 max-h-96 overflow-y-auto">
            <div v-for="h in pedido.historico_status" :key="h.id" class="px-5 py-3.5">
              <div class="flex items-center gap-2 flex-wrap">
                <span v-if="h.status_anterior" :class="badgeClass(h.status_anterior)"
                  class="px-2 py-0.5 rounded-full text-xs font-semibold">{{ labelStatus(h.status_anterior) }}</span>
                <ArrowRight v-if="h.status_anterior" class="w-3 h-3 text-gray-300" />
                <span :class="badgeClass(h.status_novo)" class="px-2 py-0.5 rounded-full text-xs font-semibold">
                  {{ labelStatus(h.status_novo) }}
                </span>
              </div>
              <div class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                <Clock class="w-3 h-3" /> {{ formatarDatetime(h.created_at) }}
              </div>
              <div v-if="h.observacao" class="text-xs text-gray-600 mt-1 bg-gray-50 rounded px-2 py-1">{{ h.observacao }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Adicionar Item -->
    <div v-if="modalItem" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="fecharModalItem">
      <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <ShoppingCart class="w-5 h-5 text-indigo-500" /> Adicionar Item
          </h2>
          <button @click="fecharModalItem" class="text-gray-400 hover:text-gray-600 transition"><X class="w-5 h-5" /></button>
        </div>
        <div class="px-6 py-5 space-y-4">
          <div v-if="erros.geral" class="flex items-center gap-2 bg-red-50 text-red-700 border border-red-200 rounded-xl px-4 py-3 text-sm">
            <AlertCircle class="w-4 h-4 shrink-0" /> {{ erros.geral }}
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Produto *</label>
            <select v-model="formItem.produto_id" :class="erros.produto_id ? 'border-red-400' : 'border-gray-200'"
              class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
              <option value="">Selecione um produto...</option>
              <option v-for="p in produtosDisponiveis" :key="p.id" :value="p.id">{{ p.nome }} — {{ p.codigo_interno }}</option>
            </select>
            <p v-if="erros.produto_id" class="text-red-500 text-xs mt-1">{{ erros.produto_id[0] }}</p>
            <p class="text-xs text-gray-400 mt-1 flex items-center gap-1">
              <Info class="w-3 h-3" /> Somente produtos ativos vinculados a <strong class="mx-0.5">{{ pedido?.fornecedor?.nome }}</strong>.
            </p>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Quantidade *</label>
              <input type="number" min="1" v-model.number="formItem.quantidade" :class="erros.quantidade ? 'border-red-400' : 'border-gray-200'"
                class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400" />
              <p v-if="erros.quantidade" class="text-red-500 text-xs mt-1">{{ erros.quantidade[0] }}</p>
            </div>
            <div>
              <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Valor Unitário *</label>
              <input type="number" step="0.01" min="0.01" v-model.number="formItem.valor_unitario" :class="erros.valor_unitario ? 'border-red-400' : 'border-gray-200'"
                class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400" />
              <p v-if="erros.valor_unitario" class="text-red-500 text-xs mt-1">{{ erros.valor_unitario[0] }}</p>
            </div>
          </div>
          <!-- Preview -->
          <div v-if="formItem.quantidade > 0 && formItem.valor_unitario > 0"
            class="bg-indigo-50 border border-indigo-100 rounded-xl px-4 py-3 flex items-center justify-between">
            <span class="text-sm text-indigo-600 font-medium flex items-center gap-1.5">
              <DollarSign class="w-4 h-4" /> Total do item
            </span>
            <span class="text-lg font-bold text-indigo-700">{{ formatarMoeda(formItem.quantidade * formItem.valor_unitario) }}</span>
          </div>
        </div>
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
          <button @click="fecharModalItem" class="px-4 py-2 text-sm border border-gray-200 rounded-xl hover:bg-gray-50 text-gray-600 transition">Cancelar</button>
          <button @click="adicionarItem" :disabled="salvandoItem"
            class="flex items-center gap-2 px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-semibold rounded-xl transition">
            <Loader2 v-if="salvandoItem" class="w-4 h-4 animate-spin" />
            <Plus v-else class="w-4 h-4" />
            {{ salvandoItem ? 'Adicionando...' : 'Adicionar Item' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import axios from 'axios'
import {
  ArrowLeft, ArrowRight, Plus, Trash2, X, Save,
  Clock, Calendar, Factory, FileText, ShoppingCart,
  Package, DollarSign, History, Lock, Info,
  Loader2, CheckCircle, XCircle, AlertCircle
} from 'lucide-vue-next'

const route = useRoute()
const pedidoId = route.params.id

const pedido = ref(null)
const loading = ref(false)
const modalItem = ref(false)
const salvandoItem = ref(false)
const produtosDisponiveis = ref([])
const feedback = reactive({ msg: '', type: 'success' })
const erros = reactive({})
const formItem = reactive({ produto_id: '', quantidade: 1, valor_unitario: '' })

const pedidoBloqueado = computed(() => ['concluido', 'cancelado'].includes(pedido.value?.status))

let feedbackTimer = null
function mostrarFeedback(msg, type = 'success') {
  feedback.msg = msg; feedback.type = type
  clearTimeout(feedbackTimer)
  feedbackTimer = setTimeout(() => { feedback.msg = '' }, 4000)
}

function badgeClass(s) {
  return {
    aberto: 'bg-blue-100 text-blue-700',
    processando: 'bg-amber-100 text-amber-700',
    concluido: 'bg-green-100 text-green-700',
    cancelado: 'bg-red-100 text-red-600',
  }[s] || 'bg-gray-100 text-gray-600'
}

function labelStatus(s) {
  return { aberto: 'Aberto', processando: 'Processando', concluido: 'Concluído', cancelado: 'Cancelado' }[s] || s
}

function formatarData(d) {
  if (!d) return '—'
  return new Date(d).toLocaleDateString('pt-BR')
}

function formatarDatetime(d) {
  if (!d) return ''
  return new Date(d).toLocaleString('pt-BR')
}

function formatarMoeda(v) {
  return (v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

async function carregar() {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/pedidos/${pedidoId}`)
    pedido.value = data
  } finally { loading.value = false }
}

async function abrirModalItem() {
  Object.assign(formItem, { produto_id: '', quantidade: 1, valor_unitario: '' })
  Object.keys(erros).forEach(k => delete erros[k])
  const { data } = await axios.get(`/api/pedidos/${pedidoId}/produtos-disponiveis`)
  produtosDisponiveis.value = data
  modalItem.value = true
}

function fecharModalItem() { modalItem.value = false }

async function adicionarItem() {
  salvandoItem.value = true
  Object.keys(erros).forEach(k => delete erros[k])
  try {
    await axios.post(`/api/pedidos/${pedidoId}/itens`, formItem)
    mostrarFeedback('Item adicionado com sucesso!')
    fecharModalItem()
    await carregar()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.assign(erros, err.response.data.errors || {})
      erros.geral = err.response.data.message
    } else {
      mostrarFeedback('Erro ao adicionar item.', 'error')
    }
  } finally { salvandoItem.value = false }
}

async function removerItem(item) {
  if (!confirm('Deseja remover este item do pedido?')) return
  try {
    await axios.delete(`/api/pedidos/${pedidoId}/itens/${item.id}`)
    mostrarFeedback('Item removido com sucesso!')
    await carregar()
  } catch (err) {
    mostrarFeedback(err.response?.data?.message || 'Erro ao remover.', 'error')
  }
}

onMounted(carregar)
</script>