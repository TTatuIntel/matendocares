<!-- Enhanced Patient Documents Management -->
@extends('patient.layout')

@section('title', 'Documents')
@section('page-title', 'Medical Documents')
@section('page-description', 'Securely manage your healthcare documents and records')

@section('content')
<!-- Add this right after your @section('content') opening tag -->

<script data-documents type="application/json">
    {!! json_encode($documents ?? []) !!}
</script>

<!-- Make sure you have CSRF token in your head section -->
@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

<!-- Fix the JavaScript syntax error in formatFileSize function -->
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let documents = [];
    let filteredDocuments = [];

    // Safely get documents data
    try {
        const documentsData = document.querySelector('script[data-documents]');
        if (documentsData) {
            documents = JSON.parse(documentsData.textContent);
            filteredDocuments = [...documents];
        }
    } catch (error) {
        console.warn('Could not load documents data:', error);
    }

    // Setup all event listeners
    function setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', filterDocuments);
        }

        // Category filter
        const categoryFilter = document.getElementById('categoryFilter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', filterDocuments);
        }

        // Sort filter
        const sortFilter = document.getElementById('sortFilter');
        if (sortFilter) {
            sortFilter.addEventListener('change', filterDocuments);
        }

        // File input change
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.addEventListener('change', handleFileSelect);
        }

        // Upload form submission
        const uploadForm = document.getElementById('uploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', handleUpload);
        }

        // Drag and drop
        const dropZone = document.getElementById('dropZone');
        if (dropZone) {
            dropZone.addEventListener('dragover', handleDragOver);
            dropZone.addEventListener('drop', handleDrop);
            dropZone.addEventListener('dragenter', handleDragEnter);
            dropZone.addEventListener('dragleave', handleDragLeave);
        }
    }

    // Filter and search functions
    function filterDocuments() {
        const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
        const categoryFilter = document.getElementById('categoryFilter')?.value || '';
        const sortFilter = document.getElementById('sortFilter')?.value || 'newest';

        // Filter documents
        filteredDocuments = documents.filter(doc => {
            const matchesSearch = !searchTerm ||
                doc.title?.toLowerCase().includes(searchTerm) ||
                doc.category?.toLowerCase().includes(searchTerm) ||
                (doc.tags && doc.tags.toLowerCase().includes(searchTerm));

            const matchesCategory = !categoryFilter || doc.category === categoryFilter;

            return matchesSearch && matchesCategory;
        });

        // Sort documents
        sortDocuments(sortFilter);

        // Update display
        updateDocumentsDisplay();
    }

    function sortDocuments(sortBy) {
        switch (sortBy) {
            case 'oldest':
                filteredDocuments.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                break;
            case 'name':
                filteredDocuments.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
                break;
            case 'size':
                filteredDocuments.sort((a, b) => (b.file_size || 0) - (a.file_size || 0));
                break;
            case 'newest':
            default:
                filteredDocuments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                break;
        }
    }

    function updateDocumentsDisplay() {
        // This would update the documents grid
        // Implementation depends on your specific UI structure
        console.log('Filtered documents:', filteredDocuments.length);
    }

    // File handling functions
    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            if (validateFile(file)) {
                displayFilePreview(file);
            }
        }
    }

    function handleDragEnter(event) {
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.add('border-blue-400', 'bg-blue-50');
    }

    function handleDragLeave(event) {
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.remove('border-blue-400', 'bg-blue-50');
    }

    function handleDragOver(event) {
        event.preventDefault();
        event.stopPropagation();
    }

    function handleDrop(event) {
        event.preventDefault();
        event.stopPropagation();

        const dropZone = event.currentTarget;
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');

        const files = event.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (validateFile(file)) {
                const fileInput = document.getElementById('fileInput');
                if (fileInput) {
                    // Create a new FileList-like object
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    fileInput.files = dt.files;
                    displayFilePreview(file);
                }
            }
        }
    }

    function validateFile(file) {
        // Check file size (10MB limit)
        const maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if (file.size > maxSize) {
            showError('File size must be less than 10MB');
            return false;
        }

        // Check file type
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'text/plain'
        ];

        if (!allowedTypes.includes(file.type)) {
            showError('Invalid file type. Please upload PDF, DOC, DOCX, JPG, PNG, or TXT files only.');
            return false;
        }

        return true;
    }

    function displayFilePreview(file) {
        const preview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const fileIcon = document.getElementById('fileIcon');
        const dropZone = document.getElementById('dropZone');

        if (fileName) fileName.textContent = file.name;
        if (fileSize) fileSize.textContent = formatFileSize(file.size);
        if (fileIcon) fileIcon.textContent = getFileIcon(file.type);

        if (preview) preview.classList.remove('hidden');
        if (dropZone) dropZone.style.display = 'none';
    }

    function hideFilePreview() {
        const preview = document.getElementById('filePreview');
        const dropZone = document.getElementById('dropZone');

        if (preview) preview.classList.add('hidden');
        if (dropZone) dropZone.style.display = 'block';
    }

    function removeFile() {
        const fileInput = document.getElementById('fileInput');
        if (fileInput) fileInput.value = '';
        hideFilePreview();
    }

    // Upload handling
    // async function handleUpload(event) {
    //     event.preventDefault();

    //     const uploadBtn = document.getElementById('uploadBtn');
    //     const uploadBtnText = document.getElementById('uploadBtnText');
    //     const uploadSpinner = document.getElementById('uploadSpinner');

    //     // Validate form
    //     const form = event.target;
    //     const formData = new FormData(form);

    //     if (!formData.get('title')?.trim()) {
    //         showError('Please enter a document title');
    //         return;
    //     }

    //     if (!formData.get('category')) {
    //         showError('Please select a category');
    //         return;
    //     }

    //     if (!formData.get('file')) {
    //         showError('Please select a file to upload');
    //         return;
    //     }

    //     // Show loading state
    //     if (uploadBtn) uploadBtn.disabled = true;
    //     if (uploadBtnText) uploadBtnText.textContent = 'Uploading...';
    //     if (uploadSpinner) uploadSpinner.classList.remove('hidden');

    //     try {
    //         // Get CSRF token
    //         const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    //         if (!csrfToken) {
    //             throw new Error('CSRF token not found. Please refresh the page.');
    //         }

    //         const response = await fetch(form.action, {
    //             method: 'POST',
    //             body: formData,
    //             headers: {
    //                 'X-CSRF-TOKEN': csrfToken,
    //                 'Accept': 'application/json',
    //                 'X-Requested-With': 'XMLHttpRequest'
    //             }
    //         });

    //         let result;
    //         const contentType = response.headers.get('content-type');

    //         if (contentType && contentType.includes('application/json')) {
    //             result = await response.json();
    //         } else {
    //             const text = await response.text();
    //             console.error('Non-JSON response:', text);
    //             throw new Error('Server returned an invalid response');
    //         }

    //         if (!response.ok) {
    //             let errorMessage = 'Upload failed';

    //             if (result.message) {
    //                 errorMessage = result.message;
    //             } else if (result.errors) {
    //                 if (typeof result.errors === 'object') {
    //                     errorMessage = Object.values(result.errors).flat().join('\n');
    //                 } else {
    //                     errorMessage = result.errors;
    //                 }
    //             }

    //             throw new Error(errorMessage);
    //         }

    //         showToast('Document uploaded successfully!', 'success');
    //         closeUploadModal();

    //         // Refresh the page to show new document
    //         setTimeout(() => {
    //             window.location.reload();
    //         }, 1000);

    //     } catch (error) {
    //         console.error('Upload error:', error);
    //         showError(error.message || 'Error uploading document. Please try again.');
    //     } finally {
    //         // Reset button state
    //         if (uploadBtn) uploadBtn.disabled = false;
    //         if (uploadBtnText) uploadBtnText.textContent = 'Upload Document';
    //         if (uploadSpinner) uploadSpinner.classList.add('hidden');
    //     }
    // }

