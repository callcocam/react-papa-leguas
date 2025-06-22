import React, { useState, useEffect } from 'react';
import { Head, useForm, router } from '@inertiajs/react';
import AppLayout from '../../../layouts/react-app-layout';
import WorkflowForm, { type WorkflowFormData } from './components/WorkflowForm';
//@ts-ignore
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
//@ts-ignore
import { Badge } from '@/components/ui/badge';
//@ts-ignore
import { Alert, AlertDescription } from '@/components/ui/alert';
//@ts-ignore
import { Button } from '@/components/ui/button';
import { History, AlertTriangle } from 'lucide-react';

import type { BreadcrumbItem } from '../../../types';

interface EditWorkflowProps {
    mode: 'edit';
    record: WorkflowFormData & {
        id: string;
        created_at: string;
        updated_at: string;
        usage_count?: number; // Quantos itens usam este workflow
        [key: string]: any;
    };
    form_data: WorkflowFormData;
    user: any;
    permissions: any[];
    request: Record<string, any>;
}

export default function EditWorkflow({ 
    mode, 
    record, 
    form_data, 
    user, 
    permissions, 
    request 
}: EditWorkflowProps) {
    console.log('EditWorkflow - Props recebidas:', { mode, record, form_data, user, permissions, request });
    
    const breadcrumbs: BreadcrumbItem[] = [
        { title: 'Dashboard', href: '/dashboard' },
        { title: 'Workflows', href: '/admin/workflows' },
        { title: record.name }
    ];

    const { isDirty } = useForm(form_data);
    const [processing, setProcessing] = useState(false);
    const [errors, setErrors] = useState<Record<string, string>>({});
    const [hasUnsavedChanges, setHasUnsavedChanges] = useState(false);

    // Detectar mudanças não salvas
    useEffect(() => {
        setHasUnsavedChanges(isDirty);
    }, [isDirty]);

    // Função para submeter o formulário
    const handleSubmit = (data: WorkflowFormData) => {
        setProcessing(true);
        setErrors({});

        router.put(`/admin/workflows/${record.id}`, data, {
            onSuccess: () => {
                setProcessing(false);
                setHasUnsavedChanges(false);
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

    // Resetar formulário
    const handleReset = () => {
        window.location.reload();
    };

    // Componente de histórico customizado
    const HistoryContent = () => (
        <Card>
            <CardHeader>
                <CardTitle className="flex items-center gap-2">
                    <History className="w-5 h-5" />
                    Histórico de Alterações
                </CardTitle>
            </CardHeader>
            <CardContent>
                <div className="space-y-4">
                    {/* Informações do workflow */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div>
                            <label className="text-sm font-medium text-gray-600 dark:text-gray-400">ID do Workflow</label>
                            <p className="text-sm text-gray-900 dark:text-gray-100">{record.id}</p>
                        </div>
                        <div>
                            <label className="text-sm font-medium text-gray-600 dark:text-gray-400">Criado em</label>
                            <p className="text-sm text-gray-900 dark:text-gray-100">
                                {new Date(record.created_at).toLocaleDateString('pt-BR', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}
                            </p>
                        </div>
                        <div>
                            <label className="text-sm font-medium text-gray-600 dark:text-gray-400">Última atualização</label>
                            <p className="text-sm text-gray-900 dark:text-gray-100">
                                {new Date(record.updated_at).toLocaleDateString('pt-BR', {
                                    year: 'numeric',
                                    month: 'long',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit'
                                })}
                            </p>
                        </div>
                    </div>

                    {/* Informações do usuário logado */}
                    {user && (
                        <div className="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div className="flex items-center gap-2">
                                <Badge variant="outline">
                                    Editando como: {user.name || user.email}
                                </Badge>
                            </div>
                            <p className="text-sm text-green-700 dark:text-green-300 mt-2">
                                Usuário atual com permissões para edição.
                            </p>
                        </div>
                    )}

                    {record.usage_count !== undefined && (
                        <div className="p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <div className="flex items-center gap-2">
                                <Badge variant="secondary">
                                    {record.usage_count} itens usando este workflow
                                </Badge>
                            </div>
                            <p className="text-sm text-blue-700 dark:text-blue-300 mt-2">
                                {record.usage_count > 0 
                                    ? "Este workflow está sendo usado. Tenha cuidado ao fazer alterações que podem afetar itens existentes."
                                    : "Este workflow ainda não está sendo usado e pode ser modificado livremente."
                                }
                            </p>
                        </div>
                    )}

                    {/* Parâmetros da requisição */}
                    {Object.keys(request).length > 0 && (
                        <div className="p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                            <label className="text-sm font-medium text-gray-600 dark:text-gray-400">Parâmetros da Requisição</label>
                            <pre className="text-xs text-gray-700 dark:text-gray-300 mt-2 overflow-auto">
                                {JSON.stringify(request, null, 2)}
                            </pre>
                        </div>
                    )}

                    <Alert>
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription>
                            Funcionalidade de histórico detalhado será implementada em versão futura.
                            As informações básicas estão disponíveis acima.
                        </AlertDescription>
                    </Alert>

                    {hasUnsavedChanges && (
                        <div className="flex items-center gap-3 pt-4 border-t">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleReset}
                            >
                                Descartar Alterações
                            </Button>
                        </div>
                    )}
                </div>
            </CardContent>
        </Card>
    );

    return (
        <AppLayout breadcrumbs={breadcrumbs} title={`Editar: ${record.name}`}>
            <Head title={`Editar: ${record.name}`} />

            <WorkflowForm
                mode={mode}
                initialData={form_data}
                onSubmit={handleSubmit}
                onCancel={handleCancel}
                processing={processing}
                errors={errors}
                showHistoryTab={true}
                hasUnsavedChanges={hasUnsavedChanges}
            >
                <HistoryContent />
            </WorkflowForm>
        </AppLayout>
    );
} 