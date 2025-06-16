import React from 'react'
import { PapaLeguasTable, type PapaLeguasTableProps } from '../index'

/**
 * Componente Table para sintaxe declarativa
 * 
 * Este é um wrapper do PapaLeguasTable que permite usar a sintaxe:
 * <Table data={data}>
 *   <Column key="name" label="Nome">
 *     <Content>{(row) => row.name}</Content>
 *   </Column>
 * </Table>
 */
export interface TableProps extends Omit<PapaLeguasTableProps, 'columns'> {
  // Herda todas as props do PapaLeguasTable exceto columns
  // pois no modo declarativo as colunas vêm via children
  children: React.ReactNode
}

export const Table: React.FC<TableProps> = ({
  children,
  data,
  permissions,
  ...props
}) => {
  // Simplesmente passa tudo para o PapaLeguasTable
  // O detector automaticamente identificará que é modo declarativo
  return (
    <PapaLeguasTable
      data={data}
      permissions={permissions}
      {...props}
    >
      {children}
    </PapaLeguasTable>
  )
}

// Definir displayName para o detector conseguir identificar
Table.displayName = 'Table'

export default Table 