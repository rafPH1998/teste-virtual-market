<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <ClipboardList class="w-6 h-6 text-indigo-600" />
          <h1 class="text-2xl font-bold text-gray-800">Pedidos</h1>
        </div>
        <p class="text-sm text-gray-500">Acompanhe e gerencie os pedidos de compra</p>
      </div>
      <button @click="abrirModal()"
        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow-sm">
        <Plus class="w-4 h-4" /> Novo Pedido
      </button>
    </div>

    <div v-if="feedback.msg" :class="[
      'flex items-center gap-2 px-4 py-3 rounded-xl mb-4 text-sm font-medium border',
      feedback.type === 'success' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'
    ]">
      <CheckCircle v-if="feedback.type === 'success'" class="w-4 h-4 shrink-0" />
      <XCircle v-else class="w-4 h-4 shrink-0" />
      {{ feedback.msg }}
    </div>

    <!-- Cards resumo -->
    <div class="grid grid-cols-4 gap-4 mb-6">
      <button v-for="s in statusResumo" :key="s.status"
        @click="filtrarStatus(s.status)"
        :class="[
          'bg-white rounded-xl border shadow-sm px-4 py-3 flex items-center gap-3 transition text-left w-full',
          filtros.status === s.status ? 'border-indigo-300 ring-2 ring-indigo-100' : 'border-gray-100 hover:border-indigo-200'
        ]">
        <div :class="`${s.bg} p-2 rounded-lg`">
          <component :is="s.icon" :class="`w-4 h-4 ${s.color}`" />
        </div>
        <div>
          <div class="text-lg font-bold text-gray-800">{{ contarStatus(s.status) }}</div>
          <div class="text-xs text-gray-500">{{ s.label }}</div>
        </div>
      </button>
    </div>

    <!-- Filtros -->
    <div class="flex flex-wrap gap-3 mb-5">
      <select v-model="filtros.status" @change="buscar"
        class="px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
        <option value="">Todos os status</option>
        <option value="aberto">Aberto</option>
        <option value="processando">Processando</option>
        <option value="concluido">Concluído</option>
        <option value="cancelado">Cancelado</option>
      </select>
      <select v-model="filtros.fornecedor_id" @change="buscar"
        class="px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white min-w-[200px]">
        <option value="">Todos os fornecedores</option>
        <option v-for="f in fornecedores" :key="f.id" :value="f.id">{{ f.nome }}</option>
      </select>
    </div>

    <!-- Tabela -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div v-if="loading" class="flex items-center justify-center py-16 text-gray-400 text-sm gap-2">
        <Loader2 class="w-5 h-5 text-indigo-500 animate-spin" /> Carregando...
      </div>

      <div v-else-if="!pedidos.length" class="flex flex-col items-center justify-center py-16 text-gray-400">
        <ClipboardList class="w-12 h-12 mb-3 text-gray-200" />
        <p class="text-sm">Nenhum pedido encontrado.</p>
      </div>

      <table v-else class="w-full text-sm">
        <thead>
          <tr class="bg-gray-50 border-b border-gray-100">
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">#</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Fornecedor</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Data</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Itens</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Total</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="p in pedidos" :key="p.id" class="hover:bg-gray-50 transition">
            <td class="px-5 py-4 font-bold text-indigo-600">#{{ p.id }}</td>
            <td class="px-5 py-4 font-medium text-gray-700">{{ p.fornecedor?.nome }}</td>
            <td class="px-5 py-4 text-gray-500">
              <div class="flex items-center gap-1.5">
                <Calendar class="w-3.5 h-3.5 text-gray-400" />
                {{ formatarData(p.data_pedido) }}
              </div>
            </td>
            <td class="px-5 py-4">
              <span :class="badgeClass(p.status)" class="px-2.5 py-1 rounded-full text-xs font-semibold">
                {{ labelStatus(p.status) }}
              </span>
            </td>
            <td class="px-5 py-4 text-gray-500">
              <div class="flex items-center gap-1">
                <ShoppingCart class="w-3.5 h-3.5 text-gray-400" />
                {{ p.itens_count }}
              </div>
            </td>
            <td class="px-5 py-4 font-bold text-gray-800">{{ formatarMoeda(p.valor_total) }}</td>
            <td class="px-5 py-4">
              <div class="flex justify-end gap-2">
                <router-link :to="`/pedidos/${p.id}`"
                  class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-lg hover:bg-indigo-100 transition">
                  <Eye class="w-3 h-3" /> Ver
                </router-link>
                <button @click="abrirModal(p)" :disabled="['concluido','cancelado'].includes(p.status)"
                  class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition disabled:opacity-40 disabled:cursor-not-allowed">
                  <Pencil class="w-3 h-3" />
                </button>
                <button @click="excluir(p)" :disabled="p.status === 'concluido'"
                  class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-red-50 text-red-600 border border-red-100 rounded-lg hover:bg-red-100 transition disabled:opacity-40 disabled:cursor-not-allowed">
                  <Trash2 class="w-3 h-3" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="meta.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t border-gray-100 text-sm text-gray-500">
        <span>{{ meta.from }}–{{ meta.to }} de {{ meta.total }} registros</span>
        <div class="flex gap-2">
          <button :disabled="meta.current_page === 1" @click="paginar(meta.current_page - 1)"
            class="flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed text-xs transition">
            <ChevronLeft class="w-3 h-3" /> Anterior
          </button>
          <button :disabled="meta.current_page === meta.last_page" @click="paginar(meta.current_page + 1)"
            class="flex items-center gap-1 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed text-xs transition">
            Próximo <ChevronRight class="w-3 h-3" />
          </button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4" @click.self="fecharModal">
      <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
          <h2 class="text-lg font-bold text-gray-800">{{ form.id ? 'Editar Pedido' : 'Novo Pedido' }}</h2>
          <button @click="fecharModal" class="text-gray-400 hover:text-gray-600 transition"><X class="w-5 h-5" /></button>
        </div>
        <div class="px-6 py-5 space-y-4">
          <div v-if="erros.geral" class="flex items-center gap-2 bg-red-50 text-red-700 border border-red-200 rounded-xl px-4 py-3 text-sm">
            <AlertCircle class="w-4 h-4 shrink-0" /> {{ erros.geral }}
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Fornecedor *</label>
            <select v-model="form.fornecedor_id" :disabled="!!form.id" :class="erros.fornecedor_id ? 'border-red-400' : 'border-gray-200'"
              class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white disabled:bg-gray-50 disabled:text-gray-400">
              <option value="">Selecione um fornecedor...</option>
              <option v-for="f in fornecedores" :key="f.id" :value="f.id">{{ f.nome }}</option>
            </select>
            <p v-if="erros.fornecedor_id" class="text-red-500 text-xs mt-1">{{ erros.fornecedor_id[0] }}</p>
            <p v-if="form.id" class="flex items-center gap-1 text-amber-500 text-xs mt-1">
              <AlertTriangle class="w-3 h-3" /> O fornecedor não pode ser alterado após a criação.
            </p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Data do Pedido *</label>
            <input type="date" v-model="form.data_pedido" :class="erros.data_pedido ? 'border-red-400' : 'border-gray-200'"
              class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400" />
            <p v-if="erros.data_pedido" class="text-red-500 text-xs mt-1">{{ erros.data_pedido[0] }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Status *</label>
            <select v-model="form.status" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
              <option value="aberto">Aberto</option>
              <option value="processando">Processando</option>
              <option value="concluido">Concluído</option>
              <option value="cancelado">Cancelado</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Observações</label>
            <textarea v-model="form.observacoes" rows="3" placeholder="Opcional..."
              class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"></textarea>
          </div>
        </div>
        <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-100">
          <button @click="fecharModal" class="px-4 py-2 text-sm border border-gray-200 rounded-xl hover:bg-gray-50 text-gray-600 transition">Cancelar</button>
          <button @click="salvar" :disabled="salvando"
            class="flex items-center gap-2 px-5 py-2 text-sm bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white font-semibold rounded-xl transition">
            <Loader2 v-if="salvando" class="w-4 h-4 animate-spin" />
            <Save v-else class="w-4 h-4" />
            {{ salvando ? 'Salvando...' : 'Salvar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'
import {
  ClipboardList, Plus, Eye, Pencil, Trash2, Save, X,
  Calendar, ShoppingCart, ChevronLeft, ChevronRight,
  Loader2, CheckCircle, XCircle, AlertCircle, AlertTriangle,
  FolderOpen, Settings, CheckSquare, XSquare
} from 'lucide-vue-next'

const pedidos = ref([])
const fornecedores = ref([])
const loading = ref(false)
const salvando = ref(false)
const modal = ref(false)
const meta = ref({})
const filtros = reactive({ status: '', fornecedor_id: '', page: 1 })
const feedback = reactive({ msg: '', type: 'success' })
const erros = reactive({})

const statusResumo = [
  { status: 'aberto',      label: 'Abertos',      icon: FolderOpen,   bg: 'bg-blue-50',   color: 'text-blue-500' },
  { status: 'processando', label: 'Processando',   icon: Settings,     bg: 'bg-amber-50',  color: 'text-amber-500' },
  { status: 'concluido',   label: 'Concluídos',    icon: CheckSquare,  bg: 'bg-green-50',  color: 'text-green-500' },
  { status: 'cancelado',   label: 'Cancelados',    icon: XSquare,      bg: 'bg-red-50',    color: 'text-red-500' },
]

const formPadrao = () => ({
  id: null, fornecedor_id: '',
  data_pedido: new Date().toISOString().split('T')[0],
  status: 'aberto', observacoes: ''
})
const form = reactive(formPadrao())

let feedbackTimer = null
function mostrarFeedback(msg, type = 'success') {
  feedback.msg = msg; feedback.type = type
  clearTimeout(feedbackTimer)
  feedbackTimer = setTimeout(() => { feedback.msg = '' }, 4000)
}

function contarStatus(s) { return pedidos.value.filter(p => p.status === s).length }
function filtrarStatus(s) { filtros.status = filtros.status === s ? '' : s; buscar() }

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
  return new Date(d + 'T00:00:00').toLocaleDateString('pt-BR')
}

function formatarMoeda(v) {
  return (v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

async function carregar() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/pedidos', { params: filtros })
    pedidos.value = data.data
    meta.value = data.meta || data
  } finally { loading.value = false }
}

function buscar() { filtros.page = 1; carregar() }
function paginar(page) { filtros.page = page; carregar() }

function abrirModal(p = null) {
  Object.assign(form, p ? { ...p, fornecedor_id: p.fornecedor?.id || p.fornecedor_id } : formPadrao())
  Object.keys(erros).forEach(k => delete erros[k])
  modal.value = true
}

function fecharModal() { modal.value = false }

async function salvar() {
  salvando.value = true
  Object.keys(erros).forEach(k => delete erros[k])
  try {
    if (form.id) {
      await axios.put(`/api/pedidos/${form.id}`, form)
      mostrarFeedback('Pedido atualizado com sucesso!')
    } else {
      await axios.post('/api/pedidos', form)
      mostrarFeedback('Pedido criado com sucesso!')
    }
    fecharModal(); carregar()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.assign(erros, err.response.data.errors || {})
      erros.geral = err.response.data.message
    } else {
      mostrarFeedback('Erro ao salvar.', 'error')
    }
  } finally { salvando.value = false }
}

async function excluir(p) {
  if (!confirm(`Deseja excluir o pedido #${p.id}?`)) return
  try {
    await axios.delete(`/api/pedidos/${p.id}`)
    mostrarFeedback('Pedido excluído com sucesso!')
    carregar()
  } catch (err) {
    mostrarFeedback(err.response?.data?.message || 'Erro ao excluir.', 'error')
  }
}

onMounted(async () => {
  const [, forn] = await Promise.all([carregar(), axios.get('/api/fornecedores/all')])
  fornecedores.value = forn.data
})
</script>