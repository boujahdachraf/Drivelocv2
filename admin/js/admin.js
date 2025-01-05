document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTables
    const tables = document.querySelectorAll('.datatable');
    tables.forEach(table => {
        $(table).DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/French.json"
            }
        });
    });

    // Handle vehicle image preview
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    if (imageInput && imagePreview) {
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Handle delete confirmations
    document.querySelectorAll('[data-confirm]').forEach(element => {
        element.addEventListener('click', function(e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Handle status updates
    document.querySelectorAll('select[name="status"]').forEach(select => {
        select.addEventListener('change', function() {
            this.closest('form').submit();
        });
    });

    // Initialize Chart.js if stats container exists
    const statsChart = document.getElementById('statsChart');
    if (statsChart) {
        new Chart(statsChart, {
            type: 'line',
            data: {
                labels: statsChart.dataset.labels.split(','),
                datasets: [{
                    label: 'Réservations',
                    data: statsChart.dataset.values.split(','),
                    borderColor: 'rgb(59, 130, 246)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Handle bulk upload
    const bulkUploadForm = document.getElementById('bulk-upload-form');
    if (bulkUploadForm) {
        bulkUploadForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('bulk_upload.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Import réussi: ' + data.message);
                    location.reload();
                } else {
                    alert('Erreur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Une erreur est survenue lors de l\'import');
            });
        });
    }
});
