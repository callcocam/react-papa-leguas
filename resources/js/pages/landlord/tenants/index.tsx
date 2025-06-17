import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthLayout from '../../../layouts/react-app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { 
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { 
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { 
    Plus, 
    Search, 
    Filter,
    MoreHorizontal,
    Edit,
    Trash2,
    Eye,
    Building2,
    Users,
    MapPin,
    Phone,
    Mail,
    Globe,
    Download,
    ToggleLeft,
    ToggleRight
} from 'lucide-react';

interface Tenant {
    id: string;
    name: string;
    email: string;
    document?: string;
    phone?: string;
    domain?: string;
    status: 'draft' | 'published';
    status_label?: string;
    description?: string;
    is_primary: boolean;
    users_count: number;
    created_at: string;
    user?: {
        name: string;
        email: string;
    };
    default_address?: {
        street: string;
        city: string;
        state: string;
    };
}

interface Props {
    tenants: {
        data: Tenant[];
        links: any[];
        meta: any;
    };
    filters: {
        search?: string;
        status?: string;
        sort?: string;
        direction?: string;
    };
    stats: {
        total: number;
        active: number;
        draft: number;
    };
    status_options: {
        value: string;
        label: string;
        color: string;
    }[];
}

export default function TenantsIndex({ tenants, filters, stats, status_options }: Props) {
    const [selectedTenants, setSelectedTenants] = useState<string[]>([]);
    const [searchTerm, setSearchTerm] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('landlord.tenants.index'), {
            ...filters,
            search: searchTerm,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleStatusFilter = (status: string) => {
        setStatusFilter(status);
        router.get(route('landlord.tenants.index'), {
            ...filters,
            status: status || undefined,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleSort = (field: string) => {
        const direction = filters.sort === field && filters.direction === 'asc' ? 'desc' : 'asc';
        router.get(route('landlord.tenants.index'), {
            ...filters,
            sort: field,
            direction,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const toggleSelectAll = () => {
        if (selectedTenants.length === tenants.data.length) {
            setSelectedTenants([]);
        } else {
            setSelectedTenants(tenants.data.map(tenant => tenant.id));
        }
    };

    const toggleSelectTenant = (tenantId: string) => {
        setSelectedTenants(prev => 
            prev.includes(tenantId) 
                ? prev.filter(id => id !== tenantId)
                : [...prev, tenantId]
        );
    };

    const handleBulkDelete = () => {
        if (selectedTenants.length === 0) return;
        
        if (confirm(`Tem certeza que deseja excluir ${selectedTenants.length} tenant(s)?`)) {
            router.post(route('landlord.tenants.bulk-destroy'), {
                ids: selectedTenants,
            }, {
                onSuccess: () => setSelectedTenants([]),
            });
        }
    };

    const handleToggleStatus = (tenant: Tenant) => {
        router.patch(route('landlord.tenants.toggle-status', tenant.id), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const getStatusBadgeVariant = (status: string) => {
        switch (status) {
            case 'published':
                return 'success';
            case 'draft':
                return 'secondary';
            default:
                return 'secondary';
        }
    };

    return (
        <AuthLayout>
            <Head title="Tenants" />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex justify-between items-center">
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            Tenants
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400">
                            Gerencie as empresas do sistema
                        </p>
                    </div>
                    
                    <Link href={route('landlord.tenants.create')}>
                        <Button className="gap-2">
                            <Plus className="w-4 h-4" />
                            Novo Tenant
                        </Button>
                    </Link>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total de Tenants</CardTitle>
                            <Building2 className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Ativos</CardTitle>
                            <Users className="h-4 w-4 text-green-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-green-600">{stats.active}</div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Rascunhos</CardTitle>
                            <Users className="h-4 w-4 text-gray-600" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-gray-600">{stats.draft}</div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filters */}
                <Card>
                    <CardContent className="pt-6">
                        <div className="flex flex-col sm:flex-row gap-4">
                            <form onSubmit={handleSearch} className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-3 h-4 w-4 text-gray-400" />
                                    <Input
                                        placeholder="Buscar por nome, email ou documento..."
                                        className="pl-10"
                                        value={searchTerm}
                                        onChange={(e) => setSearchTerm(e.target.value)}
                                    />
                                </div>
                            </form>
                            
                            <div className="flex gap-2">
                                <Select value={statusFilter} onValueChange={handleStatusFilter}>
                                    <SelectTrigger className="w-40">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent> 
                                        {status_options.map((option) => (
                                            <SelectItem key={option.value} value={option.value}>
                                                {option.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                
                                {selectedTenants.length > 0 && (
                                    <Button 
                                        variant="destructive" 
                                        onClick={handleBulkDelete}
                                        className="gap-2"
                                    >
                                        <Trash2 className="w-4 h-4" />
                                        Excluir ({selectedTenants.length})
                                    </Button>
                                )}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                {/* Table */}
                <Card>
                    <CardContent className="p-0">
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-12">
                                            <Checkbox 
                                                checked={selectedTenants.length === tenants.data.length && tenants.data.length > 0}
                                                onCheckedChange={toggleSelectAll}
                                            />
                                        </TableHead>
                                        <TableHead 
                                            className="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                                            onClick={() => handleSort('name')}
                                        >
                                            Nome
                                        </TableHead>
                                        <TableHead 
                                            className="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                                            onClick={() => handleSort('email')}
                                        >
                                            Email
                                        </TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Usuários</TableHead>
                                        <TableHead>Endereço</TableHead>
                                        <TableHead 
                                            className="cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800"
                                            onClick={() => handleSort('created_at')}
                                        >
                                            Criado em
                                        </TableHead>
                                        <TableHead className="w-12"></TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {tenants.data.map((tenant) => (
                                        <TableRow key={tenant.id} className="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                            <TableCell>
                                                <Checkbox 
                                                    checked={selectedTenants.includes(tenant.id)}
                                                    onCheckedChange={() => toggleSelectTenant(tenant.id)}
                                                />
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-3">
                                                    <div className="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                        {tenant.name.charAt(0).toUpperCase()}
                                                    </div>
                                                    <div>
                                                        <div className="font-medium text-gray-900 dark:text-gray-100">
                                                            {tenant.name}
                                                        </div>
                                                        {tenant.document && (
                                                            <div className="text-sm text-gray-500">
                                                                {tenant.document}
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="space-y-1">
                                                    <div className="flex items-center gap-1 text-sm">
                                                        <Mail className="w-3 h-3 text-gray-400" />
                                                        {tenant.email}
                                                    </div>
                                                    {tenant.phone && (
                                                        <div className="flex items-center gap-1 text-sm text-gray-500">
                                                            <Phone className="w-3 h-3 text-gray-400" />
                                                            {tenant.phone}
                                                        </div>
                                                    )}
                                                    {tenant.domain && (
                                                        <div className="flex items-center gap-1 text-sm text-gray-500">
                                                            <Globe className="w-3 h-3 text-gray-400" />
                                                            <a href={tenant.domain} target="_blank" rel="noopener noreferrer" className="hover:text-blue-600">
                                                                {tenant.domain.replace(/^https?:\/\//, '')}
                                                            </a>
                                                        </div>
                                                    )}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-2">
                                                    <Badge variant={getStatusBadgeVariant(tenant.status) as any}>
                                                        {tenant.status_label || tenant.status}
                                                    </Badge>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => handleToggleStatus(tenant)}
                                                        className="p-1 h-auto"
                                                    >
                                                        {tenant.status === 'published' ? (
                                                            <ToggleRight className="w-4 h-4 text-green-600" />
                                                        ) : (
                                                            <ToggleLeft className="w-4 h-4 text-gray-400" />
                                                        )}
                                                    </Button>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex items-center gap-1 text-sm">
                                                    <Users className="w-3 h-3 text-gray-400" />
                                                    {tenant.users_count}
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                {tenant.default_address && (
                                                    <div className="flex items-center gap-1 text-sm text-gray-500">
                                                        <MapPin className="w-3 h-3 text-gray-400" />
                                                        {tenant.default_address.city}, {tenant.default_address.state}
                                                    </div>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-sm text-gray-500">
                                                {new Date(tenant.created_at).toLocaleDateString('pt-BR')}
                                            </TableCell>
                                            <TableCell>
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button variant="ghost" size="sm" className="p-2">
                                                            <MoreHorizontal className="w-4 h-4" />
                                                        </Button>
                                                    </DropdownMenuTrigger>
                                                    <DropdownMenuContent align="end">
                                                        <DropdownMenuItem asChild>
                                                            <Link href={route('landlord.tenants.show', tenant.id)}>
                                                                <Eye className="w-4 h-4 mr-2" />
                                                                Visualizar
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem asChild>
                                                            <Link href={route('landlord.tenants.edit', tenant.id)}>
                                                                <Edit className="w-4 h-4 mr-2" />
                                                                Editar
                                                            </Link>
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem 
                                                            className="text-red-600"
                                                            onClick={() => {
                                                                if (confirm('Tem certeza que deseja excluir este tenant?')) {
                                                                    router.delete(route('landlord.tenants.destroy', tenant.id));
                                                                }
                                                            }}
                                                        >
                                                            <Trash2 className="w-4 h-4 mr-2" />
                                                            Excluir
                                                        </DropdownMenuItem>
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                        
                        {tenants.data.length === 0 && (
                            <div className="text-center py-12">
                                <Building2 className="w-12 h-12 text-gray-400 mx-auto mb-4" />
                                <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                    Nenhum tenant encontrado
                                </h3>
                                <p className="text-gray-500 mb-6">
                                    {filters.search ? 'Tente ajustar sua busca.' : 'Comece criando seu primeiro tenant.'}
                                </p>
                                {!filters.search && (
                                    <Link href={route('landlord.tenants.create')}>
                                        <Button>
                                            <Plus className="w-4 h-4 mr-2" />
                                            Criar Tenant
                                        </Button>
                                    </Link>
                                )}
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Pagination */}
                {tenants.links && tenants.links.length > 3 && (
                    <div className="flex justify-center">
                        <div className="flex gap-1">
                            {tenants.links.map((link, index) => (
                                <Button
                                    key={index}
                                    variant={link.active ? "default" : "outline"}
                                    size="sm"
                                    disabled={!link.url}
                                    onClick={() => link.url && router.get(link.url)}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AuthLayout>
    );
}
