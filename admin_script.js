document.addEventListener('DOMContentLoaded', () => {
    // --- LÓGICA DO GRÁFICO ---
    const chartElement = document.getElementById('usersPerModuleChart');
    if(chartElement) {
        fetch('get_stats.php').then(response => response.json()).then(result => {
            if(result.status === 'success') {
                const ctx = chartElement.getContext('2d');
                new Chart(ctx, { type: 'doughnut', data: { labels: result.data.labels, datasets: [{ label: 'Utilizadores', data: result.data.values, backgroundColor: ['#4285F4', '#EA4335', '#FBBC05', '#34A853', '#A85334', '#FF6384', '#36A2EB'], hoverOffset: 4 }] }, options: { responsive: true, maintainAspectRatio: false } });
            }
        });
    }

    // --- LÓGICA DO MODAL "GERIR MÓDULOS DO UTILIZADOR" ---
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

    // --- LÓGICA DO MODAL "CRIAR/EDITAR MÓDULO" ---
    const moduleModal = document.getElementById('module-modal');
    if (moduleModal) {
        const modalTitle = document.getElementById('modal-title');
        const moduleForm = document.getElementById('module-form');
        const moduleIdInput = document.getElementById('edit-module-id');
        const moduleNameInput = document.getElementById('edit-module-name');
        const modulePathInput = document.getElementById('edit-module-path');
        const moduleSvgInput = document.getElementById('edit-module-svg');
        const showCreateBtn = document.getElementById('btn-show-create-modal');
        const closeModalBtn = moduleModal.querySelector('.close-modal');

        showCreateBtn.addEventListener('click', () => {
            modalTitle.textContent = 'Criar Novo Módulo';
            moduleForm.action = 'create_module.php';
            moduleForm.reset();
            moduleIdInput.value = '';
            moduleModal.style.display = 'block';
        });

        document.querySelectorAll('.btn-edit-module').forEach(button => {
            button.addEventListener('click', () => {
                const moduleId = button.dataset.moduleid;
                fetch(`get_module_details.php?module_id=${moduleId}`).then(res => res.json()).then(result => {
                    if (result.status === 'success') {
                        modalTitle.textContent = 'Editar Módulo';
                        moduleForm.action = 'edit_module.php';
                        moduleIdInput.value = result.data.id;
                        moduleNameInput.value = result.data.module_name;
                        modulePathInput.value = result.data.module_path;
                        moduleSvgInput.value = result.data.icon_svg;
                        moduleModal.style.display = 'block';
                    } else { alert(result.message); }
                });
            });
        });
        
        if (closeModalBtn) closeModalBtn.addEventListener('click', () => moduleModal.style.display = 'none');
        window.addEventListener('click', (e) => { if (e.target == moduleModal) moduleModal.style.display = 'none'; });
    }
});