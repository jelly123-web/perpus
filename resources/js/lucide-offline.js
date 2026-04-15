function drawFallbackIcon(element) {
    const ns = 'http://www.w3.org/2000/svg';
    const iconName = element.getAttribute('data-lucide') || 'icon';
    const svg = document.createElementNS(ns, 'svg');

    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', '2');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.setAttribute('aria-hidden', 'true');
    svg.setAttribute('data-lucide-fallback', iconName);

    if (element.className) {
        svg.setAttribute('class', element.className);
    }

    const inlineStyle = element.getAttribute('style');
    if (inlineStyle) {
        svg.setAttribute('style', inlineStyle);
    }

    const title = document.createElementNS(ns, 'title');
    title.textContent = iconName;
    svg.appendChild(title);

    const circle = document.createElementNS(ns, 'circle');
    circle.setAttribute('cx', '12');
    circle.setAttribute('cy', '12');
    circle.setAttribute('r', '9');
    svg.appendChild(circle);

    const slash = document.createElementNS(ns, 'path');
    slash.setAttribute('d', 'M8 16L16 8');
    svg.appendChild(slash);

    element.replaceWith(svg);
}

if (!window.lucide || typeof window.lucide.createIcons !== 'function') {
    window.lucide = {
        createIcons() {
            document.querySelectorAll('i[data-lucide], span[data-lucide]').forEach((element) => {
                drawFallbackIcon(element);
            });
        },
    };
}
