document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Load packages
        const packages = await PackageAPI.getAll();
        renderPackages(packages);

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
        Utils.handleError(error);
    }
});

function renderPackages(packages) {
    const packagesContainer = document.getElementById('packages-container');
    packagesContainer.innerHTML = '';

    packages.forEach(package => {
        const packageElement = createPackageElement(package);
        packagesContainer.appendChild(packageElement);
    });
}

function createPackageElement(package) {
    const div = document.createElement('div');
    div.className = 'package-card';
    div.innerHTML = `
        <div class="package-image">
            <img src="${package.image || 'placeholder.jpg'}" alt="${package.title}">
        </div>
        <div class="package-info">
            <h3>${package.title}</h3>
            <p>${package.description}</p>
            <div class="package-meta">
                <span>${Utils.formatPrice(package.price)}</span>
                <span>${package.duration}</span>
                <span>${package.status}</span>
            </div>
            <div class="package-actions">
                <button class="edit-btn" data-id="${package.id}">Edit</button>
                <button class="delete-btn" data-id="${package.id}">Delete</button>
                <button class="status-btn" data-id="${package.id}" data-status="${package.status}">
                    ${package.status === 'active' ? 'Archive' : 'Activate'}
                </button>
                <button class="feature-btn" data-id="${package.id}" data-featured="${package.is_featured}">
                    ${package.is_featured ? 'Unfeature' : 'Feature'}
                </button>
            </div>
        </div>
    `;
    return div;
}

function populateFilters(types, destinations) {
    const typeFilter = document.getElementById('type-filter');
    const destinationFilter = document.getElementById('destination-filter');

    types.forEach(type => {
        const option = document.createElement('option');
        option.value = type;
        option.textContent = type;
        typeFilter.appendChild(option);
    });

    destinations.forEach(destination => {
        const option = document.createElement('option');
        option.value = destination;
        option.textContent = destination;
        destinationFilter.appendChild(option);
    });
}

function setupEventListeners() {
    // Filter change events
    document.getElementById('type-filter').addEventListener('change', handleFilterChange);
    document.getElementById('destination-filter').addEventListener('change', handleFilterChange);
    document.getElementById('status-filter').addEventListener('change', handleFilterChange);

    // Add package form
    document.getElementById('add-package-form').addEventListener('submit', handleAddPackage);

    // Package action buttons
    document.addEventListener('click', async (e) => {
        if (e.target.matches('.edit-btn')) {
            const id = e.target.dataset.id;
            await handleEditPackage(id);
        } else if (e.target.matches('.delete-btn')) {
            const id = e.target.dataset.id;
            await handleDeletePackage(id);
        } else if (e.target.matches('.status-btn')) {
            const id = e.target.dataset.id;
            const currentStatus = e.target.dataset.status;
            const newStatus = currentStatus === 'active' ? 'archived' : 'active';
            await handleStatusChange(id, newStatus);
        } else if (e.target.matches('.feature-btn')) {
            const id = e.target.dataset.id;
            await handleToggleFeatured(id);
        }
    });
}

async function handleFilterChange() {
    const filters = {
        type: document.getElementById('type-filter').value,
        destination: document.getElementById('destination-filter').value,
        status: document.getElementById('status-filter').value
    };

    try {
        const packages = await PackageAPI.getAll(filters);
        renderPackages(packages);
    } catch (error) {
        Utils.handleError(error);
    }
}

async function handleAddPackage(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const packageData = Object.fromEntries(formData.entries());

    try {
        await PackageAPI.create(packageData);
        Utils.showNotification('Package added successfully');
        e.target.reset();
        const packages = await PackageAPI.getAll();
        renderPackages(packages);
    } catch (error) {
        Utils.handleError(error);
    }
}

async function handleEditPackage(id) {
    try {
        const package = await PackageAPI.getById(id);
        // Populate edit form with package data
        // Show edit modal
        // Handle form submission
    } catch (error) {
        Utils.handleError(error);
    }
}

async function handleDeletePackage(id) {
    if (confirm('Are you sure you want to delete this package?')) {
        try {
            await PackageAPI.delete(id);
            Utils.showNotification('Package deleted successfully');
            const packages = await PackageAPI.getAll();
            renderPackages(packages);
        } catch (error) {
            Utils.handleError(error);
        }
    }
}

async function handleStatusChange(id, status) {
    try {
        await PackageAPI.updateStatus(id, status);
        Utils.showNotification('Package status updated successfully');
        const packages = await PackageAPI.getAll();
        renderPackages(packages);
    } catch (error) {
        Utils.handleError(error);
    }
}

async function handleToggleFeatured(id) {
    try {
        await PackageAPI.toggleFeatured(id);
        Utils.showNotification('Package featured status updated successfully');
        const packages = await PackageAPI.getAll();
        renderPackages(packages);
    } catch (error) {
        Utils.handleError(error);
    }
} 