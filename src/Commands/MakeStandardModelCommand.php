<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class MakeStandardModelCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'papa-leguas:make-model {name : The name of the model} {--migration : Also create a migration}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new model following Papa Leguas standards';

    /**
     * The type of class being generated.
     */
    protected $type = 'Model';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $result = parent::handle();

        if ($this->option('migration')) {
            $this->createMigration();
        }

        return $result;
    }

    /**
     * Create the migration file.
     */
    protected function createMigration()
    {
        $name = $this->getNameInput();
        $table = Str::snake(Str::pluralStudly($name));
        
        $migrationName = "create_{$table}_table";
        
        $this->call('make:migration', [
            'name' => $migrationName,
            '--create' => $table,
        ]);

        $this->info('Migration created successfully.');
    }

    /**
     * Get the stub file for the generator.
     */
    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/model.stub';
    }

    /**
     * Get the default namespace for the class.
     */
    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Models';
    }

    /**
     * Build the class with the given name.
     */
    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replaceClass($stub, $name);
    }

    /**
     * Replace the class name for the given stub.
     */
    protected function replaceClass($stub, $name): string
    {
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('{{ModelName}}', $class, $stub);
    }
}
