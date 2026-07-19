let currentSettings = {};
let currentFilters = {
    type: '',
    status: '',
    recommender: '',
    tag: '',
    sort: 'created_at',
    order: 'DESC'
};

let currentUser = {};

async function init() {
    currentSettings = await Api.getSettings();
    applySettings(currentSettings);
    currentUser = await Api.getMe();
    await loadRecommenders();
    await loadMedia();
    bindEvents();
}

function applySettings(settings) {
    document.title = settings.site_title || 'Loci';
    document.getElementById('site-title').textContent = settings.site_title || 'Loci';

    const themeLink = document.getElementById('theme-stylesheet');
    themeLink.href = `css/theme-${settings.theme || 'light'}.css`;

    const container = document.getElementById('media-list');
    container.className = settings.view_mode === 'card' ? 'view-card' : 'view-list';

    document.getElementById('sort-by').value = settings.default_sort || 'created_at';
    document.getElementById('sort-dir').value = settings.default_sort_direction || 'DESC';
    document.getElementById('filter-status').value = settings.default_status_filter === 'all' ? '' : (settings.default_status_filter || '');

    currentFilters.sort = settings.default_sort || 'created_at';
    currentFilters.order = settings.default_sort_direction || 'DESC';
}

async function loadRecommenders() {
    const recommenders = await Api.getRecommenders();
    const select = document.getElementById('filter-recommender');
    recommenders.forEach(r => {
        const option = document.createElement('option');
        option.value = r.name;
        option.textContent = r.name;
        select.appendChild(option);
    });
}

async function loadMedia() {
    const params = {};
    if (currentFilters.type)        params.type = currentFilters.type;
    if (currentFilters.status)      params.status = currentFilters.status;
    if (currentFilters.recommender) params.recommender = currentFilters.recommender;
    if (currentFilters.tag)         params.tag = currentFilters.tag;
    if (currentFilters.sort)        params.sort = currentFilters.sort;
    if (currentFilters.order)       params.order = currentFilters.order;

    const items = await Api.getMedia(params);
    renderMedia(items);
}

function renderMedia(items) {
    const container = document.getElementById('media-list');
    container.innerHTML = '';

    if (items.length === 0) {
        container.innerHTML = `<p class="empty">${Lang.media_empty}</p>`;
        return;
    }

    items.forEach(item => {
        container.appendChild(renderItem(item));
    });
}

function renderItem(item) {
    const el = document.createElement('div');
    el.className = `media-item media-type-${item.type} media-status-${item.status}`;
    el.dataset.id = item.id;

    const tags = item.tags.map(t => `<span class="tag">${t.name}</span>`).join('');
    const recommender = item.recommender_name ? `<span class="recommender">${item.recommender_name}</span>` : '';
    const author = item.author ? `<span class="author">${item.author}</span>` : '';
    const showName = item.show_name ? `<span class="show-name">${item.show_name}</span>` : '';
    const url = item.url ? `<a href="${item.url}" target="_blank" rel="noopener noreferrer" class="item-url">${item.url}</a>` : '';
    const deadFlag = item.is_dead ? `<span class="flag flag-dead">${Lang.field_is_dead}</span>` : '';
    const paywallFlag = item.is_paywalled ? `<span class="flag flag-paywall">${Lang.field_is_paywalled}</span>` : '';
    const visibilityIcon = {
        'private': '<span class="material-icons visibility-icon" title="Private">visibility_off</span>',
        'group':   '<span class="material-icons visibility-icon" title="Group">group</span>',
        'public':  '<span class="material-icons visibility-icon" title="Public">public</span>',
    }[item.visibility] ?? '';

    el.innerHTML = `
        <div class="item-header">
            <span class="item-type">${item.type}</span>
            <span class="item-title">${item.title}</span>
            ${author}
            ${showName}
            <span class="item-status">${item.status}</span>
            ${deadFlag}
            ${paywallFlag}
            ${visibilityIcon}
        </div>
        <div class="item-meta">
            ${url}
            ${recommender}
            <span class="item-date">${formatDate(item.created_at)}</span>
        </div>
        <div class="item-tags">${tags}</div>
        <div class="item-actions">
            <button class="btn-edit" data-id="${item.id}">${Lang.edit}</button>
            <button class="btn-delete" data-id="${item.id}">${Lang.delete}</button>
            <button class="btn-status" data-id="${item.id}" data-status="${item.status}">
                ${item.status === 'queue' ? Lang.media_mark_consumed : Lang.media_mark_queue}
            </button>
        </div>
    `;

    return el;
}

