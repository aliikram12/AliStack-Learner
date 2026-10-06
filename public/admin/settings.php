<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/app/autoload.php';

use App\Repositories\SettingRepository;
use App\Helpers\Sanitizer;
use App\Helpers\Csrf;
use App\Helpers\Auth;

Auth::requireSuperAdmin();

$settingRepo = new SettingRepository();
$pdo = getDbConnection();

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

    // Only update API key if provided to prevent accidental wiping
    if (!empty($_POST['ai_api_key'])) {
        $settingsToUpdate['ai_api_key'] = trim((string)$_POST['ai_api_key']);
    }

    foreach ($settingsToUpdate as $k => $v) {
        $settingRepo->set($k, $v);
    }

    $settingRepo->logAudit(Auth::id(), 'PLATFORM_SETTINGS_UPDATE', 'platform_settings', null, 'Super Admin updated platform and AI settings');
    $_SESSION['flash_success'] = 'Platform settings updated successfully.';
    header('Location: ' . baseUrl('admin/settings.php'));
    exit;
}

$allSettings = $settingRepo->getAll();

$pageTitle = 'Platform Settings';
require_once dirname(__DIR__, 2) . '/templates/layouts/admin-layout.php';
?>

<div class="admin-page-header">
    <div>
        <h1 style="font-size: 1.75rem; margin-bottom: 4px;">Platform Configuration</h1>
        <p style="font-size: 13px; color: var(--muted); margin: 0;">Configure platform branding, AgentRouter AI Tutor parameters, and certification thresholds.</p>
    </div>
</div>

<div class="card" style="padding: 36px; max-width: 900px;">
    <form method="POST" action="<?= baseUrl('admin/settings.php') ?>">
        <?= Csrf::field() ?>

        <!-- Section 1: Branding -->
        <h3 style="font-size: 1.25rem; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            <i class="bi bi-palette"></i> Platform Branding
        </h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 28px;">
            <div class="form-group">
                <label class="form-label" for="site_name">Platform Name</label>
                <input type="text" id="site_name" name="site_name" class="form-control" value="<?= Sanitizer::e($allSettings['site_name'] ?? 'AliStack Learner') ?>">
            </div>

            <div class="form-group">
                <label class="form-label" for="parent_brand">Parent Brand</label>
                <input type="text" id="parent_brand" name="parent_brand" class="form-control" value="<?= Sanitizer::e($allSettings['parent_brand'] ?? 'AliStack') ?>">
            </div>

            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label" for="site_tagline">Platform Tagline</label>
                <input type="text" id="site_tagline" name="site_tagline" class="form-control" value="<?= Sanitizer::e($allSettings['site_tagline'] ?? '') ?>">
            </div>
        </div>

        <!-- Section 2: AgentRouter AI Tutor -->
        <h3 style="font-size: 1.25rem; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            <i class="bi bi-robot"></i> AgentRouter AI Tutor Configuration
        </h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 28px;">
            <div class="form-group">
                <label class="form-label" for="ai_provider_endpoint">AgentRouter API Endpoint</label>
                <input type="text" id="ai_provider_endpoint" name="ai_provider_endpoint" class="form-control" value="<?= Sanitizer::e($allSettings['ai_provider_endpoint'] ?? 'https://agentrouter.org/v1/chat/completions') ?>" required>
                <span style="font-size: 11px; color: var(--muted); margin-top: 2px; display: block;">Standard OpenAI-compatible endpoint.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="ai_model_name">AI Model Identifier</label>
                <input type="text" id="ai_model_name" name="ai_model_name" class="form-control" value="<?= Sanitizer::e($allSettings['ai_model_name'] ?? 'gpt-4o-mini') ?>" required>
                <span style="font-size: 11px; color: var(--muted); margin-top: 2px; display: block;">e.g. gpt-4o-mini, claude-3-5-sonnet, etc.</span>
            </div>

            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label" for="ai_api_key">AgentRouter Server API Key</label>
                <input type="password" id="ai_api_key" name="ai_api_key" class="form-control" placeholder="<?= !empty($allSettings['ai_api_key']) ? '•••••••••••••••••••••••••••••••• (Configured. Leave blank to preserve)' : 'Enter sk-agentrouter-api-key...' ?>">
                <span style="font-size: 11px; color: var(--muted); margin-top: 2px; display: block;">Stored securely on the server. Never sent to student browsers or public responses.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="ai_daily_limit_per_user">Daily Prompts Quota per Student</label>
                <input type="number" id="ai_daily_limit_per_user" name="ai_daily_limit_per_user" class="form-control" value="<?= Sanitizer::e($allSettings['ai_daily_limit_per_user'] ?? '50') ?>" min="1" max="500">
            </div>
        </div>

        <!-- Section 3: Assessment & Certification -->
        <h3 style="font-size: 1.25rem; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
            <i class="bi bi-award"></i> Assessment & Completion Policies
        </h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 28px;">
            <div class="form-group">
                <label class="form-label" for="certificate_threshold">Default Passing Score % for Certificate</label>
                <input type="number" id="certificate_threshold" name="certificate_threshold" class="form-control" value="<?= Sanitizer::e($allSettings['certificate_threshold'] ?? '70.00') ?>" step="0.1" min="1" max="100">
            </div>

            <div class="form-group">
                <label class="form-label" for="max_upload_size_mb">Max Resource Upload Size (MB)</label>
                <input type="number" id="max_upload_size_mb" name="max_upload_size_mb" class="form-control" value="<?= Sanitizer::e($allSettings['max_upload_size_mb'] ?? '10') ?>" min="1" max="100">
            </div>

            <div class="form-group" style="grid-column: span 2;">
                <label class="form-check">
                    <input type="checkbox" name="require_lesson_completion_for_test" value="1" <?= (!empty($allSettings['require_lesson_completion_for_test']) && $allSettings['require_lesson_completion_for_test'] === '1') ? 'checked' : '' ?>>
                    <span>Require students to complete 100% of course lessons before the final assessment is unlocked.</span>
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">
            <i class="bi bi-save"></i> Save Platform Settings
        </button>
    </form>
</div>

<?php require_once dirname(__DIR__, 2) . '/templates/layouts/admin-footer.php'; ?>
