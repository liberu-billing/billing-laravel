<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name : The PascalCase module name} {--force : Overwrite if exists}';

    protected $description = 'Scaffold a new application module';

    public function handle(): int
    {
        $name = $this->argument('name');

        if (! preg_match('/^[A-Z][A-Za-z0-9]+$/', $name)) {
            $this->error("Module name must start with an uppercase letter and contain only alphanumeric characters.");

            return self::FAILURE;
        }

        $modulePath = app_path("Modules/{$name}");

        if (File::exists($modulePath) && ! $this->option('force')) {
            $this->error("Module '{$name}' already exists. Use --force to overwrite.");

            return self::FAILURE;
        }

        $this->createDirectories($modulePath);
        $this->createModuleJson($name, $modulePath);
        $this->createModuleClass($name, $modulePath);
        $this->createServiceProvider($name, $modulePath);
        $this->createRoutes($name, $modulePath);
        $this->createController($name, $modulePath);
        $this->createModel($name, $modulePath);
        $this->createMigration($name, $modulePath);
        $this->createView($name, $modulePath);
        $this->createConfig($name, $modulePath);
        $this->createFilamentResource($name, $modulePath);
        $this->createTest($name, $modulePath);

        $this->info("Module '{$name}' created at app/Modules/{$name}");
        $this->line("Run: <comment>composer dump-autoload</comment> to register the new module.");

        return self::SUCCESS;
    }

    private function createDirectories(string $modulePath): void
    {
        $dirs = [
            'Filament/Resources',
            'Filament/Pages',
            'Filament/Widgets',
            'Http/Controllers',
            'Http/Middleware',
            'Models',
            'Providers',
            'Services',
            'config',
            'database/migrations',
            'database/seeders',
            'resources/assets',
            'resources/lang',
            'resources/views',
            'routes',
            'tests',
        ];

        foreach ($dirs as $dir) {
            File::makeDirectory("{$modulePath}/{$dir}", 0755, true, true);
        }
    }

    private function createModuleJson(string $name, string $modulePath): void
    {
        File::put("{$modulePath}/module.json", json_encode([
            'name' => $name,
            'version' => '1.0.0',
            'description' => "The {$name} module.",
            'dependencies' => [],
            'config' => [],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)."\n");
    }

    private function createModuleClass(string $name, string $modulePath): void
    {
        File::put("{$modulePath}/{$name}Module.php", <<<PHP
        <?php

        declare(strict_types=1);

        namespace App\Modules\\{$name};

        use App\Modules\BaseModule;
        use Illuminate\Support\Facades\Log;

        class {$name}Module extends BaseModule
        {
            protected function onEnable(): void
            {
                Log::info('{$name} module enabled.');
            }

            protected function onDisable(): void
            {
                Log::info('{$name} module disabled.');
            }

            protected function onInstall(): void
            {
                Log::info('{$name} module installed.');
            }

            protected function onUninstall(): void
            {
                Log::info('{$name} module uninstalled.');
            }
        }
        PHP);
    }

    private function createServiceProvider(string $name, string $modulePath): void
    {
        File::put("{$modulePath}/Providers/{$name}ServiceProvider.php", <<<PHP
        <?php

        declare(strict_types=1);

        namespace App\Modules\\{$name}\\Providers;

        use Illuminate\Support\ServiceProvider;

        class {$name}ServiceProvider extends ServiceProvider
        {
            public function register(): void {}

            public function boot(): void
            {
                // Filament resources, pages, and widgets in Filament/ are auto-discovered
                // by ModuleServiceProvider via PanelRegistry. No manual registration needed.
            }
        }
        PHP);
    }

    private function createFilamentResource(string $name, string $modulePath): void
    {
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
        $plural = $snake.'s';

        File::makeDirectory("{$modulePath}/Filament/Resources/{$name}Resource/Pages", 0755, true, true);

        File::put("{$modulePath}/Filament/Resources/{$name}Resource.php", <<<PHP
        <?php

        declare(strict_types=1);

        namespace App\Modules\\{$name}\\Filament\\Resources;

        use App\Modules\\{$name}\\Filament\\Resources\\{$name}Resource\\Pages;
        use App\Modules\\{$name}\\Models\\{$name};
        use Filament\Forms;
        use Filament\Forms\Form;
        use Filament\Resources\Resource;
        use Filament\Tables;
        use Filament\Tables\Table;

        class {$name}Resource extends Resource
        {
            protected static ?string \$model = {$name}::class;

            protected static ?string \$navigationIcon = 'heroicon-o-rectangle-stack';

            protected static ?string \$navigationGroup = '{$name}';

            public static function form(Form \$form): Form
            {
                return \$form->schema([
                    Forms\Components\TextInput::make('id')->disabled(),
                ]);
            }

            public static function table(Table \$table): Table
            {
                return \$table
                    ->columns([
                        Tables\Columns\TextColumn::make('id')->sortable(),
                        Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
                    ])
                    ->filters([])
                    ->actions([
                        Tables\Actions\EditAction::make(),
                        Tables\Actions\DeleteAction::make(),
                    ])
                    ->bulkActions([
                        Tables\Actions\BulkActionGroup::make([
                            Tables\Actions\DeleteBulkAction::make(),
                        ]),
                    ]);
            }

            public static function getRelations(): array
            {
                return [];
            }

            public static function getPages(): array
            {
                return [
                    'index' => Pages\\List{$name}s::route('/'),
                    'create' => Pages\\Create{$name}::route('/create'),
                    'edit' => Pages\\Edit{$name}::route('/{record}/edit'),
                ];
            }
        }
        PHP);

        File::put("{$modulePath}/Filament/Resources/{$name}Resource/Pages/List{$name}s.php", <<<PHP
        <?php

        declare(strict_types=1);

        namespace App\Modules\\{$name}\\Filament\\Resources\\{$name}Resource\\Pages;

        use App\Modules\\{$name}\\Filament\\Resources\\{$name}Resource;
        use Filament\Actions;
        use Filament\Resources\Pages\ListRecords;

        class List{$name}s extends ListRecords
        {
            protected static string \$resource = {$name}Resource::class;

            protected function getHeaderActions(): array
            {
                return [
                    Actions\CreateAction::make(),
                ];
            }
        }
        PHP);

        File::put("{$modulePath}/Filament/Resources/{$name}Resource/Pages/Create{$name}.php", <<<PHP
        <?php

        declare(strict_types=1);

        namespace App\Modules\\{$name}\\Filament\\Resources\\{$name}Resource\\Pages;

        use App\Modules\\{$name}\\Filament\\Resources\\{$name}Resource;
        use Filament\Resources\Pages\CreateRecord;

        class Create{$name} extends CreateRecord
        {
            protected static string \$resource = {$name}Resource::class;
        }
        PHP);

        File::put("{$modulePath}/Filament/Resources/{$name}Resource/Pages/Edit{$name}.php", <<<PHP
        <?php

        declare(strict_types=1);

        namespace App\Modules\\{$name}\\Filament\\Resources\\{$name}Resource\\Pages;

        use App\Modules\\{$name}\\Filament\\Resources\\{$name}Resource;
        use Filament\Actions;
        use Filament\Resources\Pages\EditRecord;

        class Edit{$name} extends EditRecord
        {
            protected static string \$resource = {$name}Resource::class;

            protected function getHeaderActions(): array
            {
                return [
                    Actions\DeleteAction::make(),
                ];
            }
        }
        PHP);
    }

    private function createRoutes(string $name, string $modulePath): void
    {
        $snake = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));

        File::put("{$modulePath}/routes/web.php", <<<PHP
        <?php

        use Illuminate\Support\Facades\Route;

        Route::middleware(['web', 'auth'])->prefix('{$snake}')->name('{$snake}.')->group(function () {
            // {$name} module web routes
        });
        PHP);

        File::put("{$modulePath}/routes/api.php", <<<PHP
        <?php

        use Illuminate\Support\Facades\Route;

        Route::middleware(['api', 'auth:sanctum'])->prefix('api/{$snake}')->name('api.{$snake}.')->group(function () {
            // {$name} module API routes
        });
        PHP);
    }

    private function createController(string $name, string $modulePath): void
    {
        File::put("{$modulePath}/Http/Controllers/{$name}Controller.php", <<<PHP
        <?php

        declare(strict_types=1);

        namespace App\Modules\\{$name}\\Http\\Controllers;

        use Illuminate\Http\Request;
        use Illuminate\Routing\Controller;

        class {$name}Controller extends Controller
        {
            public function index(Request \$request)
            {
                return view('{$name}::index');
            }
        }
        PHP);
    }

    private function createModel(string $name, string $modulePath): void
    {
        File::put("{$modulePath}/Models/{$name}.php", <<<PHP
        <?php

        declare(strict_types=1);

        namespace App\Modules\\{$name}\\Models;

        use Illuminate\Database\Eloquent\Model;

        class {$name} extends Model
        {
            protected \$guarded = [];
        }
        PHP);
    }

    private function createMigration(string $name, string $modulePath): void
    {
        $table = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name)).'s';
        $timestamp = now()->format('Y_m_d_His');

        File::put("{$modulePath}/database/migrations/{$timestamp}_create_{$table}_table.php", <<<PHP
        <?php

        use Illuminate\Database\Migrations\Migration;
        use Illuminate\Database\Schema\Blueprint;
        use Illuminate\Support\Facades\Schema;

        return new class extends Migration {
            public function up(): void
            {
                Schema::create('{$table}', function (Blueprint \$table) {
                    \$table->id();
                    \$table->timestamps();
                });
            }

            public function down(): void
            {
                Schema::dropIfExists('{$table}');
            }
        };
        PHP);
    }

    private function createView(string $name, string $modulePath): void
    {
        File::put("{$modulePath}/resources/views/index.blade.php", <<<BLADE
        <x-app-layout>
            <x-slot name="header">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {$name}
                </h2>
            </x-slot>

            <div class="py-12">
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <p>Welcome to the {$name} module.</p>
                    </div>
                </div>
            </div>
        </x-app-layout>
        BLADE);
    }

    private function createConfig(string $name, string $modulePath): void
    {
        $key = strtolower($name);

        File::put("{$modulePath}/config/{$key}.php", <<<PHP
        <?php

        return [
            // {$name} module configuration
        ];
        PHP);
    }

    private function createTest(string $name, string $modulePath): void
    {
        File::put("{$modulePath}/tests/{$name}ModuleTest.php", <<<PHP
        <?php

        declare(strict_types=1);

        namespace App\Modules\\{$name}\\Tests;

        use App\Modules\\{$name}\\{$name}Module;
        use Tests\TestCase;

        class {$name}ModuleTest extends TestCase
        {
            public function test_module_can_be_instantiated(): void
            {
                \$module = new {$name}Module;
                \$this->assertSame('{$name}', \$module->getName());
            }
        }
        PHP);
    }
}
