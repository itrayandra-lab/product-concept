# AI Skincare Product Simulator

## Purpose
An intelligent product development platform that automates the brainstorming and analysis of skincare products. Users input product specifications (functions, formulation, target market, active ingredients) and receive comprehensive AI-generated analysis including:

- **Product naming & branding** with taglines and alternatives
- **Scientific validation** with ingredient effects and research references (DOI citations)
- **Market intelligence** with competitor analysis, pricing, and positioning
- **Business recommendations** including packaging suggestions, pricing strategy, and profit margins
- **Actionable next steps** with WhatsApp consultation integration

**Goal**: Transform skincare R&D ideation from manual research (hours/days) into automated analysis (minutes) while maintaining scientific accuracy and market relevance.

## Tech Stack

### Backend
- **Laravel 11** - Main application framework
- **PHP 8.3** - Core language
- **MySQL/PostgreSQL** - Primary database
- **Redis** - Caching layer for API responses and market data

### Frontend  
- **Blade Templates** - Laravel's templating engine
- **Alpine.js** - Lightweight reactive framework for interactivity
- **TailwindCSS** - Utility-first CSS framework
- **Vite** - Asset bundling and hot reload

### Workflow Automation
- **n8n** - Visual workflow orchestrator for AI pipeline
- **Docker** - n8n containerization and deployment

### AI & External APIs
- **OpenAI GPT-4** / **Google Gemini Pro** - Text generation (product descriptions, naming, copywriting)
- **Claude 3 Haiku** - Budget-friendly fallback option
- **DALL-E 3** / **Stable Diffusion** - Product mockup generation (optional)

### Data Sources
- **Crossref API** - Scientific paper DOI lookup (free)
- **PubMed API** - Medical/cosmetic research references (free) 
- **Marketplace APIs** - Shopee/Lazada product & pricing data
- **WhatsApp Business API** - Lead generation integration

## Project Conventions

### Code Style
- **Laravel Standards**: Follow Laravel naming conventions and project structure
- **PSR-12**: PHP coding standard compliance
- **Blade Components**: Reusable UI components over inline templates
- **Alpine.js**: `x-data` stores in external JavaScript functions, not inline
- **TailwindCSS**: Utility classes preferred, custom CSS minimal
- **Database**: Snake_case for table/column names, consistent with Laravel

### Architecture Patterns
- **Modular Monolith**: Laravel app with clear service boundaries
- **Service Layer Pattern**: Business logic in dedicated service classes
- **Repository Pattern**: Data access abstraction for ingredients/citations
- **Event-Driven**: Laravel events for audit logging and notifications  
- **API-First**: n8n workflows consume Laravel API endpoints
- **Stateless Processing**: Each simulation request is independent

### API Integration Strategy
```php
// Service classes for external APIs
app/Services/
├── N8nService.php          // Workflow orchestration
├── AIService.php           // Multi-provider AI abstraction  
├── MarketService.php       // Marketplace data aggregation
├── CitationService.php     // Scientific reference lookup
└── WhatsAppService.php     // Business messaging
```

### Testing Strategy
- **Feature Tests**: Complete form submission → n8n → result display flows
- **Unit Tests**: Service classes, validation rules, data transformations
- **Integration Tests**: AI API responses, database operations
- **Manual Testing**: UI/UX workflows, mobile responsiveness
- **Staging Environment**: Full n8n + AI pipeline testing before production

### Git Workflow
- **Main Branch**: Production-ready code only
- **Development Branch**: Integration branch for features  
- **Feature Branches**: `feature/ingredient-database`, `feature/ai-integration`
- **Commit Convention**: Conventional commits (`feat:`, `fix:`, `docs:`, `refactor:`)
- **PR Requirements**: Code review + working feature demo before merge

## Domain Context

### Cosmetic Industry Knowledge
- **INCI Names**: International Nomenclature of Cosmetic Ingredients standard
- **Active Ingredients**: Functional components (Retinol, Niacinamide, Hyaluronic Acid, etc.)
- **Formulation Types**: Physical forms (serum, cream, lotion, gel, essence, etc.)
- **Skin Concerns**: Anti-aging, brightening, acne treatment, hydration, etc.
- **Packaging Standards**: Airless pumps, droppers, jars with contamination considerations
- **Regulatory**: Indonesian BPOM requirements, concentration limits, labeling standards

### Market Dynamics  
- **Price Segments**: Drugstore (< 50k IDR), Mid-range (50-200k), Premium (200k+)
- **Channels**: E-commerce (Shopee, Lazada, Sociolla), offline retail, direct-to-consumer
- **Demographics**: Primary 18-35 female, growing male market, SEA regional preferences
- **Trends**: K-beauty influence, natural ingredients, sustainable packaging

### Scientific Validation
- **Research Sources**: PubMed, cosmetic science journals, clinical studies
- **Evidence Levels**: In-vitro studies, clinical trials, peer-reviewed publications
- **Citation Format**: DOI linking, author/year/journal standardization

## Important Constraints

### Technical Constraints
- **AI API Rate Limits**: OpenAI 3,500 RPM, Gemini 2,000 RPM - implement queuing
- **Cost Management**: Monthly AI budget limits with auto-provider switching
- **Scraping Ethics**: Respect robots.txt, implement delays, use official APIs when available
- **Data Privacy**: No personal data storage, simulation history optional anonymization

### Business Constraints
- **Disclaimer Requirements**: AI outputs are suggestions only, require expert validation
- **Liability Limitation**: No medical claims, cosmetic use only
- **Intellectual Property**: Generated content belongs to user, no platform ownership
- **Compliance**: Indonesian data protection laws, international cosmetic regulations

### Performance Constraints  
- **Response Time**: Complete analysis < 2 minutes end-to-end
- **Concurrent Users**: Design for 50 simultaneous simulations
- **Data Freshness**: Market data updated daily, ingredient database weekly

## External Dependencies

### Critical APIs (Required)
- **OpenAI/Gemini**: Primary AI text generation
- **Laravel Framework**: Application foundation
- **n8n Instance**: Workflow orchestration (self-hosted or cloud)

### Scientific Data (Free Tier)
- **Crossref API**: DOI and citation metadata
- **PubMed E-utilities**: Research paper abstracts and references
- **INCI Database**: Ingredient safety and naming standards

### Market Intelligence  
- **Shopee Partner API**: Product listings and pricing (if available)
- **Lazada Open Platform**: E-commerce data integration
- **Manual Web Scraping**: Backup data collection with rate limiting

### Authentication & User Management
- **Laravel Sanctum**: JWT token-based API authentication
- **Laravel Socialite**: Google OAuth 2.0 integration
- **Google OAuth API**: Social login without email verification requirement
- **Password Reset**: Email-based password recovery (SendGrid/Mailgun)

### Business Integration
- **WhatsApp Business API**: Lead conversion and consultation booking
- **Twilio**: SMS notifications (optional)
- **Email Service**: SendGrid/Mailgun for password reset and result delivery

### Infrastructure
- **Redis Cloud**: Managed caching service
- **CDN**: Asset delivery optimization  
- **Database**: Managed MySQL/PostgreSQL hosting
- **File Storage**: Local/S3 for generated images and exports
