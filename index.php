<?php
session_start();

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/database_connection.php';

$email = $password = $otp = "";
$emailErr = $passwordErr = $otpErr = "";

// OTP Verification
if (isset($_SESSION['otp'])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['otp'])) {
        $otp = $_POST['otp'];
        if (empty($otp)) {
            $otpErr = "OTP is required";
        } elseif ($otp == $_SESSION['otp']) {
            // OTP correct, login successful
            $db_acc_type = $_SESSION['account_type'];
            $email = $_SESSION['email'];

            // SYNC WITH NEW RBAC SYSTEM
            try {
                require_once __DIR__ . '/include/database.php';
                $database = new Database();
                $pdo = $database->getConnection();
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name'] = $user['last_name'];
                    $_SESSION['email'] = $user['email'];
                }
            } catch (Exception $e) { /* Fallback to legacy */
            }

            unset($_SESSION['otp'], $_SESSION['account_type']);

            if ($db_acc_type == "1" || (isset($_SESSION['role']) && $_SESSION['role'] === 'citizen') || (isset($_SESSION['role']) && $_SESSION['role'] === 'health_worker')) {
                // Determine redirect based on role if available
                $redirect = (isset($_SESSION['role'])) ? Database::getRoleRedirect($_SESSION['role']) : 'citizen/citizen.php';
                echo "<script>window.location.href='$redirect';</script>";
            } else {
                echo "<script>window.location.href='index.php';</script>";
            }
            exit;
        } else {
            $otpErr = "Incorrect OTP";
        }
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Only process login if OTP is NOT set
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = $_POST["email"];
    }
    if (empty($_POST["password"])) {
        $passwordErr = "Password required";
    } else {
        $password = $_POST["password"];
    }

    if ($password && $email) {
        $check_email = mysqli_query($Connection, "SELECT * FROM login_table WHERE email = '" . mysqli_real_escape_string($Connection, $email) . "'");
        $check_email_row = mysqli_num_rows($check_email);
        if ($check_email_row > 0) {
            $row = mysqli_fetch_assoc($check_email);
            $db_pass = $row["password"];
            if ($password == $db_pass) {
                // Generate OTP and send to email
                $otp = rand(100000, 999999);
                $_SESSION['otp'] = $otp;
                $_SESSION['email'] = $email;
                $_SESSION['account_type'] = $row["account_type"];

                // Send OTP using simple mail function
                $to = $email;
                $subject = 'Your OTP Code';
                $message = "Your OTP code is: $otp";
                $headers = "From: " . Config::get('SMTP_FROM_EMAIL') . "\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                if (mail($to, $subject, $message, $headers)) {
                    // OTP sent successfully
                } else {
                    $otpErr = "OTP could not be sent. Please check your email configuration.";
                }
                // Show OTP form after sending
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                $passwordErr = "Incorrect password";
            }
        } else {
            $emailErr = "Your email is unregistered";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GoServePH</title>
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
            position: relative;
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

        .modal-overlay {
            background-color: rgba(0, 0, 0, 0.5);
        }

        /* Ensure reCAPTCHA badge doesn't float or follow if fixed */
        .grecaptcha-badge {
            position: fixed !important;
            bottom: 10px !important;
            right: 10px !important;
            z-index: 1000 !important;
        }

        .modal-card {
            background: rgba(255, 255, 255, 0.97);
            border-radius: 28px;
            box-shadow: 0 35px 80px rgba(15, 23, 42, 0.22);
        }

        .toggle-password {
            line-height: 0;
        }

        .toggle-password:focus {
            outline: none;
            box-shadow: none;
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

        .req-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.85rem;
            color: #64748b;
        }

        .req-dot {
            width: 10px;
            height: 10px;
            border-radius: 9999px;
            background: #e2e8f0;
            box-shadow: inset 0 0 0 1px #cbd5f5;
        }

        .req-item.met .req-dot {
            background: #22c55e;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.25);
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

        .loading {
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

        input:focus {
            transform: translateY(-2px) scale(1.01);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        input::placeholder {
            color: rgba(71, 85, 105, 0.5);
            transition: all 0.25s ease;
        }

        input:focus::placeholder {
            transform: translateY(-7px);
            opacity: 0;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
        }

        .strength-meter {
            height: 8px;
            border-radius: 9999px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .strength-meter-bar {
            height: 100%;
            width: 0;
            background: #ef4444;
            transition: width .25s ease, background-color .25s ease;
        }

        .help-text {
            font-size: 0.75rem;
            color: #64748b;
        }
    </style>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
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

    <main class="flex-1 flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-6xl grid lg:grid-cols-2 gap-12 items-center">
            <div class="space-y-6">
                <h2 class="text-4xl md:text-5xl font-bold text-slate-800 leading-tight">
                    Abot-Kamay mo ang <span class="text-blue-600">Serbisyong Publiko</span>!
                </h2>
                <p class="text-lg text-slate-600 max-w-xl">
                    GoServePH connects citizens with vital government health and sanitation services. Manage
                    appointments, monitor requests, and stay informed with ease.
                </p>
                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500">
                    <span class="inline-flex items-center gap-2 bg-white/80 rounded-full px-4 py-2 shadow-sm">
                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                        Secure & Unified Access
                    </span>
                    <span class="inline-flex items-center gap-2 bg-white/80 rounded-full px-4 py-2 shadow-sm">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        Citizen-Centered Platform
                    </span>
                </div>
            </div>

            <div class="glass-card p-1" id="mainCardWrapper">
                <div class="bg-white/95 rounded-3xl p-8 md:p-10 space-y-6">
                    <div class="text-center space-y-2">
                        <span
                            class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-br from-blue-500 to-green-500 text-white shadow-lg">
                            <i class="fas fa-user-shield text-xl"></i>
                        </span>
                        <h3 class="text-3xl font-bold text-slate-800">Welcome</h3>
                        <p class="text-slate-500">Sign in to continue managing your services</p>
                    </div>

                    <form id="loginForm" class="space-y-5">
                        <div class="space-y-4">
                            <div>
                                <label for="email" class="block text-sm font-medium text-slate-700 mb-2">Email
                                    Address</label>
                                <input type="email" id="email" name="email" placeholder="name@example.com" required
                                    class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label for="password"
                                    class="block text-sm font-medium text-slate-700 mb-2">Password</label>
                                <div class="relative">
                                    <input type="password" id="password" name="password"
                                        placeholder="Enter your password" required
                                        class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <button type="button"
                                        class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 toggle-password"
                                        data-target="password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 text-sm text-slate-600">
                            <label class="inline-flex items-center space-x-2">
                                <input type="checkbox"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                <span>Remember me</span>
                            </label>
                            <button type="button" data-no-loading
                                class="text-blue-600 font-medium hover:text-blue-500 transition" id="openTerms">Terms of
                                Service</button>
                        </div>

                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2">
                            <i class="fas fa-sign-in-alt"></i>
                            Sign In
                        </button>

                        <div class="flex items-center">
                            <span class="flex-1 h-px bg-slate-200"></span>
                            <span class="px-4 text-xs font-semibold text-slate-400">OR CONTINUE WITH</span>
                            <span class="flex-1 h-px bg-slate-200"></span>
                        </div>

                        <div class="flex flex-col gap-3">
                            <button type="button"
                                class="social-btn w-full bg-white border border-slate-200 hover:bg-slate-50 text-slate-600 font-semibold py-3 rounded-xl transition flex items-center justify-center gap-2"
                                data-no-loading>
                                <i class="fab fa-google text-red-500"></i>
                                Google
                            </button>
                        </div>

                        <div class="text-center text-sm text-slate-600">
                            Don't have an account?
                            <button type="button" id="showRegister"
                                class="text-blue-600 font-semibold hover:text-blue-500 ml-1" data-no-loading>Register
                                now</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-green-600 text-white py-4 px-6 flex flex-wrap items-center justify-between gap-4">
        <div class="text-sm">
            <p class="font-semibold">Government Services Management System</p>
            <p class="text-xs">For any inquiries, please call 123 or email helpdesk@gov.ph</p>
        </div>
        <div class="flex items-center gap-4 text-xs font-medium">
            <button id="footerTerms" class="hover:underline" data-no-loading>TERMS OF SERVICE</button>
            <button id="footerPrivacy" class="hover:underline" data-no-loading>PRIVACY POLICY</button>
            <div class="flex items-center gap-2">
                <a href="#"
                    class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center hover:bg-blue-700 transition"
                    data-no-loading>
                    <i class="fab fa-facebook-f"></i>
                </a>
                <a href="#"
                    class="w-8 h-8 bg-sky-500 rounded-full flex items-center justify-center hover:bg-sky-600 transition"
                    data-no-loading>
                    <i class="fab fa-twitter"></i>
                </a>
            </div>
        </div>
    </footer>

    <div id="registerFormContainer"
        class="modal-overlay fixed inset-0 hidden items-center justify-center px-4 py-8 z-[9999]">
        <div class="modal-card max-w-5xl lg:w-11/12 w-full p-6 md:p-8 relative max-h-[92vh] overflow-hidden">
            <button id="cancelRegister" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600"
                data-no-loading>
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="grid lg:grid-cols-5 gap-8">
                <div class="space-y-3 lg:col-span-2 hidden md:block">
                    <h3 class="text-2xl font-bold text-slate-800">Create your GoServePH account</h3>
                    <p class="text-sm text-slate-500">Access health and sanitation services tailored for citizens,
                        administrators, and officials.</p>
                    <div class="bg-blue-50 border border-blue-100 rounded-2xl p-4 space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="mt-1 text-blue-500"><i class="fas fa-id-badge"></i></span>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Unified profile management</p>
                                <p class="text-xs text-slate-500">Track service requests, appointments, and approvals in
                                    one place.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="mt-1 text-green-500"><i class="fas fa-lock"></i></span>
                            <div>
                                <p class="text-sm font-semibold text-slate-700">Secure and verified access</p>
                                <p class="text-xs text-slate-500">Multi-step verification keeps your information
                                    protected.</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Password requirements
                        </h4>
                        <div id="pwdChecklist" class="mt-3 space-y-2">
                            <div class="req-item" data-check="length"><span class="req-dot"></span> At least 10
                                characters</div>
                            <div class="req-item" data-check="upper"><span class="req-dot"></span> One uppercase letter
                            </div>
                            <div class="req-item" data-check="lower"><span class="req-dot"></span> One lowercase letter
                            </div>
                            <div class="req-item" data-check="number"><span class="req-dot"></span> One number</div>
                            <div class="req-item" data-check="special"><span class="req-dot"></span> One special
                                character</div>
                        </div>
                    </div>
                </div>

                <form id="registerForm"
                    class="space-y-6 lg:col-span-3 max-h-[75vh] overflow-y-auto pr-1 lg:pl-6 lg:border-l border-slate-200">
                    <div class="bg-white pb-3 z-10">
                        <h3 class="text-xl font-semibold text-slate-800">Create Account</h3>
                        <p class="text-xs text-slate-500">Fill in your details to continue.</p>
                    </div>
                    <h4 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Personal Information</h4>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">First Name<span
                                    class="required-asterisk">*</span></label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-user"></i></span>
                                <input type="text" name="firstName" required
                                    class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Last Name<span
                                    class="required-asterisk">*</span></label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-user"></i></span>
                                <input type="text" name="lastName" required
                                    class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="text-sm font-medium text-slate-700">Middle Name<span id="middleAsterisk"
                                    class="required-asterisk">*</span></label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-id-card"></i></span>
                                <input type="text" name="middleName" id="middleName" required
                                    class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                            </div>
                            <label class="inline-flex items-center gap-2 mt-2 text-xs text-slate-500">
                                <input type="checkbox" id="noMiddleName"
                                    class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                No middle name
                            </label>
                        </div>
                    </div>

                    <h4 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Contact Information</h4>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Email Address<span
                                    class="required-asterisk">*</span></label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="regEmail" required
                                    class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500"
                                    placeholder="name@example.com">
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Contact Number<span
                                    class="required-asterisk">*</span></label>
                            <div class="input-group">
                                <span class="input-icon"><i class="fas fa-phone"></i></span>
                                <input type="tel" name="contact" required maxlength="11" pattern="^[0-9]{11}$"
                                    class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500"
                                    placeholder="09XXXXXXXXX">
                            </div>
                            <p class="help-text mt-1">Philippine mobile number, 11 digits</p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-slate-700">Address<span
                                class="required-asterisk">*</span></label>
                        <div class="input-group">
                            <span class="input-icon"><i class="fas fa-location-dot"></i></span>
                            <input type="text" name="address" required
                                class="w-full pl-11 pr-4 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500"
                                placeholder="Street, Barangay, City">
                        </div>
                    </div>

                    <h4 class="text-sm font-semibold text-slate-600 uppercase tracking-wide">Security</h4>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-slate-700">Password<span
                                    class="required-asterisk">*</span></label>
                            <div class="relative input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="regPassword" name="regPassword" required
                                    class="w-full pl-11 pr-10 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                                <button type="button"
                                    class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 toggle-password"
                                    data-target="regPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <div class="strength-meter">
                                    <div id="strengthBar" class="strength-meter-bar"></div>
                                </div>
                                <div id="strengthLabel" class="help-text mt-1"></div>
                            </div>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-slate-700">Confirm Password<span
                                    class="required-asterisk">*</span></label>
                            <div class="relative input-group">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" id="confirmPassword" name="confirmPassword" required
                                    class="w-full pl-11 pr-10 py-3 border border-slate-200 rounded-xl focus:ring-2 focus:ring-blue-500">
                                <button type="button"
                                    class="absolute inset-y-0 right-3 flex items-center text-slate-400 hover:text-slate-600 toggle-password"
                                    data-target="confirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl inline-block">
                        <div class="g-recaptcha" data-sitekey="6LcLUecrAAAAAEGK_y-eVpjwQehUA6PxdO8-4JbZ"></div>
                    </div>

                    <div class="space-y-2 text-xs text-slate-500">
                        <label class="flex items-start gap-2">
                            <input type="checkbox" id="agreeTerms"
                                class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>I agree to the <button type="button" id="openTermsInline"
                                    class="text-blue-600 font-semibold" data-no-loading>Terms of
                                    Service</button>.</span>
                        </label>
                        <label class="flex items-start gap-2">
                            <input type="checkbox" id="agreePrivacy"
                                class="mt-1 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            <span>I agree to the <button type="button" id="openPrivacy"
                                    class="text-blue-600 font-semibold" data-no-loading>Privacy Policy</button>.</span>
                        </label>
                    </div>

                    <div class="mt-6 bg-white pt-3 border-t border-slate-200">
                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition"
                            data-no-loading>Create Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="termsModal"
        class="modal-overlay fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-[10001]">
        <div class="modal-card max-w-3xl w-full max-h-[80vh] overflow-y-auto">
            <div class="bg-white border-b px-6 py-4 flex items-center justify-center">
                <h3 class="text-lg font-semibold text-slate-800">GoServePH Terms of Service Agreement</h3>
            </div>
            <div class="px-6 py-4 space-y-4 text-sm leading-6 text-slate-600">
                <p><strong>Welcome to GoServePH!</strong></p>
                <p>This GoServePH Services Agreement ("Agreement") is a binding legal contract for the use of our
                    software systems—which handle data input, monitoring, processing, and analytics—("Services") between
                    GoServePH ("us," "our," or "we") and you, the registered user ("you" or "user").</p>
                <p>This Agreement details the terms and conditions for using our Services. By accessing or using any
                    GoServePH Services, you agree to these terms. If you don't understand any part of this Agreement,
                    please contact us at info@goserveph.com.</p>
                <h4 class="font-semibold text-slate-700">OVERVIEW OF THIS AGREEMENT</h4>
                <p>This document outlines the terms for your use of the GoServePH system:</p>
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr>
                            <th class="py-1 pr-4">Section</th>
                            <th class="py-1">Topic</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-1 pr-4">Section A</td>
                            <td class="py-1">General Account Setup and Use</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4">Section B</td>
                            <td class="py-1">Technology, Intellectual Property, and Licensing</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4">Section C</td>
                            <td class="py-1">Payment Terms, Fees, and Billing</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4">Section D</td>
                            <td class="py-1">Data Usage, Privacy, and Security</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4">Section E</td>
                            <td class="py-1">Additional Legal Terms and Disclaimers</td>
                        </tr>
                    </tbody>
                </table>
                <p class="text-xs text-slate-500 italic">For brevity, the full terms content is available. Users must
                    read and agree to continue.</p>
            </div>
            <div class="border-t px-6 py-3 flex justify-end">
                <button type="button" id="agreeTermsBtn"
                    class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold"
                    data-no-loading>Agree</button>
            </div>
        </div>
    </div>

    <div id="privacyModal"
        class="modal-overlay fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-[10001]">
        <div class="modal-card max-w-3xl w-full max-h-[80vh] overflow-y-auto">
            <div class="bg-white border-b px-6 py-4 flex items-center justify-center">
                <h3 class="text-lg font-semibold text-slate-800">GoServePH Data Privacy Policy</h3>
            </div>
            <div class="px-6 py-4 space-y-4 text-sm leading-6 text-slate-600">
                <p><strong>Protecting the information you and your users handle through our system is our highest
                        priority.</strong> This policy outlines how GoServePH manages, secures, and uses your data.</p>
                <h4 class="font-semibold text-slate-700">1. How We Define and Use Data</h4>
                <p>In this policy, we define the types of data that flow through the GoServePH system:</p>
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr>
                            <th class="py-1 pr-4">Term</th>
                            <th class="py-1">Definition</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-1 pr-4">Personal Data</td>
                            <td class="py-1">Any information that can identify a specific person, whether directly or
                                indirectly, shared or accessible through the Services.</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4">User Data</td>
                            <td class="py-1">Information that describes your business operations, services, or internal
                                activities.</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4">GoServePH Data</td>
                            <td class="py-1">Details about transactions and activity on our platform, information used
                                for fraud detection, aggregated data, and other information originating from the
                                Services.</td>
                        </tr>
                        <tr>
                            <td class="py-1 pr-4">DATA</td>
                            <td class="py-1">Used broadly to refer to all the above: Personal Data, User Data, and
                                GoServePH Data.</td>
                        </tr>
                    </tbody>
                </table>
                <h4 class="font-semibold text-slate-700">Our Commitment to Data Use</h4>
                <p>We analyze and manage data only for the following critical purposes:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>To provide, maintain, and improve the GoServePH Services for you and all other users.</li>
                    <li>To detect and mitigate fraud, financial loss, or other harm to you or other users.</li>
                    <li>To develop and enhance our products, systems, and tools.</li>
                </ul>
                <p>We will not sell or share Personal Data with unaffiliated parties for their marketing purposes. By
                    using our system, you consent to our use of your Data in this manner.</p>
                <h4 class="font-semibold text-slate-700">2. Data Protection and Compliance</h4>
                <p><strong>Confidentiality</strong></p>
                <p>We commit to using Data only as permitted by our agreement or as specifically directed by you. You,
                    in turn, must protect all Data you access through GoServePH and use it only in connection with our
                    Services. Neither party may use Personal Data to market to third parties without explicit consent.
                </p>
                <p>We will only disclose Data when legally required to do so, such as through a subpoena, court order,
                    or search warrant.</p>
                <p><strong>Privacy Compliance and Responsibilities</strong></p>
                <p><em>Your Legal Duty:</em> You affirm that you are, and will remain, compliant with all applicable
                    Philippine laws (including the Data Privacy Act of 2012) governing the collection, protection, and
                    use of the Data you provide to us.</p>
                <p><em>Consent:</em> You are responsible for obtaining all necessary rights and consents from your
                    End-Users to allow us to collect, use, and store their Personal Data.</p>
                <p><em>End-User Disclosure:</em> You must clearly inform your End-Users that GoServePH processes
                    transactions for you and may receive their Personal Data as part of that process.</p>
                <p><strong>Data Processing Roles</strong></p>
                <p>When we process Personal Data on your behalf, we operate under the following legal roles:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>You are the Data Controller (you determine why and how the data is processed).</li>
                    <li>We are the Data Intermediary (we process data strictly according to your instructions).</li>
                </ul>
                <p>As the Data Intermediary, we commit to:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Implementing appropriate security measures to protect the Personal Data we process.</li>
                    <li>Not retaining Personal Data longer than necessary to fulfill the purposes set out in our
                        agreement.</li>
                </ul>
                <p>You acknowledge that we rely entirely on your instructions. Therefore, we are not liable for any
                    claims resulting from our actions that were based directly or indirectly on your instructions.</p>
                <p><strong>Prohibited Activities</strong></p>
                <p>You are strictly prohibited from data mining the GoServePH database or any portion of it without our
                    express written permission.</p>
                <p><strong>Breach Notification</strong></p>
                <p>If we become aware of an unauthorized acquisition, disclosure, change, or loss of Personal Data on
                    our systems (a "Breach"), we will notify you and provide sufficient information to help you mitigate
                    any negative impact, consistent with our legal obligations.</p>
                <h4 class="font-semibold text-slate-700">3. Account Deactivation and Data Deletion</h4>
                <p><strong>Initiating Deactivation</strong></p>
                <p>If you wish to remove your personal information from our systems, you must go to your Edit Profile
                    page and click the 'Deactivate Account' button. This action initiates the data deletion and account
                    deactivation process.</p>
                <p><strong>Data Retention</strong></p>
                <p>Upon deactivation, all of your Personal Identifying Information will be deleted from our systems.</p>
                <p><em>Important Note:</em> Due to the nature of our role as a Government Services Management System,
                    and for legal, accounting, and audit purposes, we are required to retain some of your non-personal
                    account activity history and transactional records. You will receive a confirmation email once your
                    request has been fully processed.</p>
                <h4 class="font-semibold text-slate-700">4. Security Controls and Responsibilities</h4>
                <p><strong>Our Security</strong></p>
                <p>We are responsible for implementing commercially reasonable administrative, technical, and physical
                    procedures to protect Data from unauthorized access, loss, or modification. We comply with all
                    applicable Laws in handling Data.</p>
                <p><strong>Your Security Controls</strong></p>
                <p>You acknowledge that no security system is perfect. You agree to implement your own necessary
                    security measures ("Security Controls"), which must include:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Firewall and anti-virus systems.</li>
                    <li>Anti-phishing systems.</li>
                    <li>End-User and device management policies.</li>
                    <li>Data handling protocols.</li>
                </ul>
                <p>We reserve the right to suspend your Account or the Services if necessary to maintain system
                    integrity and security, or to prevent harm. You waive any right to claim losses that result from a
                    Breach or any action we take to prevent harm.</p>
                </thead>
                <tbody>
                    <tr>
                        <td class="py-1 pr-4">Personal Data</td>
                        <td class="py-1">Any information that can identify a specific person, whether directly or
                            indirectly, shared or accessible through the Services.</td>
                    </tr>
                    <tr>
                        <td class="py-1 pr-4">User Data</td>
                        <td class="py-1">Information that describes your business operations, services, or internal
                            activities.</td>
                    </tr>
                    <tr>
                        <td class="py-1 pr-4">GoServePH Data</td>
                        <td class="py-1">Details about transactions and activity on our platform, information used for
                            fraud detection, aggregated data, and any non-personal information generated by our system.
                        </td>
                    </tr>
                    <tr>
                        <td class="py-1 pr-4">DATA</td>
                        <td class="py-1">Used broadly to refer to all the above: Personal Data, User Data, and GoServePH
                            Data.</td>
                    </tr>
                </tbody>
                </table>
                <h4 class="font-semibold text-slate-700">Our Commitment to Data Use</h4>
                <p>We analyze and manage data only for the following critical purposes:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>To provide, maintain, and improve the GoServePH Services for you and all other users.</li>
                    <li>To detect and mitigate fraud, financial loss, or other harm to you or other users.</li>
                    <li>To develop and enhance our products, systems, and tools.</li>
                </ul>
                <p>We will not sell or share Personal Data with unaffiliated parties for their marketing purposes. By
                    using our system, you consent to our use of your Data in this manner.</p>
                <h4 class="font-semibold text-slate-700">2. Data Protection and Compliance</h4>
                <p><strong>Confidentiality</strong></p>
                <p>We commit to using Data only as permitted by our agreement or as specifically directed by you. You,
                    in turn, must protect all Data you access through GoServePH and use it only in connection with our
                    Services. Neither party may use Personal Data to market to third parties without explicit consent.
                </p>
                <p>We will only disclose Data when legally required to do so, such as through a subpoena, court order,
                    or search warrant.</p>
                <p><strong>Privacy Compliance and Responsibilities</strong></p>
                <p><em>Your Legal Duty:</em> You affirm that you are, and will remain, compliant with all applicable
                    Philippine laws (including the Data Privacy Act of 2012) governing the collection, protection, and
                    use of the Data you provide to us.</p>
                <p><em>Consent:</em> You are responsible for obtaining all necessary rights and consents from your
                    End-Users to allow us to collect, use, and store their Personal Data.</p>
                <p><em>End-User Disclosure:</em> You must clearly inform your End-Users that GoServePH processes
                    transactions for you and may receive their Personal Data as part of that process.</p>
                <p><strong>Data Processing Roles</strong></p>
                <p>When we process Personal Data on your behalf, we operate under the following legal roles:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>You are the Data Controller (you determine why and how the data is processed).</li>
                    <li>We are the Data Intermediary (we process data strictly according to your instructions).</li>
                </ul>
                <p>As the Data Intermediary, we commit to:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Implementing appropriate security measures to protect the Personal Data we process.</li>
                    <li>Not retaining Personal Data longer than necessary to fulfill the purposes set out in our
                        agreement.</li>
                </ul>
                <p>You acknowledge that we rely entirely on your instructions. Therefore, we are not liable for any
                    claims resulting from our actions that were based directly or indirectly on your instructions.</p>
                <p><strong>Prohibited Activities</strong></p>
                <p>You are strictly prohibited from data mining the GoServePH database or any portion of it without our
                    express written permission.</p>
                <p><strong>Breach Notification</strong></p>
                <p>If we become aware of an unauthorized acquisition, disclosure, change, or loss of Personal Data on
                    our systems (a "Breach"), we will notify you and provide sufficient information to help you mitigate
                    any negative impact, consistent with our legal obligations.</p>
                <h4 class="font-semibold text-slate-700">3. Account Deactivation and Data Deletion</h4>
                <p><strong>Initiating Deactivation</strong></p>
                <p>If you wish to remove your personal information from our systems, you must go to your Edit Profile
                    page and click the 'Deactivate Account' button. This action initiates the data deletion and account
                    deactivation process.</p>
                <p><strong>Data Retention</strong></p>
                <p>Upon deactivation, all of your Personal Identifying Information will be deleted from our systems.</p>
                <p><em>Important Note:</em> Due to the nature of our role as a Government Services Management System,
                    and for legal, accounting, and audit purposes, we are required to retain some of your non-personal
                    account activity history and transactional records. You will receive a confirmation email once your
                    request has been fully processed.</p>
                <h4 class="font-semibold text-slate-700">4. Security Controls and Responsibilities</h4>
                <p><strong>Our Security</strong></p>
                <p>We are responsible for implementing commercially reasonable administrative, technical, and physical
                    procedures to protect Data from unauthorized access, loss, or modification. We comply with all
                    applicable Laws in handling Data.</p>
                <p><strong>Your Security Controls</strong></p>
                <p>You acknowledge that no security system is perfect. You agree to implement your own necessary
                    security measures ("Security Controls"), which must include:</p>
                <ul class="list-disc pl-5 space-y-1">
                    <li>Firewall and anti-virus systems.</li>
                    <li>Anti-phishing systems.</li>
                    <li>End-User and device management policies.</li>
                    <li>Data handling protocols.</li>
                </ul>
                <p>We reserve the right to suspend your Account or the Services if necessary to maintain system
                    integrity and security, or to prevent harm. You waive any right to claim losses that result from a
                    Breach or any action we take to prevent harm.</p>
            </div>
            <div class="border-t px-6 py-3 flex justify-end">
                <button type="button" id="agreePrivacyBtn"
                    class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold"
                    data-no-loading>Agree</button>
            </div>
        </div>
    </div>

    <div id="otpModal" class="modal-overlay fixed inset-0 hidden items-center justify-center px-4">
        <div class="modal-card max-w-md w-full p-8 relative">
            <button id="cancelOtp" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600" data-no-loading>
                <i class="fas fa-times text-xl"></i>
            </button>
            <div class="space-y-6 text-center">
                <div class="space-y-2">
                    <span
                        class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-100 text-blue-600">
                        <i class="fas fa-key"></i>
                    </span>
                    <h3 class="text-xl font-semibold text-slate-800">Enter One-Time Password</h3>
                    <p class="text-sm text-slate-500">A 6-digit verification code has been sent to your email.</p>
                </div>
                <form id="otpForm" class="space-y-5">
                    <div id="otpInputs" class="flex justify-center gap-3">
                        <input type="text" maxlength="1" class="otp-input" autocomplete="one-time-code">
                        <input type="text" maxlength="1" class="otp-input" autocomplete="one-time-code">
                        <input type="text" maxlength="1" class="otp-input" autocomplete="one-time-code">
                        <input type="text" maxlength="1" class="otp-input" autocomplete="one-time-code">
                        <input type="text" maxlength="1" class="otp-input" autocomplete="one-time-code">
                        <input type="text" maxlength="1" class="otp-input" autocomplete="one-time-code">
                    </div>
                    <p id="otpError" class="text-sm text-red-500 hidden"></p>
                    <div class="flex items-center justify-between text-sm text-slate-500">
                        <span>Code expires in <span id="otpTimer"
                                class="font-semibold text-slate-700">02:00</span></span>
                        <button type="button" id="resendOtp" class="text-blue-600 font-semibold disabled:opacity-40"
                            disabled data-no-loading>Resend OTP</button>
                    </div>
                    <button type="submit" id="submitOtp"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl transition"
                        data-no-loading>Verify</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Government Services Management System - Login Page JavaScript


        document.addEventListener('DOMContentLoaded', function () {
            // Initialize the page
            initializePage();

            // Set up event listeners
            setupEventListeners();

            // Update date and time
            updateDateTime();
            setInterval(updateDateTime, 1000);
        });


        function initializePage() {
            // Add loading animation to buttons
            addLoadingStates();

            // Initialize form validation
            initializeFormValidation();

            // Add smooth scrolling
            addSmoothScrolling();
        }


        function setupEventListeners() {
            // Login form submission
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', handleLoginSubmit);
            }

            // Social login buttons (only target real social buttons)
            const socialButtons = document.querySelectorAll('.social-btn');
            socialButtons.forEach(button => {
                button.addEventListener('click', handleSocialLogin);
            });

            // Email input validation
            const emailInput = document.getElementById('email');
            if (emailInput) {
                emailInput.addEventListener('blur', validateEmail);
                emailInput.addEventListener('input', clearEmailError);
            }

            // Password input validation
            const passwordInput = document.getElementById('password');
            if (passwordInput) {
                passwordInput.addEventListener('blur', validatePassword);
                passwordInput.addEventListener('input', clearPasswordError);
            }

            // Register toggle
            const showRegister = document.getElementById('showRegister');
            if (showRegister) {
                showRegister.addEventListener('click', showRegisterForm);
            }
            const cancelRegister = document.getElementById('cancelRegister');
            if (cancelRegister) {
                cancelRegister.addEventListener('click', hideRegisterForm);
            }
            const registerForm = document.getElementById('registerForm');
            if (registerForm) {
                registerForm.addEventListener('submit', handleRegisterSubmit);
            }
            const regPassword = document.getElementById('regPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            if (regPassword) {
                regPassword.addEventListener('input', function () {
                    validateRegPassword(this);
                    updatePasswordChecklist(this.value);
                    updatePasswordStrength(this.value);
                    const cp = document.getElementById('confirmPassword');
                    if (cp && cp.value) { validateConfirmPassword(true); }
                });
                regPassword.addEventListener('blur', function () {
                    validateRegPassword(this, true);
                    updatePasswordChecklist(this.value);
                    updatePasswordStrength(this.value);
                    const cp = document.getElementById('confirmPassword');
                    if (cp && cp.value) { validateConfirmPassword(true); }
                });
            }
            if (confirmPassword) {
                confirmPassword.addEventListener('input', function () { validateConfirmPassword(true); });
                confirmPassword.addEventListener('blur', function () { validateConfirmPassword(true); });
            }
            const contactInput = document.querySelector('input[name="contact"]');
            if (contactInput) {
                contactInput.addEventListener('input', formatPhone);
            }
            const toggles = document.querySelectorAll('.toggle-password');
            toggles.forEach(btn => {
                btn.addEventListener('click', () => {
                    const targetId = btn.getAttribute('data-target');
                    const input = document.getElementById(targetId);
                    if (!input) return;
                    const isPassword = input.type === 'password';
                    input.type = isPassword ? 'text' : 'password';
                    const icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-eye');
                        icon.classList.toggle('fa-eye-slash');
                    }
                });
            });
            const noMiddleName = document.getElementById('noMiddleName');
            if (noMiddleName) {
                noMiddleName.addEventListener('change', function () {
                    const middle = document.getElementById('middleName');
                    const asterisk = document.getElementById('middleAsterisk');
                    if (!middle) return;
                    middle.disabled = this.checked;
                    middle.required = !this.checked;
                    if (asterisk) {
                        asterisk.style.display = this.checked ? 'none' : 'inline';
                    }
                    if (this.checked) middle.value = '';
                });
            }


            // Terms modal wiring
            const openTerms = document.getElementById('openTerms');
            const openTermsInline = document.getElementById('openTermsInline');
            const footerTerms = document.getElementById('footerTerms');
            const termsModal = document.getElementById('termsModal');
            const agreeTermsCheckbox = document.getElementById('agreeTerms');
            const agreeTermsBtn = document.getElementById('agreeTermsBtn');

            const openPrivacy = document.getElementById('openPrivacy');
            const footerPrivacy = document.getElementById('footerPrivacy');
            const privacyModal = document.getElementById('privacyModal');
            const agreePrivacyCheckbox = document.getElementById('agreePrivacy');
            const agreePrivacyBtn = document.getElementById('agreePrivacyBtn');

            function showTerms() {
                if (!termsModal) return;
                termsModal.classList.remove('hidden');
                termsModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }
            function hideTerms() {
                if (!termsModal) return;
                termsModal.classList.add('hidden');
                termsModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }

            function showPrivacy() {
                if (!privacyModal) return;
                privacyModal.classList.remove('hidden');
                privacyModal.classList.add('flex');
                document.body.classList.add('overflow-hidden');
            }
            function hidePrivacy() {
                if (!privacyModal) return;
                privacyModal.classList.add('hidden');
                privacyModal.classList.remove('flex');
                document.body.classList.remove('overflow-hidden');
            }

            // Open modals when clicking text links
            if (openTerms) openTerms.addEventListener('click', showTerms);
            if (openTermsInline) openTermsInline.addEventListener('click', (e) => {
                e.preventDefault();
                showTerms();
            });
            if (footerTerms) footerTerms.addEventListener('click', showTerms);

            if (openPrivacy) openPrivacy.addEventListener('click', (e) => {
                e.preventDefault();
                showPrivacy();
            });
            if (footerPrivacy) footerPrivacy.addEventListener('click', showPrivacy);

            // Auto-open modals when checkboxes are clicked
            if (agreeTermsCheckbox) {
                agreeTermsCheckbox.addEventListener('change', function (e) {
                    if (this.checked) {
                        // Uncheck it first, will be re-checked when user clicks Agree
                        this.checked = false;
                        showTerms();
                    }
                });
            }

            if (agreePrivacyCheckbox) {
                agreePrivacyCheckbox.addEventListener('change', function (e) {
                    if (this.checked) {
                        // Uncheck it first, will be re-checked when user clicks Agree
                        this.checked = false;
                        showPrivacy();
                    }
                });
            }

            // Agree buttons check the checkbox and close the modal
            if (agreeTermsBtn) {
                agreeTermsBtn.addEventListener('click', function () {
                    if (agreeTermsCheckbox) {
                        agreeTermsCheckbox.checked = true;
                    }
                    hideTerms();
                });
            }

            if (agreePrivacyBtn) {
                agreePrivacyBtn.addEventListener('click', function () {
                    if (agreePrivacyCheckbox) {
                        agreePrivacyCheckbox.checked = true;
                    }
                    hidePrivacy();
                });
            }
        }


        function updateDateTime() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };

            const dateTimeString = now.toLocaleDateString('en-US', options).toUpperCase();
            const dateTimeElement = document.getElementById('currentDateTime');

            if (dateTimeElement) {
                dateTimeElement.textContent = dateTimeString;
            }
        }


        function addLoadingStates() {
            const socialButtons = document.querySelectorAll('button.social-btn');
            socialButtons.forEach(button => {
                button.addEventListener('click', function () {
                    if (this.hasAttribute('data-no-loading')) return;
                    showLoadingState(this);
                });
            });
        }


        function showLoadingState(button) {
            if (button.dataset.originalText === undefined) {
                button.dataset.originalText = button.innerHTML;
            }
            button.innerHTML = '<span class="loading"></span> Processing...';
            button.disabled = true;
        }


        function initializeFormValidation() {
            // Add real-time validation
            const inputs = document.querySelectorAll('input[required]');
            inputs.forEach(input => {
                input.addEventListener('input', function () {
                    validateField(this);
                });
            });
        }


        function validateField(field) {
            const value = field.value.trim();
            const fieldName = field.name;

            // Remove existing error styling
            field.classList.remove('border-red-500', 'ring-red-500');
            field.classList.add('border-slate-200', 'ring-blue-200');

            // Remove existing error message
            const existingError = field.parentNode.querySelector('.error-message');
            if (existingError) {
                existingError.remove();
            }

            if (fieldName === 'email' || fieldName === 'regEmail') {
                validateEmail(field);
            }
        }


        function validateEmail(input) {
            const email = input.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (email && !emailRegex.test(email)) {
                showFieldError(input, 'Please enter a valid email address');
                return false;
            }

            clearEmailError(input);
            return true;
        }


        function clearEmailError(input) {
            input.classList.remove('border-red-500', 'ring-red-500');
            input.classList.add('border-slate-200', 'ring-blue-200');

            const errorMessage = input.parentNode.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.remove();
            }
        }


        function validatePassword(input) {
            const password = input.value.trim();

            if (password && password.length < 6) {
                showFieldError(input, 'Password must be at least 6 characters long');
                return false;
            }

            clearPasswordError(input);
            return true;
        }


        function clearPasswordError(input) {
            input.classList.remove('border-red-500', 'ring-red-500');
            input.classList.add('border-slate-200', 'ring-blue-200');

            const errorMessage = input.parentNode.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.remove();
            }
        }


        function showFieldError(field, message) {
            field.classList.remove('border-slate-200', 'ring-blue-200');
            field.classList.add('border-red-500', 'ring-red-500');

            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-red-500 text-sm mt-1';
            errorDiv.textContent = message;

            field.parentNode.appendChild(errorDiv);
        }


        let pendingLoginEmail = '';

        async function handleLoginSubmit(event) {
            event.preventDefault();

            const form = event.target;
            const email = form.email.value.trim();
            const password = form.password.value.trim();

            if (!validateEmail(form.email)) {
                return;
            }

            if (!validatePassword(form.password)) {
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            showLoadingState(submitButton);

            try {
                const response = await makeAPICall('api/auth.php', {
                    action: 'login',
                    email: email,
                    password: password
                });

                if (response.success && response.data) {
                    if (response.data.otp_required) {
                        // OTP is required, show OTP modal
                        pendingLoginEmail = response.data.email || email;
                        showNotification('OTP sent to your email!', 'success');
                        resetButton(submitButton);
                        showOtpModal();
                        startOtpTimer(response.data.expires_in || 120);
                    } else {
                        // Direct login (fallback)
                        const redirectUrl = response.data.redirect || 'website.php';
                        showNotification('Login successful! Redirecting...', 'success');
                        resetButton(submitButton);
                        window.location.href = redirectUrl;
                    }
                } else {
                    const message = response.message || 'Invalid email or password.';
                    showNotification(message, 'error');
                    resetButton(submitButton);
                }
            } catch (error) {
                const message = (error && error.payload && error.payload.message)
                    ? error.payload.message
                    : (error && error.message) ? error.message : 'Network error. Please try again.';
                showNotification(message, 'error');
                resetButton(submitButton);
            }
        }


        function resetButton(button) {
            if (button.dataset.originalText !== undefined) {
                button.innerHTML = button.dataset.originalText;
            }
            button.disabled = false;
        }


        function handleSocialLogin(event) {
            event.preventDefault();
            window.location.href = 'login_otp.php';
        }


        function showRegisterForm() {
            const container = document.getElementById('registerFormContainer');
            const mainCard = document.querySelector('.glass-card');
            if (container && mainCard) {
                container.classList.remove('hidden');
                container.classList.add('flex');
                mainCard.classList.add('opacity-40');
                document.body.classList.add('overflow-hidden');
            }
        }


        function hideRegisterForm() {
            const container = document.getElementById('registerFormContainer');
            const mainCard = document.querySelector('.glass-card');
            if (container && mainCard) {
                container.classList.add('hidden');
                container.classList.remove('flex');
                mainCard.classList.remove('opacity-40');
                document.body.classList.remove('overflow-hidden');
            }
        }


        async function handleRegisterSubmit(event) {
            event.preventDefault();
            const form = event.target;
            const data = serializeForm(form);
            const regPassword = document.getElementById('regPassword');
            if (!validateRegPassword(regPassword, true)) return;
            if (!validateConfirmPassword(true)) return;

            const captchaResponse = window.grecaptcha ? window.grecaptcha.getResponse() : '';
            if (!captchaResponse) {
                showNotification('Please complete the reCAPTCHA.', 'warning');
                return;
            }

            if (!document.getElementById('agreeTerms').checked || !document.getElementById('agreePrivacy').checked) {
                showNotification('You must agree to the Terms and Privacy Policy.', 'warning');
                return;
            }

            if (data.regPassword !== data.confirmPassword) {
                showNotification('Passwords do not match.', 'error');
                return;
            }

            const submitButton = form.querySelector('button[type="submit"]');
            showLoadingState(submitButton);

            const noMiddleNameCheckbox = document.getElementById('noMiddleName');
            const middleNameValue = (noMiddleNameCheckbox && noMiddleNameCheckbox.checked) ? null : (typeof data.middleName !== 'undefined' && data.middleName !== '' ? data.middleName : null);

            const payload = {
                action: 'register',
                firstName: data.firstName || '',
                middleName: middleNameValue,
                lastName: data.lastName || '',
                email: data.regEmail || '',
                contactNumber: data.contact || '',
                address: data.address || '',
                password: data.regPassword || '',
                confirmPassword: data.confirmPassword || '',
                captchaToken: captchaResponse
            };

            try {
                const response = await makeAPICall('api/auth.php', payload);
                if (response.success) {
                    showNotification('Registration successful! You can now log in.', 'success');
                    hideRegisterForm();
                    form.reset();
                    if (noMiddleNameCheckbox) {
                        noMiddleNameCheckbox.checked = false;
                    }
                    const middleNameInput = document.getElementById('middleName');
                    if (middleNameInput) {
                        middleNameInput.disabled = false;
                        middleNameInput.required = true;
                    }
                    updatePasswordChecklist('');
                    if (window.grecaptcha) {
                        window.grecaptcha.reset();
                    }
                } else {
                    const errors = response.data && Array.isArray(response.data.errors) ? response.data.errors : [];
                    const message = errors.length ? errors.join(', ') : (response.message || 'Registration failed.');
                    showNotification(message, 'error');
                }
            } catch (error) {
                let message = 'Registration failed. Please try again.';
                if (error && error.payload) {
                    if (error.payload.data && Array.isArray(error.payload.data.errors) && error.payload.data.errors.length) {
                        message = error.payload.data.errors.join(', ');
                    } else if (error.payload.message) {
                        message = error.payload.message;
                    }
                } else if (error && error.message) {
                    message = error.message;
                }
                showNotification(message, 'error');
            } finally {
                resetButton(submitButton);
            }
        }


        function validateRegPassword(inputEl, showMessage = false) {
            if (!inputEl) return false;
            const value = inputEl.value || '';
            const isValid = /[A-Z]/.test(value) && /[a-z]/.test(value) && /\d/.test(value) && /[^A-Za-z0-9]/.test(value) && value.length >= 10;
            const parent = inputEl.parentNode;
            const existing = parent.querySelector('.pwd-error');
            if (existing) existing.remove();
            inputEl.classList.remove('border-red-500', 'ring-red-500');
            if (!isValid && showMessage) {
                inputEl.classList.add('border-red-500', 'ring-red-500');
            }
            return isValid;
        }


        function validateConfirmPassword(showMessage = false) {
            const pwd = document.getElementById('regPassword');
            const confirm = document.getElementById('confirmPassword');
            if (!pwd || !confirm) return false;
            const matches = (confirm.value || '') === (pwd.value || '');
            const wrapper = confirm.parentNode;
            const existing = wrapper.parentNode.querySelector('.confirm-error');
            if (existing && existing.previousElementSibling !== wrapper) {
                existing.remove();
            }
            confirm.classList.remove('border-red-500', 'ring-red-500');
            if (!matches && showMessage) {
                confirm.classList.add('border-red-500', 'ring-red-500');
                let msg = wrapper.parentNode.querySelector('.confirm-error');
                if (!msg) {
                    msg = document.createElement('div');
                    msg.className = 'confirm-error text-red-500 text-sm mt-1';
                    if (wrapper.nextSibling) {
                        wrapper.parentNode.insertBefore(msg, wrapper.nextSibling);
                    } else {
                        wrapper.parentNode.appendChild(msg);
                    }
                }
                msg.textContent = 'Passwords do not match.';
            }
            return matches;
        }


        function updatePasswordChecklist(value) {
            const checks = {
                length: value.length >= 10,
                upper: /[A-Z]/.test(value),
                lower: /[a-z]/.test(value),
                number: /\d/.test(value),
                special: /[^A-Za-z0-9]/.test(value)
            };
            const list = document.getElementById('pwdChecklist');
            if (!list) return;
            Object.keys(checks).forEach(key => {
                const item = list.querySelector(`.req-item[data-check="${key}"]`);
                if (!item) return;
                if (checks[key]) {
                    item.classList.add('met');
                } else {
                    item.classList.remove('met');
                }
            });
        }

        function updatePasswordStrength(value) {
            const bar = document.getElementById('strengthBar');
            const label = document.getElementById('strengthLabel');
            if (!bar || !label) return;
            let strength = 0;
            if (value.length >= 10) strength++;
            if (/[A-Z]/.test(value)) strength++;
            if (/[a-z]/.test(value)) strength++;
            if (/\d/.test(value)) strength++;
            if (/[^A-Za-z0-9]/.test(value)) strength++;
            const percent = Math.min(100, strength * 20);
            bar.style.width = percent + '%';
            let color = '#ef4444';
            let text = 'Very weak';
            if (percent >= 40) { color = '#f59e0b'; text = 'Weak'; }
            if (percent >= 60) { color = '#84cc16'; text = 'Good'; }
            if (percent >= 80) { color = '#22c55e'; text = 'Strong'; }
            if (percent === 100) { color = '#15803d'; text = 'Very strong'; }
            bar.style.backgroundColor = color;
            label.textContent = text;
        }

        function formatPhone(e) {
            const digits = (e.target.value || '').replace(/\D/g, '').slice(0, 11);
            e.target.value = digits;
        }


        function showNotification(message, type = 'info') {
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notification => notification.remove());

            const notification = document.createElement('div');
            notification.className = `notification ${type}`;

            switch (type) {
                case 'success':
                    notification.classList.add('bg-green-500');
                    break;
                case 'error':
                    notification.classList.add('bg-red-500');
                    break;
                case 'warning':
                    notification.classList.add('bg-yellow-500', 'text-slate-900');
                    break;
                default:
                    notification.classList.add('bg-blue-500');
            }

            notification.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="fas fa-${getNotificationIcon(type)}"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.classList.add('show');
            }, 100);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.classList.remove('show');
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.remove();
                        }
                    }, 300);
                }
            }, 5000);
        }


        function getNotificationIcon(type) {
            switch (type) {
                case 'success':
                    return 'check-circle';
                case 'error':
                    return 'exclamation-circle';
                case 'warning':
                    return 'exclamation-triangle';
                default:
                    return 'info-circle';
            }
        }


        function addSmoothScrolling() {
            const links = document.querySelectorAll('a[href^="#"]');
            links.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });
        }


        function serializeForm(form) {
            const formData = new FormData(form);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            return data;
        }


        async function makeAPICall(url, data, method = 'POST') {
            const response = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            let payload;
            try {
                payload = await response.json();
            } catch (parseError) {
                const error = new Error('Server returned an invalid response.');
                error.cause = parseError;
                throw error;
            }

            if (!response.ok) {
                const error = new Error(payload && payload.message ? payload.message : `Request failed with status ${response.status}`);
                error.status = response.status;
                error.payload = payload;
                throw error;
            }

            return payload;
        }


        window.GSM = {
            showNotification,
            validateEmail,
            makeAPICall
        };


        let otpIntervalId = null;
        let otpExpiresAt = null;


        function showOtpModal() {
            const modal = document.getElementById('otpModal');
            const resend = document.getElementById('resendOtp');
            const error = document.getElementById('otpError');
            const submit = document.getElementById('submitOtp');
            if (!modal) return;
            error.classList.add('hidden');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            resend.disabled = true;
            submit.disabled = false;
            const inputs = Array.from(document.querySelectorAll('#otpInputs .otp-input'));
            inputs.forEach(i => i.value = '');
            setupOtpInputs(inputs);
            if (inputs[0]) inputs[0].focus();
        }


        function closeOtpModal() {
            const modal = document.getElementById('otpModal');
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            stopOtpTimer();
        }


        function startOtpTimer(seconds) {
            otpExpiresAt = Date.now() + seconds * 1000;
            updateOtpTimer();
            if (otpIntervalId) clearInterval(otpIntervalId);
            otpIntervalId = setInterval(updateOtpTimer, 1000);
        }


        function stopOtpTimer() {
            if (otpIntervalId) clearInterval(otpIntervalId);
            otpIntervalId = null;
        }


        function updateOtpTimer() {
            const timerEl = document.getElementById('otpTimer');
            const resend = document.getElementById('resendOtp');
            const submit = document.getElementById('submitOtp');
            const remaining = Math.max(0, Math.floor((otpExpiresAt - Date.now()) / 1000));
            const mm = String(Math.floor(remaining / 60)).padStart(2, '0');
            const ss = String(remaining % 60).padStart(2, '0');
            if (timerEl) timerEl.textContent = `${mm}:${ss}`;
            if (remaining === 0) {
                if (resend) resend.disabled = false;
                if (submit) submit.disabled = true;
                stopOtpTimer();
            }
        }


        document.addEventListener('DOMContentLoaded', () => {
            const cancelOtp = document.getElementById('cancelOtp');
            const otpForm = document.getElementById('otpForm');
            const resend = document.getElementById('resendOtp');
            const modal = document.getElementById('otpModal');
            if (cancelOtp) cancelOtp.addEventListener('click', closeOtpModal);
            if (resend) resend.addEventListener('click', async () => {
                if (!pendingLoginEmail) {
                    showNotification('No email found. Please login again.', 'error');
                    return;
                }
                try {
                    const response = await makeAPICall('api/auth.php', {
                        action: 'request_otp',
                        email: pendingLoginEmail
                    });
                    if (response.success) {
                        showNotification('A new OTP has been sent to your email.', 'success');
                        resend.disabled = true;
                        startOtpTimer(response.data?.expires_in || 120);
                    } else {
                        showNotification(response.message || 'Failed to resend OTP', 'error');
                    }
                } catch (error) {
                    showNotification('Failed to resend OTP. Please try again.', 'error');
                }
            });
            if (otpForm) otpForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const code = collectOtpCode();
                const error = document.getElementById('otpError');
                const submitBtn = document.getElementById('submitOtp');

                if (!code || code.length !== 6) {
                    error.textContent = 'Please enter the 6-digit OTP.';
                    error.classList.remove('hidden');
                    return;
                }
                if (submitBtn.disabled) {
                    error.textContent = 'OTP expired. Please resend a new OTP.';
                    error.classList.remove('hidden');
                    return;
                }

                if (!pendingLoginEmail) {
                    error.textContent = 'Session expired. Please login again.';
                    error.classList.remove('hidden');
                    return;
                }

                error.classList.add('hidden');
                showLoadingState(submitBtn);

                try {
                    const response = await makeAPICall('api/auth.php', {
                        action: 'verify_otp',
                        email: pendingLoginEmail,
                        otp: code
                    });

                    if (response.success && response.data) {
                        showNotification('OTP verified! Redirecting...', 'success');
                        stopOtpTimer();
                        setTimeout(() => {
                            closeOtpModal();
                            const redirectUrl = response.data.redirect || 'website.php';
                            window.location.href = redirectUrl;
                        }, 800);
                    } else {
                        error.textContent = response.message || 'Invalid OTP. Please try again.';
                        error.classList.remove('hidden');
                        resetButton(submitBtn);
                    }
                } catch (err) {
                    const message = (err && err.payload && err.payload.message)
                        ? err.payload.message
                        : 'Failed to verify OTP. Please try again.';
                    error.textContent = message;
                    error.classList.remove('hidden');
                    resetButton(submitBtn);
                }
            });
            if (modal) {
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) closeOtpModal();
                });
            }
        });


        function setupOtpInputs(inputs) {
            const submitBtn = document.getElementById('submitOtp');
            inputs.forEach((input, idx) => {
                input.addEventListener('input', (e) => {
                    const value = e.target.value.replace(/\D/g, '').slice(0, 1);
                    e.target.value = value;
                    if (value && idx < inputs.length - 1) {
                        inputs[idx + 1].focus();
                    }

                    // Auto-submit logic
                    const code = inputs.map(i => i.value).join('');
                    if (code.length === 6 && submitBtn && !submitBtn.disabled) {
                        input.blur();
                        submitBtn.click();
                    }
                });
                input.addEventListener('keydown', (e) => {
                    if (e.key === 'Backspace' && !e.target.value && idx > 0) {
                        inputs[idx - 1].focus();
                    }
                });
                input.addEventListener('paste', (e) => {
                    const text = (e.clipboardData || window.clipboardData).getData('text');
                    if (!text) return;
                    const digits = text.replace(/\D/g, '').slice(0, inputs.length).split('');
                    inputs.forEach((i, iIdx) => { i.value = digits[iIdx] || ''; });
                    e.preventDefault();

                    const nextIndex = Math.min(digits.length, inputs.length - 1);
                    inputs[nextIndex].focus();

                    // Auto-submit for paste
                    const code = inputs.map(i => i.value).join('');
                    if (code.length === 6 && submitBtn && !submitBtn.disabled) {
                        inputs[nextIndex].blur();
                        submitBtn.click();
                    }
                });
            });
        }


        function collectOtpCode() {
            const inputs = Array.from(document.querySelectorAll('#otpInputs .otp-input'));
            return inputs.map(i => i.value).join('');
        }
    </script>
</body>

</html>