function formatDate(dateStr) {
    const date = new Date(dateStr);
    return date.toLocaleDateString(document.documentElement.lang || 'en', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

function bindEvents() {

    document.getElementById('logout-btn').addEventListener('click', async () => {
        await fetch('/logout', { method: 'POST' });
        window.location.href = 'login.php';
    });
    
    document.getElementById('filter-type').addEventListener('change', e => {
        currentFilters.type = e.target.value;
        loadMedia();
    });

    document.getElementById('filter-status').addEventListener('change', e => {
        currentFilters.status = e.target.value;
        loadMedia();
    });

    document.getElementById('filter-recommender').addEventListener('change', e => {
        currentFilters.recommender = e.target.value;
        loadMedia();
    });

    document.getElementById('filter-tag').addEventListener('input', e => {
        currentFilters.tag = e.target.value;
        loadMedia();
    });

    document.getElementById('sort-by').addEventListener('change', e => {
        currentFilters.sort = e.target.value;
        loadMedia();
    });

    document.getElementById('sort-dir').addEventListener('change', e => {
        currentFilters.order = e.target.value;
        loadMedia();
    });

    document.getElementById('view-list').addEventListener('click', () => {
        document.getElementById('media-list').className = 'view-list';
        Api.updateSettings({ view_mode: 'list' });
    });

    document.getElementById('view-card').addEventListener('click', () => {
        document.getElementById('media-list').className = 'view-card';
        Api.updateSettings({ view_mode: 'card' });
    });

    document.getElementById('add-media').addEventListener('click', () => {
        openAddModal();
    });

    document.getElementById('modal-close').addEventListener('click', closeModal);

    document.getElementById('media-list').addEventListener('click', e => {
        const id = parseInt(e.target.dataset.id);

        if (e.target.classList.contains('btn-edit')) {
            openEditModal(id);
        }

        if (e.target.classList.contains('btn-delete')) {
            if (confirm(Lang.media_delete_confirm)) {
                Api.deleteMedia(id).then(loadMedia);
            }
        }

        if (e.target.classList.contains('btn-status')) {
            const newStatus = e.target.dataset.status === 'queue' ? 'consumed' : 'queue';
            const update = { status: newStatus };
            if (newStatus === 'consumed') {
                update.consumed_at = new Date().toISOString().slice(0, 19).replace('T', ' ');
            }
            Api.updateMedia(id, update).then(loadMedia);
        }
    });
}

function buildMediaForm(item = null) {
    return `
        <h2>${item ? Lang.media_edit_title : Lang.media_add_title}</h2>
        <form id="media-form">
            <label>${Lang.field_type}
                <select name="type" required>
                    <option value="url" ${item?.type === 'url' ? 'selected' : ''}>${Lang.type_url}</option>
                    <option value="book" ${item?.type === 'book' ? 'selected' : ''}>${Lang.type_book}</option>
                    <option value="movie" ${item?.type === 'movie' ? 'selected' : ''}>${Lang.type_movie}</option>
                    <option value="podcast" ${item?.type === 'podcast' ? 'selected' : ''}>${Lang.type_podcast}</option>
                </select>
            </label>
            <label>${Lang.field_title} <input type="text" name="title" value="${item?.title || ''}" required></label>
            <label>${Lang.field_author} <input type="text" name="author" value="${item?.author || ''}"></label>
            <label>${Lang.field_url} <input type="url" name="url" value="${item?.url || ''}"></label>
            <label>${Lang.field_notes} <textarea name="notes">${item?.notes || ''}</textarea></label>
            <label>${Lang.field_recommender} <input type="text" name="recommender" value="${item?.recommender_name || ''}"></label>
            <label>${Lang.field_tags} <input type="text" name="tags" value="${item?.tags?.map(t => t.name).join(', ') || ''}" placeholder="${Lang.field_tags_hint}"></label>
            <label>${Lang.field_status}
                <select name="status">
                    <option value="queue" ${item?.status === 'queue' ? 'selected' : ''}>${Lang.status_queue}</option>
                    <option value="consumed" ${item?.status === 'consumed' ? 'selected' : ''}>${Lang.status_consumed}</option>
                </select>
            </label>
            <label>${Lang.field_visibility}
                <select name="visibility">
                    <option value="group" ${item?.visibility === 'group' ? 'selected' : ''}>${Lang.visibility_group}</option>
                    <option value="private" ${item?.visibility === 'private' ? 'selected' : ''}>${Lang.visibility_private}</option>
                    <option value="public" ${item?.visibility === 'public' ? 'selected' : ''}>${Lang.visibility_public}</option>
                </select>
            </label>
            <label>${Lang.field_isbn} <input type="text" name="isbn" value="${item?.isbn || ''}"></label>
            <label>${Lang.field_show_name} <input type="text" name="show_name" value="${item?.show_name || ''}"></label>
            <label>
                <input type="checkbox" name="is_dead" ${item?.is_dead ? 'checked' : ''}> ${Lang.field_is_dead}
            </label>
            <label>
                <input type="checkbox" name="is_paywalled" ${item?.is_paywalled ? 'checked' : ''}> ${Lang.field_is_paywalled}
            </label>
            <button type="submit">${item ? Lang.save : Lang.add}</button>
        </form>
    `;
}

function openAddModal() {
    document.getElementById('modal-content').innerHTML = buildMediaForm();
    document.getElementById('modal-overlay').classList.remove('hidden');

    document.getElementById('media-form').addEventListener('submit', async e => {
        e.preventDefault();
        const data = collectFormData();
        try {
            const result = await Api.createMedia(data);
            if (result.status === 'duplicates_found') {
                showDuplicateReview(result.duplicates, result.incoming);
            } else {
                closeModal();
                loadMedia();
            }
        } catch (err) {
            alert(err.message);
        }
    });
}

async function openEditModal(id) {
    const item = await Api.getMediaItem(id);
    document.getElementById('modal-content').innerHTML = buildMediaForm(item);
    document.getElementById('modal-overlay').classList.remove('hidden');

    document.getElementById('media-form').addEventListener('submit', async e => {
        e.preventDefault();
        const data = collectFormData();
        try {
            await Api.updateMedia(id, data);
            closeModal();
            loadMedia();
        } catch (err) {
            alert(err.message);
        }
    });
}

function collectFormData() {
    const form = document.getElementById('media-form');
    const data = {
        type: form.type.value,
        title: form.title.value,
        author: form.author.value || null,
        url: form.url.value || null,
        notes: form.notes.value || null,
        recommender: form.recommender.value || null,
        tags: form.tags.value ? form.tags.value.split(',').map(t => t.trim()).filter(Boolean) : [],
        status: form.status.value,
        visibility: form.visibility.value,
        isbn: form.isbn.value || null,
        show_name: form.show_name.value || null,
        is_dead: form.is_dead.checked ? 1 : 0,
        is_paywalled: form.is_paywalled.checked ? 1 : 0,
    };
    return data;
}

function closeModal() {
    document.getElementById('modal-overlay').classList.add('hidden');
    document.getElementById('modal-content').innerHTML = '';
}

function showDuplicateReview(duplicates, incoming) {
    const container = document.getElementById('modal-content');
    
    let html = `<h2>Possible Duplicates Found</h2>
    <p>The item you're adding may already exist in your archive. Please review:</p>`;

    duplicates.forEach((dup, index) => {
        html += `
        <div class="duplicate-review" data-index="${index}">
            <div class="duplicate-confidence confidence-${dup.confidence}">
                ${dup.confidence.toUpperCase()} MATCH — Score: ${Math.round(dup.score * 100)}% — ${dup.reason}
            </div>
            <div class="duplicate-comparison">
                <div class="duplicate-col">
                    <h3>Incoming</h3>
                    <p><strong>${incoming.title || ''}</strong></p>
                    ${incoming.author ? `<p>${incoming.author}</p>` : ''}
                    ${incoming.url ? `<p><a href="${incoming.url}" target="_blank">${incoming.url}</a></p>` : ''}
                </div>
                <div class="duplicate-col">
                    <h3>Existing</h3>
                    <p><strong>${dup.existing.title || ''}</strong></p>
                    ${dup.existing.author ? `<p>${dup.existing.author}</p>` : ''}
                    ${dup.existing.url ? `<p><a href="${dup.existing.url}" target="_blank">${dup.existing.url}</a></p>` : ''}
                </div>
            </div>
            <div class="duplicate-actions">
                <button class="btn-keep-existing" data-index="${index}">Keep Existing</button>
                <button class="btn-keep-both" data-index="${index}">Keep Both</button>
            </div>
        </div>`;
    });

    html += `
    <div class="duplicate-global-actions">
        <button id="btn-save-anyway">Save Anyway (Ignore All)</button>
        <button id="btn-cancel-duplicate">Cancel</button>
    </div>`;

    container.innerHTML = html;

    // Keep existing — discard incoming
    container.querySelectorAll('.btn-keep-existing').forEach(btn => {
        btn.addEventListener('click', () => {
            closeModal();
            loadMedia();
        });
    });

    // Keep both — force save
    container.querySelectorAll('.btn-keep-both').forEach(btn => {
        btn.addEventListener('click', async () => {
            const data = { ...incoming, force: true };
            await Api.createMedia(data);
            closeModal();
            loadMedia();
        });
    });

    // Save anyway — force save ignoring all duplicates
    document.getElementById('btn-save-anyway').addEventListener('click', async () => {
        const data = { ...incoming, force: true };
        await Api.createMedia(data);
        closeModal();
        loadMedia();
    });

    // Cancel
    document.getElementById('btn-cancel-duplicate').addEventListener('click', () => {
        closeModal();
    });
}
document.addEventListener('DOMContentLoaded', init);