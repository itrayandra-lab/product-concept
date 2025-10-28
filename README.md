# AI Skincare Product Simulator

<p align="center">
  <img src="https://via.placeholder.com/400x100/FF6B35/FFFFFF?text=AI+Skincare+Simulator" alt="AI Skincare Simulator Logo" width="400">
</p>

<p align="center">
  <strong>Intelligent AI-powered skincare product development platform</strong>
</p>

<p align="center">
  <a href="#-features">Features</a> •
  <a href="#-tech-stack">Tech Stack</a> •
  <a href="#-installation">Installation</a> •
  <a href="#-testing">Testing</a> •
  <a href="#-documentation">Documentation</a>
</p>

## 🚀 About

The **AI Skincare Product Simulator** is an intelligent platform that leverages advanced AI technology to help cosmetic companies and formulators develop innovative skincare products. By analyzing user requirements and market data, the system generates comprehensive product recommendations with scientific backing and market intelligence.

### Key Capabilities

- **🤖 Multi-AI Processing**: OpenAI GPT-4, Google Gemini Pro, Claude 3 Haiku integration
- **🔬 Scientific Validation**: PubMed & Crossref API integration for evidence-based recommendations
- **📊 Market Intelligence**: Real-time competitor analysis and pricing data
- **⚡ Real-time Processing**: 60-120 second AI-powered product simulation
- **🎯 18-Field Analysis**: Comprehensive product specification and target market analysis
- **📱 Modern UI**: Responsive design with Alpine.js and TailwindCSS

## ✨ Features

### Core Functionality
- **🎯 Product Simulation**: 18-field comprehensive product analysis
- **🤖 AI-Powered Analysis**: Multi-provider AI with intelligent fallbacks
- **📊 Market Intelligence**: Real-time competitor and pricing analysis
- **🔬 Scientific Backing**: Evidence-based ingredient recommendations
- **📱 Responsive Design**: Mobile-first UI with modern UX patterns
- **💾 Auto-Save**: Intelligent form persistence with guest session support
- **📄 Export System**: PDF, Word, and JSON export capabilities

### Market Analysis Features
- **📊 Potensi Pasar**: Total Addressable Market (TAM) analysis with revenue projections
- **📈 Tren Kunci**: Real-time market trends and trending ingredients analysis
- **✍️ Marketing Copywriting**: Ready-to-use marketing content with copy-to-clipboard functionality
- **💰 Revenue Forecasting**: Monthly and yearly revenue projections with growth opportunities
- **⚠️ Risk Assessment**: Market risk factors and competitive landscape analysis
- **📱 Social Media Ready**: Platform-specific captions for Instagram, TikTok, and Facebook

### Advanced Features
- **⚡ Asynchronous Processing**: Queue-based job processing for scalability
- **🔄 Real-time Updates**: Live progress tracking and status updates
- **🛡️ Error Handling**: Comprehensive retry logic and graceful degradation
- **📈 Analytics**: User behavior tracking and simulation metrics
- **🔐 Authentication**: JWT-based auth with Google OAuth integration
- **📊 Rate Limiting**: Tier-based quotas (Free, Premium, Enterprise)

## 🛠️ Tech Stack

### Backend
- **Laravel 12.x** - PHP 8.3+ framework
- **MySQL/PostgreSQL** - Primary database
- **Redis** - Caching and session storage
- **Laravel Queues** - Asynchronous job processing
- **Laravel Sanctum** - API authentication

### Frontend
- **Alpine.js** - Reactive JavaScript framework
- **TailwindCSS** - Utility-first CSS framework
- **Vite** - Modern build tool
- **Blade Templates** - Server-side templating

### AI & External Services
- **n8n Workflow** - AI processing orchestration
- **OpenAI GPT-4** - Primary AI provider
- **Google Gemini Pro** - Secondary AI provider
- **Claude 3 Haiku** - Fallback AI provider
- **PubMed API** - Scientific reference data
- **Crossref API** - Research paper metadata
- **Marketplace APIs** - Shopee, Lazada competitor data

## 🚀 Installation

### Prerequisites

#### For VPS/Dedicated Server
- PHP 8.3+
- Composer
- Node.js 18+
- MySQL/PostgreSQL
- Redis

