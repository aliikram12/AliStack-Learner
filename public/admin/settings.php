<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Repositories\SettingRepository;
use App\Helpers\Sanitizer;
use App\Helpers\Csrf;
use App\Helpers\Auth;

Auth::requireSuperAdmin();

$settingRepo = new SettingRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Csrf::checkOrAbort();

    $settingsToUpdate = [
        'site_name' => trim((string)($_POST['site_name'] ?? 'AliStack Learner')),
        'site_tagline' => trim((string)($_POST['site_tagline'] ?? '')),
        'parent_brand' => trim((string)($_POST['parent_brand'] ?? 'AliStack')),
        'certificate_threshold' => trim((string)($_POST['certificate_threshold'] ?? '70.00')),
        'require_lesson_completion_for_test' => !empty($_POST['require_lesson_completion_for_test']) ? '1' : '0',
        'ai_provider_endpoint' => trim((string)($_POST['ai_provider_endpoint'] ?? 'https://agentrouter.org/v1/chat/completions')),
        'ai_model_name' => trim((string)($_POST['ai_model_name'] ?? 'gpt-4o-mini')),
        'ai_daily_limit_per_user' => trim((string)($_POST['ai_daily_limit_per_user'] ?? '50')),
        'max_upload_size_mb' => trim((string)($_POST['max_upload_size_mb'] ?? '10'))
    ];

    // Only update API key if explicitly provided to avoid accidental wiping
    if (!empty($_POST['ai_api_key'])) {
        $settingsToUpdate['ai_api_key'] = trim((string)$_POST['ai_api_key']);
    }

    foreach ($settingsToUpdate as $k => $v) {
        $settingRepo->set($k, $v);
    }

    $settingRepo->logAudit(Auth::id(), 'PLATFORM_SETTINGS_UPDATE', 'platform_settings', null, 'Super Admin updated platform and AgentRouter AI settings');
    $_SESSION['flash_success'] = 'Platform and AI Tutor settings saved successfully.';
    header('Location: ' . baseUrl('admin/settings.php'));
    exit;
}

$allSettings = $settingRepo->getAll();
$hasApiKey = !empty($allSettings['ai_api_key']);

$pageTitle = 'Platform Settings';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <h1 style="font-size: 1.75rem; font-weight: 800; color: var(--dark); margin: 0 0 4px;">Platform Configuration</h1>
        <p style="font-size: 13.5px; color: var(--muted); margin: 0;">Configure global brand attributes, AgentRouter AI engine parameters, and assessment rules.</p>
    </div>
</div>

