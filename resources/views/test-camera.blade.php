<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Camera Test</title>
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .container { max-width: 400px; margin: 0 auto; }
        #qr-reader { width: 100%; height: 300px; border: 1px solid #ccc; }
        button { padding: 10px 20px; margin: 10px 5px; }
        .status { padding: 10px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        .info { background: #cce7ff; color: #004085; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Camera Test</h1>
        <div id="status" class="status info">Ready to test camera</div>
        
        <div>
            <button onclick="testCamera()">Test Camera Access</button>
            <button onclick="startScanner()">Start QR Scanner</button>
            <button onclick="stopScanner()">Stop Scanner</button>
        </div>
        
        <div id="qr-reader"></div>
        
        <div id="result"></div>
    </div>

    <script>
        let qrScanner = null;
        let isScanning = false;
        
        function updateStatus(message, type = 'info') {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = `status ${type}`;
        }
        
        async function testCamera() {
            updateStatus('Testing camera access...', 'info');
            
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ video: true });
                updateStatus('Camera access granted!', 'success');
                
                // Stop the test stream
                stream.getTracks().forEach(track => track.stop());
                
                setTimeout(() => {
                    updateStatus('Camera test complete. Ready for QR scanning.', 'info');
                }, 2000);
                
            } catch (error) {
                console.error('Camera error:', error);
                let message = 'Camera access denied: ';
                
                if (error.name === 'NotAllowedError') {
                    message += 'Permission denied';
                } else if (error.name === 'NotFoundError') {
                    message += 'No camera found';
                } else if (error.name === 'NotSupportedError') {
                    message += 'Camera not supported';
                } else {
                    message += error.message;
                }
                
                updateStatus(message, 'error');
            }
        }
        
        function startScanner() {
            if (isScanning) return;
            
            updateStatus('Starting QR scanner...', 'info');
            
            qrScanner = new Html5QrcodeScanner("qr-reader", {
                fps: 10,
                qrbox: { width: 250, height: 250 }
            });
            
            qrScanner.render(onScanSuccess, onScanError);
            isScanning = true;
            updateStatus('QR scanner active', 'success');
        }
        
        function stopScanner() {
            if (!isScanning || !qrScanner) return;
            
            qrScanner.clear();
            isScanning = false;
            updateStatus('QR scanner stopped', 'info');
        }
        
        function onScanSuccess(decodedText, decodedResult) {
            console.log('QR Code scanned:', decodedText);
            document.getElementById('result').innerHTML = `
                <div class="status success">
                    <strong>QR Code Detected:</strong><br>
                    ${decodedText}
                </div>
            `;
        }
        
        function onScanError(error) {
            // Ignore frequent scan errors
            console.log('Scan error (normal):', error);
        }
        
        // Check HTTPS
        if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
            updateStatus('Warning: Camera may not work without HTTPS', 'error');
        }
        
        // Auto-test on load for debugging
        // testCamera();
    </script>
</body>
</html>

