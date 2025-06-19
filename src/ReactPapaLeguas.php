<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas;

use Illuminate\Support\Str;

class ReactPapaLeguas {

    protected $id ="admin";

    protected $prefix = "landlord";

    protected $middelwares = ['web'];

    public function getId()
    {
        return $this->id;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getMiddlewares()
    {
        return $this->middelwares;
    }

    /**
     * Extrair nome base de uma classe (UserTable, User, UserController → User)
     */
    public static function extractBaseName(string $className): string
    {
        // Obter apenas o nome da classe (sem namespace)
        $baseName = class_basename($className);
        
        // Remover sufixos comuns
        $baseName = str_replace(['Controller', 'Table'], '', $baseName);
        
        return $baseName;
    }

    /**
     * Converter nome para plural em snake_case
     */
    public static function toPlural(string $name): string
    {
        return Str::plural(Str::snake($name));
    }

    /**
     * Gerar prefixo de rota baseado em classe ou modelo
     */
    public static function generateRoutePrefix($classOrModel = null, ?string $customPrefix = null): string
    {
        if ($customPrefix) {
            return $customPrefix;
        }

        if (is_string($classOrModel)) {
            // Se for string, tratar como nome de classe
            $baseName = self::extractBaseName($classOrModel);
            return self::toPlural($baseName);
        }

        if (is_object($classOrModel)) {
            // Se for objeto, obter o nome da classe
            $baseName = self::extractBaseName(get_class($classOrModel));
            return self::toPlural($baseName);
        }

        return 'resources';
    }

    /**
     * Gerar nome de rota completo
     */
    public static function generateRouteName(string $action, $classOrModel = null, ?string $customPrefix = null): string
    {
        $prefix = self::generateRoutePrefix($classOrModel, $customPrefix);
        return "{$prefix}.{$action}";
    }

    /**
     * Obter todas as ações padrão de CRUD
     */
    public static function getStandardActions(): array
    {
        return [
            'index',
            'create', 
            'store',
            'show',
            'edit',
            'update',
            'destroy',
            'export',
            'bulk_destroy'
        ];
    }

    /**
     * Gerar todos os nomes de rotas para uma classe/modelo
     */
    public static function generateAllRouteNames($classOrModel = null, ?string $customPrefix = null): array
    {
        $routes = [];
        $actions = self::getStandardActions();
        
        foreach ($actions as $action) {
            $routes[$action] = self::generateRouteName($action, $classOrModel, $customPrefix);
        }
        
        return $routes;
    }

    /**
     * Verificar se uma ação é válida
     */
    public static function isValidAction(string $action): bool
    {
        return in_array($action, self::getStandardActions());
    }

    /**
     * Exemplos de uso:
     * 
     * ReactPapaLeguas::generateRouteName('index', 'UserTable') → 'users.index'
     * ReactPapaLeguas::generateRouteName('create', User::class) → 'users.create'
     * ReactPapaLeguas::generateRouteName('show', $userInstance) → 'users.show'
     * ReactPapaLeguas::generateAllRouteNames('UserController') → ['index' => 'users.index', ...]
     */
}
