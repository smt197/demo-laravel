#!/bin/bash

# Docker setup script for Laravel with FrankenPHP

echo "🐳 Setting up Laravel with FrankenPHP using Docker..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker and try again."
    exit 1
fi

# Create MySQL init directory
mkdir -p docker/mysql/init

# Copy environment file for Docker
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.docker..."
    cp .env.docker .env
else
    echo "⚠️  .env file exists. Make sure to update database settings for Docker."
fi

# Build and start containers
echo "🏗️  Building Docker containers..."
docker-compose up --build -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 30

# Run Laravel setup commands
echo "🎨 Running Laravel setup commands..."
docker-compose exec app php artisan key:generate --force
docker-compose exec app php artisan storage:link
docker-compose exec app php artisan migrate --force
docker-compose exec app php artisan config:cache
docker-compose exec app php artisan route:cache
docker-compose exec app php artisan view:cache

echo "✅ Laravel with FrankenPHP is ready!"
echo "🌐 Open http://localhost:8000 in your browser"
echo ""
echo "📋 Useful commands:"
echo "  docker-compose up -d          # Start containers"
echo "  docker-compose down           # Stop containers"
echo "  docker-compose logs app       # View app logs"
echo "  docker-compose exec app bash  # Access app container"
echo ""
echo "🔧 For development:"
echo "  docker-compose -f docker-compose.dev.yml up -d"