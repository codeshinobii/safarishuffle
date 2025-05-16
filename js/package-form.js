document.addEventListener('DOMContentLoaded', function() {
    const packageForm = document.getElementById('packageForm');
    const API_URL = 'http://localhost:8000/api';

    packageForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        try {
            // Show loading state
            const submitBtn = packageForm.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Saving...';
            submitBtn.disabled = true;

            // Gather form data
            const formData = new FormData();

            // Basic Information
            formData.append('title', document.getElementById('packageTitle').value);
            formData.append('description', document.getElementById('packageOverview').value);
            formData.append('price', document.getElementById('packagePrice').value);
            formData.append('duration', document.getElementById('packageDuration').value);
            formData.append('destination', document.getElementById('packageLocation').value);
            formData.append('type', document.getElementById('packageStatus').value);
            formData.append('status', document.getElementById('packageStatus').value);
            formData.append('is_featured', document.getElementById('packageFeatured').checked);

            // Hero Image
            const heroImage = document.getElementById('packageHeroImage').files[0];
            if (heroImage) {
                formData.append('image', heroImage);
            }

            // Highlights
            const highlights = [];
            document.querySelectorAll('.highlight-item').forEach(item => {
                const title = item.querySelector('.highlight-title').value;
                const desc = item.querySelector('.highlight-desc').value;
                if (title && desc) {
                    highlights.push({ title, description: desc });
                }
            });
            formData.append('highlights', JSON.stringify(highlights));

            // Itinerary
            const itinerary = [];
            document.querySelectorAll('.day-item').forEach((item, index) => {
                const title = item.querySelector('.day-title').value;
                const description = item.querySelector('.day-desc').value;
                const accommodation = item.querySelector('.day-accommodation').value;
                if (title && description) {
                    itinerary.push({
                        day: index + 1,
                        title,
                        description,
                        accommodation
                    });
                }
            });
            formData.append('itinerary', JSON.stringify(itinerary));

            // Inclusions
            const inclusions = [];
            document.querySelectorAll('.inclusion-text').forEach(item => {
                if (item.value) {
                    inclusions.push(item.value);
                }
            });
            formData.append('inclusions', JSON.stringify(inclusions));

            // Exclusions
            const exclusions = [];
            document.querySelectorAll('.exclusion-text').forEach(item => {
                if (item.value) {
                    exclusions.push(item.value);
                }
            });
            formData.append('exclusions', JSON.stringify(exclusions));

            // Gallery Images
            const galleryInput = document.getElementById('galleryInput');
            if (galleryInput.files.length > 0) {
                Array.from(galleryInput.files).forEach((file, index) => {
                    formData.append(`gallery[${index}]`, file);
                });
            }

            // Send to API
            const response = await fetch(`${API_URL}/packages`, {
                method: 'POST',
                body: formData
            });

            if (!response.ok) {
                throw new Error('Failed to save package');
            }

            const result = await response.json();

            // Show success message
            showNotification('Package saved successfully!', 'success');

            // Reset form
            packageForm.reset();

            // Redirect to package list or edit page
            setTimeout(() => {
                window.location.href = 'admin-packages.html';
            }, 2000);

        } catch (error) {
            console.error('Error saving package:', error);
            showNotification('Failed to save package. Please try again.', 'error');
        } finally {
            // Reset button state
            submitBtn.innerHTML = originalBtnText;
            submitBtn.disabled = false;
        }
    });

    // Helper function to show notifications
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} position-fixed top-0 end-0 m-3`;
        notification.style.zIndex = '9999';
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }
}); 