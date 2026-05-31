<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class InstallationScriptService
{
    protected $controlPanel;
    
    public function __construct($controlPanel, protected $gitRepo, protected $domain, protected $dbName, protected $dbUser, protected $dbPass)
    {
        $this->controlPanel = strtolower((string) $controlPanel);
    }

    public function generateScript(): string
    {
        $installDir = "~/laravel-apps/{$this->domain}";
        $publicHtmlPath = $this->getPublicHtmlPath();
        
        $script = [
            '#!/bin/bash',
            'set -e',
            '',
            '# Create installation directory',
            "mkdir -p {$installDir}",
            "cd {$installDir}",
            '',
            '# Clone repository',
            "git clone {$this->gitRepo} .",
            '',
            '# Install composer dependencies',
            'composer install --no-scripts --no-dev --optimize-autoloader',
            '',
            '# Install npm dependencies and build assets',
            'npm install',
            'npm run build',
            '',
            '# Setup Laravel environment',
            'cp .env.example .env',
            "sed -i 's/DB_DATABASE=.*/DB_DATABASE={$this->dbName}/' .env",
            "sed -i 's/DB_USERNAME=.*/DB_USERNAME={$this->dbUser}/' .env",
            "sed -i 's/DB_PASSWORD=.*/DB_PASSWORD={$this->dbPass}/' .env",
            '',
            '# Generate application key',
            'php artisan key:generate',
            '',
            '# Create storage link',
            'php artisan storage:link',
            '',
            '# Run migrations and seeders',
            'php artisan migrate --force',
            'php artisan db:seed --force',
            '',
            '# Set proper permissions',
            'find . -type f -exec chmod 644 {} \\;',
            'find . -type d -exec chmod 755 {} \\;',
            'chmod -R 775 storage bootstrap/cache',
            '',
            '# Create symbolic link',
            "ln -sf {$installDir}/public {$publicHtmlPath}",
            '',
            'echo "Installation completed successfully!"'
        ];
        
        return implode("\n", $script);
    }
    
    protected function getPublicHtmlPath(): string
    {
        return match ($this->controlPanel) {
            'cpanel' => "~/public_html",
            'plesk' => "~/httpdocs",
            'directadmin' => "~/domains/{$this->domain}/public_html",
            'virtualmin' => "~/public_html",
            default => throw new Exception("Unsupported control panel: {$this->controlPanel}"),
        };
    }
    
    public function execute(): bool
    {
        try {
            $script = $this->generateScript();
            $scriptPath = tempnam(sys_get_temp_dir(), 'laravel_install_');
            
            File::put($scriptPath, $script);
            chmod($scriptPath, 0755);
            
            $output = shell_exec($scriptPath . " 2>&1");
            unlink($scriptPath);
            
            Log::info("Installation script executed successfully", [
                'domain' => $this->domain,
                'output' => $output
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error("Installation script failed", [
                'domain' => $this->domain,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }
}