# AliStack Learner
> **Complete AI-Powered Learning, Assessment & Community Platform**  
> *Powered by AliStack &bull; Learn with focus. Practice with purpose. Prove your skills.*

---

## 1. Executive Summary & Product Vision

**AliStack Learner** is a full-stack, enterprise-ready educational web platform designed to provide a distraction-reduced learning environment for technical and non-technical students. General video platforms are filled with distracting recommendation algorithms, clickbait feeds, and comment noise. AliStack Learner solves this by placing official YouTube educational playlists inside a distraction-reduced, structured classroom experience integrated with:

1. **Official YouTube IFrame Player API** for focused course streaming with lesson progress tracking, playback position memory, and personal notes.
2. **Context-Aware AI Tutor** powered by the **AgentRouter API**, providing on-demand conceptual explanations, Urdu/Roman Urdu support, code debugging, and revision questions.
3. **Course-Specific MCQ Assessments** with server-side validation, anti-tamper scoring, and timer support.
4. **Verified Credentials & Tiered Recognition**:
   - **AliStack Certificate of Completion** (>= 70%) with unique verification codes and a public verification portal.
   - **Tiered Performance Badges**: Silver (60%–69.99%), Bronze (50%–59.99%), and Starter (40%–49.99%).
5. **Moderated Community Discussion Groups** with membership request workflows, threaded replies, and real-time interaction.
6. **Unified In-App Notification Center** triggering real-time alerts for enrollments, assessment results, credentials, and community responses.
7. **Comprehensive Admin & Super Admin Control Center** for end-to-end administration of courses, lessons, questions, users, certificates, badges, AI parameters, and immutable audit logs.

---

## 2. Technology Stack & Constraints

- **Backend Runtime**: PHP 8.2+ (Strict typing, PDO, clean modular architecture).
- **Database**: MySQL 8.0+ or MariaDB 10.4+ (InnoDB engine, utf8mb4 encoding, foreign key constraints).
- **Frontend Core**: Semantic HTML5, Vanilla CSS3 (Custom design tokens, glass/card styling, responsive flexbox & grid), ES6+ Vanilla JavaScript.
- **Icons**: Bootstrap Icons (v1.11.3 CDN).
- **Video Playback**: YouTube IFrame Player API (Official embed compliant, no video scraping or rehosting).
- **AI Engine**: AgentRouter API (`https://agentrouter.org/v1/chat/completions` or configured endpoint) via server-side cURL with private token protection.
- **Framework Policy**: Built without React, Next.js, Node.js, or Laravel; 100% compatible with native PHP/MySQL hosting and local XAMPP environments.

---

## 3. Brand Identity & Design System

The platform features a clean, high-contrast, modern SaaS user interface built around light backgrounds, deep navy typography, and vibrant royal blue and violet accents.

### Design Tokens
| Token | Variable | Value | Usage |
| :--- | :--- | :--- | :--- |
| **Primary** | `--primary` | `#2563EB` | Buttons, active navigation, links, primary indicators |
| **Primary Dark** | `--primary-dark` | `#1D4ED8` | Hover states |
| **Secondary** | `--secondary` | `#7C3AED` | Badges, AI accents, achievement highlights |
| **Dark Text** | `--text-dark` | `#0F172A` | Headings, primary text |
| **Muted Text** | `--text-muted` | `#64748B` | Subheadings, metadata, captions |
| **Main Background** | `--bg-main` | `#F8FAFC` | Page background |
| **Card Background** | `--bg-card` | `#FFFFFF` | Containers, cards, tables, modal dialogs |
| **Border Color** | `--border` | `#E2E8F0` | Subtle structural division |
| **Success** | `--success` | `#16A34A` | Passed assessments, certificates, completed lessons |
| **Warning** | `--warning` | `#D97706` | Pending approvals, partial scores |
| **Error** | `--error` | `#DC2626` | Validation errors, failed assessments, suspensions |

---

## 4. Architectural Directory Structure

