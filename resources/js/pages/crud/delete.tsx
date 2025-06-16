import AppLayout from '../../layouts/react-app-layout';
import { type BreadcrumbItem } from '../../types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Delete', 
    }
];

export default function Delete() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Delete" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
               <h1 className="text-2xl font-bold mb-4">Delete Item</h1>
            </div>
        </AppLayout>
    );
}
