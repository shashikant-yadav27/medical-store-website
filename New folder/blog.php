<?php
// blog.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

// In a real scenario, you'd fetch from DB. For now, we use placeholder data since the admin blog crud was just requested but not explicitly detailed as much as products.
$stmt = $pdo->query("SELECT b.*, a.name as author_name FROM blogs b JOIN admins a ON b.admin_id = a.id WHERE b.status = 1 ORDER BY b.id DESC");
$blogs = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="bg-light py-5 border-bottom mb-5">
    <div class="container text-center">
        <h1 class="fw-bold mb-3">Health & Wellness Blog</h1>
        <p class="lead text-muted mb-0">Stay updated with the latest medical news, health tips, and nutrition advice.</p>
    </div>
</div>

<div class="container pb-5">
    <div class="row g-4">
        <?php if(empty($blogs)): ?>
            <!-- Placeholder blogs if DB is empty -->
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <img src="https://via.placeholder.com/400x250/0d6efd/ffffff?text=Health+Tips" class="card-img-top" alt="Blog">
                    <div class="card-body p-4">
                        <span class="badge bg-primary mb-2">Nutrition</span>
                        <h5 class="card-title fw-bold">10 Essential Vitamins for Immunity</h5>
                        <p class="card-text text-muted">Discover the key vitamins and minerals your body needs to fight off infections and stay healthy all year round.</p>
                        <a href="#" class="btn btn-outline-primary mt-auto">Read More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <img src="https://via.placeholder.com/400x250/198754/ffffff?text=Medical+News" class="card-img-top" alt="Blog">
                    <div class="card-body p-4">
                        <span class="badge bg-success mb-2">Medical News</span>
                        <h5 class="card-title fw-bold">Understanding Diabetes Management</h5>
                        <p class="card-text text-muted">A comprehensive guide to managing blood sugar levels through diet, exercise, and proper medication.</p>
                        <a href="#" class="btn btn-outline-primary mt-auto">Read More</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <img src="https://via.placeholder.com/400x250/fd7e14/ffffff?text=Fitness" class="card-img-top" alt="Blog">
                    <div class="card-body p-4">
                        <span class="badge bg-warning text-dark mb-2">Fitness</span>
                        <h5 class="card-title fw-bold">Benefits of Daily 30-Min Walks</h5>
                        <p class="card-text text-muted">How incorporating a simple 30-minute walk into your daily routine can drastically improve cardiovascular health.</p>
                        <a href="#" class="btn btn-outline-primary mt-auto">Read More</a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach($blogs as $blog): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0">
                    <?php if($blog['image']): ?>
                        <img src="<?php echo SITE_URL; ?>assets/uploads/blogs/<?php echo $blog['image']; ?>" class="card-img-top object-fit-cover" height="250">
                    <?php else: ?>
                        <img src="https://via.placeholder.com/400x250?text=No+Image" class="card-img-top" alt="Blog">
                    <?php endif; ?>
                    <div class="card-body p-4 d-flex flex-column">
                        <span class="badge bg-primary mb-2 align-self-start"><?php echo htmlspecialchars($blog['category']); ?></span>
                        <h5 class="card-title fw-bold"><?php echo htmlspecialchars($blog['title']); ?></h5>
                        <p class="card-text text-muted flex-grow-1"><?php echo substr(strip_tags($blog['content']), 0, 100) . '...'; ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">By <?php echo htmlspecialchars($blog['author_name']); ?></small>
                            <a href="#" class="btn btn-sm btn-outline-primary">Read More</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
