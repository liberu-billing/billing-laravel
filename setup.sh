#!/bin/bash
# Setup script for the Liberu billing project.
#
# This script provides installation options for Standalone, Docker, or Kubernetes deployments.
# It handles composer and npm installations with fallback logic and error checking.

set -e  # Exit on error

# Colors for output
RED='\e[91m'
GREEN='\e[92m'
YELLOW='\e[93m'
BLUE='\e[94m'
RESET='\e[39m'

# Function to print colored messages
print_message() {
    local color=$1
    local message=$2
    echo -e "${color}${message}${RESET}"
}

print_header() {
    echo ""
    echo "=================================="
    echo "$1"
    echo "=================================="
    echo ""
}

print_error() {
    print_message "$RED" "❌ ERROR: $1"
}

print_success() {
    print_message "$GREEN" "✅ $1"
}

print_info() {
    print_message "$BLUE" "ℹ️  $1"
}

print_warning() {
    print_message "$YELLOW" "⚠️  $1"
}

# Check if a command exists
command_exists() {
    command -v "$1" >/dev/null 2>&1
}

# Download composer.phar if composer is not available
ensure_composer() {
    if command_exists composer; then
        print_success "Composer is already installed"
        COMPOSER_CMD=(composer)
        return 0
    fi

    print_warning "Composer command not found. Attempting to download composer.phar..."

    if ! command_exists curl; then
        print_error "curl is required to download composer. Please install curl or composer manually."
        return 1
    fi

    if ! command_exists php; then
        print_error "PHP is required. Please install PHP first."
        return 1
    fi

    # Download composer installer
    print_info "Downloading Composer installer..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"

    # Install Composer locally
    print_info "Installing Composer locally..."
    php composer-setup.php --quiet

    # Clean up installer
    php -r "unlink('composer-setup.php');"

    if [ -f "composer.phar" ]; then
        print_success "Composer.phar downloaded successfully"
        COMPOSER_CMD=(php composer.phar)
        return 0
    else
        print_error "Failed to download composer.phar"
        return 1
    fi
}

# Check PHP version meets minimum requirement
check_php_version() {
    if ! command_exists php; then
        print_error "PHP is not installed. Please install PHP 8.5 or higher."
        return 1
    fi

    local version
    version=$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
    local required="8.5"

    if php -r "exit(version_compare(PHP_VERSION, '${required}', '>=') ? 0 : 1);"; then
        print_success "PHP ${version} found (>= ${required} required)"
        return 0
    else
        print_error "PHP ${version} found but >= ${required} is required."
        return 1
    fi
}

# Install composer dependencies
install_composer_dependencies() {
    print_header "🎬 COMPOSER INSTALL"

    # Verify PHP version first
    if ! check_php_version; then
        print_error "Cannot proceed without a compatible PHP version"
        return 1
    fi

    # Check if vendor directory exists
    if [ -d "vendor" ] && [ -f "vendor/autoload.php" ]; then
        print_info "Vendor directory already exists. Skipping composer install."
        read -p "Do you want to reinstall composer dependencies? (y/n) " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_success "Skipping composer install"
            return 0
        fi
    fi

    # Ensure composer is available
    if ! ensure_composer; then
        print_error "Cannot proceed without Composer"
        return 1
    fi

    # Run composer install
    print_info "Running: ${COMPOSER_CMD[*]} install"
    if "${COMPOSER_CMD[@]}" install --no-interaction --prefer-dist; then
        print_success "Composer dependencies installed successfully"
        return 0
    else
        print_error "Composer install failed"
        return 1
    fi
}

# Install npm dependencies
install_npm_dependencies() {
    print_header "🎬 NPM INSTALL"

    # Check if node_modules directory exists
    if [ -d "node_modules" ]; then
        print_info "node_modules directory already exists. Skipping npm install."
        read -p "Do you want to reinstall npm dependencies? (y/n) " -n 1 -r
        echo
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            print_success "Skipping npm install"
            return 0
        fi
    fi

    # Check if npm is available
    if ! command_exists npm; then
        print_error "npm is not installed. Please install Node.js and npm first."
        print_info "Visit: https://nodejs.org/"
        return 1
    fi

    # Run npm install
    print_info "Running: npm install"
    if npm install; then
        print_success "NPM dependencies installed successfully"
        return 0
    else
        print_error "NPM install failed"
        return 1
    fi
}

