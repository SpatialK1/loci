document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('add-form');
    const message = document.getElementById('form-message');
    const typeSelect = document.getElementById('type-select');

    // Update default status when type changes
    typeSelect.addEventListener('change', () => {
        const statusSelect = form.querySelector('[name="status"]');
        statusSelect.value = typeSelect.value === 'url' ? 'acquired' : 'find';
    });

    // Cancel button — close popup or go back
    document.getElementById('cancel-btn').addEventListener('click', () => {
        if (window.opener) {
            window.close();
        } else {
            window.location.href = 'media.php';
        }
    });

    form.addEventListener('submit', async e => {
        e.preventDefault();

        const data = {
            type:        form.type.value,
            title:       form.title.value,
            author:      form.author.value || null,
            url:         form.url.value || null,
            notes:       form.notes.value || null,
            recommender: form.recommender.value || null,
            tags:        form.tags.value ? form.tags.value.split(',').map(t => t.trim()).filter(Boolean) : [],
            status:      form.status.value,
            visibility:  form.visibility.value,
        };

        try {
            const result = await Api.createMedia(data);

            if (result.status === 'duplicates_found') {
                message.textContent = Lang.import_duplicates + ' — ' + result.duplicates[0].reason;
                message.classList.remove('hidden');
                return;
            }

            if (result.error) {
                message.textContent = result.error;
                message.classList.remove('hidden');
                return;
            }

            // Success
            message.textContent = '✓ ' + result.title;
            message.classList.remove('hidden');
            message.style.color = 'var(--color-text)';

            setTimeout(() => {
                if (window.opener) {
                    window.close();
                } else {
                    window.location.href = 'media.php';
                }
            }, 1500);

        } catch (err) {
            message.textContent = Lang.error;
            message.classList.remove('hidden');
        }
    });
});