#### For Shared Hosting (Hostinger)
- PHP 8.3+ (via cPanel)
- MySQL database (via phpMyAdmin)
- File Manager access (via cPanel)
- Domain and SSL certificate

### Setup Instructions

#### Option 1: VPS/Dedicated Server
1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd product-concept
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**
   ```bash
   npm run build
   ```

6. **Start services**
   ```bash
   # Start queue worker
   php artisan queue:work
   
   # Start development server
   php artisan serve
   ```

#### Option 2: Shared Hosting (Hostinger)
1. **Follow the complete shared hosting deployment guide**
   - Start with [Shared Hosting Setup](docs/shared-hosting-setup.md)
   - Configure [Application Settings](docs/shared-hosting-config.md)
   - Deploy using [Deployment Process](docs/shared-hosting-deployment-process.md)

2. **Quick start for shared hosting**
   ```bash
   # Compile assets locally
   npm run build
   
   # Upload files via cPanel File Manager or FTP
   # Configure database via phpMyAdmin
   # Set up n8n Cloud integration
   ```

### Deployment Options

#### 💰 Cost Comparison
| Option | Monthly Cost | Features | Best For |
|--------|-------------|----------|----------|
| **Shared Hosting** | $25-35 | Basic features, limited control | Small projects, testing |
| **VPS/Dedicated** | $50-200+ | Full control, advanced features | Production, scaling |

#### 🚀 Choose Your Deployment
- **[Shared Hosting Guide](docs/shared-hosting-deployment.md)** - Cost-effective, easy setup
- **[VPS Deployment](docs/production-deployment.md)** - Full control, advanced features
- **[Docker Deployment](docs/docker-deployment.md)** - Containerized, scalable

### Environment Variables

#### VPS/Dedicated Server Configuration
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=skincare_simulator
DB_USERNAME=root
DB_PASSWORD=

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Cache & Session
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=database

# n8n Integration
N8N_BASE_URL=https://n8n-gczfssttvtzs.nasgor.sumopod.my.id
N8N_WEBHOOK_URL=https://n8n-gczfssttvtzs.nasgor.sumopod.my.id/webhook/lbf_product
N8N_API_KEY=your_api_key
N8N_TIMEOUT=150
N8N_MOCK_ENABLED=false

# AI Providers
OPENAI_API_KEY=your_openai_key
GOOGLE_AI_API_KEY=your_google_key
ANTHROPIC_API_KEY=your_anthropic_key
```

#### Shared Hosting Configuration
```env
# Database (from Hostinger cPanel)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=username_skincare_simulator
DB_USERNAME=username_skincare_user
DB_PASSWORD=your_database_password

# Cache & Session (shared hosting compatible)
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# n8n Integration (n8n Cloud)
N8N_BASE_URL=https://your-instance.n8n.cloud
N8N_WEBHOOK_URL=https://your-instance.n8n.cloud/webhook/lbf_product
N8N_API_KEY=your_n8n_cloud_key
N8N_TIMEOUT=150
N8N_MOCK_ENABLED=false

# AI Providers
OPENAI_API_KEY=your_openai_key
GOOGLE_AI_API_KEY=your_google_key
ANTHROPIC_API_KEY=your_anthropic_key
```

## 🧪 Testing

### Run Tests
```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --filter="SimulationTest"
php artisan test --filter="N8nServiceTest"
php artisan test --filter="ProcessSimulationJobTest"

