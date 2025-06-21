import React from 'react';
import { type RendererProps } from '../../types';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { icons } from 'lucide-react';
// lucide-react nos fornece um objeto `icons` com todos os ícones, que é seguro para mapear.
const iconMap: { [key: string]: React.ElementType } = icons;

// Helper function to get nested properties
const getNestedValue = (obj: any, path: string | undefined): any => {
    if (!path) return null;
    return path.split('.').reduce((o, k) => (o || {})[k], obj);
};

// Helper to extract displayable value
const getDisplayValue = (value: any): string => {
    if (!value) return '';
    if (typeof value === 'object' && value.formatted !== undefined) {
        return value.formatted;
    }
    if (typeof value === 'string' || typeof value === 'number') {
        return String(value);
    }
    return '';
};

const CompoundRenderer: React.FC<RendererProps> = ({ item, column }) => { 
    const config = column.rendererOptions || {}; 
    // --- Retrocompatibilidade e Lógica de Campos de Texto ---
    let textFields = config.textFields || [];
    // Manter retrocompatibilidade com titleField e descriptionField
    if (textFields.length === 0 && (config.titleField || config.descriptionField)) {
        if (config.titleField) {
            textFields.push({ field: config.titleField, className: 'font-medium text-foreground truncate' });
        }
        if (config.descriptionField) {
            textFields.push({ field: config.descriptionField, className: 'text-sm text-muted-foreground truncate' });
        }
    }

    const firstTextField = textFields.length > 0 ? textFields[0] : {};
    const title = getDisplayValue(getNestedValue(item, firstTextField.field)); 

    const avatarUrl = getNestedValue(item, config.avatarField);
    const iconName = getNestedValue(item, config.iconField) || config.icon || null;
    
    const IconComponent = iconName && !avatarUrl ? iconMap[iconName] : null;
    const getInitials = useInitials();
    const initials = getInitials(title || '');

    // Mapeamento de cores personalizadas para classes Tailwind
    const getIconContainerClass = (colorClass: string) => {
        const baseClasses = 'flex-shrink-0 h-10 w-10 rounded-full flex items-center justify-center';
        
        // Se já é uma classe Tailwind, usar diretamente
        if (colorClass.includes('bg-') || colorClass.includes('text-')) {
            return `${baseClasses} ${colorClass}`;
        }
        
        // Mapeamento de classes personalizadas para Tailwind
        const colorMap: { [key: string]: string } = {
            'raptor-compound-icon-default': 'bg-muted text-muted-foreground',
            'raptor-compound-icon-primary': 'bg-primary text-primary-foreground',
            'raptor-compound-icon-success': 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400',
            'raptor-compound-icon-warning': 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400',
            'raptor-compound-icon-error': 'bg-red-100 text-red-600 dark:bg-red-900/20 dark:text-red-400',
            'raptor-compound-icon-info': 'bg-blue-100 text-blue-600 dark:bg-blue-900/20 dark:text-blue-400',
        };
        
        return `${baseClasses} ${colorMap[colorClass] || 'bg-muted text-muted-foreground'}`;
    };

    const iconContainerClass = getIconContainerClass(config.iconColor || 'raptor-compound-icon-default');

    return (
        <div className="flex items-center space-x-3">
            {avatarUrl && (
                <Avatar>
                    <AvatarImage src={avatarUrl} alt={title || 'Avatar'} />
                    <AvatarFallback>{initials}</AvatarFallback>
                </Avatar>
            )}
            {IconComponent && (
                <div className={iconContainerClass}>
                    <IconComponent className="h-5 w-5" />
                </div>
            )}
            {textFields.length > 0 && (
                <div className={avatarUrl || IconComponent ? "flex-1 min-w-0" : ""}>
                    {textFields.map((textField: any, index: number) => {
                        const value = getDisplayValue(getNestedValue(item, textField.field));  
                        return value ? (
                            <div key={index} className={textField.className || ''}>
                                {value}
                            </div>
                        ) : null;
                    })}
                </div>
            )}
        </div>
    );
};

export default CompoundRenderer; 