# Build frontend assets
build_frontend_assets() {
    print_header "🎬 NPM BUILD"

    # Check if npm is available
    if ! command_exists npm; then
        print_error "npm is not installed. Cannot build assets."
        return 1
    fi

    # Run npm build
    print_info "Running: npm run build"
    if npm run build; then
        print_success "Frontend assets built successfully"
        return 0
    else
        print_error "NPM build failed"
        return 1
    fi
}

# Standalone installation
install_standalone() {
    print_header "STANDALONE INSTALLATION"
    print_info "Starting standalone installation process..."

    clear
    echo "=================================="
    echo "===== USER: [$(whoami)]"
    echo "===== [PHP $(php -r 'echo phpversion();')]"
    echo "=================================="
    echo ""

    # Setup the .env file
    copy=true
    while true; do
        read -p "🎬 DEV ---> DID YOU WANT TO COPY THE .ENV.EXAMPLE TO .ENV? (y/n) " yn
        case $yn in
            [Yy]* )
                print_success "Copying .env.example to .env"
                cp .env.example .env
                copy=true
                break
                ;;
            [Nn]* )
                print_success "Continuing with your .env configuration"
                copy=false
                break
                ;;
            * )
                print_warning "Please answer yes or no."
                ;;
        esac
    done

    echo ""
    echo "=================================="
    echo ""

    # Ask user to confirm that .env file is properly setup before continuing
    if [ "$copy" = true ]; then
        while true; do
            read -p "🎬 DEV ---> DID YOU SETUP YOUR DATABASE CREDENTIALS IN THE .ENV FILE? (y/n) " cond
            case $cond in
                [Yy]* )
                    print_success "Perfect let's continue with the setup"
                    break
                    ;;
                [Nn]* )
                    print_warning "Please setup your .env file and run this script again"
                    exit 0
                    ;;
                * )
                    print_warning "Please answer yes or no."
                    ;;
            esac
        done
    fi

    echo ""
    echo "=================================="
    echo ""

    # Install composer dependencies
    if ! install_composer_dependencies; then
        print_error "Installation failed at composer install step"
        exit 1
    fi

    echo ""
    echo "=================================="
    echo ""

    # Upgrade Filament assets (no-op if Filament is not installed)
    print_header "🎬 FILAMENT UPGRADE"
    if php artisan filament:upgrade 2>/dev/null; then
        print_success "Filament assets upgraded"
    else
        print_warning "Filament upgrade skipped (Filament not installed or DB not ready)"
    fi

    echo ""
    echo "=================================="
    echo ""

    # Install npm dependencies
    if ! install_npm_dependencies; then
        print_warning "NPM install failed, but continuing..."
    fi

    echo ""
    echo "=================================="
    echo ""

    # Build frontend assets
    if ! build_frontend_assets; then
        print_warning "NPM build failed, but continuing..."
    fi

    echo ""
    echo "=================================="
    echo ""

    # Generate Laravel key
    print_header "🎬 PHP ARTISAN KEY:GENERATE"
    if php artisan key:generate; then
        print_success "Application key generated"
    else
        print_error "Failed to generate application key"
        exit 1
    fi

    echo ""
    echo "=================================="
    echo ""

    # Create storage symlink
    print_header "🎬 PHP ARTISAN STORAGE:LINK"
    if php artisan storage:link 2>/dev/null; then
        print_success "Storage symlink created"
    else
        print_warning "Storage symlink already exists or failed — continuing"
    fi

    echo ""
    echo "=================================="
    echo ""

    # Run database migrations and seed
    print_header "🎬 DATABASE MIGRATION"
    print_warning "migrate:fresh DROPS ALL TABLES and re-runs all migrations."
    while true; do
        read -p "Use migrate:fresh --seed (drop all + reseed)? Or migrate (safe)? (fresh/migrate) " migrate_choice
        case "$migrate_choice" in
            fresh)
                if php artisan migrate:fresh --seed; then
                    print_success "Database migrated (fresh) and seeded"
                else
                    print_error "Database migration failed"
                    exit 1
                fi
                break
                ;;
            migrate)
                if php artisan migrate --seed --no-interaction; then
                    print_success "Database migrated and seeded"
                else
                    print_error "Database migration failed"
                    exit 1
                fi
                break
                ;;
            *)
                print_warning "Please enter 'fresh' or 'migrate'."
                ;;
        esac
    done

    echo ""
    echo "=================================="
    echo ""

    # Run tests (prefer Pest, fall back to PHPUnit)
    print_header "🎬 RUNNING TESTS"
    if [ -f "vendor/bin/pest" ]; then
        print_info "Running: ./vendor/bin/pest"
        if ./vendor/bin/pest; then
            print_success "Pest tests passed"
        else
            print_warning "Pest tests failed. Please review the errors."
        fi
    elif [ -f "vendor/bin/phpunit" ]; then
        print_info "Running: ./vendor/bin/phpunit"
        if ./vendor/bin/phpunit; then
            print_success "PHPUnit tests passed"
        else
            print_warning "PHPUnit tests failed. Please review the errors."
        fi
    else
        print_warning "No test runner found. Skipping tests."
    fi

    echo ""
    echo "=================================="
    echo ""

    # Run optimization commands for Laravel
    print_header "🎬 PHP ARTISAN OPTIMIZE:CLEAR"
    php artisan optimize:clear
    php artisan route:clear
    php artisan view:clear
    php artisan config:clear

    echo ""
    print_success "=================================="
    print_success "============== DONE =============="
    print_success "=================================="
    echo ""

    # Ask if user wants to start the server
    while true; do
        read -p "🎬 DEV ---> DID YOU WANT TO START THE SERVER? (y/n) " cond
        case $cond in
            [Yy]* )
                print_success "Starting server..."
                print_info "Tip: use 'php artisan octane:start' for better performance in production."
                php artisan serve
                break
                ;;
            [Nn]* )
                print_success "Installation complete."
                print_info "Start with:  php artisan octane:start --server=roadrunner"
                print_info "Or dev mode: php artisan serve"
                exit 0
                ;;
            * )
                print_warning "Please answer yes or no."
                ;;
        esac
    done
}

