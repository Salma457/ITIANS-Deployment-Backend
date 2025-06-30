<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training Round Ended - ITI</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #ffffff 0%, #f5d0d0 100%);
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
        
        .message-container {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 1.5rem;
            border-radius: 16px;
            margin: 1.5rem 0;
            border-left: 4px solid #dc2626;
        }
        
        .message-title {
            font-size: 1.1rem;
            color: #1e293b;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .status-container {
            text-align: center;
            margin: 2.5rem 0;
        }
        
        .status-complete {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1.2rem;
            font-weight: 600;
            display: inline-block;
            box-shadow: 0 10px 25px rgba(220, 38, 38, 0.3);
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
            <h1>Training Round Completed</h1>
        </div>
        
        <div class="content">
            <div class="greeting">
                Hello {{ $companyName }},
            </div>
            
            <div class="status-container">
                <div class="status-complete">
                    ✓ Training Round Ended
                </div>
            </div>
            
            <div class="message-container">
               
                <p>{{ $messageText }}</p>
            </div>
            
            
               
           
        </div>
        
        <div class="footer">
            
            <div class="team-signature">
                Best regards,<br>
                <strong>The ITIAN Team</strong>
            </div>
        </div>
    </div>
</body>
</html>