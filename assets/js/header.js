// Isolamento com namespace ÚNICO para evitar conflitos
(function() {
    'use strict';

    // Seletores específicos do header
    const HEADER_SELECTORS = {
        header: '.universal-header',
        dropdownToggle: '.universal-header .dropdown-toggle',
        dropdown: '.universal-header .dropdown',
        dropdownMenu: '.universal-header .dropdown-menu',
        userMenu: '.universal-header .user-menu'
    };

    /**
     * Inicializa os event listeners dos dropdowns
     */
    function initDropdowns() {
        const toggles = document.querySelectorAll(HEADER_SELECTORS.dropdownToggle);
        
        toggles.forEach(toggle => {
            toggle.addEventListener('click', handleDropdownClick);
        });
    }

    /**
     * Handler para clique no toggle do dropdown
     */
    function handleDropdownClick(e) {
        e.preventDefault();
        e.stopPropagation();

        // Encontra o elemento .dropdown dentro do header
        const dropdown = this.closest(HEADER_SELECTORS.dropdown);
        
        if (!dropdown) return;

        // Fecha todos os outros dropdowns do header
        closeOtherDropdowns(dropdown);

        // Abre/fecha o dropdown atual
        dropdown.classList.toggle('active');
    }

    /**
     * Fecha todos os dropdowns do header exceto o passado como parâmetro
     */
    function closeOtherDropdowns(exceptDropdown) {
        const allDropdowns = document.querySelectorAll(HEADER_SELECTORS.dropdown);
        
        allDropdowns.forEach(dropdown => {
            if (dropdown !== exceptDropdown) {
                dropdown.classList.remove('active');
            }
        });
    }

    /**
     * Fecha todos os dropdowns ao clicar fora
     */
    function handleClickOutside(e) {
        // Verifica se o clique foi dentro do header
        const headerElement = document.querySelector(HEADER_SELECTORS.header);
        
        if (!headerElement) return;
        
        // Se o clique não foi no header, fecha todos os dropdowns do header
        if (!headerElement.contains(e.target)) {
            const allDropdowns = document.querySelectorAll(HEADER_SELECTORS.dropdown);
            allDropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
        // Se o clique foi no header mas não em um dropdown, fecha todos
        else if (!e.target.closest(HEADER_SELECTORS.dropdown)) {
            const allDropdowns = document.querySelectorAll(HEADER_SELECTORS.dropdown);
            allDropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    }

    /**
     * Adiciona efeito scroll ao header
     */
    function handleScroll() {
        const header = document.querySelector(HEADER_SELECTORS.header);
        
        if (!header) return;
        
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    }

    /**
     * Fecha dropdowns ao pressionar ESC (apenas do header)
     */
    function handleEscapeKey(e) {
        if (e.key === 'Escape') {
            const allDropdowns = document.querySelectorAll(HEADER_SELECTORS.dropdown);
            allDropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
    }

    /**
     * Inicializa o script
     */
    function init() {
        // Verifica se o header existe antes de inicializar
        const headerElement = document.querySelector(HEADER_SELECTORS.header);
        
        if (!headerElement) {
            console.warn('Header não encontrado. Script não será inicializado.');
            return;
        }

        // Inicializa dropdowns
        initDropdowns();

        // Event listener para fechar ao clicar fora
        document.addEventListener('click', handleClickOutside);

        // Event listener para scroll
        window.addEventListener('scroll', handleScroll);

        // Event listener para tecla ESC
        document.addEventListener('keydown', handleEscapeKey);

        console.log('Header Nexus Network inicializado com sucesso!');
    }

    /**
     * Aguarda o DOM estar pronto e inicializa
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();