# Run with coverage
php artisan test --coverage
```

### Test Coverage
- **57 tests passing** (100% success rate)
- **235 assertions** covering all major functionality
- **Load testing** for 50+ concurrent users
- **Error scenario testing** with comprehensive edge cases

## 📚 Documentation

### Comprehensive Documentation (8,000+ lines)
- **[Requirements Specification](docs/requirements-specification.md)** - Complete functional & non-functional requirements
- **[API Specifications](docs/api-specifications.md)** - All endpoints, authentication, data models
- **[UI Specifications](docs/ui-specifications.md)** - UI components, forms, auto-save functionality
- **[Database Schema](docs/database-schema.md)** - Complete ERD, migrations, performance optimization
- **[n8n Workflow Specification](docs/n8n-workflow-specification.md)** - AI processing pipeline, external APIs
- **[n8n Production Integration](docs/n8n-production-integration.md)** - Production deployment guide
- **[OpenSpec Implementation Guide](docs/openspec-implementation-guide.md)** - Implementation methodology

### Deployment Guides
- **[Deployment Guide](docs/deployment-guide.md)** - General deployment strategies and options
- **[Production Deployment](docs/production-deployment.md)** - VPS/dedicated server deployment
- **[Docker Deployment](docs/docker-deployment.md)** - Containerized deployment with Docker
- **[Monitoring & Maintenance](docs/monitoring-maintenance.md)** - Production monitoring and maintenance

### Shared Hosting Deployment (NEW!)
- **[Shared Hosting Deployment](docs/shared-hosting-deployment.md)** - Complete shared hosting deployment guide
- **[Hostinger Setup](docs/shared-hosting-setup.md)** - Hostinger account and cPanel configuration
- **[Application Configuration](docs/shared-hosting-config.md)** - Laravel configuration for shared hosting
- **[Deployment Process](docs/shared-hosting-deployment-process.md)** - Step-by-step deployment procedures
- **[n8n Integration](docs/shared-hosting-n8n-integration.md)** - n8n Cloud and API integration
- **[Performance Optimization](docs/shared-hosting-optimization.md)** - Shared hosting performance tuning
- **[Workarounds & Limitations](docs/shared-hosting-workarounds.md)** - Solutions for shared hosting constraints
- **[Maintenance Guide](docs/shared-hosting-maintenance.md)** - Ongoing management and monitoring

### Quick Start Guides
- **[Ingredient API](docs/ingredient-api.md)** - API documentation with curl examples
- **[Export Setup Guide](docs/export-setup-guide.md)** - PDF/Word export configuration
- **[Auth Manual Verification](docs/auth-manual-verification.md)** - Authentication testing guide

## 🏗️ Architecture

### System Overview
```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────┐
│   Frontend      │    │   Laravel App    │    │   n8n Workflow  │
│   (Alpine.js)   │    │                  │    │                 │
└─────────────────┘    └──────────────────┘    └─────────────────┘
         │                       │                       │
         │ POST /api/simulations │                       │
         ├──────────────────────►│                       │
         │                       │ ProcessSimulationJob  │
         │                       ├──────────────────────►│
         │                       │                       │
         │                       │ Real AI Processing    │
         │                       │ (60-120 seconds)      │
         │                       │                       │
         │                       │ Complete Response     │
         │                       │◄──────────────────────┤
         │                       │                       │
         │ Complete Results      │                       │
         │◄──────────────────────┤                       │
```

### Key Components
- **SimulationController** - API endpoints for simulation management
- **ProcessSimulationJob** - Asynchronous n8n workflow processing
- **N8nService** - Interface between Laravel and n8n
- **ExportService** - PDF/Word document generation
- **SimulationAnalyticsService** - User behavior tracking

## 🚀 Production Status

### ✅ Implementation Complete
- **Real n8n Integration**: Live AI processing with production workflow
- **Asynchronous Processing**: Queue-based job processing for scalability
- **Complete Data Display**: All n8n response fields properly mapped
- **Error Handling**: Comprehensive retry and fallback logic
- **Testing**: 57 tests passing (100% success rate)
- **Performance**: Load testing passed for 50+ concurrent users
- **Documentation**: Complete integration and deployment documentation
- **Shared Hosting Support**: Complete deployment guides for shared hosting environments

### 🎯 Ready for Production
The AI Skincare Simulator is **production-ready** with:
- Real n8n workflow integration
- Robust error handling and monitoring
- Complete test coverage
- Comprehensive documentation
- Performance optimization
- **Multiple deployment options** (VPS, Docker, Shared Hosting)

### 💰 Deployment Options
- **Shared Hosting**: $25-35/month (Hostinger, easy setup)
- **VPS/Dedicated**: $50-200+/month (full control, advanced features)
- **Docker**: Containerized deployment for scalability

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing

Thank you for considering contributing to the AI Skincare Simulator! Please read our contribution guidelines and code of conduct before submitting pull requests.

## 📞 Support

For support and questions:
- **Email**: support@skincaresim.id
- **Documentation**: See the comprehensive docs in the `/docs` folder
- **Issues**: Please use GitHub Issues for bug reports and feature requests
