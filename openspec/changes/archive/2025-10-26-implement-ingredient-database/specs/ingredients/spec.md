## ADDED Requirements

### Requirement: Ingredient Database Management
The system SHALL provide comprehensive ingredient management capabilities including CRUD operations, search functionality, categorization, and scientific reference linking to support skincare product formulation and validation.

#### Scenario: Create new ingredient
- **GIVEN** an authenticated user with ingredient management permissions
- **WHEN** they submit ingredient data including name, INCI name, description, effects, safety notes, and concentration ranges
- **THEN** the system creates a new ingredient record with proper validation
- **AND** assigns it to the specified category
- **AND** returns the complete ingredient data with generated ID and timestamps

#### Scenario: Search ingredients by name and effects
- **GIVEN** a user wants to find ingredients for a specific purpose
- **WHEN** they search using keywords like "moisturizing" or "anti-aging"
- **THEN** the system returns ingredients matching the search terms in name, INCI name, description, or effects
- **AND** results are ranked by relevance
- **AND** includes pagination for large result sets

#### Scenario: Filter ingredients by category and safety
- **GIVEN** a user wants to browse ingredients by specific criteria
- **WHEN** they apply filters for category, safety level, or active status
- **THEN** the system returns only ingredients matching the filter criteria
- **AND** maintains search functionality within filtered results
- **AND** provides filter counts for each category

#### Scenario: Link scientific references to ingredients
- **GIVEN** an ingredient has scientific backing
- **WHEN** a user adds scientific references including DOI, PubMed ID, or research papers
- **THEN** the system creates reference records and links them to the ingredient
- **AND** validates DOI and PubMed ID formats
- **AND** stores reference metadata including authors, journal, and publication date

#### Scenario: Manage ingredient categories
- **GIVEN** the system needs organized ingredient classification
- **WHEN** administrators create or update ingredient categories
- **THEN** the system maintains hierarchical category structure
- **AND** supports category descriptions and color coding
- **AND** allows sorting and activation/deactivation of categories

#### Scenario: Retrieve ingredient with full details
- **GIVEN** a user requests detailed ingredient information
- **WHEN** they access an ingredient by ID or INCI name
- **THEN** the system returns complete ingredient data including effects, safety notes, concentration ranges, category information, and linked scientific references
- **AND** includes related ingredients from the same category
- **AND** provides usage recommendations based on product type

#### Scenario: Update ingredient information
- **GIVEN** an existing ingredient needs modification
- **WHEN** authorized users update ingredient details
- **THEN** the system validates the changes and updates the record
- **AND** maintains audit trail of changes
- **AND** notifies users of significant updates to ingredients they've used in simulations

#### Scenario: Deactivate ingredient safely
- **GIVEN** an ingredient needs to be removed from active use
- **WHEN** administrators deactivate an ingredient
- **THEN** the system marks the ingredient as inactive
- **AND** prevents new simulations from using the ingredient
- **AND** maintains historical data for existing simulations
- **AND** provides migration path for affected formulations
