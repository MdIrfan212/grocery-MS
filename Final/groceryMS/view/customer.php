<?php
$__candidates = [
    __DIR__ . '/../model/compat.php',
    __DIR__ . '/model/compat.php',
    __DIR__ . '/../../model/compat.php',
];
<div class="panel" style="position: relative; overflow: hidden;">
  <div style="position: absolute; top: 0; right: 0; width: 200px; height: 200px; background: linear-gradient(135deg, #4caf50, #8bc34a); opacity: 0.1; border-radius: 50%; transform: translate(100px, -100px);"></div>
  
  <h3 style="color: #2e7d32; position: relative; display: inline-block;">
    <span style="display: inline-block; animation: bounce 2s infinite;"></span> About GroceryMS - Customer Edition
  </h3>
  
  <div style="display: flex; gap: 20px; flex-wrap: wrap; margin: 20px 0;">
    <div style="flex: 1; min-width: 300px; padding: 20px; background: linear-gradient(145deg, #f8f9fa, #e8f5e9); border-radius: 12px; box-shadow: 0 4px 12px rgba(76, 175, 80, 0.1);">
      <h4 style="color: #388e3c; margin-top: 0;">What is GroceryMS?</h4>
      <p>GroceryMS is a powerful, lightweight grocery management system built specifically for educational projects and small businesses. Our file-based architecture makes it incredibly easy to deploy and manage without complex database setups.</p>
    </div>
    
    <div style="flex: 1; min-width: 300px; padding: 20px; background: linear-gradient(145deg, #f8f9fa, #e8f5e9); border-radius: 12px; box-shadow: 0 4px 12px rgba(76, 175, 80, 0.1);">
      <h4 style="color: #388e3c; margin-top: 0;"> Customer Features</h4>
      <ul style="columns: 2; column-gap: 20px;">
        <li> Easy Shopping</li>
        <li> Wishlist</li>
        <li> Order Tracking</li>
        <li> Product Reviews</li>
        <li> User-Friendly</li>
        <li>Secure Payments</li>
        <li>Order History</li>
        <li>Fast Delivery</li>
      </ul>
    </div>
  </div>

  <div style="background: linear-gradient(135deg, #4caf50, #2e7d32); color: white; padding: 25px; border-radius: 12px; margin: 20px 0; text-align: center;">
    <h4 style="margin: 0 0 15px 0; font-size: 1.3em;">📊 Customer Statistics</h4>
    <div style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 20px;">
      <div style="text-align: center;">
        <div style="font-size: 2em; font-weight: bold;"><?= count($products) ?></div>
        <div>Total Products</div>
      </div>
      <div style="text-align: center;">
        <div style="font-size: 2em; font-weight: bold;"><?= count(array_filter($orders, fn($o) => $o['by'] === $name)) ?></div>
        <div>Your Orders</div>
      </div>
      <div style="text-align: center;">
        <div style="font-size: 2em; font-weight: bold;"><?= count(get_wishlist($name)) ?></div>
        <div>Wishlist Items</div>
      </div>
      <div style="text-align: center;">
        <div style="font-size: 2em; font-weight: bold;"><?= count(array_filter($orders, fn($o) => $o['by'] === $name && $o['status'] === 'Delivered')) ?></div>
        <div>Delivered Orders</div>
      </div>
    </div>
  </div>

  <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f8f8; border-radius: 12px;">
    <p style="margin: 0; font-size: 1.1em;">
      <strong>Built with  using PHP, HTML5, CSS3 & JavaScript</strong><br>
      <span style="color: #666; font-size: 0.9em;">Perfect for learning web development and inventory management systems</span>
    </p>
  </div>
</div>

<style>
  @keyframes bounce {
    0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
    40% {transform: translateY(-10px);}
    60% {transform: translateY(-5px);}
  }
  
  .panel h3 {
    border-bottom: 3px solid #4caf50;
    padding-bottom: 10px;
    margin-bottom: 25px;
  }
</style>