```text
alistack-learner/
├── app/                                # Core Application Layer (PSR-4 App\)
│   ├── autoload.php                    # Native PSR-4 Autoloader & Bootstrap
│   ├── Helpers/
│   │   ├── Auth.php                    # RBAC, session management & auth gates
│   │   ├── Csrf.php                    # Cryptographic CSRF tokens & validation
│   │   ├── Response.php                # JSON API responder with standardized envelope
│   │   └── Sanitizer.php               # XSS defense, YouTube ID/playlist parser, slugs
│   ├── Repositories/
│   │   ├── AssessmentRepository.php    # Assessments, MCQs, and student attempts
│   │   ├── CertificateRepository.php   # Certificates, badges, and verification records
│   │   ├── CommunityRepository.php     # Discussion groups, memberships, posts, replies
│   │   ├── CourseRepository.php        # Courses, categories, lessons, bookmarks & notes
│   │   ├── NotificationRepository.php  # User in-app notifications
│   │   ├── SettingRepository.php       # Platform settings, audit logging, system metrics
│   │   └── UserRepository.php          # Users, profiles, tokens, authentication queries
│   └── Services/
│       ├── AiService.php               # AgentRouter client, context builder & token limiter
│       ├── AssessmentService.php       # Anti-tamper grading engine & credential issuer
│       ├── AuthService.php             # User registration, verification & avatar handler
│       └── CertificateService.php      # Unique certificate number generator & printable templates
├── config/
│   ├── ai.php                          # AgentRouter model, tokens, rate limits & prompt
│   ├── app.php                         # Application name, URL, security & badge thresholds
│   └── database.php                    # PDO connection factory (UTF8MB4, prepared statements)
├── database/
│   ├── database.sql                    # 31 relational tables with indexes, constraints & seeds
│   └── seed-super-admin.php            # CLI utility to create or update Super Admin account
├── public/                             # Web Root (Publicly accessible directory)
│   ├── index.php                       # Homepage with real-time statistics & featured catalog
│   ├── about.php                       # About AliStack Learner
│   ├── how-it-works.php                # Methodology & platform workflow explanation
│   ├── courses.php                     # Searchable, filterable course catalog
│   ├── course-details.php              # Comprehensive syllabus, requirements & enrollment
│   ├── dashboard.php                   # Personalized student learning dashboard
│   ├── learning.php                    # Distraction-reduced YouTube classroom player
│   ├── assessment.php                  # Interactive timed MCQ examination engine
│   ├── results.php                     # Post-assessment breakdown, certificate/badge status
│   ├── certificates.php                # Student credentials gallery & print preview
│   ├── verify-certificate.php          # Public certificate verification portal
│   ├── community.php                   # Discussion groups, join requests & thread feed
│   ├── profile.php                     # Student profile, avatar upload & password updates
│   ├── login.php                       # Secure student/staff authentication
│   ├── register.php                    # Student account registration
│   ├── forgot-password.php             # Password reset request
│   ├── reset-password.php              # Secure reset token validation & password change
│   ├── contact.php                     # Contact inquiries
│   ├── privacy.php                     # Privacy policy
│   ├── terms.php                       # Terms of service
│   ├── admin/                          # Administrative Dashboard
│   │   ├── index.php                   # Control center overview & real metrics
│   │   ├── courses.php                 # Course management & YouTube playlist import
│   │   ├── course-edit.php             # Add / edit course details
│   │   ├── lessons.php                 # Lesson manager with playlist synchronization
│   │   ├── assessments.php             # Create & configure course assessments
│   │   ├── questions.php               # Author & import course-specific MCQs
│   │   ├── students.php                # User management & RBAC assignments
│   │   ├── certificates.php            # Certificate registry & revocation controls
│   │   ├── badges.php                  # Performance badge configuration & awarded gallery
│   │   ├── community.php               # Group moderation & join request approvals
│   │   ├── settings.php                # Platform, AI & YouTube API configuration
│   │   └── audit-logs.php              # Immutable administrator audit log records
│   ├── api/                            # Asynchronous RESTful JSON Endpoints
│   │   ├── ai/chat.php                 # Contextual AI tutor chat stream/response
│   │   ├── assessments/start.php       # Initialize timed test attempt
│   │   ├── assessments/submit.php      # Server-side grading & award triggers
│   │   ├── auth/login.php              # Login API
│   │   ├── auth/logout.php             # Logout API
│   │   ├── auth/register.php           # Registration API
│   │   ├── community/create-post.php   # Publish discussion thread
│   │   ├── community/create-reply.php  # Reply to thread
│   │   ├── community/request-join.php  # Submit group join request
│   │   ├── community/review-request.php# Approve / reject join request
│   │   ├── courses/enroll.php          # Enroll in course
│   │   ├── notes/save.php              # Autosave lesson notes
│   │   ├── notifications/mark-read.php # Mark notification read
│   │   └── progress/update.php         # Update lesson completion & resume timestamp
│   └── assets/
│       ├── css/
│       │   ├── app.css                 # Core design system & layout styles
│       │   ├── player.css              # Distraction-reduced player & AI sidebar styles
│       │   └── admin.css               # Control center tables, forms & metric widgets
│       └── js/
│           ├── app.js                  # Global application scripts, modals & alerts
│           ├── player.js               # YouTube IFrame API coordinator & notes autosave
│           ├── ai-tutor.js             # AI Tutor chat interface, quick prompts & copy
│           └── assessment.js           # MCQ examination timer & question navigation
├── templates/                          # Server-side UI Templates
│   ├── layouts/
│   │   ├── header.php                  # Top navigation & user notification drawer
│   │   ├── footer.php                  # Global footer with brand links
│   │   ├── admin-layout.php            # Administrative sidebar & header layout
│   │   └── admin-footer.php            # Admin scripts & closing tags
│   └── certificates/
│       └── template.php                # High-resolution print-ready certificate layout
├── storage/                            # Protected Storage (Not exposed publicly)
│   ├── logs/                           # System and error logs
│   ├── certificates/                   # Rendered certificate artifacts
│   └── uploads/                        # User avatars & course resources
├── .env.example                        # Template environment variables
├── .env                                # Local environment secrets (ignored by git)
├── .gitignore                          # Version control exclusions
├── composer.json                       # Optional Composer metadata & dependencies
└── README.md                           # Master documentation
```

