<section class="sl-def-section sl_get_order">
    <div class="sl-base_wrapper">
        <h2>Уважаемый покупатель!</h2>
        <div class="text-center">
            <p>Ваш заказ успешно оформлен и поставлен в очередь на обработку. <br/>В ближайшее время с вами свяжется наш специалист для уточнения деталей заказа. <br/>Заказу был присвоен номер:</p>
        </div>
        <div class="text-center">
            <span class="sl_order_num">{$order.num}</span>
        </div>
        <div class="cart_wrap">
            <div class="sl-row">
                <div class="d-col-lg-8">
                    <div class="cart-holder">
                        {foreach $products as $product}
                            <!-- ROW -->
                            <div class="cart_row" id="{$product.key}">
                                <div class="sl-row sl-align-items-center">
                                    <div class="d-col-6 d-col-md-2">
                                        <div class="image">
                                            <a href="{$product.id | url}">
                                                {if $product.image}
                                                    <img src="{$product.image}" alt="{$product.pagetitle | htmlent}" title="{$product.pagetitle | htmlent}">
                                                {else}
                                                    <img src="{$_modx->config.conf_noimage}" alt="{$product.pagetitle | htmlent}" title="{$product.pagetitle | htmlent}"/>
                                                {/if}
                                            </a>
                                        </div>
                                    </div>
                                    <div class="d-col-6 d-col-md-5">
                                        {if $product.article}
                                            <span class="article">{$product.article}</span>
                                        {/if}
                                        <a href="{$product.id | url}" class="name">{$product.pagetitle | htmlent}</a>
                                        {if $product.options?}
                                            {foreach $product.options as $key => $option}
                                                {if $key in ['modification','modifications','msal']}{continue}{/if}

                                                {set $caption = $product[$key ~ '.caption']}
                                                {set $caption = $caption ? $caption : ('ms2_product_' ~ $key) | lexicon}

                                                {if $option is array}
                                                    {$caption} - {$option | join : '; '} <br>
                                                {else}
                                                    {$caption} - {$option} <br>
                                                {/if}

                                            {/foreach}
                                        {/if}
                                    </div>
                                    <div class="d-col-6 d-col-md-5">
                                        <div class="text-right">
                                            <span class="price"><span>{$product.price}</span> руб.</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- / ROW -->
                        {/foreach}
                    </div>
                </div>
                <div class="d-col-lg-4">
                    <div class="summary_block">
                        <div class="summary_title_block">
                            <span class="summary_title">Итого</span>
                        </div>
                        <ul class="summary-summation">
                            <li>
                                <div class="summary-summation__term ">Товары</div>
                                <div class="summary-summation__data "><span id="ms2_order_cart_cost">{$total.cart_cost?:0}</span>&thinsp;₽</div>
                            </li>
                            <li>
                                <div class="summary-summation__term ">Доставка</div>
                                <div class="summary-summation__data "><span id="ms2_order_delivery_cost">{$total.delivery_cost?:0}</span>&thinsp;₽</div>
                            </li>
                            <li>
                                <div class="summary-summation__total"><span id="ms2_order_cost">{$total.cost?:0}</span>&thinsp;₽</div>
                            </li>
                        </ul>
                        <!--
                        <div class="text-center">
                            <button type="button" data-toggle="modal" data-target="#callback" class="sl-btn sl-btn-text texter"> <i class="fa fa-phone"></i> Уточнить по телефону</button>
                        </div>
                        -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>