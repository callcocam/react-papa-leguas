import React from 'react'

/**
 * Componente Content para sintaxe declarativa
 * 
 * Define o conteúdo customizado de uma célula:
 * <Content>
 *   {(row, index) => (
 *     <div className="flex items-center gap-2">
 *       <Avatar src={row.avatar} />
 *       <span>{row.name}</span>
 *     </div>
 *   )}
 * </Content>
 */
export interface ContentProps {
  /**
   * Função que renderiza o conteúdo da célula
   * 
   * @param row - Dados da linha atual
   * @param index - Índice da linha na tabela
   * @param column - Configuração da coluna (opcional)
   * @returns ReactNode para renderizar na célula
   */
  children: (row: any, index: number, column?: any) => React.ReactNode
}

/**
 * Componente Content
 * 
 * Este componente não renderiza nada diretamente.
 * Ele é parseado pelo componente pai (Column) para extrair
 * a função de renderização customizada.
 */
export const Content: React.FC<ContentProps> = ({ children }) => {
  // Este componente é apenas um placeholder
  // A função children é extraída pelo parser e usada para renderizar as células
  return null
}

// Definir displayName para o detector conseguir identificar
Content.displayName = 'Content'

export default Content 