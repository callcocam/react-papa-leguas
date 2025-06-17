import AppLayout from '../../layouts/react-app-layout'
import { type BreadcrumbItem } from '../../types'
import { Head } from '@inertiajs/react'
import { PapaLeguasTable } from '../../components/table'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'CRUD',
        href: '/crud',
    },
    {
        title: 'Lista',
    }
]

interface CrudIndexProps {
    data?: any[]
    columns?: any[]
    filters?: any[]
    actions?: any[]
    permissions?: any
    pagination?: any
    config?: any
}

export default function CrudIndex({ 
    data = [], 
    columns = [], 
    filters = [], 
    actions = [], 
    permissions = {},
    pagination,
    config = {}
}: CrudIndexProps) {
    return (
        <AppLayout 
            breadcrumbs={breadcrumbs}
            title="Lista de Registros"
        >
            <Head title="CRUD - Lista" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            Lista de Registros
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-2">
                            Gerencie seus registros de forma eficiente
                        </p>
                    </div>
                </div>

                {/* Tabela Principal */}
                <PapaLeguasTable
                    data={data}
                    columns={columns}
                    filters={filters}
                    actions={actions}
                    permissions={permissions}
                    pagination={pagination}
                    config={config}
                />
            </div>
        </AppLayout>
    )
}
