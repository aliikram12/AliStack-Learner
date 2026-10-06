<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\CertificateRepository;
use App\Repositories\SettingRepository;

class CertificateService {
    protected CertificateRepository $certRepo;
    protected SettingRepository $settingRepo;

    public function __construct() {
        $this->certRepo = new CertificateRepository();
        $this->settingRepo = new SettingRepository();
    }

    public function verifyCertificate(string $searchQuery): ?array {
        $searchQuery = trim($searchQuery);
        if (empty($searchQuery)) {
            return null;
        }

        // Try verification code first
        $cert = $this->certRepo->findByVerificationCode($searchQuery);
        if ($cert) {
            return $cert;
        }

        // Try certificate number
        return $this->certRepo->findByCertificateNumber($searchQuery);
    }

    public function renderCertificateHtml(array $cert): string {
        $certNumber = htmlspecialchars($cert['certificate_number'], ENT_QUOTES, 'UTF-8');
        $studentName = htmlspecialchars($cert['student_name'], ENT_QUOTES, 'UTF-8');
        $courseTitle = htmlspecialchars($cert['course_title'], ENT_QUOTES, 'UTF-8');
        $score = number_format((float)$cert['score_percentage'], 1);
        $issueDate = date('F j, Y', strtotime($cert['issued_at']));
        $verificationUrl = baseUrl("verify-certificate.php?code=" . urlencode($cert['verification_code']));

        ob_start();
        include dirname(__DIR__, 2) . '/templates/certificates/template.php';
        return ob_get_clean();
    }
}
