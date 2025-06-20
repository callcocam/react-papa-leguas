import { TableColumn, TableRow } from '../../../types';

export interface EditorProps {
    item: TableRow;
    column: TableColumn;
    value: any;
    onValueChange: (value: any) => void;
} 