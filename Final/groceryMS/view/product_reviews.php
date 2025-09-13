<?php
// Auto-added DB bootstrap (keeps your design, replaces JSON with MySQL)
$__candidates = [
    __DIR__ . '/../model/compat.php',
    __DIR__ . '/model/compat.php',
    __DIR__ . '/../../model/compat.php',
];
foreach ($__candidates as $__p) { if (file_exists($__p)) { require_once $__p; break; } }
?>
<?php
// product_reviews.php - Product reviews and ratings page
$productId = $_GET['id'] ?? '';

if (empty($productId)) {
    header("Location: dashboard.php?page=products");
    exit;
}

// Find the product
$product = null;
foreach ($products as $p) {
    if ($p['id'] == $productId) {
        $product = $p;
        break;
    }
}

if (!$product) {
    echo "<div class='panel'><div class='muted'>Product not found.</div></div>";
    exit;
}

// Get reviews for this product
$reviews = get_product_reviews($productId);
$averageRating = get_average_rating($productId);
$ratingCount = get_rating_count($productId);

// Get user's review if exists
$userReview = get_user_review($name, $productId);
?>

<div class="panel">
    <!-- Product Header -->
    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee;">
        <img src="<?= imgPath($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
             style="width: 100px; height: 100px; object-fit: cover; border-radius: 12px; border: 2px solid #f8f9fa;">
        <div style="flex: 1;">
            <h2 style="margin: 0 0 8px 0; color: #2c3e50; font-weight: 600;"><?= htmlspecialchars($product['name']) ?></h2>
            <div style="color: #7f8c8d; font-size: 14px; margin-bottom: 12px;">
                <span style="background: #e8f5e9; padding: 4px 12px; border-radius: 20px; color: #27ae60;">
                    <?= htmlspecialchars($product['category']) ?> → <?= htmlspecialchars($product['subcategory']) ?>
                </span>
            </div>
            <div style="color: #34495e; font-size: 15px;">
                Price: <strong style="color: #e74c3c;">৳<?= number_format($product['price'], 2) ?></strong>
            </div>
        </div>
    </div>

    <!-- Rating Summary -->
    <div class="card" style="padding: 25px; margin-bottom: 25px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px;">
        <div style="display: flex; align-items: center; gap: 30px;">
            <div style="text-align: center;">
                <div style="font-size: 3.5em; font-weight: 700; line-height: 1;"><?= $averageRating ?></div>
                <div style="display: flex; justify-content: center; margin: 8px 0;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span style="color: <?= $i <= round($averageRating) ? '#ffd700' : '#ffffff80' ?>; font-size: 1.2em;">★</span>
                    <?php endfor; ?>
                </div>
                <div style="font-size: 0.9em; opacity: 0.9;">Based on <?= $ratingCount ?> review<?= $ratingCount !== 1 ? 's' : '' ?></div>
            </div>
            
            <div style="flex: 1;">
                <h3 style="margin: 0 0 15px 0; color: white;">Customer Ratings</h3>
                
                <?php if ($ratingCount > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <?php for ($stars = 5; $stars >= 1; $stars--): ?>
                        <?php
                        $starCount = 0;
                        foreach ($reviews as $review) {
                            if ($review['rating'] == $stars) {
                                $starCount++;
                            }
                        }
                        $percentage = $ratingCount > 0 ? ($starCount / $ratingCount) * 100 : 0;
                        ?>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 70px; display: flex; gap: 4px;">
                                <span style="color: #ffd700;"><?= $stars ?>★</span>
                            </div>
                            <div style="flex: 1; height: 8px; background: rgba(255,255,255,0.3); border-radius: 4px; overflow: hidden;">
                                <div style="height: 100%; width: <?= $percentage ?>%; background: #ffd700; border-radius: 4px; transition: width 0.3s ease;"></div>
                            </div>
                            <div style="width: 60px; text-align: right; font-size: 0.9em; color: rgba(255,255,255,0.9);">
                                <?= $starCount ?> (<?= round($percentage) ?>%)
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
                <?php else: ?>
                    <div style="color: rgba(255,255,255,0.8); font-style: italic;">No ratings yet</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Review Form -->
    <div class="card" style="padding: 25px; margin-bottom: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
        <h3 style="margin: 0 0 20px 0; color: #2c3e50; display: flex; align-items: center; gap: 10px;">
            <span style="background: #3498db; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px;">✏️</span>
            <?= $userReview ? 'Edit Your Review' : 'Share Your Experience' ?>
        </h3>
        
        <form method="post">
            <input type="hidden" name="action" value="add_review">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($productId) ?>">
            
            <!-- Rating -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #34495e;">Your Rating</label>
                <div style="display: flex; gap: 5px;" id="starRating">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label style="cursor: pointer; transition: transform 0.2s ease;" 
                               onmouseover="hoverStars(<?= $i ?>)" 
                               onmouseout="resetStars()">
                            <input type="radio" name="rating" value="<?= $i ?>" 
                                <?= $userReview && $userReview['rating'] == $i ? 'checked' : '' ?> 
                                style="display: none;">
                            <span style="font-size: 32px; color: <?= ($userReview && $i <= $userReview['rating']) ? '#f39c12' : '#ddd' ?>;">★</span>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Comment -->
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 10px; font-weight: 600; color: #34495e;">Your Review</label>
                <textarea name="comment" placeholder="Share your honest thoughts about this product. What did you like? What could be better?" 
                    style="width: 100%; padding: 15px; border: 2px solid #ecf0f1; border-radius: 10px; min-height: 120px; font-family: inherit; font-size: 14px; transition: border-color 0.3s ease;"
                    onfocus="this.style.borderColor='#3498db'"
                    onblur="this.style.borderColor='#ecf0f1'"><?= $userReview ? htmlspecialchars($userReview['comment']) : '' ?></textarea>
            </div>
            
            <!-- Buttons -->
            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="submit" class="btn" style="background: #27ae60; padding: 12px 24px; border-radius: 8px;">
                    <?= $userReview ? 'Update Review' : 'Submit Review' ?>
                </button>
                
                <?php if ($userReview): ?>
                <button type="button" id="deleteReviewBtn" class="btn ghost" style="padding: 12px 24px; border-radius: 8px; color: #e74c3c; border-color: #e74c3c;">
                    Delete Review
                </button>
                <?php endif; ?>
            </div>
        </form>
        
        <?php if ($userReview): ?>
        <form method="post" id="deleteReviewForm" style="display: none;">
            <input type="hidden" name="action" value="delete_review">
            <input type="hidden" name="product_id" value="<?= htmlspecialchars($productId) ?>">
            <input type="hidden" name="review_user" value="<?= htmlspecialchars($name) ?>">
        </form>
        <?php endif; ?>
    </div>

    <!-- Reviews List -->
    <div>
        <h3 style="margin: 0 0 20px 0; color: #2c3e50; display: flex; align-items: center; gap: 10px;">
            <span style="background: #e74c3c; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px;">👥</span>
            Customer Reviews (<?= $ratingCount ?>)
        </h3>
        
        <?php if (empty($reviews)): ?>
            <div style="text-align: center; padding: 40px 20px; color: #7f8c8d; background: #f8f9fa; border-radius: 12px;">
                <div style="font-size: 48px; margin-bottom: 16px;">📝</div>
                <h4 style="margin: 0 0 12px 0; color: #34495e;">No reviews yet</h4>
                <p style="margin: 0; color: #95a5a6;">Be the first to share your experience with this product!</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <?php foreach ($reviews as $review): ?>
                <div class="card" style="padding: 20px; border-radius: 12px; background: white; border: 1px solid #ecf0f1; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                    <!-- Review Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 40px; height: 40px; background: #3498db; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 16px;">
                                <?= strtoupper(substr($review['user'], 0, 1)) ?>
                            </div>
                            <div>
                                <div style="font-weight: 600; color: #2c3e50;"><?= htmlspecialchars($review['user']) ?></div>
                                <div style="color: #7f8c8d; font-size: 13px;"><?= date('M j, Y', strtotime($review['date'])) ?></div>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 3px;">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span style="color: <?= $i <= $review['rating'] ? '#f39c12' : '#ddd' ?>; font-size: 16px;">★</span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <!-- Review Content -->
                    <div style="color: #2c3e50; line-height: 1.6; margin-bottom: 16px; padding: 0 8px;">
                        <?= nl2br(htmlspecialchars($review['comment'])) ?>
                    </div>
                    
                    <!-- Actions -->
                    <?php if ($review['user'] === $name || is_admin()): ?>
                    <div style="text-align: right; border-top: 1px solid #ecf0f1; padding-top: 12px;">
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="delete_review">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($productId) ?>">
                            <input type="hidden" name="review_user" value="<?= htmlspecialchars($review['user']) ?>">
                            <button type="submit" class="btn small ghost" 
                                    style="color: #e74c3c; border-color: #e74c3c; padding: 6px 12px; font-size: 13px;"
                                    onclick="return confirm('Are you sure you want to delete this review?');">
                                🗑️ Delete
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Star rating functionality
function hoverStars(rating) {
    const stars = document.querySelectorAll('#starRating span');
    stars.forEach((star, index) => {
        star.style.color = index < rating ? '#f39c12' : '#ddd';
    });
}

function resetStars() {
    const checkedRating = document.querySelector('input[name="rating"]:checked');
    const currentRating = checkedRating ? parseInt(checkedRating.value) : 0;
    const stars = document.querySelectorAll('#starRating span');
    
    stars.forEach((star, index) => {
        star.style.color = index < currentRating ? '#f39c12' : '#ddd';
    });
}

// Initialize stars on page load
document.addEventListener('DOMContentLoaded', function() {
    resetStars();
});

// Click to select rating
document.querySelectorAll('#starRating input').forEach(input => {
    input.addEventListener('change', function() {
        resetStars();
    });
});

// Delete review confirmation
document.getElementById('deleteReviewBtn')?.addEventListener('click', function() {
    if (confirm('Are you sure you want to delete your review? This action cannot be undone.')) {
        document.getElementById('deleteReviewForm').submit();
    }
});
</script>

<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1) !important;
}

.btn {
    transition: all 0.3s ease;
    cursor: pointer;
    border: none;
    font-weight: 600;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn.ghost:hover {
    background: #e74c3c;
    color: white;
}
</style>