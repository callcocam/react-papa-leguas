import React from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AuthLayout from '../../../layouts/react-app-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import { ArrowLeft, Building2, MapPin, Save } from 'lucide-react';

interface Tenant {
    id: string;
    name: string;
    email: string;
    document?: string;
    phone?: string;
    domain?: string;
    status: string;
    description?: string;
    is_primary: boolean;
    default_address?: {
        street: string;
        number: string;
        complement: string;
        neighborhood: string;
        city: string;
        state: string;
        zip_code: string;
        country: string;
    };
}

interface Props {
    tenant: Tenant;
    status_options: {
        value: string;
        label: string;
    }[];
}

export default function TenantsEdit({ tenant, status_options }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: tenant.name || '',
        email: tenant.email || '',
        document: tenant.document || '',
        phone: tenant.phone || '',
        domain: tenant.domain || '',
        status: tenant.status || 'draft',
        description: tenant.description || '',
        is_primary: tenant.is_primary || false,
        address: {
            street: tenant.default_address?.street || '',
            number: tenant.default_address?.number || '',
            complement: tenant.default_address?.complement || '',
            neighborhood: tenant.default_address?.neighborhood || '',
            city: tenant.default_address?.city || '',
            state: tenant.default_address?.state || '',
            zip_code: tenant.default_address?.zip_code || '',
            country: tenant.default_address?.country || 'BR',
        },
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('landlord.tenants.update', tenant.id));
    };

    const setAddressField = (field: string, value: string) => {
        setData('address', {
            ...data.address,
            [field]: value,
        });
    };

    return (
        <AuthLayout>
            <Head title={`Editar ${tenant.name}`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center gap-4">
                    <Link href={route('landlord.tenants.index')}>
                        <Button variant="outline" size="sm">
                            <ArrowLeft className="w-4 h-4 mr-2" />
                            Voltar
                        </Button>
                    </Link>
                    
                    <div>
                        <h1 className="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                            Editar Tenant
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400">
                            {tenant.name}
                        </p>
                    </div>
                </div>

                <form onSubmit={handleSubmit} className="space-y-6">
                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {/* Main Information */}
                        <div className="lg:col-span-2 space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <Building2 className="w-5 h-5" />
                                        Informações Básicas
                                    </CardTitle>
                                    <CardDescription>
                                        Dados principais do tenant
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="name">Nome *</Label>
                                            <Input
                                                id="name"
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                error={errors.name}
                                                placeholder="Nome da empresa"
                                            />
                                            {errors.name && (
                                                <p className="text-sm text-red-600">{errors.name}</p>
                                            )}
                                        </div>
                                        
                                        <div className="space-y-2">
                                            <Label htmlFor="email">Email *</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                error={errors.email}
                                                placeholder="email@empresa.com"
                                            />
                                            {errors.email && (
                                                <p className="text-sm text-red-600">{errors.email}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="document">Documento</Label>
                                            <Input
                                                id="document"
                                                value={data.document}
                                                onChange={(e) => setData('document', e.target.value)}
                                                error={errors.document}
                                                placeholder="CNPJ ou CPF"
                                            />
                                            {errors.document && (
                                                <p className="text-sm text-red-600">{errors.document}</p>
                                            )}
                                        </div>
                                        
                                        <div className="space-y-2">
                                            <Label htmlFor="phone">Telefone</Label>
                                            <Input
                                                id="phone"
                                                value={data.phone}
                                                onChange={(e) => setData('phone', e.target.value)}
                                                error={errors.phone}
                                                placeholder="(11) 99999-9999"
                                            />
                                            {errors.phone && (
                                                <p className="text-sm text-red-600">{errors.phone}</p>
                                            )}
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="domain">Website</Label>
                                        <Input
                                            id="domain"
                                            type="text"
                                            value={data.domain}
                                            onChange={(e) => setData('domain', e.target.value)}
                                            error={errors.domain}
                                            placeholder="https://empresa.com"
                                        />
                                        {errors.domain && (
                                            <p className="text-sm text-red-600">{errors.domain}</p>
                                        )}
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="description">Descrição</Label>
                                        <Textarea
                                            id="description"
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                            error={errors.description}
                                            placeholder="Descrição da empresa..."
                                            rows={3}
                                        />
                                        {errors.description && (
                                            <p className="text-sm text-red-600">{errors.description}</p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Address */}
                            <Card>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2">
                                        <MapPin className="w-5 h-5" />
                                        Endereço
                                    </CardTitle>
                                    <CardDescription>
                                        Endereço principal da empresa
                                    </CardDescription>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                                        <div className="md:col-span-3 space-y-2">
                                            <Label htmlFor="street">Rua</Label>
                                            <Input
                                                id="street"
                                                value={data.address.street}
                                                onChange={(e) => setAddressField('street', e.target.value)}
                                                placeholder="Nome da rua"
                                            />
                                        </div>
                                        
                                        <div className="space-y-2">
                                            <Label htmlFor="number">Número</Label>
                                            <Input
                                                id="number"
                                                value={data.address.number}
                                                onChange={(e) => setAddressField('number', e.target.value)}
                                                placeholder="123"
                                            />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="complement">Complemento</Label>
                                            <Input
                                                id="complement"
                                                value={data.address.complement}
                                                onChange={(e) => setAddressField('complement', e.target.value)}
                                                placeholder="Sala, andar..."
                                            />
                                        </div>
                                        
                                        <div className="space-y-2">
                                            <Label htmlFor="neighborhood">Bairro</Label>
                                            <Input
                                                id="neighborhood"
                                                value={data.address.neighborhood}
                                                onChange={(e) => setAddressField('neighborhood', e.target.value)}
                                                placeholder="Nome do bairro"
                                            />
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div className="space-y-2">
                                            <Label htmlFor="city">Cidade</Label>
                                            <Input
                                                id="city"
                                                value={data.address.city}
                                                onChange={(e) => setAddressField('city', e.target.value)}
                                                placeholder="Nome da cidade"
                                            />
                                        </div>
                                        
                                        <div className="space-y-2">
                                            <Label htmlFor="state">Estado</Label>
                                            <Input
                                                id="state"
                                                value={data.address.state}
                                                onChange={(e) => setAddressField('state', e.target.value)}
                                                placeholder="SP"
                                                maxLength={2}
                                            />
                                        </div>
                                        
                                        <div className="space-y-2">
                                            <Label htmlFor="zip_code">CEP</Label>
                                            <Input
                                                id="zip_code"
                                                value={data.address.zip_code}
                                                onChange={(e) => setAddressField('zip_code', e.target.value)}
                                                placeholder="12345-678"
                                            />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Configurações</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="status">Status</Label>
                                        <Select value={data.status} onValueChange={(value) => setData('status', value)}>
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {status_options.map((option) => (
                                                    <SelectItem key={option.value} value={option.value}>
                                                        {option.label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {errors.status && (
                                            <p className="text-sm text-red-600">{errors.status}</p>
                                        )}
                                    </div>

                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="is_primary"
                                            checked={data.is_primary}
                                            onCheckedChange={(checked) => setData('is_primary', checked as boolean)}
                                        />
                                        <Label htmlFor="is_primary" className="text-sm font-normal">
                                            Tenant principal
                                        </Label>
                                    </div>
                                    {errors.is_primary && (
                                        <p className="text-sm text-red-600">{errors.is_primary}</p>
                                    )}
                                </CardContent>
                            </Card>

                            <Card>
                                <CardContent className="pt-6">
                                    <div className="flex flex-col gap-3">
                                        <Button type="submit" disabled={processing} className="w-full">
                                            <Save className="w-4 h-4 mr-2" />
                                            {processing ? 'Salvando...' : 'Salvar Alterações'}
                                        </Button>
                                        
                                        <Link href={route('landlord.tenants.show', tenant.id)}>
                                            <Button type="button" variant="outline" className="w-full">
                                                Visualizar
                                            </Button>
                                        </Link>
                                        
                                        <Link href={route('landlord.tenants.index')}>
                                            <Button type="button" variant="ghost" className="w-full">
                                                Cancelar
                                            </Button>
                                        </Link>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </form>
            </div>
        </AuthLayout>
    );
}