---

## 5. Database Schema & Data Models

The database comprises **31 normalized tables** engineered with InnoDB engine support, strict foreign key constraints, indexes on lookup fields, and default UTC timestamps:

1. `users` — Student, moderator, and administrator accounts with password hashes, avatars, status, and login timestamps.
2. `roles` & `role_permissions` — Granular role definitions (`super_admin`, `admin`, `moderator`, `student`).
3. `password_reset_tokens` — Cryptographically secure tokens for password recovery.
4. `email_verification_tokens` — Account activation tokens.
5. `course_categories` — Technical & academic categories.
6. `courses` — Courses linked to YouTube playlists, difficulty levels, duration, and completion requirements.
7. `course_lessons` — Ordered lessons with YouTube video IDs, duration, objectives, and status.
8. `enrollments` — Student enrollment records with calculated progress percentages and completion dates.
9. `lesson_progress` — Per-lesson playback state, last watched timestamp, and completion status.
10. `lesson_notes` — Student personal notes with autosave debouncing.
11. `lesson_bookmarks` — Saved lesson bookmarks for quick revision.
12. `course_resources` — Admin-uploaded supplementary files and reference links.
13. `assessments` — Course-specific MCQ exams with passing thresholds, time limits, and attempt caps.
14. `assessment_questions` — Question bank with 4 randomized options, difficulty tags, and explanations.
15. `assessment_attempts` — Individual exam sessions tracking start time, submit time, and calculated score.
16. `assessment_answers` — Exact student answer choices recorded for tamper-proof audit trails.
17. `certificates` — Unique verified certificates generated upon achieving >= 70%.
18. `achievement_badges` — Tiered performance badges (Silver 60–69.99%, Bronze 50–59.99%, Starter 40–49.99%).
19. `student_achievements` — Awarded badge records linked to assessment attempts.
20. `ai_conversations` — Grouped contextual AI chat threads per user and course.
21. `ai_messages` — Full transcript of student queries and AI mentor responses.
22. `ai_usage_logs` — Token consumption tracking and per-user rate limiting metrics.
23. `discussion_groups` — Course-linked community forums.
24. `group_memberships` — Authorized member records with roles (`member`, `moderator`).
25. `group_join_requests` — Moderated membership requests (`pending`, `approved`, `rejected`).
26. `discussion_posts` — Forum threads with rich text support.
27. `discussion_replies` — Nested discussion comments.
28. `post_reactions` — Upvotes and helpful reactions.
29. `content_reports` — Moderation flag queue for objectionable content.
30. `notifications` — In-app alerts for system events and community replies.
31. `platform_settings` & `audit_logs` — Key-value platform configurations and administrative action history.

---

## 6. Installation & Local Setup (XAMPP Guide)

