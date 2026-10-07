/**
 * AliStack Learner - Focused Learning Player Engine
 * YouTube IFrame Player API Integration & Note Autosave
 */

let ytPlayer = null;
let playbackTimer = null;
let noteDebounceTimer = null;

// YouTube IFrame API Ready Callback
function onYouTubeIframeAPIReady() {
    const playerEl = document.getElementById('youtubePlayer');
    if (!playerEl) return;

    const videoId = playerEl.getAttribute('data-video-id');
    const startSeconds = parseInt(playerEl.getAttribute('data-start-pos') || '0', 10);

    ytPlayer = new YT.Player('youtubePlayer', {
        height: '100%',
        width: '100%',
        videoId: videoId,
        playerVars: {
            'playsinline': 1,
            'rel': 0,
            'modestbranding': 1,
            'start': startSeconds
        },
        events: {
            'onReady': onPlayerReady,
            'onStateChange': onPlayerStateChange
        }
    });
}

function onPlayerReady(event) {
    const startSeconds = parseInt(document.getElementById('youtubePlayer')?.getAttribute('data-start-pos') || '0', 10);
    if (startSeconds > 5) {
        event.target.seekTo(startSeconds, true);
    }
}

function onPlayerStateChange(event) {
    // When playing, track position periodically
    if (event.data === YT.PlayerState.PLAYING) {
        startPlaybackTracker();
    } else {
        stopPlaybackTracker();
        // Save current timestamp
        savePlaybackPosition(false);
    }

    // Video completed
    if (event.data === YT.PlayerState.ENDED) {
        savePlaybackPosition(true);
        updateLessonCompletedUI();
    }
}

function startPlaybackTracker() {
    stopPlaybackTracker();
    playbackTimer = setInterval(() => {
        savePlaybackPosition(false);
    }, 15000); // every 15 seconds
}

function stopPlaybackTracker() {
    if (playbackTimer) {
        clearInterval(playbackTimer);
        playbackTimer = null;
    }
}

async function savePlaybackPosition(isCompleted = false) {
    if (!ytPlayer || typeof ytPlayer.getCurrentTime !== 'function') return;

    const currentTime = Math.floor(ytPlayer.getCurrentTime() || 0);
    const courseId = document.getElementById('playerConfig')?.getAttribute('data-course-id');
    const lessonId = document.getElementById('playerConfig')?.getAttribute('data-lesson-id');

    if (!courseId || !lessonId) return;

    try {
        const url = (typeof window.getApiUrl === 'function') 
            ? window.getApiUrl('api/progress/update.php') 
            : '/api/progress/update.php';
        await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': window.getCsrfToken()
            },
            body: JSON.stringify({
                course_id: parseInt(courseId, 10),
                lesson_id: parseInt(lessonId, 10),
                position_seconds: currentTime,
                completed: isCompleted ? 1 : 0
            })
        });
    } catch (e) {
        console.error('Failed to save playback progress', e);
    }
}

function updateLessonCompletedUI() {
    const completeBtn = document.getElementById('markCompleteBtn');
    if (completeBtn) {
        completeBtn.classList.add('btn-complete');
        completeBtn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Completed';
    }
    const currentLessonEl = document.querySelector('.lesson-item.active');
    if (currentLessonEl) {
        currentLessonEl.classList.add('completed');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Dynamically inject YouTube API Script
    if (document.getElementById('youtubePlayer')) {
        const tag = document.createElement('script');
        tag.src = "https://www.youtube.com/iframe_api";
        const firstScriptTag = document.getElementsByTagName('script')[0];
        firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
    }

    // Tab Navigation in Player Details
    document.querySelectorAll('.player-tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const tabKey = btn.getAttribute('data-tab');
            document.querySelectorAll('.player-tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.player-tab-pane').forEach(p => p.classList.remove('active'));

            btn.classList.add('active');
            const targetPane = document.getElementById(`tab-${tabKey}`);
            if (targetPane) targetPane.classList.add('active');
        });
    });

    // Mark Complete Button Click
    const completeBtn = document.getElementById('markCompleteBtn');
    if (completeBtn) {
        completeBtn.addEventListener('click', async () => {
            completeBtn.disabled = true;
            await savePlaybackPosition(true);
            updateLessonCompletedUI();
            completeBtn.disabled = false;
        });
    }

    // Bookmark Toggle Button Click
    const bookmarkBtn = document.getElementById('bookmarkBtn');
    if (bookmarkBtn) {
        bookmarkBtn.addEventListener('click', async () => {
            const courseId = document.getElementById('playerConfig')?.getAttribute('data-course-id');
            const lessonId = document.getElementById('playerConfig')?.getAttribute('data-lesson-id');

            try {
                const bmUrl = (typeof window.getApiUrl === 'function') 
                    ? window.getApiUrl('api/progress/toggle-bookmark.php') 
                    : '/api/progress/toggle-bookmark.php';
                const res = await fetch(bmUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': window.getCsrfToken()
                    },
                    body: JSON.stringify({
                        course_id: parseInt(courseId, 10),
                        lesson_id: parseInt(lessonId, 10)
                    })
                });
                const data = await res.json();
                if (data.success) {
                    if (data.is_bookmarked) {
                        bookmarkBtn.classList.add('btn-bookmarked');
                        bookmarkBtn.innerHTML = '<i class="bi bi-bookmark-fill"></i> Bookmarked';
                    } else {
                        bookmarkBtn.classList.remove('btn-bookmarked');
                        bookmarkBtn.innerHTML = '<i class="bi bi-bookmark"></i> Bookmark';
                    }
                }
            } catch (e) {
                console.error('Bookmark error', e);
            }
        });
    }

    // Notes Autosave Debounce
    const notesTextarea = document.getElementById('lessonNotesText');
    const notesStatus = document.getElementById('notesStatus');
    if (notesTextarea) {
        notesTextarea.addEventListener('input', () => {
            if (notesStatus) notesStatus.textContent = 'Unsaved changes...';
            clearTimeout(noteDebounceTimer);
            noteDebounceTimer = setTimeout(async () => {
                const courseId = document.getElementById('playerConfig')?.getAttribute('data-course-id');
                const lessonId = document.getElementById('playerConfig')?.getAttribute('data-lesson-id');
                const content = notesTextarea.value;

                if (notesStatus) notesStatus.innerHTML = '<i class="bi bi-arrow-repeat"></i> Saving...';

                try {
                    const noteUrl = (typeof window.getApiUrl === 'function') 
                        ? window.getApiUrl('api/notes/save.php') 
                        : '/api/notes/save.php';
                    const res = await fetch(noteUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': window.getCsrfToken()
                        },
                        body: JSON.stringify({
                            course_id: parseInt(courseId, 10),
                            lesson_id: parseInt(lessonId, 10),
                            content: content
                        })
                    });
                    const data = await res.json();
                    if (data.success) {
                        if (notesStatus) notesStatus.innerHTML = '<i class="bi bi-check2"></i> Saved';
                    }
                } catch (e) {
                    if (notesStatus) notesStatus.textContent = 'Save error';
                }
            }, 1000); // 1s debounce
        });
    }
});
