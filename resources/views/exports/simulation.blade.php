<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Skincare Product Simulator - Simulation Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 20px;
            background: #fff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2c3e50;
            padding-bottom: 20px;
        }
        
        .header h1 {
            font-size: 28px;
            color: #2c3e50;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .header .subtitle {
            font-size: 16px;
            color: #7f8c8d;
            margin: 0;
        }
        
        .simulation-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 25px;
            border-left: 4px solid #3498db;
        }
        
        .simulation-info h2 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            font-size: 18px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 5px 10px 5px 0;
            width: 30%;
            color: #34495e;
        }
        
        .info-value {
            display: table-cell;
            padding: 5px 0;
            color: #2c3e50;
        }
        
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .section h2 {
            font-size: 20px;
            color: #2c3e50;
            margin: 0 0 15px 0;
            padding-bottom: 8px;
            border-bottom: 2px solid #ecf0f1;
            font-weight: bold;
        }
        
        .section h3 {
            font-size: 16px;
            color: #34495e;
            margin: 15px 0 10px 0;
            font-weight: bold;
        }
        
        .product-overview {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        
        .product-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin: 0 0 10px 0;
        }
        
        .product-tagline {
            font-size: 16px;
            color: #7f8c8d;
            font-style: italic;
            margin: 0 0 15px 0;
        }
        
        .product-description {
            font-size: 14px;
            line-height: 1.8;
            color: #2c3e50;
        }
        
        .ingredients-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .ingredients-table th,
        .ingredients-table td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: left;
        }
        
        .ingredients-table th {
            background: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }
        
        .ingredients-table tr:nth-child(even) {
            background: #f8f9fa;
        }
        
        .market-analysis {
            background: #e8f5e8;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #28a745;
        }
        
        .pricing-info {
            background: #fff3cd;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #ffc107;
        }
        
        .references-list {
            list-style: none;
            padding: 0;
        }
        
        .references-list li {
            margin: 8px 0;
            padding: 10px;
            background: #f8f9fa;
            border-left: 3px solid #007bff;
            border-radius: 3px;
        }
        
        .marketing-section {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #2196f3;
        }
        
        .key-points {
            list-style: none;
            padding: 0;
        }
        
        .key-points li {
            margin: 8px 0;
            padding: 8px 12px;
            background: #fff;
            border-radius: 5px;
            border-left: 3px solid #007bff;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 10px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .highlight {
            background: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
        }
        
        .success {
            color: #28a745;
            font-weight: bold;
        }
        
        .warning {
            color: #ffc107;
            font-weight: bold;
        }
        
        .danger {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>AI Skincare Product Simulator</h1>
        <p class="subtitle">Comprehensive Product Development Report</p>
    </div>

    <div class="simulation-info">
        <h2>Simulation Details</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Simulation ID:</div>
                <div class="info-value">{{ $simulation_id ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Generated At:</div>
                <div class="info-value">{{ $generated_at ?? 'N/A' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Processing Time:</div>
                <div class="info-value">{{ $processing_time ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    @if(isset($product_overview))
    <div class="section">
        <h2>Product Overview</h2>
        <div class="product-overview">
            <div class="product-name">{{ $product_overview['product_name'] ?? 'N/A' }}</div>
            <div class="product-tagline">{{ $product_overview['tagline'] ?? 'N/A' }}</div>
            <div class="product-description">{{ $product_overview['description'] ?? 'N/A' }}</div>
            
            @if(isset($product_overview['alternative_names']) && count($product_overview['alternative_names']) > 0)
            <h3>Alternative Names</h3>
            <ul>
                @foreach($product_overview['alternative_names'] as $name)
                <li>{{ $name }}</li>
                @endforeach
            </ul>
            @endif
            
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Formulation Type:</div>
                    <div class="info-value">{{ $product_overview['formulation_type'] ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Volume:</div>
                    <div class="info-value">{{ $product_overview['volume'] ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Target Gender:</div>
                    <div class="info-value">{{ $product_overview['target_market']['gender'] ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Target Age:</div>
                    <div class="info-value">{{ implode(', ', $product_overview['target_market']['age_ranges'] ?? []) }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Target Country:</div>
                    <div class="info-value">{{ $product_overview['target_market']['country'] ?? 'N/A' }}</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(isset($ingredients))
    <div class="section">
        <h2>Ingredients Analysis</h2>
        
        @if(isset($ingredients['active_ingredients']) && count($ingredients['active_ingredients']) > 0)
        <h3>Active Ingredients</h3>
        <table class="ingredients-table">
            <thead>
                <tr>
                    <th>Ingredient</th>
                    <th>Concentration</th>
                    <th>Function</th>
                    <th>Safety Level</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ingredients['active_ingredients'] as $ingredient)
                <tr>
                    <td>{{ $ingredient['name'] ?? 'N/A' }}</td>
                    <td>{{ $ingredient['concentration'] ?? 'N/A' }}</td>
                    <td>{{ $ingredient['function'] ?? 'N/A' }}</td>
                    <td>
                        @if(isset($ingredient['safety_level']))
                            @if($ingredient['safety_level'] === 'safe')
                                <span class="success">Safe</span>
                            @elseif($ingredient['safety_level'] === 'caution')
                                <span class="warning">Caution</span>
                            @else
                                <span class="danger">{{ ucfirst($ingredient['safety_level']) }}</span>
                            @endif
                        @else
                            N/A
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
        
        @if(isset($ingredients['compatibility_score']))
        <h3>Compatibility Assessment</h3>
        <p><strong>Overall Compatibility Score:</strong> 
            <span class="highlight">{{ $ingredients['compatibility_score'] }}/10</span>
        </p>
        @endif
        
        @if(isset($ingredients['safety_assessment']))
        <h3>Safety Assessment</h3>
        <p>{{ $ingredients['safety_assessment'] }}</p>
        @endif
    </div>
    @endif

    @if(isset($market_analysis))
    <div class="section">
        <h2>Market Analysis</h2>
        <div class="market-analysis">
            @if(isset($market_analysis['competitor_analysis']))
            <h3>Competitor Analysis</h3>
            <p>{{ $market_analysis['competitor_analysis'] ?? 'N/A' }}</p>
            @endif
            
            @if(isset($market_analysis['market_trends']))
            <h3>Market Trends</h3>
            <p>{{ $market_analysis['market_trends'] ?? 'N/A' }}</p>
            @endif
            
            @if(isset($market_analysis['target_audience']))
            <h3>Target Audience Insights</h3>
            <p>{{ $market_analysis['target_audience'] ?? 'N/A' }}</p>
            @endif
        </div>
    </div>
    @endif

    @if(isset($pricing))
    <div class="section">
        <h2>Pricing Analysis</h2>
        <div class="pricing-info">
            @if(isset($pricing['estimated_cost']))
            <h3>Cost Estimation</h3>
            <div class="info-grid">
                <div class="info-row">
                    <div class="info-label">Raw Materials:</div>
                    <div class="info-value">{{ $pricing['estimated_cost']['raw_materials'] ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Manufacturing:</div>
                    <div class="info-value">{{ $pricing['estimated_cost']['manufacturing'] ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Packaging:</div>
                    <div class="info-value">{{ $pricing['estimated_cost']['packaging'] ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Total HPP:</div>
                    <div class="info-value"><strong>{{ $pricing['estimated_cost']['total_hpp'] ?? 'N/A' }}</strong></div>
                </div>
            </div>
            @endif
            
            @if(isset($pricing['recommended_retail']))
            <h3>Recommended Retail Price</h3>
            <p><strong>{{ $pricing['recommended_retail'] ?? 'N/A' }}</strong></p>
            @endif
            
            @if(isset($pricing['profit_margin']))
            <h3>Profit Margin</h3>
            <p><strong>{{ $pricing['profit_margin'] ?? 'N/A' }}</strong></p>
            @endif
        </div>
    </div>
    @endif

    @if(isset($scientific_references) && count($scientific_references) > 0)
    <div class="section">
        <h2>Scientific References</h2>
        <ul class="references-list">
            @foreach($scientific_references as $reference)
            <li>
                <strong>{{ $reference['title'] ?? 'N/A' }}</strong><br>
                <em>{{ $reference['authors'] ?? 'N/A' }}</em><br>
                {{ $reference['journal'] ?? 'N/A' }} - {{ $reference['year'] ?? 'N/A' }}<br>
                @if(isset($reference['url']))
                <a href="{{ $reference['url'] }}">{{ $reference['url'] }}</a>
                @endif
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(isset($marketing))
    <div class="section">
        <h2>Marketing Strategy</h2>
        <div class="marketing-section">
            @if(isset($marketing['copy']))
            <h3>Marketing Copy</h3>
            <p>{{ $marketing['copy'] }}</p>
            @endif
            
            @if(isset($marketing['key_selling_points']) && count($marketing['key_selling_points']) > 0)
            <h3>Key Selling Points</h3>
            <ul class="key-points">
                @foreach($marketing['key_selling_points'] as $point)
                <li>{{ $point }}</li>
                @endforeach
            </ul>
            @endif
            
            @if(isset($marketing['target_channels']) && count($marketing['target_channels']) > 0)
            <h3>Recommended Channels</h3>
            <ul>
                @foreach($marketing['target_channels'] as $channel)
                <li>{{ $channel }}</li>
                @endforeach
            </ul>
            @endif
            
            @if(isset($marketing['whatsapp_cta']))
            <h3>WhatsApp Contact</h3>
            <p><a href="{{ $marketing['whatsapp_cta'] }}">Contact via WhatsApp</a></p>
            @endif
        </div>
    </div>
    @endif

    <div class="footer">
        <p>Generated by AI Skincare Product Simulator | {{ now()->format('F j, Y \a\t g:i A') }}</p>
        <p>This report is generated by artificial intelligence and should be reviewed by qualified professionals.</p>
    </div>
</body>
</html>
