/**
 * Favorites Management System
 * Handles star clicks and favorites navigation
 */

class FavoritesManager {
    constructor() {
        this.csrfToken = window.csrfToken || '';
        this.favoritesNav = document.getElementById('favorites-nav');
        this.init();
    }

    init() {
        this.loadFavorites();
        this.bindStarClicks();
        this.checkCurrentPageFavoriteStatus();
    }

    /**
     * Load and display favorites in header navigation
     */
    async loadFavorites() {
        try {
            const response = await fetch('/api/favorites', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': this.csrfToken
                }
            });

            const data = await response.json();

            if (data.success && data.favorites) {
                this.renderFavoritesNav(data.favorites);
            }
        } catch (error) {
            console.error('Failed to load favorites:', error);
        }
    }

    /**
     * Render favorites in the header navigation
     */
    renderFavoritesNav(favorites) {
        if (!this.favoritesNav || favorites.length === 0) {
            return;
        }

        // Clear existing favorites
        this.favoritesNav.innerHTML = '';

        // Add separator if we have favorites
        if (favorites.length > 0) {
            const separator = document.createElement('div');
            separator.className = 'h-4 w-px bg-indigo-300 mx-2';
            this.favoritesNav.appendChild(separator);
        }

        // Add each favorite
        favorites.forEach(favorite => {
            const favoriteLink = this.createFavoriteLink(favorite);
            this.favoritesNav.appendChild(favoriteLink);
        });
    }

    /**
     * Create a favorite link element
     */
    createFavoriteLink(favorite) {
        const link = document.createElement('a');
        link.href = this.getFavoriteUrl(favorite);
        link.className = 'flex items-center px-2 py-1 text-sm text-indigo-100 hover:text-white hover:bg-indigo-500 rounded transition-colors';
        link.title = favorite.page_title;

        // Add icon if available
        if (favorite.page_icon) {
            const icon = document.createElement('span');
            icon.className = 'mr-1';
            icon.textContent = favorite.page_icon;
            link.appendChild(icon);
        }

        // Add title (truncated if too long)
        const title = document.createElement('span');
        title.textContent = favorite.page_title.length > 20 
            ? favorite.page_title.substring(0, 20) + '...' 
            : favorite.page_title;
        link.appendChild(title);

        return link;
    }

    /**
     * Get URL for a favorite
     */
    getFavoriteUrl(favorite) {
        if (favorite.page_url) {
            return favorite.page_url;
        }

        // Generate URL based on type and ID
        switch (favorite.favorite_type) {
            case 'project':
                return `/projects/view/${favorite.favorite_id}`;
            case 'task':
                return `/tasks/view/${favorite.favorite_id}`;
            case 'milestone':
                return `/milestones/view/${favorite.favorite_id}`;
            case 'sprint':
                return `/sprints/view/${favorite.favorite_id}`;
            default:
                return '#';
        }
    }

    /**
     * Bind click events to star buttons
     */
    bindStarClicks() {
        document.addEventListener('click', (e) => {
            const starButton = e.target.closest('.favorite-star');
            if (starButton) {
                e.preventDefault();
                this.handleStarClick(starButton);
            }
        });
    }

    /**
     * Handle star button click
     */
    async handleStarClick(starButton) {
        const isFavorited = starButton.classList.contains('favorited');
        const favoriteData = this.extractFavoriteData(starButton);

        if (!favoriteData) {
            console.error('Could not extract favorite data from star button');
            return;
        }

        try {
            if (isFavorited) {
                await this.removeFavorite(favoriteData);
                this.updateStarAppearance(starButton, false);
            } else {
                await this.addFavorite(favoriteData);
                this.updateStarAppearance(starButton, true);
            }

            // Reload favorites navigation
            this.loadFavorites();
        } catch (error) {
            console.error('Failed to toggle favorite:', error);
            this.showNotification('Failed to update favorite', 'error');
        }
    }

    /**
     * Extract favorite data from star button
     */
    extractFavoriteData(starButton) {
        const type = starButton.dataset.type;
        const itemId = starButton.dataset.itemId ? parseInt(starButton.dataset.itemId) : null;
        const title = starButton.dataset.title;
        const url = starButton.dataset.url || null;
        const icon = starButton.dataset.icon || '⭐';

        if (!type || !title) {
            return null;
        }

        return { type, item_id: itemId, title, url, icon };
    }

    /**
     * Add a favorite
     */
    async addFavorite(favoriteData) {
        const response = await fetch('/api/favorites/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': this.csrfToken
            },
            body: JSON.stringify(favoriteData)
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to add favorite');
        }

        this.showNotification('Added to favorites!', 'success');
        return data;
    }

    /**
     * Remove a favorite
     */
    async removeFavorite(favoriteData) {
        const response = await fetch('/api/favorites/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': this.csrfToken
            },
            body: JSON.stringify({
                type: favoriteData.type,
                item_id: favoriteData.item_id,
                url: favoriteData.url
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.error || 'Failed to remove favorite');
        }

        this.showNotification('Removed from favorites!', 'success');
        return data;
    }

    /**
     * Update star button appearance
     */
    updateStarAppearance(starButton, isFavorited) {
        const svg = starButton.querySelector('svg');
        
        if (isFavorited) {
            starButton.classList.add('favorited');
            starButton.classList.remove('text-gray-400');
            starButton.classList.add('text-yellow-400');
            if (svg) {
                svg.setAttribute('fill', 'currentColor');
            }
        } else {
            starButton.classList.remove('favorited');
            starButton.classList.remove('text-yellow-400');
            starButton.classList.add('text-gray-400');
            if (svg) {
                svg.setAttribute('fill', 'none');
            }
        }
    }

    /**
     * Check if current page is favorited and update star appearance
     */
    async checkCurrentPageFavoriteStatus() {
        const starButtons = document.querySelectorAll('.favorite-star');
        
        for (const starButton of starButtons) {
            const favoriteData = this.extractFavoriteData(starButton);
            if (favoriteData) {
                try {
                    const isFavorited = await this.checkIfFavorited(favoriteData);
                    this.updateStarAppearance(starButton, isFavorited);
                } catch (error) {
                    console.error('Failed to check favorite status:', error);
                }
            }
        }
    }

    /**
     * Check if an item is favorited
     */
    async checkIfFavorited(favoriteData) {
        const params = new URLSearchParams({
            type: favoriteData.type
        });

        if (favoriteData.item_id) {
            params.append('item_id', favoriteData.item_id.toString());
        }

        if (favoriteData.url) {
            params.append('url', favoriteData.url);
        }

        const response = await fetch(`/api/favorites/check?${params}`, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': this.csrfToken
            }
        });

        const data = await response.json();
        return data.success && data.is_favorited;
    }

    /**
     * Show notification to user
     */
    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-md text-white transition-opacity duration-300 ${
            type === 'success' ? 'bg-green-500' : 
            type === 'error' ? 'bg-red-500' : 'bg-blue-500'
        }`;
        notification.textContent = message;

        document.body.appendChild(notification);

        // Remove after 3 seconds
        setTimeout(() => {
            notification.style.opacity = '0';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
}

// Initialize favorites manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new FavoritesManager();
});
