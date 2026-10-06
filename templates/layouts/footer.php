    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div>
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
                        <div class="brand-icon" style="width: 32px; height: 32px; font-size: 1rem;">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <span style="font-weight: 800; font-size: 1.2rem; color: #FFFFFF;">AliStack Learner</span>
                    </div>
                    <p style="color: #94A3B8; font-size: 14px; max-width: 340px;">
                        Learn with focus. Practice with purpose. Prove your skills. An AI-powered learning environment engineered to eliminate video distractions and reward verified mastery.
                    </p>
                    <div style="margin-top: 16px; font-size: 13px; color: #64748B;">
                        A proud product of <strong>AliStack</strong>
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
                    <div class="footer-col-title">Verified Badges</div>
                    <p style="font-size: 13px; color: #94A3B8; line-height: 1.6;">
                        Earn industry-recognized certificates with 70%+ assessment performance, or performance badges for 40%+ competency.
                    </p>
                    <div style="display: flex; gap: 8px; margin-top: 12px;">
                        <span class="badge badge-tier-silver"><i class="bi bi-award-fill"></i> Silver</span>
                        <span class="badge badge-tier-bronze"><i class="bi bi-award-fill"></i> Bronze</span>
                        <span class="badge badge-tier-starter"><i class="bi bi-award-fill"></i> Starter</span>
                    </div>
                </div>
            </div>

            <div class="footer-bottom">
                <div>
                    &copy; <?= date('Y') ?> AliStack Learner. All rights reserved.
                </div>
                <div style="display: flex; gap: 20px;">
                    <a href="<?= baseUrl('privacy.php') ?>">Privacy</a>
                    <a href="<?= baseUrl('terms.php') ?>">Terms</a>
                    <a href="<?= baseUrl('contact.php') ?>">Support</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- App JavaScript -->
    <script src="<?= assetUrl('js/app.js') ?>"></script>
    <?php if (!empty($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= assetUrl('js/' . $js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