### Prerequisites
- **XAMPP for Windows** (PHP 8.2+ and MariaDB/MySQL).
- Web browser (Chrome, Edge, or Firefox).

### Step 1: Clone or Copy Project Files
Place the repository inside your XAMPP web root (or run directly using the PHP development server):
```powershell
# Default XAMPP location:
# C:\xampp\htdocs\alistack-learner
```

### Step 2: Configure Environment Variables
Copy `.env.example` to `.env` in the root directory:
```powershell
Copy-Item .env.example .env
```
Ensure database credentials match your local MySQL configuration:
```env
APP_NAME="AliStack Learner"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=alistack_learner
DB_USER=root
DB_PASS=

# AI Configuration (Supplied by platform owner)
AGENTROUTER_API_KEY=your_agentrouter_api_key_here
AGENTROUTER_API_URL=https://agentrouter.org/v1/chat/completions
AGENTROUTER_MODEL=gpt-4o-mini
```

### Step 3: Import Database Schema & Seed Data
1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open phpMyAdmin at `http://localhost/phpmyadmin/` or open a terminal.
3. Create the database `alistack_learner`.
4. Import `database/database.sql`:
```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS alistack_learner CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
& "C:\xampp\mysql\bin\mysql.exe" -u root alistack_learner < "database\database.sql"
```

### Step 4: Seed Super Admin Account
Run the automated seed script to generate the initial Super Administrator:
```powershell
& "C:\xampp\php\php.exe" database\seed-super-admin.php
```

**Default Administrative Credentials**:
- **Email**: `admin@alistack.com`
- **Password**: `Admin@AliStack2026!`
- **Role**: `super_admin`

### Step 5: Start Local Web Server
You can serve the application using PHP's built-in development server pointing directly to the `public/` directory:
```powershell
& "C:\xampp\php\php.exe" -S localhost:8000 -t public
```
Visit the platform in your browser at:
`http://localhost:8000`

*(Or if accessed via Apache at `http://localhost/alistack-learner/`, root redirects automatically route into `/public/`).*

---

## 7. Key Workflows & Features

### Workflow A: Distraction-Reduced Learning Player
- Students navigate to **Courses**, enroll with one click, and launch the **Learning Player** (`learning.php?course_id=1`).
- The player initializes the **YouTube IFrame Player API** inside a clean, distraction-free container devoid of external video recommendations or trending rabbit holes.
- As the video plays, student playback timestamps and completion status automatically synchronize to the database (`api/progress/update.php`).
- Students write personal notes in the integrated sidebar; changes autosave with debouncing.
- Next/Previous lesson buttons enable seamless progression through the playlist.

### Workflow B: AliStack AI Tutor
- Located as a floating widget on the learning player, clicking opens the AI mentor interface.
- Context is automatically injected server-side: course title, category, active lesson title, lesson objectives, and student query.
- The backend communicates securely with the configured **AgentRouter API** using PHP cURL.
- **Security Guarantee**: The `AGENTROUTER_API_KEY` is loaded strictly from environment configuration on the server. No secrets or tokens are ever exposed to browser JavaScript or client headers.
- Quick prompt actions include: *Explain This Concept*, *Summarize Lesson*, *Give Me an Example*, *Explain in Roman Urdu*, and *Help Me Debug*.

### Workflow C: MCQ Assessment & Anti-Tamper Grading
- Assessments require completion of course lessons before unlocking.
- The exam interface (`assessment.php`) provides a countdown timer, dynamic question palette, and one-question-at-a-time or full-list review.
- Upon submission, grading occurs strictly on the server (`api/assessments/submit.php`). Answers submitted by the client are cross-referenced with database answer keys. Client-submitted scores are never accepted.
- Scores and attempt histories are immutably stored in `assessment_attempts` and `assessment_answers`.

### Workflow D: Certificates & Performance Badges
Following server grading, achievements are immediately computed:
- **Score >= 70%**: Qualifies for the **AliStack Certificate of Completion**. A unique, non-guessable verification identifier (e.g. `ALISTACK-CERT-2026-XXXXX`) is generated and registered in the database.
- **Score 60% – 69.99%**: Awards the **Silver-Level Performance Badge**.
- **Score 50% – 59.99%**: Awards the **Bronze-Level Performance Badge**.
- **Score 40% – 49.99%**: Awards the **Starter-Level Performance Badge**.
- **Score < 40%**: No credential awarded; student is encouraged to review lessons and retake the test.
- Certificates can be viewed and printed via `certificates.php` or verified publicly without login at `verify-certificate.php`.

