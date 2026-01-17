<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Login - GoServePH</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, rgba(240, 240, 240, 0.4) 0%, rgba(230, 230, 230, 0.4) 50%, rgba(220, 220, 220, 0.3) 100%);
            position: relative;
            min-height: 100vh;
            overflow-x: hidden;
        }

        body::after {
            content: '';
            position: fixed;
            top: 72px;
            left: 0;
            width: 100%;
            height: calc(100% - 72px);
            background: url('img/gsmbg.png') center/cover no-repeat;
            opacity: 0.08;
            pointer-events: none;
            z-index: -2;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background:
                radial-gradient(circle at 20% 80%, rgba(76, 175, 80, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(74, 144, 226, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(253, 168, 17, 0.08) 0%, transparent 55%);
            animation: backgroundFloat 18s ease-in-out infinite;
            z-index: -3;
        }

        @keyframes backgroundFloat {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-18px) rotate(1deg);
            }
        }

        header {
            background: rgba(255, 255, 255, 0.92);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
            backdrop-filter: blur(18px);
            border-bottom: 3px solid #FDA811;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        header::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.35), transparent);
            animation: shimmer 3s ease-in-out infinite;
        }

        @keyframes shimmer {
            0% {
                left: -100%;
            }

            100% {
                left: 100%;
            }
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.86);
            border: 1px solid rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(22px);
            box-shadow: 0 30px 70px rgba(15, 23, 42, 0.18);
            border-radius: 28px;
        }

        .otp-input {
            width: 52px;
            height: 58px;
            text-align: center;
            font-size: 1.25rem;
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
        }

        .otp-input:focus {
            outline: none;
            border-color: #4A90E2;
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.28);
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            z-index: 1000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
        }

        .notification.show {
            transform: translateX(0);
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="min-h-screen flex flex-col">
    <header class="relative py-4 px-6 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <img src="img/GSM_logo.png" alt="GoServePH" class="h-12 w-12 rounded-full shadow-md">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">GoServePH</h1>
                <p class="text-xs font-semibold text-blue-500">Government Services Management System</p>
            </div>
        </div>
        <div class="text-sm text-slate-600 font-medium" id="currentDateTime"></div>
    </header>

    <div class="notification hidden" id="toast"></div>

    <main class="flex-1 flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-6xl grid lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <h2 class="text-4xl md:text-5xl font-bold text-slate-800 leading-tight">
                    Seamless <span class="text-blue-600">OTP Login</span> for faster access
                </h2>
                <p class="text-lg text-slate-600 max-w-xl">
                    Securely access GoServePH using a one-time password delivered straight to your email. No password to
                    remember—just a quick verification step to keep your account safe.
                </p>
                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                    <span class="inline-flex items-center gap-2 bg-white/80 rounded-full px-4 py-2 shadow-sm">
                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                        Email-based Verification
                    </span>
                    <span class="inline-flex items-center gap-2 bg-white/80 rounded-full px-4 py-2 shadow-sm">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        10-minute OTP Validity
                    </span>
                    <span class="inline-flex items-center gap-2 bg-white/80 rounded-full px-4 py-2 shadow-sm">
                        <span class="w-2 h-2 bg-amber-500 rounded-full"></span>
                        Secure Account Recovery
                    </span>
                </div>
            </div>

            <div class="space-y-6">
                <div class="glass-card p-8 space-y-6" id="emailCard">
                    <div class="rounded-xl border border-slate-200 bg-blue-50/80 p-4 text-left space-y-2">
                        <div class="flex items-center gap-3 text-blue-700 font-semibold">
                            <i class="fas fa-circle-info"></i>
                            <span>How OTP Login Works</span>
                        </div>
                        <ul class="text-sm text-slate-600 pl-6 list-disc space-y-1">
                            <li>Enter your registered email address.</li>
                            <li>Receive a 6-digit code via email.</li>
                            <li>Enter the code within 10 minutes.</li>
                            <li>Access your account securely.</li>
                        </ul>
                    </div>
                    <form id="emailForm" class="space-y-5 text-left">
                        <label class="block text-sm font-medium text-slate-600" for="otpEmail">Email Address</label>
                        <input id="otpEmail" type="email" required placeholder="Enter your email address"
                            class="w-full rounded-xl border border-slate-200 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-500">
                        <button type="submit" id="sendOtpBtn"
                            class="w-full flex items-center justify-center gap-2 bg-blue-600 text-white font-semibold py-3 rounded-xl hover:bg-blue-700 transition">
                            <span class="button-icon"><i class="fas fa-paper-plane"></i></span>
                            <span>Send OTP to Email</span>
                        </button>
                    </form>
                    <div class="text-sm text-slate-500 flex items-center gap-2 justify-center">
                        <span>Or</span>
                        <a href="index.php" class="text-blue-600 font-semibold hover:underline">Login with Password</a>
                    </div>
                    <div class="text-sm text-slate-400">
                        Don't have an account? <a href="index.php#register"
                            class="text-blue-600 font-semibold hover:underline">Create one here</a>
                    </div>
                    <div>
                        <a href="index.php"
                            class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-600">
                            <i class="fas fa-arrow-left"></i> Back loginll
                        </a>
                    </div>
                </div>

                <div class="glass-card p-8 space-y-6 hidden" id="otpCard">
                    <div class="space-y-2 text-left">
                        <h2 class="text-xl font-semibold text-slate-800">Enter the 6-digit OTP</h2>
                        <p class="text-sm text-slate-500">We sent a verification code to <span id="otpEmailDisplay"
                                class="font-semibold text-slate-700"></span>. The code expires in <span id="otpTimer"
                                class="font-semibold">02:00</span>.</p>
                    </div>
                    <form id="otpForm" class="space-y-5">
                        <div class="flex justify-center gap-3" id="otpInputs"></div>
                        <p id="otpError" class="hidden text-sm text-red-500 text-center"></p>
                        <button type="submit" id="verifyOtpBtn"
                            class="w-full flex items-center justify-center gap-2 bg-blue-600 text-white font-semibold py-3 rounded-xl hover:bg-blue-700 transition">
                            <span class="button-icon"><i class="fas fa-lock"></i></span>
                            <span>Verify & Login</span>
                        </button>
                    </form>
                    <div class="flex items-center justify-between text-sm text-slate-500">
                        <button type="button" id="resendOtpBtn"
                            class="text-blue-600 font-semibold hover:underline disabled:opacity-40" disabled>Resend
                            OTP</button>
                        <button type="button" id="changeEmailBtn" class="text-slate-500 hover:text-slate-700">Change
                            email</button>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <footer class="px-6 pb-6 text-center text-xs text-slate-400">
        © <?php echo date('Y'); ?> GoServePH. All rights reserved.
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" defer></script>
    <script>
        const emailForm = document.getElementById('emailForm');
        const emailInput = document.getElementById('otpEmail');
        const emailCard = document.getElementById('emailCard');
        const otpCard = document.getElementById('otpCard');
        const otpInputsWrapper = document.getElementById('otpInputs');
        const otpForm = document.getElementById('otpForm');
        const otpError = document.getElementById('otpError');
        const resendBtn = document.getElementById('resendOtpBtn');
        const changeEmailBtn = document.getElementById('changeEmailBtn');
        const otpEmailDisplay = document.getElementById('otpEmailDisplay');
        const otpTimer = document.getElementById('otpTimer');
        const toast = document.getElementById('toast');
        const sendOtpBtn = document.getElementById('sendOtpBtn');
        const verifyOtpBtn = document.getElementById('verifyOtpBtn');

        let countdownInterval = null;
        let currentEmail = '';
        const OTP_LENGTH = 6;

        initializeOtpInputs();

        function initializeOtpInputs() {
            otpInputsWrapper.innerHTML = '';
            for (let i = 0; i < OTP_LENGTH; i++) {
                const input = document.createElement('input');
                input.type = 'text';
                input.maxLength = 1;
                input.className = 'otp-input';
                input.inputMode = 'numeric';
                input.pattern = '[0-9]*';
                input.addEventListener('input', handleOtpInput);
                input.addEventListener('keydown', handleOtpKeydown);
                otpInputsWrapper.appendChild(input);
            }
        }

        function handleOtpInput(event) {
            const input = event.target;
            input.value = input.value.replace(/[^0-9]/g, '').slice(0, 1);
            if (input.value && input.nextElementSibling) {
                input.nextElementSibling.focus();
            }
            hideOtpError();

            // Auto-submit if all fields are filled
            const code = collectOtp();
            if (code.length === OTP_LENGTH) {
                input.blur(); // Remove focus
                verifyOtp(code);
            }
        }

        function handleOtpKeydown(event) {
            const input = event.target;
            if (event.key === 'Backspace' && !input.value && input.previousElementSibling) {
                input.previousElementSibling.focus();
                input.previousElementSibling.value = '';
            }
        }

        function showToast(message, type = 'info') {
            toast.textContent = message;
            toast.className = 'notification show';
            toast.classList.add(type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500');
            toast.classList.remove('hidden');
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.classList.add('hidden'), 300);
            }, 2600);
        }

        function setLoading(button, isLoading) {
            if (!button) return;
            if (isLoading) {
                button.dataset.originalText = button.innerHTML;
                button.innerHTML = '<span class="loading-spinner"></span> Processing...';
                button.disabled = true;
            } else if (button.dataset.originalText) {
                button.innerHTML = button.dataset.originalText;
                button.disabled = false;
            }
        }

        emailForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            if (!emailInput.value || !emailInput.checkValidity()) {
                showToast('Please enter a valid email address.', 'error');
                return;
            }
            await requestOtp(emailInput.value.trim());
        });

        resendBtn.addEventListener('click', async () => {
            if (!currentEmail) return;
            await requestOtp(currentEmail, true);
        });

        changeEmailBtn.addEventListener('click', () => {
            stopTimer();
            otpForm.reset();
            initializeOtpInputs();
            emailCard.classList.remove('hidden');
            otpCard.classList.add('hidden');
            emailInput.focus();
        });

        otpForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            const code = collectOtp();
            if (code.length !== OTP_LENGTH) {
                showOtpError('Please enter the 6-digit OTP.');
                return;
            }
            await verifyOtp(code);
        });

        function collectOtp() {
            return Array.from(otpInputsWrapper.querySelectorAll('input'))
                .map(input => input.value.trim())
                .join('');
        }

        async function requestOtp(email, isResend = false) {
            setLoading(sendOtpBtn, !isResend);
            setLoading(resendBtn, isResend);
            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ action: 'request_otp', email })
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to send OTP.');
                }

                currentEmail = email;
                otpEmailDisplay.textContent = email;
                emailCard.classList.add('hidden');
                otpCard.classList.remove('hidden');
                otpCard.scrollIntoView({ behavior: 'smooth' });
                initializeOtpInputs();
                otpInputsWrapper.querySelector('input').focus();
                hideOtpError();
                startTimer(data.data?.expires_in || 120);
                showToast(isResend ? 'A new OTP has been sent.' : 'OTP sent successfully.', 'success');
            } catch (error) {
                console.error(error);
                showToast(error.message || 'Failed to send OTP.', 'error');
            } finally {
                setLoading(sendOtpBtn, false);
                setLoading(resendBtn, false);
            }
        }

        async function verifyOtp(code) {
            setLoading(verifyOtpBtn, true);
            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    credentials: 'same-origin',
                    body: JSON.stringify({ action: 'verify_otp', email: currentEmail, otp: code })
                });
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'OTP verification failed.');
                }

                showToast('OTP verified! Redirecting...', 'success');
                setTimeout(() => {
                    window.location.href = data.data?.redirect || 'website.php';
                }, 800);
            } catch (error) {
                console.error(error);
                showOtpError(error.message || 'OTP verification failed.');
            } finally {
                setLoading(verifyOtpBtn, false);
            }
        }

        function startTimer(seconds) {
            stopTimer();
            updateTimerDisplay(seconds);
            resendBtn.disabled = true;
            countdownInterval = setInterval(() => {
                seconds--;
                updateTimerDisplay(seconds);
                if (seconds <= 0) {
                    stopTimer();
                    resendBtn.disabled = false;
                    showOtpError('OTP expired. Please request a new code.');
                }
            }, 1000);
        }

        function stopTimer() {
            if (countdownInterval) {
                clearInterval(countdownInterval);
                countdownInterval = null;
            }
        }

        function updateTimerDisplay(seconds) {
            const minutes = Math.max(0, Math.floor(seconds / 60));
            const secs = Math.max(0, seconds % 60);
            otpTimer.textContent = `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        function showOtpError(message) {
            otpError.textContent = message;
            otpError.classList.remove('hidden');
        }

        function hideOtpError() {
            otpError.classList.add('hidden');
        }
    </script>
</body>

</html>