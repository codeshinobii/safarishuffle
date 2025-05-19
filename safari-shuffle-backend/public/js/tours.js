document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Show loading states
        document.getElementById('featured-packages').innerHTML = '<div class="loading">Loading featured tours...</div>';
        document.getElementById('all-packages').innerHTML = '<div class="loading">Loading all tours...</div>';

        // Load featured packages
        const featuredPackages = await PackageAPI.getAll({ featured: true, status: 'active' });
        renderFeaturedPackages(featuredPackages);

        // Load all packages
        const allPackages = await PackageAPI.getAll({ status: 'active' });
        renderAllPackages(allPackages);

        // Load package types and destinations for filters
        const [types, destinations] = await Promise.all([
            PackageAPI.getTypes(),
            PackageAPI.getDestinations()
        ]);

        // Populate filter dropdowns
        populateFilters(types, destinations);

        // Add event listeners
        setupEventListeners();
    } catch (error) {
        console.error('Error loading packages:', error);
        showError('Failed to load tours. Please try again later.');
    }
});

function renderFeaturedPackages(packages) {
    const featuredContainer = document.getElementById('featured-packages');
    
    if (!packages || packages.length === 0) {
        featuredContainer.innerHTML = '<div class="error-message">No featured tours available at the moment.</div>';
        return;
    }

    featuredContainer.innerHTML = '';
    packages.forEach(package => {
        const packageElement = createPackageCard(package);
        featuredContainer.appendChild(packageElement);
    });
}

function renderAllPackages(packages) {
    const packagesContainer = document.getElementById('all-packages');
    
    if (!packages || packages.length === 0) {
        packagesContainer.innerHTML = '<div class="error-message">No tours available at the moment.</div>';
        return;
    }

    packagesContainer.innerHTML = '';
    packages.forEach(package => {
        const packageElement = createPackageCard(package);
        packagesContainer.appendChild(packageElement);
    });
}

function createPackageCard(package) {
    const div = document.createElement('div');
    div.className = 'package-card';
    div.innerHTML = `
        <div class="package-image">
            <img src="${package.image ? `storage/${package.image}` : 'images/placeholder.jpg'}" alt="${package.title}">
            ${package.is_featured ? '<span class="featured-badge">Featured</span>' : ''}
        </div>
        <div class="package-info">
            <h3>${package.title}</h3>
            <p>${package.description.substring(0, 150)}${package.description.length > 150 ? '...' : ''}</p>
            <div class="package-meta">
                <span>${Utils.formatPrice(package.price)}</span>
                <span>${package.duration}</span>
                <span>${package.destination}</span>
            </div>
            <div class="package-highlights">
                ${package.highlights ? package.highlights.slice(0, 3).map(highlight => 
                    `<span>${highlight}</span>`
                ).join('') : ''}
            </div>
            <a href="tour-details.html?id=${package.id}" class="view-details-btn">View Details</a>
        </div>
    `;
    return div;
}

function populateFilters(types, destinations) {
    const typeFilter = document.getElementById('type-filter');
    const destinationFilter = document.getElementById('destination-filter');

    // Clear existing options except the first one
    while (typeFilter.options.length > 1) typeFilter.remove(1);
    while (destinationFilter.options.length > 1) destinationFilter.remove(1);

    // Add type options
    types.forEach(type => {
        if (type) { // Only add non-empty types
            const option = document.createElement('option');
            option.value = type;
            option.textContent = type;
            typeFilter.appendChild(option);
        }
    });

    // Add destination options
    destinations.forEach(destination => {
        if (destination) { // Only add non-empty destinations
            const option = document.createElement('option');
            option.value = destination;
            option.textContent = destination;
            destinationFilter.appendChild(option);
        }
    });
}

function setupEventListeners() {
    // Filter change events
    document.getElementById('type-filter').addEventListener('change', handleFilterChange);
    document.getElementById('destination-filter').addEventListener('change', handleFilterChange);
    document.getElementById('price-filter').addEventListener('change', handleFilterChange);

    // Search input with debounce
    let searchTimeout;
    document.getElementById('search-input').addEventListener('input', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => handleSearch(e), 300);
    });
}

async function handleFilterChange() {
    try {
        const filters = {
            type: document.getElementById('type-filter').value,
            destination: document.getElementById('destination-filter').value,
            price: document.getElementById('price-filter').value,
            status: 'active' // Only show active packages
        };

        // Show loading state
        document.getElementById('all-packages').innerHTML = '<div class="loading">Loading tours...</div>';

        const packages = await PackageAPI.getAll(filters);
        renderAllPackages(packages);
    } catch (error) {
        console.error('Error filtering packages:', error);
        showError('Failed to filter tours. Please try again.');
    }
}

async function handleSearch(e) {
    try {
        const searchTerm = e.target.value.toLowerCase().trim();
        
        if (searchTerm.length < 2) {
            // If search term is too short, show all packages
            const packages = await PackageAPI.getAll({ status: 'active' });
            renderAllPackages(packages);
            return;
        }

        // Show loading state
        document.getElementById('all-packages').innerHTML = '<div class="loading">Searching tours...</div>';

        const packages = await PackageAPI.getAll({ status: 'active' });
        const filteredPackages = packages.filter(package => 
            package.title.toLowerCase().includes(searchTerm) ||
            package.description.toLowerCase().includes(searchTerm) ||
            package.destination.toLowerCase().includes(searchTerm)
        );
        renderAllPackages(filteredPackages);
    } catch (error) {
        console.error('Error searching packages:', error);
        showError('Failed to search tours. Please try again.');
    }
}

function showError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.textContent = message;
    
    const container = document.getElementById('all-packages');
    container.innerHTML = '';
    container.appendChild(errorDiv);
} 