### Workflow E: Community Discussion Groups
- Students browse course-linked discussion groups at `community.php`.
- Joining a group submits a membership request reviewed by administrators or group moderators.
- Once approved, members create threads, post replies, and receive notifications for community responses.

### Workflow F: Admin & Super Admin Control Center
- Accessible at `/admin/index.php` for authorized staff.
- Includes full CRUD management for courses, lessons, questions, assessments, users, and certificates.
- Administrators can paste YouTube playlist URLs (e.g., `https://www.youtube.com/playlist?list=PL...`), and the system automatically extracts and stores playlist IDs.
- Super Admins can update platform settings (passing thresholds, badge limits, AI system prompts) and inspect the **System Audit Log** (`admin/audit-logs.php`).

---

## 8. Security Specifications

1. **SQL Injection Defense**: 100% of database interactions utilize **PDO prepared statements** with strict parameter binding. No user input is directly concatenated into SQL queries.
2. **Cross-Site Scripting (XSS)**: All user-supplied output is escaped using `Sanitizer::e()` (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`).
3. **Cross-Site Request Forgery (CSRF)**: All state-changing POST requests require cryptographically secure CSRF tokens validated via `Csrf::checkOrAbort()`.
4. **Authentication & Password Hashing**: Passwords are encrypted using PHP's native `password_hash()` with `PASSWORD_BCRYPT`. Sessions are regenerated on login to prevent session fixation.
5. **Role-Based Access Control (RBAC)**: Centralized authorization gates (`Auth::requireRole()`, `Auth::requireAdmin()`, `Auth::requireAuth()`) verify user permissions on every request.
6. **API Key Security**: The AgentRouter AI secret is kept in private server-side `.env` storage, never transmitted across browser channels or included in client scripts.
7. **Rate Limiting**: AI queries and login attempts enforce rate limits and error logging.
8. **Audit Logging**: Privileged administrative actions (user suspensions, badge threshold changes, certificate revocations) write immutable records to `audit_logs`.

---

## 9. Production Deployment Checklist

When deploying to a Linux production server (Nginx / Apache):
1. **Web Root Configuration**: Point the web server's `DocumentRoot` directly to the `public/` directory so that `app/`, `config/`, `database/`, and `storage/` remain outside public HTTP reach.
2. **HTTPS / SSL**: Enforce TLS 1.3 encryption across all routes.
3. **Environment Secrets**: Create a secure production `.env` with `APP_ENV=production` and `APP_DEBUG=false`.
4. **PHP Settings (`php.ini`)**:
   ```ini
   display_errors = Off
   log_errors = On
   error_log = /path/to/storage/logs/php_errors.log
   session.cookie_httponly = 1
   session.cookie_secure = 1
   session.cookie_samesite = Lax
   ```
5. **Storage Permissions**: Ensure `storage/` is writable by the web server user (`chown -R www-data:www-data storage/`).

---

## 10. Acceptance & Quality Assurance Verification

| Workflow | Status | Verification Detail |
| :--- | :---: | :--- |
| **Workflow A: Student Registration & Auth** | PASS | Full validation, bcrypt hashing, session regeneration, dashboard metrics |
| **Workflow B: Course Creation & Playlists** | PASS | YouTube playlist extraction, lesson management, course catalog filter |
| **Workflow C: Distraction-Reduced Player** | PASS | YouTube IFrame Player API, lesson progress saving, personal notes autosave |
| **Workflow D: AgentRouter AI Tutor** | PASS | Contextual prompt injection, private server key proxy, quick actions |
| **Workflow E: Assessment Engine** | PASS | Timed exam runner, server-side anti-tamper grading, attempt logging |
| **Workflow F: Certificates & Badges** | PASS | Thresholds (70% Cert, 60% Silver, 50% Bronze, 40% Starter), public verification |
| **Workflow G: Discussion Groups** | PASS | Join request moderation, thread creation, comment replies, notifications |
| **Workflow H: Application Security** | PASS | PDO prepared statements, CSRF protection, RBAC checks, audit logging |

---

## 11. License & Brand Attribution

AliStack Learner is developed and maintained under the **AliStack** umbrella.  
&copy; 2026 AliStack. All rights reserved.
