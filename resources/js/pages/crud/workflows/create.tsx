import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/react-app-layout';
import WorkflowForm, { type WorkflowFormData } from './components/WorkflowForm';

import type { BreadcrumbItem } from '../../../types';

interface CreateWorkflowProps {
    mode: 'create';
    form_data: Partial<WorkflowFormData>;
    user: any;
    permissions: any[];
    request: Record<string, any>;
}

export default function CreateWorkflow({ 
    mode, 
    form_data, 
    user, 
    permissions, 
    request 
}: CreateWorkflowProps) {
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});

    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Workflows', href: '/admin/workflows' },
        { title: 'Criar Workflow' }
    ];

    // Função para submeter o formulário
    const handleSubmit = (data: WorkflowFormData) => {
        setProcessing(true);
        setErrors({});

        router.post('/admin/workflows', data, {
            onSuccess: () => {
                setProcessing(false);
            },
            onError: (errors) => {
                setProcessing(false);
                setErrors(errors as Record<string, string>);
            }
        });
    };

    // Função para cancelar e voltar
    const handleCancel = () => {
        window.history.back();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs} title="Criar Workflow">
            <Head title="Criar Workflow" />

            <WorkflowForm
                mode={mode}
                initialData={form_data}
                onSubmit={handleSubmit}
                onCancel={handleCancel}
                processing={processing}
                errors={errors}
            />
        </AppLayout>
    );
}