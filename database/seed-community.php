<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/autoload.php';

$pdo = getDbConnection();

echo "Seeding Community Members & Discussions...\n";

// Ensure memberships exist
$memberships = [
    ['group_id' => 1, 'user_id' => 1, 'role' => 'moderator', 'status' => 'active'],
    ['group_id' => 1, 'user_id' => 2, 'role' => 'member', 'status' => 'active'],
    ['group_id' => 1, 'user_id' => 3, 'role' => 'member', 'status' => 'active'],

    ['group_id' => 2, 'user_id' => 1, 'role' => 'moderator', 'status' => 'active'],
    ['group_id' => 2, 'user_id' => 2, 'role' => 'member', 'status' => 'active'],
    ['group_id' => 2, 'user_id' => 3, 'role' => 'member', 'status' => 'active'],

    ['group_id' => 3, 'user_id' => 1, 'role' => 'moderator', 'status' => 'active'],
    ['group_id' => 3, 'user_id' => 2, 'role' => 'member', 'status' => 'active'],
    ['group_id' => 3, 'user_id' => 3, 'role' => 'member', 'status' => 'active'],

    ['group_id' => 4, 'user_id' => 1, 'role' => 'moderator', 'status' => 'active'],
    ['group_id' => 4, 'user_id' => 2, 'role' => 'member', 'status' => 'active'],
    ['group_id' => 4, 'user_id' => 3, 'role' => 'member', 'status' => 'active'],
];

foreach ($memberships as $m) {
    $stmt = $pdo->prepare("SELECT id FROM group_memberships WHERE group_id = ? AND user_id = ?");
    $stmt->execute([$m['group_id'], $m['user_id']]);
    if (!$stmt->fetch()) {
        $ins = $pdo->prepare("INSERT INTO group_memberships (group_id, user_id, role, status, joined_at) VALUES (?, ?, ?, ?, NOW())");
        $ins->execute([$m['group_id'], $m['user_id'], $m['role'], $m['status']]);
    }
}

// Add sample discussions if none exist
$samplePosts = [
    [
        'group_id' => 1,
        'user_id' => 2,
        'title' => 'Best practices for PDO transactions and repository architecture?',
        'content' => "Hello everyone! I've been structuring the database layer using repository classes and PDO prepared statements. What is the recommended way to handle multi-table rollbacks cleanly in PHP 8.2?",
        'replies' => [
            [
                'user_id' => 1,
                'content' => "Great question Ali! Wrap your repository unit-of-work in \$pdo->beginTransaction() and always enclose the execute calls in a try-catch block. In the catch block, invoke \$pdo->rollBack() and rethrow a domain-specific exception."
            ]
        ]
    ],
    [
        'group_id' => 2,
        'user_id' => 3,
        'title' => 'Understanding Async/Await and Event Loop Microtasks',
        'content' => "Does Promise.resolve().then() run before setTimeout(..., 0)? Testing this in modern Chrome showed microtasks always drain first.",
        'replies' => [
            [
                'user_id' => 2,
                'content' => "Yes! The V8 event loop processes the entire microtask queue (which includes Promises and MutationObserver callbacks) before fetching the next macrotask from the timers queue."
            ]
        ]
    ],
    [
        'group_id' => 3,
        'user_id' => 1,
        'title' => 'AgentRouter & Claude 3.5 System Prompt Engineering Patterns',
        'content' => "When designing prompt templates for the AI Tutor and MCQ generator, few-shot schema enforcement produces 100% valid JSON responses. Feel free to share your custom prompt templates here!",
        'replies' => [
            [
                'user_id' => 2,
                'content' => "The JSON schema enforcement is incredibly reliable. It completely eliminated parsing errors on our assessments."
            ]
        ]
    ],
    [
        'group_id' => 4,
        'user_id' => 2,
        'title' => 'Responsive Grid vs Flexbox for Interactive Dashboards',
        'content' => "Building the new video learning layout! Using CSS Grid with clamp() and minmax() makes multi-device scaling super clean without massive media query bloat.",
        'replies' => [
            [
                'user_id' => 3,
                'content' => "Agreed! Modern CSS Grid with repeat(auto-fit, minmax(280px, 1fr)) makes dashboards look flawless on mobile, tablet, and 4K displays."
            ]
        ]
    ]
];

foreach ($samplePosts as $p) {
    $stmt = $pdo->prepare("SELECT id FROM discussion_posts WHERE group_id = ? AND title = ?");
    $stmt->execute([$p['group_id'], $p['title']]);
    $existing = $stmt->fetch();
    if (!$existing) {
        $ins = $pdo->prepare("INSERT INTO discussion_posts (group_id, user_id, title, content, is_pinned, is_locked, replies_count, created_at, updated_at) VALUES (?, ?, ?, ?, 0, 0, ?, NOW(), NOW())");
        $ins->execute([$p['group_id'], $p['user_id'], $p['title'], $p['content'], count($p['replies'])]);
        $postId = (int)$pdo->lastInsertId();

        foreach ($p['replies'] as $rep) {
            $repIns = $pdo->prepare("INSERT INTO discussion_replies (post_id, user_id, content, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
            $repIns->execute([$postId, $rep['user_id'], $rep['content']]);
        }
    }
}

echo "Community seeded successfully!\n";
