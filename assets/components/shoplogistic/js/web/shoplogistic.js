dart_hash = {
    get: function () {
        var vars = {}, hash, splitter, hashes;
        if (!this.oldbrowser()) {
            var pos = window.location.href.indexOf('?');
            hashes = (pos != -1) ? decodeURIComponent(window.location.href.substr(pos + 1)) : '';
            splitter = '&';
        }
        else {
            hashes = decodeURIComponent(window.location.hash.substr(1));
            splitter = '/';
        }

        if (hashes.length == 0) {
            return vars;
        }
        else {
            hashes = hashes.split(splitter);
        }

        for (var i in hashes) {
            if (hashes.hasOwnProperty(i)) {
                hash = hashes[i].split('=');
                if (typeof hash[1] == 'undefined') {
                    vars['anchor'] = hash[0];
                }
                else {
                    vars[hash[0]] = hash[1];
                }
            }
        }
        return vars;
    },
    set: function (vars) {
        var hash = '';
        var i;
        for (i in vars) {
            if (vars.hasOwnProperty(i)) {
                hash += '&' + i + '=' + vars[i];
            }
        }
        if (!this.oldbrowser()) {
            if (hash.length != 0) {
                hash = '?' + hash.substr(1);
                var specialChars = {"%": "%25", "+": "%2B"};
                for (i in specialChars) {
                    if (specialChars.hasOwnProperty(i) && hash.indexOf(i) !== -1) {
                        hash = hash.replace(new RegExp('\\' + i, 'g'), specialChars[i]);
                    }
                }
            }
            window.history.pushState({mSearch2: document.location.pathname + hash}, '', document.location.pathname + hash);
        }
        else {
            window.location.hash = hash.substr(1);
        }
    },
    add: function (key, val) {
        var hash = this.get();
        hash[key] = val;
        this.set(hash);
    },
    remove: function (key) {
        var hash = this.get();
        delete hash[key];
        this.set(hash);
    },
    clear: function () {
        this.set({});
    },
    oldbrowser: function () {
        return !(window.history && history.pushState);
    }
};