// Replace your handleUpload function with this improved version:

async function handleUpload(event) {
    event.preventDefault();

    const uploadBtn = document.getElementById('uploadBtn');
    const uploadBtnText = document.getElementById('uploadBtnText');
    const uploadSpinner = document.getElementById('uploadSpinner');

    // Validate form
    const form = event.target;
    const formData = new FormData(form);

    if (!formData.get('title')?.trim()) {
        showError('Please enter a document title');
        return;
    }

    if (!formData.get('category')) {
        showError('Please select a category');
        return;
    }

    if (!formData.get('file')) {
        showError('Please select a file to upload');
        return;
    }

    // Show loading state
    if (uploadBtn) uploadBtn.disabled = true;
    if (uploadBtnText) uploadBtnText.textContent = 'Uploading...';
    if (uploadSpinner) uploadSpinner.classList.remove('hidden');

    try {
        // Try multiple ways to get CSRF token
        let csrfToken = null;

        // Method 1: From meta tag
        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            csrfToken = metaToken.getAttribute('content');
        }

        // Method 2: From window object (if set)
        if (!csrfToken && window.csrfToken) {
            csrfToken = window.csrfToken;
        }

        // Method 3: From Laravel's hidden input (if exists)
        if (!csrfToken) {
            const hiddenToken = form.querySelector('input[name="_token"]');
            if (hiddenToken) {
                csrfToken = hiddenToken.value;
            }
        }

        if (!csrfToken) {
            throw new Error('CSRF token not found. Please refresh the page and try again.');
        }

        // Prepare headers
        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        // Add CSRF token to headers
        headers['X-CSRF-TOKEN'] = csrfToken;

        const response = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: headers
        });

        let result;
        const contentType = response.headers.get('content-type');

        if (contentType && contentType.includes('application/json')) {
            result = await response.json();
        } else {
            const text = await response.text();
            console.error('Non-JSON response:', text);

            // Check if it's a CSRF token mismatch error
            if (text.includes('CSRF token mismatch') || text.includes('419')) {
                throw new Error('Security token expired. Please refresh the page and try again.');
            }

            throw new Error('Server returned an invalid response');
        }

        if (!response.ok) {
            let errorMessage = 'Upload failed';

            if (result.message) {
                errorMessage = result.message;
            } else if (result.errors) {
                if (typeof result.errors === 'object') {
                    errorMessage = Object.values(result.errors).flat().join('\n');
                } else {
                    errorMessage = result.errors;
                }
            }

            throw new Error(errorMessage);
        }

        showToast('Document uploaded successfully!', 'success');
        closeUploadModal();

        // Refresh the page to show new document
        setTimeout(() => {
            window.location.reload();
        }, 1000);

    } catch (error) {
        console.error('Upload error:', error);
        showError(error.message || 'Error uploading document. Please try again.');
    } finally {
        // Reset button state
        if (uploadBtn) uploadBtn.disabled = false;
        if (uploadBtnText) uploadBtnText.textContent = 'Upload Document';
        if (uploadSpinner) uploadSpinner.classList.add('hidden');
    }
}
    // Utility functions
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]; // Fixed syntax error here
    }

    function getFileIcon(fileType) {
        const icons = {
            'application/pdf': '📄',
            'application/msword': '📝',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': '📝',
            'image/jpeg': '🖼️',
            'image/jpg': '🖼️',
            'image/png': '🖼️',
            'text/plain': '📋'
        };
        return icons[fileType] || '📄';
    }

    function showToast(message, type = 'success') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        toast.textContent = message;

        document.body.appendChild(toast);

        // Remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    }

    function showError(message) {
        const errorModal = document.getElementById('errorModal');
        const errorMessage = document.getElementById('errorMessage');

        if (errorModal && errorMessage) {
            errorMessage.textContent = message;
            errorModal.classList.remove('hidden');
        } else {
            // Fallback to alert if modal not available
            alert('Error: ' + message);
        }
    }

    // Modal functions
    function openUploadModal() {
        const modal = document.getElementById('uploadModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeUploadModal() {
        const modal = document.getElementById('uploadModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            const form = document.getElementById('uploadForm');
            if (form) form.reset();
            hideFilePreview();
        }
    }

    function closeViewModal() {
        const modal = document.getElementById('viewModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // Document actions
    function editDocument(id) {
        console.log('Edit document:', id);
        // Implement edit functionality
    }

    function shareDocument(id) {
        console.log('Share document:', id);
        // Implement share functionality
    }

    function archiveDocument(id) {
        if (confirm('Are you sure you want to archive this document?')) {
            console.log('Archive document:', id);
            // Implement archive functionality
        }
    }

    function deleteDocument(id) {
        if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
            console.log('Delete document:', id);
            // Implement delete functionality
        }
    }

    function toggleDropdown(id) {
        const dropdown = document.getElementById(`dropdown-${id}`);
        if (dropdown) {
            // Close all other dropdowns first
            document.querySelectorAll('[id^="dropdown-"]').forEach(dd => {
                if (dd.id !== `dropdown-${id}`) {
                    dd.classList.add('hidden');
                }
            });

            dropdown.classList.toggle('hidden');
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('[onclick^="toggleDropdown"]')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
        }
    });

    // Make functions globally available
    window.openUploadModal = openUploadModal;
    window.closeUploadModal = closeUploadModal;
    window.closeViewModal = closeViewModal;
    window.removeFile = removeFile;
    window.editDocument = editDocument;
    window.shareDocument = shareDocument;
    window.archiveDocument = archiveDocument;
    window.deleteDocument = deleteDocument;
    window.toggleDropdown = toggleDropdown;

    // Initialize
    setupEventListeners();
});

