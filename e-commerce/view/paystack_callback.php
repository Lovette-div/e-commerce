<?php
/**
 * Paystack Payment Callback Handler
 * This page is called after Paystack payment process
 * User is redirected here by Paystack after payment
 */

require_once '../settings/core.php';
require_once '../settings/paystack_config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ../login/login.php');
    exit();
}

// Get reference from URL
$reference = isset($_GET['reference']) ? trim($_GET['reference']) : null;

if (!$reference) {
    // Payment cancelled or reference missing
    header('Location: checkout.php?error=cancelled');
    exit();
}

error_log("=== PAYSTACK CALLBACK PAGE ===");
error_log("Reference from URL: $reference");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Processing Payment - Launder</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Cormorant+Garamond:wght@600;700&display=swap" rel="stylesheet">
    <style>
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        
        body { 
            font-family: 'Inter', sans-serif; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            padding: 20px;
        }
        
        .container { 
            max-width: 500px; 
            width: 90%; 
            background: white; 
            padding: 60px 40px; 
            border-radius: 20px; 
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1); 
            text-align: center;
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .spinner {
            display: inline-block;
            width: 60px;
            height: 60px;
            border: 5px solid #e2e8f0;
            border-top: 5px solid #50c8aff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 30px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        h1 { 
            font-family: 'Cormorant Garamond', serif; 
            font-size: 2rem; 
            color: #2d3748; 
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        p { 
            color: #718096; 
            font-size: 16px; 
            line-height: 1.6; 
            margin-bottom: 20px; 
        }
        
        .reference { 
            background: #f7fafc; 
            padding: 15px; 
            border-radius: 10px; 
            margin: 25px 0; 
            word-break: break-all; 
            font-family: monospace; 
            font-size: 13px; 
            color: #4a5568;
            border: 2px solid #e2e8f0;
        }

        .reference strong {
            color: #50c8aff;
            font-weight: 600;
        }
        
        .error { 
            color: #c0392b; 
            background: #fadbd8; 
            border: 2px solid #f1948a; 
            padding: 15px; 
            border-radius: 10px; 
            margin: 20px 0; 
            display: none;
            animation: shake 0.5s ease;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .success { 
            color: #229954; 
            background: #eafaf1; 
            border: 2px solid #abebc6; 
            padding: 15px; 
            border-radius: 10px; 
            margin: 20px 0; 
            display: none;
            animation: slideDown 0.5s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            font-size: 3rem;
            margin-bottom: 20px;
        }

        .progress-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 30px;
        }

        .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #e2e8f0;
            animation: pulse 1.5s ease-in-out infinite;
        }

        .dot:nth-child(1) { animation-delay: 0s; }
        .dot:nth-child(2) { animation-delay: 0.2s; }
        .dot:nth-child(3) { animation-delay: 0.4s; }

        @keyframes pulse {
            0%, 100% { 
                background: #e2e8f0;
                transform: scale(1);
            }
            50% { 
                background: #50c8aff;
                transform: scale(1.3);
            }
        }

        @media (max-width: 600px) {
            .container {
                padding: 40px 25px;
            }

            h1 {
                font-size: 1.5rem;
            }

            .reference {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">🧺</div>
        <div class="spinner" id="spinner"></div>
        
        <h1>Verifying Payment</h1>
        <p>Please wait while we verify your payment with Paystack...</p>
        
        <div class="reference">
            Payment Reference: <strong><?php echo htmlspecialchars($reference); ?></strong>
        </div>
        
        <div class="progress-dots">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
        
        <div class="error" id="errorBox">
            <strong>⚠️ Error:</strong> <span id="errorMessage"></span>
        </div>
        
        <div class="success" id="successBox">
            <strong>✓ Success!</strong> Your payment has been verified. Redirecting...
        </div>
    </div>

    <script>
        /**
         * Verify payment with backend
         */
        async function verifyPayment() {
            const reference = '<?php echo htmlspecialchars($reference); ?>';
            
            try {
                const response = await fetch('../actions/paystack_verify_payment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        reference: reference,
                        cart_items: null, // Will be fetched from backend
                        total_amount: null // Will be calculated from cart
                    })
                });
                
                const data = await response.json();
                console.log('Verification response:', data);
                
                // Hide spinner
                document.getElementById('spinner').style.display = 'none';
                
                if (data.status === 'success' && data.verified) {
                    // Payment verified successfully
                    document.getElementById('successBox').style.display = 'block';
                    
                    // Redirect to success page after 1 second
                    setTimeout(() => {
                        window.location.replace(`payment_success.php?reference=${encodeURIComponent(reference)}&invoice=${encodeURIComponent(data.invoice_no)}`);
                    }, 1000);
                    
                } else {
                    // Payment verification failed
                    const errorMsg = data.message || 'Payment verification failed';
                    showError(errorMsg);
                    
                    // Redirect to checkout after 5 seconds
                    setTimeout(() => {
                        window.location.href = 'checkout.php?error=verification_failed';
                    }, 5000);
                }
                
            } catch (error) {
                console.error('Verification error:', error);
                showError('Connection error. Please try again or contact support.');
                
                // Redirect to checkout after 5 seconds
                setTimeout(() => {
                    window.location.href = 'checkout.php?error=connection_error';
                }, 5000);
            }
        }
        
        /**
         * Show error message
         */
        function showError(message) {
            document.getElementById('errorBox').style.display = 'block';
            document.getElementById('errorMessage').textContent = message;
        }
        
        // Start verification when page loads
        window.addEventListener('load', verifyPayment);
    </script>
</body>
</html>