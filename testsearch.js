document.addEventListener('DOMContentLoaded', function() {
    const searchIcon = document.getElementById('search-icon');
    const searchBar = document.getElementById('search-bar');

    searchIcon.addEventListener('click', function(event) {
        event.preventDefault(); // Prevent default anchor behavior
        if (searchBar.style.display === 'block') {
            searchBar.style.display = 'none';
        } else {
            searchBar.style.display = 'block';
            searchBar.querySelector('input').focus(); // Focus the search input when shown
        }
    });

    // Close the search bar if clicked outside of it
    document.addEventListener('click', function(event) {
        if (!searchBar.contains(event.target) && !searchIcon.contains(event.target)) {
            searchBar.style.display = 'none';
        }
    });
});
