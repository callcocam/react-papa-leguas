import React, { useState, useCallback, useEffect } from 'react';
import { useForm } from '@inertiajs/react';
//@ts-ignore
import { Button } from '@/components/ui/button';
//@ts-ignore
import { Input } from '@/components/ui/input';
//@ts-ignore
import { Label } from '@/components/ui/label';
//@ts-ignore
import { Textarea } from '@/components/ui/textarea';
//@ts-ignore
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
//@ts-ignore
import { Badge } from '@/components/ui/badge';
//@ts-ignore
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
//@ts-ignore
import { Checkbox } from '@/components/ui/checkbox';
//@ts-ignore
import { Separator } from '@/components/ui/separator';
//@ts-ignore
import { Alert, AlertDescription } from '@/components/ui/alert';
import { 
    Plus, 
    Trash2, 
    GripVertical, 
    Save, 
    ArrowLeft,
    Palette,
    Settings,
    Eye,
    EyeOff,
    Star,
    History,
    AlertTriangle
} from 'lucide-react';

// Cores disponíveis para templates
const WORKFLOW_COLORS = [
    { value: '#3b82f6', label: 'Azul', class: 'bg-blue-500' },
    { value: '#ef4444', label: 'Vermelho', class: 'bg-red-500' },
    { value: '#10b981', label: 'Verde', class: 'bg-green-500' },
    { value: '#f59e0b', label: 'Amarelo', class: 'bg-yellow-500' },
    { value: '#8b5cf6', label: 'Roxo', class: 'bg-purple-500' },
    { value: '#06b6d4', label: 'Ciano', class: 'bg-cyan-500' },
    { value: '#dc2626', label: 'Vermelho Escuro', class: 'bg-red-600' },
    { value: '#059669', label: 'Verde Escuro', class: 'bg-green-600' },
    { value: '#6b7280', label: 'Cinza', class: 'bg-gray-500' },
];

// Ícones disponíveis (Lucide) para templates
const AVAILABLE_ICONS = [
    'AlertCircle', 'CheckCircle', 'XCircle', 'Clock', 'Play', 'Pause', 'Stop',
    'ArrowRight', 'ArrowDown', 'ArrowUp', 'Settings', 'Cog', 'Zap', 'Star',
    'Heart', 'Target', 'Flag', 'Bell', 'Mail', 'Phone', 'User', 'Users',
    'Home', 'Building', 'Car', 'Truck', 'Plane', 'Ship', 'Rocket', 'Lightbulb',
    'Wrench', 'Hammer', 'Scissors', 'Paintbrush', 'Palette', 'Camera', 'Video',
    'Music', 'Headphones', 'Mic', 'Speaker', 'Volume2', 'VolumeX', 'Wifi',
    'Bluetooth', 'Battery', 'Plug', 'Power', 'Monitor', 'Smartphone', 'Tablet',
    'Laptop', 'HardDrive', 'Database', 'Server', 'Cloud', 'Download', 'Upload',
    'Link', 'Unlink', 'Lock', 'Unlock', 'Key', 'Shield', 'Eye', 'EyeOff',
    'Search', 'Filter', 'Sort', 'Grid', 'List', 'BarChart', 'PieChart', 'TrendingUp',
    'Calendar', 'Timer', 'Stopwatch', 'MapPin', 'Map', 'Navigation',
    'Compass', 'Globe', 'Sun', 'Moon', 'CloudRain', 'Snowflake', 'Thermometer'
];

// Status base (enum)
const BASE_STATUS = [
    { value: 'draft', label: 'Rascunho' },
    { value: 'published', label: 'Publicado' },
    { value: 'archived', label: 'Arquivado' }
];

interface WorkflowTemplate {
    id: string;
    name: string;
    slug: string;
    description: string;
    instructions: string;
    category: string;
    tags: string[];
    color: string;
    icon: string;
    sort_order: number;
    max_items: number;
    auto_assign: boolean;
    requires_approval: boolean;
    transition_rules: {
        next_templates: string[];
        required_fields: string[];
        auto_transition_after_hours: number | null;
    };
}

