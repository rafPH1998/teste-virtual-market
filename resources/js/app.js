import { createApp } from 'vue'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import axios from 'axios'

import Fornecedores from './pages/Fornecedores.vue'
import Produtos from './pages/Produtos.vue'
import ProdutoVinculos from './pages/ProdutoVinculos.vue'
import Pedidos from './pages/Pedidos.vue'
import PedidoDetalhe from './pages/PedidoDetalhe.vue'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', redirect: '/fornecedores' },
    { path: '/fornecedores', component: Fornecedores },
    { path: '/produtos', component: Produtos },
    { path: '/produtos/:id/vinculos', component: ProdutoVinculos },
    { path: '/pedidos', component: Pedidos },
    { path: '/pedidos/:id', component: PedidoDetalhe },
  ],
})

const app = createApp(App)
app.use(router)
app.config.globalProperties.$axios = axios
app.mount('#app')