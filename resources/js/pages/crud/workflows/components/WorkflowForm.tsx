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

// Cores disponíveis para workflows e templates
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

// Ícones disponíveis (Lucide)
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

// Categorias de workflow
const WORKFLOW_CATEGORIES = [
    { value: 'support', label: 'Suporte' },
    { value: 'development', label: 'Desenvolvimento' },
    { value: 'enhancement', label: 'Melhorias' },
    { value: 'sales', label: 'Vendas' },
    { value: 'marketing', label: 'Marketing' },
    { value: 'hr', label: 'Recursos Humanos' },
    { value: 'finance', label: 'Financeiro' },
    { value: 'operations', label: 'Operações' },
    { value: 'quality', label: 'Qualidade' },
    { value: 'other', label: 'Outros' }
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
    category: string;
    tags: string[];
    color: string;
    icon: string;
    estimated_duration_days: number;
    is_required_by_default: boolean;
    is_active: boolean;
    is_featured: boolean;
    sort_order: number;
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
    category: 'support',
    tags: [],
    color: '#3b82f6',
    icon: 'Settings',
    estimated_duration_days: 3,
    is_required_by_default: false,
    is_active: true,
    is_featured: false,
    sort_order: 1,
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
            id: `temp_${Date.now()}`,
            name: '',
            slug: '',
            description: '',
            instructions: '',
            category: 'processo',
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

        updateFormData({
            templates: [...formData.templates, newTemplate]
        });
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
            templates: formData.templates.map(template => 
                template.id === templateId 
                    ? { ...template, ...updates, slug: updates.name ? generateSlug(updates.name) : template.slug }
                    : template
            )
        });
    };

    // Reordenar templates
    const reorderTemplates = (fromIndex: number, toIndex: number) => {
        const newTemplates = [...formData.templates];
        const [removed] = newTemplates.splice(fromIndex, 1);
        newTemplates.splice(toIndex, 0, removed);
        
        // Atualizar sort_order
        const reorderedTemplates = newTemplates.map((template, index) => ({
            ...template,
            sort_order: index + 1
        }));

        updateFormData({ templates: reorderedTemplates });
    };

    // Submeter formulário
    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSubmit(formData);
    };

    // Componente para seleção de cor
    const ColorPicker = ({ value, onChange, className = '' }: { value: string; onChange: (color: string) => void; className?: string }) => (
        <div className={`flex flex-wrap gap-2 ${className}`}>
            {WORKFLOW_COLORS.map(color => (
                <button
                    key={color.value}
                    type="button"
                    onClick={() => onChange(color.value)}
                    className={`w-8 h-8 rounded-full border-2 transition-all ${
                        value === color.value ? 'border-gray-400 scale-110' : 'border-gray-200'
                    } ${color.class}`}
                    title={color.label}
                />
            ))}
        </div>
    );

    // Componente para seleção de ícone
    const IconPicker = ({ value, onChange }: { value: string; onChange: (icon: string) => void }) => (
        <Select value={value} onValueChange={onChange}>
            <SelectTrigger>
                <SelectValue placeholder="Selecionar ícone" />
            </SelectTrigger>
            <SelectContent className="max-h-48">
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
                            ? 'Configure um novo workflow com suas etapas (templates)'
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
                {/* Tab: Configurações do Workflow */}
                {currentTab === 'workflow' && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <Settings className="w-5 h-5" />
                                Informações Básicas
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

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {/* Categoria */}
                                <div className="space-y-2">
                                    <Label htmlFor="category">Categoria</Label>
                                    <Select value={formData.category} onValueChange={(value) => updateFormData({ category: value })}>
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {WORKFLOW_CATEGORIES.map(cat => (
                                                <SelectItem key={cat.value} value={cat.value}>
                                                    {cat.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>

                                {/* Status */}
                                <div className="space-y-2">
                                    <Label htmlFor="status">Status</Label>
                                    <Select value={formData.status} onValueChange={(value) => updateFormData({ status: value })}>
                                        <SelectTrigger>
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

                                {/* Duração Estimada */}
                                <div className="space-y-2">
                                    <Label htmlFor="estimated_duration_days">Duração (dias)</Label>
                                    <Input
                                        id="estimated_duration_days"
                                        type="number"
                                        min="1"
                                        value={formData.estimated_duration_days}
                                        onChange={(e) => updateFormData({ estimated_duration_days: parseInt(e.target.value) || 1 })}
                                    />
                                </div>
                            </div>

                            {/* Aparência */}
                            <Separator />
                            <div>
                                <h3 className="text-lg font-medium mb-4 flex items-center gap-2">
                                    <Palette className="w-5 h-5" />
                                    Aparência
                                </h3>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {/* Cor */}
                                    <div className="space-y-2">
                                        <Label>Cor Principal</Label>
                                        <ColorPicker
                                            value={formData.color}
                                            onChange={(color) => updateFormData({ color })}
                                        />
                                    </div>

                                    {/* Ícone */}
                                    <div className="space-y-2">
                                        <Label>Ícone</Label>
                                        <IconPicker
                                            value={formData.icon}
                                            onChange={(icon) => updateFormData({ icon })}
                                        />
                                    </div>
                                </div>
                            </div>

                            {/* Configurações */}
                            <Separator />
                            <div>
                                <h3 className="text-lg font-medium mb-4">Configurações</h3>
                                <div className="space-y-4">
                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="is_active"
                                            checked={formData.is_active}
                                            onCheckedChange={(checked) => updateFormData({ is_active: !!checked })}
                                        />
                                        <Label htmlFor="is_active">Workflow ativo</Label>
                                    </div>

                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="is_featured"
                                            checked={formData.is_featured}
                                            onCheckedChange={(checked) => updateFormData({ is_featured: !!checked })}
                                        />
                                        <Label htmlFor="is_featured">Destacar workflow</Label>
                                    </div>

                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="is_required_by_default"
                                            checked={formData.is_required_by_default}
                                            onCheckedChange={(checked) => updateFormData({ is_required_by_default: !!checked })}
                                        />
                                        <Label htmlFor="is_required_by_default">Obrigatório por padrão</Label>
                                    </div>
                                </div>
                            </div>
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
                                    Defina as etapas que compõem este workflow. Arraste para reordenar.
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
export { WORKFLOW_COLORS, AVAILABLE_ICONS, WORKFLOW_CATEGORIES, BASE_STATUS }; 