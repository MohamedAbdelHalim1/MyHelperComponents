<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 20px;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        input[type="file"] {
            display: block;
            margin: 20px auto;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 80%;
            background-color: #fff;
            transition: border-color 0.3s;
        }
        input[type="file"]:hover {
            border-color: #888;
        }
        .image-preview {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            position: relative;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .image-preview img {
            max-width: 100px;
            margin-right: 10px;
            border-radius: 5px;
        }
        .upload-btn, .delete-btn, .upload-all-btn {
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-top: 10px;
            display: block;
        }
        .upload-btn {
            background-color: #28a745;
            color: #fff;
        }
        .upload-btn:hover {
            background-color: #218838;
        }
        .delete-btn {
            background-color: #dc3545;
            color: #fff;
        }
        .delete-btn:hover {
            background-color: #c82333;
        }
        .upload-all-btn {
            background-color: #007bff;
            color: #fff;
            margin: 20px auto;
        }
        .upload-all-btn:hover {
            background-color: #0069d9;
        }
        .remove-btn {
            color: #4d4d4d;
            cursor: pointer;
            position: absolute;
            top: 10px;
            right: 10px;
            font-weight: bold;
        }
        .status-text {
            margin-top: 10px;
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>

<h1>Upload Images</h1>
<input type="file" id="file-input" accept="image/*" multiple>
<button class="upload-all-btn" id="upload-all-btn" style="display:none;" onclick="this.innerHTML = 'Uploading...' ;uploadAllImages(this)">Upload All</button>
<div id="image-preview-container"></div>

<script>
    const fileInput = document.getElementById('file-input');
    const previewContainer = document.getElementById('image-preview-container');
    const uploadAllBtn = document.getElementById('upload-all-btn');
    let uploadedFiles = [];  // Store the files to be uploaded
    let uploadedCount = 0;   // Count of successfully uploaded files

    fileInput.addEventListener('change', function() {
        const files = Array.from(this.files);
        uploadedFiles = files;
        previewContainer.innerHTML = ''; // Clear previous previews

        for (let file of files) {
            const reader = new FileReader();
            reader.onload = (event) => {
                addImagePreview(event.target.result, file);
            };
            reader.readAsDataURL(file);
        }

        toggleUploadAllButton();  // Show/Hide the Upload All button based on file selection
    });

    function addImagePreview(imageSrc, file) {
        const div = document.createElement('div');
        div.classList.add('image-preview');
        div.innerHTML = `
            <img src="${imageSrc}" alt="Uploaded Image">
            <div>
                <button class="upload-btn" onclick="uploadImage(this, '${file.name}')">Upload</button>
                <span class="status-text" style="display:none;">Uploaded Successfully</span>
                <button class="delete-btn" style="display:none;" onclick="deleteImage(this, '','${file.name}')">Delete</button>
            </div>
            <span class="remove-btn" onclick="removePreview(this, '${file.name}')">x</span>
        `;
        previewContainer.appendChild(div);
    }

    function uploadImage(button, fileName) {
        const formData = new FormData();
        const file = uploadedFiles.find(f => f.name === fileName);

        if (file) {
            formData.append('file', file);
            button.innerHTML = 'Uploading...';
            button.disabled = true;

            fetch('/upload', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.id) {
                    button.style.display = 'none'; // Hide upload button
                    const statusText = button.nextElementSibling;
                    statusText.style.display = 'block'; // Show success text
                    const deleteBtn = statusText.nextElementSibling;
                    deleteBtn.style.display = 'block'; // Show delete button
                    deleteBtn.setAttribute('onclick', `deleteImage(this, ${data.id} , '${file.name}')`); // Set ID for deletion

                    uploadedCount++; // Increment the count of uploaded files
                    toggleUploadAllButton(); // Hide Upload All button if any photo is uploaded
                } else {
                    alert('Error uploading image');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error uploading image');
            });
        }
    }

    function deleteImage(element, id, fileName) {        
        if (confirm('Are you sure you want to delete this photo?')) {
            fetch(`/delete/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Image deleted successfully');
                    removePreview(element, fileName);  // Pass the file name for correct removal
                    uploadedCount--; // Decrement the count of uploaded files
                    toggleUploadAllButton();  // Re-check for the Upload All button display
                } else {
                    alert('Error deleting image');
                }
            })
            .catch(err => {
                console.error(err);
                alert('Error deleting image');
            });
        }
    }



    function removePreview(element, fileName) {        
        if (fileName) {
            uploadedFiles = uploadedFiles.filter(f => f.name !== fileName);  // Remove file from the array
        }
        element.closest('.image-preview').remove();  // Remove the preview element

        if (previewContainer.children.length === 0) {
            uploadedFiles = [];  // Reset if no images left
            uploadedCount = 0;   // Reset uploaded count as well
        }
        toggleUploadAllButton();  // Check if Upload All button should be visible
    }



    function uploadAllImages() {
        const formData = new FormData();
        for (let file of uploadedFiles) {
            formData.append('files[]', file);
        }

        fetch('/upload-all', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('All images uploaded successfully');
                previewContainer.innerHTML = '';  // Clear previews after upload
                uploadedFiles = [];  // Clear the array after all files are uploaded
                uploadedCount = 0;  // Reset uploaded count
                toggleUploadAllButton(); // Hide the Upload All button
                window.location.reload();
            } else {
                alert('Error uploading images');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Error uploading images');
        });
    }


    function toggleUploadAllButton() {
        // Show the Upload All button only when there are files left to upload
        if (uploadedFiles.length > 0 && uploadedCount === 0) {
            uploadAllBtn.style.display = 'block';
        } else {
            uploadAllBtn.style.display = 'none';
        }
    }

</script>

</body>
</html>
