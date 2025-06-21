import React, { useState, useEffect, useCallback } from 'react';
import { EditorProps } from './types';
import { Combobox, ComboboxOption } from '../../../components/Combobox';
import axios from 'axios';
import debounce from 'lodash/debounce';

const SelectEditor: React.FC<EditorProps> = ({ value, onValueChange, column }) => {
    const [options, setOptions] = useState<ComboboxOption[]>(column.options || []);
    const [isLoading, setIsLoading] = useState(false);

    // Função para buscar dados da API
    const fetchOptions = useCallback(
        debounce(async (query: string = '') => {
            if (!column.fetchUrl) return;

            setIsLoading(true);
            try {
                const response = await axios.get(column.fetchUrl, {
                    params: { search: query, limit: 15 },
                });
                // Assumindo que a API retorna um array de { value, label }
                setOptions(response.data || []);
            } catch (error) {
                console.error('Falha ao buscar opções:', error);
                setOptions([]); // Limpa as opções em caso de erro
            } finally {
                setIsLoading(false);
            }
        }, 300), // Debounce de 300ms para evitar chamadas excessivas
        [column.fetchUrl]
    );

    // Efeito para buscar dados na montagem ou quando a URL mudar
    useEffect(() => {
        if (column.fetchUrl) {
            fetchOptions();
        } else {
            setOptions(column.options || []);
        }
    }, [column.fetchUrl, column.options, fetchOptions]);


    return (
        <Combobox
            value={String(value)}
            onChange={onValueChange}
            options={options}
            isLoading={isLoading}
            placeholder="Selecione..."
            searchPlaceholder="Busque ou selecione..."
            noResultsMessage={isLoading ? "Carregando..." : "Nenhum resultado."}
        />
    );
};

export default SelectEditor; 