// Handle page errors
window.addEventListener('error', function(event) {
    console.error('JavaScript error:', event.error);
});

window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
});
</script>
@endpush
<div class="space-y-8">
    <!-- Header Section -->
    <div class="medical-card p-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
            <div class="mb-6 lg:mb-0">
                <h2 class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-green-600 bg-clip-text text-transparent mb-2">
                    📄 Medical Documents
                </h2>
                <p class="text-gray-600 text-lg">Securely manage your healthcare documents and records</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-4">
                <button onclick="openUploadModal()" class="btn-primary">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Upload Document
                </button>

            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="medical-card p-6 hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-2xl">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900" id="totalDocuments">{{ $stats['total'] ?? 0 }}</p>
                    <p class="text-gray-600 text-sm font-medium">Total Documents</p>
                </div>
            </div>
        </div>

        <div class="medical-card p-6 hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-2xl">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2-2V7a2 2 0 012-2h2a2 2 0 002 2v2a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 00-2 2h-2a2 2 0 00-2 2v6a2 2 0 01-2 2H9z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900" id="labReports">{{ $stats['lab_reports'] ?? 0 }}</p>
                    <p class="text-gray-600 text-sm font-medium">Lab Reports</p>
                </div>
            </div>
        </div>

        <div class="medical-card p-6 hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-2xl">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900" id="prescriptions">{{ $stats['prescriptions'] ?? 0 }}</p>
                    <p class="text-gray-600 text-sm font-medium">Prescriptions</p>
                </div>
            </div>
        </div>

        <div class="medical-card p-6 hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center">
                <div class="p-3 bg-orange-100 rounded-2xl">
                    <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-2xl font-bold text-gray-900" id="imaging">{{ $stats['imaging'] ?? 0 }}</p>
                    <p class="text-gray-600 text-sm font-medium">Medical Imaging</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="medical-card p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex-1">
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Search documents by title, category, or tags..."
                           class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white shadow-sm">
                    <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <select id="categoryFilter" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white shadow-sm">
                    <option value="">All Categories</option>
                    @foreach($categories ?? [] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <select id="sortFilter" class="px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white shadow-sm">
                    <option value="newest">Newest First</option>
                    <option value="oldest">Oldest First</option>
                    <option value="name">Name A-Z</option>
                    <option value="size">File Size</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Documents Grid -->
<!-- Documents Grid -->
<div id="documentsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @forelse($documents as $document)
    <div class="medical-card p-6 hover:shadow-2xl transition-all duration-300 document-card" data-category="{{ $document->category }}">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center">
                <span class="text-3xl mr-3">{{ $document->file_icon }}</span>
                <div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1 line-clamp-2">{{ $document->title }}</h3>
                    <p class="text-xs text-gray-500 font-medium">{{ $document->category_label }}</p>
                </div>
            </div>
            @if($document->is_confidential)
                <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full font-semibold">🔒 Confidential</span>
            @endif
        </div>

        @if($document->description)
            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $document->description }}</p>
        @endif

        <div class="flex items-center justify-between text-xs text-gray-500 mb-4 font-medium">
            <span>{{ $document->file_size_human }}</span>
            <span>{{ $document->created_at->diffForHumans() }}</span>
        </div>

        @if(count($document->tags_array) > 0)
            <div class="mb-4">
                @foreach(array_slice($document->tags_array, 0, 3) as $tag)
                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full mr-1 mb-1 font-medium">{{ $tag }}</span>
                @endforeach
                @if(count($document->tags_array) > 3)
                    <span class="text-xs text-gray-500">+{{ count($document->tags_array) - 3 }} more</span>
                @endif
            </div>
        @endif

        <div class="flex space-x-2">
            <a href="{{ route('patient.documents.show', $document->id) }}"
               class="flex-1 btn-primary text-center py-2.5 rounded-xl text-sm font-semibold">
                👁️ View
            </a>
            <a href="{{ route('patient.documents.download', $document->id) }}"
               class="flex-1 btn-secondary text-center py-2.5 rounded-xl text-sm font-semibold">
                ⬇️ Download
            </a>
            <div class="relative">
                <button onclick="toggleDropdown('{{ $document->id }}')" class="p-2.5 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-xl transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01"></path>
                    </svg>
                </button>
                <div id="dropdown-{{ $document->id }}" class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg z-20 hidden border border-gray-200">
                    <button onclick="editDocument('{{ $document->id }}')" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 rounded-t-xl transition-colors">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit Details
                    </button>
                    <button onclick="shareDocument('{{ $document->id }}')" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-t border-gray-100">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
                        </svg>
                        Share with Doctor
                    </button>
                    <button onclick="archiveDocument('{{ $document->id }}')" class="block w-full text-left px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors border-t border-gray-100">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8l6 6m0 0l6-6m-6 6V3"></path>
                        </svg>
                        Archive
                    </button>
                    <button onclick="deleteDocument('{{ $document->id }}')" class="block w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors border-t border-gray-100 rounded-b-xl">
                        <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
    @empty
    <!-- Empty State -->
    <div class="col-span-full">
        <div class="medical-card rounded-2xl p-12 text-center">
            <div class="max-w-md mx-auto">
                <div class="text-8xl mb-6">📄</div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">No Documents Found</h3>
                <p class="text-gray-600 mb-8 text-lg">Start by uploading your first medical document to keep track of your healthcare records.</p>
                <button onclick="openUploadModal()" class="btn-primary px-8 py-4 text-lg font-semibold">
                    <svg class="w-6 h-6 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    Upload Your First Document
                </button>
            </div>
        </div>
    </div>
    @endforelse
