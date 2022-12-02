{if !count($products)}
    <div class="alert alert-info">{'ms2_cart_is_empty' | lexicon}</div>
{else}
    <div class="msCart" id="msCart">
        <div class="sl-row">
            <div class="d-col-6">
                <span class="title">Корзина</span>
            </div>
            <div class="d-col-6 text-right">
                <form method="post" class="ms2_form">
                    <button type="submit" name="ms2_action" value="cart/clean" class="sl-btn sl-btn-text">
                        Очистить корзину
                    </button>
                </form>
            </div>
        </div>
        <div class="cart-holder">
            {foreach $products as $product}
                <!-- ROW -->
                <div class="cart_row" id="{$product.key}">
                    <div class="deleter">
                        <form method="post" class="ms2_form">
                            <input type="hidden" name="key" value="{$product.key}">
                            <button class="sl-btn sl-btn-text" type="submit" name="ms2_action"
                                    value="cart/remove" title="Удалить"><i class="fa fa-trash"
                                                                           aria-hidden="true"></i>
                            </button>
                        </form>
                    </div>
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
                            <!-- <span class="name">{$product.pagetitle | htmlent}</span> -->
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
                        <div class="d-col-6 d-col-md-3">
                            <form method="post" class="ms2_form form-inline" role="form">
                                <input type="hidden" name="key" value="{$product.key}">
                                <div class="cb-qnt sl-quantity">
                                    <button type="button" class="btn-count minus">-</button>
                                    <input type="text" name="count" class="counter" value="{$product.count}" data-krat="{($resource.parent | resource : "krat")?:1}" data-min="{($resource.parent | resource : "min")?:1}">
                                    <button type="button" class="btn-count plus">+</button>
                                </div>
                                <button type="submit" name="ms2_action"
                                        value="cart/change"><i class="fa fa-refresh"
                                                               aria-hidden="true"></i></button>
                            </form>
                        </div>
                        <div class="d-col-6 d-col-md-2">
                            <span class="price"><span>{$product.price}</span> руб.</span>
                        </div>
                    </div>
                </div>
                <!-- / ROW -->
            {/foreach}
        </div>
    </div>
{/if}