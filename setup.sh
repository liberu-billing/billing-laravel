#!/bin/bash
# Installation script for the billing-laravel project.
#
# This script provides installation options for Standalone, Docker, or Kubernetes deployments.

set -e

RED='\e[91m'
GREEN='\e[92m'
YELLOW='\e[93m'
BLUE='\e[94m'
RESET='\e[39m'

print_message() { echo -e "${1}${2}${RESET}"; }
print_header() { echo ""; echo "=================================="; echo "$1"; echo "=================================="; echo ""; }
print_error() { print_message "$RED" "❌ ERROR: $1"; }
print_success() { print_message "$GREEN" "✅ $1"; }
print_info() { print_message "$BLUE" "ℹ️  $1"; }
print_warning() { print_message "$YELLOW" "⚠️  $1"; }

command_exists() { command -v "$1" >/dev/null 2>&1; }

ensure_composer() {
    if command_exists composer; then
        COMPOSER_CMD="composer"
        return 0
    fi
    print_warning "Composer not found. Downloading composer.phar..."
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    php composer-setup.php --quiet
    php -r "unlink('composer-setup.php');"
    if [ -f "composer.phar" ]; then
        COMPOSER_CMD="php composer.phar"
        return 0
    fi
    print_error "Failed to download composer.phar"
    return 1
}

install_composer_dependencies() {
    print_header "COMPOSER INSTALL"
    if ! ensure_composer; then
        print_error "Cannot proceed without Composer"
        return 1
    fi
    if eval "$COMPOSER_CMD install --no-interaction --prefer-dist"; then
        print_success "Composer dependencies installed"
        return 0
    fi
    print_error "Composer install failed"
    return 1
}

install_npm_dependencies() {
    print_header "NPM INSTALL"
    if ! command_exists npm; then
        print_error "npm is not installed."
        return 1
    fi
    if npm install; then
        print_success "NPM dependencies installed"
        return 0
    fi
    print_error "NPM install failed"
    return 1
}

build_frontend_assets() {
    print_header "NPM BUILD"
    if ! command_exists npm; then
        print_error "npm is not installed."
        return 1
    fi
    if npm run build; then
        print_success "Frontend assets built"
        return 0
    fi
    print_error "NPM build failed"
    return 1
}

install_standalone() {
    print_header "STANDALONE INSTALLATION"
    clear
    echo "=================================="
    echo "===== USER: [$(whoami)]"
    echo "===== [PHP $(php -r 'echo phpversion();')]"
    echo "=================================="
    echo ""

    copy=true
    while true; do
        read -p "Did you want to copy .env.example to .env? (y/n) " yn
        case $yn in
            [Yy]* ) cp .env.example .env; copy=true; break;;
            [Nn]* ) copy=false; break;;
            * ) print_warning "Please answer yes or no.";;
        esac
    done

    if [ "$copy" = true ]; then
        while true; do
            read -p "Did you setup your database credentials in .env? (y/n) " cond
            case $cond in
                [Yy]* ) break;;
                [Nn]* ) print_warning "Please setup .env and re-run."; exit 0;;
                * ) print_warning "Please answer yes or no.";;
            esac
        done
    fi

    install_composer_dependencies || exit 1
    install_npm_dependencies || print_warning "NPM install failed, continuing..."
    build_frontend_assets || print_warning "NPM build failed, continuing..."

    print_header "PHP ARTISAN KEY:GENERATE"
    php artisan key:generate || exit 1

    print_header "PHP ARTISAN MIGRATE:FRESH"
    php artisan migrate:fresh || exit 1

    print_header "PHP ARTISAN DB:SEED"
    php artisan db:seed || exit 1

    print_header "RUNNING PHPUNIT TESTS"
    if [ -f "vendor/bin/phpunit" ]; then
        ./vendor/bin/phpunit || print_warning "Tests failed. Please review."
    fi

    php artisan optimize:clear
    php artisan route:clear

    print_success "=================================="
    print_success "============== DONE =============="
    print_success "=================================="

    while true; do
        read -p "Did you want to start the server? (y/n) " cond
        case $cond in
            [Yy]* ) php artisan serve; break;;
            [Nn]* ) exit 0;;
            * ) print_warning "Please answer yes or no.";;
        esac
    done
}

install_docker() {
    print_header "DOCKER INSTALLATION"
    if ! command_exists docker; then
        print_error "Docker is not installed."
        exit 1
    fi
    if [ ! -f ".env" ]; then
        cp .env.example .env
        print_warning "Please edit .env before continuing."
        read -p "Press Enter to continue..."
    fi
    if command_exists docker-compose; then
        docker-compose up -d --build
    else
        docker compose up -d --build
    fi
    print_success "Docker containers started. App available at http://localhost:8000"
}

install_kubernetes() {
    print_header "KUBERNETES INSTALLATION"
    if ! command_exists kubectl; then
        print_error "kubectl is not installed."
        exit 1
    fi
    K8S_DIR="k8s"
    [ ! -d "$K8S_DIR" ] && K8S_DIR="kubernetes"
    if [ ! -d "$K8S_DIR" ]; then
        print_error "No Kubernetes config directory found."
        exit 1
    fi
    if [ ! -f ".env" ]; then
        cp .env.example .env
        read -p "Edit .env and press Enter to continue..."
    fi
    kubectl apply -f "$K8S_DIR/" && print_success "Kubernetes resources created."
}

main() {
    clear
    print_header "BILLING-LARAVEL INSTALLER"
    echo "  1) Standalone"
    echo "  2) Docker"
    echo "  3) Kubernetes"
    echo "  4) Exit"
    echo ""
    while true; do
        read -p "Enter your choice (1-4): " choice
        case $choice in
            1) install_standalone; break;;
            2) install_docker; break;;
            3) install_kubernetes; break;;
            4) exit 0;;
            *) print_warning "Invalid choice.";;
        esac
    done
}

main