var profile = {
    options: {
        modal: '.newAddresToggleModal',
        delivery: '.newAddresDelivery',
        address_field: '#profile_address',
        map: 'addressMap',
        form: ".addressModalForm",
        select_address: ".newAddresDelivery__select"
    },
    initialize: function () {
        const newAddresToggleModal = document.querySelectorAll(this.options.modal);
        const newAddresDelivery = document.querySelector(this.options.delivery);

        if(newAddresToggleModal){
            for(let i = 0; i<newAddresToggleModal.length; i++){
                const attrib = newAddresToggleModal[i].dataset.listener
                console.log(attrib)
                if (attrib !== 'true') {
                    newAddresToggleModal[i].dataset.listener = 'true';
                    newAddresToggleModal[i].addEventListener('click', (e) => {
                        if (newAddresDelivery.classList.contains('show')) {
                            newAddresDelivery.classList.remove('show')
                            newAddresDelivery.classList.remove('new')
                            newAddresDelivery.classList.remove('edit')
                            newAddresDelivery.classList.remove('select')
                            body.style.overflow = "auto"
                        } else {
                            newAddresDelivery.classList.add('show')
                            body.style.overflow = "hidden"
                            if (Object.prototype.hasOwnProperty.call(newAddresToggleModal[i].dataset, "data")) {
                                var data = JSON.parse(newAddresToggleModal[i].dataset.data)
                            } else {
                                var data = {};
                            }
                            if (newAddresToggleModal[i].classList.contains('new')) {
                                newAddresDelivery.classList.add('new')
                                $('.addressModalForm')[0].reset();
                                $(profile.options.form).find("input[name=id]").val("0")
                            }
                            if (newAddresToggleModal[i].classList.contains('edit')) {
                                newAddresDelivery.classList.add('edit')
                                const data = JSON.parse(newAddresToggleModal[i].dataset.data)
                                profile.set(data)
                            }
                            if (newAddresToggleModal[i].classList.contains('select')) {
                                newAddresDelivery.classList.add('select')
                                const sting_dt = newAddresToggleModal[i].dataset.data
                                if (sting_dt) {
                                    const data = JSON.parse(sting_dt)
                                    profile.set(data)
                                }
                            }
                            data.sl_action = "profile/address/getlocation"
                            const location = this.send(data)
                            // this.initMap(location)
                        }
                    })
                }
            }
            const address_field = $(profile.options.form).find("#profile_address");
            $(document).on("keyup", profile.options.form + " " + "#profile_address", function(e){
                $(this).closest('.dart-input').removeClass("error");
            })
            $(this.options.modal + ' ' + this.options.address_field).suggestions({
                token: shoplogisticConfig['dadata_api_key'],
                type: "ADDRESS",
                /* Вызывается, когда пользователь выбирает одну из подсказок */
                onSelect: profile.setAddressFields
            });
            $(this.options.form).submit(function(e){
                e.preventDefault();
                $(profile.options.form).find("input[name=sl_action]").val("profile/address/set")
                const address = $(profile.options.form).find("#profile_address").val();
                if(address){
                    $(".newAddresDelivery").addClass("loading")
                    const data = $(profile.options.form).serialize();
                    profile.send(data)
                }else{
                    $(profile.options.form).find("#profile_address").closest('.dart-input').addClass("error");
                }
            })
            $(".newAddresDelivery__delete").click(function (e) {
                e.preventDefault();
                $(profile.options.form).find("input[name=sl_action]").val("profile/address/remove")
                $(".newAddresDelivery").addClass("loading")
                const data = $(profile.options.form).serialize();
                profile.send(data)
            })

            const selectAddress = document.querySelector(this.options.select_address);
            selectAddress.addEventListener('click', (e) => {
                e.preventDefault();
                const data = e.target.closest('.addressModalForm');
                const elem = data.querySelector("input[name=location_data]")
                const selectAddress = JSON.parse(elem.value);
                selectAddress.text_address = data.querySelector("input[name=text_address]").value
                selectAddress.room = data.querySelector("input[name=room]").value
                selectAddress.floor = data.querySelector("input[name=floor]").value
                selectAddress.entrance = data.querySelector("input[name=entrance]").value
                selectAddress.doorphone = data.querySelector("input[name=doorphone]").value
                const dt = {
                    data: selectAddress
                }
                sl_delivery.setDeliveryFields(dt);
                newAddresDelivery.classList.remove('show')
                newAddresDelivery.classList.remove('new')
                newAddresDelivery.classList.remove('edit')
                newAddresDelivery.classList.remove('select')
                // body.style.overflow = "auto"
                document.querySelector('#dm-my-addres').classList.remove('show');
            })
        }
    },
    reinitButtons: function(){
        const newAddresToggleModal = document.querySelectorAll('._js_addresses_list ' + this.options.modal + ', .dart-order ' + this.options.modal + ', ._js_addresses_list_modal ' + this.options.modal);
        const newAddresDelivery = document.querySelector(this.options.delivery);

        if(newAddresToggleModal) {
            for (let i = 0; i < newAddresToggleModal.length; i++) {
                const attrib = newAddresToggleModal[i].dataset.listener
                console.log(attrib)
                if (attrib !== 'true') {
                    newAddresToggleModal[i].dataset.listener = 'true';
                    newAddresToggleModal[i].addEventListener('click', (e) => {
                        if (newAddresDelivery.classList.contains('show')) {
                            newAddresDelivery.classList.remove('show')
                            newAddresDelivery.classList.remove('new')
                            newAddresDelivery.classList.remove('edit')
                            newAddresDelivery.classList.remove('select')
                            body.style.overflow = "auto"
                        } else {
                            newAddresDelivery.classList.add('show')
                            body.style.overflow = "hidden"
                            if (Object.prototype.hasOwnProperty.call(newAddresToggleModal[i].dataset, "data")) {
                                var data = JSON.parse(newAddresToggleModal[i].dataset.data)
                            } else {
                                var data = {};
                            }
                            if (newAddresToggleModal[i].classList.contains('new')) {
                                newAddresDelivery.classList.add('new')
                                $('.addressModalForm')[0].reset();
                                $(profile.options.form).find("input[name=id]").val("0")
                            }
                            if (newAddresToggleModal[i].classList.contains('edit')) {
                                newAddresDelivery.classList.add('edit')
                                const data = JSON.parse(newAddresToggleModal[i].dataset.data)
                                profile.set(data)
                            }
                            if (newAddresToggleModal[i].classList.contains('select')) {
                                newAddresDelivery.classList.add('select')
                                const sting_dt = newAddresToggleModal[i].dataset.data
                                if (sting_dt) {
                                    const data = JSON.parse(sting_dt)
                                    profile.set(data)
                                }
                            }
                            data.sl_action = "profile/address/getlocation"
                            const location = this.send(data)
                            // this.initMap(location)
                        }
                    })
                }
            }
        }

        const selectAddress = document.querySelector(this.options.select_address);
        selectAddress.addEventListener('click', (e) => {
            e.preventDefault();
            const data = e.target.closest('.addressModalForm');
            const elem = data.querySelector("input[name=location_data]")
            const selectAddress = JSON.parse(elem.value);
            selectAddress.text_address = data.querySelector("input[name=text_address]").value
            selectAddress.room = data.querySelector("input[name=room]").value
            selectAddress.floor = data.querySelector("input[name=floor]").value
            selectAddress.entrance = data.querySelector("input[name=entrance]").value
            selectAddress.doorphone = data.querySelector("input[name=doorphone]").value
            const dt = {
                data: selectAddress
            }
            sl_delivery.setDeliveryFields(dt);
        })

    },
    setAddressFields: function(suggestion){
        const coords = [suggestion.data.geo_lat, suggestion.data.geo_lon]
        profile.myPlacemark.geometry.setCoordinates(coords);
        profile.map.setCenter(coords)
        const location = {
            location_data: JSON.stringify(suggestion.data),
            postal_code: suggestion.data.postal_code,
            city: suggestion.data.city_with_type? suggestion.data.city_with_type : suggestion.data.settlement_with_type,
            street: suggestion.data.street,
            house: suggestion.data.house
        }
        profile.set(location)
    },
    set: function(data) {
        for (const key in data) {
            $(this.options.modal + ' input[name=' + key + ']').val(data[key])
        }
    },
    save: function(data) {

    },
    remove: function(data) {

    },
    update: function(data) {

    },
    updateList: function(data) {

    },
    initMap: function (location) {
        if(this.map){
            this.map.destroy();
        }
        const container = document.getElementById(profile.options.map)
        const image = container.querySelector('img')
        if(image){
            image.remove()
        }
        this.map = new ymaps.Map(profile.options.map, {
                center: [location.geo_lat, location.geo_lon],
                zoom: 10
            }, {
                searchControlProvider: 'yandex#search'
            });

        this.myPlacemark = new ymaps.Placemark([location.geo_lat, location.geo_lon], null, {
            preset: 'islands#blueDotIcon',
            draggable: true
        });

        this.map.geoObjects.add(this.myPlacemark);
        /* Событие dragend - получение нового адреса */
        this.myPlacemark.events.add('dragend', function(e){
            var cord = e.get('target').geometry.getCoordinates();
            $('#ypoint').val(cord);
            ymaps.geocode(cord).then(function(res) {
                let data = res.geoObjects.get(0).properties.getAll();
                let form_data = profile.mapPrepareData(data)
                profile.set(form_data)
                $('#profile_address').val(data.text);
            });
        });

        this.map.events.add('click', function (e) {
            var coords = e.get('coords');
            $('#ypoint').val(coords);
            profile.myPlacemark.geometry.setCoordinates(coords);
            ymaps.geocode(coords).then(function(res) {
                let data = res.geoObjects.get(0).properties.getAll();
                let form_data = profile.mapPrepareData(data)
                profile.set(form_data)
                $('#profile_address').val(data.text);
            });
        });
    },
    mapPrepareData: function(data) {
        const address_data = data.metaDataProperty.GeocoderMetaData.Address.Components
        const location = {
            postal_code: data.metaDataProperty.GeocoderMetaData.Address.postal_code
        }
        address_data.forEach((element) => {
            if(element.kind == 'locality'){
                location.city = element.name
            }else{
                location[element.kind] = element.name
            }
        });
        location.location_data = JSON.stringify(location)
        return location
    },
    send: function(data) {
        console.log(data)
        const container = document.getElementById(profile.options.map)
        container.classList.add('loading')
        var response = '';
        $.ajax({
            type: "POST",
            url: shoplogisticConfig['actionUrl'],
            dataType: 'json',
            data: data,
            success:  function(data_r) {
                const container = document.getElementById(profile.options.map)
                container.classList.remove('loading')
                $("._js_addresses_list").removeClass("loading")
                $("._js_addresses_list_modal").removeClass("loading")
                $(".newAddresDelivery").removeClass("loading")
                if(Object.prototype.hasOwnProperty.call(data_r, "map_location")){
                    profile.initMap(data_r.map_location)
                }
                if(Object.prototype.hasOwnProperty.call(data_r, "update_data")){
                    $("._js_addresses_list").html(data_r.update_data)
                    profile.reinitButtons()
                }
                if(Object.prototype.hasOwnProperty.call(data_r, "update_data_modal")){
                    $("._js_addresses_list_modal").html(data_r.update_data_modal)
                    profile.reinitButtons()
                }
                if (data_r.success) {
                    const newAddresToggleModal = document.querySelectorAll(profile.options.modal);
                    const newAddresDelivery = document.querySelector(profile.options.delivery);
                    if (newAddresDelivery.classList.contains('show')) {
                        newAddresDelivery.classList.remove('show')
                        newAddresDelivery.classList.remove('new')
                        newAddresDelivery.classList.remove('edit')
                        newAddresDelivery.classList.remove('select')
                        body.style.overflow = "auto"
                    }
                    const data = {
                        sl_action: "profile/address/update"
                    }
                    $("._js_addresses_list").addClass("loading")
                    $("._js_addresses_list_modal").addClass("loading")
                    profile.send(data)
                    profile.reinitButtons()
                }
            }
        });
    }
}

