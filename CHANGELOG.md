# Changelog

## 2025-10-26

### Added

#### **Guest Form Management** - Complete implementation of guest user form auto-save and session management
- **Backend API** - Dedicated GuestSessionController with 4 endpoints
  - `POST /api/guest/save-form-data` - Save guest form data with progress tracking (public)
  - `GET /api/guest/session/{session_id}` - Retrieve session data (public)
  - `DELETE /api/guest/session/{session_id}` - Delete session (public)
  - `POST /api/simulations/generate-from-guest` - Restore & generate simulation (auth required)
  - `GET /api/guest/stats` - Admin statistics (auth required)

- **Auto-Save System** - Intelligent dual-storage auto-save with Alpine.js
  - Automatic localStorage save every 2 seconds (debounced)
  - Backend API save with retry logic (max 3 attempts, exponential backoff)
  - Visual status indicators (Saving... / Saved with timestamp / Error)
  - Before-unload warning when unsaved changes detected
  - Progress tracking based on 18 form fields (0-100%)
  - Step completion tracking (basic, target, ingredients, advanced)

- **Session Management**
  - 24-hour session expiration with automatic cleanup
  - Session restoration after authentication (login/registration)
  - Form data persistence across browser sessions
  - Automatic session ID generation and storage

- **Data Repository** - Enhanced GuestSessionRepository
  - Form progress calculation (percentage completion)
  - Completed steps tracking
  - Form data validation (18 required fields)
  - Session expiration handling
  - Statistics aggregation

- **Testing**
  - 11 comprehensive feature tests (all passing, 36 assertions)
  - API endpoint validation
  - Expiration handling
  - Progress calculation accuracy
  - Authentication requirements
  - Session cleanup verification

- **Frontend Integration** - Alpine.js component
  - Form data model binding for 18 fields
  - Real-time progress tracking
  - Save status management (idle, saving, saved, error)
  - Retry logic with exponential backoff
  - Offline support with localStorage fallback
  - Error handling and user notifications

#### **Ingredient Database System** - Complete implementation of ingredient management API
- **Models & Database**
  - 6 Eloquent models with comprehensive relationships: `Ingredient`, `IngredientCategory`, `ScientificReference`, `IngredientReference`, `SimulationIngredient`, `SimulationHistory`
  - BelongsTo, HasMany, BelongsToMany relationships properly defined
  - JSON field casting for effects, concentration_ranges, and scientific_references
  - Query scopes for active filtering, search, and category filtering
  - Database-agnostic implementation (MySQL/SQLite compatible)

- **API Endpoints** (13 RESTful endpoints)
  - `GET /api/ingredients` - List ingredients with search, filtering, and pagination
  - `POST /api/ingredients` - Create new ingredient (requires auth)
  - `GET /api/ingredients/{id}` - Get ingredient details with relationships
  - `GET /api/ingredients/inci/{inci_name}` - Get ingredient by INCI name
  - `PUT /api/ingredients/{id}` - Update ingredient (requires auth)
  - `DELETE /api/ingredients/{id}` - Deactivate ingredient (requires auth)
  - `GET /api/ingredients/{id}/related` - Get related ingredients by category
  - `GET /api/ingredient-categories` - List all categories
  - `POST /api/ingredient-categories` - Create category (requires auth)
  - `GET /api/ingredient-categories/{id}` - Get category details
  - `PUT /api/ingredient-categories/{id}` - Update category (requires auth)
  - `DELETE /api/ingredient-categories/{id}` - Delete category (requires auth)

- **Features**
  - Full-text search across name, INCI name, description, and effects
  - Category-based filtering and related ingredient suggestions
  - Pagination with configurable page size
  - API resources for structured JSON responses
  - Public read access, authenticated write operations

- **Data Management**
  - 3 Model factories with states for test data generation
  - 2 Seeders with production-ready data:
    - 10 ingredient categories (Humectants, Emollients, Antioxidants, Active Ingredients, Preservatives, Surfactants, Thickeners, Vitamins, Peptides, Botanical Extracts)
    - 7 popular skincare ingredients with complete data (Hyaluronic Acid, Glycerin, Vitamin C, Niacinamide, Retinol, Squalane, Matrixyl 3000)
  - Integrated into DatabaseSeeder for easy setup

- **Testing**
  - 9 comprehensive feature tests (all passing, 40 assertions)
  - CRUD operations tested
  - Search and filtering validated
  - Authentication requirements verified
  - Database-agnostic test suite

- **Documentation**
  - Complete API documentation with curl examples (`docs/ingredient-api.md`)
  - Data model specifications and relationship patterns
  - Error response examples and rate limiting information
  - Implementation summary with usage examples

#### **User Authentication System** - Complete implementation of user authentication with Laravel Sanctum and Socialite
- User registration and login with email/password validation
- Google OAuth integration for social login
- Password reset functionality with email notifications
- Token-based authentication with access and refresh tokens
- Guest session management for form data persistence
- Rate limiting based on subscription tiers (free: 3, premium: 20, enterprise: 100)
- Comprehensive audit logging for all authentication events
- API endpoints: `/api/auth/register`, `/api/auth/login`, `/api/auth/logout`, `/api/auth/refresh`, `/api/auth/google`, `/api/simulations/save-guest`, `/api/simulations/from-guest`
- Feature tests covering all authentication flows (9 tests, 54 assertions)
- Manual verification documentation and Postman collection

#### **Database Foundation**
- Initial Laravel project bootstrap with OpenSpec documentation, custom migrations, and seed data for the AI Skincare Product Simulator.
- Full database foundation covering users (with OAuth fields, rate limits, and soft deletes), guest sessions, simulations, ingredient catalog, scientific references, product configuration lookups, market cache, audit logs, and Sanctum personal access tokens.
- Lookup seeder that preloads ingredient categories, product types/functions, target demographics, and packaging types to support the 18-field simulation form.
- Documentation of the migration order, maintenance jobs, and seeding strategy in `docs/database-foundation.md` to guide downstream proposals.

### Technical Details

#### Guest Form Management
- **Architecture**: Dedicated GuestSessionController with separation of concerns, progress tracking in repository layer
- **Data Flow**: Dual storage (localStorage + API) with retry logic and offline fallback
- **Session Lifecycle**: Auto-generate session ID, 24-hour expiration, automatic cleanup
- **Progress Tracking**: Real-time calculation based on 18 form fields with step completion
- **Error Handling**: Exponential backoff retry, graceful degradation, user-friendly error messages
- **Testing**: 11 feature tests covering all scenarios including edge cases
- **Security**: Public endpoints for guest operations, authentication required for simulation generation

#### Ingredient Database
- **Architecture**: Service layer pattern with controllers, API resource transformers, and repository pattern via Eloquent scopes
- **Data Validation**: INCI name uniqueness, category constraints, proper relationship handling
- **Search Implementation**: Database-agnostic search with MySQL JSON_SEARCH support and SQLite fallback
- **Code Quality**: PSR-12 compliant, Laravel 12.x conventions, comprehensive type hints
- **Security**: Mass assignment protection, authentication guards, safe soft-delete pattern

#### Authentication System
- **Authentication Flow**: Token-based authentication using Laravel Sanctum with 2-hour access tokens and 7-30 day refresh tokens
- **Security Features**: Rate limiting middleware, audit logging, token lifecycle management, guest session restoration
- **API Design**: RESTful endpoints with proper HTTP status codes and JSON responses
- **Testing**: Comprehensive test suite with 100% pass rate for authentication flows
