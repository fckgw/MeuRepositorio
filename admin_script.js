document.addEventListener('DOMContentLoaded', () => {
    const chartElement = document.getElementById('usersPerModuleChart');
    if(chartElement) {
        fetch('get_stats.php')
        .then(response => response.json())
        .then(result => {
            if(result.status === 'success') {
                const ctx = chartElement.getContext('2d');
                new Chart(ctx, { type: 'doughnut', data: { labels: result.data.labels, datasets: [{ label: 'Utilizadores', data: result.data.values, backgroundColor: ['#4285F4', '#EA4335', '#FBBC05', '#34A853', '#A85334', '#FF6384', '#36A2EB'], hoverOffset: 4 }] }, options: { responsive: true, maintainAspectRatio: false } });
            }
        });
    }

    const manageModulesModal = document.getElementById('manage-modules-modal');
    if(manageModulesModal) {
        document.querySelectorAll('.btn-manage-modules').forEach(button => {
            button.addEventListener('click', () => {
                const userId = button.dataset.userid;
                const username = button.dataset.username;
                const modalUsername = document.getElementById('modal-username');
                const modalUserIdInput = document.getElementById('modal-user-id');
                const formCheckboxes = document.querySelectorAll('#manage-modules-form input[type="checkbox"]');
                modalUsername.textContent = username;
                modalUserIdInput.value = userId;
                formCheckboxes.forEach(cb => cb.checked = false);
                fetch(`get_user_modules.php?user_id=${userId}`).then(res => res.json()).then(result => {
                    if (result.status === 'success' && result.modules) {
                        result.modules.forEach(moduleId => {
                            const checkbox = document.querySelector(`#manage-modules-form input[value="${moduleId}"]`);
                            if (checkbox) checkbox.checked = true;
                        });
                    }
                });
                manageModulesModal.style.display = 'block';
            });
        });
        const closeManageModal = manageModulesModal.querySelector('.close-modal');
        if(closeManageModal) closeManageModal.addEventListener('click', () => manageModulesModal.style.display = 'none');
        window.addEventListener('click', (e) => { if (e.target == manageModulesModal) manageModulesModal.style.display = 'none'; });
    }

    const editModal = document.getElementById('edit-module-modal');
    if (editModal) {
        const editModuleId = document.getElementById('edit-module-id');
        const editModuleName = document.getElementById('edit-module-name');
        const editModulePath = document.getElementById('edit-module-path');
        const editModuleSvg = document.getElementById('edit-module-svg');
        document.querySelectorAll('.btn-edit-module').forEach(button => {
            button.addEventListener('click', () => {
                const moduleId = button.dataset.moduleid;
                fetch(`get_module_details.php?module_id=${moduleId}`).then(res => res.json()).then(result => {
                    if (result.status === 'success') {
                        editModuleId.value = result.data.id;
                        editModuleName.value = result.data.module_name;
                        editModulePath.value = result.data.module_path;
                        editModuleSvg.value = result.data.icon_svg;
                        editModal.style.display = 'block';
                    } else { alert(result.message); }
                });
            });
        });
        const closeEditModal = editModal.querySelector('.close-modal');
        if(closeEditModal) closeEditModal.addEventListener('click', () => editModal.style.display = 'none');
        window.addEventListener('click', (e) => { if (e.target == editModal) editModal.style.display = 'none'; });
    }
    
    const createCard = document.getElementById('create-module-card');
    const showCreateFormBtn = document.getElementById('btn-show-create-form');
    if(createCard && showCreateFormBtn) {
        showCreateFormBtn.addEventListener('click', () => {
            if (createCard.style.display === 'none' || createCard.style.display === '') {
                createCard.style.display = 'block';
                showCreateFormBtn.textContent = 'Cancelar';
            } else {
                createCard.style.display = 'none';
                showCreateFormBtn.textContent = 'Criar Novo Módulo';
            }
        });
    }
});