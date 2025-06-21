import { create } from 'zustand';

interface GlobalLoadingState {
  isLoading: boolean;
  message: string;
  setLoading: (isLoading: boolean, message?: string) => void;
  startLoading: (message?: string) => void;
  stopLoading: () => void;
}

/**
 * Hook para gerenciar o estado de loading global da aplicação
 * 
 * Funcionalidades:
 * - Estado global de loading
 * - Mensagem customizável
 * - Funções utilitárias start/stop
 * - Integração com Zustand para performance
 */
export const useGlobalLoading = create<GlobalLoadingState>((set) => ({
  isLoading: false,
  message: 'Processando...',
  
  setLoading: (isLoading: boolean, message = 'Processando...') => 
    set({ isLoading, message }),
    
  startLoading: (message = 'Processando...') => 
    set({ isLoading: true, message }),
    
  stopLoading: () => 
    set({ isLoading: false })
}));

export default useGlobalLoading; 