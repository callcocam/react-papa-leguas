/**
 * Utilitários para filtros
 */

/**
 * Filtra opções válidas removendo valores null/undefined
 */
export const filterValidOptions = (options: Record<string, any>) => {
    if (!options || typeof options !== 'object') {
        return [];
    }
    
    return Object.entries(options).filter(([key, label]) => {
        // Remover entradas com key ou label null/undefined
        if (key === null || key === undefined || label === null || label === undefined) {
            return false;
        }
        // Converter key 'null' string para null real se necessário
        if (key === 'null' || key === 'undefined') {
            return false;
        }
        return true;
    });
};

/**
 * Valida se há opções válidas disponíveis
 */
export const hasValidOptions = (options: Record<string, any>): boolean => {
    return filterValidOptions(options).length > 0;
};

/**
 * Obtém o label de uma opção de forma segura
 */
export const getOptionLabel = (label: any, key: string): string => {
    if (typeof label === 'string') {
        return label;
    }
    if (label && typeof label === 'object' && label.label) {
        return label.label;
    }
    return key;
}; 