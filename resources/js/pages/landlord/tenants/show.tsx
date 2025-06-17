import React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AuthLayout from '../../../layouts/react-app-layout';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { 
    ArrowLeft, 
    Building2, 
    MapPin, 
    Phone, 
    Mail, 
    Globe, 
    Users, 
    Settings,
    Edit,
    Trash2,
    Calendar,
    Hash,
    FileText,
    ToggleLeft,
    ToggleRight
} from 'lucide-react';

interface Address {
    street: string;
    number: string;
    complement: string;
    neighborhood: string;
    city: string;
    state: string;
    zip_code: string;
    country: string;
}

interface User {
    id: string;
    name: string;
    email: string;
}

interface Role {
    id: string;
    name: string;
    description?: string;
}

interface Tenant {
    id: string;
    name: string;
    email: string;
    document?: string;
    phone?: string;
    domain?: string;
    status: string;
    status_label: string;
    description?: string;
    is_primary: boolean;
    created_at: string;
    updated_at: string;
    user?: User;
    addresses?: Address[];
    users?: User[];
    roles?: Role[];
}

interface Props {
    tenant: Tenant;
    stats: {
        users_count: number;
        roles_count: number;
        addresses_count: number;
    };
}

export default function TenantsShow({ tenant, stats }: Props) {
    const handleToggleStatus = () => {
        router.patch(route('landlord.tenants.toggle-status', tenant.id), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    const handleDelete = () => {
        if (confirm('Tem certeza que deseja excluir este tenant? Esta ação não pode ser desfeita.')) {
            router.delete(route('landlord.tenants.destroy', tenant.id));
        }
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
            <Head title={tenant.name} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div className="flex items-center gap-4">
                        <Link href={route('landlord.tenants.index')}>
                            <Button variant="outline" size="sm">
                                <ArrowLeft className="w-4 h-4 mr-2" />
                                Voltar
                            </Button>
                        </Link>
                        
                        <div>
                            <div className="flex items-center gap-3">
                                <div className="flex-shrink-0 w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center text-white text-lg font-medium">
                                    {tenant.name.charAt(0).toUpperCase()}
                                </div>
                                <div>
                                    <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                        {tenant.name}
                                    </h1>
                                    <div className="flex items-center gap-2">
                                        <Badge variant={getStatusBadgeVariant(tenant.status) as any}>
                                            {tenant.status_label}
                                        </Badge>
                                        {tenant.is_primary && (
                                            <Badge variant="outline">Principal</Badge>
                                        )}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            onClick={handleToggleStatus}
                            className="gap-2"
                        >
                            {tenant.status === 'published' ? (
                                <ToggleRight className="w-4 h-4 text-green-600" />
                            ) : (
                                <ToggleLeft className="w-4 h-4 text-gray-400" />
                            )}
                            {tenant.status === 'published' ? 'Desativar' : 'Ativar'}
                        </Button>
                        
                        <Link href={route('landlord.tenants.edit', tenant.id)}>
                            <Button variant="outline" size="sm" className="gap-2">
                                <Edit className="w-4 h-4" />
                                Editar
                            </Button>
                        </Link>
                        
                        <Button 
                            variant="destructive" 
                            size="sm" 
                            onClick={handleDelete}
                            className="gap-2"
                        >
                            <Trash2 className="w-4 h-4" />
                            Excluir
                        </Button>
                    </div>
                </div>

                {/* Stats Cards */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Usuários</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.users_count}</div>
                            <p className="text-xs text-muted-foreground">
                                Total de usuários
                            </p>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Funções</CardTitle>
                            <Settings className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.roles_count}</div>
                            <p className="text-xs text-muted-foreground">
                                Funções configuradas
                            </p>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Endereços</CardTitle>
                            <MapPin className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.addresses_count}</div>
                            <p className="text-xs text-muted-foreground">
                                Endereços cadastrados
                            </p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {/* Main Information */}
                    <div className="lg:col-span-2 space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <Building2 className="w-5 h-5" />
                                    Informações Básicas
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div className="space-y-3">
                                        <div className="flex items-center gap-2 text-sm">
                                            <Mail className="w-4 h-4 text-gray-400" />
                                            <span className="font-medium">Email:</span>
                                            <a href={`mailto:${tenant.email}`} className="text-blue-600 hover:text-blue-800">
                                                {tenant.email}
                                            </a>
                                        </div>
                                        
                                        {tenant.phone && (
                                            <div className="flex items-center gap-2 text-sm">
                                                <Phone className="w-4 h-4 text-gray-400" />
                                                <span className="font-medium">Telefone:</span>
                                                <span>{tenant.phone}</span>
                                            </div>
                                        )}
                                        
                                        {tenant.domain && (
                                            <div className="flex items-center gap-2 text-sm">
                                                <Globe className="w-4 h-4 text-gray-400" />
                                                <span className="font-medium">Website:</span>
                                                <a 
                                                    href={tenant.domain} 
                                                    target="_blank" 
                                                    rel="noopener noreferrer"
                                                    className="text-blue-600 hover:text-blue-800"
                                                >
                                                    {tenant.domain.replace(/^https?:\/\//, '')}
                                                </a>
                                            </div>
                                        )}
                                    </div>
                                    
                                    <div className="space-y-3">
                                        {tenant.document && (
                                            <div className="flex items-center gap-2 text-sm">
                                                <Hash className="w-4 h-4 text-gray-400" />
                                                <span className="font-medium">Documento:</span>
                                                <span>{tenant.document}</span>
                                            </div>
                                        )}
                                        
                                        <div className="flex items-center gap-2 text-sm">
                                            <Calendar className="w-4 h-4 text-gray-400" />
                                            <span className="font-medium">Criado em:</span>
                                            <span>{new Date(tenant.created_at).toLocaleDateString('pt-BR')}</span>
                                        </div>
                                        
                                        <div className="flex items-center gap-2 text-sm">
                                            <Calendar className="w-4 h-4 text-gray-400" />
                                            <span className="font-medium">Atualizado em:</span>
                                            <span>{new Date(tenant.updated_at).toLocaleDateString('pt-BR')}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                {tenant.description && (
                                    <>
                                        <Separator />
                                        <div>
                                            <div className="flex items-center gap-2 mb-2">
                                                <FileText className="w-4 h-4 text-gray-400" />
                                                <span className="font-medium text-sm">Descrição:</span>
                                            </div>
                                            <p className="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                                {tenant.description}
                                            </p>
                                        </div>
                                    </>
                                )}
                                
                                {tenant.user && (
                                    <>
                                        <Separator />
                                        <div>
                                            <div className="flex items-center gap-2 mb-2">
                                                <Users className="w-4 h-4 text-gray-400" />
                                                <span className="font-medium text-sm">Criado por:</span>
                                            </div>
                                            <div className="text-sm">
                                                <div className="font-medium">{tenant.user.name}</div>
                                                <div className="text-gray-500">{tenant.user.email}</div>
                                            </div>
                                        </div>
                                    </>
                                )}
                            </CardContent>
                        </Card>

                        {/* Addresses */}
                        {tenant.addresses && tenant.addresses.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <MapPin className="w-5 h-5" />
                                        Endereços
                                    </CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-4">
                                        {tenant.addresses.map((address, index) => (
                                            <div key={index} className="p-4 border rounded-lg">
                                                <div className="space-y-1">
                                                    <div className="font-medium">
                                                        {address.street}, {address.number}
                                                        {address.complement && ` - ${address.complement}`}
                                                    </div>
                                                    <div className="text-sm text-gray-600 dark:text-gray-300">
                                                        {address.neighborhood}
                                                    </div>
                                                    <div className="text-sm text-gray-600 dark:text-gray-300">
                                                        {address.city}, {address.state} - {address.zip_code}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        {/* Recent Users */}
                        {tenant.users && tenant.users.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg">Usuários Recentes</CardTitle>
                                    <CardDescription>
                                        Últimos usuários cadastrados
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-3">
                                        {tenant.users.map((user) => (
                                            <div key={user.id} className="flex items-center gap-3">
                                                <div className="flex-shrink-0 w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center text-sm font-medium">
                                                    {user.name.charAt(0).toUpperCase()}
                                                </div>
                                                <div className="flex-1 min-w-0">
                                                    <div className="text-sm font-medium truncate">
                                                        {user.name}
                                                    </div>
                                                    <div className="text-xs text-gray-500 truncate">
                                                        {user.email}
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                        
                                        {stats.users_count > tenant.users.length && (
                                            <div className="text-center pt-2">
                                                <Button variant="outline" size="sm">
                                                    Ver todos ({stats.users_count})
                                                </Button>
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Recent Roles */}
                        {tenant.roles && tenant.roles.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle className="text-lg">Funções</CardTitle>
                                    <CardDescription>
                                        Funções configuradas
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2">
                                        {tenant.roles.map((role) => (
                                            <div key={role.id} className="p-2 border rounded">
                                                <div className="font-medium text-sm">{role.name}</div>
                                                {role.description && (
                                                    <div className="text-xs text-gray-500">{role.description}</div>
                                                )}
                                            </div>
                                        ))}
                                        
                                        {stats.roles_count > tenant.roles.length && (
                                            <div className="text-center pt-2">
                                                <Button variant="outline" size="sm">
                                                    Ver todas ({stats.roles_count})
                                                </Button>
                                            </div>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        {/* Actions */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-lg">Ações</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <Link href={route('landlord.tenants.edit', tenant.id)}>
                                    <Button variant="outline" className="w-full justify-start gap-2">
                                        <Edit className="w-4 h-4" />
                                        Editar Tenant
                                    </Button>
                                </Link>
                                
                                <Button 
                                    variant="outline" 
                                    className="w-full justify-start gap-2"
                                    onClick={handleToggleStatus}
                                >
                                    {tenant.status === 'published' ? (
                                        <ToggleLeft className="w-4 h-4" />
                                    ) : (
                                        <ToggleRight className="w-4 h-4" />
                                    )}
                                    {tenant.status === 'published' ? 'Desativar' : 'Ativar'}
                                </Button>
                                
                                <Button 
                                    variant="destructive" 
                                    className="w-full justify-start gap-2"
                                    onClick={handleDelete}
                                >
                                    <Trash2 className="w-4 h-4" />
                                    Excluir Tenant
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthLayout>
    );
}
