
const Index = () => import('./components/l-limitless-bs4/recurring/Index');
const Form = () => import('./components/l-limitless-bs4/recurring/Form');
const Show = () => import('./components/l-limitless-bs4/recurring/Show');
const SideBarLeft = () => import('./components/l-limitless-bs4/recurring/SideBarLeft');
const SideBarRight = () => import('./components/l-limitless-bs4/recurring/SideBarRight');

const routes = [

    {
        path: '/recurring-invoices',
        components: {
            default: Index,
            //'sidebar-left': ComponentSidebarLeft,
            //'sidebar-right': ComponentSidebarRight
        },
        meta: {
            title: 'Accounting :: Sales :: Recurring Invoices',
            metaTags: [
                {
                    name: 'description',
                    content: 'Recurring Invoices'
                },
                {
                    property: 'og:description',
                    content: 'Recurring Invoices'
                }
            ]
        }
    },
    {
        path: '/recurring-invoices/create',
        components: {
            default: Form,
            //'sidebar-left': ComponentSidebarLeft,
            //'sidebar-right': ComponentSidebarRight
        },
        meta: {
            title: 'Accounting :: Sales :: Recurring Invoice :: Create',
            metaTags: [
                {
                    name: 'description',
                    content: 'Create Recurring Invoice'
                },
                {
                    property: 'og:description',
                    content: 'Create Recurring Invoice'
                }
            ]
        }
    },
    {
        path: '/recurring-invoices/:id',
        components: {
            default: Show,
            'sidebar-left': SideBarLeft,
            'sidebar-right': SideBarRight
        },
        meta: {
            title: 'Accounting :: Sales :: Recurring Invoice',
            metaTags: [
                {
                    name: 'description',
                    content: 'Recurring Invoice'
                },
                {
                    property: 'og:description',
                    content: 'Recurring Invoice'
                }
            ]
        }
    },
    {
        path: '/recurring-invoices/:id/copy',
        components: {
            default: Form,
        },
        meta: {
            title: 'Accounting :: Sales :: Recurring Invoice :: Copy',
            metaTags: [
                {
                    name: 'description',
                    content: 'Copy Recurring Invoice'
                },
                {
                    property: 'og:description',
                    content: 'Copy Recurring Invoice'
                }
            ]
        }
    },
    {
        path: '/recurring-invoices/:id/edit',
        components: {
            default: Form,
        },
        meta: {
            title: 'Accounting :: Sales :: Recurring Invoice :: Edit',
            metaTags: [
                {
                    name: 'description',
                    content: 'Edit Recurring Invoice'
                },
                {
                    property: 'og:description',
                    content: 'Edit Recurring Invoice'
                }
            ]
        }
    },

    {
        path: '/estimates/:id/process/recurring-invoices',
        components: {
            default: Form,
        },
        meta: {
            title: 'Accounting :: Sales :: Estimate :: Process',
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

]

export default routes
