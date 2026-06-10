<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f0f4f8; display: flex; align-items: center; justify-content: center; min-height: 100vh; padding: 20px; }
        .card { background: #fff; border-radius: 16px; padding: 48px 32px; max-width: 360px; width: 100%; text-align: center; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .icon { font-size: 56px; margin-bottom: 20px; }
        h2 { font-size: 22px; font-weight: 600; color: #1a1a2e; margin-bottom: 10px; }
        p { font-size: 15px; color: #666; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="card">
        @if($status === 'success')
            <div class="icon">✅</div>
            <h2>Sign-in Successful</h2>
            <p>Returning to app…</p>
        @elseif($status === 'verification_required')
            <div class="icon">📧</div>
            <h2>Check Your Email</h2>
            <p>A verification code has been sent. Returning to app…</p>
        @else
            <div class="icon">❌</div>
            <h2>Authentication Failed</h2>
            <p>Something went wrong. Please try again.</p>
        @endif
    </div>
    <script>
        // Redirect to the app's custom scheme so WebBrowser.openAuthSessionAsync detects
        // the callback and closes the browser automatically.
        setTimeout(function () {
            window.location.href = 'quranicclinic://auth-callback?status={{ $status }}';
        }, 400);
    </script>
</body>
</html>
