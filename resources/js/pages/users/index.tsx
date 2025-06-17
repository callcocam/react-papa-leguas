import React, { useState, useMemo } from 'react';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { 
    Search, 
    Plus, 
    Download, 
    Eye, 
    Edit, 
    Trash2,
    MoreHorizontal,
    ChevronDown,
    Users,
    Activity,
    TrendingUp,
    Filter
} from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';

interface UsersProps {
    table?: {
        data?: any[];
        total?: number;
        perPage?: number;
        currentPage?: number;
        lastPage?: number;
    };
    title?: string;
    auth?: {
        user?: any;
    };
}

interface UserItem {
    id: number;
    name: string;
    email?: string;
    status: 'active' | 'inactive' | 'pending' | 'cancelled';
    created_at: string;
    updated_at: string;
    [key: string]: any;
}

export default function UsersIndex({ table, title = 'Users', auth }: UsersProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [selectedItems, setSelectedItems] = useState<number[]>([]);
    const [showDebug, setShowDebug] = useState(false);

    // Dados mock como fallback
    const mockData: UserItem[] = [
        {
            id: 1,
            name: 'Item de Exemplo 1',
            email: 'exemplo1@email.com',
            status: 'active',
            created_at: '2024-01-15T10:30:00Z',
            updated_at: '2024-01-15T10:30:00Z',
        },
        {
            id: 2,
            name: 'Item de Exemplo 2',
            email: 'exemplo2@email.com',
            status: 'inactive',
            created_at: '2024-01-14T09:20:00Z',
            updated_at: '2024-01-14T09:20:00Z',
        },
        {
            id: 3,
            name: 'Item de Exemplo 3',
            email: 'exemplo3@email.com',
            status: 'pending',
            created_at: '2024-01-13T14:45:00Z',
            updated_at: '2024-01-13T14:45:00Z',
        },
    ];

    // Usar dados do backend ou fallback para mock
    const items: UserItem[] = table?.data || mockData;

    // Filtrar dados com base na busca
    const filteredItems = useMemo(() => {
        if (!searchTerm) return items;
        
        return items.filter(item =>
            item.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
            item.email?.toLowerCase().includes(searchTerm.toLowerCase())
        );
    }, [items, searchTerm]);

    // Estatísticas
    const stats = useMemo(() => {
        const total = items.length;
        const active = items.filter(item => item.status === 'active').length;
        const filtered = filteredItems.length;

        return {
            total,
            active,
            filtered,
            inactive: total - active,
        };
    }, [items, filteredItems]);

    // Manipular seleção
    const handleSelectAll = (checked: boolean) => {
        if (checked) {
            setSelectedItems(filteredItems.map(item => item.id));
        } else {
            setSelectedItems([]);
        }
    };

    const handleSelectItem = (itemId: number, checked: boolean) => {
        if (checked) {
            setSelectedItems(prev => [...prev, itemId]);
        } else {
            setSelectedItems(prev => prev.filter(id => id !== itemId));
        }
    };

    // Formatar data
    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    };

    // Obter cor do badge de status
    const getStatusBadgeColor = (status: string) => {
        const colors = {
            active: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            inactive: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
            pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            cancelled: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        };
        return colors[status as keyof typeof colors] || colors.inactive;
    };

    // Traduzir status
    const translateStatus = (status: string) => {
        const translations = {
            active: 'Ativo',
            inactive: 'Inativo',
            pending: 'Pendente',
            cancelled: 'Cancelado',
        };
        return translations[status as keyof typeof translations] || status;
    };

    // Ações
    const handleCreate = () => {
        router.visit(route('users.create'));
    };

    const handleExport = () => {
        router.visit(route('users.export'));
    };

    const handleView = (id: number) => {
        router.visit(route('users.show', id));
    };

    const handleEdit = (id: number) => {
        router.visit(route('users.edit', id));
    };

    const handleDelete = (id: number) => {
        if (confirm('Tem certeza que deseja excluir este item?')) {
            router.delete(route('users.destroy', id));
        }
    };

    const handleBulkDelete = () => {
        if (selectedItems.length === 0) return;
        
        if (confirm(`Tem certeza que deseja excluir ${selectedItems.length} item(ns) selecionado(s)?`)) {
            router.post(route('users.bulk-delete'), {
                ids: selectedItems
            });
        }
    };

    return (
        <>
            <Head title={title} />
            
            <div className="space-y-6">
                {/* Cabeçalho */}
                <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">{title}</h1>
                        <p className="text-muted-foreground">
                            Gerencie users do sistema
                        </p>
                    </div>
                    
                    <div className="flex gap-2">
                        <Button onClick={handleExport} variant="outline">
                            <Download className="mr-2 h-4 w-4" />
                            Exportar
                        </Button>
                        <Button onClick={handleCreate}>
                            <Plus className="mr-2 h-4 w-4" />
                            Novo User
                        </Button>
                    </div>
                </div>

                {/* Estatísticas */}
                <div className="grid gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Ativos</CardTitle>
                            <Activity className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{stats.active}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Inativos</CardTitle>
                            <TrendingUp className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-gray-600">{stats.inactive}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Filtrados</CardTitle>
                            <Filter className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-blue-600">{stats.filtered}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filtros e Busca */}
                <Card>
                    <CardHeader>
                        <div className="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                    <Input
                                        placeholder="Buscar users..."
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                        className="pl-10"
                                    />
                                </div>
                            </div>
                            
                            {selectedItems.length > 0 && (
                                <div className="flex gap-2">
                                    <Button
                                        variant="destructive"
                                        size="sm"
                                        onClick={handleBulkDelete}
                                    >
                                        <Trash2 className="mr-2 h-4 w-4" />
                                        Excluir {selectedItems.length} selecionado(s)
                                    </Button>
                                </div>
                            )}
                        </div>
                    </CardHeader>
                    
                    <CardContent>
                        {/* Tabela */}
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead>
                                    <tr className="border-b">
                                        <th className="text-left p-4">
                                            <Checkbox
                                                checked={selectedItems.length === filteredItems.length && filteredItems.length > 0}
                                                onCheckedChange={handleSelectAll}
                                            />
                                        </th>
                                        <th className="text-left p-4 font-medium">Nome</th>
                                        <th className="text-left p-4 font-medium">Email</th>
                                        <th className="text-left p-4 font-medium">Status</th>
                                        <th className="text-left p-4 font-medium">Criado em</th>
                                        <th className="text-right p-4 font-medium">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {filteredItems.map((item) => (
                                        <tr key={item.id} className="border-b hover:bg-muted/50">
                                            <td className="p-4">
                                                <Checkbox
                                                    checked={selectedItems.includes(item.id)}
                                                    onCheckedChange={(checked) => 
                                                        handleSelectItem(item.id, checked as boolean)
                                                    }
                                                />
                                            </td>
                                            <td className="p-4 font-medium">{item.name}</td>
                                            <td className="p-4 text-muted-foreground">{item.email}</td>
                                            <td className="p-4">
                                                <Badge className={getStatusBadgeColor(item.status)}>
                                                    {translateStatus(item.status)}
                                                </Badge>
                                            </td>
                                            <td className="p-4 text-muted-foreground">
                                                {formatDate(item.created_at)}
                                            </td>
                                            <td className="p-4 text-right">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="sm">
                                                            <MoreHorizontal className="h-4 w-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem onClick={() => handleView(item.id)}>
                                                            <Eye className="mr-2 h-4 w-4" />
                                                            Visualizar
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem onClick={() => handleEdit(item.id)}>
                                                            <Edit className="mr-2 h-4 w-4" />
                                                            Editar
                                                        </DropdownMenuItem>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem 
                                                            onClick={() => handleDelete(item.id)}
                                                            className="text-destructive"
                                                        >
                                                            <Trash2 className="mr-2 h-4 w-4" />
                                                            Excluir
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                            
                            {filteredItems.length === 0 && (
                                <div className="text-center py-8 text-muted-foreground">
                                    Nenhum item encontrado.
                                </div>
                            )}
                        </div>
                    </CardContent>
                </Card>

                {/* Debug Section */}
                <Collapsible open={showDebug} onOpenChange={setShowDebug}>
                    <CollapsibleTrigger asChild>
                        <Button variant="outline" size="sm">
                            <ChevronDown className="mr-2 h-4 w-4" />
                            Debug Info
                        </Button>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <Card className="mt-4">
                            <CardHeader>
                                <CardTitle className="text-sm">Informações de Debug</CardTitle>
                                <CardDescription>
                                    Dados recebidos do backend
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <pre className="text-xs bg-muted p-4 rounded overflow-auto">
                                    {JSON.stringify({ table, title, auth }, null, 2)}
                                </pre>
                            </CardContent>
                        </Card>
                    </CollapsibleContent>
                </Collapsible>
            </div>
        </>
    );
}