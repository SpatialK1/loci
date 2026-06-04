const Api = (() => {
    const BASE_URL = '';

    async function request(method, path, data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json'
            }
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(`${BASE_URL}/${path}`, options);

        if (response.status === 401) {
            window.location.href = '/login.html';
            return;
        }

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.error || 'An error occurred');
        }

        return response.json();
    }

    return {
        // Media
        getMedia: (params = {}) => {
            const query = new URLSearchParams(params).toString();
            return request('GET', `media${query ? '?' + query : ''}`);
        },
        getMediaItem: (id) => request('GET', `media/${id}`),
        createMedia: (data) => request('POST', 'media', data),
        updateMedia: (id, data) => request('PUT', `media/${id}`, data),
        deleteMedia: (id) => request('DELETE', `media/${id}`),

        // Tags
        getTags: () => request('GET', 'tags'),
        createTag: (name) => request('POST', 'tags', { name }),
        updateTag: (id, name) => request('PUT', `tags/${id}`, { name }),
        deleteTag: (id) => request('DELETE', `tags/${id}`),

        // Recommenders
        getRecommenders: () => request('GET', 'recommenders'),
        createRecommender: (name) => request('POST', 'recommenders', { name }),
        updateRecommender: (id, name) => request('PUT', `recommenders/${id}`, { name }),
        deleteRecommender: (id) => request('DELETE', `recommenders/${id}`),

        // Lists
        getLists: () => request('GET', 'lists'),
        getList: (id) => request('GET', `lists/${id}`),
        createList: (data) => request('POST', 'lists', data),
        updateList: (id, data) => request('PUT', `lists/${id}`, data),
        deleteList: (id) => request('DELETE', `lists/${id}`),
        addToList: (listId, mediaId) => request('POST', `lists/${listId}/media`, { media_id: mediaId }),
        removeFromList: (listId, mediaId) => request('DELETE', `lists/${listId}/media/${mediaId}`),

        // Settings
        getSettings: () => request('GET', 'settings'),
        updateSettings: (data) => request('PUT', 'settings', data),
    };
})();