var dart_filters = {
    options: {
        form: 'df_filters',
        products: ".df_products",
        pagination: ".df_pagination",
        sortbutton: ".df_sort",
        instockbutton: "#toggle-button-catalog-df_instock",
        page: 1,
        values_delimeter: ","
    },
    elements: ['filters', 'results', 'pagination', 'total', 'sort', 'selected', 'limit', 'tpl'],
    initialize: function(){
        var query = window.location.search.substring(1)
        var qs = this.parseQuery(query)
        if(Object.prototype.hasOwnProperty.call(qs, "page")){
            dart_filters.options.page = qs.page
        }
        const filterForm = document.getElementById(this.options.form)
        if(filterForm){
            // sort handlers
            $(document).on("click", this.options.sortbutton, function(e){
                e.preventDefault();
                var parent = $(this).closest(".modal-sort");
                var sortdir = $(this).data("sortdir");
                var sortby = $(this).data("sortby");
                var csortdir = $("#" + dart_filters.options.form).find("input[name=sortdir]").val();
                var csortby = $("#" + dart_filters.options.form).find("input[name=sortby]").val();
                var text = $(this).text();

                parent.find(".modalSortToggle span").text(text);
                if(sortby != csortby){
                    parent.removeClass("active active_" + csortdir);
                }
                if(csortdir && csortby){
                    if(sortdir == csortdir){
                        if(csortdir == 'asc'){
                            sortdir = "desc";
                        }else{
                            sortdir = "asc";
                        }
                        $("#" + dart_filters.options.form).find("input[name=sortby]").val(sortby);
                        $("#" + dart_filters.options.form).find("input[name=sortdir]").val(sortdir);
                        parent.removeClass("active active_" + csortdir);
                        parent.addClass("active active_" + sortdir);
                    }else{
                        parent.data("sortby", "");
                        parent.data("sortdir", "");
                        $("#" + dart_filters.options.form).find("input[name=sortby]").val("");
                        $("#" + dart_filters.options.form).find("input[name=sortdir]").val("");
                        parent.removeClass("active active_" + csortdir);
                    }
                }else{
                    parent.data("sortby", sortby);
                    parent.data("sortdir", sortdir);
                    $("#" + dart_filters.options.form).find("input[name=sortby]").val(sortby);
                    $("#" + dart_filters.options.form).find("input[name=sortdir]").val(sortdir);
                    parent.addClass("active active_" + sortdir);
                }
                $(".modal-sort__close").trigger("click");
                const page = 1
                const old_page = dart_filters.options.page
                dart_filters.pageHandler(page, old_page)
                filterForm.dispatchEvent(new CustomEvent('submit', {cancelable: true}));
            });
            $(dart_filters.options.instockbutton).click(function(){
                if ($(this).is(':checked')){
                    $("#" + dart_filters.options.form).find("input[name=instock]").val(1);
                }else{
                    $("#" + dart_filters.options.form).find("input[name=instock]").val(0);
                }
                const page = 1
                const old_page = dart_filters.options.page
                dart_filters.pageHandler(page, old_page)
                filterForm.dispatchEvent(new CustomEvent('submit', {cancelable: true}));
            });

            $(".sliderui .polzunok-5").slider({
                min: $(".sliderui .sliderui-min input").attr('data-min') / 1,
                max: $(".sliderui .sliderui-max input").attr('data-max'),
                values: [$(".sliderui .sliderui-min input").attr('data-min'), $(".sliderui .sliderui-max input").attr('data-max')],
                range: true,
                animate: "fast",
                stop : function(event, ui) {
                    $(".sliderui-min input").val($(".sliderui .polzunok-5").slider("values", 0));
                    $(".sliderui-max input").val($(".sliderui .polzunok-5").slider("values", 1));
                    filterForm.dispatchEvent(new CustomEvent('submit', {cancelable: true}));
                }
            });

            $(".sliderui .polzunok-5").change(function() {
                var input_left = $(".sliderui-min input").val().replace(/[^0-9]/g, ''),
                    opt_left = $(".sliderui .polzunok-5").slider("option", "min"),
                    where_right = $(".sliderui .polzunok-5").slider("values", 1),
                    input_right = $(".sliderui-max input").val().replace(/[^0-9]/g, ''),
                    opt_right = $(".sliderui .polzunok-5").slider("option", "max"),
                    where_left = $(".sliderui .polzunok-5").slider("values", 0);
                if (input_left > where_right) {
                    input_left = where_right;
                }
                if (input_left < opt_left) {
                    input_left = opt_left;
                }
                if (input_left == "") {
                    input_left = 0;
                }
                if (input_right < where_left) {
                    input_right = where_left;
                }
                if (input_right > opt_right) {
                    input_right = opt_right;
                }
                if (input_right == "") {
                    input_right = 0;
                }
                $(".sliderui-min input").val(input_left);
                $(".sliderui-max input").val(input_right);
                if (input_left != where_left) {
                    $(".sliderui .polzunok-5").slider("values", 0, input_left);
                }
                if (input_right != where_right) {
                    $(".sliderui .polzunok-5").slider("values", 1, input_right);
                }
            });
            const inputs = filterForm.querySelectorAll('input');
            inputs.forEach((input) => {
                input.addEventListener("change", (e) => {
                    const page = 1
                    const old_page = dart_filters.options.page
                    dart_filters.pageHandler(page, old_page)
                    filterForm.dispatchEvent(new CustomEvent('submit', {cancelable: true}));
                })
            })
            filterForm.addEventListener( 'submit', (e) => {
                e.preventDefault()
                const myFormData = new FormData(e.target)
                const obj = Object.fromEntries(myFormData)
                const inputs = filterForm.querySelectorAll('input[type=checkbox]');
                inputs.forEach((input) => {
                    var attr = input.getAttribute('name')
                    var old_attr = attr
                    delete obj[attr];
                    attr = attr.replace("[]", '')
                    const vars = myFormData.getAll(old_attr)
                    if(vars.length){
                        obj[attr] = myFormData.getAll(old_attr)
                    }
                })
                obj.sl_action = "get/filterdata"
                obj.filter_page = dart_filters.options.page
                // const params = JSON.stringify(obj)
                this.send(obj)
            })
            filterForm.addEventListener("reset", (e) => {
                e.preventDefault()
                const obj = Object.fromEntries(new FormData(e.target))
                obj.sl_action = "get/filterdata"
                dart_filters.pageHandler(1, dart_filters.options.page)
                this.send(obj)
            });
            this.pagesInit()
        }
    },
    parseQuery: function(query){
        var vars = query.split("&");
        var query_string = {};
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            var key = decodeURIComponent(pair.shift());
            var value = decodeURIComponent(pair.join("="));
            // If first entry with this name
            if (typeof query_string[key] === "undefined") {
                query_string[key] = value;
                // If second entry with this name
            } else if (typeof query_string[key] === "string") {
                var arr = [query_string[key], value];
                query_string[key] = arr;
                // If third or later entry with this name
            } else {
                query_string[key].push(value);
            }
        }
        return query_string;
    },
    pagesInit: function(){
        const filterForm = document.getElementById(this.options.form)
        if(filterForm) {
            const pages = document.querySelectorAll(this.options.pagination + " a");
            pages.forEach((page) => {
                page.addEventListener("click", (e) => {
                    e.preventDefault()
                    const page = e.target.dataset.number
                    const old_page = dart_filters.options.page
                    dart_filters.pageHandler(page, old_page)
                })
            })
        }
    },
    pageHandler: function(page, old_page){
        const filterForm = document.getElementById(this.options.form)
        dart_filters.options.page = page
        if(dart_filters.options.page != old_page){
            const params = new URLSearchParams(window.location.search)
            params.delete('page');
            if(dart_filters.options.page != 1){
                params.set('page', dart_filters.options.page);
                window.history.replaceState({ }, "", decodeURIComponent(`${ window.location.pathname}?${ params}`));

            }else{
                window.history.replaceState({ }, "", decodeURIComponent(`${ window.location.pathname}`));
            }
            dart_filters.setMeta(dart_filters.options.page)
            filterForm.dispatchEvent(new CustomEvent('submit', {cancelable: true}));
            const scrollTarget = document.querySelector('.dart_main')
            const topOffset = 0;
            const elementPosition = scrollTarget.getBoundingClientRect().top;
            const offsetPosition = elementPosition - topOffset;
            window.scrollBy({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }else{
            const params = new URLSearchParams(window.location.search)
            params.delete('page');
        }
    },
    setMeta: function(page){
        const head = $("head")
        if(page > 1){
            if(!$("head meta[name=robots]").length){
                var meta = document.createElement('meta');
                meta.name = "robots";
                meta.content = "noindex,follow";
                document.getElementsByTagName('head')[0].appendChild(meta);
            }
            if(!$("head link[rel=canonical]").length) {
                var meta = document.createElement('link');
                meta.rel = "canonical";
                meta.href = window.location.protocol + '//' + window.location.host + window.location.pathname;
                document.getElementsByTagName('head')[0].appendChild(meta);
            }
        }else{
            $("head meta[name=robots]").remove();
            $("head link[rel=canonical]").remove();
        }
    },
    getFilters: function(){
        const data = Array.prototype.reduce.call(
            document.getElementById(this.options.form).elements,
            (acc, n) => {
                const keys = n.name.match(/\w+/g);
                const key = keys.pop();
                keys.reduce((p, c) => p[c] ??= {}, acc)[key] = n.value;
                return acc;
            },
            {}
        );
        console.log(data);
    },
    send: function(data){
        const container = document.querySelector(dart_filters.options.products)
        container.classList.add('loading')
        var response = '';
        $.ajax({
            type: "POST",
            url: shoplogisticConfig['actionUrl'],
            dataType: 'json',
            data: data,
            success:  function(data_r) {
                if(data_r.hasOwnProperty('products')){
                    // $(document).trigger('mse2_load', response);
                    const container = document.querySelector(dart_filters.options.products)
                    container.innerHTML = data_r.products
                    container.classList.add('active')
                }
                if(data_r.hasOwnProperty('aggregate')){
                    const filterForm = document.getElementById(dart_filters.options.form)
                    const labels = filterForm.querySelectorAll('label sup');
                    labels.forEach((item) => {
                        item.innerText = 0
                    })
                    const inputs = filterForm.querySelectorAll('input[type=checkbox]');
                    inputs.forEach((item) => {
                        item.setAttribute('disabled', "")
                    })
                    for (const [key, value] of Object.entries(data_r.aggregate)) {
                        let name = key
                        for (const [k, v] of Object.entries(value)) {
                            if(k == 'min' || k == 'max'){
                                const input = document.querySelector(".polzunok-" + name + "-" + k)
                                input.value = v
                                const hinput = document.querySelector(".hpolzunok-" + name + "-" + k)
                                hinput.value = v
                            }else{
                                const input = document.querySelector(".dart-cheackbox__" + name + "[value='" + k + "']")
                                if(input){
                                    input.removeAttribute('disabled')
                                }
                                const elem = document.querySelector(".dart-cheackbox__" + name + "[value='" + k + "'] + label sup")
                                if(elem){
                                    elem.innerText = v
                                }
                            }
                        }
                    }
                    const form = document
                    let event = new CustomEvent("filter_update", data_r);
                    form.dispatchEvent(event)
                    // console.log(data_r.aggregate)
                }
                if(data_r.hasOwnProperty('pagination')){
                    const pagination = document.querySelector(dart_filters.options.pagination)
                    pagination.innerHTML = data_r.pagination
                    dart_filters.pagesInit()
                }
                const container = document.querySelector(dart_filters.options.products)
                container.classList.remove('loading')

                const blockTextInfo = document.querySelectorAll('.block-text-info');
                if(blockTextInfo){
                    for(let i = 0; i < blockTextInfo.length; i++){
                        if(blockTextInfo[i].scrollHeight >= 333){
                            blockTextInfo[i].classList.add("block-text-info__button");
                        }
                    }
                }

                const blockTextInfoMore = document.querySelectorAll('.block-text-info__more');

                if(blockTextInfoMore){
                    for(let i = 0; i < blockTextInfoMore.length; i++){
                        blockTextInfoMore[i].addEventListener('click', () => {
                            if (blockTextInfoMore[i].parentNode.parentNode.style.maxHeight != '333px') {
                                blockTextInfoMore[i].parentNode.parentNode.style.maxHeight = '333px';
                                blockTextInfoMore[i].innerText = "Читать далее"
                                blockTextInfoMore[i].parentNode.parentNode.classList.remove('show')
                            } else {
                                blockTextInfoMore[i].parentNode.parentNode.style.maxHeight =  blockTextInfoMore[i].parentNode.parentNode.scrollHeight + 60 + "px";
                                blockTextInfoMore[i].innerText = "Скрыть"
                                blockTextInfoMore[i].parentNode.parentNode.classList.add('show')
                            }
                        })
                    }
                }
            }
        });
    },
}


document.addEventListener("DOMContentLoaded", function(event) {
    dart_filters.initialize();
});

var dart_search = {
    options: {
        input: '.dart_header_search-block input',
        inputAlt: '.search-block input',
        form: '.dart-search__form',
        formInput: '.dart-search__form input',
        search: '.dart-search',
        activeClass: 'show',
        overlay: '.dart-search__overlay',
        dialog: '.dart-search__dialog',
        clear: '.dart-search__clear',
        timerId: null
    },
    initialize: function(){
        const searchField = document.querySelector(this.options.input)
        if(searchField) {
            searchField.addEventListener('focusin', (e) => {
                const body = document.querySelector('body')
                const formInput = document.querySelector(this.options.formInput)
                var elementOffset = e.target.getBoundingClientRect().top;
                var availableHeight = window.innerHeight
                const field = document.querySelector(this.options.search)
                field.classList.add(this.options.activeClass)
                if (elementOffset > 0) {
                    // field.style.paddingTop = elementOffset + 'px'
                } else {
                    // field.style.paddingTop = 0
                }
                this.getHeight(availableHeight)
                body.classList.add('noscroll')
                formInput.focus()
            })
            /*
            const searchFieldAlt = document.querySelector(this.options.inputAlt)
            searchFieldAlt.addEventListener('focusin', (e) => {
                const body = document.querySelector('body')
                const formInput = document.querySelector(this.options.formInput)
                var elementOffset = e.target.getBoundingClientRect().top;
                var availableHeight = window.innerHeight
                const field = document.querySelector(this.options.search)
                field.classList.add(this.options.activeClass)
                if (elementOffset > 0) {
                    field.style.paddingTop = elementOffset + 'px'
                } else {
                    field.style.paddingTop = 0
                }
                this.getHeight(availableHeight)
                body.classList.add('noscroll')
                formInput.focus()
            })*/
            const overlay = document.querySelector(this.options.overlay)
            overlay.addEventListener('click', (e) => {
                const body = document.querySelector('body')
                const field = document.querySelector(this.options.search)
                field.classList.remove(this.options.activeClass)
                body.classList.remove('noscroll')
            })
            /*
            const clear = document.querySelector(this.options.clear)
            clear.addEventListener('click', (e) => {
                e.preventDefault();
                const body = document.querySelector('body')
                const searchField = document.querySelector(this.options.formInput)
                searchField.value = ""
                const field = document.querySelector(this.options.input)
                field.value = ""
                const search = document.querySelector(this.options.search)
                search.classList.remove(this.options.activeClass)
                body.classList.remove('noscroll')
            })
            */
            // handle input
            const formInput = document.querySelector(this.options.formInput)
            formInput.addEventListener('input', function (e) {
                const search = document.querySelector('.dart-search__results')
                search.classList.remove(dart_search.options.activeClass)
                if (e.target.value.length > 3) {
                    let inputValue = e.target.value.trim();
                    let lastTime = performance.now();
                    if (dart_search.options.timerId) {
                        clearTimeout(dart_search.options.timerId);
                    }
                    dart_search.options.timerId = setTimeout(function () {
                        var timer = performance.now() - lastTime;
                        if (timer > 1000 && inputValue) {
                            var data = {
                                sl_action: 'search/get_preresults',
                                search: inputValue
                            }
                            dart_search.send(data);
                        }
                    }, 1000);
                }
            })
            window.addEventListener('resize', (e) => {
                var availableHeight = window.innerHeight
                this.getHeight(availableHeight)
            })
        }
    },
    getHeight: function(availableHeight){
        const dialog = document.querySelector(this.options.dialog)
        const height = availableHeight * 0.7
        if(availableHeight < 1000){
            dialog.style.maxHeight = height + 'px'
        }
    },
    send: function(data){
        const form = document.querySelector(dart_search.options.form)
        form.classList.add('loading')
        var response = '';
        $.ajax({
            type: "POST",
            url: shoplogisticConfig['actionUrl'],
            dataType: 'json',
            data: data,
            success:  function(data_r) {
                if(data_r.hasOwnProperty('data')){
                    const container = document.querySelector('.dart-search__results')
                    container.innerHTML = data_r.data.data
                    container.classList.add(dart_search.options.activeClass)
                }
                form.classList.remove('loading')
            }
        });
    },
}


document.addEventListener("DOMContentLoaded", function(event) {
    dart_search.initialize();
});


var sl_delivery = {
    options: {
        wrapper: '.sl_order',
        del_wrap: '.sl_del',
        deliveries: '.sl_deliveries',
        address_field: '.sl_address',
        hidden_address: ".sl_address_block",
        services: ".sl-services",
        service: ".sl-services input",
        map: '.service-pvz-map',
        pvz_map: '.sl_pvz_map',
        pvz: '.sl_pvz',
        choosed_pvz: '.choosed_data',
        yandex: '.yandex_delivery',
        pvz_address_field: '.pvz_address_field',
        placemarks: [],
        terminals: [],
        base_terminals: []
    },
    initialize: function(){
        // handlers event
        $(this.options.wrapper + ' ' + this.options.address_field).suggestions({
            token: shoplogisticConfig['dadata_api_key'],
            type: "ADDRESS",
            /* Вызывается, когда пользователь выбирает одну из подсказок */
            onSelect: sl_delivery.setDeliveryFields
        });
        this.viewAddress();
        $(document).on("change", this.options.wrapper + ' input[type=radio][name=delivery]', function() {
            sl_delivery.viewAddress();
            $(sl_delivery.options.pvz).text('Выберите пункт выдачи');
            $(sl_delivery.options.choosed_pvz).removeClass('active');
            var d = $('input[type=radio][name=delivery]:checked').val();
            var pvz = $('input[type=radio][name=sl_service]:checked').closest(".visual_block").data("pvz");
            if(d == shoplogisticConfig['punkt_delivery'] && pvz){
                $(sl_delivery.options.pvz_map).addClass("active");
            }else{
                $(sl_delivery.options.pvz_map).removeClass("active");
            }
            if(d == shoplogisticConfig['punkt_delivery'] || d == shoplogisticConfig['curier_delivery']){
                var geo_data = $(sl_delivery.options.hidden_address).find("input[name=geo_data]").val();
                if (geo_data){
                    sl_delivery.getDeliveryPrices(geo_data);
                }
            }
        });
        // radio fix in miniShop2 default.js (order.add)
        $(document).on("change", this.options.wrapper + ' input[type=radio][name=sl_service]', function() {
            sl_delivery.updateSlService();
        });

        miniShop2.Callbacks.add('Cart.change.response.success', 'ShopLogisticCartChange', function (response) {
            sl_delivery.update_price();
        })
        miniShop2.Callbacks.add('Cart.remove.response.success', 'ShopLogisticCartRemove', function (response) {
            sl_delivery.update_price();
        })

        // map
        $("body").on("keyup", this.options.pvz_address_field, function (e) {
            clearTimeout(e.target.timer);
            e.target.timer = setTimeout(() => {
                var val = $(this).val();
                var terminals = sl_delivery.options.base_terminals
                if (val.length > 2) {
                    terminals = []
                    // поиск терминалов
                    for (var i = 0; i < sl_delivery.options.base_terminals.length; i++) {
                        const code = sl_delivery.options.base_terminals[i].code.toLowerCase()
                        const address = sl_delivery.options.base_terminals[i].address.toLowerCase()
                        const v = val.toLowerCase()
                        // по адресу
                        if (address.includes(v)) {
                            terminals.push(sl_delivery.options.base_terminals[i]);
                        }
                        // по коду
                        if (code.includes(v)) {
                            terminals.push(sl_delivery.options.base_terminals[i]);
                        }
                    }
                }
                console.log(terminals)
                sl_delivery.setMap(terminals);
            }, 500);
        });
    },
    setDeliveryFields: function(suggestion){
        var address = suggestion.data;
        $(sl_delivery.options.hidden_address).show();
        if(address.fias_id){
            $(sl_delivery.options.hidden_address).find("input[name=fias]").val(address.fias_id);
        }
        if(address.city_fias_id){
            $(sl_delivery.options.hidden_address).find("input[name=fias]").val(address.city_fias_id);
        }
        //$(sl_delivery.options.hidden_address).find("input[name=fias]").val(address.city_fias_id);
        if(address.area_kladr_id){
            $(sl_delivery.options.hidden_address).find("input[name=kladr]").val(address.area_kladr_id);
        }
        if(address.city_kladr_id){
            $(sl_delivery.options.hidden_address).find("input[name=kladr]").val(address.city_kladr_id);
        }
        $(sl_delivery.options.hidden_address).find("input[name=kladr]").val(address.city_kladr_id);
        $(sl_delivery.options.hidden_address).find("input[name=geo]").val(sl_delivery.join([
            address.geo_lat, ",", address.geo_lon], ""));
        $(sl_delivery.options.hidden_address).find("input[name=index]").val(address.postal_code);
        $(sl_delivery.options.hidden_address).find("input[name=region]").val(sl_delivery.join([
            sl_delivery.join([address.region_type, address.region], " "),
            sl_delivery.join([address.area_type, address.area], " ")
        ]));
        $(sl_delivery.options.hidden_address).find("input[name=city]").val(sl_delivery.join([
            sl_delivery.join([address.city_type, address.city], " "),
            sl_delivery.join([address.settlement_type, address.settlement], " ")
        ]));
        $(sl_delivery.options.hidden_address).find("input[name=street]").val(sl_delivery.join([address.street_type, address.street], " "));
        $(sl_delivery.options.hidden_address).find("input[name=building]").val(sl_delivery.join([
            sl_delivery.join([address.house_type, address.house], " "),
            sl_delivery.join([address.block_type, address.block], " ")
        ]));
        $(sl_delivery.options.hidden_address).find("input[name=room]").val(sl_delivery.join([address.flat_type, address.flat], " "));
        var fias = $(sl_delivery.options.hidden_address).find("input[name=fias]").val();
        $(sl_delivery.options.hidden_address).closest(".dart-order__express").find(".text_address").text(address.text_address);
        $(sl_delivery.options.hidden_address).find("input[name=text_address]").val(address.text_address);
        $(sl_delivery.options.hidden_address).find("input[name=geo_data]").val(JSON.stringify(address));
        $(sl_delivery.options.hidden_address).find("input[name=room]").val(address.room);
        $(sl_delivery.options.hidden_address).find("input[name=floor]").val(address.floor);
        $(sl_delivery.options.hidden_address).find("input[name=entrance]").val(address.entrance);
        $(sl_delivery.options.hidden_address).find("input[name=doorphone]").val(address.doorphone);
        if(address){
            sl_delivery.getDeliveryPrices(JSON.stringify(address));
        }
    },
    update_price: function(){
		miniShop2.Order.getcost();
        var geo_data = $(sl_delivery.options.hidden_address).find("input[name=geo_data]").val();
        if (geo_data){
            sl_delivery.getDeliveryPrices(geo_data);
        }
    },
    viewAddress: function(){
        var d = $('input[type=radio][name=delivery]:checked').val();
        if (d == shoplogisticConfig['default_delivery']) {
            $(sl_delivery.options.deliveries).hide();
            $(sl_delivery.options.del_wrap).hide();
            $(sl_delivery.options.yandex).find('input').attr("disabled", "disabled");
            $(sl_delivery.options.map).removeClass("active");
            $(sl_delivery.options.services).removeClass('active');
        }
        if(d == shoplogisticConfig['punkt_delivery']){
            $(sl_delivery.options.deliveries).show();
            $(sl_delivery.options.del_wrap).show();
            $(sl_delivery.options.yandex).find('input').attr("disabled", "disabled");
            $(sl_delivery.options.map).removeClass("active");
            //$(sl_delivery.options.services).show();
        }
        if(d == shoplogisticConfig['curier_delivery']){
            $(sl_delivery.options.deliveries).show();
            $(sl_delivery.options.del_wrap).show();
            $(sl_delivery.options.yandex).find('input').removeAttr("disabled");
            $(sl_delivery.options.map).removeClass("active");
            //$(sl_delivery.options.services).show();
        }
        if($('input[type=radio][name=sl_service]:checked').length){
            sl_delivery.updateSlService();
        }
        // TODO: check this block
        /*
        if(d == shoplogisticConfig['post_delivery']){
            $(sl_delivery.options.deliveries).show();
            $(sl_delivery.options.del_wrap).show();
            $(sl_delivery.options.yandex).find('input').attr("disabled", "disabled");
            $(sl_delivery.options.map).removeClass("active");
            $(sl_delivery.options.services).removeClass('active');
        }
        */
    },
    updateSlService: function(){
        var main_data = $('input[type=radio][name=sl_service]:checked').data('data');
        sl_delivery.setData(JSON.parse(main_data));
        var pvz = $('input[type=radio][name=sl_service]:checked').closest(".visual_block").data("pvz");
        var d = $('input[type=radio][name=delivery]:checked').val();
        $(sl_delivery.options.pvz).text('Выберите пункт выдачи');
        $(sl_delivery.options.choosed_pvz).removeClass('active');
        if(d == shoplogisticConfig['punkt_delivery'] && pvz){
            $(sl_delivery.options.pvz_map).addClass("active");
        }else{
            $(sl_delivery.options.pvz_map).removeClass("active");
        }
    },
    getDeliveryPrices: function(address){
        if(address){
            $(sl_delivery.options.services).addClass("active");
            $(sl_delivery.options.service).each(function(){
                var service = $(this).val();
                if(service){
                    sl_delivery.getDeliveryPrice(address, service);
                }
            })
        }
    },
    getDeliveryPrice: async function(address, service){
		$('.'+service+'_price').text('');
        $('.'+service+'_srok').text('');
		$('.'+service+'_price').closest('.service_info_'+service).hide();
		$('.'+service+'_srok').closest('.visual_block').addClass('loading');
        var data = {
            sl_action: 'delivery/get_price',
            address: address,
            service: service
        }
        this.send(data);
    },
    setData: function(data){
        console.log(data)
        $(sl_delivery.options.services).find('.service_info_'+data.main_key).hide();
        var d = $('input[type=radio][name=delivery]:checked').val();
        if(d == shoplogisticConfig['punkt_delivery']){
            var prop = 'terminal';
        }else{
            var prop = 'door';
        }
        data.method = prop;
        if(data[data.main_key].price){
            if(data[data.main_key].price.hasOwnProperty(prop) && data[data.main_key].price[prop].hasOwnProperty('price')){
                if(data[data.main_key].price[prop].price > 0){
                    var price = data[data.main_key].price[prop].price;
                    var srok = data[data.main_key].price[prop].time;
                    $('.'+data.main_key+'_price').text(price);
                    $('.'+data.main_key+'_srok').text(srok);
                    $('.'+data.main_key+'_price').closest('.service_info_'+data.main_key).show();
                    $('input#service_'+data.main_key).removeAttr("disabled");
                    $('.'+data.main_key+'_price').closest('.visual_block').removeClass('loading');
                    $('input#service_'+data.main_key).closest(".visual_block").show();
                }else{
                    $('input#service_'+data.main_key).closest(".visual_block").hide();
                }
            }else{
                $('input#service_'+data.main_key).attr("disabled", "disabled");
                $('input#service_'+data.main_key).removeAttr("checked");
                $('input#service_'+data.main_key).prop('checked', false);
				$('.'+data.main_key+'_price').closest('.visual_block').removeClass('loading');
                $('input#service_'+data.main_key).closest(".visual_block").hide();
            }
        }else{
            $('input#service_'+data.main_key).attr("disabled", "disabled");
            $('input#service_'+data.main_key).removeAttr("checked");
            $('input#service_'+data.main_key).prop('checked', false);
            $('input#service_'+data.main_key).closest(".visual_block").hide();
			$('.'+data.main_key+'_price').closest('.visual_block').removeClass('loading');
        }
        $("input[name=sl_service][value="+data.main_key+"]").data("data", JSON.stringify(data));
        if($("input[name=sl_service][value="+data.main_key+"]").prop("checked")){
            // set delivery price
            var save_data = {};
            var main_data = data;
            if (typeof main_data === 'object'){
                save_data.service = main_data;
            }else{
                save_data.service = JSON.parse(main_data);
            }
            var send_data = JSON.stringify(save_data);
            $(sl_delivery.options.wrapper).find('.delivery_data').val(send_data);
            var data = {
                sl_action: 'delivery/add_order',
                data: send_data
            }
            setTimeout(function() {
                sl_delivery.send(data);
            }, 100);
			
            if(prop == 'terminal' && save_data.service[save_data.service.main_key].price.hasOwnProperty(prop) && save_data.service.main_key != "postrf"){
                sl_delivery.options.base_terminals = save_data.service[save_data.service.main_key].price.terminals;
                $(sl_delivery.options.map).addClass("active");
                sl_delivery.setMap(save_data.service[save_data.service.main_key].price.terminals);
            }else{
                if(this.map){
                    this.map.destroy();
                }
                $(sl_delivery.options.map).removeClass("active");
            }
        }
    },
    pvzclick: function(code){
        if(code){
            const placemarks = sl_delivery.options.placemarks
            placemarks.forEach((element, index) => {
                if(element.properties._data.pvz_code == code){
                    sl_delivery.map.setCenter(sl_delivery.options.placemarks[index].geometry.getBounds()[0], 15, {
                        checkZoomRange: true, //контролируем доступность масштаба
                        callback: function() { //спозиционировались
                            sl_delivery.options.placemarks[index].events.add('mapchange', function(e){
                                //точка появилась
                                if( e.get('newMap') != null) {
                                    //точка загрузилась
                                    setTimeout(function() {
                                        //задержка
                                        // sl_delivery.options.placemarks[index].balloon.open();//открытие балуна
                                    }, 300);
                                } });
                        }
                    });
                    // sl_delivery.options.placemarks[index].balloon.open();
                }
            })

            // sl_delivery.options.placemarks[code].balloon.open();
            const terminals = sl_delivery.options.terminals
            terminals.forEach((element) => {
                if(element.code == code){
                    var btn = $(".changeAddresPoint__content .changeshop__button .sl_check");
                    btn.attr("data-code", code);
                }
            });
        }
    },
    pvzcheck: function(elem){
        var code = elem.dataset.code;
        var save_data = {};
        var data = {}
        const terminals = sl_delivery.options.terminals
        terminals.forEach((element) => {
            if(element.code == code){
                data = element
            }
        });
        // var data = sl_delivery.options.terminals[code];
        var d = $('input[type=radio][name=sl_service]:checked').val();
        var main_data = $('input[type=radio][name=sl_service]:checked').data('data');
        if (typeof data === 'object'){
            save_data.pvz = data;
        }else{
            save_data.pvz = JSON.parse(data);
        }
        if (typeof main_data === 'object'){
            save_data.service = main_data;
        }else{
            save_data.service = JSON.parse(main_data);
        }
        var send_data = JSON.stringify(save_data);
        $(sl_delivery.options.wrapper).find('.delivery_data').val(send_data);
        var data = {
            sl_action: 'delivery/add_order',
            data: send_data
        }
        sl_delivery.send(data);
        sl_delivery.map.balloon.close();

        $(sl_delivery.options.pvz).text(save_data.pvz.code + ' || ' +  save_data.pvz.address);
        $(sl_delivery.options.choosed_pvz).addClass('active');
        $(".changeAddresPoint").removeClass("show");
    },
    initMap: function(center, terminals){
        if(sl_delivery.map){
            sl_delivery.map.destroy();
        }
        sl_delivery.options.placemarks = []
        sl_delivery.options.terminals = []
        sl_delivery.map = new ymaps.Map('service-pvz-map', {
            center: center,
            zoom: 9
        }, {
            searchControlProvider: 'yandex#search'
        });
        var element_text = '';
        terminals.forEach((element, index, array) => {
            var coords = [element['lat'], element['lon']];
            var data = JSON.stringify(element);
            sl_delivery.options.terminals.push(element);
            element_text += '<div class="changeshop__el" onclick="sl_delivery.pvzclick(\''+ element['code'] +'\')" data-code="'+ element['code'] +'" data-info=\''+data+'\'>\n' +
                '                    <div class="changeshop__info">\n' +
                '                        <h4>Пункт выдачи заказов</h4>\n' +
                '                        <p class="mt-3 mb-1">\n' +
                '                            '+element['address'] +
                '                        </p>\n';
            if(element['workTime']) {
                element_text += '                        <p class="shop-map__timing mt-3">\n' +
                '                            ' + element['workTime'] +
                '                        </p>\n';
            }
            element_text += '                    </div>\n' +
                '                    <div>\n' +
                '                        <img class="shop-map__icon" src="'+element['image']+'" alt="">\n' +
                '                    </div>\n' +
                '                </div>';
            $(".changeAddresPoint__content .changeshop__shops").html(element_text);
            var text = '<div class="sl_baloon_header"><img src="'+element['image']+'" width="10"/>'+element['address']+'</div>';
            if(element['phones']){
                text = text+'<div class="sl_baloon_phones sl_baloon_block"><b>Телефоны:</b><br/>'+element['phones']+'</div>';
            }
            if(element['workTime']){
                text = text+'<div class="sl_baloon_works sl_baloon_block"><b>Время работы:</b><br/>'+element['workTime']+'</div>';
            }
            text = text+'<div class="sl_baloon_submit sl_baloon_block"><button type="button" class="sl_check" onclick="sl_delivery.pvzcheck(this)" data-code="'+ element['code'] +'">Забрать отсюда</button></div>';
            sl_delivery.options.placemarks.push(new ymaps.Placemark(coords, {
                hintContent: element['address'],
                balloonContent: text,
                pvz_code: element['code']
            }, {
                iconLayout: 'default#image',
                iconImageHref: element['map_image'],
                iconImageSize: [60, 60],
                iconImageOffset: [-30, -60]
            }));
            // this.map.geoObjects.add(sl_delivery.options.placemarks[element['code']]);
        });
        var clusterer = new ymaps.Clusterer({ });
        clusterer.add(sl_delivery.options.placemarks);
        sl_delivery.map.geoObjects.add(clusterer);
        sl_delivery.map.setBounds(clusterer.getBounds(), {
            checkZoomRange: true
        });
    },
    setMap: function(terminals){
        var geo = $(sl_delivery.options.deliveries).find("input[name=geo]").val();
        if(geo){
            var g = geo.split(',');
            this.initMap(g, terminals);
            $(sl_delivery.options.map).addClass('active');
        }
    },
    send: function(data){
        var response = '';
        $.ajax({
            type: "POST",
            url: shoplogisticConfig['actionUrl'],
            dataType: 'json',
            data: data,
            success:  function(data_r) {
                console.log(data_r);
                if(data_r.main_key){
                    sl_delivery.setData(data_r);
                }else{
                    if(data_r.hasOwnProperty('data')){
                        if(data_r.data.hasOwnProperty('re_calc')){
                            if(data_r.data.re_calc){
                                miniShop2.Order.getcost();
                            }
                        }
                    }
                }
            }
        });
    },
    join: function (arr /*, separator */) {
        var separator = arguments.length > 1 ? arguments[1] : ", ";
        return arr.filter(function(n){return n}).join(separator);
    }
}

var sl_marketplace = {
    options: {
        wrapper: '.sl_wrap',
        live_form: '.sl_live_form',
        form: '.sl_form',
        generate_api: '.regerate_apikey',
        profile_product: '.profile-products__item-wrap',
        alert_change_btn: '.alert_change_btn'
    },
    initialize: function () {
        if($('.delivery_info_block').length){
            $('.delivery_info_block').each(function(){
                var action = 'get/delivery';
                var data = {
                    sl_action: action,
                    id: $(this).data('id'),
                    type: $(this).data('type'),
                    from_id: $(this).data('from_id')
                };
                sl_marketplace.send(data);
            })
        }
        if($('.delivery_data').length){
            var action = 'get/delivery';
            var data = {
                sl_action: action,
                id: $('.delivery_data').data('id')
            };
            sl_marketplace.send(data);
        }
        $(document).on("click", sl_marketplace.options.generate_api, function(e) {
            e.preventDefault();
            var type = $(this).data('type');
            var id = $(this).data('id');
            var action = 'apikey/generate';
            var str = shoplogisticConfig['regexp_gen_code'];
            var gen = sl_marketplace.genRegExpString(str);
            var data = {
                sl_action: action,
                type: type,
                id: id,
                apikey: gen
            };
            sl_marketplace.send(data);
        });
        $(document).on('change', '.change_status select', function(e){
            $(this).closest(sl_marketplace.options.live_form).trigger('submit');
        });
        $(document).on("click", sl_marketplace.options.alert_change_btn, function(e) {
            e.preventDefault();
            var form = $(this).closest('form');
            form.find('input').each(function(i){
                var name = $(this).attr("name");
                var val = $(this).val();
                if(name != 'sl_action'){
                    $("#change_profile_data").find("input[name="+name+"]").val(val);
                }
            });
        });
        $(document).on('submit', sl_marketplace.options.live_form, function(e){
            e.preventDefault();
            var data = $(this).serialize();
            sl_marketplace.send(data);
        });
        $(document).on('submit', sl_marketplace.options.form, function(e){
            e.preventDefault();
            $(this).find('.message').html("");
            var data = $(this).serialize();
            sl_marketplace.send(data);
        });
        $(sl_marketplace.options.live_form).on('keyup input', 'input[type=text]', function(e){
            if($(this).val().length >= 3 || $(this).val().length == 0){
                const url = new URL(document.location);
                const searchParams = url.searchParams;
                searchParams.delete("page");
                window.history.pushState({}, '', url.toString());
                pdoPage.keys['page'] = 1;
                $(this).closest(sl_marketplace.options.live_form).trigger('submit');
            }
        });
        $(sl_marketplace.options.live_form).on('change', 'input[type=checkbox]', function(e){
            const url = new URL(document.location);
            const searchParams = url.searchParams;
            searchParams.delete("page");
            window.history.pushState({}, '', url.toString());
            pdoPage.keys['page'] = 1;
            $(this).closest(sl_marketplace.options.live_form).trigger('submit');
        });
        $(document).on("click", sl_marketplace.options.profile_product, function(e) {
            e.preventDefault();
            var product_id = $(this).closest('.profile-products__item').data('id');
            var product_name = $(this).closest('.profile-products__item').data('name');
            var type = $(this).closest('.profile-products__item').data('type');
            var col_id = $(this).closest('.profile-products__item').data('col_id');
            var remains = $(this).closest('.profile-products__item').data('remains');
            var price = $(this).closest('.profile-products__item').data('price');
            var description = $(this).closest('.profile-products__item').data('description');
            $('#remain input[name="product_id"]').val(product_id);
            $('#remain input[name="product_name"]').val(product_name);
            $('#remain input[name="type"]').val(type);
            $('#remain input[name="col_id"]').val(col_id);
            $('#remain input[name="remains"]').val(remains);
            $('#remain input[name="price"]').val(price.replace(/\s+/g, ''));
            $('#remain textarea[name="description"]').val(description);
            var remainModal = new bootstrap.Modal(document.getElementById('remain'));
            remainModal.show();
        });
        // CALENDAR
        $(document).on( "click", ".calendar_navigation", function(e){
            e.preventDefault();
            var month = $(this).data("month");
            var year = $(this).data("year");
            var action = 'calendar/get';
            var warehouse_id = $(this).closest(".calendar").data("warehouse_id");
            var data = {
                sl_action: action,
                col_id: warehouse_id,
                month: month,
                year: year
            };
            sl_marketplace.send(data);
        });
    },
    genRegExpString: function (str) {
        var str_new = str;

        var words = {};
        words['0-9'] = '0123456789';
        words['a-z'] = 'qwertyuiopasdfghjklzxcvbnm';
        words['A-Z'] = 'QWERTYUIOPASDFGHJKLZXCVBNM';

        var match = /\/((\(?\[[^\]]+\](\{[0-9-]+\})*?\)?[^\[\(]*?)+)\//.exec(str);
        if (match != null) {
            str_new = match[1].replace(/\(?(\[[^\]]+\])(\{[0-9-]+\})\)?/g, regexpReplace1);
            str_new = str.replace(match[0], str_new);
        }

        return str_new;

        function rand(min, max) {
            if (max) {
                return Math.floor(Math.random() * (max - min + 1)) + min;
            }
            else {
                return Math.floor(Math.random() * (min + 1));
            }
        }

        function regexpReplace1(match, symbs, count) {
            symbs = (symbs + count).replace(/\[([0-9a-zA-Z-]+)\]\{([0-9]+-[0-9]+|[0-9]+)\}/g, regexpReplace2);

            return symbs;
        }

        function regexpReplace2(match, symbs, count) {
            var r = match;
            var arr_symbs = symbs.match(/[0-9a-zA-Z]-[0-9a-zA-Z]/g);

            if (arr_symbs.length > 0) {
                var maxcount = 1;

                if (typeof count != 'undefined') {
                    nums = count.split('-');

                    if (typeof nums[1] == 'undefined') {
                        maxcount = +nums[0];
                    }
                    else {
                        min = +nums[0];
                        max = +nums[1];

                        maxcount = rand(min, max);
                        maxcount = maxcount < min ? min : maxcount;
                    }
                }

                for (var i = 0; i < arr_symbs.length; i++) {
                    symbs = symbs.replace(arr_symbs[i], words[arr_symbs[i]]);
                }

                var maxpos = symbs.length - 1,
                    pos,
                    r = '';

                for (var i = 0; i < maxcount; i++) {
                    pos = Math.floor(Math.random() * maxpos);
                    r += symbs[pos];
                }
            }

            return r;
        }
    },
    send: function(data){
        var response = '';
        $.ajax({
            type: "POST",
            url: shoplogisticConfig['actionUrl'],
            dataType: 'json',
            data: data,
            success:  function(data_r) {
                if(data_r.data.hasOwnProperty('html_delivery')){
                    if(data_r.data.html_delivery){
                        if(data_r.data.hasOwnProperty('selector_id')) {
                            $(data_r.data.selector_id).html(data_r.data.html_delivery);
                        }
                        if(data_r.data.hasOwnProperty('selector_modal_id')) {
                            $(data_r.data.selector_modal_id).html(data_r.data.html_delivery);
                        }
                    }
                }
                if(typeof data_r.data.reload !== "undefined"){
                    if(data_r.data.reload) {
                        document.location.reload();
                    }
                }
                if(typeof data_r.data.showSuccessModal !== "undefined"){
                    if(data_r.data.showSuccessModal) {
                        var successModal = new bootstrap.Modal(document.getElementById('success_modal'));
                        $("#success_modal").find(".modal_success .text .title").text(data_r.data.ms2_response);
                        $(".modal.show .btn-close").trigger("click");
                        successModal.show();
                    }
                }
                if(typeof data_r.data.apikey !== "undefined"){
                    if(data_r.data.apikey) {
                        $("#apikey_" + data_r.data.type + "_" + data_r.data.id).val(data_r.data.apikey);
                    }
                }
                if(typeof data_r.data.cityclose !== "undefined"){
                    if(data_r.data.cityclose) {
                        $(".city_popup").removeClass('active');
                    }
                }
                if(typeof data_r.data.calendar !== "undefined"){
                    if(data_r.data.calendar){
                        $("#calendar").html(data_r.data.html);
                    }
                }
                if(typeof data_r.data.type !== "undefined"){
                    if(data_r.data.type && data_r.data.action != 'sw/alert_change'){
                        var form = $(".form_edit_"+data_r.data.type+"_"+data_r.data.id);
                        for (key in data_r.data) {
                            form.find('input[name='+key+']').val(data_r.data[key]);
                        }
                        form.find('.message').html(data_r.message);
                        $('html, body').animate({
                            scrollTop: form.offset().top
                        }, 500);
                        setTimeout(function(){
                            form.find('.message').hide("slow", function() {
                                form.find('.message').html();
                            }).html('');
                        }, 2000);
                    }
                }
                if(typeof data_r.data.remains !== "undefined"){
                    if(data_r.data.remains){
                        var form = $("#remain form");
                        form.find('.message').html(data_r.message);
                        setTimeout(function(){
                            form.find('.message').hide("slow", function() {
                                form.find('.message').html();
                            }).html('');
                        }, 2000);
                        $(sl_marketplace.options.live_form).trigger("submit");
                    }
                }
                if(data_r.data.action == 'sw/alert_change'){
                    var form = $("#change_profile_data form");
                    form.find('.message').html(data_r.message);
                    $('.modal').animate({
                        scrollTop: 0
                    }, 500);
                    setTimeout(function(){
                        form.find('.message').hide("slow", function() {
                            form.find('.message').html();
                        }).html('');
                    }, 2000);
                }
                if(typeof data_r.topdo !== "undefined"){
                    if(data_r.topdo){
                        $('#pdopage .rows').html(data_r.data);
                        $('#pdopage .pagination').html(data_r.pagination);
                        $('#pdopage span.total').html(data_r.total);
                    }
                }
            }
        });
    },
}

$(document).ready(function(){
    if($(sl_delivery.options.wrapper).length){
        sl_delivery.initialize();
    }
    sl_marketplace.initialize();
    profile.initialize();
    // ms2 pseudo submit
    $(document).on("click", ".pseudo_submit", function(e) {
        e.preventDefault();
		// check validation
		$('.error-desc').remove();
        $('.sl_services .dart-alert-error').remove();
        $('.sl_pvz_map .dart-alert-error').remove();
		$('.dart-input input').removeClass('error');
		var errors = {};
		var d = $('input[type=radio][name=delivery]:checked').val();
		var required = ['receiver','email','phone'];
		required.forEach((element) => {
			var val = $('#msOrder #order_'+element).val();
			if(val == ''){
				errors[element] = 'Заполните это поле.';
			}else{
				if(element == 'email'){
					var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,8})$/;
					if(reg.test(val) == false) {
						errors[element] = 'Напишите корректный email.';
					}
				}
				if(element == 'phone'){
					var reg = /^[\d\+][\d\(\)\ -]{4,14}\d$/;
					if(reg.test(val) == false) {
						errors[element] = 'Напишите корректный телефон.';
					}
				}
			}
		});
		if(d == shoplogisticConfig['curier_delivery']){
			var required = ['address', 'index','region','city','street','building'];
			required.forEach((element) => {
				var val = $('#msOrder #order_'+element).val();
				if(val == ''){
					errors['address'] = 'Укажите адрес полностью, включая квартиру, если это необходимо.';
				}
			});		
			// отдельно проверяем 'sl_service'
			var service = $('input[type=radio][name=sl_service]:checked').val();
			if(!service){
				errors['sl-services'] = 'Укажите транспортную компанию для доставки.';
			}
		}
		if(d == shoplogisticConfig['punkt_delivery']){
			var required = ['address', 'index','region','city','street','building'];
			required.forEach((element) => {
				var val = $('#msOrder #'+element).val();
				if(val == ''){
					errors['address'] = 'Укажите адрес включая дом для более точного расчета стоимости доставки.';
				}
			});		
			// отдельно проверяем 'sl_service'
			var service = $('input[type=radio][name=sl_service]:checked').val();
			if(!service){
				errors['sl-services'] = 'Укажите транспортную компанию для доставки.';
			}
			var pvz = $('.sl_pvz').text();
			if(pvz == 'Выберите пункт выдачи' && service != 'postrf'){
				errors['sl_pvz_map'] = 'Выберите удобный пункт выдачи заказов на карте.';
			}
		}
		if(Object.keys(errors).length){
			for (const [key, value] of Object.entries(errors)) {
				var error_string = "<div class='dart-alert dart-alert-error'>"+value+"</div>";
				if(key == 'sl-services' || key == 'sl_pvz_map'){
					$('.'+key).prepend(error_string);
				}else{
					var error_string = "<span class='error-desc'>"+value+"</span>";
                    $('#order_'+key).addClass('error');
					$('#order_'+key).closest('.dart-input').append(error_string);
				}
			}
            $('.summary-block__title').find('.alert').remove();
			$('.summary-block__title').append("<div class='alert alert-danger'>Проверьте форму на наличие ошибок.</div>")
		}else{
			// $('body').addClass("sl_noscroll");
			$('.dart-order__content').addClass('loading');
            if (!$(this).attr("disabled")) {
                $(this).attr("disabled", "disabled");
                $("#msOrder .ms2_link").trigger("click");
            }
		}
    });
})