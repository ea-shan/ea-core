"use strict";
const lighboxgallaries = document.getElementsByClassName('gspb_gallery_lightbox');
if(lighboxgallaries.length){
    let arraylight = Array.from(lighboxgallaries);
    arraylight.forEach(item => {
        new SimpleLightbox({elements: item.querySelectorAll('.imagelink'), videoRegex: new RegExp(/\.mp4|youtube.com|vimeo.com/)});
    });
}