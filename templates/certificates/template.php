<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate of Achievement - <?= $certNumber ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            background-color: #0F172A;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: #0F172A;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }

        .cert-actions {
            margin-bottom: 24px;
            display: flex;
            gap: 16px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-print {
            background: #2563EB;
            color: #FFFFFF;
        }
        .btn-print:hover { background: #1D4ED8; transform: translateY(-1px); }

        .btn-back {
            background: rgba(255, 255, 255, 0.1);
            color: #FFFFFF;
        }
        .btn-back:hover { background: rgba(255, 255, 255, 0.2); }

        .cert-container {
            width: 100%;
            max-width: 960px;
            background: #FFFFFF;
            padding: 48px 56px;
            border-radius: 12px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            position: relative;
            border: 12px solid #F1F5F9;
            outline: 2px solid #CBD5E1;
            background-image: 
                radial-gradient(circle at 100% 100%, rgba(37, 99, 235, 0.03) 0%, transparent 60%),
                radial-gradient(circle at 0% 0%, rgba(124, 58, 237, 0.03) 0%, transparent 60%);
        }

        .cert-inner-border {
            border: 2px dashed #94A3B8;
            padding: 36px 40px;
            border-radius: 8px;
            text-align: center;
            position: relative;
        }

        .cert-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 24px;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #2563EB, #7C3AED);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 20px;
        }

        .brand-name {
            font-family: 'Cinzel', serif;
            font-size: 22px;
            font-weight: 700;
            color: #0F172A;
            letter-spacing: 2px;
        }

        .cert-title {
            font-family: 'Cinzel', serif;
            font-size: 32px;
            font-weight: 800;
            color: #1E293B;
            letter-spacing: 4px;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .cert-subtitle {
            font-size: 14px;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 24px;
        }

        .cert-recipient-prefix {
            font-size: 15px;
            color: #64748B;
            font-style: italic;
            margin-bottom: 8px;
        }

        .cert-recipient {
            font-family: 'Cinzel', serif;
            font-size: 34px;
            font-weight: 700;
            color: #2563EB;
            border-bottom: 2px solid #E2E8F0;
            display: inline-block;
            padding: 0 40px 6px;
            margin-bottom: 20px;
        }

        .cert-statement {
            font-size: 15px;
            color: #334155;
            max-width: 680px;
            margin: 0 auto 20px;
            line-height: 1.6;
        }

        .cert-course {
            font-size: 24px;
            font-weight: 700;
            color: #0F172A;
            margin-bottom: 12px;
        }

        .cert-score-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #EFF6FF;
            color: #1D4ED8;
            font-weight: 700;
            font-size: 14px;
            padding: 6px 16px;
            border-radius: 999px;
            border: 1px solid #BFDBFE;
            margin-bottom: 36px;
        }

        .cert-footer {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            align-items: flex-end;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #E2E8F0;
        }

        .footer-col {
            text-align: center;
        }

        .signature-line {
            width: 140px;
            height: 1px;
            background: #94A3B8;
            margin: 0 auto 8px;
        }

        .signer-title {
            font-size: 12px;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .gold-seal {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: radial-gradient(circle, #FDE047 0%, #D97706 80%);
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
            border: 3px double #FFFFFF;
            color: #78350F;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .gold-seal i { font-size: 22px; margin-bottom: 2px; }

        .cert-meta {
            margin-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #64748B;
            border-top: 1px solid #F1F5F9;
            padding-top: 12px;
        }

        .verification-link {
            color: #2563EB;
            text-decoration: none;
            word-break: break-all;
        }

        @media print {
            body { background: #FFFFFF; padding: 0; }
            .cert-actions { display: none !important; }
            .cert-container {
                box-shadow: none;
                border: 4px solid #CBD5E1;
                max-width: 100%;
                width: 100%;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="cert-actions">
        <button onclick="window.print()" class="btn-action btn-print">
            <i class="bi bi-printer-fill"></i> Print / Download PDF
        </button>
        <a href="<?= baseUrl('certificates.php') ?>" class="btn-action btn-back">
            <i class="bi bi-arrow-left"></i> Back to Achievements
        </a>
    </div>

    <div class="cert-container">
        <div class="cert-inner-border">
            <div class="cert-brand">
                <div class="brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="brand-name">AliStack Learner</div>
            </div>

            <div class="cert-title">Certificate of Achievement</div>
            <div class="cert-subtitle">Official Verification & Skill Demonstration</div>

            <div class="cert-recipient-prefix">This is proudly presented to</div>
            <div class="cert-recipient"><?= $studentName ?></div>

            <p class="cert-statement">
                for demonstrating proficient technical mastery, rigorous lesson completion, and successfully passing the comprehensive final assessment for:
            </p>

            <div class="cert-course"><?= $courseTitle ?></div>

            <div>
                <span class="cert-score-badge">
                    <i class="bi bi-patch-check-fill"></i> Verified Score: <?= $score ?>%
                </span>
            </div>

            <div class="cert-footer">
                <div class="footer-col">
                    <div style="font-weight: 600; font-size: 13px; margin-bottom: 4px;"><?= $issueDate ?></div>
                    <div class="signature-line"></div>
                    <div class="signer-title">Date of Issuance</div>
                </div>

                <div class="footer-col">
                    <div class="gold-seal">
                        <i class="bi bi-award-fill"></i>
                        VERIFIED
                    </div>
                </div>

                <div class="footer-col">
                    <div style="font-family:'Cinzel',serif; font-weight: 700; font-size: 13px; color: #2563EB; margin-bottom: 4px;">AliStack Academic Board</div>
                    <div class="signature-line"></div>
                    <div class="signer-title">Director of Education</div>
                </div>
            </div>

            <div class="cert-meta">
                <div><strong>Certificate ID:</strong> <?= $certNumber ?></div>
                <div>
                    <strong>Verification:</strong> 
                    <a href="<?= $verificationUrl ?>" class="verification-link" target="_blank"><?= $verificationUrl ?></a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
