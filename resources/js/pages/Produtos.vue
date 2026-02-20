<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <Package class="w-6 h-6 text-indigo-600" />
          <h1 class="text-2xl font-bold text-gray-800">Produtos</h1>
        </div>
        <p class="text-sm text-gray-500">Gerencie o catálogo de produtos</p>
      </div>
      <button @click="abrirModal()"
        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition shadow-sm">
        <Plus class="w-4 h-4" /> Novo Produto
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

    <!-- Filtros -->
    <div class="flex flex-wrap gap-3 mb-5">
      <div class="relative flex-1 min-w-[220px] max-w-sm">
        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
        <input v-model="filtros.search" @input="buscar" placeholder="Buscar por nome ou código..."
          class="w-full pl-9 pr-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white" />
      </div>
      <select v-model="filtros.status" @change="buscar"
        class="px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
        <option value="">Todos os status</option>
        <option value="ativo">Ativo</option>
        <option value="inativo">Inativo</option>
      </select>
    </div>

    <!-- Tabela -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
      <div v-if="loading" class="flex items-center justify-center py-16 text-gray-400 text-sm gap-2">
        <Loader2 class="w-5 h-5 text-indigo-500 animate-spin" /> Carregando...
      </div>

      <div v-else-if="!produtos.length" class="flex flex-col items-center justify-center py-16 text-gray-400">
        <Package class="w-12 h-12 mb-3 text-gray-200" />
        <p class="text-sm">Nenhum produto encontrado.</p>
      </div>

      <table v-else class="w-full text-sm">
        <thead>
          <tr class="bg-gray-50 border-b border-gray-100">
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Código</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Nome</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Descrição</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          <tr v-for="p in produtos" :key="p.id" class="hover:bg-gray-50 transition">
            <td class="px-5 py-4">
              <span class="bg-gray-100 text-gray-600 font-mono text-xs px-2 py-1 rounded-md">{{ p.codigo_interno }}</span>
            </td>
            <td class="px-5 py-4 font-semibold text-gray-800">{{ p.nome }}</td>
            <td class="px-5 py-4 text-gray-500 max-w-xs truncate">{{ p.descricao || '—' }}</td>
            <td class="px-5 py-4">
              <span :class="p.status === 'ativo' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
                class="px-2.5 py-1 rounded-full text-xs font-semibold">
                {{ p.status === 'ativo' ? 'Ativo' : 'Inativo' }}
              </span>
            </td>
            <td class="px-5 py-4">
              <div class="flex justify-end gap-2">
                <button @click="abrirModal(p)"
                  class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium border border-gray-200 rounded-lg hover:bg-gray-50 text-gray-600 transition">
                  <Pencil class="w-3 h-3" /> Editar
                </button>
                <router-link :to="`/produtos/${p.id}/vinculos`"
                  class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-indigo-50 text-indigo-600 border border-indigo-100 rounded-lg hover:bg-indigo-100 transition">
                  <Link class="w-3 h-3" /> Vínculos
                </router-link>
                <button @click="excluir(p)"
                  class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium bg-red-50 text-red-600 border border-red-100 rounded-lg hover:bg-red-100 transition">
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
          <h2 class="text-lg font-bold text-gray-800">{{ form.id ? 'Editar Produto' : 'Novo Produto' }}</h2>
          <button @click="fecharModal" class="text-gray-400 hover:text-gray-600 transition"><X class="w-5 h-5" /></button>
        </div>
        <div class="px-6 py-5 space-y-4">
          <div v-if="erros.geral" class="flex items-center gap-2 bg-red-50 text-red-700 border border-red-200 rounded-xl px-4 py-3 text-sm">
            <AlertCircle class="w-4 h-4 shrink-0" /> {{ erros.geral }}
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Nome *</label>
            <input v-model="form.nome" :class="erros.nome ? 'border-red-400' : 'border-gray-200'"
              class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400" />
            <p v-if="erros.nome" class="text-red-500 text-xs mt-1">{{ erros.nome[0] }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Código Interno *</label>
            <input v-model="form.codigo_interno" :class="erros.codigo_interno ? 'border-red-400' : 'border-gray-200'"
              class="w-full px-4 py-2.5 text-sm border rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 font-mono" />
            <p v-if="erros.codigo_interno" class="text-red-500 text-xs mt-1">{{ erros.codigo_interno[0] }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Descrição</label>
            <textarea v-model="form.descricao" rows="3"
              class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none"></textarea>
          </div>
          <div>
            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1.5">Status *</label>
            <select v-model="form.status" class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
              <option value="ativo">Ativo</option>
              <option value="inativo">Inativo</option>
            </select>
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
  Package, Plus, Search, Pencil, Trash2, Link, Save, X,
  ChevronLeft, ChevronRight, Loader2,
  CheckCircle, XCircle, AlertCircle
} from 'lucide-vue-next'

const produtos = ref([])
const loading = ref(false)
const salvando = ref(false)
const modal = ref(false)
const meta = ref({})
const filtros = reactive({ search: '', status: '', page: 1 })
const feedback = reactive({ msg: '', type: 'success' })
const erros = reactive({})

const formPadrao = () => ({ id: null, nome: '', codigo_interno: '', descricao: '', status: 'ativo' })
const form = reactive(formPadrao())

let feedbackTimer = null
function mostrarFeedback(msg, type = 'success') {
  feedback.msg = msg; feedback.type = type
  clearTimeout(feedbackTimer)
  feedbackTimer = setTimeout(() => { feedback.msg = '' }, 4000)
}

async function carregar() {
  loading.value = true
  try {
    const { data } = await axios.get('/api/produtos', { params: filtros })
    produtos.value = data.data
    meta.value = data.meta || data
  } finally { loading.value = false }
}

let searchTimer = null
function buscar() {
  clearTimeout(searchTimer)
  filtros.page = 1
  searchTimer = setTimeout(carregar, 400)
}

function paginar(page) { filtros.page = page; carregar() }

function abrirModal(p = null) {
  Object.assign(form, p ? { ...p } : formPadrao())
  Object.keys(erros).forEach(k => delete erros[k])
  modal.value = true
}

function fecharModal() { modal.value = false }

async function salvar() {
  salvando.value = true
  Object.keys(erros).forEach(k => delete erros[k])
  try {
    if (form.id) {
      await axios.put(`/api/produtos/${form.id}`, form)
      mostrarFeedback('Produto atualizado com sucesso!')
    } else {
      await axios.post('/api/produtos', form)
      mostrarFeedback('Produto cadastrado com sucesso!')
    }
    fecharModal(); carregar()
  } catch (err) {
    if (err.response?.status === 422) {
      Object.assign(erros, err.response.data.errors || {})
      if (err.response.data.message) erros.geral = err.response.data.message
    } else {
      mostrarFeedback('Erro ao salvar.', 'error')
    }
  } finally { salvando.value = false }
}

async function excluir(p) {
  if (!confirm(`Deseja excluir o produto "${p.nome}"?`)) return
  try {
    await axios.delete(`/api/produtos/${p.id}`)
    mostrarFeedback('Produto excluído com sucesso!')
    carregar()
  } catch (err) {
    mostrarFeedback(err.response?.data?.message || 'Erro ao excluir.', 'error')
  }
}

onMounted(carregar)
</script>