<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';

$pdo = getDbConnection();

echo "=== Seeding Courses, Lessons, and Assessments ===\n";

// 1. Ensure categories exist
$categories = [
    [1, 'Web Development', 'web-development', 'Modern web technologies including HTML, CSS, JavaScript, PHP, and modern full-stack development.', 'bi-code-slash'],
    [2, 'Artificial Intelligence', 'artificial-intelligence', 'Machine Learning, Prompt Engineering, Agentic AI, and Large Language Models.', 'bi-robot'],
    [3, 'Computer Science Foundations', 'cs-foundations', 'Algorithms, Data Structures, System Design, and software engineering principles.', 'bi-cpu'],
    [4, 'DevOps & Cloud', 'devops-cloud', 'Docker, CI/CD pipelines, Linux servers, cloud deployments, and web infrastructure.', 'bi-cloud-check'],
];

foreach ($categories as $cat) {
    $stmt = $pdo->prepare("INSERT INTO course_categories (id, name, slug, description, icon, is_active, created_at)
        VALUES (?, ?, ?, ?, ?, 1, NOW())
        ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description), icon = VALUES(icon)");
    $stmt->execute([$cat[0], $cat[1], $cat[2], $cat[3], $cat[4]]);
}
echo "✓ Categories validated.\n";

// 2. Ensure Courses 1, 2, 3 exist
$courses = [
    [
        'id' => 1,
        'category_id' => 1,
        'title' => 'Full-Stack PHP 8 & MySQL Modern Architecture',
        'slug' => 'fullstack-php-8-mysql-architecture',
        'thumbnail' => 'php-course.jpg',
        'short_desc' => 'Master modern PHP 8.2+, OOP design patterns, PDO security, and relational MySQL database design.',
        'full_desc' => 'A comprehensive, distraction-free course guiding you through production PHP 8.2+ development. You will learn modern syntax, type declarations, Object-Oriented programming principles, secure database access via PDO, authentication systems, and scalable MVC architectures without heavyweight frameworks.',
        'difficulty' => 'beginner',
        'language' => 'English / Urdu',
        'estimated_duration' => '8 Hours',
        'learning_objectives' => "Master PHP 8.2+ syntax and typed properties\nBuild secure database queries using PDO prepared statements\nUnderstand password hashing and session security\nArchitect clean, maintainable modular backends",
        'prerequisites' => 'Basic understanding of HTML and basic logic.',
        'instructor_name' => 'Engr. Ali & Team AliStack',
        'youtube_playlist_url' => 'https://www.youtube.com/playlist?list=PL4cUxeGkcC9gksOX3BdCEVDhUXF689Vj',
        'youtube_playlist_id' => 'PL4cUxeGkcC9gksOX3BdCEVDhUXF689Vj',
        'status' => 'published',
        'is_featured' => 1,
        'course_order' => 1
    ],
    [
        'id' => 2,
        'category_id' => 1,
        'title' => 'Modern Vanilla JavaScript & Web APIs Deep Dive',
        'slug' => 'modern-vanilla-javascript-deep-dive',
        'thumbnail' => 'js-course.jpg',
        'short_desc' => 'Understand asynchronous JavaScript, DOM manipulation, Fetch API, and state management.',
        'full_desc' => 'Learn vanilla JavaScript from foundational concepts to advanced asynchronous programming. Explore ES6+ modules, promises, async/await, DOM events, and API integrations with zero framework bloat.',
        'difficulty' => 'intermediate',
        'language' => 'English / Urdu',
        'estimated_duration' => '6 Hours',
        'learning_objectives' => "Confidently manipulate the DOM without libraries\nMaster asynchronous JavaScript with Promises and async/await\nConnect to REST APIs cleanly using Fetch\nHandle state and browser storage securely",
        'prerequisites' => 'Basic HTML & CSS knowledge.',
        'instructor_name' => 'AliStack Academy',
        'youtube_playlist_url' => 'https://www.youtube.com/playlist?list=PL4cUxeGkcC9haFPT7J25Q9GRB_Z5230Z7',
        'youtube_playlist_id' => 'PL4cUxeGkcC9haFPT7J25Q9GRB_Z5230Z7',
        'status' => 'published',
        'is_featured' => 1,
        'course_order' => 2
    ],
    [
        'id' => 3,
        'category_id' => 2,
        'title' => 'Applied AI & Practical Prompt Engineering',
        'slug' => 'applied-ai-practical-prompt-engineering',
        'thumbnail' => 'ai-course.jpg',
        'short_desc' => 'Harness Large Language Models, Agentic APIs, and structured prompts to build intelligent workflows.',
        'full_desc' => 'Step into practical AI engineering. Discover how LLMs process tokens, master chain-of-thought and few-shot prompt design, connect external tools through APIs, and integrate AI tutors and copilots into modern web applications.',
        'difficulty' => 'beginner',
        'language' => 'English / Urdu',
        'estimated_duration' => '5 Hours',
        'learning_objectives' => "Understand LLM architectures, temperature, and tokens\nDesign robust few-shot and system prompts\nIntegrate AI gateway APIs like AgentRouter\nBuild contextual assistants with memory and constraints",
        'prerequisites' => 'No prior AI experience required.',
        'instructor_name' => 'AliStack AI Research Group',
        'youtube_playlist_url' => 'https://www.youtube.com/playlist?list=PL4cUxeGkcC9i1m1Vq4zO0F5yZ5040f2p',
        'youtube_playlist_id' => 'PL4cUxeGkcC9i1m1Vq4zO0F5yZ5040f2p',
        'status' => 'published',
        'is_featured' => 1,
        'course_order' => 3
    ]
];