# Docker installation
install_docker() {
    print_header "DOCKER INSTALLATION"
    print_info "Starting Docker installation process..."

    # Check if Docker is installed
    if ! command_exists docker; then
        print_error "Docker is not installed. Please install Docker first."
        print_info "Visit: https://docs.docker.com/get-docker/"
        exit 1
    fi

    print_success "Docker is installed"

    # Check for docker-compose
    if ! command_exists docker-compose && ! docker compose version >/dev/null 2>&1; then
        print_error "Docker Compose is not installed. Please install Docker Compose first."
        print_info "Visit: https://docs.docker.com/compose/install/"
        exit 1
    fi

    print_success "Docker Compose is available"

    # Setup .env file
    if [ ! -f ".env" ]; then
        print_info "Copying .env.example to .env"
        cp .env.example .env
        print_warning "Please edit .env file to configure your Docker environment"
        read -p "Press Enter to continue after editing .env..."
    fi

    # Build and start containers
    print_info "Building and starting Docker containers..."
    DOCKER_CMD="docker compose"
    command_exists docker-compose && DOCKER_CMD="docker-compose"

    if $DOCKER_CMD up -d --build; then
        print_success "Docker containers started successfully"
        print_info "Application available at http://localhost:8000"
        print_info "Run '$DOCKER_CMD logs -f' to follow logs"
    else
        print_error "Failed to start Docker containers"
        exit 1
    fi
}

