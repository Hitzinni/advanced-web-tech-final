<?php
// API Debug Tool

// Display all PHP errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Collect server information
$serverInfo = [
    'REQUEST_METHOD' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    'HTTP_HOST' => $_SERVER['HTTP_HOST'] ?? 'unknown',
    'SCRIPT_NAME' => $_SERVER['SCRIPT_NAME'] ?? 'unknown',
    'SCRIPT_FILENAME' => $_SERVER['SCRIPT_FILENAME'] ?? 'unknown',
    'DOCUMENT_ROOT' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
    'PHP_SELF' => $_SERVER['PHP_SELF'] ?? 'unknown',
    'BASE_PATH' => dirname(__DIR__) ?? 'unknown',
    'Current Directory' => getcwd() ?? 'unknown'
];

// Check for specific directories
$pathInfo = [
    'api_dir_exists' => is_dir(__DIR__ . '/api'),
    'api_register_dir_exists' => is_dir(__DIR__ . '/api/register'),
    'api_register_file_exists' => file_exists(__DIR__ . '/api/register/index.php')
];

// Collect file info
$fileContents = [];
if (file_exists(__DIR__ . '/api/register/index.php')) {
    $fileContents['api/register/index.php'] = htmlspecialchars(file_get_contents(__DIR__ . '/api/register/index.php'));
}
if (file_exists(__DIR__ . '/.htaccess')) {
    $fileContents['.htaccess'] = htmlspecialchars(file_get_contents(__DIR__ . '/.htaccess'));
}

// Generate a test request URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$baseUrl = $protocol . '://' . $host;
$apiUrl = $baseUrl . '/api/register';
$relativeApiUrl = './api/register';

// Output as HTML
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Debug Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1, h2 { color: #333; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
        .section { margin-bottom: 30px; border: 1px solid #ddd; padding: 15px; border-radius: 5px; }
        button { padding: 10px; cursor: pointer; }
        #result { margin-top: 10px; padding: 10px; background: #f0f0f0; display: none; }
    </style>
</head>
<body>
    <h1>API Debug Tool</h1>
    
    <div class="section">
        <h2>Server Information</h2>
        <pre><?= json_encode($serverInfo, JSON_PRETTY_PRINT) ?></pre>
    </div>
    
    <div class="section">
        <h2>Path Information</h2>
        <pre><?= json_encode($pathInfo, JSON_PRETTY_PRINT) ?></pre>
    </div>
    
    <?php foreach ($fileContents as $file => $content): ?>
    <div class="section">
        <h2>File: <?= htmlspecialchars($file) ?></h2>
        <pre><?= $content ?></pre>
    </div>
    <?php endforeach; ?>
    
    <div class="section">
        <h2>Test API Request</h2>
        <p>Absolute URL: <code><?= htmlspecialchars($apiUrl) ?></code></p>
        <p>Relative URL: <code><?= htmlspecialchars($relativeApiUrl) ?></code></p>
        <p>Use this to test if the API endpoint is accessible:</p>
        
        <button id="testAbsolute">Test Absolute URL</button>
        <button id="testRelative">Test Relative URL</button>
        <div id="result"></div>
        
        <script>
            document.getElementById('testAbsolute').addEventListener('click', async () => {
                await testEndpoint('<?= $apiUrl ?>');
            });
            
            document.getElementById('testRelative').addEventListener('click', async () => {
                await testEndpoint('<?= $relativeApiUrl ?>');
            });
            
            async function testEndpoint(url) {
                const resultDiv = document.getElementById('result');
                resultDiv.style.display = 'block';
                resultDiv.innerHTML = 'Testing...';
                
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            name: 'Test User',
                            email: 'test@example.com',
                            phone: '1234567890',
                            password: 'testing123'
                        })
                    });
                    
                    let responseText = '';
                    try {
                        const jsonData = await response.json();
                        responseText = JSON.stringify(jsonData, null, 2);
                    } catch (parseError) {
                        const text = await response.text();
                        responseText = `Failed to parse JSON: ${text}`;
                    }
                    
                    resultDiv.innerHTML = `
                        <strong>Status:</strong> ${response.status} ${response.statusText}<br>
                        <strong>Response:</strong><br>
                        <pre>${responseText}</pre>
                    `;
                } catch (error) {
                    resultDiv.innerHTML = `<strong>Error:</strong> ${error.message}`;
                }
            }
        </script>
    </div>
</body>
</html> 