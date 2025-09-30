document.addEventListener('DOMContentLoaded', () => {
    // --- VARIÁVEIS GLOBAIS E INICIAIS ---
    const fileItemWrappers = document.querySelectorAll('.file-item-wrapper');
    const currentPath = new URLSearchParams(window.location.search).get('path') || '';

    // --- LÓGICA DE ÍCONES E PREVIEW (EXECUTADA PARA CADA ITEM) ---
    fileItemWrappers.forEach(wrapper => {
        const filename = wrapper.dataset.filename;
        const fileItem = wrapper.querySelector('.file-item');
        if (!fileItem) return;

        const iconElement = fileItem.querySelector('.file-icon');
        const isDir = fileItem.dataset.isDir === '1';
        const isImage = fileItem.dataset.isImage === '1';

        // Define o ícone correto
        let iconClass = 'icon-default';
        if (isDir) { iconClass = 'icon-folder'; }
        else if (/\.(jpg|jpeg|png|gif|webp|svg)$/i.test(filename)) { iconClass = 'icon-image'; }
        else if (/\.(mp3|wav|ogg|flac)$/i.test(filename)) { iconClass = 'icon-audio'; }
        else if (/\.(mp4|webm|mov|avi)$/i.test(filename)) { iconClass = 'icon-video'; }
        else if (/\.(zip|rar|7z|tar\.gz)$/i.test(filename)) { iconClass = 'icon-archive'; }
        iconElement.classList.add(iconClass);

        // Adiciona o evento de clique para preview de imagens
        if (isImage) {
            fileItem.addEventListener('click', () => {
                const modal = document.getElementById('image-preview-modal');
                const modalImg = document.getElementById('modal-image');
                const imageUrl = publicBaseUrl + publicBasePath + (currentPath ? currentPath + '/' : '') + filename;
                modalImg.src = imageUrl;
                modal.style.display = 'block';
            });
        }
    });

    // --- FUNCIONALIDADE DE UPLOAD COM BARRA DE PROGRESSO E VALIDAÇÃO ---
    const uploadBtn = document.getElementById('upload-btn');
    const fileInput = document.getElementById('file-input');
    const uploadForm = document.getElementById('upload-form');
    if (uploadBtn) {
        uploadBtn.addEventListener('click', () => fileInput.click());
    }
    if (fileInput) {
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                let totalSize = 0;
                for (const file of fileInput.files) { totalSize += file.size; }

                if (totalSize > availableSpace) {
                    const totalSizeMB = (totalSize / 1024 / 1024).toFixed(2);
                    const availableSpaceMB = (availableSpace / 1024 / 1024).toFixed(2);
                    alert(`Espaço de Armazenamento Insuficiente!\n\n` +
                          `Você tentou enviar ${totalSizeMB} MB, mas só tem ${availableSpaceMB} MB disponíveis.\n\n` +
                          `Para continuar, libere espaço ou compre mais armazenamento com a nossa equipe.`);
                    fileInput.value = '';
                    return;
                }
                uploadFiles(uploadForm);
            }
        });
    }

    function uploadFiles(form) {
        const formData = new FormData(form);
        const xhr = new XMLHttpRequest();
        const uploadModal = document.getElementById('upload-progress-modal');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');
        const uploadFeedback = document.getElementById('upload-feedback');
        xhr.open('POST', form.action, true);
        xhr.upload.addEventListener('progress', e => {
            if (e.lengthComputable) {
                const percentComplete = Math.round((e.loaded / e.total) * 100);
                progressBar.style.width = percentComplete + '%';
                progressText.textContent = `${percentComplete}% concluído`;
            }
        });
        xhr.onload = () => { location.reload(); };
        xhr.onerror = () => { alert("Ocorreu um erro de rede durante o upload."); uploadModal.style.display = 'none'; };
        uploadModal.style.display = 'block';
        progressText.textContent = 'Iniciando...';
        progressBar.style.width = '0%';
        uploadFeedback.innerHTML = `Enviando ${formData.getAll('arquivos[]').length} arquivo(s)...`;
        xhr.send(formData);
    }

    // --- FUNCIONALIDADE DE CRIAR PASTA ---
    const newFolderBtn = document.getElementById('new-folder-btn');
    const newFolderForm = document.getElementById('new-folder-form');
    const folderNameInput = document.getElementById('folder_name_input');
    if (newFolderBtn) {
        newFolderBtn.addEventListener('click', () => {
            const folderName = prompt('Digite o nome da nova pasta:');
            if (folderName && folderName.trim() !== '') {
                folderNameInput.value = folderName;
                newFolderForm.submit();
            }
        });
    }

    // --- FUNCIONALIDADE DE DELETAR ITEM ---
    const deleteBtns = document.querySelectorAll('.delete');
    const deleteItemForm = document.getElementById('delete-item-form');
    const itemNameInput = document.getElementById('item_name_input');
    deleteBtns.forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation(); e.preventDefault();
            const itemName = btn.dataset.name;
            if (confirm(`Tem certeza que deseja remover "${itemName}"? Esta ação não pode ser desfeita.`)) {
                itemNameInput.value = itemName;
                deleteItemForm.submit();
            }
        });
    });

    // --- FUNCIONALIDADE "MOVER PARA..." ---
    const moveModal = document.getElementById('move-item-modal');
    const confirmMoveBtn = document.getElementById('confirm-move-btn');
    let itemToMove = null;
    document.querySelectorAll('.move').forEach(btn => {
        btn.addEventListener('click', e => {
            e.stopPropagation(); e.preventDefault();
            itemToMove = btn.dataset.name;
            const moveItemNameEl = document.getElementById('move-item-name');
            const folderSelect = document.getElementById('folder-destination-select');
            moveItemNameEl.textContent = itemToMove;
            folderSelect.innerHTML = '<option>Carregando pastas...</option>';
            fetch('get_folders.php').then(res => res.json()).then(data => {
                folderSelect.innerHTML = '';
                if (data.status === 'success' && data.folders) {
                    data.folders.forEach(folder => {
                        const option = document.createElement('option');
                        option.value = folder.path;
                        option.innerHTML = folder.name;
                        folderSelect.appendChild(option);
                    });
                }
            });
            moveModal.style.display = 'block';
        });
    });

    if (confirmMoveBtn) confirmMoveBtn.addEventListener('click', () => {
        const folderSelect = document.getElementById('folder-destination-select');
        const targetFolder = folderSelect.value;
        if (itemToMove && targetFolder !== null) {
            const data = { source_item: itemToMove, target_folder: targetFolder, current_path: currentPath };
            fetch('move_item.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) })
            .then(res => res.json()).then(result => { if (result.status === 'success') location.reload(); else alert(result.message); });
        }
    });
    
    // --- CONTROLE GERAL DOS MODAIS (FECHAR) ---
    const allModals = document.querySelectorAll('.modal');
    allModals.forEach(modal => {
        const closeModalBtn = modal.querySelector('.close-modal');
        if (closeModalBtn) closeModalBtn.addEventListener('click', () => modal.style.display = 'none');
    });
    window.addEventListener('click', e => { allModals.forEach(modal => { if (e.target == modal) modal.style.display = 'none'; }); });

    // --- FUNCIONALIDADE DRAG AND DROP ---
    const droppableFolders = document.querySelectorAll('.file-item[data-is-dir="1"]');
    fileItemWrappers.forEach(draggable => {
        draggable.addEventListener('dragstart', () => draggable.classList.add('dragging'));
        draggable.addEventListener('dragend', () => draggable.classList.remove('dragging'));
    });
    droppableFolders.forEach(folder => {
        folder.addEventListener('dragover', e => { e.preventDefault(); folder.classList.add('drag-over'); });
        folder.addEventListener('dragleave', () => folder.classList.remove('drag-over'));
        folder.addEventListener('drop', e => {
            e.preventDefault();
            folder.classList.remove('drag-over');
            const draggable = document.querySelector('.dragging');
            if (!draggable) return;
            const sourceItemName = draggable.dataset.filename;
            const targetFolderName = folder.closest('.file-item-wrapper').dataset.filename;
            if (confirm(`Mover "${sourceItemName}" para a pasta "${targetFolderName}"?`)) {
                const data = { source_item: sourceItemName, target_folder: currentPath ? currentPath + '/' + targetFolderName : targetFolderName, current_path: currentPath };
                fetch('move_item.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) })
                .then(res => res.json()).then(result => { if (result.status === 'success') location.reload(); else alert('Erro ao mover: ' + (result.message || 'Erro desconhecido.')); })
                .catch(error => { console.error('Erro:', error); alert('Ocorreu um erro de comunicação ao tentar mover o item.'); });
            }
        });
    });
});