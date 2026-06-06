
document.addEventListener("DOMContentLoaded", function() {
    // Find how many folders deep we are relative to root by looking at where assets/ is loaded from
    const scripts = document.getElementsByTagName('script');
    let rootPrefix = './';
    for (let script of scripts) {
        if (script.src && script.src.includes('components.js')) {
            // The src will look like "../../assets/dashboard/js/components.js"
            // We can extract the relative path prefix
            const match = script.getAttribute('src').match(/^(.*?)assets\/dashboard\/js\/components\.js/);
            if (match && match[1]) {
                rootPrefix = match[1];
            }
            break;
        }
    }

    function loadComponent(id, file) {
        const el = document.getElementById(id);
        if (el) {
            fetch(rootPrefix + file)
                .then(response => {
                    if (response.ok) return response.text();
                    throw new Error('Failed to load component');
                })
                .then(html => {
                    el.outerHTML = html;
                })
                .catch(err => console.error('Error fetching component:', err));
        }
    }

    loadComponent('sidebar-container', 'components/sidebar.html');
    loadComponent('header-container', 'components/header.html');
    loadComponent('footer-container', 'components/footer.html');
});
    