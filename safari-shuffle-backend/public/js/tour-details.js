document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Get package ID from URL
        const urlParams = new URLSearchParams(window.location.search);
        const packageId = urlParams.get('id');

        if (!packageId) {
            throw new Error('No package ID provided');
        }

        // Load package details
        const package = await PackageAPI.getById(packageId);
        renderPackageDetails(package);

        // Add event listeners
        setupEventListeners(package);
    } catch (error) {
        Utils.handleError(error);
        // Redirect to tours page if there's an error
        window.location.href = 'tours.html';
    }
});

function renderPackageDetails(package) {
    // Update page title
    document.title = `${package.title} | Safari Shuffle`;

    // Update main content
    const mainContent = document.getElementById('package-details');
    mainContent.innerHTML = `
        <div class="package-header">
            <div class="package-image">
                <img src="${package.image || 'placeholder.jpg'}" alt="${package.title}">
                ${package.is_featured ? '<span class="featured-badge">Featured</span>' : ''}
            </div>
            <div class="package-info">
                <h1>${package.title}</h1>
                <div class="package-meta">
                    <span>${Utils.formatPrice(package.price)}</span>
                    <span>${package.duration}</span>
                    <span>${package.destination}</span>
                </div>
                <p class="package-description">${package.description}</p>
            </div>
        </div>

        <div class="package-details">
            <div class="package-highlights">
                <h2>Highlights</h2>
                <ul>
                    ${package.highlights.map(highlight => `<li>${highlight}</li>`).join('')}
                </ul>
            </div>

            <div class="package-itinerary">
                <h2>Itinerary</h2>
                <div class="itinerary-steps">
                    ${package.itinerary.map(day => `
                        <div class="itinerary-step">
                            <h3>Day ${day.day}: ${day.title}</h3>
                            <p>${day.description}</p>
                        </div>
                    `).join('')}
                </div>
            </div>

            <div class="package-inclusions">
                <h2>What's Included</h2>
                <ul>
                    ${package.inclusions.map(inclusion => `<li>${inclusion}</li>`).join('')}
                </ul>
            </div>

            <div class="package-exclusions">
                <h2>What's Not Included</h2>
                <ul>
                    ${package.exclusions.map(exclusion => `<li>${exclusion}</li>`).join('')}
                </ul>
            </div>
        </div>

        <div class="package-booking">
            <h2>Book This Tour</h2>
            <form id="booking-form">
                <div class="form-group">
                    <label for="date">Select Date</label>
                    <input type="date" id="date" name="date" required>
                </div>
                <div class="form-group">
                    <label for="pax">Number of People</label>
                    <input type="number" id="pax" name="pax" min="${package.min_pax}" 
                           max="${package.max_pax || ''}" required>
                </div>
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>
                <div class="form-group">
                    <label for="notes">Additional Notes</label>
                    <textarea id="notes" name="notes"></textarea>
                </div>
                <button type="submit" class="book-now-btn">Book Now</button>
            </form>
        </div>
    `;
}

function setupEventListeners(package) {
    // Booking form submission
    document.getElementById('booking-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const bookingData = {
            package_id: package.id,
            date: formData.get('date'),
            pax: formData.get('pax'),
            name: formData.get('name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            notes: formData.get('notes')
        };

        try {
            // Here you would typically send the booking data to your backend
            // For now, we'll just show a success message
            Utils.showNotification('Booking request submitted successfully! We will contact you shortly.');
            e.target.reset();
        } catch (error) {
            Utils.handleError(error);
        }
    });
} 