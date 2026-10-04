import {
  createRouter,
  createWebHistory
} from 'vue-router'

import HomeView from '../views/HomeView.vue'
import ProductsView from '../views/ProductsView.vue'
import ServicesView from '../views/ServicesView.vue'
import AiAssistantView from '../views/AiAssistantView.vue'

import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'

import ProfileView from '../views/ProfileView.vue'
import PetsView from '../views/PetsView.vue'

import CustomerDashboardView
  from '../views/CustomerDashboardView.vue'

import {
  authState
} from '../services/auth'

import AdminDashboardView
  from '../views/AdminDashboardView.vue'

import AdminCategoriesView
  from '../views/AdminCategoriesView.vue'  

import AdminProductsView
  from '../views/AdminProductsView.vue'

  import AdminServicesView
  from '../views/AdminServicesView.vue'

  import AdminCustomersView
  from '../views/AdminCustomersView.vue'

  import AiHistoryView from '../views/AiHistoryView.vue'

  import AiHistoryDetailView from '../views/AiHistoryDetailView.vue'

  import AdminAiMonitoringView from '../views/AdminAiMonitoringView.vue'

const routes = [

  /*
  |--------------------------------------------------------------------------
  | PUBLIC
  |--------------------------------------------------------------------------
  */

  {
  path: '/admin/ai-monitoring',
  name: 'admin-ai-monitoring',
  component: AdminAiMonitoringView,
  meta: {
    requiresAdmin: true,
  },
},

  {
  path: '/customer/ai-history',
  name: 'customer-ai-history',
  component: AiHistoryView,
  meta: {
    requiresCustomer: true,
  },
},

{
  path: '/customer/ai-history/:id',
  name: 'customer-ai-history-detail',
  component: AiHistoryDetailView,
  meta: {
    requiresCustomer: true,
  },
},



  {
    path: '/',
    name: 'home',
    component: HomeView,
  },

  {
    path: '/products',
    name: 'products',
    component: ProductsView,
  },

  {
    path: '/services',
    name: 'services',
    component: ServicesView,
  },

  {
    path: '/ai-assistant',
    name: 'ai-assistant',
    component: AiAssistantView,
  },


  /*
  |--------------------------------------------------------------------------
  | GUEST
  |--------------------------------------------------------------------------
  */

  {
    path: '/login',
    name: 'login',
    component: LoginView,

    meta: {
      guestOnly: true,
    },
  },

  {
    path: '/register',
    name: 'register',
    component: RegisterView,

    meta: {
      guestOnly: true,
    },
  },


  /*
  |--------------------------------------------------------------------------
  | CUSTOMER
  |--------------------------------------------------------------------------
  */

  {
    path: '/profile',
    name: 'profile',
    component: ProfileView,

    meta: {
      requiresCustomer: true,
    },
  },

  {
    path: '/my-pets',
    name: 'my-pets',
    component: PetsView,

    meta: {
      requiresCustomer: true,
    },
  },

  /*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

{
  path: '/admin/dashboard',
  name: 'admin-dashboard',
  component: AdminDashboardView,

  meta: {
    requiresAdmin: true,
  },
},

  {
  path: '/customer/dashboard',
  name: 'customer-dashboard',
  component: CustomerDashboardView,

  meta: {
    requiresCustomer: true,
  },
},

{
  path: '/admin/products',
  name: 'admin-products',
  component: AdminProductsView,

  meta: {
    requiresAdmin: true,
  },
},

{
  path: '/admin/categories',
  name: 'admin-categories',
  component: AdminCategoriesView,

  meta: {
    requiresAdmin: true,
  },
},

{
  path: '/admin/services',
  name: 'admin-services',
  component: AdminServicesView,

  meta: {
    requiresAdmin: true,
  },
},

{
  path: '/admin/customers',
  name: 'admin-customers',
  component: AdminCustomersView,

  meta: {
    requiresAdmin: true,
  },
},

]


const router = createRouter({
  history: createWebHistory(),
  routes,

  scrollBehavior() {
    return {
      top: 0,
    }
  },
})




/*
|--------------------------------------------------------------------------
| ROUTE GUARD
|--------------------------------------------------------------------------
*/

router.beforeEach((to) => {

  const user = authState.user


  /*
   * GUEST ONLY
   */

  if (
    to.meta.guestOnly &&
    user
  ) {

    if (user.role === 'admin') {
      return '/admin/dashboard'
    }

    if (user.role === 'customer') {
      return '/customer/dashboard'
    }

    return '/'
  }


  /*
   * CUSTOMER ONLY
   */

  if (to.meta.requiresCustomer) {

    if (!user) {
      return '/login'
    }

    if (user.role !== 'customer') {
      return '/admin/dashboard'
    }
  }


  /*
   * ADMIN ONLY
   */

  if (to.meta.requiresAdmin) {

    if (!user) {
      return '/login'
    }

    if (user.role !== 'admin') {
      return '/customer/dashboard'
    }
  }


  return true
})


export default router