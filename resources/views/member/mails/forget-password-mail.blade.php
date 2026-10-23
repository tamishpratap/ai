<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{config('detailsApp.name')}} - Password Reset</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Inter', 'Poppins', sans-serif;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .header-section {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 500px;
            height: 500px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .logo-wrapper {
            position: relative;
            z-index: 1;
            margin-bottom: 20px;
        }
        
        .logo-wrapper img {
            max-width: 80px;
            height: auto;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
        }
        
        .welcome-icon {
            font-size: 48px;
            color: #ffffff;
            margin-bottom: 15px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .header-section h1 {
            color: #ffffff;
            font-size: 32px;
            font-weight: 700;
            margin: 0;
            letter-spacing: -0.5px;
        }
        
        .header-section p {
            color: rgba(255, 255, 255, 0.95);
            font-size: 16px;
            margin: 10px 0 0 0;
            font-weight: 300;
        }
        
        .content-section {
            padding: 40px 30px;
        }
        
        .greeting-text {
            color: #333333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        
        .info-message {
            background: #fef3e2;
            border-left: 4px solid #ff6b6b;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            color: #333333;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .password-section {
            margin-bottom: 35px;
        }
        
        .details-section {
            margin-bottom: 35px;
        }
        
        .details-card {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 15px 40px rgba(255, 107, 107, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .details-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .details-card::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }
        
        .card-title {
            color: #ffffff;
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            position: relative;
            z-index: 1;
        }
        
        .card-title i {
            font-size: 22px;
        }
        
        /* Input Fields Styling */
        .input-group-wrapper {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin: 25px 0;
            position: relative;
            z-index: 1;
        }
        
        .input-field {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 18px;
            background: rgba(255, 255, 255, 0.95);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
            backdrop-filter: blur(10px);
        }
        
        .input-field:hover {
            background: #ffffff;
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        
        .input-field.active {
            background: #ffffff;
            border-color: rgba(255, 255, 255, 0.8);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }
        
        .input-icon {
            color: #ff6b6b;
            font-size: 18px;
            min-width: 24px;
            text-align: center;
            flex-shrink: 0;
        }
        
        .input-content {
            flex: 1;
        }
        
        .input-label {
            font-size: 11px;
            font-weight: 700;
            color: #999999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: block;
        }
        
        .input-value {
            font-size: 15px;
            font-weight: 600;
            color: #333333;
            word-break: break-all;
            line-height: 1.4;
        }
        
        .security-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 10px;
            padding: 15px 20px;
            margin-top: 25px;
            color: #856404;
            font-size: 13px;
            line-height: 1.6;
        }
        
        .security-notice i {
            color: #ff9800;
            margin-right: 8px;
            font-weight: bold;
        }
        
        .footer-section {
            background: #f8f9fc;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e8eef7;
        }
        
        .footer-text {
            color: #666666;
            font-size: 14px;
            margin: 10px 0;
            line-height: 1.6;
        }
        
        .company-name {
            font-weight: 700;
            color: #ff6b6b;
            font-size: 18px;
            margin-top: 15px;
        }
        
        .logo-footer {
            margin-top: 20px;
        }
        
        .logo-footer img {
            max-width: 60px;
            height: auto;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }
        
        .member-info {
            background: #f0f7ff;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .member-info-icon {
            color: #ff6b6b;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .member-info-text {
            color: #333333;
            font-size: 14px;
            font-weight: 500;
            line-height: 1.5;
        }
        
        .member-id-highlight {
            font-weight: 700;
            color: #ff6b6b;
            font-size: 16px;
        }
        
        /* Responsive Design */
        @media (max-width: 600px) {
            .email-container {
                margin: 10px;
                border-radius: 12px;
            }
            
            .header-section {
                padding: 30px 20px;
            }
            
            .header-section h1 {
                font-size: 26px;
            }
            
            .content-section {
                padding: 25px 20px;
            }
            
            .input-field {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .input-value {
                max-width: 100%;
                text-align: left;
                width: 100%;
            }
            
            .welcome-icon {
                font-size: 40px;
            }
            
            .member-info {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header Section -->
        <div class="header-section">
            <div class="logo-wrapper">
                <img src="{{$message->embed(public_path().'/logo/logo.png')}}" alt="{{config('detailsApp.name')}} Logo">
            </div>
            <div class="welcome-icon">
                <i class="fas fa-key"></i>
            </div>
            <h1>Password Reset</h1>
            <p>Your new password has been generated</p>
        </div>
        
        <!-- Content Section -->
        <div class="content-section">
            <p class="greeting-text">
                <i class="fas fa-wave"></i> Hello, <strong>{{$name}}</strong>!
            </p>
            
            <div class="info-message">
                <i class="fas fa-info-circle" style="color: #ff6b6b; margin-right: 8px;"></i>
                We received a request to reset your password. Here is your new temporary password. Please change it after logging in.
            </div>
            
            <!-- Member Information -->
            <div class="member-info">
                <div class="member-info-icon">
                    <i class="fas fa-id-card"></i>
                </div>
                <div class="member-info-text">
                    Password reset for account: <span class="member-id-highlight">{{$memberid}}</span>
                </div>
            </div>
            
            <!-- Password Details in Card -->
            <div class="details-section">
                <div class="details-card">
                    <div class="card-title">
                        <i class="fas fa-lock"></i>
                        Your New Password
                    </div>
                    
                    <div class="input-group-wrapper">
                        <div class="input-field">
                            <div class="input-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <div class="input-content">
                                <span class="input-label">Temporary Password</span>
                                <div class="input-value">{{$password}}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Security Notice -->
            <div class="security-notice">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Important:</strong> Keep this password confidential and change it to something memorable after your first login. Never share your password with anyone.
            </div>
        </div>
        
        <!-- Footer Section -->
        <div class="footer-section">
            <p class="footer-text">
                <i class="fas fa-question-circle"></i> If you didn't request this, please contact support immediately
            </p>
            <p class="company-name">
                <i class="fas fa-star"></i> {{config('detailsApp.name')}}
            </p>
            <div class="logo-footer">
                <img src="{{$message->embed(public_path().'/logo/logo.png')}}" alt="{{config('detailsApp.name')}}">
            </div>
            <p class="footer-text" style="margin-top: 15px; font-size: 12px; color: #999999;">
                © {{ date('Y') }} {{config('detailsApp.name')}}. All rights reserved.
            </p>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous"></script>
    
    <script>
        // Add interactive effects to input fields
        document.addEventListener('DOMContentLoaded', function() {
            const inputFields = document.querySelectorAll('.input-field');
            
            inputFields.forEach(field => {
                field.addEventListener('mouseenter', function() {
                    this.classList.add('active');
                });
                
                field.addEventListener('mouseleave', function() {
                    this.classList.remove('active');
                });
            });
        });
    </script>
</body>
</html>
