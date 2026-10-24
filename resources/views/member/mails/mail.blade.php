<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to {{ config('detailsApp.name') }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .email-container {
            width: 100%;
            max-width: 600px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Header Section */
        .email-header {
            background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .email-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .email-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .logo-wrapper {
            position: relative;
            z-index: 1;
            margin-bottom: 20px;
        }

        .logo-wrapper img {
            width: 80px;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .logo-wrapper img:hover {
            transform: scale(1.05);
        }

        .header-title {
            color: white;
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
            letter-spacing: -0.5px;
        }

        .header-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }

        /* Welcome Section */
        .email-body {
            padding: 40px 30px;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeIn 0.8s ease-out 0.3s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .welcome-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .welcome-title {
            color: #111827;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            letter-spacing: -0.5px;
        }

        .welcome-text {
            color: #6B7280;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .success-badge {
            display: inline-block;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 15px;
        }

        /* Credentials Section */
        .credentials-section {
            margin: 30px 0;
        }

        .section-title {
            color: #111827;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #4F46E5;
            display: inline-block;
        }

        .credentials-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .credential-card {
            background: linear-gradient(135deg, #F8FAFC 0%, #F0F4FF 100%);
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .credential-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #4F46E5, #6366F1);
        }

        .credential-card:hover {
            border-color: #4F46E5;
            box-shadow: 0 8px 24px rgba(79, 70, 229, 0.12);
            transform: translateY(-2px);
        }

        .credential-icon {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .credential-label {
            color: #6B7280;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .credential-value {
            color: #111827;
            font-size: 16px;
            font-weight: 700;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            padding: 8px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 6px;
        }

        /* Single Column for Small Items */
        .credential-card-full {
            grid-column: 1 / -1;
        }

        /* URL Card */
        .url-card {
            background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%);
            border: 2px solid #10B981;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .url-card:hover {
            box-shadow: 0 8px 24px rgba(16, 185, 129, 0.15);
            transform: translateY(-2px);
        }

        .url-icon {
            font-size: 24px;
            margin-bottom: 10px;
            color: #10B981;
        }

        .url-label {
            color: #047857;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .url-value {
            color: #065F46;
            font-size: 16px;
            font-weight: 700;
            word-break: break-all;
        }

        .url-link {
            display: inline-block;
            color: #10B981;
            text-decoration: none;
            font-weight: 600;
            margin-top: 8px;
            padding: 8px 16px;
            background: rgba(16, 185, 129, 0.1);
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .url-link:hover {
            background: rgba(16, 185, 129, 0.2);
            text-decoration: underline;
        }

        /* Footer Section */
        .email-footer {
            background: #F8FAFC;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #E5E7EB;
        }

        .footer-text {
            color: #6B7280;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .signature {
            color: #111827;
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .company-name {
            color: #4F46E5;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .footer-divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #4F46E5, #6366F1);
            margin: 15px auto;
            border-radius: 2px;
        }

        /* Security Notice */
        .security-notice {
            background: linear-gradient(135deg, #FEF3C7 0%, #FCD34D 100%);
            border: 2px solid #F59E0B;
            border-radius: 12px;
            padding: 16px;
            margin-top: 20px;
            font-size: 13px;
            color: #92400E;
        }

        .security-icon {
            font-size: 18px;
            margin-right: 8px;
            vertical-align: middle;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .email-container {
                border-radius: 12px;
            }

            .email-header {
                padding: 30px 20px;
            }

            .header-title {
                font-size: 24px;
            }

            .header-subtitle {
                font-size: 14px;
            }

            .email-body {
                padding: 25px 20px;
            }

            .welcome-title {
                font-size: 24px;
            }

            .welcome-text {
                font-size: 14px;
            }

            .credentials-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .credential-card {
                padding: 16px;
            }

            .credential-label {
                font-size: 12px;
            }

            .credential-value {
                font-size: 14px;
            }

            .section-title {
                font-size: 16px;
                margin-bottom: 15px;
            }

            .email-footer {
                padding: 20px 15px;
            }

            .footer-text {
                font-size: 12px;
            }

            .signature {
                font-size: 14px;
            }

            .company-name {
                font-size: 16px;
            }
        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                padding: 0;
            }

            .email-container {
                box-shadow: none;
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header Section -->
        <div class="email-header">
            <div class="logo-wrapper">
                <img src="{{ $message->embed(public_path().'/logo/logo.png') }}" alt="{{ config('detailsApp.name') }} Logo">
            </div>
            <h1 class="header-title">{{ config('detailsApp.name') }}</h1>
            <p class="header-subtitle">Welcome to Your Account</p>
        </div>

        <!-- Body Section -->
        <div class="email-body">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <div class="welcome-icon">🎉</div>
                <h2 class="welcome-title">Welcome, {{ $name }}!</h2>
                <p class="welcome-text">Your account has been successfully created with {{ config('detailsApp.name') }}.</p>
                <p class="welcome-text">You're all set to start your journey with us. Below are your account credentials.</p>
                <span class="success-badge">✓ Account Created Successfully</span>
            </div>

            <!-- Credentials Section -->
            <div class="credentials-section">
                <h3 class="section-title">📋 Account Credentials</h3>
                
                <div class="credentials-grid">
                    <!-- Member ID -->
                    <div class="credential-card">
                        <div class="credential-icon">👤</div>
                        <div class="credential-label">Member ID</div>
                        <div class="credential-value">{{ $memberid }}</div>
                    </div>

                    <!-- Email -->
                    <div class="credential-card">
                        <div class="credential-icon">📧</div>
                        <div class="credential-label">Email Address</div>
                        <div class="credential-value">{{ $email }}</div>
                    </div>

                    <!-- Mobile -->
                    <div class="credential-card">
                        <div class="credential-icon">📱</div>
                        <div class="credential-label">Mobile Number</div>
                        <div class="credential-value">{{ $mobile }}</div>
                    </div>

                    <!-- Password -->
                    <div class="credential-card">
                        <div class="credential-icon">🔐</div>
                        <div class="credential-label">Login Password</div>
                        <div class="credential-value">{{ $password }}</div>
                    </div>

                    <!-- Transaction Password -->
                    <div class="credential-card credential-card-full">
                        <div class="credential-icon">🔑</div>
                        <div class="credential-label">Security Pin</div>
                        <div class="credential-value">{{ $txn_password }}</div>
                    </div>
                </div>

                <!-- URL Card -->
                <div class="url-card">
                    <div class="url-icon">🌐</div>
                    <div class="url-label">Platform URL</div>
                    <div class="url-value">{{ url('/') }}</div>
                    <a href="{{ url('/') }}" class="url-link">📍 Visit Platform</a>
                </div>

                <!-- Security Notice -->
                <div class="security-notice">
                    <span class="security-icon">⚠️</span>
                    <strong>Keep your credentials safe!</strong> Never share your passwords with anyone, including our support team. We'll never ask for them via email or message.
                </div>
            </div>
        </div>

        <!-- Footer Section -->
        <div class="email-footer">
            <p class="footer-text">If you have any questions or need assistance, please don't hesitate to reach out to our support team.</p>
            <div class="footer-divider"></div>
            <p class="signature">With Regards,</p>
            <p class="company-name">Team {{ config('detailsApp.name') }}</p>
        </div>
    </div>

    <script>
        // Add animation on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.credential-card');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${0.1 * (index + 1)}s`;
                card.style.animation = 'slideIn 0.6s ease-out forwards';
            });
        });
    </script>
</body>
</html>
