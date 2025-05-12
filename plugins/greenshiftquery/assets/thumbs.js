document.addEventListener('click', function (ev) {
    let gsthumbElement = ev.target.closest('.hotenable');
    if (!gsthumbElement) {
        return;
    }
    else {
        let obj = gsthumbElement;
        if (obj.classList.contains('alreadyhot')) return false;
        let post_id = parseInt(obj.dataset.post_id);
        let informer = parseInt(obj.dataset.informer);
        let maxtemp = parseInt(obj.dataset.maxtemp);
        let svgicon = obj.innerHTML;
        let actioncounter = '';
        if (obj.classList.contains('gs-thumbsplus')) {
            actioncounter = 'hot';
        } else if (obj.classList.contains('gs-thumbsminus')) {
            actioncounter = 'cold';
        }
        obj.classList.add("loading");
        obj.closest('.gspb_thumbs').querySelector('.gs-thumbsminus').classList.add('alreadyhot');
        obj.closest('.gspb_thumbs').querySelector('.gs-thumbsplus').classList.add('alreadyhot');
        obj.closest('.gspb_thumbs').querySelector('.gs-thumbsminus').classList.remove('hotenable');
        obj.closest('.gspb_thumbs').querySelector('.gs-thumbsplus').classList.remove('hotenable');

        obj.innerHTML = '<svg version="1.1" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="20" viewBox="0 0 100 100"><path d="M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50"><animateTransform attributeName="transform" attributeType="XML" type="rotate" dur="1s" from="0 50 50" to="360 50 50" repeatCount="indefinite"></animateTransform></path></svg>';

        const request = new XMLHttpRequest();
        request.open('POST', gsthumbvars.ajax_url, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.responseType = 'json';
        request.onload = function () {
            if (this.status >= 200 && this.status < 400) {
                //let responseobj = this.response.data;
                obj.innerHTML = svgicon;
                if (obj.classList.contains('gs-thumbsplus')) {
                    informer = informer + 1;
                } else if (obj.classList.contains('gs-thumbsminus')) {
                    informer = informer - 1;
                }
                obj.classList.remove("loading");
                obj.closest('.gspb_thumbs').querySelector('#gs-thumbscount' + post_id + ' .countval').innerHTML = informer;
                obj.setAttribute("data-informer", informer);
                let widthcount = informer / maxtemp * 100;
                if (widthcount > 100) { widthcount = 100; }
                if (widthcount < 0) { widthcount = 0; }
                if (document.getElementById('scaleperc' + post_id)) {
                    document.getElementById('scaleperc' + post_id).css("width", widthcount + '%');
                }
            } else {
                // Response error
            }
        };
        request.onerror = function () {
            // Connection error
        };
        request.send('action=gshotcounter&hotnonce=' + gsthumbvars.hotnonce + '&hot_count=' + actioncounter + '&post_id=' + post_id);


    }
});