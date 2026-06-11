<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>رمز التحقق — المشفى القرآني</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Arial', sans-serif;
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
            padding: 40px 28px 32px;
            max-width: 360px;
            width: 100%;
            text-align: center;
            box-shadow: 0 4px 32px rgba(19,84,82,0.10);
        }
        .brand {
            color: #135452;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 20px;
            font-weight: 700;
            color: #181d27;
            margin-bottom: 8px;
        }
        .hint {
            font-size: 14px;
            color: #535862;
            line-height: 1.7;
            margin-bottom: 28px;
        }
        .hint strong { color: #135452; }
        .boxes {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 16px;
            direction: ltr;
        }
        .box {
            width: 44px;
            height: 54px;
            border: 2px solid #d5d7da;
            border-radius: 10px;
            font-size: 24px;
            font-weight: 700;
            text-align: center;
            color: #181d27;
            background: #fff;
            outline: none;
            transition: border-color 0.15s, background 0.15s;
            -webkit-appearance: none;
        }
        .box:focus  { border-color: #135452; }
        .box.filled { border-color: #135452; background: #ebfafa; }
        .box.error  { border-color: #f04438; background: #fff8f7; animation: shake 0.3s; }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            25%      { transform: translateX(-4px); }
            75%      { transform: translateX(4px); }
        }
        .error-msg {
            color: #f04438;
            font-size: 13px;
            min-height: 20px;
            margin-bottom: 12px;
        }
        .spinner-wrap { margin: 8px 0 12px; min-height: 28px; }
        .spinner {
            display: inline-block;
            width: 24px;
            height: 24px;
            border: 3px solid #d5e9e9;
            border-top-color: #135452;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .success-msg {
            color: #135452;
            font-size: 15px;
            font-weight: 600;
            padding: 12px 0 4px;
        }
        .resend {
            margin-top: 20px;
            font-size: 13px;
            color: #717680;
        }
        .resend button {
            background: none;
            border: none;
            color: #135452;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
            padding: 0 4px;
        }
        .resend button:disabled { color: #aac8c8; cursor: default; }
        .hidden { display: none !important; }
    </style>
</head>
<body>
<div class="card">
    <div class="brand">المشفى القرآني</div>
    <h2>أدخل رمز التحقق</h2>
    <p class="hint">
        تم إرسال رمز مكوّن من 6 أرقام إلى<br>
        <strong>{{ $email }}</strong>
    </p>

    <div class="boxes" id="boxes">
        <input class="box" type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code">
        <input class="box" type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]">
        <input class="box" type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]">
        <input class="box" type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]">
        <input class="box" type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]">
        <input class="box" type="tel" maxlength="1" inputmode="numeric" pattern="[0-9]">
    </div>

    <div class="error-msg" id="error-msg"></div>

    <div class="spinner-wrap">
        <div class="spinner hidden" id="spinner"></div>
    </div>

    <div class="success-msg hidden" id="success-msg">
        ✅ تم التحقق، جارٍ الرجوع إلى التطبيق…
    </div>

    <div class="resend" id="resend-section">
        لم يصلك الرمز؟
        <button id="resend-btn" disabled>
            إعادة إرسال (<span id="countdown">60</span>)
        </button>
    </div>
</div>

<script>
    const EMAIL        = @json($email);
    const SESSION_TOKEN = @json($sessionToken);
    const API_BASE     = @json(rtrim(config('app.url'), '/'));

    const boxes       = Array.from(document.querySelectorAll('.box'));
    const errorMsg    = document.getElementById('error-msg');
    const spinner     = document.getElementById('spinner');
    const successMsg  = document.getElementById('success-msg');
    const resendBtn   = document.getElementById('resend-btn');
    const countdownEl = document.getElementById('countdown');
    const resendSec   = document.getElementById('resend-section');

    boxes[0].focus();

    // ── Countdown ──────────────────────────────────────────────────────────────
    let cdSecs = 60;
    const cdTimer = setInterval(() => {
        cdSecs--;
        countdownEl.textContent = cdSecs;
        if (cdSecs <= 0) {
            clearInterval(cdTimer);
            resendBtn.innerHTML = 'إعادة إرسال';
            resendBtn.disabled = false;
        }
    }, 1000);

    // ── Input handling ─────────────────────────────────────────────────────────
    boxes.forEach((box, i) => {
        box.addEventListener('input', e => {
            const v = e.target.value.replace(/\D/g, '').slice(-1);
            e.target.value = v;
            clearError();
            if (v) {
                e.target.classList.add('filled');
                if (i < 5) boxes[i + 1].focus();
                if (boxes.every(b => b.value)) submit();
            } else {
                e.target.classList.remove('filled');
            }
        });

        box.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !box.value && i > 0) {
                boxes[i - 1].value = '';
                boxes[i - 1].classList.remove('filled');
                boxes[i - 1].focus();
            }
        });

        box.addEventListener('paste', e => {
            e.preventDefault();
            const text = (e.clipboardData || window.clipboardData)
                .getData('text').replace(/\D/g, '').slice(0, 6);
            text.split('').forEach((ch, idx) => {
                if (boxes[idx]) { boxes[idx].value = ch; boxes[idx].classList.add('filled'); }
            });
            if (text.length === 6) submit();
        });
    });

    function clearError() {
        errorMsg.textContent = '';
        boxes.forEach(b => b.classList.remove('error'));
    }

    function setLoading(on) {
        spinner.classList.toggle('hidden', !on);
        boxes.forEach(b => b.disabled = on);
        resendBtn.disabled = on;
    }

    // ── Submit ─────────────────────────────────────────────────────────────────
    async function submit() {
        const otp = boxes.map(b => b.value).join('');
        if (otp.length < 6) return;
        setLoading(true);
        try {
            const res  = await fetch(API_BASE + '/api/auth/verify-otp', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body:    JSON.stringify({ email: EMAIL, otp, session_token: SESSION_TOKEN }),
            });
            const data = await res.json();
            if (res.ok && data.status === 'success') {
                setLoading(false);
                document.getElementById('boxes').classList.add('hidden');
                resendSec.classList.add('hidden');
                successMsg.classList.remove('hidden');
                // Redirect to app scheme so Android closes the Custom Tab and brings the app to foreground.
                // On iOS the app's polling will already have dismissed the browser before this fires.
                setTimeout(() => { window.location.href = 'quranicclinic://'; }, 1500);
            } else {
                throw new Error(data.error || 'invalid_otp');
            }
        } catch (err) {
            setLoading(false);
            errorMsg.textContent = err.message === 'too_many_requests'
                ? 'لقد تجاوزت الحد المسموح. حاول لاحقاً.'
                : 'الرمز غير صحيح. حاول مجدداً.';
            boxes.forEach(b => { b.value = ''; b.classList.remove('filled'); b.classList.add('error'); });
            boxes[0].focus();
        }
    }

    // ── Resend ─────────────────────────────────────────────────────────────────
    resendBtn.addEventListener('click', async () => {
        resendBtn.disabled = true;
        try {
            await fetch(API_BASE + '/api/auth/resend-otp', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body:    JSON.stringify({ email: EMAIL }),
            });
        } catch (_) {}
        let s = 60;
        countdownEl.textContent = s;
        resendBtn.innerHTML = 'إعادة إرسال (<span id="countdown">' + s + '</span>)';
        const t = setInterval(() => {
            s--;
            const el = document.getElementById('countdown');
            if (el) el.textContent = s;
            if (s <= 0) { clearInterval(t); resendBtn.innerHTML = 'إعادة إرسال'; resendBtn.disabled = false; }
        }, 1000);
    });
</script>
</body>
</html>
