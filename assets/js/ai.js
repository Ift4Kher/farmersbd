/**
 * FarmersBD — AI Disease Detection JavaScript
 */

'use strict';

document.addEventListener('DOMContentLoaded', () => {
    initAIUpload();
});

function initAIUpload() {
    const dropZone   = document.getElementById('ai-drop-zone');
    const fileInput  = document.getElementById('ai-file-input');
    const preview    = document.getElementById('ai-preview');
    const previewBox = document.getElementById('ai-preview-box');
    const form       = document.getElementById('ai-upload-form');
    const submitBtn  = document.getElementById('ai-submit-btn');
    const clearBtn   = document.getElementById('ai-clear-btn');

    if (!dropZone || !fileInput) return;

    // Click on zone opens file picker
    dropZone.addEventListener('click', () => fileInput.click());

    // File input change
    fileInput.addEventListener('change', () => handleFile(fileInput.files[0]));

    // Drag and drop
    ['dragenter', 'dragover'].forEach(e => {
        dropZone.addEventListener(e, ev => {
            ev.preventDefault();
            dropZone.classList.add('drag-over');
        });
    });
    ['dragleave', 'drop'].forEach(e => {
        dropZone.addEventListener(e, ev => {
            ev.preventDefault();
            dropZone.classList.remove('drag-over');
            if (e === 'drop' && ev.dataTransfer?.files[0]) {
                fileInput.files = ev.dataTransfer.files;
                handleFile(ev.dataTransfer.files[0]);
            }
        });
    });

    function handleFile(file) {
        if (!file) return;

        // Client-side type check (server re-validates)
        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!allowed.includes(file.type)) {
            showToast('শুধুমাত্র JPG, PNG, WEBP ফরম্যাট গ্রহণযোগ্য।', 'danger');
            return;
        }

        // 50MB check
        if (file.size > 50 * 1024 * 1024) {
            showToast('ফাইলের আকার সর্বোচ্চ ৫০ MB হতে পারবে।', 'danger');
            return;
        }

        const reader = new FileReader();
        reader.onload = e => {
            if (preview) preview.src = e.target.result;
            if (previewBox) previewBox.classList.remove('d-none');
            dropZone.classList.add('d-none');
            if (submitBtn) submitBtn.disabled = false;
        };
        reader.readAsDataURL(file);
    }

    // Clear selection
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            fileInput.value = '';
            if (preview) preview.src = '';
            if (previewBox) previewBox.classList.add('d-none');
            dropZone.classList.remove('d-none');
            if (submitBtn) submitBtn.disabled = true;
        });
    }

    // Form submission UI
    if (form) {
        form.addEventListener('submit', () => {
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>বিশ্লেষণ হচ্ছে…';
            }
        });
    }
}
