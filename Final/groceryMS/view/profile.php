<?php
// Auto-added DB bootstrap (keeps your design, replaces JSON with MySQL)
$__candidates = [
    __DIR__ . '/../model/compat.php',
    __DIR__ . '/model/compat.php',
    __DIR__ . '/../../model/compat.php',
];
<!DOCTYPE html>
<head>
    <title>My Profile</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7f9;
            color: #333;
            line-height: 1.6;
        }
        
        .profile-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #3498db;
        }
        
        .profile-header h2 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 32px;
            font-weight: 600;
        }
        
        .profile-header p {
            color: #7f8c8d;
            font-size: 16px;
        }
        
        .profile-content {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .profile-form-section {
            flex: 1;
            min-width: 300px;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        
        .profile-form-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
            font-size: 22px;
        }
        
        .profile-form .form-row {
            margin-bottom: 20px;
        }
        
        .profile-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #34495e;
            font-size: 15px;
        }
        
        .profile-form input,
        .profile-form textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .profile-form input:focus,
        .profile-form textarea:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .input-hint {
            display: block;
            margin-top: 5px;
            color: #7f8c8d;
            font-size: 13px;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
            width: 100%;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .profile-stats-section {
            flex: 1;
            min-width: 300px;
        }
        
        .profile-stats-section h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
            font-size: 22px;
        }
        
        .stats-card {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
        }
        
        .stat-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #f1f1f1;
        }
        
        .stat-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
        
        .stat-info {
            flex: 1;
        }
        
        .stat-label {
            display: block;
            color: #7f8c8d;
            font-size: 15px;
            font-weight: 500;
        }
        
        .stat-value {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .notice {
            padding: 12px 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
            font-weight: 500;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border-left-color: #28a745;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border-left-color: #dc3545;
        }
        
        @media (max-width: 768px) {
            .profile-content {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <h2>My Profile</h2>
            <p>View and update your personal information</p>
        </div>
        
        <div class="notice success">Profile updated successfully.</div>
        
        <div class="profile-content">
            <div class="profile-form-section">
                <h3>Personal Information</h3>
                <form class="profile-form">
                    <div class="form-row">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-row">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <div class="form-row">
                        <label for="phone">Phone Number:</label>
                        <input type="tel" id="phone" name="phone" pattern="01[3-9]\d{8}" 
                               title="11-digit Bangladeshi number" 
                               placeholder="01XXXXXXXXX">
                        <small class="input-hint"></small>
                    </div>
                    
                    <div class="form-row">
                        <label for="address">Address:</label>
                        <textarea id="address" name="address" rows="3" placeholder="Enter your complete address"></textarea>
                    </div>
                    
                    <button type="submit" class="btn-primary">Update Profile</button>
                </form>
            </div>
            
            <div class="profile-stats-section">
                <h3>Order Statistics</h3>
                <div class="stats-card">
                    <div class="stat-item">
                        <div class="stat-info">
                            <span class="stat-label">Total Orders</span>
                            <span class="stat-value">0</span>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-info">
                            <span class="stat-label">Completed Orders</span>
                            <span class="stat-value">0</span>
                        </div>
                    </div>
                    
                    <div class="stat-item">
                        <div class="stat-info">
                            <span class="stat-label">Total Spent</span>
                            <span class="stat-value">0.00</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form submission
            const form = document.querySelector('.profile-form');
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                
                const phoneInput = document.getElementById('phone');
                const phonePattern = /^01[3-9]\d{8}$/;
                
                if (phoneInput.value && !phonePattern.test(phoneInput.value)) {
                    alert('Please enter a valid Bangladeshi phone number (11 digits starting with 01)');
                    phoneInput.focus();
                    return;
                }
                
                const notice = document.querySelector('.notice');
                notice.textContent = 'Profile updated successfully.';
                notice.className = 'notice success';
                notice.style.display = 'block';
                
                setTimeout(() => {
                    notice.style.display = 'none';
                }, 3000);
            });
        });
    </script>
</body>
</html>
