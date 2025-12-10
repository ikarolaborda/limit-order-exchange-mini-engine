import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'trading',
    component: () => import('@/pages/TradingPage.vue'),
    meta: { title: 'Trading' },
  },
  {
    path: '/ai',
    name: 'ai',
    component: () => import('@/pages/AIPage.vue'),
    meta: { title: 'AI Sentiment' },
  },
  {
    path: '/web3',
    name: 'web3',
    component: () => import('@/pages/Web3Page.vue'),
    meta: { title: 'Web3 / Ethereum' },
  },
  {
    path: '/settings',
    name: 'settings',
    component: () => import('@/pages/SettingsPage.vue'),
    meta: { title: 'Settings' },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to, _from, next) => {
  const title = to.meta.title as string | undefined
  document.title = title ? `${title} | Limit Order Exchange` : 'Limit Order Exchange'
  next()
})

export default router
