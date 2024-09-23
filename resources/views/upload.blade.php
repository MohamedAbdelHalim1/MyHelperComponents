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
            color: red;
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
<button class="upload-all-btn" id="upload-all-btn" style="display:none;" onclick="this.innerHTML='Uploading...';">Upload All</button>
<div id="image-preview-container"></div>

<script>
    const fileInput = document.getElementById('file-input');
    const previewContainer = document.getElementById('image-preview-container');
    const uploadAllBtn = document.getElementById('upload-all-btn');
    let validFiles = []; // Array to keep track of valid files

    fileInput.addEventListener('change', function() {
        const files = this.files;
        uploadAllBtn.style.display = files.length > 0 ? 'block' : 'none'; // Show button if files are selected

        previewContainer.innerHTML = ''; // Clear previous previews
        validFiles = []; // Reset valid files array

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            validFiles.push(file); // Add to valid files
            const reader = new FileReader();

            reader.onload = (event) => {
                addImagePreview(event.target.result, file);
            };

            reader.readAsDataURL(file);
        }
    });

    function addImagePreview(imageSrc, file) {
        const div = document.createElement('div');
        div.classList.add('image-preview');
        div.innerHTML = `
            <img src="${imageSrc}" alt="Uploaded Image">
            <div>
                <button class="upload-btn" onclick="uploadImage(this, '${file.name}')">Upload</button>
                <span class="status-text" style="display:none;">Uploaded Successfully</span>
                <button class="delete-btn" style="display:none;" onclick="deleteImage(this, '')">Delete</button>
            </div>
            <span class="remove-btn" onclick="removePreview(this, '${file.name}')">x</span>
        `;
        previewContainer.appendChild(div);
    }

    function uploadAllImages() {
        const formData = new FormData();

        for (let i = 0; i < validFiles.length; i++) {
            formData.append('files[]', validFiles[i]);
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
                previewContainer.innerHTML = ''; // Clear previews after upload
                uploadAllBtn.style.display = 'none'; // Hide the upload all button
                validFiles = []; // Reset valid files
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

    function removePreview(element, fileName) {
        element.parentElement.remove();

        // Remove the file from validFiles
        validFiles = validFiles.filter(file => file.name !== fileName);

        // Check if there are any more previews left
        if (previewContainer.children.length === 0) {
            uploadAllBtn.style.display = 'none'; // Hide the upload button if no previews left
        }
    }

    uploadAllBtn.addEventListener('click', uploadAllImages);
</script>

</body>
</html>