</div>

    <!-- Loading State -->
    <div id="loadingState" class="text-center py-12 hidden">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mb-4"></div>
        <p class="text-gray-600">Loading documents...</p>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="medical-card rounded-3xl p-8 w-full max-w-2xl max-h-[90vh] overflow-y-auto custom-scrollbar">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Upload New Document</h2>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="uploadForm" action="{{ route('patient.documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Document Title *</label>
                        <input type="text" name="title" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter document title">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Category *</label>
                        <select name="category" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select Category</option>
                            @foreach($categories ?? [] as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Description</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                  placeholder="Brief description of the document (optional)"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">File Upload *</label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-400 transition-colors">
                            <input type="file" name="file" id="fileInput" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.txt" class="hidden">
                            <div id="dropZone" onclick="document.getElementById('fileInput').click()" class="cursor-pointer">
                                <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="text-lg font-medium text-gray-700 mb-2">Click to upload or drag and drop</p>
                                <p class="text-sm text-gray-500">PDF, DOC, DOCX, JPG, PNG, TXT (Max 10MB)</p>
                            </div>
                            <div id="filePreview" class="hidden mt-4 p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center">
                                        <span id="fileIcon" class="text-2xl mr-3"></span>
                                        <div>
                                            <p id="fileName" class="font-medium text-gray-900"></p>
                                            <p id="fileSize" class="text-sm text-gray-500"></p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="removeFile()" class="text-red-500 hover:text-red-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-900 mb-2">Tags</label>
                        <input type="text" name="tags"
                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                               placeholder="Enter tags separated by commas (e.g., blood test, routine, 2024)">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_confidential" id="isConfidential" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <label for="isConfidential" class="ml-2 text-sm text-gray-700">Mark as confidential document</label>
                    </div>

                    <div class="flex space-x-4 pt-6">
                        <button type="button" onclick="closeUploadModal()"
                                class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors font-semibold">
                            Cancel
                        </button>
                        <button type="submit" id="uploadBtn"
                                class="flex-1 btn-primary px-6 py-3 rounded-xl font-semibold">
                            <span id="uploadBtnText">Upload Document</span>
                            <div id="uploadSpinner" class="hidden inline-block ml-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Document View Modal -->
<div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="medical-card rounded-3xl p-8 w-full max-w-4xl max-h-[90vh] overflow-y-auto custom-scrollbar">
            <div class="flex items-center justify-between mb-6">
                <h2 id="viewModalTitle" class="text-2xl font-bold text-gray-900"></h2>
                <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="viewModalContent">
                <!-- Document content will be loaded here -->
            </div>
        </div>
    </div>
</div>


<!-- Doctor Documents Modal -->
<!-- Documents Display Modal -->
<div id="documentsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 max-w-6xl shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-xl leading-6 font-medium text-gray-900">Patient Documents</h3>
                    <p class="text-sm text-gray-600 mt-1">View all uploaded documents for this patient</p>
                </div>
                <button type="button" onclick="closeDocumentsModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Loading State -->
            <div id="documentsLoading" class="flex justify-center items-center py-12 hidden">
                <svg class="animate-spin h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="ml-2 text-gray-600">Loading documents...</span>
            </div>

            <!-- Empty State -->
            <div id="documentsEmpty" class="text-center py-12 hidden">
                <svg class="mx-auto h-16 w-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">No documents found</h3>
                <p class="mt-2 text-gray-600">No documents have been uploaded for this patient yet.</p>
            </div>

            <!-- Documents Grid -->
            <div id="documentsGrid" class="hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4" id="documentsContainer">
                    <!-- Documents will be populated here -->
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <span id="documentsCount">0 documents</span>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="refreshDocuments()" class="px-4 py-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                    <button type="button" onclick="closeDocumentsModal()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Details Modal -->
<div id="documentDetailsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Document Details</h3>
                <button type="button" onclick="closeDocumentDetailsModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="documentDetailsContent">
                <!-- Details will be populated here -->
            </div>
        </div>
    </div>
</div>




<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>

<!-- Error Modal -->
<div id="errorModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="medical-card rounded-3xl p-8 w-full max-w-md">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-red-600">Error</h2>
                <button onclick="document.getElementById('errorModal').classList.add('hidden')"
                        class="text-gray-400 hover:text-gray-600 text-2xl">
                    &times;
                </button>
            </div>
            <div id="errorMessage" class="text-gray-700 mb-6"></div>
            <button onclick="document.getElementById('errorModal').classList.add('hidden')"
                    class="w-full btn-primary py-3 rounded-xl font-semibold">
                OK
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let documents = [];
    let filteredDocuments = [];

    // Safely get documents data
    try {
        const documentsData = document.querySelector('script[data-documents]');
        if (documentsData) {
            documents = JSON.parse(documentsData.textContent);
            filteredDocuments = [...documents];
        }
    } catch (error) {
        console.warn('Could not load documents data:', error);
    }

    // Setup all event listeners
    function setupEventListeners() {
        // Search functionality
        const searchInput = document.getElementById('searchInput');
        if (searchInput) {
            searchInput.addEventListener('input', filterDocuments);
        }

        // Category filter
        const categoryFilter = document.getElementById('categoryFilter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', filterDocuments);
        }

        // Sort filter
        const sortFilter = document.getElementById('sortFilter');
        if (sortFilter) {
            sortFilter.addEventListener('change', filterDocuments);
        }

        // File input change
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.addEventListener('change', handleFileSelect);
        }

        // Upload form submission
        const uploadForm = document.getElementById('uploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', handleUpload);
        }

        // Drag and drop
        const dropZone = document.getElementById('dropZone');
        if (dropZone) {
            dropZone.addEventListener('dragover', handleDragOver);
            dropZone.addEventListener('drop', handleDrop);
            dropZone.addEventListener('dragenter', handleDragEnter);
            dropZone.addEventListener('dragleave', handleDragLeave);
        }
    }

    // Filter and search functions
    function filterDocuments() {
        const searchTerm = document.getElementById('searchInput')?.value.toLowerCase() || '';
        const categoryFilter = document.getElementById('categoryFilter')?.value || '';
        const sortFilter = document.getElementById('sortFilter')?.value || 'newest';

        // Filter documents
        filteredDocuments = documents.filter(doc => {
            const matchesSearch = !searchTerm ||
                doc.title?.toLowerCase().includes(searchTerm) ||
                doc.category?.toLowerCase().includes(searchTerm) ||
                (doc.tags && doc.tags.toLowerCase().includes(searchTerm));

            const matchesCategory = !categoryFilter || doc.category === categoryFilter;

            return matchesSearch && matchesCategory;
        });

        // Sort documents
        sortDocuments(sortFilter);

        // Update display
        updateDocumentsDisplay();
    }

    function sortDocuments(sortBy) {
        switch (sortBy) {
            case 'oldest':
                filteredDocuments.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
                break;
            case 'name':
                filteredDocuments.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
                break;
            case 'size':
                filteredDocuments.sort((a, b) => (b.file_size || 0) - (a.file_size || 0));
                break;
            case 'newest':
            default:
                filteredDocuments.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                break;
        }
    }

    function updateDocumentsDisplay() {
        // This would update the documents grid
        // Implementation depends on your specific UI structure
        console.log('Filtered documents:', filteredDocuments.length);
    }

    // File handling functions
    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            if (validateFile(file)) {
                displayFilePreview(file);
            }
        }
    }

    function handleDragEnter(event) {
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.add('border-blue-400', 'bg-blue-50');
    }

    function handleDragLeave(event) {
        event.preventDefault();
        event.stopPropagation();
        event.currentTarget.classList.remove('border-blue-400', 'bg-blue-50');
    }

    function handleDragOver(event) {
        event.preventDefault();
        event.stopPropagation();
    }

    function handleDrop(event) {
        event.preventDefault();
        event.stopPropagation();

        const dropZone = event.currentTarget;
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');

        const files = event.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            if (validateFile(file)) {
                const fileInput = document.getElementById('fileInput');
                if (fileInput) {
                    // Create a new FileList-like object
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    fileInput.files = dt.files;
                    displayFilePreview(file);
                }
            }
        }
    }

    function validateFile(file) {
        // Check file size (10MB limit)
        const maxSize = 10 * 1024 * 1024; // 10MB in bytes
        if (file.size > maxSize) {
            showError('File size must be less than 10MB');
            return false;
        }

        // Check file type
        const allowedTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'text/plain'
        ];

        if (!allowedTypes.includes(file.type)) {
            showError('Invalid file type. Please upload PDF, DOC, DOCX, JPG, PNG, or TXT files only.');
            return false;
        }

        return true;
    }

    function displayFilePreview(file) {
        const preview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const fileIcon = document.getElementById('fileIcon');
        const dropZone = document.getElementById('dropZone');

        if (fileName) fileName.textContent = file.name;
        if (fileSize) fileSize.textContent = formatFileSize(file.size);
        if (fileIcon) fileIcon.textContent = getFileIcon(file.type);

        if (preview) preview.classList.remove('hidden');
        if (dropZone) dropZone.style.display = 'none';
    }

    function hideFilePreview() {
        const preview = document.getElementById('filePreview');
        const dropZone = document.getElementById('dropZone');

        if (preview) preview.classList.add('hidden');
        if (dropZone) dropZone.style.display = 'block';
    }

    function removeFile() {
        const fileInput = document.getElementById('fileInput');
        if (fileInput) fileInput.value = '';
        hideFilePreview();
    }

    // Upload handling
    async function handleUpload(event) {
        event.preventDefault();

        const uploadBtn = document.getElementById('uploadBtn');
        const uploadBtnText = document.getElementById('uploadBtnText');
        const uploadSpinner = document.getElementById('uploadSpinner');

        // Validate form
        const form = event.target;
        const formData = new FormData(form);

        if (!formData.get('title')?.trim()) {
            showError('Please enter a document title');
            return;
        }

        if (!formData.get('category')) {
            showError('Please select a category');
            return;
        }

        if (!formData.get('file')) {
            showError('Please select a file to upload');
            return;
        }

        // Show loading state
        if (uploadBtn) uploadBtn.disabled = true;
        if (uploadBtnText) uploadBtnText.textContent = 'Uploading...';
        if (uploadSpinner) uploadSpinner.classList.remove('hidden');

        try {
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                throw new Error('CSRF token not found. Please refresh the page.');
            }

            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            let result;
            const contentType = response.headers.get('content-type');

            if (contentType && contentType.includes('application/json')) {
                result = await response.json();
            } else {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned an invalid response');
            }

            if (!response.ok) {
                let errorMessage = 'Upload failed';

                if (result.message) {
                    errorMessage = result.message;
                } else if (result.errors) {
                    if (typeof result.errors === 'object') {
                        errorMessage = Object.values(result.errors).flat().join('\n');
                    } else {
                        errorMessage = result.errors;
                    }
                }

                throw new Error(errorMessage);
            }

            showToast('Document uploaded successfully!', 'success');
            closeUploadModal();

            // Refresh the page to show new document
            setTimeout(() => {
                window.location.reload();
            }, 1000);

        } catch (error) {
            console.error('Upload error:', error);
            showError(error.message || 'Error uploading document. Please try again.');
        } finally {
            // Reset button state
            if (uploadBtn) uploadBtn.disabled = false;
            if (uploadBtnText) uploadBtnText.textContent = 'Upload Document';
            if (uploadSpinner) uploadSpinner.classList.add('hidden');
        }
    }

    // Modal functions
    function openUploadModal() {
        const modal = document.getElementById('uploadModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeUploadModal() {
        const modal = document.getElementById('uploadModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            const form = document.getElementById('uploadForm');
            if (form) form.reset();
            hideFilePreview();
        }
    }

    function closeViewModal() {
        const modal = document.getElementById('viewModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    }

    // Document actions
    function editDocument(id) {
        console.log('Edit document:', id);
        // Implement edit functionality
    }

    function shareDocument(id) {
        console.log('Share document:', id);
        // Implement share functionality
    }

    function archiveDocument(id) {
        if (confirm('Are you sure you want to archive this document?')) {
            console.log('Archive document:', id);
            // Implement archive functionality
        }
    }

    function deleteDocument(id) {
        if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
            console.log('Delete document:', id);
            // Implement delete functionality
        }
    }

    function toggleDropdown(id) {
        const dropdown = document.getElementById(`dropdown-${id}`);
        if (dropdown) {
            // Close all other dropdowns first
            document.querySelectorAll('[id^="dropdown-"]').forEach(dd => {
                if (dd.id !== `dropdown-${id}`) {
                    dd.classList.add('hidden');
                }
            });

            dropdown.classList.toggle('hidden');
        }
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(event) {
        if (!event.target.closest('[onclick^="toggleDropdown"]')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(dropdown => {
                dropdown.classList.add('hidden');
            });
        }
    });

    // Utility functions
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function getFileIcon(fileType) {
        const icons = {
            'application/pdf': '📄',
            'application/msword': '📝',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': '📝',
            'image/jpeg': '🖼️',
            'image/jpg': '🖼️',
            'image/png': '🖼️',
            'text/plain': '📋'
        };
        return icons[fileType] || '📄';
    }

    function showToast(message, type = 'success') {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        }`;
        toast.textContent = message;

        document.body.appendChild(toast);

        // Remove after 3 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 3000);
    }

    function showError(message) {
        const errorModal = document.getElementById('errorModal');
        const errorMessage = document.getElementById('errorMessage');

        if (errorModal && errorMessage) {
            errorMessage.textContent = message;
            errorModal.classList.remove('hidden');
        } else {
            // Fallback to alert if modal not available
            alert('Error: ' + message);
        }
    }

    // Make functions globally available
    window.openUploadModal = openUploadModal;
    window.closeUploadModal = closeUploadModal;
    window.closeViewModal = closeViewModal;
    window.removeFile = removeFile;
    window.editDocument = editDocument;
    window.shareDocument = shareDocument;
    window.archiveDocument = archiveDocument;
    window.deleteDocument = deleteDocument;
    window.toggleDropdown = toggleDropdown;

    // Initialize
    setupEventListeners();
});

// Handle page errors
window.addEventListener('error', function(event) {
    console.error('JavaScript error:', event.error);
    // You can show a user-friendly error message here
});

window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    // You can show a user-friendly error message here
});
    // Initialize variables
    let documents = @json($documents ?? []);
    let filteredDocuments = [...documents];

    // Setup all event listeners
    function setupEventListeners() {
        // Search functionality
        document.getElementById('searchInput')?.addEventListener('input', filterDocuments);

        // Category filter
        document.getElementById('categoryFilter')?.addEventListener('change', filterDocuments);

        // Sort filter
        document.getElementById('sortFilter')?.addEventListener('change', filterDocuments);

        // File input change
        document.getElementById('fileInput')?.addEventListener('change', handleFileSelect);

        // Upload form submission
        const uploadForm = document.getElementById('uploadForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', handleUpload);
        }

        // Drag and drop
        const dropZone = document.getElementById('dropZone');
        if (dropZone) {
            dropZone.addEventListener('dragover', handleDragOver);
            dropZone.addEventListener('drop', handleDrop);
        }
    }

    // File handling functions
    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            displayFilePreview(file);
        }
    }

    function handleDragOver(event) {
        event.preventDefault();
        event.stopPropagation();
    }

    function handleDrop(event) {
        event.preventDefault();
        event.stopPropagation();

        const files = event.dataTransfer.files;
        if (files.length > 0) {
            const file = files[0];
            document.getElementById('fileInput').files = files;
            displayFilePreview(file);
        }
    }

    function displayFilePreview(file) {
        const preview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const fileIcon = document.getElementById('fileIcon');

        if (fileName) fileName.textContent = file.name;
        if (fileSize) fileSize.textContent = formatFileSize(file.size);
        if (fileIcon) fileIcon.textContent = getFileIcon(file.type);

        if (preview) preview.classList.remove('hidden');
    }

    function hideFilePreview() {
        const preview = document.getElementById('filePreview');
        if (preview) preview.classList.add('hidden');
    }

    function removeFile() {
        document.getElementById('fileInput').value = '';
        hideFilePreview();
    }

    // Upload handling
    async function handleUpload(event) {
        event.preventDefault();

        const uploadBtn = document.getElementById('uploadBtn');
        const uploadBtnText = document.getElementById('uploadBtnText');
        const uploadSpinner = document.getElementById('uploadSpinner');

        // Show loading state
        uploadBtn.disabled = true;
        if (uploadBtnText) uploadBtnText.textContent = 'Uploading...';
        if (uploadSpinner) uploadSpinner.classList.remove('hidden');

        try {
            const formData = new FormData(event.target);

            const response = await fetch(event.target.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message ||
                    (result.errors ? Object.values(result.errors).join('\n') : 'Upload failed');
            }

            showToast('Document uploaded successfully!', 'success');
            closeUploadModal();

            // Refresh the page to show new document
            window.location.reload();

        } catch (error) {
            console.error('Upload error:', error);
            showToast(error.message || 'Error uploading document. Please try again.', 'error');
        } finally {
            // Reset button state
            if (uploadBtn) uploadBtn.disabled = false;
            if (uploadBtnText) uploadBtnText.textContent = 'Upload Document';
            if (uploadSpinner) uploadSpinner.classList.add('hidden');
        }
    }

    // Document actions
    function viewDocument(id) {
        window.open(`/patient/documents/${id}`, '_blank');
    }

    function downloadDocument(id) {
        window.open(`/patient/documents/${id}/download`, '_blank');
    }

    // Modal functions
    function openUploadModal() {
        const modal = document.getElementById('uploadModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeUploadModal() {
        const modal = document.getElementById('uploadModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
            const form = document.getElementById('uploadForm');
            if (form) form.reset();
            hideFilePreview();
        }
    }

    // Utility functions
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2) + ' ' + sizes[i];
    }

    function getFileIcon(fileType) {
        const icons = {
            'application/pdf': '📄',
            'application/msword': '📝',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document': '📝',
            'image/jpeg': '🖼️',
            'image/jpg': '🖼️',
            'image/png': '🖼️',
            'text/plain': '📋'
        };
        return icons[fileType] || '📄';
    }

    function showToast(message, type = 'success') {
        // Implement your toast notification system here
        console.log(`${type}: ${message}`);
    }

    // Initialize
    setupEventListeners();
});
</script>


<script>
    function openDoctorDocumentsModal() {
        document.getElementById('documentsModal').classList.remove('hidden');
    }

    function closeDocumentsModal() {
        document.getElementById('documentsModal').classList.add('hidden');
    }
</script>

<script>
// Global variables
let currentPatientId = null;
let documentsData = [];

// Open documents modal
function openDocumentsModal(patientId = null) {
    currentPatientId = patientId;
    document.getElementById('documentsModal').classList.remove('hidden');
    loadDocuments();
}

// Close documents modal
function closeDocumentsModal() {
    document.getElementById('documentsModal').classList.add('hidden');
    currentPatientId = null;
    documentsData = [];
}

// Load documents from server
async function loadDocuments() {
    showLoading();

    try {
        // Construct URL - adjust this based on your routing
        let url = '/doctor/documents';
        if (currentPatientId) {
            url += `?patient_id=${currentPatientId}`;
        }

        const response = await fetch(url, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        documentsData = data.documents || [];
        displayDocuments();

    } catch (error) {
        console.error('Error loading documents:', error);
        showError('Failed to load documents. Please try again.');
    }
}

// Display documents in grid
function displayDocuments() {
    hideLoading();

    if (documentsData.length === 0) {
        showEmpty();
        return;
    }

    showGrid();
    const container = document.getElementById('documentsContainer');
    container.innerHTML = '';

    documentsData.forEach(doc => {
        const card = createDocumentCard(doc);
        container.appendChild(card);
    });

    updateDocumentsCount();
}

// Create a single document card based on your existing design
function createDocumentCard(doc) {
    const card = document.createElement('div');
    card.className = 'border rounded-lg p-4 hover:shadow-md transition-shadow';

    // Format file size
    const fileSize = formatFileSize(doc.file_size);

    // Format date
    const uploadDate = new Date(doc.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    // Get file type icon based on your existing logic
    const fileIcon = getFileIcon(doc.file_type);

    card.innerHTML = `
        <div class="flex items-start">
            <div class="flex-shrink-0">
                ${fileIcon}
            </div>
            <div class="ml-4 flex-1">
                <h3 class="text-sm font-medium text-gray-900">${escapeHtml(doc.title)}</h3>
                <p class="text-xs text-gray-500">${escapeHtml(doc.category)}</p>
                <p class="text-xs text-gray-400">${escapeHtml(doc.file_name)} (${fileSize})</p>
                <p class="text-xs text-gray-400 mt-1">${uploadDate}</p>
                ${doc.description ? `<p class="text-xs text-gray-600 mt-1">${escapeHtml(doc.description.substring(0, 60))}${doc.description.length > 60 ? '...' : ''}</p>` : ''}
            </div>
        </div>
        <div class="mt-3 flex justify-end space-x-2">
            <a href="#" onclick="downloadDocument('${doc.id}'); return false;" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                Download
            </a>
            <button onclick="showDocumentDetails('${doc.id}')" class="text-gray-600 hover:text-gray-800 text-sm font-medium">
                Details
            </button>
            <button onclick="deleteDocument('${doc.id}')" class="text-red-600 hover:text-red-800 text-sm font-medium">
                Delete
            </button>
        </div>
    `;

    return card;
}

// Get file icon based on your existing design
function getFileIcon(fileType) {
    if (fileType && fileType.includes('image')) {
        return `<svg class="h-10 w-10 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>`;
    } else if (fileType === 'application/pdf') {
        return `<svg class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
        </svg>`;
    } else {
        return `<svg class="h-10 w-10 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
        </svg>`;
    }
}

