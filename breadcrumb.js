let breadcrumbs = JSON.parse(sessionStorage.getItem('breadcrumbs')) || [];
let currentPageUrl = window.location.pathname;

// 🧠 Instead of hardcoding, get <title> automatically
let currentPageName = document.title.trim();

let pageExists = breadcrumbs.some(b => b.url === currentPageUrl);

if (!pageExists) {
    breadcrumbs.push({ name: currentPageName, url: currentPageUrl });
    sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));
}

let breadcrumbList = document.getElementById('breadcrumb-list');
breadcrumbList.innerHTML = '';

breadcrumbs.forEach((breadcrumb, index) => {
    let item = document.createElement('li');
    let link = document.createElement('a');
    link.href = breadcrumb.url;
    link.textContent = breadcrumb.name;

    link.addEventListener('click', (e) => {
        e.preventDefault();
        breadcrumbs = breadcrumbs.slice(0, index + 1);
        sessionStorage.setItem('breadcrumbs', JSON.stringify(breadcrumbs));
        window.location.href = breadcrumb.url;
    });

    item.appendChild(link);
    breadcrumbList.appendChild(item);

    if (index < breadcrumbs.length - 1) {
        let separator = document.createElement('span');
        separator.textContent = ' > ';
        breadcrumbList.appendChild(separator);
    }
});