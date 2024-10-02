document.addEventListener('DOMContentLoaded', function() {
    const uploadBtn = document.getElementById('uploadBtn');
    const fileInput = document.getElementById('fileInput');
    const createFolderBtn = document.getElementById('createFolderBtn');
    const logoutBtn = document.getElementById('logoutBtn');
    const fileGrid = document.getElementById('fileGrid');
    const breadcrumb = document.getElementById('breadcrumb');
    let currentDir = '.';

    // Load file list on page load
    loadFileList(currentDir);

    // Event listeners
    uploadBtn.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', uploadFile);
    createFolderBtn.addEventListener('click', createFolder);
    logoutBtn.addEventListener('click', logout);

    function loadFileList(dir) {
        fetch(`list_files.php?dir=${encodeURIComponent(dir)}`)
            .then(response => response.json())
            .then(data => {
                currentDir = data.currentDir;
                updateBreadcrumb(currentDir);
                fileGrid.innerHTML = '';
                data.files.forEach(file => {
                    const fileItem = createFileItem(file);
                    fileGrid.appendChild(fileItem);
                });
            })
            .catch(error => console.error('Error loading file list:', error));
    }

    function updateBreadcrumb(path) {
        const parts = path.split('/').filter(part => part !== '');
        breadcrumb.innerHTML = `
            <ol class="flex items-center space-x-4">
                <li>
                    <div>
                        <a href="#" class="text-gray-400 hover:text-gray-500" data-path=".">
                            <i class="fas fa-home"></i>
                            <span class="sr-only">Home</span>
                        </a>
                    </div>
                </li>
                ${parts.map((part, index) => `
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <a href="#" class="text-sm font-medium text-gray-500 hover:text-gray-700" data-path="${parts.slice(0, index + 1).join('/')}">${part}</a>
                        </div>
                    </li>
                `).join('')}
            </ol>
        `;

        breadcrumb.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                loadFileList(e.target.dataset.path);
            });
        });
    }

    function createFileItem(file) {
        const item = document.createElement('div');
        item.className = 'flex flex-col items-center p-4 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 relative';
        
        const icon = document.createElement('i');
        icon.className = getFileIcon(file);
        icon.style.fontSize = '3rem';
        
        const name = document.createElement('span');
        name.className = 'mt-2 text-sm text-center break-all';
        name.textContent = file.name;
        
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'absolute top-1 right-1 text-red-500 hover:text-red-700';
        deleteBtn.innerHTML = '<i class="fas fa-trash-alt"></i>';
        deleteBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            deleteFile(file.name, file.type);
        });
        
        item.appendChild(icon);
        item.appendChild(name);
        item.appendChild(deleteBtn);
        
        if (file.type === 'dir') {
            item.addEventListener('click', () => openFolder(file.name));
        } else {
            item.addEventListener('click', () => downloadFile(file.name));
        }
        
        return item;
    }

    function getFileIcon(file) {
        if (file.type === 'dir') {
            return 'fas fa-folder text-yellow-500';
        }
        const extension = file.name.split('.').pop().toLowerCase();
        switch (extension) {
            case 'pdf':
                return 'fas fa-file-pdf text-red-500';
            case 'doc':
            case 'docx':
                return 'fas fa-file-word text-blue-500';
            case 'xls':
            case 'xlsx':
                return 'fas fa-file-excel text-green-500';
            case 'ppt':
            case 'pptx':
                return 'fas fa-file-powerpoint text-orange-500';
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
                return 'fas fa-file-image text-purple-500';
            default:
                return 'fas fa-file text-gray-500';
        }
    }

    function uploadFile() {
        const file = fileInput.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        formData.append('currentDir', currentDir);

        fetch('upload_file.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                loadFileList(currentDir);
                alert('File uploaded successfully');
            } else {
                alert('Error uploading file: ' + result.message);
            }
        })
        .catch(error => console.error('Error uploading file:', error));
    }

    function createFolder() {
        const folderName = prompt('Enter folder name:');
        if (!folderName) return;

        const formData = new FormData();
        formData.append('folderName', folderName);
        formData.append('currentDir', currentDir);

        fetch('create_folder.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                loadFileList(currentDir);
                alert('Folder created successfully');
            } else {
                alert('Error creating folder: ' + result.message);
            }
        })
        .catch(error => console.error('Error creating folder:', error));
    }

    function openFolder(folderName) {
        const newDir = currentDir === '.' ? folderName : `${currentDir}/${folderName}`;
        loadFileList(newDir);
    }

    function downloadFile(filename) {
        window.location.href = `download_file.php?filename=${encodeURIComponent(filename)}&dir=${encodeURIComponent(currentDir)}`;
    }

    function deleteFile(name, type) {
        if (confirm(`Are you sure you want to delete ${name}?`)) {
            const formData = new FormData();
            formData.append('name', name);
            formData.append('type', type);
            formData.append('currentDir', currentDir);

            fetch('delete_file.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    loadFileList(currentDir);
                } else {
                    alert('Error deleting ' + (type === 'dir' ? 'folder' : 'file') + ': ' + result.message);
                }
            })
            .catch(error => console.error('Error deleting ' + (type === 'dir' ? 'folder' : 'file') + ':', error));
        }
    }

    function logout() {
        fetch('logout.php')
            .then(() => {
                window.location.href = 'index.html';
            })
            .catch(error => console.error('Error logging out:', error));
    }
});