foreach ($courses as $c) {
    $stmt = $pdo->prepare("INSERT INTO courses (id, category_id, title, slug, thumbnail, short_desc, full_desc, difficulty, language, estimated_duration, learning_objectives, prerequisites, instructor_name, youtube_playlist_url, youtube_playlist_id, status, is_featured, course_order, created_at, updated_at)
        VALUES (:id, :category_id, :title, :slug, :thumbnail, :short_desc, :full_desc, :difficulty, :language, :estimated_duration, :learning_objectives, :prerequisites, :instructor_name, :youtube_playlist_url, :youtube_playlist_id, :status, :is_featured, :course_order, NOW(), NOW())
        ON DUPLICATE KEY UPDATE title = VALUES(title), short_desc = VALUES(short_desc), full_desc = VALUES(full_desc), status = VALUES(status), is_featured = VALUES(is_featured)");
    $stmt->execute($c);
}
echo "✓ Core courses validated.\n";

// 3. Make sure Course 4 has great details
$pdo->prepare("UPDATE courses SET 
    category_id = 1,
    title = 'Full-Stack Web Development Masterclass',
    short_desc = 'Comprehensive guide covering HTML5, CSS3, modern JavaScript, responsive design, and web architecture.',
    full_desc = 'A complete zero-to-hero web development journey. Learn HTML structure, modern CSS flexbox & grid styling, JavaScript DOM scripting, and asynchronous web APIs in a distraction-free environment.',
    difficulty = 'beginner',
    language = 'Urdu / Hindi / English',
    estimated_duration = '12 Hours',
    learning_objectives = 'Master semantic HTML5 markup\nCreate responsive layouts with CSS Flexbox and Grid\nProgram interactive features with Vanilla JavaScript\nBuild and deploy complete real-world projects',
    prerequisites = 'No prior programming experience required. A computer with web browser and text editor.',
    instructor_name = 'AliStack & Apna College Curated',
    status = 'published',
    is_featured = 1
    WHERE id = 4
")->execute();
echo "✓ Course 4 updated with rich metadata.\n";

// 4. Seed Lessons
$pdo->prepare("DELETE FROM course_lessons WHERE course_id IN (1, 2, 3, 4)")->execute();

$lessons = [
    // Course 1: PHP & MySQL
    [1, 'Introduction to Modern PHP 8.2 & Environment Setup', 'Welcome to the course. We overview modern PHP standards, setup XAMPP / CLI, and write our first strongly typed PHP script.', 'nZJbQkIq3t0', 12, 1, 1],
    [1, 'PHP 8 Types, Functions & Control Structures', 'Explore scalar types, union types, nullables, match expressions, and pure functions in modern PHP.', '243pQxSDApM', 18, 2, 1],
    [1, 'Object-Oriented PHP: Classes, Inheritance & Interfaces', 'Understand encapsulation, inheritance, interfaces, and dependency injection in practical PHP software design.', 'gCo6JqGMi30', 22, 3, 1],
    [1, 'PDO Fundamentals & SQL Injection Defense', 'Connecting to MySQL securely with PDO. Using prepared statements with bound parameters to eliminate SQL injection.', 'kEW6f7PILc4', 25, 4, 1],
    [1, 'Session Management, Password Hashing & Authentication', 'Implement secure user sessions, session regeneration, and cryptographic password hashing with password_hash.', 'x0bO_o8R90s', 20, 5, 1],

    // Course 2: JavaScript
    [2, 'JavaScript Fundamentals: Scope, Closures & ES6', 'Deep dive into let/const, block scoping, arrow functions, and closures in modern JavaScript.', 'W6NZfCO5SIk', 15, 1, 1],
    [2, 'The DOM Tree, Event Listeners & Event Delegation', 'Querying DOM nodes efficiently, manipulating attributes, and utilizing event delegation for high performance.', '0ik6X4DJKCc', 18, 2, 1],
    [2, 'Promises, Async/Await and Error Handling', 'Understand the JavaScript event loop, asynchronous execution, and cleanly orchestrating promises with try/catch.', 'PoRJizFvM7s', 20, 3, 1],
    [2, 'The Fetch API & RESTful Communication', 'Communicating with backend endpoints using Fetch, sending JSON payloads, and reading HTTP response codes.', 'cuEtnrL9-H0', 22, 4, 1],

    // Course 3: Applied AI
    [3, 'Foundations of LLMs, Tokens & Context Windows', 'How language models predict text, understand tokens, context limits, and temperature hyperparameters.', 'aircAruvnKk', 15, 1, 1],
    [3, 'System Prompts & Few-Shot Engineering', 'Crafting deterministic system instructions, role assignment, and few-shot formatting techniques.', 'jC4v5AS4RIM', 18, 2, 1],
    [3, 'Connecting LLMs to Web Apps via AgentRouter API', 'Building server-side API clients, maintaining chat history, and securing API keys.', 'zjkBMFhNj_g', 25, 3, 1],

    // Course 4: Web Dev Course (Apna College playlist lessons)
    [4, 'HTML5 Foundations: Tags, Attributes & Page Structure', 'Learn the building blocks of the web: HTML tags, paragraphs, headings, anchor links, and clean semantic document structure.', 'l1EssrLxtVM', 25, 1, 1],
    [4, 'HTML5 Forms, Tables, Media & Semantic Elements', 'Deep dive into input types, form submission, audio/video tags, table structures, and accessibility standards.', 'kUMe1FH4CHE', 30, 2, 1],
    [4, 'CSS3 Styling, Box Model, Colors & Typography', 'Understanding the CSS box model (margin, border, padding, content), typography, background colors, and CSS cascade rules.', 'ESnrn1kAD4E', 35, 3, 1],
    [4, 'Modern Responsive Layouts with CSS Flexbox', 'Master flex-direction, justify-content, align-items, flex-wrap, and build modern mobile-first navbar and grid layouts.', 'phWxA89Dy94', 40, 4, 1],
    [4, 'JavaScript Core Fundamentals: Variables, Loops & Functions', 'Introduction to JavaScript programming: let, const, data types, conditional logic, for loops, and function declarations.', 'VlPiVmYuoqw', 45, 5, 1],
    [4, 'JavaScript DOM Manipulation & Interactive Events', 'Selecting HTML elements with querySelector, handling click & input events, toggling classes, and creating interactive web apps.', '0ik6X4DJKCc', 35, 6, 1],
    [4, 'Asynchronous JavaScript: Callbacks, Promises & Fetch API', 'Master modern asynchronous JavaScript: Promises, async/await syntax, and fetching live JSON data from external web APIs.', 'cuEtnrL9-H0', 40, 7, 1]
];

$insLesson = $pdo->prepare("INSERT INTO course_lessons (course_id, title, description, youtube_video_id, duration_minutes, lesson_order, is_published, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

foreach ($lessons as $l) {
    $insLesson->execute($l);
}
echo "✓ Inserted " . count($lessons) . " published video lessons across courses 1, 2, 3, 4.\n";

// 5. Seed Assessments
$assessments = [
    [1, 1, 'Full-Stack PHP 8 & MySQL Final Assessment', 'This assessment tests your comprehensive knowledge of modern PHP 8.2, PDO security, Object-Oriented principles, and authentication. Pass with 70%+ to earn your official AliStack Certificate.', 5, 50, 70.00, 15, 3],
    [2, 2, 'Modern JavaScript & Web APIs Assessment', 'Demonstrate your mastery of ES6+, DOM manipulation, asynchronous programming, and REST integrations.', 4, 40, 70.00, 12, 3],
    [3, 3, 'Applied AI & Prompt Engineering Assessment', 'Evaluate your understanding of prompt design, context management, tokens, and API integration.', 3, 30, 70.00, 10, 3],
    [4, 4, 'Full-Stack Web Development Certification Assessment', 'Validate your mastery of HTML5, CSS3 responsive layout, JavaScript DOM interactivity, and modern web application development.', 5, 50, 70.00, 20, 3]
];

$insAssess = $pdo->prepare("INSERT INTO assessments (id, course_id, title, instructions, total_questions_to_ask, total_marks, pass_percentage, time_limit_minutes, max_attempts, randomize_questions, randomize_options, reveal_answers, retakes_allowed, is_published, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0, 1, 1, 1, NOW())
    ON DUPLICATE KEY UPDATE title = VALUES(title), instructions = VALUES(instructions), total_questions_to_ask = VALUES(total_questions_to_ask)");

foreach ($assessments as $a) {
    $insAssess->execute([$a[0], $a[1], $a[2], $a[3], $a[4], $a[5], $a[6], $a[7], $a[8]]);
}
echo "✓ Assessments validated for all 4 courses.\n";

// 6. Seed Assessment Questions
$pdo->prepare("DELETE FROM assessment_questions WHERE assessment_id IN (1, 2, 3, 4)")->execute();

$questions = [
    // Course 1: PHP
    [1, 1, 'Which PHP function is the cryptographically secure and standard recommended method for hashing user passwords?', 'md5()', 'sha1()', 'password_hash()', 'crypt_aes()', 'C', 'password_hash() uses strong one-way hashing algorithms (such as Bcrypt or Argon2) with automatic salt generation.', 'Security', 'easy', 10],
    [1, 1, 'What is the primary security advantage of using PDO prepared statements with bound parameters?', 'It compresses the query string to save bandwidth', 'It guarantees queries execute faster in all scenarios', 'It completely separates SQL code from user-supplied data, neutralizing SQL injection', 'It encrypts database tables on disk automatically', 'C', 'Prepared statements send query structure and parameters separately to the database engine, preventing SQL injection.', 'PDO & Database', 'medium', 10],
    [1, 1, 'Why is session_regenerate_id(true) critical immediately following a successful user login?', 'It clears the user browser cache', 'It prevents Session Fixation attacks by issuing a brand new session identifier', 'It refreshes the database connection pool', 'It encrypts the PHP source files in memory', 'B', 'Regenerating the ID upon privilege change invalidates any pre-session identifier created before login.', 'Authentication', 'medium', 10],
    [1, 1, 'In PHP 8.0+, what is the behavior of the match expression compared to a legacy switch statement?', 'It uses loose comparison (==) and allows fall-through', 'It uses strict comparison (===), returns a value directly, and does not require break statements', 'It can only evaluate boolean variables', 'It runs in a separate thread asynchronously', 'B', 'The match expression uses strict identity checks (===) and returns an expression result directly.', 'PHP Syntax', 'medium', 10],
    [1, 1, 'Which HTTP header attribute prevents JavaScript from accessing session cookies, defending against XSS cookie theft?', 'SameSite=Lax', 'Secure', 'HttpOnly', 'Max-Age', 'C', 'The HttpOnly flag directs browsers not to expose the cookie to client-side scripts like document.cookie.', 'Session Security', 'easy', 10],

    // Course 2: JavaScript
    [2, 2, 'What does an async function in modern JavaScript always return?', 'A boolean value indicating execution status', 'A Promise that resolves with the returned value or rejects with an uncaught error', 'A generator iterator object', 'Undefined if no return statement is explicitly written', 'B', 'Functions declared with async always wrap their return value inside a resolved Promise.', 'Async JS', 'medium', 10],
    [2, 2, 'Which technique attaches a single event listener to a parent element to handle events from multiple children?', 'Event Bubbling Negation', 'Event Delegation', 'Event Throttling', 'Event Hoisting', 'B', 'Event delegation takes advantage of event bubbling to handle events on parent containers efficiently.', 'DOM', 'easy', 10],
    [2, 2, 'When using the Fetch API, does a 404 or 500 HTTP response status cause the returned Promise to reject?', 'Yes, any status outside 200-299 rejects the promise', 'No, the Promise only rejects on network failures; response.ok must be checked', 'Yes, only 500 status codes reject', 'Only if the credentials option is set to include', 'B', 'A fetch() Promise only rejects if there is a network disconnection or DNS failure.', 'Web APIs', 'medium', 10],
    [2, 2, 'What is the purpose of debouncing an input event handler in JavaScript?', 'To run the function repeatedly at exact intervals', 'To delay function execution until a specified delay has passed since the user last triggered the event', 'To cancel all network packets in flight', 'To convert synchronous code into web worker threads', 'B', 'Debouncing limits the rate at which a function is invoked, ensuring it only executes after the user stops typing.', 'Performance', 'medium', 10],

    // Course 3: AI
    [3, 3, 'In LLM applications, what does lowering the temperature parameter (e.g. from 0.9 to 0.1) achieve?', 'Increases the maximum tokens generated per second', 'Makes responses more deterministic, focused, and repeatable', 'Doubles the context window capacity', 'Automatically verifies factual accuracy with Google Search', 'B', 'Lower temperature reduces sampling randomness, making responses focused and deterministic.', 'LLM Parameters', 'easy', 10],
    [3, 3, 'Where should an external AI gateway API key (such as AgentRouter) be stored and used in a web application?', 'In client-side JavaScript constants', 'Exclusively on the server environment, never exposed to browser requests or public source files', 'In public HTML data attributes', 'Inside CSS root custom properties', 'B', 'API keys represent paid access and secrets; exposing them to client browsers allows unauthorized extraction.', 'Security & API', 'easy', 10],
    [3, 3, 'What is "few-shot prompting"?', 'Limiting the model to 3 attempts before returning an error', 'Providing the model with a few high-quality input-output examples inside the prompt to guide its behavior', 'Training model weights using gradient descent on client devices', 'Using multiple AI models simultaneously in a round-robin cycle', 'B', 'Few-shot prompting provides demonstrations within the prompt context, dramatically improving output quality.', 'Prompt Engineering', 'medium', 10],

    // Course 4: Web Dev
    [4, 4, 'Which HTML5 semantic element is most appropriate for wrapping independent, self-contained syndicated content (like a blog post or news story)?', '<section>', '<article>', '<aside>', '<main>', 'B', 'The <article> tag specifies independent, self-contained content that can be distributed or reused independently.', 'HTML5 Semantics', 'easy', 10],
    [4, 4, 'In CSS Flexbox, which property controls alignment of flex items along the CROSS axis?', 'justify-content', 'align-items', 'flex-direction', 'align-self', 'B', 'align-items defines how flex items are aligned along the cross axis (vertical in row layout).', 'CSS Flexbox', 'easy', 10],
    [4, 4, 'In modern JavaScript, what is the key difference between let and const variables?', 'let is globally scoped while const is function scoped', 'const cannot be reassigned after declaration, whereas let can be reassigned', 'let can only hold strings, while const holds numbers', 'const variables are hoisted to the top, while let variables are not hoisted at all', 'B', 'const creates an immutable binding that cannot be reassigned, whereas let allows reassignment.', 'JavaScript Basics', 'easy', 10],
    [4, 4, 'Which method is the standard, modern way to attach a click event handler to a button element in the DOM?', 'button.onclick = function()', 'button.addEventListener("click", handler)', 'button.attachEvent("onclick", handler)', 'button.trigger("click", handler)', 'B', 'addEventListener is the modern standard method supporting multiple listeners and event capture/bubble phases.', 'DOM Events', 'medium', 10],
    [4, 4, 'What is the standard CSS box-sizing value recommended in modern CSS resets so padding and borders do NOT increase an element\'s specified width?', 'content-box', 'border-box', 'padding-box', 'initial', 'B', 'box-sizing: border-box includes padding and border within the specified width and height, preventing layout breakage.', 'CSS Box Model', 'medium', 10]
];

$insQ = $pdo->prepare("INSERT INTO assessment_questions (assessment_id, course_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, topic, difficulty, marks, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

foreach ($questions as $q) {
    $insQ->execute($q);
}
echo "✓ Inserted " . count($questions) . " MCQ questions into the question bank.\n";

// 7. Seed Discussion Groups
$pdo->prepare("DELETE FROM discussion_groups WHERE id IN (1, 2, 3, 4)")->execute();

$groups = [
    [1, 1, 'PHP & MySQL Architecture Circle', 'php-mysql-architecture-circle', 'A dedicated space for students of the Full-Stack PHP & MySQL Masterclass to discuss patterns, ask debugging questions, and collaborate.', 0],
    [2, 2, 'JavaScript Engineers Lounge', 'javascript-engineers-lounge', 'Share code snippets, discuss modern browser features, DOM tricks, and asynchronous design.', 0],
    [3, 3, 'AI & Prompt Crafting Hub', 'ai-prompt-crafting-hub', 'Explore LLM workflows, test system prompts, share tips on AgentRouter integration, and optimize model responses.', 0],
    [4, 4, 'Web Developers Mastermind', 'web-developers-mastermind', 'Ask HTML, CSS, and JavaScript questions, showcase your personal projects, and get code reviews from fellow developers.', 0]
];

$insGroup = $pdo->prepare("INSERT INTO discussion_groups (id, course_id, name, slug, description, rules, is_private, is_active, created_by, created_at)
    VALUES (?, ?, ?, ?, ?, '1. Be polite and respectful.\\n2. Do not leak assessment answer keys.\\n3. Format code blocks cleanly.', ?, 1, 1, NOW())");

foreach ($groups as $g) {
    $insGroup->execute([$g[0], $g[1], $g[2], $g[3], $g[4], $g[5]]);
}
echo "✓ Discussion groups created.\n";

echo "\n>>> COMPLETE DATABASE SEEDING SUCCESSFUL! <<<\n";
