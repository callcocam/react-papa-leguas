import { TableProvider } from "../../papa-leguas/table/contexts/TableContext";
import { getViewsRenderers } from "./index";
import { ConfirmationDialogProvider } from "../../papa-leguas/table/contexts/ConfirmationDialogContext";
import { ModalProvider } from "../../papa-leguas/table/contexts/ModalContext";

export default function RendererView({
    view,
    data,
    columns,
    config,
    actions,
    className,
    meta
}: any) {

    const renderers = getViewsRenderers(); 

    const Renderer = renderers[view] || renderers.default; 
  

    return (
        <TableProvider initialData={data} meta={meta}>
            <ConfirmationDialogProvider>
                <ModalProvider>
                    <Renderer data={data} columns={columns} config={config} actions={actions} className={className} />
                </ModalProvider>
            </ConfirmationDialogProvider>
        </TableProvider>
    )
}