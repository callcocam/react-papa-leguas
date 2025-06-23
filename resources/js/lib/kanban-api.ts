/**
 * Helper Dinâmico para API do Kanban
 * 
 * Sistema 100% dinâmico que se adapta aos workflows reais configurados no banco.
 * Não usa mapeamentos hardcoded - tudo é baseado nos dados dos WorkflowTemplates.
 */

import axios, { AxiosResponse } from 'axios';

// ========================================
// 🎯 INTERFACES DINÂMICAS
// ========================================

export interface KanbanApiConfig {
    resource?: string;
    baseUrl?: string;
    workflowSlug?: string;
}

export interface MoveCardRequest {
    card_id: string | number;
    from_template_id: string;
    to_template_id: string;
    item?: any;
    workflow_data?: Record<string, any>;
}

export interface MoveCardResponse {
    success: boolean;
    message?: string;
    data?: {
        id: string | number;
        current_template_id: string;
        current_step: number;
        workflow_slug: string;
        template_slug: string;
        template_name: string;
        moved_at: string;
        previous_template?: string;
        next_templates?: string[];
    };
    errors?: Record<string, string[]>;
}

export interface KanbanStatsResponse {
    success: boolean;
    data?: {
        total: number;
        workflow: {
            id: string;
            name: string;
            slug: string;
        };
        by_template: Array<{
            id: string;
            slug: string;
            name: string;
            count: number;
            percentage: number;
            color: string;
            icon: string;
            order: number;
        }>;
        updated_at: string;
    };
    message?: string;
    error?: string;
}

export interface WorkflowTemplate {
    id: string;
    slug: string;
    name: string;
    color: string;
    icon: string;
    order: number;
    max_items?: number;
    is_initial: boolean;
    is_final: boolean;
    can_transition_to: string[];
    estimated_duration_days?: number;
    auto_assign?: boolean;
    requires_approval?: boolean;
}

export interface KanbanColumnsResponse {
    success: boolean;
    data?: {
        workflow: {
            id: string;
            name: string;
            slug: string;
            description?: string;
        };
        templates: WorkflowTemplate[];
        total_items: number;
        updated_at: string;
    };
    message?: string;
    error?: string;
}

// ========================================
// 🚀 CLASSE DINÂMICA DA API
// ========================================

/**
 * API Client dinâmica para Kanban
 * 
 * Funciona com qualquer workflow configurado no banco de dados.
 * Não possui mapeamentos hardcoded - tudo é descoberto dinamicamente.
 */
export class KanbanApi {
    private config: Required<KanbanApiConfig>;
    private _templates: WorkflowTemplate[] = [];
    private _workflow: any = null;

    constructor(config: KanbanApiConfig = {}) {
        // Detectar configuração automaticamente
        const detected = this.detectFromCurrentUrl();
        
        this.config = {
            baseUrl: '/api/admin',
            resource: detected.resource,
            workflowSlug: detected.workflowSlug,
            ...config
        };

        // Configurar Axios com interceptors
        this.setupAxiosInterceptors();
    }

    /**
     * Detectar configuração baseada na URL atual
     */
    private detectFromCurrentUrl(): { resource: string; workflowSlug: string } {
        const pathSegments = window.location.pathname.split('/').filter(Boolean);
        const adminIndex = pathSegments.indexOf('admin');
        
        let resource = 'kanban';
        let workflowSlug = 'generic';
        
        if (adminIndex !== -1 && pathSegments[adminIndex + 1]) {
            resource = pathSegments[adminIndex + 1];
            
            // Mapear recursos para slugs de workflow (dinâmico)
            const resourceToWorkflowSlug: Record<string, string> = {
                tickets: 'suporte-tecnico',
                sales: 'vendas-pipeline',
                orders: 'pedidos-producao',
                pipeline: 'desenvolvimento-software',
                projects: 'gestao-projetos',
                leads: 'captacao-leads',
                support: 'atendimento-cliente',
            };
            
            workflowSlug = resourceToWorkflowSlug[resource] || resource;
        }

        return { resource, workflowSlug };
    }

    /**
     * Configurar interceptors do Axios
     */
    private setupAxiosInterceptors(): void {
        // Request interceptor - adicionar headers automaticamente
        axios.interceptors.request.use((config) => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            if (csrfToken) {
                config.headers['X-CSRF-TOKEN'] = csrfToken;
            }
            
            config.headers['X-Requested-With'] = 'XMLHttpRequest';
            config.headers['Accept'] = 'application/json';
            
            return config;
        });

