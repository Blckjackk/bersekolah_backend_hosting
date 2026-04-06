#!/bin/bash

# Bersekolah Backend API Deployment Script
# Deploy API Bersekolah ke Hostinger

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# SSH Configuration
SSH_HOST="46.202.138.221"
SSH_USER="u787393221"
SSH_PORT="65002"
SSH_PASSWORD="Bersekolah_123456"
REMOTE_PATH="/home/u787393221/domains/api.bersekolah.com/project_files"

# Local paths
LOCAL_APP_PATH="./app"
LOCAL_CONFIG_PATH="./config"
LOCAL_ROUTES_PATH="./routes"
LOCAL_PUBLIC_PATH="./public"
LOCAL_BOOTSTRAP_PATH="./bootstrap"
LOCAL_DATABASE_PATH="./database"

echo -e "${CYAN}
╔══════════════════════════════════════════════════════════════╗
║                ⚙️  BERSEKOLAH BACKEND DEPLOYMENT ⚙️           ║
║                                                              ║
║  Script mudah untuk deploy backend API Bersekolah ke Hostinger ║
╚══════════════════════════════════════════════════════════════╝
${NC}"

echo -e "${YELLOW}📋 Pilih opsi deployment:${NC}"
echo -e "${BLUE}1.${NC} Deploy Core Files Only (app, routes, config)"
echo -e "${BLUE}2.${NC} Deploy dengan Storage & Public"
echo -e "${BLUE}3.${NC} Deploy Images Only (ke public_html)"
echo -e "${BLUE}4.${NC} Test SSH Connection"
echo -e "${BLUE}5.${NC} Cek Status API"
echo -e "${BLUE}6.${NC} Update Database Schema"
echo -e "${BLUE}7.${NC} Setup Bersekolah System (Migration + Seeder)"
echo -e "${BLUE}8.${NC} Clear Cache"
echo -e "${BLUE}9.${NC} Fix Storage Symlink (AMAN - tidak hapus file existing)"
echo -e "${BLUE}10.${NC} Keluar"

read -p "Pilih opsi (1-10): " choice