# Kubernetes installation
install_kubernetes() {
    print_header "KUBERNETES INSTALLATION"
    print_info "Starting Kubernetes installation process..."

    # Check if kubectl is installed
    if ! command_exists kubectl; then
        print_error "kubectl is not installed. Please install kubectl first."
        print_info "Visit: https://kubernetes.io/docs/tasks/tools/"
        exit 1
    fi

    print_success "kubectl found: $(kubectl version --client 2>/dev/null | head -1)"

    # Verify cluster connectivity
    if ! kubectl cluster-info >/dev/null 2>&1; then
        print_error "Cannot connect to a Kubernetes cluster."
        print_info "Ensure your kubeconfig is set up correctly (kubectl cluster-info)."
        exit 1
    fi

    print_success "Kubernetes cluster reachable"

    # Locate k8s directory
    K8S_DIR="k8s"
    if [ ! -d "$K8S_DIR" ] && [ -d "kubernetes" ]; then
        K8S_DIR="kubernetes"
    fi

    if [ ! -d "$K8S_DIR" ]; then
        print_error "No Kubernetes configuration directory found (k8s/ or kubernetes/)"
        exit 1
    fi

    print_info "Using Kubernetes configurations from: $K8S_DIR/"

    # Choose overlay
    echo ""
    echo "Select deployment environment:"
    echo "  1) production"
    echo "  2) development"
    echo ""

    OVERLAY="production"
    while true; do
        read -p "Enter your choice (1-2, default: 1): " env_choice
        case "${env_choice:-1}" in
            1) OVERLAY="production"; break ;;
            2) OVERLAY="development"; break ;;
            *) print_warning "Please enter 1 or 2." ;;
        esac
    done

    if [ ! -d "$K8S_DIR/overlays/$OVERLAY" ]; then
        print_error "Overlay '$OVERLAY' not found at $K8S_DIR/overlays/$OVERLAY"
        exit 1
    fi

    print_info "Deploying overlay: $OVERLAY"

    # Validate first (optional — continue on failure)
    if [ -f "$K8S_DIR/validate.sh" ]; then
        print_info "Running pre-deploy validation..."
        if bash "$K8S_DIR/validate.sh" "$OVERLAY"; then
            print_success "Validation passed"
        else
            print_warning "Validation reported issues. Review above before proceeding."
            read -p "Continue anyway? (y/n) " -n 1 -r; echo
            [[ ! $REPLY =~ ^[Yy]$ ]] && { print_info "Deployment cancelled."; exit 0; }
        fi
    fi

    # Apply via kustomize
    print_info "Applying: kubectl apply -k $K8S_DIR/overlays/$OVERLAY"
    if kubectl apply -k "$K8S_DIR/overlays/$OVERLAY"; then
        print_success "Kubernetes resources applied successfully"
    else
        print_error "Failed to apply Kubernetes configurations"
        exit 1
    fi

    # Wait for rollout
    print_info "Waiting for rollout (timeout: 5m)..."
    NAMESPACE=$(kubectl kustomize "$K8S_DIR/overlays/$OVERLAY" 2>/dev/null \
        | grep '^  namespace:' | head -1 | awk '{print $2}')
    NAMESPACE="${NAMESPACE:-liberu-billing}"

    if kubectl rollout status deployment/liberu-billing-app -n "$NAMESPACE" --timeout=5m; then
        print_success "Deployment rolled out successfully"
    else
        print_warning "Rollout did not complete within 5 minutes — check: kubectl get pods -n $NAMESPACE"
    fi

    echo ""
    print_success "Deployment complete!"
    print_info "Useful commands:"
    print_info "  kubectl get pods -n $NAMESPACE"
    print_info "  kubectl logs -f deployment/liberu-billing-app -n $NAMESPACE"
    print_info "  kubectl describe deployment/liberu-billing-app -n $NAMESPACE"
    print_info "  ./k8s/deploy.sh $OVERLAY"
}

# Main installation menu
main() {
    clear
    print_header "LIBERU BILLING - INSTALLER"

    echo "Please select installation type:"
    echo ""
    echo "  1) Standalone (Local development/production)"
    echo "  2) Docker (Containerized deployment)"
    echo "  3) Kubernetes (K8s cluster deployment)"
    echo "  4) Exit"
    echo ""

    while true; do
        read -p "Enter your choice (1-4): " choice
        case $choice in
            1)
                install_standalone
                break
                ;;
            2)
                install_docker
                break
                ;;
            3)
                install_kubernetes
                break
                ;;
            4)
                print_info "Installation cancelled"
                exit 0
                ;;
            *)
                print_warning "Invalid choice. Please enter 1, 2, 3, or 4."
                ;;
        esac
    done
}

# Run main function
main
