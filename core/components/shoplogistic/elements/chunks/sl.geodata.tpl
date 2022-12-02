<div class="geo_data">
    <div class="sl-item city_check">
        <a href="#" data-bs-toggle="modal" data-bs-target="#modal_city">
            <span class="sl_geo_city">{$_modx->getPlaceholder("sl.city")? : 'Город не выбран'}</span>
        </a>
        <div class="city_popup">
            <span>Вы находитесь в <b class="sl_geo_city">{$_modx->getPlaceholder("sl.city")}</b>?</span>
            <div class="buttons">
                <a href="#" class="sl-btn sl-btn-outline-secondary sl_city_more_info">Нет, другой</a>
                <a href="#" class="sl-btn sl-btn-primary sl_city_close city_checked">Да, верно</a>
            </div>
        </div>
    </div>
    <div class="sl-item sl-item-store">
        <a href="#" data-bs-toggle="modal" data-bs-target="#modal_store">
            <i class="sl_icon sl_icon-map-marker"></i>
            <span class="sl_geo_store">{$_modx->getPlaceholder("sl.store")}</span>
        </a>
    </div>
</div>