
console.log('scrolleditor.js loaded');
if(window.name == 'editor-canvas'){
    let innerDoc = window.document;
    let iframeforGSAP = window.parent.document.querySelector('[name="editor-canvas"]');
    console.log('iframeforGSAP', iframeforGSAP);
    ScrollTrigger.scrollerProxy(innerDoc.scrollingElement, {
        scrollTop(value) {
            if (arguments.length) {
                innerDoc.scrollingElement.scrollTop = value; // setter
            }
            return innerDoc.scrollingElement.scrollTop;    // getter
        },
        getBoundingClientRect() {
            return iframeforGSAP.getBoundingClientRect();
        }
    });
    innerDoc.addEventListener("scroll", ScrollTrigger.update);
}
