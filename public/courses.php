<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Repositories\CourseRepository;
use App\Helpers\Sanitizer;

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

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border-color); padding: 48px 0;">
    <div class="container">
        <h1 style="font-size: 2.25rem; margin-bottom: 8px;">Explore Technical Courses</h1>
        <p style="color: var(--muted); font-size: 15px; margin: 0;">
            Browse structured courses engineered to build real-world software engineering competencies.
        </p>
    </div>
</div>

<div class="container" style="padding: 40px 24px 80px;">
    <!-- Filter Bar -->
    <div class="card" style="padding: 20px; margin-bottom: 32px;">
        <form method="GET" action="<?= baseUrl('courses.php') ?>" style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
            <div style="display: flex; gap: 12px; flex-wrap: wrap; flex: 1; min-width: 280px;">
                <input type="text" name="q" value="<?= Sanitizer::e($search ?? '') ?>" placeholder="Search by title, instructor, or keywords..." class="form-control" style="max-width: 340px;">
                
                <select name="category" class="form-select" style="max-width: 220px;" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($selectedCat === (int)$cat['id']) ? 'selected' : '' ?>>
                            <?= Sanitizer::e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="difficulty" class="form-select" style="max-width: 180px;" onchange="this.form.submit()">
                    <option value="">All Difficulties</option>
                    <option value="beginner" <?= ($selectedDiff === 'beginner') ? 'selected' : '' ?>>Beginner</option>
                    <option value="intermediate" <?= ($selectedDiff === 'intermediate') ? 'selected' : '' ?>>Intermediate</option>
                    <option value="advanced" <?= ($selectedDiff === 'advanced') ? 'selected' : '' ?>>Advanced</option>
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-funnel"></i> Apply Filter
                </button>
                <?php if ($selectedCat || $selectedDiff || $search): ?>
                    <a href="<?= baseUrl('courses.php') ?>" class="btn btn-outline btn-sm">Reset</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Courses Grid -->
    <?php if (empty($courses)): ?>
        <div style="text-align: center; padding: 64px 20px; background: #FFFFFF; border: 1px dashed var(--border-color); border-radius: var(--radius-lg);">
            <div style="font-size: 48px; color: var(--muted-light); margin-bottom: 16px;"><i class="bi bi-search"></i></div>
            <h3 style="margin-bottom: 8px;">No courses found</h3>
            <p style="color: var(--muted); max-width: 440px; margin: 0 auto 20px;">
                We could not find any courses matching your filter criteria. Try clearing your filters or searching for different keywords.
            </p>
            <a href="<?= baseUrl('courses.php') ?>" class="btn btn-outline btn-sm">View All Courses</a>
        </div>
    <?php else: ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 28px;">
            <?php foreach ($courses as $c): ?>
                <div class="course-card">
                    <div class="course-thumb-wrap">
                        <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #1E293B, #0F172A); display: flex; align-items: center; justify-content: center; color: #FFFFFF;">
                            <i class="bi bi-play-circle-fill" style="font-size: 48px; opacity: 0.85; color: var(--primary);"></i>
                        </div>
                        <div class="course-thumb-overlay">
                            <span class="badge badge-primary"><?= ucfirst(Sanitizer::e($c['difficulty'])) ?></span>
                        </div>
                    </div>
                    <div class="course-body">
                        <div style="font-size: 12px; color: var(--muted); font-weight: 600; margin-bottom: 6px; text-transform: uppercase;">
                            <?= Sanitizer::e($c['category_name'] ?? 'Technical Course') ?>
                        </div>
                        <h3 class="course-title">
                            <a href="<?= baseUrl('course-details.php?slug=' . urlencode($c['slug'])) ?>">
                                <?= Sanitizer::e($c['title']) ?>
                            </a>
                        </h3>
                        <p class="course-desc"><?= Sanitizer::e($c['short_desc']) ?></p>
                        <div class="course-meta">
                            <span><i class="bi bi-clock"></i> <?= Sanitizer::e($c['estimated_duration']) ?></span>
                            <span><i class="bi bi-collection-play"></i> <?= (int)$c['lesson_count'] ?> Lessons</span>
                        </div>
                        <div style="margin-top: 16px;">
                            <a href="<?= baseUrl('course-details.php?slug=' . urlencode($c['slug'])) ?>" class="btn btn-primary" style="width: 100%;">
                                View Course Details
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
