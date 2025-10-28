<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Simulation Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        .info { margin: 10px 0; }
    </style>
</head>
<body>
    <h1>AI Skincare Product Simulator</h1>
    <h2>Simulation Report</h2>
    
    <div class="info">
        <strong>Simulation ID:</strong> {{ $simulation_id ?? 'N/A' }}
    </div>
    <div class="info">
        <strong>Generated At:</strong> {{ $generated_at ?? 'N/A' }}
    </div>
    <div class="info">
        <strong>Processing Time:</strong> {{ $processing_time ?? 'N/A' }}
    </div>
    
    @if(isset($product_overview))
    <h3>Product Overview</h3>
    <div class="info">
        <strong>Product Name:</strong> {{ $product_overview['product_name'] ?? 'N/A' }}
    </div>
    <div class="info">
        <strong>Tagline:</strong> {{ $product_overview['tagline'] ?? 'N/A' }}
    </div>
    <div class="info">
        <strong>Description:</strong> {{ $product_overview['description'] ?? 'N/A' }}
    </div>
    @endif
    
    @if(isset($ingredients))
    <h3>Ingredients</h3>
    <div class="info">
        <strong>Compatibility Score:</strong> {{ $ingredients['compatibility_score'] ?? 'N/A' }}
    </div>
    @endif
</body>
</html>
