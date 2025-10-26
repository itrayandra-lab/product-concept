## Change ID
implement-simulation-engine

## Why
- The simulation engine is the core business value of the AI Skincare Simulator, transforming user form input into comprehensive AI-generated product analysis.
- Requirements FR-006 to FR-012 mandate automated content generation, scientific validation, market intelligence, and business recommendations that require a robust processing pipeline.
- The database foundation (A), user authentication (B), ingredient database (C), and guest form management (D) are complete, enabling the simulation engine to process user requests and generate results.
- Without the simulation engine, users cannot receive the AI-generated product names, descriptions, scientific references, market analysis, and business recommendations that differentiate this platform.

## What Changes
- **ADDED** Core simulation processing system that handles 18-field form submissions and orchestrates AI analysis
- **ADDED** n8n workflow integration for multi-AI processing with fallback mechanisms
- **ADDED** Simulation status tracking and progress monitoring for real-time user feedback
- **ADDED** Result storage and retrieval system with comprehensive AI-generated outputs
- **ADDED** Export functionality for PDF and Word document generation
- **ADDED** Regeneration capability allowing users to create alternative results
- **ADDED** Rate limiting integration with user tier-based simulation quotas
- **ADDED** WhatsApp lead generation integration with pre-filled consultation messages

## Impact
- **Affected specs**: `simulation-engine` (new capability)
- **Affected code**: 
  - `app/Http/Controllers/SimulationController.php` (new controller)
  - `app/Services/N8nService.php` (new service)
  - `app/Services/ExportService.php` (new service)
  - `app/Models/SimulationHistory.php` (new model)
  - `app/Http/Resources/SimulationResource.php` (new resource)
  - `database/migrations/` (simulation-related migrations)
  - `routes/api.php` (simulation endpoints)
  - `tests/Feature/SimulationTest.php` (new tests)
- **Dependencies**: Database Foundation (A), User Authentication (B), Ingredient Database (C), Guest Form Management (D) ✅
- **Enables**: n8n AI Pipeline (G), UI Components (H)

## Out of Scope
- No changes to n8n workflow configuration (handled in Proposal G)
- No changes to UI components (handled in Proposal H)
- No changes to market intelligence scraping (skipped Proposal E)
- No changes to authentication or user management (already implemented)

## Acceptance Criteria
1. Users can submit 18-field simulation forms and receive comprehensive AI-generated results within 120 seconds
2. Simulation processing includes product naming, descriptions, scientific references, market analysis, and business recommendations
3. Real-time status tracking shows processing progress with estimated completion times
4. Export functionality generates downloadable PDF and Word reports with customizable sections
5. Regeneration capability allows users to create alternative results with same inputs
6. Rate limiting enforces user tier-based simulation quotas (free: 50/day, premium: 200/day, enterprise: 1000/day)
7. WhatsApp integration provides pre-filled consultation messages for lead generation
8. All simulation data is stored with proper relationships and can be retrieved by users
9. Error handling provides graceful fallbacks when AI providers or external APIs fail
10. `openspec validate implement-simulation-engine --strict` passes
