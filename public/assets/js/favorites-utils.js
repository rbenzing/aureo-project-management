/**
 * Favorites Utilities
 * Helper functions for managing favorites cache across the application
 */

/**
 * Refresh favorites cache
 * Call this when favorites might have changed outside of the normal flow
 */
function refreshFavorites() {
    if (window.favoritesManager) {
        window.favoritesManager.refreshFavorites();
    }
}

/**
 * Clear favorites cache
 * Useful for logout or when switching users
 */
function clearFavoritesCache() {
    if (window.favoritesManager) {
        window.favoritesManager.clearFavoritesCache();
    }
}

/**
 * Get favorites cache status
 * Returns information about the current cache state
 */
function getFavoritesCacheStatus() {
    if (window.favoritesManager) {
        return window.favoritesManager.getCacheStatus();
    }
    return { cached: false, expired: true };
}

/**
 * Force reload favorites from server
 * Bypasses cache completely
 */
function forceReloadFavorites() {
    if (window.favoritesManager) {
        window.favoritesManager.loadFavorites(true);
    }
}

// Make functions globally available
window.refreshFavorites = refreshFavorites;
window.clearFavoritesCache = clearFavoritesCache;
window.getFavoritesCacheStatus = getFavoritesCacheStatus;
window.forceReloadFavorites = forceReloadFavorites;

// Debug function for development
window.debugFavorites = function() {
    const status = getFavoritesCacheStatus();
    console.log('Favorites Cache Status:', status);

    if (status.cached) {
        console.log(`Cache expires in: ${Math.round(status.expiresIn / 1000)} seconds`);
        console.log(`Cached items: ${status.itemCount}`);
    }
};

// Add event listeners for logout links when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Find all logout links and add cache clearing functionality
    const logoutLinks = document.querySelectorAll('a[href="/logout"]');

    logoutLinks.forEach(link => {
        link.addEventListener('click', function() {
            // Clear favorites cache before logout
            clearFavoritesCache();
        });
    });
});
