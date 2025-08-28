# 🛒 Laravel E-Commerce Platform

A modern, flexible e-commerce platform built with Laravel 12, optimized for shared hosting environments with Prestashop-inspired features.

## 📋 Project Overview

This project is a comprehensive e-commerce solution designed for:
- **E-commerce business owners** seeking a flexible online store
- **Developers** building custom retail solutions  
- **Admin users** managing complex product catalogs and inventory

## ✨ Key Features

### 🏗️ Architecture
- **Hybrid Frontend**: SSR with Laravel Blade for SEO + SPA components for admin
- **Shared Hosting Optimized**: Minimal resource usage, file-based optimizations
- **Prestashop-Inspired**: Advanced image handling, product variants, SEO management

### 🎯 Core Functionality
- ✅ **Product Management**: Complex variants, units of measure, stock tracking
- ✅ **Brand & Category System**: Hierarchical categories with SEO optimization
- ✅ **Image Management**: Prestashop-style resize profiles (thumbnail, small, medium, large, xlarge)
- ✅ **SEO Optimization**: Meta tags, breadcrumbs, sitemap generation
- 🚧 **Admin Panel**: Modern SPA interface with real-time updates
- 🚧 **Email Management**: Bulk mailing, customer communications
- 🚧 **Campaign System**: Discounts, promotions, abandoned cart recovery

### 🎨 Frontend Features
- **Mobile-First**: PWA capabilities with offline support
- **Performance**: Optimized for shared hosting environments
- **SEO-Friendly**: Server-side rendered customer pages
- **Interactive**: Alpine.js for dynamic components

## 🛠️ Technology Stack

### Backend
- **Laravel 12** (PHP 8.2+)
- **MySQL** Database
- **Intervention Image** for image processing
- **Laravel Excel** for bulk operations

### Frontend  
- **Blade Templates** (SSR pages)
- **Tailwind CSS 4.0** (Styling)
- **Alpine.js** (Interactive components)
- **Vue.js** (Admin SPA components)
- **Vite 7.0** (Asset bundling)

### Shared Hosting Compatibility
- Pre-compiled assets
- Database-driven configuration
- Minimal server requirements
- File permission optimization

## 📦 Installation

### Development Environment
```bash
# Clone repository
git clone https://github.com/luqequax1a-new/ecommerce.git
cd ecommerce

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --graceful
php artisan db:seed

# Asset compilation
npm run dev

# Start development server
php artisan serve
```

### Shared Hosting Deployment
```bash
# Build production assets
npm run build

# Optimize for production
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🗃️ Database Schema

### Core Tables
- **products** - Main product information with SEO fields
- **product_variants** - SKU, pricing, stock, units, attributes
- **product_images** - Multi-format image storage with resize profiles
- **brands** - Brand management with logo and SEO optimization
- **categories** - Hierarchical category system with breadcrumb support
- **units** - Flexible units of measure (piece, kg, meter, etc.)
- **stock_movements** - Complete inventory tracking

### Features
- **SEO Optimization**: Meta tags, canonical URLs, schema markup
- **Hierarchical Categories**: Parent-child relationships with unlimited depth
- **Image Management**: Prestashop-style resize profiles with automatic generation
- **Brand System**: Logo management, website links, contact information

## 🚀 Key Models & Relationships

### Product Model
```php
// Relationships
$product->category      // BelongsTo Category
$product->brand         // BelongsTo Brand
$product->variants      // HasMany ProductVariant
$product->images        // HasMany ProductImage

// Helper Methods
$product->coverImage()              // Get main product image
$product->getCoverImageResizedUrl() // Get resized image URL
$product->price_range              // Price range string
$product->breadcrumb               // SEO breadcrumb array
```

### Category Model
```php
// Hierarchical Structure
$category->parent       // BelongsTo Category
$category->children     // HasMany Category
$category->descendants  // HasMany Category (recursive)

