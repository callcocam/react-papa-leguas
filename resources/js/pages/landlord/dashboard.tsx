import React from 'react';
import { Head, usePage } from '@inertiajs/react';
import AuthLayout from '../../layouts/react-auth-layout';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    Users, 
    Building2, 
    Settings, 
    Plus,
    Activity,
    Database,
    Shield,
    Globe
} from 'lucide-react';

interface DashboardProps {
    auth?: {
        landlord?: {
            id: string;
            name: string;
            email: string;
        };
    };
    stats?: {
        totalTenants: number;
        totalUsers: number;
        activeConnections: number;
    };
    [key: string]: any;
}

export default function LandlordDashboard() {
    const { auth, stats } = usePage<DashboardProps>().props;

    const dashboardCards = [
        {
            title: 'Total de Tenants',
            value: stats?.totalTenants || 0,
            description: 'Empresas cadastradas',
            icon: Building2,
            color: 'text-blue-600',
            bgColor: 'bg-blue-50',
        },
        {
            title: 'Total de Usuários',
            value: stats?.totalUsers || 0,
            description: 'Usuários ativos',
            icon: Users,
            color: 'text-green-600',
            bgColor: 'bg-green-50',
        },
        {
            title: 'Conexões Ativas',
            value: stats?.activeConnections || 0,
            description: 'Sessões online',
            icon: Activity,
            color: 'text-orange-600',
            bgColor: 'bg-orange-50',
        },
    ];

    const quickActions = [
        {
            title: 'Novo Tenant',
            description: 'Cadastrar nova empresa',
            icon: Plus,
            href: '/landlord/tenants/create',
            color: 'bg-blue-600 hover:bg-blue-700',
        },
        {
            title: 'Gerenciar Usuários',
            description: 'Administrar usuários do sistema',
            icon: Users,
            href: '/landlord/users',
            color: 'bg-green-600 hover:bg-green-700',
        },
        {
            title: 'Configurações',
            description: 'Configurar sistema',
            icon: Settings,
            href: '/landlord/settings',
            color: 'bg-purple-600 hover:bg-purple-700',
        },
        {
            title: 'Permissões',
            description: 'Gerenciar ACL',
            icon: Shield,
            href: '/landlord/permissions',
            color: 'bg-orange-600 hover:bg-orange-700',
        },
    ];

    return (
        <AuthLayout>
            <Head title="Dashboard - Papa Leguas Admin" />
            
            <div className="min-h-screen bg-gray-50">
                {/* Header */}
                <div className="bg-white shadow-sm border-b">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                        <div className="flex justify-between items-center">
                            <div className="flex items-center space-x-4">
                                <div className="text-2xl">🦘</div>
                                <div>
                                    <h1 className="text-2xl font-bold text-gray-900">
                                        Papa Leguas Admin
                                    </h1>
                                    <p className="text-sm text-gray-600">
                                        Bem-vindo, {auth?.landlord?.name || 'Administrador'}
                                    </p>
                                </div>
                            </div>
                            
                            <div className="flex items-center space-x-4">
                                <Badge variant="secondary" className="flex items-center">
                                    <Globe className="w-3 h-3 mr-1" />
                                    Sistema Multi-tenant
                                </Badge>
                                
                                <Button
                                    variant="outline"
                                    onClick={() => {
                                        // Logout logic
                                        window.location.href = '/landlord/logout';
                                    }}
                                >
                                    Sair
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Main Content */}
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    {/* Stats Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        {dashboardCards.map((card, index) => {
                            const Icon = card.icon;
                            return (
                                <Card key={index}>
                                    <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                        <CardTitle className="text-sm font-medium">
                                            {card.title}
                                        </CardTitle>
                                        <div className={`p-2 rounded-full ${card.bgColor}`}>
                                            <Icon className={`h-4 w-4 ${card.color}`} />
                                        </div>
                                    </CardHeader>
                                    <CardContent>
                                        <div className="text-2xl font-bold">{card.value}</div>
                                        <p className="text-xs text-muted-foreground">
                                            {card.description}
                                        </p>
                                    </CardContent>
                                </Card>
                            );
                        })}
                    </div>

                    {/* Quick Actions */}
                    <Card className="mb-8">
                        <CardHeader>
                            <CardTitle>Ações Rápidas</CardTitle>
                            <CardDescription>
                                Principais funcionalidades do sistema
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                {quickActions.map((action, index) => {
                                    const Icon = action.icon;
                                    return (
                                        <Button
                                            key={index}
                                            variant="outline"
                                            className="h-auto p-6 flex flex-col items-center space-y-2 hover:shadow-md transition-shadow"
                                            onClick={() => {
                                                // Navigation logic
                                                window.location.href = action.href;
                                            }}
                                        >
                                            <div className={`p-3 rounded-full text-white ${action.color}`}>
                                                <Icon className="h-6 w-6" />
                                            </div>
                                            <div className="text-center">
                                                <div className="font-semibold">{action.title}</div>
                                                <div className="text-xs text-muted-foreground">
                                                    {action.description}
                                                </div>
                                            </div>
                                        </Button>
                                    );
                                })}
                            </div>
                        </CardContent>
                    </Card>

                    {/* System Status */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {/* System Info */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <Database className="mr-2 h-5 w-5" />
                                    Status do Sistema
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex justify-between items-center">
                                    <span className="text-sm">Banco de Dados</span>
                                    <Badge variant="default" className="bg-green-100 text-green-800">
                                        Conectado
                                    </Badge>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm">Cache</span>
                                    <Badge variant="default" className="bg-green-100 text-green-800">
                                        Ativo
                                    </Badge>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm">Multi-tenancy</span>
                                    <Badge variant="default" className="bg-green-100 text-green-800">
                                        Funcionando
                                    </Badge>
                                </div>
                                <div className="flex justify-between items-center">
                                    <span className="text-sm">ACL</span>
                                    <Badge variant="default" className="bg-green-100 text-green-800">
                                        Ativo
                                    </Badge>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Recent Activity */}
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center">
                                    <Activity className="mr-2 h-5 w-5" />
                                    Atividade Recente
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="space-y-3">
                                    <div className="flex items-center space-x-3">
                                        <div className="w-2 h-2 bg-green-500 rounded-full"></div>
                                        <div className="flex-1">
                                            <p className="text-sm">Sistema inicializado com sucesso</p>
                                            <p className="text-xs text-muted-foreground">
                                                Há poucos minutos
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-center space-x-3">
                                        <div className="w-2 h-2 bg-blue-500 rounded-full"></div>
                                        <div className="flex-1">
                                            <p className="text-sm">Configurações carregadas</p>
                                            <p className="text-xs text-muted-foreground">
                                                Há 5 minutos
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div className="flex items-center space-x-3">
                                        <div className="w-2 h-2 bg-orange-500 rounded-full"></div>
                                        <div className="flex-1">
                                            <p className="text-sm">Aguardando primeiro tenant</p>
                                            <p className="text-xs text-muted-foreground">
                                                Status atual
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AuthLayout>
    );
}
