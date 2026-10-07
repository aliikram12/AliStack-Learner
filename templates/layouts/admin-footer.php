            </main>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="<?= assetUrl('js/app.js') ?>"></script>
    <script>
        document.getElementById('adminSidebarToggle')?.addEventListener('click', function(e) {
            e.stopPropagation();
            const sidebar = document.querySelector('.admin-sidebar');
            if (sidebar) sidebar.classList.toggle('open');
        });
        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.admin-sidebar');
            if (sidebar && sidebar.classList.contains('open') && !sidebar.contains(e.target) && !e.target.closest('#adminSidebarToggle')) {
                sidebar.classList.remove('open');
            }
        });
    </script>
</body>
</html>
