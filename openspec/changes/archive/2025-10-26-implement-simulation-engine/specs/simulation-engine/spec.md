# Specification: Simulation Engine

## ADDED Requirements

### Requirement: Simulation Request Processing
The system SHALL process 18-field product simulation requests and generate comprehensive AI analysis including product naming, descriptions, scientific validation, market intelligence, and business recommendations.

#### Scenario: Successful simulation processing
- **WHEN** user submits complete 18-field form
- **THEN** system validates input, stores request, triggers n8n workflow, and returns simulation ID with processing status

#### Scenario: Form validation failure
- **WHEN** user submits incomplete or invalid form data
- **THEN** system returns validation errors with specific field messages and does not create simulation

#### Scenario: User quota exceeded
- **WHEN** user exceeds daily simulation quota for their tier
- **THEN** system returns quota exceeded error with upgrade suggestion

### Requirement: Real-Time Status Tracking
The system SHALL provide real-time status updates during simulation processing with progress indicators and estimated completion times.

#### Scenario: Status check during processing
- **WHEN** user requests simulation status during processing
- **THEN** system returns current status, progress percentage, current step, and estimated completion time

#### Scenario: Status check for completed simulation
- **WHEN** user requests status for completed simulation
- **THEN** system returns completed status with processing time and result availability

### Requirement: AI-Generated Results
The system SHALL deliver comprehensive AI-generated analysis including product names, descriptions, scientific references, market analysis, and business recommendations.

#### Scenario: Complete simulation results
- **WHEN** simulation processing completes successfully
- **THEN** system returns product names (3-5 alternatives), taglines, descriptions, ingredient analysis, scientific references, market analysis, pricing recommendations, and marketing copy

#### Scenario: Partial results with fallback
- **WHEN** some AI providers fail during processing
- **THEN** system returns available results with degraded quality indicators and fallback content

### Requirement: Export Functionality
The system SHALL generate downloadable PDF and Word document exports with customizable sections and professional formatting.

#### Scenario: PDF export generation
- **WHEN** user requests PDF export of simulation results
- **THEN** system generates professional PDF with selected sections and provides download link

#### Scenario: Word document export
- **WHEN** user requests Word export of simulation results
- **THEN** system generates editable Word document with customizable sections and provides download link

### Requirement: Simulation Regeneration
The system SHALL allow users to regenerate simulations with same inputs to create alternative AI-generated results.

#### Scenario: Regenerate simulation
- **WHEN** user requests regeneration of existing simulation
- **THEN** system creates new simulation with same inputs, generates new results, and maintains original simulation history

### Requirement: Rate Limiting Integration
The system SHALL enforce user tier-based simulation quotas and provide clear quota information.

#### Scenario: Free tier quota enforcement
- **WHEN** free user attempts to exceed 50 simulations per day
- **THEN** system blocks request and suggests premium upgrade

#### Scenario: Premium tier quota enforcement
- **WHEN** premium user attempts to exceed 200 simulations per day
- **THEN** system blocks request and suggests enterprise upgrade

### Requirement: WhatsApp Lead Generation
The system SHALL generate pre-filled WhatsApp consultation links for lead conversion.

#### Scenario: WhatsApp CTA generation
- **WHEN** simulation completes successfully
- **THEN** system generates WhatsApp link with pre-filled message including product name and user context

### Requirement: Error Handling and Resilience
The system SHALL handle AI provider failures, external API errors, and processing timeouts with graceful fallbacks.

#### Scenario: AI provider failure
- **WHEN** primary AI provider fails during processing
- **THEN** system automatically switches to fallback provider and continues processing

#### Scenario: Processing timeout
- **WHEN** simulation processing exceeds 120 seconds
- **THEN** system returns timeout error with option to retry

#### Scenario: External API failure
- **WHEN** external APIs (PubMed, Crossref) fail during processing
- **THEN** system continues with available data and indicates missing information

### Requirement: Guest Session Integration
The system SHALL integrate with guest form management to restore form data and proceed with simulation after authentication.

#### Scenario: Guest to authenticated simulation
- **WHEN** guest user authenticates after form completion
- **THEN** system restores saved form data and automatically starts simulation processing

### Requirement: Simulation History Management
The system SHALL store and retrieve simulation history for authenticated users with proper data relationships.

#### Scenario: User simulation history
- **WHEN** authenticated user requests simulation history
- **THEN** system returns paginated list of user's simulations with status and creation dates

#### Scenario: Simulation data retention
- **WHEN** simulation data reaches retention period
- **THEN** system automatically archives or deletes old simulation data according to policy
