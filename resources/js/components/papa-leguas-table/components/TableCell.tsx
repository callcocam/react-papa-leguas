import React from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Copy, Check, X } from 'lucide-react';
import { TableColumn, TableRow } from '../types';

interface TableCellProps {
    column: TableColumn;
    row: TableRow;
    value: any;
}

export function TableCell({ column, row, value }: TableCellProps) {
    const [copied, setCopied] = React.useState(false);

    const handleCopy = async (text: string) => {
        try {
            await navigator.clipboard.writeText(text);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch (err) {
            console.error('Falha ao copiar:', err);
        }
    };

    const formatValue = (value: any, column: TableColumn) => {
        if (value === null || value === undefined) {
            return column.formatConfig?.placeholder || '-';
        }

        switch (column.type) {
            case 'date':
                return formatDate(value, column.formatConfig?.dateFormat);
            
            case 'boolean':
                return formatBoolean(value, column.formatConfig);
            
            case 'currency':
                return formatCurrency(value, column.formatConfig?.currency);
            
            case 'image':
                return formatImage(value);
            
            case 'badge':
                return formatBadge(value, column.formatConfig?.colors);
            
            case 'text':
            default:
                return formatText(value, column.formatConfig);
        }
    };

    const formatDate = (value: any, format?: string) => {
        try {
            const date = new Date(value);
            if (isNaN(date.getTime())) return value;
            
            return date.toLocaleDateString('pt-BR', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                ...(format?.includes('H:i') && {
                    hour: '2-digit',
                    minute: '2-digit'
                })
            });
        } catch {
            return value;
        }
    };

    const formatBoolean = (value: any, config?: any) => {
        const isTrue = Boolean(value);
        
        if (config?.trueIcon || config?.falseIcon) {
            const icon = isTrue ? config.trueIcon : config.falseIcon;
            const color = isTrue ? config.trueColor : config.falseColor;
            
            return (
                <div className={`inline-flex items-center gap-1 ${color ? `text-${color}` : ''}`}>
                    {icon && <i className={`lucide lucide-${icon} h-4 w-4`} />}
                    <span>{isTrue ? (config?.trueLabel || 'Sim') : (config?.falseLabel || 'Não')}</span>
                </div>
            );
        }
        
        return (
            <Badge variant={isTrue ? 'default' : 'secondary'}>
                {isTrue ? 'Sim' : 'Não'}
            </Badge>
        );
    };

    const formatCurrency = (value: any, currency = 'BRL') => {
        const numValue = parseFloat(value);
        if (isNaN(numValue)) return value;
        
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: currency
        }).format(numValue);
    };

    const formatImage = (value: any) => {
        if (!value) return '-';
        
        return (
            <img
                src={value}
                alt="Imagem"
                className="h-8 w-8 rounded object-cover"
                onError={(e) => {
                    e.currentTarget.src = '/placeholder-image.png';
                }}
            />
        );
    };

    const formatBadge = (value: any, colors?: Record<string, string>) => {
        const color = colors?.[value] || 'default';
        
        return (
            <Badge variant={color as any}>
                {value}
            </Badge>
        );
    };

    const formatText = (value: any, config?: any) => {
        let text = String(value);
        
        if (config?.limit && text.length > config.limit) {
            text = text.substring(0, config.limit) + '...';
        }
        
        return text;
    };

    const cellAlign = column.align === 'center' ? 'text-center' : 
                     column.align === 'right' ? 'text-right' : 'text-left';

    const formattedValue = formatValue(value, column);

    return (
        <td className={`px-4 py-3 ${cellAlign}`}>
            <div className="flex items-center gap-2">
                {/* Ícone da coluna */}
                {column.formatConfig?.icon && (
                    <i className={`lucide lucide-${column.formatConfig.icon} h-4 w-4 text-muted-foreground`} />
                )}
                
                {/* Valor formatado */}
                <span className={column.formatConfig?.fontMono ? 'font-mono' : ''}>
                    {formattedValue}
                </span>
                
                {/* Botão de copiar */}
                {column.formatConfig?.copyable && value && (
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-6 w-6 p-0"
                        onClick={() => handleCopy(String(value))}
                    >
                        {copied ? (
                            <Check className="h-3 w-3 text-green-600" />
                        ) : (
                            <Copy className="h-3 w-3" />
                        )}
                    </Button>
                )}
            </div>
        </td>
    );
}