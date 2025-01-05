document.addEventListener('DOMContentLoaded', function() {
    // Load vehicles with initial category filter
    loadVehicles();

    // Setup category filter buttons
    document.querySelectorAll('[data-category]').forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.dataset.category;
            loadVehicles(categoryId);
        });
    });

    // Setup search form
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const searchQuery = this.querySelector('input[name="search"]').value;
            loadVehicles(null, searchQuery);
        });
    }
});

function loadVehicles(categoryId = null, search = null) {
    const container = document.getElementById('vehicles-container');
    if (!container) return;

    // Build query string
    let url = 'vehicles.php?ajax=1';
    if (categoryId) url += `&category=${categoryId}`;
    if (search) url += `&search=${encodeURIComponent(search)}`;

    // Show loading state
    container.innerHTML = '<div class="text-center py-4">Chargement...</div>';

    fetch(url)
        .then(response => response.text())
        .then(html => {
            container.innerHTML = html;
        })
        .catch(error => {
            console.error('Error loading vehicles:', error);
            container.innerHTML = '<div class="text-center py-4 text-red-600">Erreur lors du chargement des véhicules</div>';
        });
}

// Reservation form handling
const reservationForm = document.getElementById('reservation-form');
if (reservationForm) {
    reservationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Basic form validation
        const startDate = new Date(this.pickup_date.value);
        const endDate = new Date(this.return_date.value);
        
        if (endDate <= startDate) {
            alert('La date de retour doit être postérieure à la date de prise en charge');
            return;
        }

        // Submit form
        this.submit();
    });
}

// Review form handling
const reviewForm = document.getElementById('review-form');
if (reviewForm) {
    reviewForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Basic validation
        if (this.rating.value < 1 || this.rating.value > 5) {
            alert('La note doit être comprise entre 1 et 5');
            return;
        }

        // Submit form
        this.submit();
    });
}
