<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Square Payment Integration</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Square SDK -->
    <script src="https://sandbox.web.squarecdn.com/v1/square.js"></script>
</head>

<body>
<div class="container">

    <div class="header">
        <h1>💳 Secure Payment</h1>
        <p>Complete your purchase safely and securely</p>
    </div>

    <div class="payment-form">

        <form id="payment-form">

            <div class="form-group">
                <label for="amount">Amount (USD)</label>
                <input type="number" id="amount" value="10" min="1" step="0.01">
            </div>

            <div class="form-group">
                <label for="customer_email">Email Address</label>
                <input type="email" id="customer_email" placeholder="your@email.com">
            </div>

            <div class="payment-details">
                <h3>🔒 Card Information</h3>
                <div id="card-container"></div>
            </div>

            <button type="button" id="card-button" disabled>
                <span id="button-text">Pay Now</span>
            </button>

            <div id="message"></div>
        </form>

    </div>

    <div class="footer">
        <p>🔒 Secure & encrypted</p>
    </div>

</div>


<script>

    const appId = "{{ $app_id }}";
    const locationId = "{{ $location_id }}";

    let payments;
    let card;

    // ✅ Initialize Square
    async function initSquare() {
        try {
            payments = window.Square.payments(appId, locationId);

            card = await payments.card();
            await card.attach("#card-container");

            document.getElementById("card-button").disabled = false;

        } catch (e) {
            showMessage("Square failed to load.", "error");
        }
    }

    // ✅ Show Toast Messages
    function showMessage(msg, type) {
        const box = document.getElementById("message");

        box.className = type;
        box.textContent = msg;

        if (type === "success") {
            setTimeout(() => box.textContent = "", 3000);
        }
    }

    // ✅ Tokenize card
    async function tokenize() {
        const result = await card.tokenize();

        if (result.status === "OK") {
            return result.token;
        }

        throw new Error(result.errors?.[0]?.message || "Tokenization failed");
    }

    // ✅ Handle payment button click
    document.getElementById("card-button").onclick = async () => {

        const amount = document.getElementById("amount").value;
        const email  = document.getElementById("customer_email").value;

        if (!amount || amount < 1) {
            showMessage("Please enter valid amount", "error");
            return;
        }

        if (!email) {
            showMessage("Please enter email", "error");
            return;
        }

        const btn = document.getElementById("card-button");
        const text = document.getElementById("button-text");

        btn.disabled = true;
        text.textContent = "Processing...";

        let token = null;
        let clientError = null;

        try {
            token = await tokenize();    // ✅ Try tokenization

        } catch (e) {
            clientError = e.message;     // ✅ Capture frontend error
        }

        // ✅ Always send POST request to backend (even if token fails)
        const payload = {
            sourceId: token,               // null if fail
            amount: parseFloat(amount),
            customer_email: email,
            order_id: "ORD-" + Date.now(),
            client_error: clientError      // ✅ frontend error stored in DB
        };

        try {
            const response = await fetch("/process-payment", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector("meta[name='csrf-token']").content
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                showMessage("Payment Successful ✅", "success");

                setTimeout(() => {
                    window.location.href = "/pay";
                }, 1500);

            } else {
                showMessage("Payment Failed ❌", "error");
            }

        } catch (e) {
            showMessage("Network Error: " + e.message, "error");

        } finally {
            btn.disabled = false;
            text.textContent = "Pay Now";
        }
    };

    document.addEventListener("DOMContentLoaded", initSquare);

</script>

</body>
</html>