interface WorkflowFormData {
    name: string;
    slug: string;
    description: string;
    status: string;
    templates: WorkflowTemplate[];
    [key: string]: any;
}

interface WorkflowFormProps {
    mode: 'create' | 'edit';
    initialData?: Partial<WorkflowFormData>;
    onSubmit: (data: WorkflowFormData) => void;
    onCancel: () => void;
    processing?: boolean;
    errors?: Record<string, string>;
    showHistoryTab?: boolean;
    hasUnsavedChanges?: boolean;
    children?: React.ReactNode; // Para conteúdo adicional (ex: histórico)
}

const defaultFormData: WorkflowFormData = {
    name: '',
    slug: '',
    description: '',
    status: 'published',
    templates: []
};

export default function WorkflowForm({ 
    mode, 
    initialData = {}, 
    onSubmit, 
    onCancel, 
    processing = false, 
    errors = {},
    showHistoryTab = false,
    hasUnsavedChanges = false,
    children 
}: WorkflowFormProps) {
    const [formData, setFormData] = useState<WorkflowFormData>({
        ...defaultFormData,
        ...initialData
    });

    const [currentTab, setCurrentTab] = useState<'workflow' | 'templates' | 'history'>('workflow');
    const [previewMode, setPreviewMode] = useState(false);

    // Gerar slug automaticamente
    const generateSlug = useCallback((name: string) => {
        return name
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim();
    }, []);

    // Atualizar dados do formulário
    const updateFormData = (updates: Partial<WorkflowFormData>) => {
        setFormData(prev => ({ ...prev, ...updates }));
    };

    // Atualizar nome e slug
    const handleNameChange = (name: string) => {
        updateFormData({
            name,
            // No modo create, sempre gera slug. No edit, só se estiver vazio
            slug: mode === 'create' || !formData.slug ? generateSlug(name) : formData.slug
        });
    };

    // Adicionar nova template
    const addTemplate = () => {
        const newTemplate: WorkflowTemplate = {
            id: Date.now().toString(),
            name: '',
            slug: '',
            description: '',
            instructions: '',
            category: 'inicial',
            tags: [],
            color: '#3b82f6',
            icon: 'Circle',
            sort_order: formData.templates.length + 1,
            max_items: 50,
            auto_assign: false,
            requires_approval: false,
            transition_rules: {
                next_templates: [],
                required_fields: [],
                auto_transition_after_hours: null
            }
        };
        updateFormData({ templates: [...formData.templates, newTemplate] });
    };

    // Remover template
    const removeTemplate = (templateId: string) => {
        updateFormData({
            templates: formData.templates.filter(t => t.id !== templateId)
        });
    };

    // Atualizar template
    const updateTemplate = (templateId: string, updates: Partial<WorkflowTemplate>) => {
        updateFormData({
            templates: formData.templates.map(t => 
                t.id === templateId ? { ...t, ...updates } : t
            )
        });
    };

    // Reordenar templates
    const reorderTemplates = (fromIndex: number, toIndex: number) => {
        const newTemplates = [...formData.templates];
        const [removed] = newTemplates.splice(fromIndex, 1);
        newTemplates.splice(toIndex, 0, removed);
        
        // Atualizar sort_order
        newTemplates.forEach((template, index) => {
            template.sort_order = index + 1;
        });
        
        updateFormData({ templates: newTemplates });
    };

    // Submeter formulário
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSubmit(formData);
    };

    // Componente para seleção de cores (para templates)
    const ColorPicker = ({ value, onChange, className = '' }: { value: string; onChange: (color: string) => void; className?: string }) => (
        <div className={`flex flex-wrap gap-2 ${className}`}>
            {WORKFLOW_COLORS.map(color => (
                <button
                    key={color.value}
                    type="button"
                    className={`w-8 h-8 rounded-full border-2 ${
                        value === color.value ? 'border-gray-800 dark:border-gray-200' : 'border-gray-300'
                    } ${color.class}`}
                    onClick={() => onChange(color.value)}
                    title={color.label}
                />
            ))}
        </div>
    );

    // Componente para seleção de ícones (para templates)
    const IconPicker = ({ value, onChange }: { value: string; onChange: (icon: string) => void }) => (
        <Select value={value} onValueChange={onChange}>
            <SelectTrigger>
                <SelectValue />
            </SelectTrigger>
            <SelectContent>
                {AVAILABLE_ICONS.map(icon => (
                    <SelectItem key={icon} value={icon}>
                        {icon}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );

    return (
        <div className="space-y-6">
            {/* Header */}
            <div className="flex items-center justify-between">
                <div>
                    <h1 className="text-3xl font-bold text-gray-900 dark:text-gray-100">
                        {mode === 'create' ? 'Criar Workflow' : 'Editar Workflow'}
                    </h1>
                    <p className="text-gray-600 dark:text-gray-400 mt-2">
                        {mode === 'create' 
                            ? 'Configure um novo workflow simplificado com suas etapas (templates)'
                            : 'Modifique as configurações e etapas do workflow'
                        }
                    </p>
                    {hasUnsavedChanges && mode === 'edit' && (
                        <div className="flex items-center gap-2 mt-2">
                            <AlertTriangle className="w-4 h-4 text-yellow-500" />
                            <span className="text-sm text-yellow-600 dark:text-yellow-400">
                                Você tem alterações não salvas
                            </span>
                        </div>
                    )}
                </div>
                <div className="flex items-center gap-3">
                    <Button
                        variant="outline"
                        onClick={() => setPreviewMode(!previewMode)}
                    >
                        {previewMode ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                        {previewMode ? 'Editar' : 'Preview'}
                    </Button>
                    <Button
                        variant="outline"
                        onClick={onCancel}
                    >
                        <ArrowLeft className="w-4 h-4" />
                        Voltar
                    </Button>
                </div>
            </div>

            {/* Tabs */}
            <div className="border-b border-gray-200 dark:border-gray-700">
                <nav className="-mb-px flex space-x-8">
                    <button
                        onClick={() => setCurrentTab('workflow')}
                        className={`py-2 px-1 border-b-2 font-medium text-sm ${
                            currentTab === 'workflow'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        }`}
                    >
                        <Settings className="w-4 h-4 inline mr-2" />
                        Configurações do Workflow
                    </button>
                    <button
                        onClick={() => setCurrentTab('templates')}
                        className={`py-2 px-1 border-b-2 font-medium text-sm ${
                            currentTab === 'templates'
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                        }`}
                    >
                        <Star className="w-4 h-4 inline mr-2" />
                        Templates (Etapas)
                        {formData.templates.length > 0 && (
                            <Badge variant="secondary" className="ml-2">
                                {formData.templates.length}
                            </Badge>
                        )}
                    </button>
                    {showHistoryTab && (
                        <button
                            onClick={() => setCurrentTab('history')}
                            className={`py-2 px-1 border-b-2 font-medium text-sm ${
                                currentTab === 'history'
                                    ? 'border-blue-500 text-blue-600'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                            }`}
                        >
                            <History className="w-4 h-4 inline mr-2" />
                            Histórico
                        </button>
                    )}
                </nav>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6">
                {/* Tab: Configurações do Workflow - Simplificado */}
                {currentTab === 'workflow' && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Settings className="w-5 h-5" />
                                Informações Básicas - Sistema Simplificado
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {/* Nome */}
                                <div className="space-y-2">
                                    <Label htmlFor="name">Nome do Workflow *</Label>
                                    <Input
                                        id="name"
                                        value={formData.name}
                                        onChange={(e) => handleNameChange(e.target.value)}
                                        placeholder="Ex: Suporte Técnico"
                                        className={errors.name ? 'border-red-500' : ''}
                                    />
                                    {errors.name && (
                                        <p className="text-sm text-red-500">{errors.name}</p>
                                    )}
                                </div>

                                {/* Slug */}
                                <div className="space-y-2">
                                    <Label htmlFor="slug">Slug</Label>
                                    <Input
                                        id="slug"
                                        value={formData.slug}
                                        onChange={(e) => updateFormData({ slug: e.target.value })}
                                        placeholder="suporte-tecnico"
                                        className={errors.slug ? 'border-red-500' : ''}
                                    />
                                    {errors.slug && (
                                        <p className="text-sm text-red-500">{errors.slug}</p>
                                    )}
                                </div>
                            </div>

                            {/* Descrição */}
                            <div className="space-y-2">
                                <Label htmlFor="description">Descrição *</Label>
                                <Textarea
                                    id="description"
                                    value={formData.description}
                                    onChange={(e) => updateFormData({ description: e.target.value })}
                                    placeholder="Descreva o propósito deste workflow..."
                                    rows={3}
                                    className={errors.description ? 'border-red-500' : ''}
                                />
                                {errors.description && (
                                    <p className="text-sm text-red-500">{errors.description}</p>
                                )}
                            </div>

                            {/* Status */}
                            <div className="space-y-2">
                                <Label htmlFor="status">Status</Label>
                                <Select value={formData.status} onValueChange={(value) => updateFormData({ status: value })}>
                                    <SelectTrigger className="w-full md:w-1/3">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {BASE_STATUS.map(status => (
                                            <SelectItem key={status.value} value={status.value}>
                                                {status.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>

                            {/* Nota sobre simplificação */}
                            <Alert>
                                <AlertTriangle className="h-4 w-4" />
                                <AlertDescription>
                                    <strong>Sistema Simplificado:</strong> Configurações visuais (cores, ícones, categorias) 
                                    agora estão nas templates individuais. Isso permite maior flexibilidade e customização 
                                    por etapa do workflow.
                                </AlertDescription>
                            </Alert>
                        </CardContent>
                    </Card>
                )}

                {/* Tab: Templates */}
                {currentTab === 'templates' && (
                    <div className="space-y-6">
                        {/* Header Templates */}
                        <div className="flex items-center justify-between">
                            <div>
                                <h2 className="text-xl font-semibold">Templates (Etapas do Workflow)</h2>
                                <p className="text-gray-600 dark:text-gray-400">
                                    Defina as etapas que compõem este workflow. Configurações visuais estão aqui.
                                </p>
                            </div>
                            <Button onClick={addTemplate} type="button">
                                <Plus className="w-4 h-4" />
                                Adicionar Template
                            </Button>
                        </div>

                        {/* Lista de Templates */}
                        {formData.templates.length === 0 ? (
                            <Alert>
                                <AlertDescription>
                                    Nenhuma template criada. Clique em "Adicionar Template" para começar.
                                </AlertDescription>
                            </Alert>
                        ) : (
                            <div className="space-y-4">
                                {formData.templates.map((template, index) => (
                                    <Card key={template.id} className="relative">
                                        <CardHeader className="pb-3">
                                            <div className="flex items-center justify-between">
                                                <div className="flex items-center gap-3">
                                                    <div 
                                                        className="w-4 h-4 rounded-full flex-shrink-0"
                                                        style={{ backgroundColor: template.color }}
                                                    />
                                                    <CardTitle className="text-base">
                                                        Template #{template.sort_order}
                                                    </CardTitle>
                                                </div>
                                                <div className="flex items-center gap-2">
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        className="cursor-grab"
                                                        type="button"
                                                    >
                                                        <GripVertical className="w-4 h-4" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => removeTemplate(template.id)}
                                                        type="button"
                                                    >
                                                        <Trash2 className="w-4 h-4 text-red-500" />
                                                    </Button>
                                                </div>
                                            </div>
                                        </CardHeader>
                                        <CardContent className="space-y-4">
                                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                {/* Nome da Template */}
                                                <div className="space-y-2">
                                                    <Label>Nome da Etapa *</Label>
                                                    <Input
                                                        value={template.name}
                                                        onChange={(e) => updateTemplate(template.id, { name: e.target.value })}
                                                        placeholder="Ex: Em Andamento"
                                                    />
                                                </div>

                                                {/* Descrição */}
                                                <div className="space-y-2">
                                                    <Label>Descrição</Label>
                                                    <Input
                                                        value={template.description}
                                                        onChange={(e) => updateTemplate(template.id, { description: e.target.value })}
                                                        placeholder="Breve descrição da etapa"
                                                    />
                                                </div>
                                            </div>

                                            {/* Instruções */}
                                            <div className="space-y-2">
                                                <Label>Instruções</Label>
                                                <Textarea
                                                    value={template.instructions}
                                                    onChange={(e) => updateTemplate(template.id, { instructions: e.target.value })}
                                                    placeholder="Orientações sobre o que fazer nesta etapa..."
                                                    rows={2}
                                                />
                                            </div>

                                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                {/* Cor */}
                                                <div className="space-y-2">
                                                    <Label>Cor</Label>
                                                    <ColorPicker
                                                        value={template.color}
                                                        onChange={(color) => updateTemplate(template.id, { color })}
                                                        className="justify-start"
                                                    />
                                                </div>

                                                {/* Ícone */}
                                                <div className="space-y-2">
                                                    <Label>Ícone</Label>
                                                    <IconPicker
                                                        value={template.icon}
                                                        onChange={(icon) => updateTemplate(template.id, { icon })}
                                                    />
                                                </div>

                                                {/* Limite máximo */}
                                                <div className="space-y-2">
                                                    <Label>Limite máximo</Label>
                                                    <Input
                                                        type="number"
                                                        min="1"
                                                        value={template.max_items}
                                                        onChange={(e) => updateTemplate(template.id, { max_items: parseInt(e.target.value) || 50 })}
                                                    />
                                                </div>
                                            </div>

                                            {/* Configurações da Template */}
                                            <div className="space-y-3">
                                                <Label className="text-sm font-medium">Configurações</Label>
                                                <div className="flex flex-wrap gap-4">
                                                    <div className="flex items-center space-x-2">
                                                        <Checkbox
                                                            id={`auto_assign_${template.id}`}
                                                            checked={template.auto_assign}
                                                            onCheckedChange={(checked) => updateTemplate(template.id, { auto_assign: !!checked })}
                                                        />
                                                        <Label htmlFor={`auto_assign_${template.id}`} className="text-sm">
                                                            Auto-atribuir
                                                        </Label>
                                                    </div>

                                                    <div className="flex items-center space-x-2">
                                                        <Checkbox
                                                            id={`requires_approval_${template.id}`}
                                                            checked={template.requires_approval}
                                                            onCheckedChange={(checked) => updateTemplate(template.id, { requires_approval: !!checked })}
                                                        />
                                                        <Label htmlFor={`requires_approval_${template.id}`} className="text-sm">
                                                            Requer aprovação
                                                        </Label>
                                                    </div>
                                                </div>
                                            </div>
                                        </CardContent>
                                    </Card>
                                ))}
                            </div>
                        )}
                    </div>
                )}

                {/* Tab: Histórico (conteúdo customizado) */}
                {currentTab === 'history' && showHistoryTab && (
                    <div className="space-y-6">
                        {children}
                    </div>
                )}

                {/* Botões de Ação */}
                <div className="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <Button
                        type="button"
                        variant="outline"
                        onClick={onCancel}
                    >
                        Cancelar
                    </Button>
                    <Button
                        type="submit"
                        disabled={processing}
                    >
                        <Save className="w-4 h-4 mr-2" />
                        {processing ? 'Salvando...' : mode === 'create' ? 'Criar Workflow' : 'Salvar Alterações'}
                    </Button>
                </div>
            </form>
        </div>
    );
}

// Exportar tipos e constantes para uso nos arquivos que importam
export type { WorkflowFormData, WorkflowTemplate };
export { WORKFLOW_COLORS, AVAILABLE_ICONS, BASE_STATUS }; 