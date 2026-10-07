<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Repositories\CourseRepository;
use App\Helpers\Sanitizer;
use App\Helpers\Auth;

$courseRepo = new CourseRepository();
$categories = $courseRepo->getCategories();

$selectedCat = !empty($_GET['category']) ? (int)$_GET['category'] : null;
$selectedDiff = !empty($_GET['difficulty']) ? trim((string)$_GET['difficulty']) : null;
$search = !empty($_GET['q']) ? trim((string)$_GET['q']) : null;

$courses = $courseRepo->getAllPublished($selectedCat, $selectedDiff, $search);

$pageTitle = 'Explore Courses';
$pageDesc = 'Discover structured technical video courses on AliStack Learner, complete with AI tutoring and verified assessments.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border); padding: 48px 0;">
    <div class="container">
        <div style="font-size: 13px; font-weight: 700; color: var(--primary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 6px;">
            STRUCTURED CURRICULUM
        </div>
        <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: 8px;">Explore Technical Courses</h1>
        <p style="color: var(--text-muted); font-size: 15px; margin: 0; max-width: 620px;">
            Browse structured courses engineered to eliminate algorithmic rabbit holes and build verified competencies through practice and testing.
        </p>
    </div>
</div>

<div class="container" style="padding: 40px 24px 80px;">
    <!-- Filter & Search Bar -->
    <div class="card" style="padding: 20px 24px; margin-bottom: 36px;">
        <form method="GET" action="<?= baseUrl('courses.php') ?>" style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 12px; flex-wrap: wrap; flex: 1; min-width: 280px;">
                <div class="input-icon-wrap" style="flex: 1; min-width: 240px; max-width: 360px;">
                    <i class="bi bi-search input-icon"></i>
                    <input type="text" name="q" value="<?= Sanitizer::e($search ?? '') ?>" placeholder="Search by title or topic..." class="form-control">
                </div>
                
                <select name="category" class="form-select" style="max-width: 220px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($selectedCat === (int)$cat['id']) ? 'selected' : '' ?>>
                            <?= Sanitizer::e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="difficulty" class="form-select" style="max-width: 180px;" onchange="this.form.submit()">
                    <option value="">All Levels</option>
                    <option value="beginner" <?= ($selectedDiff === 'beginner') ? 'selected' : '' ?>>Beginner</option>
                    <option value="intermediate" <?= ($selectedDiff === 'intermediate') ? 'selected' : '' ?>>Intermediate</option>
                    <option value="advanced" <?= ($selectedDiff === 'advanced') ? 'selected' : '' ?>>Advanced</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel"></i> Filter
                </button>
                <?php if ($selectedCat || $selectedDiff || $search): ?>
                    <a href="<?= baseUrl('courses.php') ?>" class="btn btn-outline btn-sm">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Courses Grid -->
    <?php if (empty($courses)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-search"></i>
            </div>
            <div class="empty-state-title">No courses match your criteria</div>
            <div class="empty-state-desc">
                We could not find any courses matching your filter query. Try clearing your filters or searching for different topics.
            </div>
            <a href="<?= baseUrl('courses.php') ?>" class="btn btn-outline btn-sm">Reset All Filters</a>
        </div>
    <?php else: ?>
        <div class="grid grid-3">
            <?php foreach ($courses as $c): ?>
                <div class="course-card">
                    <div class="course-thumbnail-wrap">
                        <?php if (!empty($c['thumbnail'])): ?>
                            <img src="<?= baseUrl('assets/images/' . $c['thumbnail']) ?>" alt="Thumbnail" class="course-thumbnail-img" onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=500&q=80';">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; background: #0F172A; display: flex; align-items: center; justify-content: center; color: #FFFFFF;">
                                <i class="bi bi-play-circle-fill" style="font-size: 48px; color: var(--primary);"></i>
                            </div>
                        <?php endif; ?>
                        <span class="badge badge-primary course-difficulty-badge">
                            <?= ucfirst(Sanitizer::e($c['difficulty'])) ?>
                        </span>
                    </div>

                    <div class="course-card-content">
                        <div class="course-card-category"><?= Sanitizer::e($c['category_name'] ?? 'Technical Course') ?></div>
                        <h3 class="course-card-title">
                            <a href="<?= baseUrl('course-details.php?slug=' . urlencode($c['slug'])) ?>" style="color: var(--text-dark);">
                                <?= Sanitizer::e($c['title']) ?>
                            </a>
                        </h3>
                        <p class="course-card-desc"><?= Sanitizer::e($c['short_desc']) ?></p>

                        <div class="course-card-meta">
                            <span class="course-meta-item"><i class="bi bi-clock"></i> <?= Sanitizer::e($c['estimated_duration']) ?></span>
                            <span class="course-meta-item"><i class="bi bi-collection-play"></i> <?= (int)$c['lesson_count'] ?> Lessons</span>
                        </div>

                        <div style="margin-top: 18px;">
                            <a href="<?= baseUrl('course-details.php?slug=' . urlencode($c['slug'])) ?>" class="btn btn-primary" style="width: 100%;">
                                View Course & Syllabus <i class="bi bi-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
