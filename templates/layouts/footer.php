<?php
declare(strict_types=1);

use App\Helpers\Auth;
use App\Helpers\Sanitizer;

$adminTargetUrl = Auth::canAccessAdmin() ? baseUrl('admin/index.php') : baseUrl('admin/login.php');
?>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                        <div class="brand-icon" style="width: 34px; height: 34px; font-size: 1.05rem;">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <span style="font-family: var(--font-heading); font-weight: 800; font-size: 1.25rem; color: #FFFFFF; letter-spacing: -0.02em;">
                            AliStack Learner
                        </span>
                    </div>
                    <p style="color: #94A3B8; font-size: 14px; line-height: 1.6; max-width: 360px;">
                        Learn with focus. Practice with purpose. Prove your skills. A distraction-reduced educational environment engineered to eliminate video feed rabbit holes and reward verified mastery.
                    </p>
                    <div style="margin-top: 18px; font-size: 13px; color: #64748B;">
                        A proud product of <a href="<?= $adminTargetUrl ?>" class="brand-subtle-link" title="AliStack Platform Control" aria-label="AliStack">AliStack</a>
                    </div>
                </div>

                <div>
                    <div class="footer-col-title">Learning Platform</div>
                    <ul class="footer-links">
                        <li><a href="<?= baseUrl('courses.php') ?>">Explore Courses</a></li>
                        <li><a href="<?= baseUrl('how-it-works.php') ?>">How It Works</a></li>
                        <li><a href="<?= baseUrl('verify-certificate.php') ?>">Verify Certificate</a></li>
                        <li><a href="<?= baseUrl('community.php') ?>">Discussion Groups</a></li>
                    </ul>
                </div>

                <div>
                    <div class="footer-col-title">Company & Trust</div>
                    <ul class="footer-links">
                        <li><a href="<?= baseUrl('about.php') ?>">About AliStack</a></li>
                        <li><a href="<?= baseUrl('contact.php') ?>">Contact Support</a></li>
                        <li><a href="<?= baseUrl('privacy.php') ?>">Privacy Policy</a></li>
                        <li><a href="<?= baseUrl('terms.php') ?>">Terms of Service</a></li>
                    </ul>
                </div>

                <div>
                    <div class="footer-col-title">Verified Badges & Awards</div>
                    <p style="font-size: 13px; color: #94A3B8; line-height: 1.6;">
                        Earn verified certificates with 70%+ score on final assessments, or earn performance badges for 40%+ competency.
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px;">
                        <span class="badge badge-tier-silver"><i class="bi bi-award-fill"></i> Silver (60-69%)</span>
                        <span class="badge badge-tier-bronze"><i class="bi bi-award-fill"></i> Bronze (50-59%)</span>
                        <span class="badge badge-tier-starter"><i class="bi bi-award-fill"></i> Starter (40-49%)</span>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    &copy; <?= date('Y') ?> AliStack Learner. A proud product of <a href="<?= $adminTargetUrl ?>" class="brand-subtle-link" title="AliStack" aria-label="AliStack">AliStack</a>. All rights reserved.
                </div>
                <div style="display: flex; gap: 24px;">
                    <a href="<?= baseUrl('privacy.php') ?>">Privacy Policy</a>
                    <a href="<?= baseUrl('terms.php') ?>">Terms of Service</a>
                    <a href="<?= baseUrl('contact.php') ?>">Support Desk</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- App JavaScript Engine -->
    <script src="<?= assetUrl('js/app.js') ?>"></script>
    <?php if (!empty($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= assetUrl('js/' . $js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
