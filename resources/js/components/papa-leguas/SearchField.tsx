import React, { useCallback, useState } from 'react';
import { router } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Search, X } from 'lucide-react';

interface SearchFieldProps {
    searchTerm: string;
    isSearching?: boolean;
    placeholder?: string;
    disabled?: boolean;
    onSearchChange: (value: string) => void;
    onSearch: (term: string) => void;
    onClear: () => void;
    onKeyPress?: (e: React.KeyboardEvent) => void;
    className?: string;
    showCard?: boolean;
}

export default function SearchField({
    searchTerm,
    isSearching = false,
    placeholder = "Buscar registros...",
    disabled = false,
    onSearchChange,
    onSearch,
    onClear,
    onKeyPress,
    className = "",
    showCard = true
}: SearchFieldProps) {
    
    const handleKeyPress = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter') {
            onSearch(searchTerm);
        }
        onKeyPress?.(e);
    };

    const searchContent = (
        <div className={`space-y-3 ${className}`}>
            <div className="flex items-center gap-3">
                <div className="relative flex-1">
                    <Search className="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                    <Input
                        placeholder={placeholder}
                        value={searchTerm}
                        onChange={(e) => onSearchChange(e.target.value)}
                        onKeyPress={handleKeyPress}
                        className="pl-10"
                        disabled={isSearching || disabled}
                    />
                    {searchTerm && (
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={onClear}
                            disabled={isSearching || disabled}
                            className="absolute right-2 top-1/2 transform -translate-y-1/2 h-6 w-6 p-0 hover:bg-muted"
                        >
                            <X className="h-3 w-3" />
                        </Button>
                    )}
                </div>
                <Button
                    onClick={() => onSearch(searchTerm)}
                    disabled={isSearching || disabled}
                    className="min-w-[100px]"
                >
                    {isSearching ? (
                        <>
                            <span className="animate-spin mr-2">⚪</span>
                            Buscando...
                        </>
                    ) : (
                        <>
                            <Search className="h-4 w-4 mr-2" />
                            Buscar
                        </>
                    )}
                </Button>
            </div>
            {searchTerm && (
                <div className="text-sm text-muted-foreground">
                    Buscando por: <span className="font-medium">"{searchTerm}"</span>
                    <Button
                        variant="link"
                        size="sm"
                        onClick={onClear}
                        disabled={isSearching || disabled}
                        className="ml-2 h-auto p-0 text-xs underline"
                    >
                        Limpar busca
                    </Button>
                </div>
            )}
        </div>
    );

    if (showCard) {
        return (
            <Card>
                <CardContent className="p-4">
                    {searchContent}
                </CardContent>
            </Card>
        );
    }

    return searchContent;
}

// Hook para uso com Inertia.js (opcional)
export function useSearchField(initialTerm: string = '') {
    const [searchTerm, setSearchTerm] = useState<string>(initialTerm);
    const [isSearching, setIsSearching] = useState(false);

    // Aplicar busca via URL
    const applySearch = useCallback(async (term: string) => {
        if (isSearching) return;
        
        setIsSearching(true);
        
        try {
            const currentUrl = new URL(window.location.href);
            
            if (term.trim()) {
                currentUrl.searchParams.set('search', term.trim());
            } else {
                currentUrl.searchParams.delete('search');
            }
            
            // Resetar para primeira página ao fazer busca
            currentUrl.searchParams.delete('page');
            
            router.visit(currentUrl.toString(), {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    console.log('✅ Busca aplicada com sucesso');
                },
                onError: (errors) => {
                    console.error('❌ Erro ao aplicar busca:', errors);
                },
                onFinish: () => {
                    setIsSearching(false);
                }
            });
        } catch (error) {
            console.error('❌ Erro inesperado ao aplicar busca:', error);
            setIsSearching(false);
        }
    }, [isSearching]);

    // Limpar busca
    const clearSearch = useCallback(() => {
        setSearchTerm('');
        applySearch('');
    }, [applySearch]);

    // Handler para mudança no campo
    const handleSearchChange = (value: string) => {
        setSearchTerm(value);
    };

    return {
        searchTerm,
        isSearching,
        setSearchTerm,
        applySearch,
        clearSearch,
        handleSearchChange
    };
} 