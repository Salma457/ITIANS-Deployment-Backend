<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status - ITI</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #ffffff 0%, #cca6a6 100%);
            min-height: 100vh;
            padding: 2rem 1rem;
            line-height: 1.6;
        }
        
        .email-container {
            max-width: 650px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.8s ease-out;
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
        
        .header {
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: rotate 20s linear infinite;
        }
        
        @keyframes rotate {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        
        .logo {
            position: relative;
            z-index: 2;
            margin-bottom: 1rem;
        }
        
        .logo-placeholder {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .logo-text {
            font-weight: 700;
            font-size: 1.5rem;
            color: white;
            text-align: center;
                margin: auto;

            letter-spacing: 2px;
        }
        
        .header h1 {
            color: white;
            font-size: 1.8rem;
            font-weight: 600;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }
        
        .content {
            padding: 3rem 2.5rem;
            background: white;
        }
        
        .greeting {
            font-size: 1.3rem;
            color: #1e293b;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .job-info {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 1.5rem;
            border-radius: 16px;
            margin: 1.5rem 0;
            border-left: 4px solid #dc2626;
        }
        
        .job-title {
            font-size: 1.1rem;
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .status-container {
            text-align: center;
            margin: 2.5rem 0;
        }
        
        .status-approved {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 600;
            display: inline-block;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.3);
            animation: pulse 2s infinite;
        }
        
        .status-rejected {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 600;
            display: inline-block;
            box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .message {
            background: #f8fafc;
            padding: 2rem;
            border-radius: 16px;
            margin: 2rem 0;
            font-size: 1.05rem;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        
        .success-message {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border-color: #10b981;
        }
        
        .success-message::before {
            content: '🎉';
            font-size: 2rem;
            display: block;
            text-align: center;
            margin-bottom: 1rem;
        }
        
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white !important;
            padding: 1rem 2rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
            margin-top: 1.5rem;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(220, 38, 38, 0.4);
        }
        
        .footer {
            background: #f8fafc;
            padding: 2rem;
            text-align: center;
            color: #64748b;
            font-size: 0.95rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .team-signature {
            margin-top: 1rem;
            font-weight: 500;
            color: #1e293b;
        }
        
        .decorative-line {
            height: 4px;
            background: linear-gradient(90deg, #dc2626, #ef4444, #f87171);
            margin: 1.5rem 0;
            border-radius: 2px;
        }
        
        @media (max-width: 640px) {
            body {
                padding: 1rem 0.5rem;
            }
            
            .content {
                padding: 2rem 1.5rem;
            }
            
            .header {
                padding: 2rem 1.5rem;
            }
            
            .header h1 {
                font-size: 1.5rem;
            }
            
            .greeting {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">
                <div class="logo-placeholder">
                    <span class="logo-text ">ITI</span>
                </div>
            </div>
            <h1>Application Status Update</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                Hi {{ $user->name ?? 'Applicant' }},
            </div>
            <div class="job-info">
                <div class="job-title">
                    Job Title: {{ $jobTitle }}
                </div>
                <div class="text-gray-600 text-sm">
                 Company: {{ $companyName }}
                </div>
            </div>

            <p>We hope this message finds you well. Your application has been carefully reviewed by our recruitment team.</p>
            
            
            <div class="decorative-line"></div>
            
            <div class="status-container">
                <!-- Replace this with your dynamic status -->
                <div class="status-approved">
                    ✓ Status: Approved
                </div>
                <!-- For rejected status, use: -->
                <!-- <div class="status-rejected">✗ Status: Not Selected</div> -->
            </div>
            
            <!-- Success Message (show if approved) -->
            <div class="message success-message">
                <strong>Congratulations!</strong> We're thrilled to inform you that you've been selected to move forward in our recruitment process. Your qualifications and experience align perfectly with what we're looking for. Our HR team will be reaching out to you within the next 2-3 business days with detailed information about the next steps.
            </div>
            
            <!-- Rejection Message (show if rejected) -->
            <!-- <div class="message">
                Thank you for taking the time to apply for this position and for your interest in joining our team. While your background is impressive, we've decided to move forward with other candidates whose experience more closely matches our current needs. We encourage you to apply for future opportunities that align with your skills.
            </div> -->
        </div>
        
        <div class="footer">
            <p>This is an automated message from our recruitment system. If you have any questions, please don't hesitate to reach out to our HR team.</p>
            <div class="team-signature">
                Best regards,<br>
                <strong>The ITI Recruitment Team</strong>
            </div>
        </div>
    </div>
</body>
</html>