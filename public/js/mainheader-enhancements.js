document.addEventListener('DOMContentLoaded', function() {
  // 'Ctrl+K' or 'Cmd+K' search shortcut
  document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && (e.key === 'k' || e.key === 'K')) {
      e.preventDefault();
      const searchInput = document.getElementById('global-search');
      if (searchInput) {
        searchInput.focus();
      }
    }
  });

  // Optional: focus the search input when the visible search icon is clicked
  const searchButtons = document.querySelectorAll('.enhanced-search .input-group .btn');
  searchButtons.forEach(function(btn) {
    btn.addEventListener('click', function() {
      const input = btn.closest('.input-group').querySelector('input[type="text"]');
      if (input) input.focus();
    });
  });
});
