/**
 * AliStack Learner - AliStack AI Tutor Client
 * Integrates contextual chat, quick actions, history, and markdown code rendering
 */

document.addEventListener('DOMContentLoaded', () => {
    const aiDrawer = document.getElementById('aiDrawer');
    const aiLaunchBtn = document.getElementById('aiLaunchBtn');
    const aiCloseBtn = document.getElementById('aiCloseBtn');
    const aiChatForm = document.getElementById('aiChatForm');
    const aiInputField = document.getElementById('aiInputField');
    const aiChatBody = document.getElementById('aiChatBody');
    const aiTypingIndicator = document.getElementById('aiTypingIndicator');
    const aiNewConvBtn = document.getElementById('aiNewConvBtn');

    let currentConversationId = null;

    if (!aiDrawer || !aiLaunchBtn) return;

    // Toggle Drawer
    aiLaunchBtn.addEventListener('click', () => {
        aiDrawer.classList.toggle('open');
        if (aiDrawer.classList.contains('open')) {
            aiInputField?.focus();
            scrollToBottom();
        }
    });

    aiCloseBtn?.addEventListener('click', () => {
        aiDrawer.classList.remove('open');
    });

    // Start New Conversation
    aiNewConvBtn?.addEventListener('click', () => {
        if (confirm('Start a new AI Tutor conversation?')) {
            currentConversationId = null;
            // Clear message list except initial welcome
            aiChatBody.innerHTML = `
                <div class="ai-message assistant">
                    <div class="msg-bubble">
                        Hello! I am your <strong>AliStack AI Tutor</strong>. I am here to assist you with this lesson, answer conceptual questions, debug code, or prepare you for the assessment. What can I help you with?
                    </div>
                </div>
            `;
        }
    });

    // Quick Action Buttons
    document.querySelectorAll('.quick-action-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const prompt = btn.getAttribute('data-prompt');
            if (prompt) {
                sendMessage(prompt);
            }
        });
    });

    // Handle Enter to Send (Shift+Enter for new line)
    aiInputField?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            aiChatForm?.dispatchEvent(new Event('submit'));
        }
    });

    // Handle Form Submit
    aiChatForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        const text = aiInputField.value.trim();
        if (!text) return;
        aiInputField.value = '';
        sendMessage(text);
    });

    async function sendMessage(text) {
        appendMessage('user', text);
        showTyping(true);

        const courseId = document.getElementById('playerConfig')?.getAttribute('data-course-id') || null;
        const lessonId = document.getElementById('playerConfig')?.getAttribute('data-lesson-id') || null;
        const currentNotes = document.getElementById('lessonNotesText')?.value || '';

        try {
            const res = await fetch('/api/ai/chat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': window.getCsrfToken()
                },
                body: JSON.stringify({
                    message: text,
                    course_id: courseId ? parseInt(courseId, 10) : null,
                    lesson_id: lessonId ? parseInt(lessonId, 10) : null,
                    conversation_id: currentConversationId,
                    additional_context: currentNotes ? currentNotes.substring(0, 300) : null
                })
            });

            const data = await res.json();
            showTyping(false);

            if (data.success) {
                if (data.conversation_id) {
                    currentConversationId = data.conversation_id;
                }
                appendMessage('assistant', data.reply);
            } else {
                appendMessage('assistant', `⚠️ ${data.error || 'The AI Tutor could not complete your request. Please try again.'}`);
            }
        } catch (err) {
            showTyping(false);
            appendMessage('assistant', '⚠️ Network connection error. Please verify your connection and try again.');
        }
    }

    function appendMessage(sender, text) {
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-message ${sender}`;

        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble';

        if (sender === 'assistant') {
            // Render basic markdown formatting safely
            bubble.innerHTML = formatMarkdown(text);

            // Add Copy Button
            const copyBtn = document.createElement('button');
            copyBtn.className = 'ai-copy-btn';
            copyBtn.innerHTML = '<i class="bi bi-clipboard"></i>';
            copyBtn.title = 'Copy response';
            copyBtn.addEventListener('click', () => {
                navigator.clipboard.writeText(text);
                copyBtn.innerHTML = '<i class="bi bi-check2"></i>';
                setTimeout(() => { copyBtn.innerHTML = '<i class="bi bi-clipboard"></i>'; }, 2000);
            });
            bubble.appendChild(copyBtn);
        } else {
            bubble.textContent = text;
        }

        msgDiv.appendChild(bubble);
        aiChatBody.appendChild(msgDiv);
        scrollToBottom();
    }

    function showTyping(show) {
        if (!aiTypingIndicator) return;
        aiTypingIndicator.style.display = show ? 'flex' : 'none';
        if (show) scrollToBottom();
    }

    function scrollToBottom() {
        if (aiChatBody) {
            aiChatBody.scrollTop = aiChatBody.scrollHeight;
        }
    }

    function formatMarkdown(text) {
        // Safe escaping then replacement of code blocks, inline code, bold, lists
        let escaped = text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;");

        // Code blocks: ```code```
        escaped = escaped.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
        // Inline code: `code`
        escaped = escaped.replace(/`([^`]+)`/g, '<code>$1</code>');
        // Bold: **text**
        escaped = escaped.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
        // Line breaks
        escaped = escaped.replace(/\n/g, '<br>');

        return escaped;
    }
});