// Utility functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

function updateDocumentsCount() {
    const count = documentsData.length;
    const countElement = document.getElementById('documentsCount');
    countElement.textContent = `${count} document${count !== 1 ? 's' : ''}`;
}

function showDoctorDocumentsError(message) {
    hideDoctorDocumentsLoading();
    console.error(message);
    // Optional: Show a toast notification or alert
    // alert(message); // Uncomment if you want to show an alert
} functions
function showLoading() {
    document.getElementById('documentsLoading').classList.remove('hidden');
    document.getElementById('documentsEmpty').classList.add('hidden');
    document.getElementById('documentsGrid').classList.add('hidden');
}

function hideLoading() {
    document.getElementById('documentsLoading').classList.add('hidden');
}

function showEmpty() {
    document.getElementById('documentsEmpty').classList.remove('hidden');
    document.getElementById('documentsGrid').classList.add('hidden');
}

function showGrid() {
    document.getElementById('documentsGrid').classList.remove('hidden');
    document.getElementById('documentsEmpty').classList.add('hidden');
}

function showError(message) {
    hideLoading();
    alert(message); // You can replace this with a toast notification
    console.error(message);
}

// Action functions
async function downloadDocument(docId) {
    try {
        const response = await fetch(`/doctor/documents/download/${docId}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            }
        });

        if (!response.ok) {
            throw new Error('Download failed');
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.style.display = 'none';
        a.href = url;

        // Get filename from response headers or use default
        const contentDisposition = response.headers.get('Content-Disposition');
        let filename = 'document';
        if (contentDisposition) {
            const matches = /filename="([^"]*)"/.exec(contentDisposition);
            if (matches && matches[1]) {
                filename = matches[1];
            }
        }

        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);

    } catch (error) {
        console.error('Download error:', error);
        alert('Failed to download document');
    }
}

function showDocumentDetails(docId) {
    const doc = documentsData.find(d => d.id === docId);
    if (!doc) return;

    const modal = document.getElementById('documentDetailsModal');
    const content = document.getElementById('documentDetailsContent');

    const uploadDate = new Date(doc.created_at).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    content.innerHTML = `
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Title</label>
                <p class="mt-1 text-sm text-gray-900">${escapeHtml(doc.title)}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <p class="mt-1 text-sm text-gray-900">${escapeHtml(doc.category)}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">File Name</label>
                <p class="mt-1 text-sm text-gray-900">${escapeHtml(doc.file_name)}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">File Type</label>
                    <p class="mt-1 text-sm text-gray-900">${escapeHtml(doc.file_type)}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">File Size</label>
                    <p class="mt-1 text-sm text-gray-900">${formatFileSize(doc.file_size)}</p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Upload Date</label>
                <p class="mt-1 text-sm text-gray-900">${uploadDate}</p>
            </div>
            ${doc.description ? `
            <div>
                <label class="block text-sm font-medium text-gray-700">Description</label>
                <p class="mt-1 text-sm text-gray-900">${escapeHtml(doc.description)}</p>
            </div>
            ` : ''}
            <div class="flex justify-end space-x-2 pt-4">
                <button onclick="downloadDocument('${doc.id}')" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Download
                </button>
                <button onclick="closeDocumentDetailsModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400">
                    Close
                </button>
            </div>
        </div>
    `;

    modal.classList.remove('hidden');
}

function closeDocumentDetailsModal() {
    document.getElementById('documentDetailsModal').classList.add('hidden');
}

async function deleteDocument(docId) {
    if (!confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch(`/doctor/documents/delete/${docId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Delete failed');
        }

        // Refresh the documents list
        await loadDocuments();
        alert('Document deleted successfully');

    } catch (error) {
        console.error('Delete error:', error);
        alert('Failed to delete document');
    }
}

function refreshDocuments() {
    loadDocuments();
}
</script>
@endpush
