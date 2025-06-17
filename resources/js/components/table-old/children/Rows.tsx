import React from 'react'

/**
 * Componente Rows para sintaxe declarativa
 * 
 * Permite customização completa das linhas da tabela
 */
export interface RowsProps {
  /**
   * Função que renderiza uma linha customizada
   * 
   * @param row - Dados da linha atual
   * @param index - Índice da linha na tabela
   * @param columns - Array com configurações das colunas
   * @returns ReactNode para renderizar a linha (geralmente um <tr>)
   */
  children: (row: any, index: number, columns?: any[]) => React.ReactNode
}

/**
 * Componente Rows
 * 
 * Este componente não renderiza nada diretamente.
 * Ele é parseado pelo componente pai (Table) para extrair
 * a função de renderização customizada das linhas.
 * 
 * Quando usado, substitui completamente a renderização padrão das linhas,
 * dando controle total sobre o HTML gerado.
 */
export const Rows: React.FC<RowsProps> = ({ children }) => {
  // Este componente é apenas um placeholder
  // A função children é extraída pelo parser e usada para renderizar as linhas
  return null
}

// Definir displayName para o detector conseguir identificar
Rows.displayName = 'Rows'

export default Rows 