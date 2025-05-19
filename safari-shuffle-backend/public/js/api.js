const API_BASE_URL = 'http://localhost:8000/api';

// Package API functions
const PackageAPI = {
    // Get all packages with optional filters
    getAll: async (filters = {}) => {
        const queryParams = new URLSearchParams(filters);
        const response = await fetch(`${API_BASE_URL}/packages?${queryParams}`);
        return response.json();
    },

    // Get a single package by ID
    getById: async (id) => {
        const response = await fetch(`${API_BASE_URL}/packages/${id}`);
        return response.json();
    },

    // Create a new package
    create: async (packageData) => {
        const formData = new FormData();
        Object.keys(packageData).forEach(key => {
            if (key === 'image' && packageData[key] instanceof File) {
                formData.append('image', packageData[key]);
            } else if (typeof packageData[key] === 'object') {
                formData.append(key, JSON.stringify(packageData[key]));
            } else {
                formData.append(key, packageData[key]);
            }
        });

        const response = await fetch(`${API_BASE_URL}/packages`, {
            method: 'POST',
            body: formData
        });
        return response.json();
    },

    // Update a package
    update: async (id, packageData) => {
        const formData = new FormData();
        Object.keys(packageData).forEach(key => {
            if (key === 'image' && packageData[key] instanceof File) {
                formData.append('image', packageData[key]);
            } else if (typeof packageData[key] === 'object') {
                formData.append(key, JSON.stringify(packageData[key]));
            } else {
                formData.append(key, packageData[key]);
            }
        });

        const response = await fetch(`${API_BASE_URL}/packages/${id}`, {
            method: 'PUT',
            body: formData
        });
        return response.json();
    },

    // Delete a package
    delete: async (id) => {
        const response = await fetch(`${API_BASE_URL}/packages/${id}`, {
            method: 'DELETE'
        });
        return response.json();
    },

    // Update package status
    updateStatus: async (id, status) => {
        const response = await fetch(`${API_BASE_URL}/packages/${id}/status`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ status })
        });
        return response.json();
    },

    // Toggle featured status
    toggleFeatured: async (id) => {
        const response = await fetch(`${API_BASE_URL}/packages/${id}/toggle-featured`, {
            method: 'PUT'
        });
        return response.json();
    },

    // Get all package types
    getTypes: async () => {
        const response = await fetch(`${API_BASE_URL}/packages/types`);
        return response.json();
    },

    // Get all destinations
    getDestinations: async () => {
        const response = await fetch(`${API_BASE_URL}/packages/destinations`);
        return response.json();
    }
};

// Utility functions
const Utils = {
    // Format price with currency
    formatPrice: (price) => {
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        }).format(price);
    },

    // Format date
    formatDate: (date) => {
        return new Date(date).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    },

    // Show notification
    showNotification: (message, type = 'success') => {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    },

    // Handle API errors
    handleError: (error) => {
        console.error('API Error:', error);
        Utils.showNotification(error.message || 'An error occurred', 'error');
    }
}; 