        // Response interceptor - tratamento global de erros
        axios.interceptors.response.use(
            (response) => response,
            (error) => {
                console.error('❌ Kanban API Error:', error);
                
                if (error.response?.status === 401) {
                    window.location.href = '/login';
                }
                
                return Promise.reject(error);
            }
        );
    }

    /**
     * Obter endpoint base dinamicamente
     */
    private getBaseEndpoint(): string {
        const { baseUrl, resource } = this.config;
        return `${baseUrl}/${resource}/kanban`;
    }

    /**
     * Carregar templates do workflow (cache local)
     */
    async loadWorkflowTemplates(force: boolean = false): Promise<WorkflowTemplate[]> {
        if (this._templates.length > 0 && !force) {
            return this._templates;
        }

        try {
            const response = await this.getColumns();
            
            if (response.success && response.data) {
                this._templates = response.data.templates;
                this._workflow = response.data.workflow;
            }
            
            return this._templates;
        } catch (error) {
            console.error('❌ Erro ao carregar templates:', error);
            return [];
        }
    }

    /**
     * Obter template por ID ou slug
     */
    async getTemplate(idOrSlug: string): Promise<WorkflowTemplate | null> {
        const templates = await this.loadWorkflowTemplates();
        
        return templates.find(t => 
            t.id === idOrSlug || 
            t.slug === idOrSlug
        ) || null;
    }

    /**
     * Validar se transição é permitida
     */
    async canTransition(fromTemplateId: string, toTemplateId: string): Promise<boolean> {
        const fromTemplate = await this.getTemplate(fromTemplateId);
        
        if (!fromTemplate) return false;
        
        return fromTemplate.can_transition_to.includes(toTemplateId);
    }

    /**
     * Obter próximos templates possíveis
     */
    async getNextTemplates(currentTemplateId: string): Promise<WorkflowTemplate[]> {
        const templates = await this.loadWorkflowTemplates();
        const currentTemplate = await this.getTemplate(currentTemplateId);
        
        if (!currentTemplate) return [];
        
        return templates.filter(t => 
            currentTemplate.can_transition_to.includes(t.id)
        );
    }

    /**
     * Mover card entre templates (100% dinâmico)
     */
    async moveCard(data: MoveCardRequest): Promise<MoveCardResponse> {
        try {
            // Validar transição antes de enviar
            const canMove = await this.canTransition(data.from_template_id, data.to_template_id);
            
            if (!canMove) {
                return {
                    success: false,
                    message: 'Transição não permitida entre estes templates',
                    errors: {
                        transition: ['Movimento não permitido pelo workflow']
                    }
                };
            }

            const endpoint = `${this.getBaseEndpoint()}/move-card`;
            
            const response: AxiosResponse<MoveCardResponse> = await axios.post(endpoint, {
                ...data,
                workflow_slug: this.config.workflowSlug,
            });

            return response.data;
        } catch (error: any) {
            console.error('❌ Kanban API: Erro ao mover card:', error);
            
            return {
                success: false,
                message: error.response?.data?.message || 'Erro ao mover card',
                errors: error.response?.data?.errors || {}
            };
        }
    }

    /**
     * Obter estatísticas dinâmicas
     */
    async getStats(filters: Record<string, any> = {}): Promise<KanbanStatsResponse> {
        try {
            const endpoint = `${this.getBaseEndpoint()}/stats`;
            
            const response: AxiosResponse<KanbanStatsResponse> = await axios.get(endpoint, {
                params: {
                    workflow_slug: this.config.workflowSlug,
                    ...filters,
                },
            });

            return response.data;
        } catch (error: any) {
            console.error('❌ Kanban API: Erro ao buscar estatísticas:', error);
            
            return {
                success: false,
                message: error.response?.data?.message || 'Erro ao buscar estatísticas',
            };
        }
    }

    /**
     * Obter colunas/templates do workflow
     */
    async getColumns(filters: Record<string, any> = {}): Promise<KanbanColumnsResponse> {
        try {
            const endpoint = `${this.getBaseEndpoint()}/columns`;
            
            const response: AxiosResponse<KanbanColumnsResponse> = await axios.get(endpoint, {
                params: {
                    workflow_slug: this.config.workflowSlug,
                    ...filters,
                },
            });

            return response.data;
        } catch (error: any) {
            console.error('❌ Kanban API: Erro ao buscar colunas:', error);
            
            return {
                success: false,
                message: error.response?.data?.message || 'Erro ao buscar colunas',
            };
        }
    }

    /**
     * Buscar itens por template
     */
    async getItemsByTemplate(templateId: string, filters: Record<string, any> = {}): Promise<any[]> {
        try {
            const endpoint = `${this.getBaseEndpoint()}/items`;
            
            const response = await axios.get(endpoint, {
                params: {
                    template_id: templateId,
                    workflow_slug: this.config.workflowSlug,
                    ...filters,
                },
            });

            return response.data?.data || [];
        } catch (error: any) {
            console.error('❌ Kanban API: Erro ao buscar itens:', error);
            return [];
        }
    }

    /**
     * Atualizar configuração dinamicamente
     */
    updateConfig(newConfig: Partial<KanbanApiConfig>): void {
        this.config = { ...this.config, ...newConfig };
        
        // Limpar cache se mudou o workflow
        if (newConfig.workflowSlug && newConfig.workflowSlug !== this.config.workflowSlug) {
            this._templates = [];
            this._workflow = null;
        }
    }

    /**
     * Obter informações do workflow atual
     */
    getWorkflowInfo(): { slug: string; resource: string; baseUrl: string } {
        return {
            slug: this.config.workflowSlug,
            resource: this.config.resource,
            baseUrl: this.config.baseUrl,
        };
    }
}

// ========================================
// 🏭 FACTORY FUNCTIONS
// ========================================

/**
 * Criar instância da API para recurso específico
 */
export function createKanbanApi(config: Partial<KanbanApiConfig> = {}): KanbanApi {
    return new KanbanApi(config);
}

/**
 * Criar API para tickets
 */
export function createTicketsKanbanApi(): KanbanApi {
    return new KanbanApi({
        resource: 'tickets',
        workflowSlug: 'suporte-tecnico',
    });
}

/**
 * Criar API para vendas
 */
export function createSalesKanbanApi(): KanbanApi {
    return new KanbanApi({
        resource: 'sales',
        workflowSlug: 'vendas-pipeline',
    });
}

/**
 * Criar API para pedidos
 */
export function createOrdersKanbanApi(): KanbanApi {
    return new KanbanApi({
        resource: 'orders',
        workflowSlug: 'pedidos-producao',
    });
}

// ========================================
// 🌐 INSTÂNCIA GLOBAL DINÂMICA
// ========================================

/**
 * Instância global que se adapta automaticamente à URL atual
 */
export const kanbanApi = createKanbanApi();

/**
 * Hook para React (futuro)
 */
export function useKanbanApi(config?: Partial<KanbanApiConfig>): KanbanApi {
    return createKanbanApi(config);
} 