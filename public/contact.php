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

<div style="background: radial-gradient(circle at 50% 20%, rgba(37,99,235,0.06) 0%, rgba(248,250,252,1) 80%); border-bottom: 1px solid var(--border-color); padding: 72px 0;">
    <div class="container" style="max-width: 760px; text-align: center;">
        <div style="display: inline-flex; align-items: center; gap: 6px; background: var(--primary-light); color: var(--primary); font-size: 12px; font-weight: 700; padding: 4px 14px; border-radius: 9999px; margin-bottom: 16px; text-transform: uppercase;">
            <i class="bi bi-chat-dots-fill"></i> Academic Support
        </div>
        <h1 style="font-size: clamp(2.2rem, 4vw, 3rem); font-weight: 800; letter-spacing: -0.02em; margin-bottom: 14px; color: var(--dark);">
            Get in Touch
        </h1>
        <p style="color: var(--muted); font-size: 16px; line-height: 1.6; max-width: 600px; margin: 0 auto;">
            Have questions about AliStack Learner, course curricula, certificates, or community moderation? Our team is here to help.
        </p>
    </div>
</div>

<div class="container" style="max-width: 920px; padding: 64px 20px 96px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 48px; align-items: flex-start;">
        <div data-animate="fade-up">
            <h2 style="font-size: 1.5rem; font-weight: 800; color: var(--dark); margin: 0 0 16px;">Contact Information</h2>
            <p style="font-size: 14.5px; color: var(--muted); line-height: 1.7; margin-bottom: 32px;">
                AliStack Learner is maintained by AliStack Engineering. For immediate technical support or questions regarding certificate validation, reach out to us directly.
            </p>

            <div style="display: flex; flex-direction: column; gap: 20px; font-size: 14px;">
                <div class="card" style="padding: 16px 20px; border: 1px solid var(--border-color); border-radius: var(--radius-md); display: flex; align-items: center; gap: 14px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #EFF6FF; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase;">Support Email</div>
                        <div style="font-weight: 700; color: var(--dark);">support@alistack.com</div>
                    </div>
                </div>

                <div class="card" style="padding: 16px 20px; border: 1px solid var(--border-color); border-radius: var(--radius-md); display: flex; align-items: center; gap: 14px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #F5F3FF; color: var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bi bi-building"></i>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase;">Organization</div>
                        <div style="font-weight: 700; color: var(--dark);">AliStack Education Division</div>
                    </div>
                </div>

                <div class="card" style="padding: 16px 20px; border: 1px solid var(--border-color); border-radius: var(--radius-md); display: flex; align-items: center; gap: 14px;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 18px;">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <div style="font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase;">Academic Registry</div>
                        <div style="font-weight: 700; color: var(--dark);">Verified Credential Registry</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card" data-animate="fade-up" style="padding: 36px 32px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: var(--shadow-lg); background: #FFFFFF;">
            <?php if ($sent): ?>
                <div class="alert alert-success" style="padding: 24px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 12px;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 24px;">
                        <i class="bi bi-check2-circle"></i>
                    </div>
                    <h3 style="color: #14532D; font-size: 1.25rem; font-weight: 800; margin: 0;">Message Sent Successfully</h3>
                    <p style="color: #166534; font-size: 14px; margin: 0; line-height: 1.5;">Thank you for reaching out! Our academic support team will review your message and reply via email shortly.</p>
                </div>
            <?php else: ?>
                <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--dark); margin: 0 0 20px;">Send a Message</h3>
                <form method="POST" action="<?= baseUrl('contact.php') ?>" style="display: flex; flex-direction: column; gap: 18px;">
                    <?= Csrf::field() ?>
                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="name" style="font-weight: 600; font-size: 13px; color: var(--dark);">Your Name</label>
                        <input type="text" id="name" name="name" class="form-control" required placeholder="Alex Morgan">
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="email" style="font-weight: 600; font-size: 13px; color: var(--dark);">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control" required placeholder="alex@example.com">
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="subject" style="font-weight: 600; font-size: 13px; color: var(--dark);">Subject</label>
                        <input type="text" id="subject" name="subject" class="form-control" required placeholder="Inquiry regarding course or certificate">
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label class="form-label" for="message" style="font-weight: 600; font-size: 13px; color: var(--dark);">Message</label>
                        <textarea id="message" name="message" class="form-control" required placeholder="Describe your question or feedback..." rows="4"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 700; box-shadow: 0 4px 14px rgba(37,99,235,0.25);">
                        <i class="bi bi-send-fill"></i> Send Message
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once dirname(__DIR__) . '/templates/layouts/footer.php'; ?>
