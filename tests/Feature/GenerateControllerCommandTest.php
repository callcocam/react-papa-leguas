<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

describe('Generate Controller Command', function () {
    
    afterEach(function () {
        // Clean up generated files
        $paths = [
            app_path('Http/Controllers/TestController.php'),
            app_path('Http/Controllers/Admin/TestController.php'),
            app_path('Http/Controllers/Landlord/TestController.php'),
        ];
        
        foreach ($paths as $path) {
            if (File::exists($path)) {
                File::delete($path);
            }
        }
    });

    describe('Basic Controller Generation', function () {
        it('can generate a basic controller', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
            ]);

            $output = Artisan::output();
            expect($output)->toContain('Controller created successfully');
            
            $controllerPath = app_path('Http/Controllers/TestController.php');
            expect(File::exists($controllerPath))->toBeTrue();
            
            $content = File::get($controllerPath);
            expect($content)->toContain('class TestController');
            expect($content)->toContain('namespace App\Http\Controllers');
        });

        it('can generate controller with model', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
                '--model' => 'User',
            ]);

            $controllerPath = app_path('Http/Controllers/TestController.php');
            $content = File::get($controllerPath);
            
            expect($content)->toContain('use App\Models\User');
            expect($content)->toContain('$user');
            expect($content)->toContain('User::');
        });

        it('can generate resource controller', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
                '--resource' => true,
            ]);

            $controllerPath = app_path('Http/Controllers/TestController.php');
            $content = File::get($controllerPath);
            
            expect($content)->toContain('public function index()');
            expect($content)->toContain('public function create()');
            expect($content)->toContain('public function store(');
            expect($content)->toContain('public function show(');
            expect($content)->toContain('public function edit(');
            expect($content)->toContain('public function update(');
            expect($content)->toContain('public function destroy(');
        });

        it('can generate API controller', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
                '--api' => true,
            ]);

            $controllerPath = app_path('Http/Controllers/TestController.php');
            $content = File::get($controllerPath);
            
            expect($content)->toContain('public function index()');
            expect($content)->toContain('public function store(');
            expect($content)->toContain('public function show(');
            expect($content)->toContain('public function update(');
            expect($content)->toContain('public function destroy(');
            expect($content)->not->toContain('public function create()');
            expect($content)->not->toContain('public function edit(');
        });
    });

    describe('Controller Types', function () {
        it('can generate admin controller', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
                '--type' => 'admin',
            ]);

            $controllerPath = app_path('Http/Controllers/Admin/TestController.php');
            expect(File::exists($controllerPath))->toBeTrue();
            
            $content = File::get($controllerPath);
            expect($content)->toContain('namespace App\Http\Controllers\Admin');
            expect($content)->toContain('use Callcocam\ReactPapaLeguas\Core\Concerns\BelongsToTenant');
        });

        it('can generate landlord controller', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
                '--type' => 'landlord',
            ]);

            $controllerPath = app_path('Http/Controllers/Landlord/TestController.php');
            expect(File::exists($controllerPath))->toBeTrue();
            
            $content = File::get($controllerPath);
            expect($content)->toContain('namespace App\Http\Controllers\Landlord');
            expect($content)->toContain('use Callcocam\ReactPapaLeguas\Core\Concerns\BelongsToLandlord');
        });
    });

    describe('Features', function () {
        it('can generate controller with table feature', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
                '--table' => true,
                '--model' => 'User',
            ]);

            $controllerPath = app_path('Http/Controllers/TestController.php');
            $content = File::get($controllerPath);
            
            expect($content)->toContain('use Callcocam\ReactPapaLeguas\Core\Table\Table');
            expect($content)->toContain('protected function configureTable()');
            expect($content)->toContain('return Table::make()');
        });

        it('can generate controller with form feature', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
                '--form' => true,
                '--model' => 'User',
            ]);

            $controllerPath = app_path('Http/Controllers/TestController.php');
            $content = File::get($controllerPath);
            
            expect($content)->toContain('use Callcocam\ReactPapaLeguas\Core\Concerns\BelongsToForm');
            expect($content)->toContain('protected function getValidationRules()');
        });

        it('can generate controller with both table and form features', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
                '--table' => true,
                '--form' => true,
                '--model' => 'User',
            ]);

            $controllerPath = app_path('Http/Controllers/TestController.php');
            $content = File::get($controllerPath);
            
            expect($content)->toContain('use Callcocam\ReactPapaLeguas\Core\Table\Table');
            expect($content)->toContain('use Callcocam\ReactPapaLeguas\Core\Concerns\BelongsToForm');
            expect($content)->toContain('protected function configureTable()');
            expect($content)->toContain('protected function getValidationRules()');
        });
    });

    describe('Model Integration', function () {
        it('generates proper model imports and usage', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'PostController',
                '--model' => 'Post',
                '--resource' => true,
            ]);

            $controllerPath = app_path('Http/Controllers/PostController.php');
            $content = File::get($controllerPath);
            
            expect($content)->toContain('use App\Models\Post');
            expect($content)->toContain('Post $post');
            expect($content)->toContain('Post::query()');
            expect($content)->toContain('Post::create(');
        });

        it('handles custom model namespaces', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
                '--model' => 'Custom\Models\CustomModel',
            ]);

            $controllerPath = app_path('Http/Controllers/TestController.php');
            $content = File::get($controllerPath);
            
            expect($content)->toContain('use Custom\Models\CustomModel');
            expect($content)->toContain('CustomModel $customModel');
        });
    });

    describe('Validation and Error Handling', function () {
        it('prevents overwriting existing controllers without force flag', function () {
            // Create a controller first
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
            ]);

            // Try to create it again
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
            ]);

            $output = Artisan::output();
            expect($output)->toContain('Controller already exists');
        });

        it('can force overwrite existing controllers', function () {
            // Create a controller first
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
            ]);

            // Overwrite with force flag
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
                '--force' => true,
            ]);

            $output = Artisan::output();
            expect($output)->toContain('Controller created successfully');
        });

        it('validates controller name format', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'invalid-name',
            ]);

            $output = Artisan::output();
            expect($output)->toContain('Invalid controller name');
        });
    });

    describe('Stub Selection', function () {
        it('uses correct stub for basic controller', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
            ]);

            $controllerPath = app_path('Http/Controllers/TestController.php');
            $content = File::get($controllerPath);
            
            expect($content)->toContain('class TestController extends Controller');
            expect($content)->not->toContain('public function index()');
        });

        it('uses correct stub for resource controller', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
                '--resource' => true,
                '--model' => 'User',
            ]);

            $controllerPath = app_path('Http/Controllers/TestController.php');
            $content = File::get($controllerPath);
            
            expect($content)->toContain('public function index()');
            expect($content)->toContain('public function create()');
            expect($content)->toContain('public function store(');
        });

        it('uses correct stub for API controller', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'TestController',
                '--api' => true,
                '--model' => 'User',
            ]);

            $controllerPath = app_path('Http/Controllers/TestController.php');
            $content = File::get($controllerPath);
            
            expect($content)->toContain('public function index()');
            expect($content)->not->toContain('public function create()');
            expect($content)->not->toContain('public function edit(');
        });
    });

    describe('File Output', function () {
        it('creates controller in correct directory structure', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'Admin/TestController',
            ]);

            $controllerPath = app_path('Http/Controllers/Admin/TestController.php');
            expect(File::exists($controllerPath))->toBeTrue();
            
            $content = File::get($controllerPath);
            expect($content)->toContain('namespace App\Http\Controllers\Admin');
        });

        it('creates necessary directories', function () {
            Artisan::call('papa-leguas:generate-controller', [
                'name' => 'Deep/Nested/TestController',
            ]);

            $controllerPath = app_path('Http/Controllers/Deep/Nested/TestController.php');
            expect(File::exists($controllerPath))->toBeTrue();
            
            $content = File::get($controllerPath);
            expect($content)->toContain('namespace App\Http\Controllers\Deep\Nested');
        });
    });
});