// SEO & Display
$category->products     // HasMany Product  
$category->breadcrumb   // SEO breadcrumb trail
$category->url         // SEO-friendly URL
```

### Brand Model
```php
// Features
$brand->products        // HasMany Product
$brand->logo_url       // Logo image URL
$brand->meta_title     // SEO title with fallback
```

## 🖼️ Image Management System

### Resize Profiles (Prestashop-style)
- **thumbnail**: 80x80px (cropped)
- **small**: 200x200px (cropped)  
- **medium**: 400x400px (cropped)
- **large**: 800x800px (proportional)
- **xlarge**: 1200x1200px (proportional)

### Features
- Automatic resize generation on upload
- "Regenerate Images" functionality
- Optimized storage structure
- WebP, JPEG, PNG support

## 🔧 Shared Hosting Optimization

### Performance Features
- **Minimal Resource Usage**: Optimized queries, efficient caching
- **Pre-compiled Assets**: No Node.js required in production
- **Database-driven Config**: No file permission issues
- **Optimized Indexes**: Fast queries on limited resources

### Deployment Strategy
- File-based session storage
- Database configuration management
- Asset optimization for CDN compatibility
- Memory-efficient image processing

## 🌐 SEO Features

### Customer Pages (SSR)
- **Meta Tags**: Automatic generation with fallbacks
- **Breadcrumbs**: Hierarchical navigation
- **Canonical URLs**: Duplicate content prevention
- **Schema Markup**: Rich snippets support
- **Sitemap**: Auto-generated XML sitemap

### URL Structure
```
/                           # Homepage (product catalog)
/kategori/{slug}           # Category pages
/marka/{slug}              # Brand pages  
/p/{slug}                  # Product detail pages
/admin/*                   # Admin SPA routes
```

## 👨‍💼 Admin Panel Features

### Product Management
- **Bulk Operations**: Mass price updates, category assignments
- **Quick Edit**: Inline editing for common fields
- **Advanced Filtering**: Category, brand, price, stock filters
- **Image Management**: Drag-drop upload, resize, reorder

### SEO Management  
- **Bulk SEO Updates**: Mass meta tag generation
- **Template System**: SEO templates for products/categories
- **Sitemap Management**: Automatic XML generation

### Email System
- **Customer Communications**: Order confirmations, shipping updates
- **Bulk Campaigns**: Newsletter, promotional emails
- **Abandoned Cart**: Automated recovery emails

## 🏃‍♂️ Development Workflow

### Commands
```bash
# Development server with hot reload
composer dev
# or
npm run dev

# Run tests
composer test

# Code style check
./vendor/bin/pint

# Database refresh
php artisan migrate:fresh --seed
```

### Git Workflow
```bash
# Feature development
git checkout -b feature/new-functionality
git add .
git commit -m "Add: New functionality description"
git push origin feature/new-functionality

# Production deployment  
git checkout main
git merge feature/new-functionality
git tag v1.0.0
git push origin main --tags
```

## 📈 Performance Metrics

### Shared Hosting Optimized
- **Memory Usage**: < 128MB per request
- **Database Queries**: Optimized with eager loading
- **Asset Size**: Minified CSS/JS bundles
- **Image Optimization**: Automatic compression

### SEO Performance
- **Core Web Vitals**: Optimized loading times
- **Mobile-First**: Responsive design
- **Accessibility**: WCAG 2.1 compliance
- **Schema Markup**: Rich snippets support

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add: Amazing Feature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

## 📄 License

This project is open-sourced software licensed under the [MIT license](LICENSE).

## 🙏 Acknowledgments

- **Laravel Framework** - Robust PHP framework
- **Prestashop** - E-commerce inspiration
- **Tailwind CSS** - Utility-first CSS framework
- **Intervention Image** - PHP image manipulation

---

**Developed by [luqequax1a-new](https://github.com/luqequax1a-new)**

*Building modern e-commerce solutions for shared hosting environments* 🚀