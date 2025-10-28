# Design: Simulation Engine Architecture

## Context
The simulation engine is the core business logic that transforms user form input into comprehensive AI-generated product analysis. It serves as the bridge between the Laravel application and the n8n AI processing pipeline, handling the complete simulation lifecycle from form submission to result delivery.

## Goals / Non-Goals

### Goals
- **Core Business Value**: Deliver the main differentiating feature of AI-generated product analysis
- **Performance**: Complete simulations within 120 seconds end-to-end
- **Reliability**: Handle AI provider failures with graceful fallbacks
- **Scalability**: Support 50+ concurrent simulations
- **User Experience**: Provide real-time progress tracking and status updates
- **Integration**: Seamlessly connect Laravel with n8n workflow orchestration

### Non-Goals
- **AI Model Training**: No custom AI model development
- **Market Data Scraping**: No direct marketplace scraping (handled by n8n)
- **UI Components**: No frontend interface development (handled in Proposal H)
- **n8n Workflow Design**: No workflow node configuration (handled in Proposal G)

## Decisions

### Decision: Laravel-n8n Integration Architecture
**What**: Use webhook-based integration between Laravel and n8n
**Why**: 
- n8n handles complex AI processing orchestration
- Laravel focuses on user management, data storage, and API responses
- Clear separation of concerns between business logic and AI processing
- n8n provides visual workflow management and external API integration

**Alternatives considered**:
- Direct AI API calls from Laravel: Too complex for multi-provider fallbacks
- Queue-based processing: Adds unnecessary complexity for this use case
- Microservices architecture: Overkill for current scale requirements

### Decision: Simulation Status Tracking
**What**: Implement comprehensive status tracking with progress indicators
**Why**:
- Users need feedback during 60-120 second processing time
- Status tracking enables better error handling and debugging
- Progress indicators improve user experience and reduce abandonment

**Implementation**:
```php
enum SimulationStatus: string
{
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}
```

### Decision: Multi-Format Export System
**What**: Support both PDF and Word document exports with customizable sections
**Why**:
- Different users have different documentation needs
- PDF for presentations and sharing
- Word for further editing and customization
- Customizable sections allow users to focus on relevant information

### Decision: Rate Limiting Integration
**What**: Integrate with existing user tier system for simulation quotas
**Why**:
- Prevents abuse and controls costs
- Enables freemium business model
- Tier-based limits encourage upgrades

**Implementation**:
```php
// User tier quotas
'free' => 50,           // simulations per day
'premium' => 200,       // simulations per day  
'enterprise' => 1000,   // simulations per day
```

## Risks / Trade-offs

### Risk: AI Provider Failures
**Impact**: High - Core functionality unavailable
**Mitigation**: 
- Multi-provider fallback system (OpenAI → Gemini → Claude)
- Cached responses for common requests
- Graceful degradation with partial results

### Risk: n8n Workflow Failures
**Impact**: High - Processing pipeline broken
**Mitigation**:
- Comprehensive error handling and logging
- Retry mechanisms with exponential backoff
- Fallback to simplified processing if needed

### Risk: Performance Under Load
**Impact**: Medium - User experience degradation
**Mitigation**:
- Queue-based processing for high load
- Database indexing for fast queries
- Redis caching for frequent lookups
- Load balancing for horizontal scaling

### Risk: Export Generation Failures
**Impact**: Low - Secondary feature unavailable
**Mitigation**:
- Robust error handling in export service
- Fallback to basic text export
- Clear error messages to users

## Migration Plan

### Phase 1: Core Infrastructure (Week 1)
1. Database schema and models
2. Basic simulation controller
3. n8n integration service
4. Status tracking system

### Phase 2: Advanced Features (Week 2)
1. Export functionality
2. Regeneration capability
3. Rate limiting integration
4. Error handling and resilience

### Phase 3: Testing & Optimization
1. Comprehensive testing suite
2. Performance optimization
3. Documentation completion
4. Integration validation

## Open Questions

### Q1: n8n Webhook Security
**Question**: How to secure n8n webhook endpoints from unauthorized access?
**Options**:
- API key authentication
- Webhook signature validation
- IP whitelisting
**Recommendation**: Webhook signature validation with HMAC

### Q2: Export File Storage
**Question**: Where to store generated export files?
**Options**:
- Local file system
- AWS S3
- Google Cloud Storage
**Recommendation**: Start with local storage, migrate to S3 for production

### Q3: Simulation Data Retention
**Question**: How long to retain simulation data?
**Options**:
- 30 days
- 90 days
- 1 year
**Recommendation**: 90 days with user option to extend

## Technical Architecture

### Component Diagram
```
User Request → SimulationController → N8nService → n8n Workflow
     ↓              ↓                    ↓
Database ← ExportService ← WebhookHandler ← AI Processing
     ↓
User Response
```

### Data Flow
1. **Form Submission**: User submits 18-field form
2. **Validation**: Laravel validates input and checks quotas
3. **Storage**: Store simulation request in database
4. **Trigger**: Send data to n8n workflow
5. **Processing**: n8n orchestrates AI processing
6. **Webhook**: n8n sends results back to Laravel
7. **Storage**: Store complete results in database
8. **Response**: Return results to user

### Database Schema
```sql
simulation_histories:
├── id (Primary Key)
├── user_id (Foreign Key, nullable for guests)
├── simulation_id (Unique identifier)
├── input_data (JSON - 18 form fields)
├── output_data (JSON - AI results)
├── status (enum: processing, completed, failed)
├── processing_started_at (timestamp)
├── completed_at (timestamp)
├── processing_time_seconds (integer)
├── created_at, updated_at
└── Indexes for performance
```

### API Endpoints
```php
POST   /api/simulations              // Create simulation
GET    /api/simulations/{id}        // Get results
GET    /api/simulations/{id}/status // Check progress
POST   /api/simulations/{id}/regenerate // Create alternative
POST   /api/simulations/{id}/export // Generate PDF/Word
```

## Success Metrics

### Performance Metrics
- **Processing Time**: < 120 seconds end-to-end
- **Success Rate**: > 95% successful simulations
- **Concurrent Users**: Support 50+ simultaneous simulations
- **API Response Time**: < 5 seconds for status checks

### Business Metrics
- **User Engagement**: > 3 minutes average result viewing time
- **Export Usage**: > 40% of users download exports
- **Regeneration Rate**: > 20% of users regenerate simulations
- **WhatsApp Conversion**: > 15% click-through to consultation

### Technical Metrics
- **Error Rate**: < 1% for simulation failures
- **Cache Hit Rate**: > 80% for ingredient lookups
- **Queue Processing**: < 30 seconds average queue time
- **Database Performance**: < 100ms for simulation queries
