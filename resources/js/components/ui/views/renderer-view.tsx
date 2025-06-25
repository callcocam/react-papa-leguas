import { getViewsRenderers } from "./index";

export default function RendererView({
    view,
    data,
    columns,
    config,
    actions,
    className
}: any) {

    const renderers = getViewsRenderers();

    const Renderer = renderers[view] || renderers.default;

    return <Renderer data={data} columns={columns} config={config} actions={actions} className={className} />;
}