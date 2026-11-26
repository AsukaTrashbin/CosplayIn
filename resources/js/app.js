import * as bootstrap from 'bootstrap';

// Bootstrap 5: aktifkan seluruh komponen (navbar collapse, dropdown, dsb).
window.bootstrap = bootstrap;

function initCatalogCityDropdown() {
    const container = document.querySelector('[data-city-dropdown]');
    if (!container) return;

    const cities = JSON.parse(container.dataset.cities || '[]');
    const triggerBtn = container.querySelector('#kotaDropdownBtn');
    const menu = container.querySelector('#kotaDropdownMenu');
    const searchInput = container.querySelector('[data-city-search]');
    const optionsBox = container.querySelector('[data-city-options]');
    const emptyHint = container.querySelector('[data-city-empty]');
    const labelSpan = container.querySelector('[data-city-label]');
    const hiddenInput = document.getElementById('kotaValue');

    if (!triggerBtn || !menu || !searchInput || !optionsBox || !emptyHint || !labelSpan || !hiddenInput) {
        return;
    }

    const createOptionButton = (cityValue, text) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'catalog-city-option' + (hiddenInput.value === cityValue ? ' is-selected' : '');
        button.dataset.cityOption = cityValue;
        button.textContent = text;
        button.addEventListener('click', () => {
            selectCity(cityValue);
        });
        return button;
    };

    const renderOptions = (rawQuery) => {
        const query = rawQuery.trim().toLowerCase();
        optionsBox.innerHTML = '';

        // Default sebelum pengguna mengetik manual: hanya tampilkan "Semua kota".
        if (query === '') {
            optionsBox.appendChild(createOptionButton('', 'Semua kota'));
            emptyHint.classList.add('d-none');
            return;
        }

        const matched = cities.filter((city) => city.toLowerCase().includes(query));

        if (matched.length === 0) {
            optionsBox.appendChild(createOptionButton('', 'Semua kota'));
            emptyHint.classList.remove('d-none');
            return;
        }

        emptyHint.classList.add('d-none');
        matched.forEach((city) => {
            optionsBox.appendChild(createOptionButton(city, city));
        });
    };

    const selectCity = (cityValue) => {
        hiddenInput.value = cityValue;
        labelSpan.textContent = cityValue ? `Kota: ${cityValue}` : 'Kota: Semua';
        closeMenu();
    };

    const openMenu = () => {
        menu.classList.remove('d-none');
        triggerBtn.setAttribute('aria-expanded', 'true');
        searchInput.value = '';
        renderOptions('');
        requestAnimationFrame(() => searchInput.focus());
    };

    const closeMenu = () => {
        menu.classList.add('d-none');
        triggerBtn.setAttribute('aria-expanded', 'false');
    };

    triggerBtn.addEventListener('click', (event) => {
        event.stopPropagation();
        if (menu.classList.contains('d-none')) {
            openMenu();
        } else {
            closeMenu();
        }
    });

    searchInput.addEventListener('input', () => {
        renderOptions(searchInput.value);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            const firstMatch = optionsBox.querySelector('.catalog-city-option');
            if (firstMatch) {
                selectCity(firstMatch.dataset.cityOption || '');
            }
        } else if (event.key === 'Escape') {
            closeMenu();
            triggerBtn.focus();
        }
    });

    document.addEventListener('click', (event) => {
        if (!container.contains(event.target)) {
            closeMenu();
        }
    });
}

document.addEventListener('DOMContentLoaded', initCatalogCityDropdown);
