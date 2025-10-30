@extends('layouts.app')

@section('title', 'Edit Page')

@section('content')
    <div class="max-w-6xl mx-auto p-6 bg-white rounded-xl shadow-lg">
        <h1 class="text-2xl font-semibold mb-6">🎨 Customize Your Page</h1>

        <!-- Live Preview Area -->
        <div id="preview" class="p-6 border rounded-lg mb-6 bg-gray-50">
            <h2 id="preview-title" contenteditable="true" class="text-3xl font-bold mb-4 text-gray-800">
                Welcome to My Page
            </h2>
            <p id="preview-content" contenteditable="true" class="text-gray-700">
                You can edit this text to personalize your website content.
            </p>
            <img id="preview-image" src="https://via.placeholder.com/600x250" alt="Preview Image"
                class="rounded-lg mt-4 w-full h-64 object-cover">
        </div>

        <!-- Theme Selector -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">Select Theme Colors:</h3>
            <div class="flex space-x-3">
                <button class="theme-btn w-8 h-8 rounded-full bg-gray-800 border-2 border-white shadow" data-bg="#111"
                    data-text="#fff"></button>
                <button class="theme-btn w-8 h-8 rounded-full bg-blue-600 border-2 border-white shadow" data-bg="#2563eb"
                    data-text="#fff"></button>
                <button class="theme-btn w-8 h-8 rounded-full bg-green-600 border-2 border-white shadow" data-bg="#16a34a"
                    data-text="#fff"></button>
                <button class="theme-btn w-8 h-8 rounded-full bg-yellow-500 border-2 border-white shadow" data-bg="#facc15"
                    data-text="#000"></button>
                <button class="theme-btn w-8 h-8 rounded-full bg-pink-500 border-2 border-white shadow" data-bg="#ec4899"
                    data-text="#fff"></button>
            </div>
        </div>

        <!-- Image Upload -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Change Image:</label>
            <input type="file" id="imageUpload" accept="image/*" class="block w-full text-sm text-gray-500">
        </div>

        <!-- Save Button -->
        <button id="saveChanges"
            class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg shadow-md transition">
            💾 Save Changes
        </button>
    </div>

    <!-- JavaScript for Live Editing -->
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Theme color preview
                const themeButtons = document.querySelectorAll('.theme-btn');
                const preview = document.getElementById('preview');
                const previewTitle = document.getElementById('preview-title');
                const previewContent = document.getElementById('preview-content');

                themeButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        const bg = button.getAttribute('data-bg');
                        const text = button.getAttribute('data-text');
                        preview.style.backgroundColor = bg;
                        previewTitle.style.color = text;
                        previewContent.style.color = text;
                    });
                });

                // Live image update
                const imageUpload = document.getElementById('imageUpload');
                const previewImage = document.getElementById('preview-image');
                imageUpload.addEventListener('change', e => {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = event => {
                            previewImage.src = event.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                });

                // Save Changes (simulate AJAX)
                const saveButton = document.getElementById('saveChanges');
                saveButton.addEventListener('click', () => {
                    const data = {
                        title: previewTitle.innerText.trim(),
                        content: previewContent.innerText.trim(),
                        theme_bg: preview.style.backgroundColor,
                        image: previewImage.src
                    };

                    console.log('Saving...', data);

                    // Simulated success message
                    alert(' Changes saved successfully (mock mode). You can now link this to backend.');
                });
            });
        </script>
    @endpush
@endsection
