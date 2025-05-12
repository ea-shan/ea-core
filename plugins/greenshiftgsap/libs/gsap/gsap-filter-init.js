function GSFlipFilters(items, filters, allCheckbox) {
    const state = Flip.getState(items),
        classes = filters.filter(checkbox => checkbox.checked).map(checkbox => "." + checkbox.id),
        matches = classes.length ? gsap.utils.toArray(classes.join(",")) : classes;

    items.forEach(item => {
        if (matches.indexOf(item) === -1) {
            item.style.display = "none";
            item.classList.add('filter-remove-item');
        } else {
            item.classList.remove('filter-remove-item');
            item.style.display = "";
        }
    });
    Flip.from(state, {
        duration: 0.7,
        scale: true,
        ease: "power1.inOut",
        stagger: 0.08,
        onEnter: elements => gsap.fromTo(elements, { opacity: 0, scale: 0 }, { opacity: 1, scale: 1, duration: 1 }),
        onLeave: elements => gsap.to(elements, { opacity: 0, scale: 0, duration: 1 })
    });
}

let gsfilter_wrappers = document.getElementsByClassName('gspb-flipfilters');
if (gsfilter_wrappers.length > 0) {
    for (let i = 0; i < gsfilter_wrappers.length; i++) {
        let current = gsfilter_wrappers[i];

        const allCheckbox = current.querySelector('.gspb-checkbox-filter-all'),
        filters = gsap.utils.toArray(current.querySelectorAll('.gspb-checkbox-filter-item')),
        items = gsap.utils.toArray(current.querySelectorAll('.gs-flipfilter'));

        filters.forEach(btn => btn.addEventListener('click', function (ev) {
            GSFlipFilters(items, filters, allCheckbox);
        }
        ));
        allCheckbox.addEventListener('click', function (ev) {
            filters.forEach(checkbox => checkbox.checked = allCheckbox.checked);
            GSFlipFilters(items, filters, allCheckbox);
        });
    };
}