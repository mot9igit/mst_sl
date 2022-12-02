{if $services}
    <div class="sl-services">
        <div class="checkers form-group sl-row">
            <div class="d-col yandex_delivery">
                <div class="checkbox">
                    <label class="col-form-label sl-service">
                        <input type="radio" name="sl_service" value="yandex" id="service_yandex">
                        <div class="visual_block">
                            <div class="preloader-inblock">
                                <div class="loader"></div>
                            </div>
                            <div class="visual_block_inner">
                                <div class="visual_block_inner-image">
                                    <img src="img/yandex-delivery.png" alt="Яндекс.Доставка">
                                </div>
                                <div class="visual_block_inner-text service_info_yandex">
                                    <div class="price"><b class="yandex_price"></b> руб.</div>
                                    <div class="srok"><b class="yandex_srok"></b></div>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
            {foreach $services as $key => $service index=$index}
                <div class="d-col">
                    <div class="checkbox">
                        <label class="col-form-label sl-service">
                            <input type="radio" name="sl_service" value="{$key}" id="service_{$key}">
                            <div class="visual_block">
                                <div class="preloader-inblock">
                                    <div class="loader"></div>
                                </div>
                                <div class="visual_block_inner">
                                    <div class="visual_block_inner-image">
                                        <img src="{$service['logo']}" alt="{$service['name']}">
                                    </div>
                                    <div class="visual_block_inner-text service_info_{$key}">
                                        <div class="price"><b class="{$key}_price"></b> руб.</div>
                                        <div class="srok"><b class="{$key}_srok"></b></div>
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>
            {/foreach}
        </div>
        <div class="service-map">
            <div class="sl-alert sl-alert-info">{('shoplogistic_choose_pvz') | lexicon}</div>
            <div id="service-map"></div>
            <div class="choosed_data">
                <span class="sl_pvz"></span>
            </div>
        </div>
    </div>
{/if}