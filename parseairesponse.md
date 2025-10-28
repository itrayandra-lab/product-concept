const rawOutput = items[0].json;

let parsedData = {};

// Handle format {output: "```json\n{...}\n```"}
if (rawOutput.output) {
  const outputString = rawOutput.output;
  
  // Remove markdown code blocks and wrapper text
  let cleaned = outputString
    .replace(/```json\n?/g, '')
    .replace(/```\n?/g, '')
    .replace(/^[^{]*/, '') // Remove text before first {
    .replace(/[^}]*$/, '') // Remove text after last }
    .trim();
  
  try {
    parsedData = JSON.parse(cleaned);
  } catch (e) {
    console.error('Primary parse failed, attempting fallback:', e.message);
    
    // Fallback: extract JSON from string
    const jsonMatch = outputString.match(/\{[\s\S]*\}/);
    if (jsonMatch) {
      try {
        parsedData = JSON.parse(jsonMatch[0]);
      } catch (e2) {
        console.error('Fallback parse also failed:', e2.message);
        throw new Error('Unable to parse AI response: ' + e2.message);
      }
    } else {
      throw new Error('No valid JSON found in AI response');
    }
  }
} else {
  // Direct JSON response
  parsedData = rawOutput;
}

// Validate required fields
const requiredFields = [
  'product_names', 'selected_name', 'taglines', 'selected_tagline',
  'description', 'ingredients_analysis', 'scientific_references',
  'market_analysis', 'packaging_recommendations', 'regulatory_compliance',
  'marketing_suggestions', 'marketing_copywriting', 'key_trends', 'market_potential',
  'image_prompts'
];

const missingFields = requiredFields.filter(field => !parsedData[field]);

if (missingFields.length > 0) {
  console.warn('Missing fields in AI response:', missingFields);
  // Add empty structures for missing fields
  missingFields.forEach(field => {
    if (field === 'marketing_copywriting') {
      parsedData[field] = {
        headline: '',
        sub_headline: '',
        body_copy: '',
        social_media_captions: [],
        email_subject_lines: []
      };
    } else if (field === 'key_trends') {
      parsedData[field] = {
        trending_ingredients: [],
        market_movements: [],
        competitive_landscape: ''
      };
    } else if (field === 'market_potential') {
      parsedData[field] = {
        total_addressable_market: {},
        target_market_size: {},
        revenue_projections: {},
        growth_opportunities: [],
        risk_factors: []
      };
    } else if (field === 'image_prompts') {
      parsedData[field] = {
        product_hero_shot: '',
        lifestyle_shots: [],
        ingredient_visualization: [],
        before_after_mockup: '',
        social_media_assets: [],
        packaging_mockup: ''
      };
    }
  });
}

return [{ json: parsedData }];