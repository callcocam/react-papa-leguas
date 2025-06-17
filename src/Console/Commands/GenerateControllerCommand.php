<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateControllerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'papa-leguas:generate-controller 
                            {name : The name of the controller}
                            {model : The model class name}
                            {--resource : Generate a resource controller}
                            {--api : Generate an API controller}
                            {--force : Overwrite existing controller}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a custom controller based on the TenantController template';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $model = $this->argument('model');
        $resource = $this->option('resource');
        $api = $this->option('api');
        $force = $this->option('force');

        // Normalize controller name
        if (!Str::endsWith($name, 'Controller')) {
            $name .= 'Controller';
        }

        // Determine controller path
        $controllerPath = app_path('Http/Controllers/' . $name . '.php');

        // Check if controller already exists
        if (File::exists($controllerPath) && !$force) {
            $this->error("Controller {$name} already exists. Use --force to overwrite.");
            return 1;
        }

        // Generate controller content
        $content = $this->generateControllerContent($name, $model, $resource, $api);

        // Create directory if it doesn't exist
        $directory = dirname($controllerPath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // Write controller file
        File::put($controllerPath, $content);

        $this->info("Controller {$name} generated successfully at {$controllerPath}");

        // Generate routes suggestion
        $this->generateRoutesSuggestion($name, $model, $resource, $api);

        return 0;
    }

    /**
     * Generate the controller content.
     */
    protected function generateControllerContent(string $name, string $model, bool $resource, bool $api): string
    {
        $namespace = 'App\\Http\\Controllers';
        $modelClass = "App\\Models\\{$model}";
        $modelVariable = Str::camel($model);
        $modelVariablePlural = Str::plural($modelVariable);
        $routeName = Str::kebab(Str::plural($model));

        $template = $this->getControllerTemplate();

        $replacements = [
            '{{namespace}}' => $namespace,
            '{{className}}' => $name,
            '{{modelClass}}' => $modelClass,
            '{{model}}' => $model,
            '{{modelVariable}}' => $modelVariable,
            '{{modelVariablePlural}}' => $modelVariablePlural,
            '{{routeName}}' => $routeName,
            '{{resourceMethods}}' => $resource ? $this->getResourceMethods($model, $modelVariable, $modelVariablePlural, $routeName) : '',
            '{{apiMethods}}' => $api ? $this->getApiMethods($model, $modelVariable, $modelVariablePlural) : '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    /**
     * Get the controller template.
     */
    protected function getControllerTemplate(): string
    {
        return '<?php

namespace {{namespace}};

use {{modelClass}};
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class {{className}} extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InertiaResponse|JsonResponse
    {
        $query = {{model}}::query();

        // Apply search
        if ($search = $request->get(\'search\')) {
            $query->where(function ($q) use ($search) {
                $q->where(\'name\', \'like\', "%{$search}%");
                // Add more searchable fields as needed
            });
        }

        // Apply filters
        if ($status = $request->get(\'status\')) {
            $query->where(\'status\', $status);
        }

        // Apply sorting
        $sortBy = $request->get(\'sort_by\', \'created_at\');
        $sortDirection = $request->get(\'sort_direction\', \'desc\');
        $query->orderBy($sortBy, $sortDirection);

        ${{modelVariablePlural}} = $query->paginate($request->get(\'per_page\', 15))
            ->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(${{modelVariablePlural}});
        }

        return Inertia::render(\'{{model}}/Index\', [
            \'{{modelVariablePlural}}\' => ${{modelVariablePlural}},
            \'filters\' => $request->only([\'search\', \'status\', \'sort_by\', \'sort_direction\']),
        ]);
    }

{{resourceMethods}}{{apiMethods}}
}';
    }

    /**
     * Get resource methods.
     */
    protected function getResourceMethods(string $model, string $modelVariable, string $modelVariablePlural, string $routeName): string
    {
        return "
    /**
     * Show the form for creating a new resource.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('{$model}/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request \$request): RedirectResponse
    {
        \$validated = \$request->validate([
            'name' => 'required|string|max:255',
            'status' => 'boolean',
            // Add validation rules as needed
        ]);

        \${$modelVariable} = {$model}::create(\$validated);

        return redirect()->route('{$routeName}.index')
            ->with('success', '{$model} created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show({$model} \${$modelVariable}): InertiaResponse
    {
        return Inertia::render('{$model}/Show', [
            '{$modelVariable}' => \${$modelVariable},
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit({$model} \${$modelVariable}): InertiaResponse
    {
        return Inertia::render('{$model}/Edit', [
            '{$modelVariable}' => \${$modelVariable},
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request \$request, {$model} \${$modelVariable}): RedirectResponse
    {
        \$validated = \$request->validate([
            'name' => 'required|string|max:255',
            'status' => 'boolean',
            // Add validation rules as needed
        ]);

        \${$modelVariable}->update(\$validated);

        return redirect()->route('{$routeName}.index')
            ->with('success', '{$model} updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy({$model} \${$modelVariable}): RedirectResponse
    {
        \${$modelVariable}->delete();

        return redirect()->route('{$routeName}.index')
            ->with('success', '{$model} deleted successfully.');
    }

    /**
     * Bulk destroy multiple resources.
     */
    public function bulkDestroy(Request \$request): JsonResponse|RedirectResponse
    {
        \$ids = \$request->input('ids', []);
        
        if (empty(\$ids)) {
            if (\$request->wantsJson()) {
                return response()->json(['message' => 'No items selected'], 400);
            }
            return back()->with('error', 'No items selected');
        }

        {$model}::whereIn('id', \$ids)->delete();

        \$message = count(\$ids) . ' {$modelVariablePlural} deleted successfully.';

        if (\$request->wantsJson()) {
            return response()->json(['message' => \$message]);
        }

        return back()->with('success', \$message);
    }

    /**
     * Toggle the status of the specified resource.
     */
    public function toggleStatus({$model} \${$modelVariable}): JsonResponse|RedirectResponse
    {
        \${$modelVariable}->update([
            'status' => !\${$modelVariable}->status
        ]);

        \$message = '{$model} status updated successfully.';

        if (request()->wantsJson()) {
            return response()->json([
                'message' => \$message,
                '{$modelVariable}' => \${$modelVariable}->fresh()
            ]);
        }

        return back()->with('success', \$message);
    }";
    }

    /**
     * Get API methods.
     */
    protected function getApiMethods(string $model, string $modelVariable, string $modelVariablePlural): string
    {
        return "
    /**
     * Store a newly created resource in storage (API).
     */
    public function apiStore(Request \$request): JsonResponse
    {
        \$validated = \$request->validate([
            'name' => 'required|string|max:255',
            'status' => 'boolean',
            // Add validation rules as needed
        ]);

        \${$modelVariable} = {$model}::create(\$validated);

        return response()->json([
            'message' => '{$model} created successfully.',
            '{$modelVariable}' => \${$modelVariable}
        ], 201);
    }

    /**
     * Display the specified resource (API).
     */
    public function apiShow({$model} \${$modelVariable}): JsonResponse
    {
        return response()->json(\${$modelVariable});
    }

    /**
     * Update the specified resource in storage (API).
     */
    public function apiUpdate(Request \$request, {$model} \${$modelVariable}): JsonResponse
    {
        \$validated = \$request->validate([
            'name' => 'required|string|max:255',
            'status' => 'boolean',
            // Add validation rules as needed
        ]);

        \${$modelVariable}->update(\$validated);

        return response()->json([
            'message' => '{$model} updated successfully.',
            '{$modelVariable}' => \${$modelVariable}
        ]);
    }

    /**
     * Remove the specified resource from storage (API).
     */
    public function apiDestroy({$model} \${$modelVariable}): JsonResponse
    {
        \${$modelVariable}->delete();

        return response()->json([
            'message' => '{$model} deleted successfully.'
        ]);
    }";
    }

    /**
     * Generate routes suggestion.
     */
    protected function generateRoutesSuggestion(string $name, string $model, bool $resource, bool $api): void
    {
        $routeName = Str::kebab(Str::plural($model));
        $controllerClass = $name;

        $this->info("\nSuggested routes to add to your routes file:");
        $this->line("// In routes/web.php or routes/api.php");

        if ($resource) {
            $this->line("Route::resource('{$routeName}', {$controllerClass}::class);");
            $this->line("Route::post('{$routeName}/bulk-destroy', [{$controllerClass}::class, 'bulkDestroy'])->name('{$routeName}.bulk-destroy');");
            $this->line("Route::patch('{$routeName}/{{$model}}/toggle-status', [{$controllerClass}::class, 'toggleStatus'])->name('{$routeName}.toggle-status');");
        }

        if ($api) {
            $this->line("\n// API routes");
            $this->line("Route::apiResource('{$routeName}', {$controllerClass}::class);");
        }
    }
}
