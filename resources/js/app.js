import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

/**
 * Generic password visibility toggle. Any button with
 * data-password-toggle="#target-input-id" flips that input between
 * type="password" and type="text" and swaps its icon.
 */
document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-password-toggle]');
    if (!toggle) return;

    const input = document.querySelector(toggle.getAttribute('data-password-toggle'));
    if (!input) return;

    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';

    const icon = toggle.querySelector('img');
    if (icon) {
        icon.src = isHidden ? toggle.dataset.iconVisible : toggle.dataset.iconHidden;
        icon.alt = isHidden ? 'Hide password' : 'Show password';
    }
});

/**
 * Drag-and-drop picture picker for the entry create/edit form
 * (resources/views/entries/form.blade.php). Only runs on pages that have
 * a #picture-dropzone element.
 *
 * A plain <input type="file"> can't have individual files removed from
 * its FileList, so newly-added files are kept in a JS array instead and
 * the input's FileList is rebuilt from that array (via DataTransfer)
 * whenever it changes — including right before the form submits.
 *
 * On edit, the entry's already-uploaded pictures are shown in this same
 * pool (via the dropzone's data-existing attribute, not a separate
 * "Existing Pictures" section). Removing one of them only hides it and
 * records its id in a hidden remove_pictures[] input — nothing is
 * actually deleted server-side until the form is submitted and
 * EntryController::update() saves, so a technician can't lose a picture
 * by accidentally clicking remove before pressing Save Changes.
 */
(() => {
    const dropzone = document.getElementById('picture-dropzone');
    if (!dropzone) return;

    const input = document.getElementById('pictures');
    const placeholder = document.getElementById('picture-dropzone-placeholder');
    const previews = document.getElementById('picture-dropzone-previews');
    const warning = document.getElementById('picture-dropzone-warning');
    const removedInputs = document.getElementById('picture-dropzone-removed-inputs');
    const form = dropzone.closest('form');

    const MAX_PICTURES = 5;
    const MAX_TOTAL_BYTES = 10 * 1024 * 1024;

    // Some browsers/OS file-type associations fail to report a MIME type
    // for a dropped file (file.type comes back ""), which silently dropped
    // anything but .jpg here before — fall back to the extension so PNG
    // (and other image types) are recognized just as reliably as JPG.
    const IMAGE_EXTENSION = /\.(jpe?g|png|gif|webp|bmp|svg)$/i;

    function isImageFile(file) {
        return file.type.startsWith('image/') || IMAGE_EXTENSION.test(file.name);
    }

    let existingPictures = [];
    try {
        existingPictures = JSON.parse(dropzone.dataset.existing || '[]');
    } catch {
        existingPictures = [];
    }

    const removedPictureIds = new Set();
    let pendingFiles = [];

    function showWarning(message) {
        warning.textContent = message;
        warning.classList.toggle('hidden', !message);
    }

    function syncInput() {
        const transfer = new DataTransfer();
        pendingFiles.forEach((file) => transfer.items.add(file));
        input.files = transfer.files;
    }

    function syncRemovedInputs() {
        removedInputs.innerHTML = '';
        removedPictureIds.forEach((id) => {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'remove_pictures[]';
            hidden.value = id;
            removedInputs.appendChild(hidden);
        });
    }

    function remainingExisting() {
        return existingPictures.filter((picture) => !removedPictureIds.has(picture.id));
    }

    function addThumbnail(url, name, onRemove) {
        const wrapper = document.createElement('div');
        wrapper.className = 'relative';

        const img = document.createElement('img');
        img.src = url;
        img.alt = name;
        img.className = 'h-20 w-full rounded-md border border-brand-light object-cover';
        wrapper.appendChild(img);

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.setAttribute('aria-label', `Remove ${name}`);
        remove.className = 'absolute -right-1.5 -top-1.5 inline-flex h-6 w-6 items-center justify-center rounded-full bg-brand-darkest text-brand-pale shadow transition-colors hover:bg-brand-primary';
        remove.textContent = '×';
        remove.addEventListener('click', (event) => {
            // Without this, the click bubbles up to the dropzone's own
            // click handler and immediately reopens the file browser.
            event.stopPropagation();
            onRemove();
        });
        wrapper.appendChild(remove);

        previews.appendChild(wrapper);
    }

    function renderPreviews() {
        previews.innerHTML = '';

        const kept = remainingExisting();
        const hasFiles = kept.length > 0 || pendingFiles.length > 0;
        placeholder.classList.toggle('hidden', hasFiles);
        previews.classList.toggle('hidden', !hasFiles);
        previews.classList.toggle('grid', hasFiles);

        kept.forEach((picture) => {
            addThumbnail(picture.url, picture.name, () => {
                removedPictureIds.add(picture.id);
                syncRemovedInputs();
                renderPreviews();
            });
        });

        pendingFiles.forEach((file, index) => {
            addThumbnail(URL.createObjectURL(file), file.name, () => {
                pendingFiles.splice(index, 1);
                syncInput();
                renderPreviews();
            });
        });

        const totalBytes = kept.reduce((sum, picture) => sum + picture.size, 0)
            + pendingFiles.reduce((sum, file) => sum + file.size, 0);
        const totalCount = kept.length + pendingFiles.length;

        if (totalCount > MAX_PICTURES) {
            showWarning(`Only ${MAX_PICTURES} pictures are allowed per entry.`);
        } else if (totalBytes > MAX_TOTAL_BYTES) {
            showWarning('Pictures may not exceed 10 MB in total.');
        } else {
            showWarning('');
        }
    }

    function addFiles(fileList) {
        pendingFiles = pendingFiles.concat(Array.from(fileList).filter(isImageFile));
        syncInput();
        renderPreviews();
    }

    dropzone.addEventListener('click', () => input.click());

    input.addEventListener('change', () => addFiles(input.files));

    ['dragover', 'dragleave', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            event.stopPropagation();
            dropzone.classList.toggle('border-brand-primary', eventName === 'dragover');
        });
    });

    dropzone.addEventListener('drop', (event) => {
        if (event.dataTransfer?.files?.length) {
            addFiles(event.dataTransfer.files);
        }
    });

    form?.addEventListener('submit', syncInput);

    renderPreviews();
})();
