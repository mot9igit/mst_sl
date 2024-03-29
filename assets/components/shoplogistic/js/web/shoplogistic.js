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
                var csortdir = parent.data("sortdir");
                var csortby = parent.data("sortby");
                var text = $(this).text();

                parent.find(".modalSortToggle span").text(text);
                if(sortby != csortby){
                    parent.removeClass("active active_" + csortdir);
                }
                if(parent.hasClass("active")){
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
                    dart_filters.options.page = 1
                    filterForm.dispatchEvent(new CustomEvent('submit', {cancelable: true}));
                })
            })
            filterForm.addEventListener( 'submit', (e) => {
                e.preventDefault()
                const obj = Object.fromEntries(new FormData(e.target))
                obj.sl_action = "get/filterdata"
                obj.filter_page = dart_filters.options.page
                // const params = JSON.stringify(obj)
                this.send(obj)
            })
            filterForm.addEventListener("reset", (e) => {
                e.preventDefault()
                const obj = Object.fromEntries(new FormData(e.target))
                obj.sl_action = "get/filterdata"
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
                    dart_filters.options.page = page
                    if(dart_filters.options.page != old_page){
                        const params = new URLSearchParams(window.location.search)
                        params.delete('page');
                        params.set('page', dart_filters.options.page);
                        window.history.replaceState({ }, "", decodeURIComponent(`${ window.location.pathname}?${ params}`));
                        filterForm.dispatchEvent(new CustomEvent('submit', {cancelable: true}));
                    }else{
                        const params = new URLSearchParams(window.location.search)
                        params.delete('page');
                    }
                })
            })
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
                let event = new Event("filter_update", data_r);
                if(data_r.hasOwnProperty('products')){
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
                    const form = document.getElementById(dart_filters.options.form)
                    form.dispatchEvent(event)
                    console.log(data_r.aggregate)
                }
                if(data_r.hasOwnProperty('pagination')){
                    const pagination = document.querySelector(dart_filters.options.pagination)
                    pagination.innerHTML = data_r.pagination
                    dart_filters.pagesInit()
                }
                const container = document.querySelector(dart_filters.options.products)
                container.classList.remove('loading')
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
        placemarks: {},
        terminals: {}
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
        $(this.options.wrapper + ' input[type=radio][name=delivery]').change(function() {
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
        $(this.options.wrapper + ' input[type=radio][name=sl_service]').change(function() {
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
        });

        miniShop2.Callbacks.add('Cart.change.response.success', 'ShopLogisticCartChange', function (response) {
            sl_delivery.update_price();
        })
        miniShop2.Callbacks.add('Cart.remove.response.success', 'ShopLogisticCartRemove', function (response) {
            sl_delivery.update_price();
        })

        // map
        /*$("body").on("keyup", this.options.address_field, function (e) {
            var val = $(this).val();
            if(val.length > 2){
                var data = {
                    sl_action: "get/suggestion",
                    value: val,
                    ctx: shoplogisticConfig['ctx']
                }
                sl_delivery.send(data);
            }
        });*/
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
        $(sl_delivery.options.hidden_address).find("input[name=geo_data]").val(JSON.stringify(address));
        if(address){
            sl_delivery.getDeliveryPrices(JSON.stringify(address));
        }
    },
    update_price: function(){
		miniShop2.Order.getcost();
        var fias = $(sl_delivery.options.hidden_address).find("input[name=fias]").val();
        if(fias){
            sl_delivery.getDeliveryPrices(fias);
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
                var price = data[data.main_key].price[prop].price;
                var srok = data[data.main_key].price[prop].time;
                $('.'+data.main_key+'_price').text(price);
                $('.'+data.main_key+'_srok').text(srok);
                $('.'+data.main_key+'_price').closest('.service_info_'+data.main_key).show();
                $('input#service_'+data.main_key).removeAttr("disabled");
				$('.'+data.main_key+'_price').closest('.visual_block').removeClass('loading');
            }else{
                $('input#service_'+data.main_key).attr("disabled", "disabled");
                $('input#service_'+data.main_key).removeAttr("checked");
                $('input#service_'+data.main_key).prop('checked', false);
				$('.'+data.main_key+'_price').closest('.visual_block').removeClass('loading');
            }
        }else{
            $('input#service_'+data.main_key).attr("disabled", "disabled");
            $('input#service_'+data.main_key).removeAttr("checked");
            $('input#service_'+data.main_key).prop('checked', false);
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
            $(sl_delivery.options.wrapper).find('.delivery_data').val(JSON.stringify(send_data));
            var data = {
                sl_action: 'delivery/add_order',
                data: send_data
            }
            setTimeout(function() {
                sl_delivery.send(data);
            }, 100);
			
            if(prop == 'terminal' && save_data.service[save_data.service.main_key].price.hasOwnProperty(prop) && save_data.service.main_key != "postrf"){
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
            sl_delivery.options.placemarks[code].balloon.open();
            if(sl_delivery.options.terminals[code]){
                var btn = $(".changeAddresPoint__content .changeshop__button .sl_check");
                btn.attr("data-code", code);
            }
        }
    },
    pvzcheck: function(elem){
        var code = elem.dataset.code;
        var save_data = {};
        var data = sl_delivery.options.terminals[code];
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
        $(sl_delivery.options.wrapper).find('.delivery_data').val(JSON.stringify(send_data));
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
        if(this.map){
            this.map.destroy();
        }
        this.map = new ymaps.Map('service-pvz-map', {
            center: center,
            zoom: 9
        }, {
            searchControlProvider: 'yandex#search'
        });
        var element_text = '';
        terminals.forEach((element, index, array) => {
            var coords = [element['lat'], element['lon']];
            var data = JSON.stringify(element);
            sl_delivery.options.terminals[element['code']] = element
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
            sl_delivery.options.placemarks[element['code']] = new ymaps.Placemark(coords, {
                hintContent: element['address'],
                balloonContent: text
            }, {
                iconLayout: 'default#image',
                iconImageHref: element['image'],
                iconImageSize: [20, 20],
                iconImageOffset: [-10, -10]
            });
            this.map.geoObjects.add(sl_delivery.options.placemarks[element['code']]);
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
    // ms2 pseudo submit
    $(".pseudo_submit").click(function(e) {
        e.preventDefault();
		// check validation
		$('.error-desc').remove();
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
				var error_string = "<div class='sl-alert sl-alert-error'>"+value+"</div>";
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
			$('.dart-order__container').addClass('loading');
			if(!$(this).attr("disabled")){
				$(this).attr("disabled", "disabled");
				$("#msOrder .ms2_link").trigger("click");
			}
		}
    });
})