<div class="card" data-animate="fade-up" style="padding: 40px; max-width: 960px; border: 1px solid var(--border-color); border-radius: var(--radius-xl); box-shadow: var(--shadow-sm); background: #FFFFFF;">
    <form method="POST" action="<?= baseUrl('admin/settings.php') ?>" style="display: flex; flex-direction: column; gap: 36px;">
        <?= Csrf::field() ?>

        <!-- Section 1: Branding -->
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #EFF6FF; color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="bi bi-palette-fill"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark); margin: 0;">Platform & Brand Identity</h3>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="site_name" style="font-weight: 600; font-size: 13px;">Platform Name</label>
                    <input type="text" id="site_name" name="site_name" class="form-control" value="<?= Sanitizer::e($allSettings['site_name'] ?? 'AliStack Learner') ?>" required>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="parent_brand" style="font-weight: 600; font-size: 13px;">Parent Brand Identifier</label>
                    <input type="text" id="parent_brand" name="parent_brand" class="form-control" value="<?= Sanitizer::e($allSettings['parent_brand'] ?? 'AliStack') ?>" required>
                </div>

                <div class="form-group" style="margin: 0; grid-column: 1 / -1;">
                    <label class="form-label" for="site_tagline" style="font-weight: 600; font-size: 13px;">Platform Tagline</label>
                    <input type="text" id="site_tagline" name="site_tagline" class="form-control" value="<?= Sanitizer::e($allSettings['site_tagline'] ?? 'Learn with focus. Practice with purpose. Prove your skills.') ?>">
                </div>
            </div>
        </div>

        <!-- Section 2: AgentRouter AI Tutor -->
        <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #F5F3FF; color: var(--secondary); display: flex; align-items: center; justify-content: center; font-size: 16px;">
                        <i class="bi bi-robot"></i>
                    </div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark); margin: 0;">AgentRouter AI Tutor Engine</h3>
                </div>

                <div>
                    <?php if ($hasApiKey): ?>
                        <span class="badge badge-success" style="font-size: 12px; font-weight: 700; padding: 6px 12px;">
                            <i class="bi bi-check-circle-fill"></i> API Configured & Active
                        </span>
                    <?php else: ?>
                        <span class="badge badge-warning" style="font-size: 12px; font-weight: 700; padding: 6px 12px;">
                            <i class="bi bi-exclamation-triangle-fill"></i> API Key Pending
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="ai_provider_endpoint" style="font-weight: 600; font-size: 13px;">AgentRouter Gateway Endpoint</label>
                    <input type="text" id="ai_provider_endpoint" name="ai_provider_endpoint" class="form-control" value="<?= Sanitizer::e($allSettings['ai_provider_endpoint'] ?? 'https://agentrouter.org/v1/chat/completions') ?>" required>
                    <span style="font-size: 11.5px; color: var(--muted); margin-top: 4px; display: block;">OpenAI-compatible server endpoint.</span>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="ai_model_name" style="font-weight: 600; font-size: 13px;">Model Identifier</label>
                    <input type="text" id="ai_model_name" name="ai_model_name" class="form-control" value="<?= Sanitizer::e($allSettings['ai_model_name'] ?? 'gpt-4o-mini') ?>" required>
                    <span style="font-size: 11.5px; color: var(--muted); margin-top: 4px; display: block;">Default model: gpt-4o-mini or claude-3-5-sonnet.</span>
                </div>

                <div class="form-group" style="margin: 0; grid-column: 1 / -1;">
                    <label class="form-label" for="ai_api_key" style="font-weight: 600; font-size: 13px;">AgentRouter Server API Key</label>
                    <div style="position: relative;">
                        <input type="password" id="ai_api_key" name="ai_api_key" class="form-control" placeholder="<?= $hasApiKey ? '•••••••••••••••••••••••••••••••• (Configured. Leave blank to preserve)' : 'Enter sk-agentrouter-api-key...' ?>" autocomplete="new-password">
                    </div>
                    <span style="font-size: 12px; color: var(--muted); margin-top: 4px; display: flex; align-items: center; gap: 4px;">
                        <i class="bi bi-shield-check" style="color: var(--success);"></i>
                        <span>Stored strictly on the local database. The complete key is never exposed to client browsers or network logs.</span>
                    </span>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="ai_daily_limit_per_user" style="font-weight: 600; font-size: 13px;">Daily Prompts Quota per Student</label>
                    <input type="number" id="ai_daily_limit_per_user" name="ai_daily_limit_per_user" class="form-control" value="<?= Sanitizer::e($allSettings['ai_daily_limit_per_user'] ?? '50') ?>" min="1" max="1000">
                    <span style="font-size: 11.5px; color: var(--muted); margin-top: 4px; display: block;">Prevents rate-limit exhaustion and abuse.</span>
                </div>
            </div>
        </div>

        <!-- Section 3: Assessment Policies -->
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: #DCFCE7; color: #16A34A; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="bi bi-award-fill"></i>
                </div>
                <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--dark); margin: 0;">Assessment & Credentialing Rules</h3>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="certificate_threshold" style="font-weight: 600; font-size: 13px;">Certificate Passing Score %</label>
                    <input type="number" id="certificate_threshold" name="certificate_threshold" class="form-control" value="<?= Sanitizer::e($allSettings['certificate_threshold'] ?? '70.00') ?>" step="0.1" min="1" max="100">
                    <span style="font-size: 11.5px; color: var(--muted); margin-top: 4px; display: block;">Default standard is 70.0% for verified certificates.</span>
                </div>

                <div class="form-group" style="margin: 0;">
                    <label class="form-label" for="max_upload_size_mb" style="font-weight: 600; font-size: 13px;">Max File Upload Limit (MB)</label>
                    <input type="number" id="max_upload_size_mb" name="max_upload_size_mb" class="form-control" value="<?= Sanitizer::e($allSettings['max_upload_size_mb'] ?? '10') ?>" min="1" max="100">
                </div>

                <div class="form-group" style="margin: 0; grid-column: 1 / -1;">
                    <label class="form-check" style="cursor: pointer;">
                        <input type="checkbox" name="require_lesson_completion_for_test" value="1" <?= (!empty($allSettings['require_lesson_completion_for_test']) && $allSettings['require_lesson_completion_for_test'] === '1') ? 'checked' : '' ?>>
                        <span style="font-size: 13.5px; color: var(--dark); font-weight: 600;">
                            Require students to complete 100% of course lessons before the final assessment is unlocked.
                        </span>
                    </label>
                </div>
            </div>
        </div>

        <div>
            <button type="submit" class="btn btn-primary btn-lg" style="box-shadow: 0 4px 14px rgba(37,99,235,0.25);">
                <i class="bi bi-save-fill"></i> Save Platform Settings
            </button>
        </div>
    </form>
</div>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
