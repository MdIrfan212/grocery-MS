<?php
// Auto-added DB bootstrap (keeps your design, replaces JSON with MySQL)
$__candidates = [
    __DIR__ . '/../model/compat.php',
    __DIR__ . '/model/compat.php',
    __DIR__ . '/../../model/compat.php',
];
foreach ($__candidates as $__p) { if (file_exists($__p)) { require_once $__p; break; } }
?>
<div class="panel" style="position: relative;">
  <div style="position: absolute; top: 0; left: 0; width: 150px; height: 150px; background: linear-gradient(45deg, #2196f3, #03a9f4); opacity: 0.1; border-radius: 50%; transform: translate(-75px, -75px);"></div>
  
  <h3 style="color: #1976d2; display: flex; align-items: center; gap: 10px;">
    <span style="font-size: 1.5em;">💎</span> Project Information & Credits
  </h3>

  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 25px 0;">
    <!-- Team Card -->
    <div style="padding: 20px; background: linear-gradient(145deg, #f8f9fa, #e3f2fd); border-radius: 12px; box-shadow: 0 4px 12px rgba(33, 150, 243, 0.1);">
      <h4 style="color: #1976d2; margin-top: 0; display: flex; align-items: center; gap: 8px;">👥 Development Team</h4>
      <div style="background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #2196f3;">
        <div style="font-weight: bold; color: #1976d2;">Team Members:</div>
        <ul style="margin: 10px 0 0 0; padding-left: 20px;">
          <li>Lead Developer</li>
          <li>UI/UX Designer</li>
          <li>Backend Engineer</li>
          <li>Project Manager</li>
        </ul>
      </div>
    </div>

    <!-- Course Card -->
    <div style="padding: 20px; background: linear-gradient(145deg, #f8f9fa, #e3f2fd); border-radius: 12px; box-shadow: 0 4px 12px rgba(33, 150, 243, 0.1);">
      <h4 style="color: #1976d2; margin-top: 0; display: flex; align-items: center; gap: 8px;">🎓 Academic Information</h4>
      <div style="background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #2196f3;">
        <div><strong>Course:</strong> Web Technology</div>
        <div><strong>Faculty:</strong> Computer Science & Engineering</div>
        <div><strong>Institution:</strong> American International University of Bangladesh </div>
        <div><strong>Semester:</strong> summer 2025</div>
      </div>
    </div>

    <!-- Technology Card -->
    <div style="padding: 20px; background: linear-gradient(145deg, #f8f9fa, #e3f2fd); border-radius: 12px; box-shadow: 0 4px 12px rgba(33, 150, 243, 0.1);">
      <h4 style="color: #1976d2; margin-top: 0; display: flex; align-items: center; gap: 8px;">🛠️ Technology Stack</h4>
      <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;">
        <span style="background: #2196f3; color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.9em;">PHP 7.4+</span>
        <span style="background: #2196f3; color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.9em;">HTML5</span>
        <span style="background: #2196f3; color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.9em;">CSS3</span>
        <span style="background: #2196f3; color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.9em;">JavaScript</span>
        <span style="background: #2196f3; color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.9em;">JSON DB</span>
        <span style="background: #2196f3; color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.9em;">Session Auth</span>
      </div>
    </div>
  </div>

  <!-- Important Notice -->
  <div style="background: linear-gradient(135deg, #ff9800, #f57c00); color: white; padding: 20px; border-radius: 12px; margin: 25px 0; position: relative;">
    <div style="position: absolute; top: 10px; right: 10px; font-size: 2em;">⚠️</div>
    <h4 style="margin: 0 0 15px 0; display: flex; align-items: center; gap: 10px;">Important Security Notice</h4>
    <p style="margin: 0; font-size: 0.95em;">
      <strong>This is a demonstration system for educational purposes only.</strong><br>
      For production use, implement proper security measures including:
    </p>
    <ul style="margin: 10px 0 0 0; padding-left: 20px; font-size: 0.9em;">
      <li>Password hashing (bcrypt/Argon2)</li>
      <li>SQL database with prepared statements</li>
      <li>CSRF protection</li>
      <li>Input validation & sanitization</li>
      <li>HTTPS encryption</li>
      <li>Regular security audits</li>
    </ul>
  </div>

  <!-- Version & Status -->
  <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px; background: #e8f5e9; border-radius: 8px; margin-top: 20px;">
    <div>
      <strong>Version:</strong> 2.1.0 | <strong>Status:</strong> 
      <span style="background: #4caf50; color: white; padding: 2px 8px; border-radius: 12px; font-size: 0.8em;">Active</span>
    </div>
    <div style="font-size: 0.9em; color: #666;">
      Last updated: <?= date('F j, Y') ?>
    </div>
  </div>
</div>

<style>
  .panel h3 {
    border-bottom: 3px solid #2196f3;
    padding-bottom: 10px;
    margin-bottom: 25px;
  }
</style>