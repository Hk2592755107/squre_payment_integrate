<!DOCTYPE html>
<html>
<head>
    <title>Payment Successful</title>
    <style>
        body { font-family: Arial; text-align: center; padding: 50px; background: #f5f5f5; }
        .success-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); display: inline-block; }
        .success-icon { font-size: 60px; color: #28a745; margin-bottom: 20px; }
    </style>
</head>
<body>
<div class="success-box">
    <div class="success-icon">✅</div>
    <h2>Payment Successful!</h2>
    <p>Thank you for your payment.</p>
    <p><strong>Payment ID:</strong> {{ $payment['id'] ?? 'N/A' }}</p>
    <p><strong>Status:</strong> {{ $payment['status'] ?? 'COMPLETED' }}</p>
    <p><strong>Amount:</strong> ${{ $payment['amount'] ?? '10.00' }}</p>
    <br>
    <a href="/" style="background: #006aff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;">Make Another Payment</a>
</div>
</body>
</html>
