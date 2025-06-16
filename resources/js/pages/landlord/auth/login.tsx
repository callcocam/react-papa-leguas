import React, { FormEvent, useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AuthLayout from '../../../layouts/react-auth-layout';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Loader2 } from 'lucide-react';

interface LoginProps {
    errors?: {
        email?: string;
        password?: string;
    };
    message?: string;
}

export default function LandlordLogin({ errors = {}, message }: LoginProps) {
    const [formData, setFormData] = useState({
        email: '',
        password: '',
        remember: false,
    });

    const [isLoading, setIsLoading] = useState(false);

    const handleSubmit = (e: FormEvent) => {
        e.preventDefault();
        setIsLoading(true);

        router.post('/landlord/login', formData, {
            onFinish: () => setIsLoading(false),
            preserveScroll: true,
        });
    };

    const handleInputChange = (field: string, value: string | boolean) => {
        setFormData(prev => ({ ...prev, [field]: value }));
    };

    return (
        <AuthLayout>
            <Head title="Login do Administrador - Papa Leguas" />
            
            <div className="min-h-screen flex items-center justify-center bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 py-12 px-4 sm:px-6 lg:px-8">
                <div className="max-w-md w-full space-y-6">
                    {/* Main Login Card */}
                    <Card className="shadow-2xl">
                        <CardHeader className="text-center space-y-4">
                            <div className="text-4xl">🦘</div>
                            <div>
                                <CardTitle className="text-3xl font-bold">Papa Leguas</CardTitle>
                                <CardDescription className="text-base">
                                    Painel do Administrador
                                </CardDescription>
                            </div>
                        </CardHeader>
                        
                        <CardContent className="space-y-6">
                            {/* Message Alert */}
                            {message && (
                                <Alert>
                                    <AlertDescription>{message}</AlertDescription>
                                </Alert>
                            )}

                            {/* Login Form */}
                            <form onSubmit={handleSubmit} className="space-y-4">
                                {/* Email Field */}
                                <div className="space-y-2">
                                    <Label htmlFor="email">E-mail</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={formData.email}
                                        onChange={(e) => handleInputChange('email', e.target.value)}
                                        placeholder="seu@email.com"
                                        className={errors.email ? 'border-destructive' : ''}
                                        required
                                    />
                                    {errors.email && (
                                        <p className="text-sm text-destructive">{errors.email}</p>
                                    )}
                                </div>

                                {/* Password Field */}
                                <div className="space-y-2">
                                    <Label htmlFor="password">Senha</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        value={formData.password}
                                        onChange={(e) => handleInputChange('password', e.target.value)}
                                        placeholder="••••••••"
                                        className={errors.password ? 'border-destructive' : ''}
                                        required
                                    />
                                    {errors.password && (
                                        <p className="text-sm text-destructive">{errors.password}</p>
                                    )}
                                </div>

                                {/* Remember Me Checkbox */}
                                <div className="flex items-center space-x-2">
                                    <Checkbox
                                        id="remember"
                                        checked={formData.remember}
                                        onCheckedChange={(checked) => handleInputChange('remember', !!checked)}
                                    />
                                    <Label htmlFor="remember" className="text-sm font-normal">
                                        Lembrar de mim
                                    </Label>
                                </div>

                                {/* Submit Button */}
                                <Button
                                    type="submit"
                                    className="w-full"
                                    disabled={isLoading}
                                >
                                    {isLoading ? (
                                        <>
                                            <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                            Entrando...
                                        </>
                                    ) : (
                                        'Entrar'
                                    )}
                                </Button>
                            </form>

                            {/* Footer */}
                            <div className="text-center space-y-1 pt-4 border-t">
                                <p className="text-xs text-muted-foreground">
                                    Papa Leguas &copy; {new Date().getFullYear()}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                    Sistema de Multi-tenancy e ACL
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Info Card */}
                    <Card className="bg-background/95 backdrop-blur-sm">
                        <CardHeader>
                            <CardTitle className="text-lg text-center">Primeiro Acesso?</CardTitle>
                            <CardDescription className="text-center">
                                Para configurar o sistema, você precisa:
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <ul className="space-y-2 text-sm">
                                <li className="flex items-center">
                                    <span className="text-green-500 mr-2">✓</span>
                                    Fazer login como administrador
                                </li>
                                <li className="flex items-center">
                                    <span className="text-green-500 mr-2">✓</span>
                                    Cadastrar o primeiro tenant
                                </li>
                                <li className="flex items-center">
                                    <span className="text-green-500 mr-2">✓</span>
                                    Configurar domínios e permissões
                                </li>
                            </ul>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthLayout>
    );
}
