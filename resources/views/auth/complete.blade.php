<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication — Quranic Clinic</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #ebfafa;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 48px 32px;
            max-width: 360px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 32px rgba(19,84,82,0.10);
        }
        .spinner {
            display: inline-block;
            width: 36px;
            height: 36px;
            border: 4px solid #d5e9e9;
            border-top-color: #135452;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin-bottom: 24px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .icon { font-size: 48px; margin-bottom: 20px; }
        h2 { font-size: 20px; font-weight: 700; color: #135452; margin-bottom: 10px; }
        p  { font-size: 14px; color: #535862; line-height: 1.6; }
    </style>
</head>
<body>
    <div class="card">
        @if($status === 'success')
            <div class="spinner"></div>
            <h2>Signing you in…</h2>
            <p>Please wait, returning to the app.</p>
            <script>
                // Close the Custom Tab on Android by navigating to the app's URL scheme.
                // On iOS the app's polling already dismissed the browser before this fires.
                setTimeout(function() { window.location.href = 'quranicclinic://'; }, 2000);
            </script>
        @elseif($status === 'error')
            <div class="icon">❌</div>
            <h2>Authentication Failed</h2>
            <p>Something went wrong. Please close this window and try again.</p>
        @endif
    </div>
</body>
</html>
