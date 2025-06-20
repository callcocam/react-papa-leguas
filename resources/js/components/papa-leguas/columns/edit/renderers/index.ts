import TextEditor from './TextEditor';
// No futuro, importaremos outros editores aqui
// import NumberEditor from './NumberEditor';
// import DateEditor from './DateEditor';

// Mapeamento dos tipos de editor para os componentes React
const EDIT_RENDERERS: Record<string, React.ComponentType<any>> = {
    text: TextEditor,
    // number: NumberEditor,
    // date: DateEditor,
};

// Renderer padrão caso nenhum seja encontrado
const defaultRenderer = TextEditor;

/**
 * Retorna um objeto contendo todos os renderizadores de edição registrados.
 * Inclui um renderer 'default' como fallback.
 */
export const getEditRenderers = () => ({
    ...EDIT_RENDERERS,
    default: defaultRenderer,
}); 