case $choice in
    1)
        echo -e "${PURPLE}⚙️ Deploying Core Files (app, routes, config)...${NC}"
        
        # Test SSH connection
        echo -e "${YELLOW}🔐 Testing SSH connection...${NC}"
        if sshpass -p "$SSH_PASSWORD" ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "echo 'SSH connection successful!'" 2>/dev/null; then
            echo -e "${GREEN}✅ SSH connection berhasil!${NC}"
        else
            echo -e "${RED}❌ SSH connection gagal! Periksa konfigurasi SSH.${NC}"
            exit 1
        fi
        
        # Upload core files
        echo -e "${YELLOW}📦 Preparing core files for upload...${NC}"
        sshpass -p "$SSH_PASSWORD" rsync -avz -e "ssh -o StrictHostKeyChecking=no -p $SSH_PORT" \
            $LOCAL_APP_PATH/ $SSH_USER@$SSH_HOST:$REMOTE_PATH/app/
        sshpass -p "$SSH_PASSWORD" rsync -avz -e "ssh -o StrictHostKeyChecking=no -p $SSH_PORT" \
            $LOCAL_CONFIG_PATH/ $SSH_USER@$SSH_HOST:$REMOTE_PATH/config/
        sshpass -p "$SSH_PASSWORD" rsync -avz -e "ssh -o StrictHostKeyChecking=no -p $SSH_PORT" \
            $LOCAL_ROUTES_PATH/ $SSH_USER@$SSH_HOST:$REMOTE_PATH/routes/
        sshpass -p "$SSH_PASSWORD" rsync -avz -e "ssh -o StrictHostKeyChecking=no -p $SSH_PORT" \
            $LOCAL_BOOTSTRAP_PATH/ $SSH_USER@$SSH_HOST:$REMOTE_PATH/bootstrap/
        
        # Set permissions
        echo -e "${YELLOW}🔧 Setting permissions...${NC}"
        sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "chmod -R 755 $REMOTE_PATH"
        
        # Clear Laravel cache
        echo -e "${YELLOW}🧹 Clearing Laravel cache...${NC}"
        sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "cd $REMOTE_PATH && php artisan optimize:clear" || echo -e "${YELLOW}⚠️ Cache clear skipped (artisan not found)${NC}"
        
        echo -e "${GREEN}✅ Core files deployed successfully!${NC}"
        echo -e "${CYAN}🔗 API: https://api.bersekolah.com${NC}"
        ;;
    2)
        echo -e "${PURPLE}📁 Deploying dengan Storage & Public...${NC}"
        
        # Test SSH connection
        echo -e "${YELLOW}🔐 Testing SSH connection...${NC}"
        if sshpass -p "$SSH_PASSWORD" ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "echo 'SSH connection successful!'" 2>/dev/null; then
            echo -e "${GREEN}✅ SSH connection berhasil!${NC}"
        else
            echo -e "${RED}❌ SSH connection gagal! Periksa konfigurasi SSH.${NC}"
            exit 1
        fi
        
        # Upload with storage and public
        echo -e "${YELLOW}📤 Uploading files with storage and public...${NC}"
        sshpass -p "$SSH_PASSWORD" rsync -avz --delete -e "ssh -o StrictHostKeyChecking=no -p $SSH_PORT" \
            --exclude='.git' \
            --exclude='node_modules' \
            --exclude='storage/logs' \
            --exclude='.env' \
            ./ $SSH_USER@$SSH_HOST:$REMOTE_PATH/
        
        # Set permissions
        echo -e "${YELLOW}🔧 Setting permissions...${NC}"
        sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "chmod -R 755 $REMOTE_PATH"
        
        # Create storage symlink
        echo -e "${YELLOW}🔗 Creating storage symlink...${NC}"
        sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "cd $REMOTE_PATH && php artisan storage:link"
        
        echo -e "${GREEN}✅ Deploy with storage & public completed!${NC}"
        echo -e "${CYAN}🔗 API: https://api.bersekolah.com${NC}"
        ;;
    3)
        echo -e "${PURPLE}🖼️ Deploying Images Only...${NC}"
        
        # Test SSH connection
        echo -e "${YELLOW}🔐 Testing SSH connection...${NC}"
        if sshpass -p "$SSH_PASSWORD" ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "echo 'SSH connection successful!'" 2>/dev/null; then
            echo -e "${GREEN}✅ SSH connection berhasil!${NC}"
        else
            echo -e "${RED}❌ SSH connection gagal! Periksa konfigurasi SSH.${NC}"
            exit 1
        fi
        
        # Upload images only
        echo -e "${YELLOW}📤 Uploading images to public_html...${NC}"
        sshpass -p "$SSH_PASSWORD" rsync -avz -e "ssh -o StrictHostKeyChecking=no -p $SSH_PORT" \
            $LOCAL_PUBLIC_PATH/assets/ $SSH_USER@$SSH_HOST:$REMOTE_PATH/public/assets/
        
        # Set permissions
        echo -e "${YELLOW}🔧 Setting permissions...${NC}"
        sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "chmod -R 755 $REMOTE_PATH/public && chown -R $SSH_USER:$SSH_USER $REMOTE_PATH/public"
        
        echo -e "${GREEN}✅ Images deployed successfully!${NC}"
        echo -e "${CYAN}🔗 API: https://api.bersekolah.com${NC}"
        ;;
    4)
        echo -e "${PURPLE}🔐 Testing SSH Connection...${NC}"
        if sshpass -p "$SSH_PASSWORD" ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "echo 'SSH connection successful!'" 2>/dev/null; then
            echo -e "${GREEN}✅ SSH connection berhasil!${NC}"
            echo -e "${CYAN}🌐 Host: $SSH_HOST${NC}"
            echo -e "${CYAN}👤 User: $SSH_USER${NC}"
            echo -e "${CYAN}📁 Path: $REMOTE_PATH${NC}"
        else
            echo -e "${RED}❌ SSH connection gagal!${NC}"
            echo -e "${YELLOW}Periksa konfigurasi SSH di file deploy-api.sh${NC}"
        fi
        ;;
    5)
        echo -e "${PURPLE}🔍 Checking API Status...${NC}"
        echo -e "${YELLOW}Backend API: https://api.bersekolah.com${NC}"
        echo -e "${YELLOW}Testing API connection...${NC}"
        
        # Test API
        if curl -s -o /dev/null -w "%{http_code}" https://api.bersekolah.com | grep -q "200"; then
            echo -e "${GREEN}✅ Backend API: Online${NC}"
        else
            echo -e "${RED}❌ Backend API: Offline${NC}"
        fi
        
        # Test specific endpoints
        echo -e "${YELLOW}Testing specific endpoints...${NC}"
        if curl -s https://api.bersekolah.com/api/beasiswa-periods | grep -q "success"; then
            echo -e "${GREEN}✅ API Endpoints: Working${NC}"
        else
            echo -e "${RED}❌ API Endpoints: Not responding${NC}"
        fi
        ;;
    6)
        echo -e "${PURPLE}🗄️ Updating Database Schema...${NC}"
        
        # Test SSH connection
        echo -e "${YELLOW}🔐 Testing SSH connection...${NC}"
        if sshpass -p "$SSH_PASSWORD" ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "echo 'SSH connection successful!'" 2>/dev/null; then
            echo -e "${GREEN}✅ SSH connection berhasil!${NC}"
        else
            echo -e "${RED}❌ SSH connection gagal! Periksa konfigurasi SSH.${NC}"
            exit 1
        fi
        
        # Run migrations
        echo -e "${YELLOW}🔄 Running database migrations...${NC}"
        sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "cd $REMOTE_PATH && php artisan migrate --force"
        
        echo -e "${GREEN}✅ Database schema updated successfully!${NC}"
        ;;
    7)
        echo -e "${PURPLE}🔧 Setting up Bersekolah System...${NC}"
        
        # Test SSH connection
        echo -e "${YELLOW}🔐 Testing SSH connection...${NC}"
        if sshpass -p "$SSH_PASSWORD" ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "echo 'SSH connection successful!'" 2>/dev/null; then
            echo -e "${GREEN}✅ SSH connection berhasil!${NC}"
        else
            echo -e "${RED}❌ SSH connection gagal! Periksa konfigurasi SSH.${NC}"
            exit 1
        fi
        
        # Run migrations and seeders
        echo -e "${YELLOW}🔄 Running migrations and seeders...${NC}"
        sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "cd $REMOTE_PATH && php artisan migrate --force && php artisan db:seed --force"
        
        echo -e "${GREEN}✅ Bersekolah system setup completed!${NC}"
        ;;
    8)
        echo -e "${PURPLE}🧹 Clearing Cache...${NC}"
        
        # Test SSH connection
        echo -e "${YELLOW}🔐 Testing SSH connection...${NC}"
        if sshpass -p "$SSH_PASSWORD" ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "echo 'SSH connection successful!'" 2>/dev/null; then
            echo -e "${GREEN}✅ SSH connection berhasil!${NC}"
        else
            echo -e "${RED}❌ SSH connection gagal! Periksa konfigurasi SSH.${NC}"
            exit 1
        fi
        
        # Clear cache
        echo -e "${YELLOW}🧹 Clearing application cache...${NC}"
        sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "cd $REMOTE_PATH && php artisan cache:clear && php artisan config:clear && php artisan route:clear && php artisan view:clear"
        
        echo -e "${GREEN}✅ Cache cleared successfully!${NC}"
        ;;
    9)
        echo -e "${PURPLE}🔗 Fixing Storage Symlink (AMAN)...${NC}"
        
        # Test SSH connection
        echo -e "${YELLOW}🔐 Testing SSH connection...${NC}"
        if sshpass -p "$SSH_PASSWORD" ssh -o ConnectTimeout=10 -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "echo 'SSH connection successful!'" 2>/dev/null; then
            echo -e "${GREEN}✅ SSH connection berhasil!${NC}"
        else
            echo -e "${RED}❌ SSH connection gagal! Periksa konfigurasi SSH.${NC}"
            exit 1
        fi
        
        # Create storage directories (safe - only create if not exists)
        echo -e "${YELLOW}📁 Creating storage directories...${NC}"
        sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "mkdir -p $REMOTE_PATH/storage/app/public/{admin,artikel,mentor,testimoni,navbar}"
        
        # Copy files from assets to storage (safe copy - only if not exists)
        echo -e "${YELLOW}📤 Copying files from assets to storage (safe copy)...${NC}"
        sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "
            if [ -d '$REMOTE_PATH/public/assets/image/admin' ]; then
                cp -n $REMOTE_PATH/public/assets/image/admin/* $REMOTE_PATH/storage/app/public/admin/ 2>/dev/null || true
            fi
            if [ -d '$REMOTE_PATH/public/assets/image/artikel' ]; then
                cp -n $REMOTE_PATH/public/assets/image/artikel/* $REMOTE_PATH/storage/app/public/artikel/ 2>/dev/null || true
            fi
            if [ -d '$REMOTE_PATH/public/assets/image/mentor' ]; then
                cp -n $REMOTE_PATH/public/assets/image/mentor/* $REMOTE_PATH/storage/app/public/mentor/ 2>/dev/null || true
            fi
            if [ -d '$REMOTE_PATH/public/assets/image/testimoni' ]; then
                cp -n $REMOTE_PATH/public/assets/image/testimoni/* $REMOTE_PATH/storage/app/public/testimoni/ 2>/dev/null || true
            fi
            if [ -d '$REMOTE_PATH/public/assets/image/navbar' ]; then
                cp -n $REMOTE_PATH/public/assets/image/navbar/* $REMOTE_PATH/storage/app/public/navbar/ 2>/dev/null || true
            fi
        "
        
        # Create storage symlink
        echo -e "${YELLOW}🔗 Creating storage symlink...${NC}"
        sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "cd $REMOTE_PATH && php artisan storage:link"
        
        # Set permissions
        echo -e "${YELLOW}🔧 Setting permissions...${NC}"
        sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "chmod -R 755 $REMOTE_PATH/storage/app/public"
        
        # Clear cache
        echo -e "${YELLOW}🧹 Clearing cache...${NC}"
        sshpass -p "$SSH_PASSWORD" ssh -o StrictHostKeyChecking=no -p $SSH_PORT $SSH_USER@$SSH_HOST "cd $REMOTE_PATH && php artisan cache:clear"
        
        echo -e "${GREEN}✅ Storage symlink fixed successfully!${NC}"
        echo -e "${CYAN}🔗 Storage URLs should now work:${NC}"
        echo -e "${CYAN}   https://api.bersekolah.com/storage/admin/filename.jpg${NC}"
        echo -e "${CYAN}   https://api.bersekolah.com/storage/artikel/filename.jpg${NC}"
        echo -e "${CYAN}   https://api.bersekolah.com/storage/mentor/filename.jpg${NC}"
        echo -e "${CYAN}   https://api.bersekolah.com/storage/testimoni/filename.jpg${NC}"
        ;;
    10)
        echo -e "${GREEN}👋 Goodbye!${NC}"
        exit 0
        ;;
    *)
        echo -e "${RED}❌ Invalid option. Please choose 1-10.${NC}"
        exit 1
        ;;
esac
