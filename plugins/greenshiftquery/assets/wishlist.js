
document.addEventListener('click', function (ev) {
    let gsheartplusElement = ev.target.closest('.gsheartplus');
    if (!gsheartplusElement) {
        return;
    }
    else {
        let obj = gsheartplusElement;
        let actionwishlist = 'add';
        if (obj.classList.contains("restrict_for_guests")) {
            let loginpage = obj.dataset.customurl;
            window.location.href = loginpage;
            return false;
        }
        if (obj.classList.contains("alreadywish")) {
            if (typeof obj.dataset.wishlink !== "undefined" && obj.dataset.wishlink != '' && document.querySelector('.gspb-favorites-posts') == null) {
                window.location.href = obj.dataset.wishlink;
                return false;
            }
            actionwishlist = 'remove';
        }

        let post_id = parseInt(obj.dataset.post_id);
        let informer = parseInt(obj.dataset.informer);
        let svgicon = obj.querySelector('.wishiconwrap').innerHTML;

        obj.classList.add("loading");
        obj.querySelector('.wishiconwrap').innerHTML = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" viewBox="0 0 100 100"><path d="M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50"><animateTransform attributeName="transform" attributeType="XML" type="rotate" dur="1s" from="0 50 50" to="360 50 50" repeatCount="indefinite"></animateTransform></path></svg>';

        const request = new XMLHttpRequest();
        request.open('POST', gswishvars.ajax_url, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.responseType = 'json';
        request.onload = function () {
            if (this.status >= 200 && this.status < 400) {
                //let responseobj = this.response.data;
                obj.querySelector('.wishiconwrap').innerHTML = svgicon;
                obj.classList.remove("loading");

                if (actionwishlist == 'remove') {
                    obj.classList.remove("alreadywish");
                    informer = informer - 1;
                    obj.closest('.gs-wishlist-wrap').querySelector('#wishcount' + post_id + '').innerHTML = informer;
                    if (document.querySelector('.gs-wish-icon-counter') != null) {
                        let wishcountericons = document.querySelectorAll('.gs-wish-icon-counter');
                        wishcountericons.forEach(function (item) {
                            let overallcount = parseInt(item.innerHTML);
                            item.innerHTML = overallcount - 1;
                        });
                    }
                    obj.dataset.informer = informer;
                } else {
                    obj.classList.add("alreadywish");
                    informer = informer + 1;
                    obj.closest('.gs-wishlist-wrap').querySelector('#wishcount' + post_id + '').innerHTML = informer;
                    if (document.querySelector('.gs-wish-icon-counter') != null) {
                        let wishcountericons = document.querySelectorAll('.gs-wish-icon-counter');
                        wishcountericons.forEach(function (item) {
                            let overallcount = parseInt(item.innerHTML);
                            item.innerHTML = overallcount + 1;
                        });
                    }
                    obj.dataset.informer = informer;
                }
            } else {
                // Response error
            }
        };
        request.onerror = function () {
            // Connection error
        };
        request.send('action=gswishcounter&wishnonce=' + gswishvars.wishnonce + '&wish_count=' + actionwishlist + '&post_id=' + post_id);

    }
});

const requestFavUpdate = new XMLHttpRequest();
requestFavUpdate.open('POST', gswishvars.ajax_url, true);
requestFavUpdate.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
requestFavUpdate.responseType = 'json';
requestFavUpdate.onload = function () {
    if (this.status >= 200 && this.status < 400) {
        let responseobj = this.response;
        if (responseobj.wishlistids) {
            let wishlistids = responseobj.wishlistids.split(',');
            if (wishlistids.length != 0) {
                let favListed = document.querySelectorAll(".gsheartplus");
                favListed.forEach(function (index) {
                    var postID = index.getAttribute("data-post_id");
                    if (wishlistids.includes(postID)) {
                        if (!index.classList.contains('alreadywish')) {
                            index.classList.add('alreadywish');
                            let informer = parseInt(index.getAttribute("data-informer"));
                            informer = informer + 1;
                            index.setAttribute("data-informer", informer);
                            index.closest('.gs-wishlist-wrap').querySelector('#wishcount' + postID + '').innerHTML = informer;
                        }
                    }
                });
                if (document.querySelector('.gs-wish-icon-counter') != null) {
                    let wishcountericons = document.querySelectorAll('.gs-wish-icon-counter');
                    wishcountericons.forEach(function (item) {
                        item.innerHTML = responseobj.wishcounter;
                    });
                }
            }
        }
    } else {
        // Response error
    }
};
requestFavUpdate.onerror = function () {
    // Connection error
};
requestFavUpdate.send('action=gswishrecount&wishnonce=' + gswishvars.wishnonce);

if(document.getElementById('gs-wish-list-results') != null){
    let xhr = new XMLHttpRequest();
    xhr.open('POST', gswishvars.ajax_url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    requestFavUpdate.responseType = 'json';
    xhr.onload = function() {
        if (this.status >= 200 && this.status < 400) {
            if(JSON.parse(this.response).data == 'noitem'){
                document.getElementById('gs-wish-list-results').innerHTML = document.getElementById('gs-wish-list-results').dataset.noitem;
            }else{
                document.getElementById('gs-wish-list-results').innerHTML = JSON.parse(this.response).data;
            }
        } else {
            // Error handling
            console.error('AJAX error');
        }
    };

    xhr.send('action=gswishresults&wishnonce=' + gswishvars.wishnonce);
}