<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

use App\Helpers\Csrf;
use App\Helpers\Sanitizer;

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();
    $sent = true;
}

$pageTitle = 'Contact Us';
$pageDesc = 'Get in touch with the AliStack Learner academic and technical support team.';

require_once dirname(__DIR__) . '/templates/layouts/header.php';
?>

<div style="background: #FFFFFF; border-bottom: 1px solid var(--border-color); padding: 56px 0;">
    <div class="container" style="max-width: 760px; text-align: center;">
        <h1 style="font-size: 2.5rem; margin-bottom: 12px;">Get in Touch</h1>
        <p style="color: var(--muted); font-size: 15px;">
            Have questions about AliStack Learner, course playlists, certificates, or community moderation? We are here to help.
        </p>
    </div>
</div>

<div class="container" style="max-width: 800px; padding: 56px 24px;">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 48px;">
        <div>
            <h3 style="margin-bottom: 16px;">Contact Information</h3>
            <p style="font-size: 14px; color: var(--muted); line-height: 1.6; margin-bottom: 24px;">
                AliStack Learner is maintained by AliStack Engineering. For immediate technical support or questions regarding certificates, reach out to us.
            </p>

            <div style="display: flex; flex-direction: column; gap: 16px; font-size: 14px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="bi bi-envelope-fill" style="color: var(--primary); font-size: 18px;"></i>
                    <span>support@alistack.com</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="bi bi-building" style="color: var(--secondary); font-size: 18px;"></i>
                    <span>AliStack Education Division</span>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <i class="bi bi-shield-check" style="color: var(--success); font-size: 18px;"></i>
                    <span>Verified Academic Support</span>
                </div>
            </div>
        </div>

        <div class="card" style="padding: 28px;">
            <?php if ($sent): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>Thank you for reaching out! Your message has been received and our team will get back to you shortly.</div>
                </div>
            <?php else: ?>
                <form method="POST" action="<?= baseUrl('contact.php') ?>">
                    <?= Csrf::field() ?>
                    <div class="form-group">
                        <label class="form-label" for="name">Your Name</label>
                        <input type="text" id="name" name="name" class="form-control" required placeholder="John Doe">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required placeholder="name@example.com">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="subject">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" required placeholder="Question about course/assessment">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="message">Message</label>
                        <textarea id="message" name="message" class="form-control" required placeholder="How can we assist you?" rows="4"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-send-fill"></i> Send Message
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
