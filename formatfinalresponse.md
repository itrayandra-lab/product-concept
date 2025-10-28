// Format final response sesuai specification
const data = items[0].json;

const formatted = {
  workflow_id: data.workflow_id || 'unknown',
  simulation_id: data.simulation_id || 0,
  status: "completed",
  processing_time_seconds: 0,
  generated_at: new Date().toISOString(),
  data: {
    product_names: data.product_names || [],
    selected_name: data.selected_name || '',
    taglines: data.taglines || [],
    selected_tagline: data.selected_tagline || '',
    description: data.description || '',
    ingredients_analysis: data.ingredients_analysis || {},
    scientific_references: data.scientific_references || [],
    market_analysis: data.market_analysis || {},
    packaging_recommendations: data.packaging_recommendations || {},
    regulatory_compliance: data.regulatory_compliance || {},
    marketing_suggestions: data.marketing_suggestions || {},
    
    // NEW SECTIONS
    marketing_copywriting: data.marketing_copywriting || {
      headline: '',
      sub_headline: '',
      body_copy: '',
      social_media_captions: [],
      email_subject_lines: []
    },
    
    key_trends: data.key_trends || {
      trending_ingredients: [],
      market_movements: [],
      competitive_landscape: ''
    },
    
    market_potential: data.market_potential || {
      total_addressable_market: {
        segment: '',
        estimated_size: 0,
        value_idr: 0,
        source: ''
      },
      target_market_size: {
        segment_description: '',
        estimated_customers: 0,
        penetration_rate: ''
      },
      revenue_projections: {
        scenario: 'moderate',
        monthly_units: 0,
        monthly_revenue: 0,
        yearly_revenue: 0,
        assumptions: []
      },
      growth_opportunities: [],
      risk_factors: []
    },
    
    // IMAGE PROMPTS SECTION
    image_prompts: data.image_prompts || {
      product_hero_shot: '',
      lifestyle_shots: [],
      ingredient_visualization: [],
      before_after_mockup: '',
      social_media_assets: [],
      packaging_mockup: ''
    }
  },
  processing_metadata: {
    ai_providers_used: ['google-gemini'],
    cache_hits: 0,
    cache_misses: 0,
    api_calls_made: 1,
    total_tokens_used: 0,
    estimated_cost_usd: 0,
    quality_score: 0
  }
};

return [{ json: formatted }];