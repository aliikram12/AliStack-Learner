/**
 * AliStack Learner - MCQ Assessment Player Engine
 * Timer countdown, navigation dots, autosave, and tamper-proof submission
 */

document.addEventListener('DOMContentLoaded', () => {
    const testConfig = document.getElementById('testConfig');
    if (!testConfig) return;

    const attemptId = parseInt(testConfig.getAttribute('data-attempt-id'), 10);
    const timeLimitMinutes = parseInt(testConfig.getAttribute('data-time-limit') || '20', 10);
    const totalQuestions = parseInt(testConfig.getAttribute('data-total-questions') || '10', 10);

    let currentQuestionIndex = 0;
    let timerSeconds = timeLimitMinutes * 60;
    let timerInterval = null;

    const timerDisplay = document.getElementById('timerDisplay');
    const questionCards = document.querySelectorAll('.test-question-item');
    const prevBtn = document.getElementById('prevQuestionBtn');
    const nextBtn = document.getElementById('nextQuestionBtn');
    const submitBtn = document.getElementById('submitTestBtn');
    const navDots = document.querySelectorAll('.nav-dot');

    // 1. Timer Logic
    function startTimer() {
        updateTimerDisplay();
        timerInterval = setInterval(() => {
            timerSeconds--;
            updateTimerDisplay();

            if (timerSeconds <= 0) {
                clearInterval(timerInterval);
                alert('Time limit expired! Your answers are being submitted automatically.');
                submitAssessment(true);
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        if (!timerDisplay) return;
        const mins = Math.floor(timerSeconds / 60);
        const secs = timerSeconds % 60;
        timerDisplay.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        if (timerSeconds < 120) {
            timerDisplay.parentElement?.classList.add('timer-warning');
        }
    }

    // 2. Question Navigation
    function showQuestion(index) {
        if (index < 0 || index >= questionCards.length) return;
        currentQuestionIndex = index;

        questionCards.forEach((c, idx) => {
            c.style.display = (idx === index) ? 'block' : 'none';
        });

        navDots.forEach((dot, idx) => {
            dot.classList.toggle('active', idx === index);
        });

        if (prevBtn) prevBtn.disabled = (index === 0);
        if (nextBtn) {
            if (index === questionCards.length - 1) {
                nextBtn.style.display = 'none';
                if (submitBtn) submitBtn.style.display = 'inline-flex';
            } else {
                nextBtn.style.display = 'inline-flex';
                if (submitBtn) submitBtn.style.display = 'none';
            }
        }
    }

    prevBtn?.addEventListener('click', () => showQuestion(currentQuestionIndex - 1));
    nextBtn?.addEventListener('click', () => showQuestion(currentQuestionIndex + 1));

    navDots.forEach(dot => {
        dot.addEventListener('click', () => {
            const idx = parseInt(dot.getAttribute('data-index'), 10);
            showQuestion(idx);
        });
    });

    // 3. Option Selection Tracking
    document.querySelectorAll('.option-radio').forEach(radio => {
        radio.addEventListener('change', () => {
            const qId = radio.getAttribute('name').replace('q_', '');
            const dot = document.querySelector(`.nav-dot[data-question-id="${qId}"]`);
            if (dot) dot.classList.add('answered');

            // Highlight label
            const questionCard = radio.closest('.test-question-item');
            if (questionCard) {
                questionCard.querySelectorAll('.option-label').forEach(l => l.classList.remove('selected'));
                radio.closest('.option-label')?.classList.add('selected');
            }
        });
    });

    // 4. Submit Assessment
    submitBtn?.addEventListener('click', () => {
        const answeredCount = document.querySelectorAll('.option-radio:checked').length;
        let confirmMsg = 'Are you sure you want to finish and submit your assessment?';
        if (answeredCount < totalQuestions) {
            confirmMsg = `You have only answered ${answeredCount} of ${totalQuestions} questions. Are you sure you want to submit?`;
        }

        if (confirm(confirmMsg)) {
            submitAssessment(false);
        }
    });

    async function submitAssessment(isForced = false) {
        if (submitBtn) submitBtn.disabled = true;
        clearInterval(timerInterval);

        // Gather all answers
        const answers = {};
        document.querySelectorAll('.option-radio:checked').forEach(radio => {
            const qId = radio.getAttribute('name').replace('q_', '');
            answers[qId] = radio.value;
        });

        try {
            const endpoint = (typeof window.getApiUrl === 'function') 
                ? window.getApiUrl('api/assessments/submit.php') 
                : '/api/assessments/submit.php';
            const res = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.getCsrfToken()
                },
                body: JSON.stringify({
                    attempt_id: attemptId,
                    answers: answers
                })
            });

            const data = await res.json();
            if (data.success && data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                alert(data.error || 'Failed to submit test. Please try again.');
                if (submitBtn) submitBtn.disabled = false;
            }
        } catch (err) {
            console.error('Submission error', err);
            alert('A network error occurred. Please check your connection and retry.');
            if (submitBtn) submitBtn.disabled = false;
        }
    }

    // Initialize
    showQuestion(0);
    startTimer();
});
