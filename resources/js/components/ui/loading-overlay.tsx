import React from 'react';
import { Loader2 } from 'lucide-react';
import { cn } from '@/lib/utils';

interface LoadingOverlayProps {
  isVisible: boolean;
  message?: string;
  className?: string;
}

/**
 * Overlay de loading global para bloquear interações durante operações
 * 
 * Funcionalidades:
 * - Bloqueia toda a interface
 * - Spinner animado profissional
 * - Mensagem customizável
 * - Backdrop com blur
 * - Suporte a tema dark/light
 */
export function LoadingOverlay({ 
  isVisible, 
  message = 'Processando...', 
  className 
}: LoadingOverlayProps) {
  if (!isVisible) return null;

  return (
    <div 
      className={cn(
        "fixed inset-0 z-[9999] flex items-center justify-center",
        "bg-background/80 backdrop-blur-sm",
        "animate-in fade-in-0 duration-200",
        className
      )}
      aria-hidden="true"
    >
      <div className="flex flex-col items-center justify-center space-y-4 p-8">
        {/* Spinner Principal */}
        <div className="relative">
          <Loader2 className="h-8 w-8 animate-spin text-primary" />
          
          {/* Círculo de background para melhor contraste */}
          <div className="absolute inset-0 -z-10 h-8 w-8 rounded-full bg-background/50 shadow-lg" />
        </div>
        
        {/* Mensagem */}
        <div className="text-center">
          <p className="text-sm font-medium text-foreground">{message}</p>
          <p className="text-xs text-muted-foreground mt-1">Aguarde...</p>
        </div>
      </div>
    </div>
  );
}

export default LoadingOverlay; 