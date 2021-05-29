import RecurringRoutes from './RecurringRouter'

const Index = () => import('./components/l-limitless-bs4/Index');
const Form = () => import('./components/l-limitless-bs4/Form');
const Show = () => import('./components/l-limitless-bs4/Show');
const SideBarLeft = () => import('./components/l-limitless-bs4/SideBarLeft');
const SideBarRight = () => import('./components/l-limitless-bs4/SideBarRight');

let routes = [

    {
        path: '/invoices',
        components: {
            default: Index,
            //'sidebar-left': ComponentSidebarLeft,
            //'sidebar-right': ComponentSidebarRight
        },
        meta: {
            title: 'Accounting :: Sales :: Invoices',
            metaTags: [
                {
                    name: 'description',
                    content: 'Invoices'
                },
                {
                    property: 'og:description',
                    content: 'Invoices'
                }
            ]
        }
    },
    {
        path: '/invoices/create',
        components: {
            default: Form,
            //'sidebar-left': ComponentSidebarLeft,
        },
        meta: {
            title: 'Accounting :: Sales ::  Invoices :: Create',
            metaTags: [
                {
                    name: 'description',
                    content: 'Create Invoices'
                },
                {
                    property: 'og:description',
                    content: 'Create Invoices'
                }
            ]
        }
    },
    {
        path: '/invoices/:id',
        components: {
            default: Show,
            'sidebar-left': SideBarLeft,
            'sidebar-right': SideBarRight
        },
        meta: {
            title: 'Accounting :: Sales :: Invoice',
            metaTags: [
                {
                    name: 'description',
                    content: 'Invoice'
                },
                {
                    property: 'og:description',
                    content: 'Invoice'
                }
            ]
        }
    },
    {
        path: '/invoices/:id/copy',
        components: {
            default: Form,
        },
        meta: {
            title: 'Accounting :: Sales :: Invoice :: Copy',
            metaTags: [
                {
                    name: 'description',
                    content: 'Copy Invoice'
                },
                {
                    property: 'og:description',
                    content: 'Copy Invoice'
                }
            ]
        }
    },
    {
        path: '/invoices/:id/edit',
        components: {
            default: Form,
        },
        meta: {
            title: 'Invoice :: Copy',
            metaTags: [
                {
                    name: 'description',
                    content: 'Edit Invoice'
                },
                {
                    property: 'og:description',
                    content: 'Edit Invoice'
                }
            ]
        }
    },

    {
        path: '/estimates/:id/process/invoice',
        components: {
            default: Form,
        },
        meta: {
            title: 'Accounting :: Estimate :: Process',
            metaTags: [
                {
                    name: 'description',
                    content: 'Process Estimate'
                },
                {
                    property: 'og:description',
                    content: 'Process Estimate'
                }
            ]
        }
    }

];

routes = routes.concat(
    routes,
    RecurringRoutes
);

export default routes
